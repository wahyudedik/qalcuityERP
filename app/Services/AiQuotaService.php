<?php

namespace App\Services;

use App\Models\AiUsageLog;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

/**
 * AiQuotaService — Centralized AI quota enforcement.
 *
 * Quota limits per plan (messages/month):
 *   trial      → 20
 *   basic      → 100
 *   pro        → 500
 *   enterprise → unlimited (-1)
 *
 * Uses a 30-second cache to reduce DB hits on rapid requests.
 * Cache is busted immediately after each tracked call.
 */
class AiQuotaService
{
    private const CACHE_TTL = 30; // seconds

    /**
     * Check if tenant has quota remaining.
     * Returns true if allowed, false if exceeded.
     */
    public function isAllowed(int $tenantId): bool
    {
        $limit = $this->getLimit($tenantId);
        if ($limit === -1) return true;

        return $this->getUsed($tenantId) < $limit;
    }

    /**
     * Track one AI call and return updated usage info.
     * Call this AFTER a successful AI response.
     */
    public function track(int $tenantId, int $userId, int $tokens = 0): array
    {
        $total = AiUsageLog::track($tenantId, $userId, $tokens);

        // Bust cache so next check is fresh
        Cache::forget($this->cacheKey($tenantId));

        $limit = $this->getLimit($tenantId);

        return [
            'used'      => $total,
            'limit'     => $limit,
            'remaining' => $limit === -1 ? null : max(0, $limit - $total),
            'unlimited' => $limit === -1,
        ];
    }

    /**
     * Get current usage for tenant this month (cached).
     */
    public function getUsed(int $tenantId): int
    {
        return Cache::remember(
            $this->cacheKey($tenantId),
            self::CACHE_TTL,
            fn() => AiUsageLog::tenantMonthlyCount($tenantId)
        );
    }

    /**
     * Get monthly limit for tenant based on their plan.
     * Returns -1 for unlimited.
     */
    public function getLimit(int $tenantId): int
    {
        return Cache::remember(
            "ai_limit_{$tenantId}",
            300, // cache plan limit 5 minutes
            function () use ($tenantId) {
                $tenant = Tenant::with('subscriptionPlan')->find($tenantId);
                return $tenant?->maxAiMessages() ?? 20;
            }
        );
    }

    /**
     * Get full quota status for a tenant.
     */
    public function status(int $tenantId): array
    {
        $limit = $this->getLimit($tenantId);
        $used  = $this->getUsed($tenantId);

        return [
            'used'       => $used,
            'limit'      => $limit,
            'remaining'  => $limit === -1 ? null : max(0, $limit - $used),
            'unlimited'  => $limit === -1,
            'exceeded'   => $limit !== -1 && $used >= $limit,
            'percent'    => $limit > 0 ? min(100, round(($used / $limit) * 100)) : 0,
            'warning'    => $limit !== -1 && $used >= ($limit * 0.8), // 80% threshold
        ];
    }

    /**
     * Bust the plan limit cache (call when tenant upgrades plan).
     */
    public function bustLimitCache(int $tenantId): void
    {
        Cache::forget("ai_limit_{$tenantId}");
        Cache::forget($this->cacheKey($tenantId));
    }

    private function cacheKey(int $tenantId): string
    {
        return "ai_quota_{$tenantId}_" . now()->format('Y-m');
    }
}
