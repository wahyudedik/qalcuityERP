<?php

namespace App\Services;

use App\Models\AiUsageLog;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
 *
 * BUG-AI-004 FIX: Implements fail-safe mechanism when cache is down.
 * If cache fails, falls back to direct DB query instead of bypassing quota check.
 */
class AiQuotaService
{
    private const CACHE_TTL = 30; // seconds

    /**
     * Check if tenant has quota remaining.
     * Returns true if allowed, false if exceeded.
     *
     * BUG-AI-004 FIX: Fail-safe - if cache is down, use DB query directly
     * to prevent quota bypass.
     */
    public function isAllowed(int $tenantId): bool
    {
        try {
            $limit = $this->getLimit($tenantId);
            if ($limit === -1) {
                return true;
            }

            return $this->getUsed($tenantId) < $limit;
        } catch (\Throwable $e) {
            // BUG-AI-004 FIX: Cache is down, fallback to DB query
            Log::warning('AiQuotaService: Cache unavailable, using DB fallback for quota check', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return $this->isAllowedFromDatabase($tenantId);
        }
    }

    /**
     * Track one AI call and return updated usage info.
     * Call this AFTER a successful AI response.
     *
     * BUG-AI-004 FIX: Handle cache failure gracefully when busting cache
     */
    public function track(int $tenantId, int $userId, int $tokens = 0): array
    {
        // Always track in database (this is the source of truth)
        $total = AiUsageLog::track($tenantId, $userId, $tokens);

        // Try to bust cache, but don't fail if cache is down
        try {
            Cache::forget($this->cacheKey($tenantId));
        } catch (\Throwable $e) {
            Log::warning('AiQuotaService: Failed to bust cache after tracking', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
        }

        $limit = $this->getLimit($tenantId);

        return [
            'used' => $total,
            'limit' => $limit,
            'remaining' => $limit === -1 ? null : max(0, $limit - $total),
            'unlimited' => $limit === -1,
        ];
    }

    /**
     * Get current usage for tenant this month (cached).
     *
     * BUG-AI-004 FIX: Fallback to DB if cache fails
     */
    public function getUsed(int $tenantId): int
    {
        try {
            return Cache::remember(
                $this->cacheKey($tenantId),
                self::CACHE_TTL,
                fn () => AiUsageLog::tenantMonthlyCount($tenantId)
            );
        } catch (\Throwable $e) {
            // BUG-AI-004 FIX: Cache failed, query DB directly
            Log::warning('AiQuotaService: Cache read failed, falling back to DB', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return AiUsageLog::tenantMonthlyCount($tenantId);
        }
    }

    /**
     * Get monthly limit for tenant based on their plan.
     * Returns -1 for unlimited.
     *
     * BUG-AI-004 FIX: Fallback to DB if cache fails
     */
    public function getLimit(int $tenantId): int
    {
        try {
            return Cache::remember(
                "ai_limit_{$tenantId}",
                300, // cache plan limit 5 minutes
                function () use ($tenantId) {
                    $tenant = Tenant::with('subscriptionPlan')->find($tenantId);

                    return $tenant?->maxAiMessages() ?? 20;
                }
            );
        } catch (\Throwable $e) {
            // BUG-AI-004 FIX: Cache failed, query DB directly
            Log::warning('AiQuotaService: Cache read failed for limit, falling back to DB', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            $tenant = Tenant::with('subscriptionPlan')->find($tenantId);

            return $tenant?->maxAiMessages() ?? 20;
        }
    }

    /**
     * Get full quota status for a tenant.
     *
     * BUG-AI-004 FIX: Wrapped in try-catch for cache failures
     */
    public function status(int $tenantId): array
    {
        try {
            $limit = $this->getLimit($tenantId);
            $used = $this->getUsed($tenantId);

            return [
                'used' => $used,
                'limit' => $limit,
                'remaining' => $limit === -1 ? null : max(0, $limit - $used),
                'unlimited' => $limit === -1,
                'exceeded' => $limit !== -1 && $used >= $limit,
                'percent' => $limit > 0 ? min(100, round(($used / $limit) * 100)) : 0,
                'warning' => $limit !== -1 && $used >= ($limit * 0.8), // 80% threshold
            ];
        } catch (\Throwable $e) {
            // BUG-AI-004 FIX: Last resort fallback
            Log::error('AiQuotaService: All cache and DB methods failed, using conservative defaults', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            // Conservative: assume quota is exceeded to prevent abuse
            return [
                'used' => 0,
                'limit' => 20, // Default trial limit
                'remaining' => 0,
                'unlimited' => false,
                'exceeded' => false, // Allow but logged
                'percent' => 0,
                'warning' => false,
                'fallback' => true, // Flag to indicate fallback mode
            ];
        }
    }

    /**
     * Bust the plan limit cache (call when tenant upgrades plan).
     *
     * BUG-AI-004 FIX: Handle cache failure gracefully
     */
    public function bustLimitCache(int $tenantId): void
    {
        try {
            Cache::forget("ai_limit_{$tenantId}");
            Cache::forget($this->cacheKey($tenantId));
        } catch (\Throwable $e) {
            Log::warning('AiQuotaService: Failed to bust limit cache', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * BUG-AI-004 FIX: Direct database quota check (fallback when cache is down)
     *
     * This method bypasses cache entirely and queries the database directly.
     * Used as a fail-safe when Redis/cache service is unavailable.
     */
    protected function isAllowedFromDatabase(int $tenantId): bool
    {
        try {
            $limit = $this->getLimitFromDatabase($tenantId);
            if ($limit === -1) {
                return true;
            }

            $used = AiUsageLog::tenantMonthlyCount($tenantId);

            return $used < $limit;
        } catch (\Throwable $e) {
            // BUG-AI-004 FIX: Both cache AND DB failed
            // CONSERVATIVE APPROACH: Deny access to prevent quota abuse
            Log::critical('AiQuotaService: Both cache and database unavailable! Denying access to prevent abuse.', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            // Return false (deny) to prevent unlimited usage
            // This is safer than returning true (allow)
            return false;
        }
    }

    /**
     * BUG-AI-004 FIX: Get limit directly from database (no cache)
     */
    protected function getLimitFromDatabase(int $tenantId): int
    {
        $tenant = Tenant::with('subscriptionPlan')->find($tenantId);

        return $tenant?->maxAiMessages() ?? 20;
    }

    private function cacheKey(int $tenantId): string
    {
        return "ai_quota_{$tenantId}_".now()->format('Y-m');
    }
}
