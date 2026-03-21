<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentGatewayController extends Controller
{
    // ─── Midtrans ─────────────────────────────────────────────────

    public function midtransCheckout(Request $request)
    {
        $data = $request->validate([
            'plan_id'  => 'required|exists:subscription_plans,id',
            'billing'  => 'required|in:monthly,yearly',
        ]);

        $plan   = SubscriptionPlan::findOrFail($data['plan_id']);
        $tenant = auth()->user()->tenant;
        $amount = $data['billing'] === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $orderId = 'SUB-' . $tenant->id . '-' . time();

        // Record pending payment
        $payment = SubscriptionPayment::create([
            'tenant_id'   => $tenant->id,
            'plan_id'     => $plan->id,
            'order_id'    => $orderId,
            'amount'      => $amount,
            'billing'     => $data['billing'],
            'gateway'     => 'midtrans',
            'status'      => 'pending',
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
                    'order_id'     => $orderId,
                    'gross_amount' => (int) $amount,
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name,
                    'email'      => auth()->user()->email,
                    'phone'      => $tenant->phone ?? '',
                ],
                'item_details' => [[
                    'id'       => $plan->slug,
                    'price'    => (int) $amount,
                    'quantity' => 1,
                    'name'     => "Qalcuity ERP - {$plan->name} (" . ucfirst($data['billing']) . ")",
                ]],
                'callbacks' => [
                    'finish' => route('payment.midtrans.finish'),
                ],
            ]);

        if (!$response->successful() || !isset($response['token'])) {
            return back()->with('error', 'Gagal membuat sesi pembayaran Midtrans. Coba lagi.');
        }

        $payment->update(['gateway_token' => $response['token']]);

        return view('subscription.checkout', [
            'gateway'      => 'midtrans',
            'snapToken'    => $response['token'],
            'isProduction' => $isProduction,
            'plan'         => $plan,
            'amount'       => $amount,
            'billing'      => $data['billing'],
            'orderId'      => $orderId,
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
        $serverKey  = config('services.midtrans.server_key');
        $orderId    = $request->order_id;
        $statusCode = $request->status_code;
        $grossAmount= $request->gross_amount;

        // Verify signature
        $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if ($signatureKey !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $payment = SubscriptionPayment::where('order_id', $orderId)->first();
        if (!$payment) return response()->json(['message' => 'Order not found'], 404);

        if (in_array($request->transaction_status, ['settlement', 'capture'])) {
            $this->activatePlan($payment);
        } elseif (in_array($request->transaction_status, ['cancel', 'deny', 'expire'])) {
            $payment->update(['status' => 'failed']);
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

        $plan    = SubscriptionPlan::findOrFail($data['plan_id']);
        $tenant  = auth()->user()->tenant;
        $amount  = $data['billing'] === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $orderId = 'SUB-' . $tenant->id . '-' . time();

        $payment = SubscriptionPayment::create([
            'tenant_id' => $tenant->id,
            'plan_id'   => $plan->id,
            'order_id'  => $orderId,
            'amount'    => $amount,
            'billing'   => $data['billing'],
            'gateway'   => 'xendit',
            'status'    => 'pending',
        ]);

        // Xendit Invoice API
        $response = Http::withBasicAuth(config('services.xendit.secret_key'), '')
            ->post('https://api.xendit.co/v2/invoices', [
                'external_id'      => $orderId,
                'amount'           => (int) $amount,
                'description'      => "Qalcuity ERP - {$plan->name} (" . ucfirst($data['billing']) . ")",
                'payer_email'      => auth()->user()->email,
                'customer'         => [
                    'given_names'  => auth()->user()->name,
                    'email'        => auth()->user()->email,
                    'mobile_number'=> $tenant->phone ?? '',
                ],
                'success_redirect_url' => route('payment.xendit.finish'),
                'failure_redirect_url' => route('subscription.index'),
                'currency'         => 'IDR',
                'items'            => [[
                    'name'     => "Qalcuity ERP {$plan->name}",
                    'quantity' => 1,
                    'price'    => (int) $amount,
                ]],
            ]);

        if (!$response->successful() || !isset($response['invoice_url'])) {
            return back()->with('error', 'Gagal membuat invoice Xendit. Coba lagi.');
        }

        $payment->update([
            'gateway_token' => $response['id'],
            'gateway_url'   => $response['invoice_url'],
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
        // Verify Xendit webhook token
        $callbackToken = config('services.xendit.webhook_token');
        if ($request->header('x-callback-token') !== $callbackToken) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $externalId = $request->external_id;
        $status     = $request->status;

        $payment = SubscriptionPayment::where('order_id', $externalId)->first();
        if (!$payment) return response()->json(['message' => 'Not found'], 404);

        if ($status === 'PAID') {
            $this->activatePlan($payment);
        } elseif (in_array($status, ['EXPIRED', 'FAILED'])) {
            $payment->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'OK']);
    }

    // ─── Shared ───────────────────────────────────────────────────

    private function activatePlan(SubscriptionPayment $payment): void
    {
        if ($payment->status === 'paid') return; // idempotent

        $payment->update(['status' => 'paid', 'paid_at' => now()]);

        $plan   = $payment->plan;
        $tenant = $payment->tenant;

        $expiresAt = $payment->billing === 'yearly'
            ? now()->addYear()
            : now()->addMonth();

        $tenant->update([
            'subscription_plan_id' => $plan->id,
            'plan'                 => $plan->slug,
            'plan_expires_at'      => $expiresAt,
            'is_active'            => true,
        ]);
    }
}
