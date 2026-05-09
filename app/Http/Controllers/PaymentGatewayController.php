<?php

namespace App\Http\Controllers;

use App\Models\ErpNotification;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Notifications\SubscriptionPaymentFailedNotification;
use App\Services\AffiliateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentGatewayController extends Controller
{
    // ─── Midtrans ─────────────────────────────────────────────────

    public function midtransCheckout(Request $request)
    {
        $data = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing' => 'required|in:monthly,yearly',
        ]);

        $plan = SubscriptionPlan::findOrFail($data['plan_id']);
        $tenant = auth()->user()->tenant;
        $amount = $data['billing'] === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $orderId = 'SUB-'.$tenant->id.'-'.time();

        // Record pending payment
        $payment = SubscriptionPayment::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'order_id' => $orderId,
            'amount' => $amount,
            'billing' => $data['billing'],
            'gateway' => 'midtrans',
            'status' => 'pending',
        ]);

        // Midtrans Snap API
        $serverKey = config('services.midtrans.server_key');
        $isProduction = config('services.midtrans.is_production', false);
        $baseUrl = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $response = Http::withBasicAuth($serverKey, '')
            ->post($baseUrl, [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $amount,
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'phone' => $tenant->phone ?? '',
                ],
                'item_details' => [[
                    'id' => $plan->slug,
                    'price' => (int) $amount,
                    'quantity' => 1,
                    'name' => "Qalcuity ERP - {$plan->name} (".ucfirst($data['billing']).')',
                ]],
                'callbacks' => [
                    'finish' => route('payment.midtrans.finish'),
                ],
            ]);

        if (! $response->successful() || ! isset($response['token'])) {
            return back()->with('error', 'Gagal membuat sesi pembayaran Midtrans. Coba lagi.');
        }

        $payment->update(['gateway_token' => $response['token']]);

        return view('subscription.checkout', [
            'gateway' => 'midtrans',
            'snapToken' => $response['token'],
            'isProduction' => $isProduction,
            'plan' => $plan,
            'amount' => $amount,
            'billing' => $data['billing'],
            'orderId' => $orderId,
        ]);
    }

    public function midtransFinish(Request $request)
    {
        $orderId = $request->order_id;
        $payment = SubscriptionPayment::where('order_id', $orderId)->first();

        if ($payment && in_array($request->transaction_status, ['settlement', 'capture'])) {
            $this->activatePlan($payment);
        }

        return redirect()->route('subscription.index')
            ->with('success', 'Pembayaran berhasil! Paket Anda telah diaktifkan.');
    }

    public function midtransWebhook(Request $request)
    {
        // Signature sudah diverifikasi oleh VerifyWebhookSignature middleware
        $orderId = $request->order_id;

        $payment = SubscriptionPayment::where('order_id', $orderId)->first();
        if (! $payment) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if (in_array($request->transaction_status, ['settlement', 'capture'])) {
            $this->activatePlan($payment);
        } elseif (in_array($request->transaction_status, ['cancel', 'deny', 'expire'])) {
            $payment->update(['status' => 'failed']);
            $this->notifyPaymentFailed($payment, $request->transaction_status);
        }

        return response()->json(['message' => 'OK']);
    }

    // ─── Xendit ───────────────────────────────────────────────────

    public function xenditCheckout(Request $request)
    {
        $data = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing' => 'required|in:monthly,yearly',
        ]);

        $plan = SubscriptionPlan::findOrFail($data['plan_id']);
        $tenant = auth()->user()->tenant;
        $amount = $data['billing'] === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $orderId = 'SUB-'.$tenant->id.'-'.time();

        $payment = SubscriptionPayment::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'order_id' => $orderId,
            'amount' => $amount,
            'billing' => $data['billing'],
            'gateway' => 'xendit',
            'status' => 'pending',
        ]);

        // Xendit Invoice API
        $response = Http::withBasicAuth(config('services.xendit.secret_key'), '')
            ->post('https://api.xendit.co/v2/invoices', [
                'external_id' => $orderId,
                'amount' => (int) $amount,
                'description' => "Qalcuity ERP - {$plan->name} (".ucfirst($data['billing']).')',
                'payer_email' => auth()->user()->email,
                'customer' => [
                    'given_names' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'mobile_number' => $tenant->phone ?? '',
                ],
                'success_redirect_url' => route('payment.xendit.finish'),
                'failure_redirect_url' => route('subscription.index'),
                'currency' => 'IDR',
                'items' => [[
                    'name' => "Qalcuity ERP {$plan->name}",
                    'quantity' => 1,
                    'price' => (int) $amount,
                ]],
            ]);

        if (! $response->successful() || ! isset($response['invoice_url'])) {
            return back()->with('error', 'Gagal membuat invoice Xendit. Coba lagi.');
        }

        $payment->update([
            'gateway_token' => $response['id'],
            'gateway_url' => $response['invoice_url'],
        ]);

        return redirect($response['invoice_url']);
    }

    public function xenditFinish(Request $request)
    {
        return redirect()->route('subscription.index')
            ->with('success', 'Pembayaran sedang diproses. Paket akan aktif dalam beberapa menit.');
    }

    public function xenditWebhook(Request $request)
    {
        // Signature sudah diverifikasi oleh VerifyWebhookSignature middleware
        $externalId = $request->external_id;
        $status = $request->status;

        $payment = SubscriptionPayment::where('order_id', $externalId)->first();
        if (! $payment) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if ($status === 'PAID') {
            $this->activatePlan($payment);
        } elseif (in_array($status, ['EXPIRED', 'FAILED'])) {
            $payment->update(['status' => 'failed']);
            $this->notifyPaymentFailed($payment, $status);
        }

        return response()->json(['message' => 'OK']);
    }

    // ─── Shared ───────────────────────────────────────────────────

    private function activatePlan(SubscriptionPayment $payment): void
    {
        if ($payment->status === 'paid') {
            return;
        } // idempotent

        $payment->update(['status' => 'paid', 'paid_at' => now()]);

        $plan = $payment->plan;
        $tenant = $payment->tenant;

        $expiresAt = $payment->billing === 'yearly'
            ? now()->addYear()
            : now()->addMonth();

        $tenant->update([
            'subscription_plan_id' => $plan->id,
            'plan' => $plan->slug,
            'plan_expires_at' => $expiresAt,
            'is_active' => true,
        ]);

        // Affiliate commission
        app(AffiliateService::class)->createCommission($tenant, $payment);
    }

    private function notifyPaymentFailed(SubscriptionPayment $payment, string $reason): void
    {
        $payment->load(['tenant', 'plan']);
        $tenant = $payment->tenant;
        if (! $tenant) {
            return;
        }

        $reasonLabel = match (strtolower($reason)) {
            'cancel' => 'Dibatalkan oleh pengguna',
            'deny' => 'Ditolak oleh bank/penyedia pembayaran',
            'expire' => 'Waktu pembayaran habis',
            'expired' => 'Waktu pembayaran habis',
            'failed' => 'Gagal diproses',
            default => $reason,
        };

        $admins = User::where('tenant_id', $tenant->id)
            ->where('role', 'admin')
            ->get();

        foreach ($admins as $admin) {
            // In-app notification
            ErpNotification::create([
                'tenant_id' => $tenant->id,
                'user_id' => $admin->id,
                'type' => 'payment_failed',
                'title' => '❌ Pembayaran Langganan Gagal',
                'body' => "Pembayaran paket {$payment->plan?->name} senilai Rp ".
                               number_format($payment->amount, 0, ',', '.').
                               " gagal. Alasan: {$reasonLabel}.",
                'data' => [
                    'order_id' => $payment->order_id,
                    'amount' => $payment->amount,
                    'reason' => $reasonLabel,
                    'gateway' => $payment->gateway,
                ],
            ]);

            // Email notification
            $admin->notify(new SubscriptionPaymentFailedNotification(
                tenantName: $tenant->name,
                plan: $payment->plan?->name ?? $payment->gateway,
                amount: (float) $payment->amount,
                reason: $reasonLabel,
                orderId: $payment->order_id,
            ));
        }
    }
}
