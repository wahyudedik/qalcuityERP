<?php

namespace App\Http\Controllers\Api\Telecom;

use App\Models\Customer;
use App\Models\TelecomSubscription;
use App\Services\Telecom\UsageTrackingService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UsageController extends TelecomApiController
{
    protected UsageTrackingService $usageService;

    public function __construct()
    {
        $this->usageService = new UsageTrackingService;
    }

    /**
     * Get usage data for a customer.
     *
     * GET /api/telecom/usage/{customerId}
     */
    public function index(Request $request, int $customerId)
    {
        try {
            // Verify customer belongs to tenant
            $customer = Customer::where('id', $customerId)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->firstOrFail();

            $period = $request->get('period', 'monthly');

            // Get active subscription
            $subscription = TelecomSubscription::where('customer_id', $customerId)
                ->where('status', 'active')
                ->latest()
                ->first();

            if (! $subscription) {
                return $this->error('No active subscription found for this customer', 404);
            }

            $usageSummary = $this->usageService->getUsageSummary($subscription, $period);

            $this->logApiRequest($request, "GET /api/telecom/usage/{$customerId}", [
                'period' => $period,
            ]);

            return $this->success([
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                ],
                'subscription' => [
                    'id' => $subscription->id,
                    'package_name' => $subscription->package?->name ?? 'N/A',
                    'status' => $subscription->status,
                    'started_at' => $subscription->started_at,
                    'ends_at' => $subscription->ends_at,
                ],
                'usage' => $usageSummary,
            ]);

        } catch (ModelNotFoundException $e) {
            return $this->error('Customer not found', 404);
        } catch (\Exception $e) {
            Log::error('Failed to get usage data', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to get usage data: '.$e->getMessage(), 500);
        }
    }

    /**
     * Record usage data (called by router webhook or polling job).
     *
     * POST /api/telecom/usage/record
     */
    public function record(Request $request)
    {
        try {
            $validated = $request->validate([
                'subscription_id' => 'required|exists:telecom_subscriptions,id',
                'bytes_in' => 'required|integer|min:0',
                'bytes_out' => 'required|integer|min:0',
                'packets_in' => 'nullable|integer|min:0',
                'packets_out' => 'nullable|integer|min:0',
                'duration_seconds' => 'nullable|integer|min:0',
                'peak_bandwidth_kbps' => 'nullable|integer|min:0',
                'ip_address' => 'nullable|ip',
                'mac_address' => 'nullable|string',
                'period_type' => 'nullable|string|in:hourly,daily,weekly,monthly',
                'period_start' => 'nullable|date',
                'period_end' => 'nullable|date|after:period_start',
            ]);

            $subscription = TelecomSubscription::findOrFail($validated['subscription_id']);

            // Verify tenant ownership (if authenticated)
            if (auth()->check() && $subscription->tenant_id !== auth()->user()->tenant_id) {
                return $this->error('Unauthorized', 403);
            }

            $metadata = [
                'packets_in' => $validated['packets_in'] ?? 0,
                'packets_out' => $validated['packets_out'] ?? 0,
                'duration_seconds' => $validated['duration_seconds'] ?? 0,
                'peak_bandwidth_kbps' => $validated['peak_bandwidth_kbps'] ?? 0,
                'ip_address' => $validated['ip_address'] ?? null,
                'mac_address' => $validated['mac_address'] ?? null,
                'period_type' => $validated['period_type'] ?? 'daily',
                'period_start' => $validated['period_start'] ?? now()->startOfDay(),
                'period_end' => $validated['period_end'] ?? now()->endOfDay(),
            ];

            $usageRecord = $this->usageService->recordUsage(
                $subscription,
                $validated['bytes_in'],
                $validated['bytes_out'],
                $metadata
            );

            $this->logApiRequest($request, 'POST /api/telecom/usage/record', [
                'subscription_id' => $subscription->id,
                'bytes_total' => $usageRecord->bytes_total,
            ]);

            return $this->success([
                'usage_record' => $usageRecord,
                'quota_used_bytes' => $subscription->fresh()->quota_used_bytes,
                'quota_exceeded' => $subscription->fresh()->quota_exceeded,
            ], 'Usage recorded successfully', 201);

        } catch (ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Failed to record usage', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('Failed to record usage: '.$e->getMessage(), 500);
        }
    }
}
