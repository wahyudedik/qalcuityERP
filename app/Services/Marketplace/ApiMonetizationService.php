<?php

namespace App\Services\Marketplace;

use App\Models\ApiKey;
use App\Models\ApiUsageLog;
use App\Models\ApiSubscription;
use Illuminate\Support\Str;

class ApiMonetizationService
{
    /**
     * Generate API key
     */
    public function generateApiKey(int $tenantId, int $userId, string $name, array $permissions = [], int $rateLimit = 1000): ApiKey
    {
        return ApiKey::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'key' => 'ak_' . Str::random(40),
            'name' => $name,
            'permissions' => $permissions,
            'rate_limit' => $rateLimit,
            'is_active' => true,
        ]);
    }

    /**
     * Validate API key
     */
    public function validateApiKey(string $apiKey): ?ApiKey
    {
        $key = ApiKey::where('key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (!$key) {
            return null;
        }

        // Check expiration
        if ($key->expires_at && $key->expires_at < now()) {
            return null;
        }

        // Check rate limit
        if ($key->requests_used >= $key->rate_limit) {
            return null;
        }

        return $key;
    }

    /**
     * Track API usage
     */
    public function trackUsage(int $apiKeyId, string $endpoint, string $method, int $responseCode, int $responseTime, ?string $ipAddress = null): void
    {
        ApiUsageLog::create([
            'api_key_id' => $apiKeyId,
            'endpoint' => $endpoint,
            'method' => $method,
            'response_code' => $responseCode,
            'response_time' => $responseTime,
            'ip_address' => $ipAddress,
        ]);

        // Increment usage counter
        ApiKey::where('id', $apiKeyId)->increment('requests_used');
        ApiKey::where('id', $apiKeyId)->update(['last_used_at' => now()]);
    }

    /**
     * Check rate limit
     */
    public function checkRateLimit(int $apiKeyId): bool
    {
        $key = ApiKey::find($apiKeyId);

        if (!$key) {
            return false;
        }

        // Reset counter if new hour
        if ($key->last_used_at && $key->last_used_at->hour !== now()->hour) {
            $key->update(['requests_used' => 0]);
            return true;
        }

        return $key->requests_used < $key->rate_limit;
    }

    /**
     * Create API subscription
     */
    public function createSubscription(int $tenantId, string $planName, int $rateLimit, float $price, string $billingPeriod = 'monthly', array $features = []): ApiSubscription
    {
        return ApiSubscription::create([
            'tenant_id' => $tenantId,
            'plan_name' => $planName,
            'rate_limit' => $rateLimit,
            'price' => $price,
            'billing_period' => $billingPeriod,
            'features' => $features,
            'starts_at' => now(),
            'ends_at' => $billingPeriod === 'monthly' ? now()->addMonth() : now()->addYear(),
            'status' => 'active',
        ]);
    }

    /**
     * Upgrade subscription plan
     */
    public function upgradePlan(int $subscriptionId, string $newPlan, int $newRateLimit, float $newPrice): bool
    {
        try {
            $subscription = ApiSubscription::findOrFail($subscriptionId);
            $subscription->update([
                'plan_name' => $newPlan,
                'rate_limit' => $newRateLimit,
                'price' => $newPrice,
            ]);

            // Update all API keys for this tenant
            ApiKey::where('tenant_id', $subscription->tenant_id)
                ->update(['rate_limit' => $newRateLimit]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Upgrade plan failed', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get usage statistics
     */
    public function getUsageStats(int $tenantId, ?string $period = null): array
    {
        $query = ApiUsageLog::whereHas('apiKey', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        });

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'this_week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'this_month') {
            $query->whereMonth('created_at', now()->month);
        }

        $totalRequests = $query->count();
        $avgResponseTime = $query->avg('response_time');
        $errorCount = $query->where('response_code', '>=', 400)->count();

        // Top endpoints
        $topEndpoints = $query->selectRaw('endpoint, COUNT(*) as count')
            ->groupBy('endpoint')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'total_requests' => $totalRequests,
            'average_response_time' => round($avgResponseTime ?? 0, 2),
            'error_count' => $errorCount,
            'error_rate' => $totalRequests > 0 ? round(($errorCount / $totalRequests) * 100, 2) : 0,
            'top_endpoints' => $topEndpoints,
            'period' => $period ?? 'all_time',
        ];
    }

    /**
     * Get tenant's subscription
     */
    public function getSubscription(int $tenantId): ?ApiSubscription
    {
        return ApiSubscription::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('ends_at', '>=', now())
            ->first();
    }
}
