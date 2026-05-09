<?php

namespace App\Services\MultiCompany;

use App\Models\SharedService;
use App\Models\SharedServiceBilling;
use App\Models\SharedServiceSubscription;

class SharedServiceService
{
    /**
     * Create shared service
     */
    public function createService(array $data): SharedService
    {
        return SharedService::create([
            'company_group_id' => $data['company_group_id'],
            'provider_tenant_id' => $data['provider_tenant_id'],
            'service_name' => $data['service_name'],
            'description' => $data['description'] ?? null,
            'billing_method' => $data['billing_method'] ?? 'allocation',
            'fixed_fee' => $data['fixed_fee'] ?? null,
            'allocation_rules' => $data['allocation_rules'] ?? null,
            'is_active' => true,
        ]);
    }

    /**
     * Subscribe tenant to service
     */
    public function subscribeTenant(int $serviceId, int $tenantId, float $allocationPercentage): bool
    {
        try {
            SharedServiceSubscription::create([
                'shared_service_id' => $serviceId,
                'subscriber_tenant_id' => $tenantId,
                'allocation_percentage' => $allocationPercentage,
                'start_date' => now(),
                'is_active' => true,
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Subscribe tenant failed', [
                'service_id' => $serviceId,
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Generate billings for period
     */
    public function generateBillings(int $serviceId, string $periodStart, string $periodEnd): array
    {
        $service = SharedService::findOrFail($serviceId);
        $subscriptions = SharedServiceSubscription::where('shared_service_id', $serviceId)
            ->where('is_active', true)
            ->get();

        $billings = [];

        foreach ($subscriptions as $subscription) {
            $amount = $this->calculateBillingAmount($service, $subscription, $periodStart, $periodEnd);

            $billing = SharedServiceBilling::create([
                'shared_service_id' => $serviceId,
                'subscriber_tenant_id' => $subscription->subscriber_tenant_id,
                'billing_period_start' => $periodStart,
                'billing_period_end' => $periodEnd,
                'amount' => $amount,
                'currency' => 'IDR',
                'status' => 'pending',
                'calculation_details' => [
                    'method' => $service->billing_method,
                    'allocation_percentage' => $subscription->allocation_percentage,
                    'base_amount' => $service->fixed_fee ?? 0,
                ],
            ]);

            $billings[] = $billing;
        }

        return $billings;
    }

    /**
     * Mark billing as invoiced
     */
    public function markAsInvoiced(int $billingId, int $invoiceId): bool
    {
        try {
            $billing = SharedServiceBilling::findOrFail($billingId);
            $billing->update([
                'status' => 'invoiced',
                'invoice_id' => $invoiceId,
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Mark as invoiced failed', [
                'billing_id' => $billingId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Mark billing as paid
     */
    public function markAsPaid(int $billingId): bool
    {
        try {
            $billing = SharedServiceBilling::findOrFail($billingId);
            $billing->update(['status' => 'paid']);

            return true;
        } catch (\Exception $e) {
            \Log::error('Mark as paid failed', [
                'billing_id' => $billingId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get service subscribers
     */
    public function getServiceSubscribers(int $serviceId): array
    {
        return SharedServiceSubscription::where('shared_service_id', $serviceId)
            ->where('is_active', true)
            ->with('subscriberTenant')
            ->get()
            ->map(function ($sub) {
                return [
                    'tenant_id' => $sub->subscriber_tenant_id,
                    'tenant_name' => $sub->subscriberTenant->name ?? 'Unknown',
                    'allocation_percentage' => $sub->allocation_percentage,
                    'start_date' => $sub->start_date,
                ];
            })
            ->toArray();
    }

    /**
     * Get pending billings
     */
    public function getPendingBillings(int $groupId): array
    {
        return SharedServiceBilling::whereHas('sharedService', function ($query) use ($groupId) {
            $query->where('company_group_id', $groupId);
        })
            ->where('status', 'pending')
            ->with(['sharedService', 'subscriberTenant'])
            ->orderBy('billing_period_end', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Calculate billing amount
     */
    protected function calculateBillingAmount(SharedService $service, SharedServiceSubscription $subscription, string $start, string $end): float
    {
        if ($service->billing_method === 'fixed_fee') {
            return $service->fixed_fee * ($subscription->allocation_percentage / 100);
        }

        // For allocation or usage-based, would calculate based on rules
        return 0.00;
    }
}
