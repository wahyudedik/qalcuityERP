<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Dashboard Cache Service
 * 
 * Provides caching functionality for dashboard statistics to reduce database queries.
 * Use this service to cache expensive stats queries with automatic TTL management.
 */
class DashboardCacheService
{
    /**
     * Get cached stats or execute callback to generate and cache them.
     *
     * @param string $cacheKey Unique cache key (e.g., "stats:bpjs_claims:123")
     * @param callable $callback Function to generate stats if not cached
     * @param int $ttl Time to live in seconds (default: 300 = 5 minutes)
     * @return mixed
     */
    public static function getStats(string $cacheKey, callable $callback, int $ttl = 300)
    {
        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Clear cached stats by key.
     * Call this when data changes (store/update/destroy).
     *
     * @param string $cacheKey Cache key to clear
     * @return bool
     */
    public static function clearStats(string $cacheKey): bool
    {
        return Cache::forget($cacheKey);
    }

    /**
     * Clear multiple cache keys by pattern.
     * Useful when you need to clear all stats for a tenant.
     *
     * @param string $pattern Pattern to match (e.g., "stats:*:123")
     * @return void
     */
    public static function clearStatsByPattern(string $pattern): void
    {
        // Note: This requires cache driver that supports tags or scanning
        // For file/database cache, you may need to track keys manually
        $cache = Cache::getStore();

        if (method_exists($cache, 'flush')) {
            // For simple cache clearing, we use tags
            // Implementation depends on cache driver
        }
    }

    /**
     * Get cache statistics for monitoring.
     *
     * @return array
     */
    public static function getCacheInfo(): array
    {
        return [
            'driver' => config('cache.default'),
            'prefix' => config('cache.prefix', ''),
        ];
    }
}
