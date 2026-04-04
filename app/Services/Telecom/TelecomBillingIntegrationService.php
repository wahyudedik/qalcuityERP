<?php

namespace App\Services\Telecom;

use App\Models\Invoice;
use App\Models\TelecomSubscription;
use App\Models\Customer;
use App\Services\WebhookService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TelecomBillingIntegrationService
{
    protected WebhookService $webhookService;
    protected NotificationService $notificationService;

    public function __construct()
    {
        $this->webhookService = app(WebhookService::class);
        $this->notificationService = app(NotificationService::class);
    }

    /**
     * Generate invoice for telecom subscription.
     */
    public function generateSubscriptionInvoice(TelecomSubscription $subscription, array $options = []): Invoice
    {
        return DB::transaction(function () use ($subscription, $options) {
            $package = $subscription->package;
            $customer = $subscription->customer;
            $tenantId = $subscription->tenant_id;

            // Calculate amounts
            $baseAmount = $options['amount'] ?? $package->price;
            $discount = $options['discount'] ?? 0;
            $taxRate = $options['tax_rate'] ?? 11; // Default PPN 11%
            $taxAmount = round(($baseAmount - $discount) * ($taxRate / 100), 2);
            $totalAmount = $baseAmount - $discount + $taxAmount;

            // Determine billing period
            $periodStart = $options['period_start'] ?? now();
            $periodEnd = match ($package->billing_cycle) {
                'monthly' => $periodStart->copy()->addMonth(),
                'quarterly' => $periodStart->copy()->addMonths(3),
                'semi_annual' => $periodStart->copy()->addMonths(6),
                'annual' => $periodStart->copy()->addYear(),
                default => $periodStart->copy()->addMonth(),
            };

            // Create invoice
            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customer->id,
                'number' => $this->generateInvoiceNumber($tenantId),
                'issue_date' => now(),
                'due_date' => $periodStart->copy()->addDays($options['payment_terms'] ?? 7),
                'status' => 'unpaid',
                'subtotal' => $baseAmount - $discount,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount,
                'total_amount' => $totalAmount,
                'currency' => 'IDR',
                'notes' => "Internet subscription: {$package->name} ({$periodStart->format('M Y')} - {$periodEnd->format('M Y')})",
                'metadata' => [
                    'type' => 'telecom_subscription',
                    'subscription_id' => $subscription->id,
                    'package_id' => $package->id,
                    'period_start' => $periodStart->toIso8601String(),
                    'period_end' => $periodEnd->toIso8601String(),
                    'billing_cycle' => $package->billing_cycle,
                ],
            ]);

            // Add invoice line item
            $invoice->items()->create([
                'description' => "Internet Package: {$package->name}\n" .
                    "Speed: {$package->download_speed_mbps}/{$package->upload_speed_mbps} Mbps\n" .
                    "Quota: " . ($package->quota_bytes ? round($package->quota_bytes / 1073741824, 2) . ' GB' : 'Unlimited') . "\n" .
                    "Period: {$periodStart->format('d M Y')} - {$periodEnd->format('d M Y')}",
                'quantity' => 1,
                'unit_price' => $baseAmount - $discount,
                'total' => $baseAmount - $discount,
                'tax_rate' => $taxRate,
                'metadata' => [
                    'subscription_id' => $subscription->id,
                    'package_id' => $package->id,
                ],
            ]);

            // Update subscription billing info
            $subscription->update([
                'last_billing_date' => now(),
                'next_billing_date' => $periodEnd,
            ]);

            // Trigger webhook
            $this->webhookService->dispatch($tenantId, 'invoice.created', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->number,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'amount' => $totalAmount,
                'due_date' => $invoice->due_date->toIso8601String(),
                'subscription_id' => $subscription->id,
                'package_name' => $package->name,
            ]);

            // Send notification to customer
            $this->notificationService->sendToCustomer($customer, 'invoice.created', [
                'invoice_number' => $invoice->number,
                'amount' => $totalAmount,
                'due_date' => $invoice->due_date->format('d M Y'),
                'package_name' => $package->name,
            ]);

            Log::info("Telecom subscription invoice generated", [
                'invoice_id' => $invoice->id,
                'subscription_id' => $subscription->id,
                'amount' => $totalAmount,
            ]);

            return $invoice;
        });
    }

    /**
     * Generate invoices for all due subscriptions.
     */
    public function generateDueInvoices(): array
    {
        $dueSubscriptions = TelecomSubscription::where('status', 'active')
            ->whereNotNull('next_billing_date')
            ->where('next_billing_date', '<=', now())
            ->with(['customer', 'package'])
            ->get();

        $generated = [];
        $failed = [];

        foreach ($dueSubscriptions as $subscription) {
            try {
                $invoice = $this->generateSubscriptionInvoice($subscription);
                $generated[] = [
                    'subscription_id' => $subscription->id,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->number,
                    'amount' => $invoice->total_amount,
                ];
            } catch (\Exception $e) {
                $failed[] = [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ];

                Log::error("Failed to generate telecom invoice", [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'generated' => $generated,
            'failed' => $failed,
            'total_processed' => $dueSubscriptions->count(),
            'success_count' => count($generated),
            'failed_count' => count($failed),
        ];
    }

    /**
     * Handle payment success for telecom invoice.
     */
    public function handlePaymentSuccess(Invoice $invoice): void
    {
        if (!isset($invoice->metadata['subscription_id'])) {
            return;
        }

        $subscription = TelecomSubscription::find($invoice->metadata['subscription_id']);

        if (!$subscription) {
            return;
        }

        // Update subscription status if needed
        if ($subscription->status === 'suspended') {
            $subscription->update(['status' => 'active']);

            // Reconnect user to router
            if ($subscription->hotspot_username) {
                try {
                    $adapter = RouterAdapterFactory::create($subscription->device);
                    $adapter->reconnectUser($subscription->hotspot_username);
                } catch (\Exception $e) {
                    Log::warning("Failed to reconnect user after payment", [
                        'subscription_id' => $subscription->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Trigger webhook
        $this->webhookService->dispatch($invoice->tenant_id, 'telecom.payment_received', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->number,
            'subscription_id' => $subscription->id,
            'customer_id' => $subscription->customer_id,
            'amount' => $invoice->total_amount,
            'package_name' => $subscription->package->name,
        ]);

        // Send notification
        $this->notificationService->sendToCustomer($subscription->customer, 'telecom.payment_confirmed', [
            'invoice_number' => $invoice->number,
            'amount' => $invoice->total_amount,
            'package_name' => $subscription->package->name,
            'next_billing_date' => $subscription->next_billing_date?->format('d M Y'),
        ]);

        Log::info("Telecom subscription payment confirmed", [
            'invoice_id' => $invoice->id,
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Handle quota exceeded event.
     */
    public function handleQuotaExceeded(TelecomSubscription $subscription): void
    {
        $customer = $subscription->customer;
        $package = $subscription->package;

        // Trigger webhook
        $this->webhookService->dispatch($subscription->tenant_id, 'telecom.quota_exceeded', [
            'subscription_id' => $subscription->id,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'package_name' => $package->name,
            'quota_gb' => round($package->quota_bytes / 1073741824, 2),
            'current_usage_gb' => round($subscription->current_usage_bytes / 1073741824, 2),
            'exceeded_at' => now()->toIso8601String(),
        ]);

        // Send notification to customer
        $this->notificationService->sendToCustomer($customer, 'telecom.quota_exceeded', [
            'package_name' => $package->name,
            'quota_gb' => round($package->quota_bytes / 1073741824, 2),
            'current_usage_gb' => round($subscription->current_usage_bytes / 1073741824, 2),
            'action_required' => 'Upgrade package or wait for next billing cycle',
        ]);

        // Send notification to admin
        $this->notificationService->sendToAdmin('telecom.quota_exceeded_alert', [
            'subscription_id' => $subscription->id,
            'customer_name' => $customer->name,
            'package_name' => $package->name,
            'usage_percentage' => round(($subscription->current_usage_bytes / $package->quota_bytes) * 100, 2),
        ]);

        Log::info("Quota exceeded alert sent", [
            'subscription_id' => $subscription->id,
            'customer_id' => $customer->id,
        ]);
    }

    /**
     * Handle device offline event.
     */
    public function handleDeviceOffline(\App\Models\NetworkDevice $device): void
    {
        $tenantId = $device->tenant_id;

        // Get affected subscriptions
        $affectedSubscriptions = TelecomSubscription::where('device_id', $device->id)
            ->where('status', 'active')
            ->count();

        // Trigger webhook
        $this->webhookService->dispatch($tenantId, 'telecom.device_offline', [
            'device_id' => $device->id,
            'device_name' => $device->name,
            'device_type' => $device->device_type,
            'ip_address' => $device->ip_address,
            'brand' => $device->brand,
            'last_seen_at' => $device->last_seen_at?->toIso8601String(),
            'affected_subscriptions' => $affectedSubscriptions,
            'offline_at' => now()->toIso8601String(),
        ]);

        // Send notification to admin
        $this->notificationService->sendToAdmin('telecom.device_offline_alert', [
            'device_id' => $device->id,
            'device_name' => $device->name,
            'ip_address' => $device->ip_address,
            'affected_subscriptions' => $affectedSubscriptions,
            'severity' => $affectedSubscriptions > 10 ? 'critical' : 'high',
        ]);

        Log::warning("Device offline alert sent", [
            'device_id' => $device->id,
            'device_name' => $device->name,
            'affected_subscriptions' => $affectedSubscriptions,
        ]);
    }

    /**
     * Generate unique invoice number.
     */
    protected function generateInvoiceNumber(int $tenantId): string
    {
        $prefix = config('app.name', 'QALC');
        $date = now()->format('Ymd');

        // Get last invoice number for today
        $lastInvoice = Invoice::where('tenant_id', $tenantId)
            ->where('number', 'like', "{$prefix}-TEL-{$date}-%")
            ->orderBy('number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}-TEL-{$date}-{$newNumber}";
    }
}
