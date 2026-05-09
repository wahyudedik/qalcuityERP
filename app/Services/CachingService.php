<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CachingService
{
    /**
     * Get cached data with tenant isolation
     *
     * @param  string  $key  Cache key
     * @param  callable  $callback  Function to execute if cache misses
     * @param  int  $ttl  Time to live in minutes
     * @param  array  $tags  Cache tags for invalidation
     * @return mixed
     */
    public function remember(string $key, callable $callback, int $ttl = 60, array $tags = [])
    {
        $cacheKey = $this->buildKey($key);

        if (empty($tags)) {
            return Cache::remember($cacheKey, now()->addMinutes($ttl), $callback);
        }

        return Cache::tags($tags)->remember($cacheKey, now()->addMinutes($ttl), $callback);
    }

    /**
     * Invalidate cache by tags
     */
    public function invalidate(array $tags = [])
    {
        foreach ($tags as $tag) {
            Cache::tags([$tag])->flush();
        }
    }

    /**
     * Invalidate all tenant-specific cache
     */
    public function invalidateTenant(int $tenantId)
    {
        Cache::tags(["tenant_{$tenantId}"])->flush();
    }

    /**
     * Invalidate module-specific cache
     */
    public function invalidateModule(string $module)
    {
        Cache::tags(["module_{$module}"])->flush();
    }

    /**
     * Build tenant-scoped cache key
     */
    protected function buildKey(string $key): string
    {
        $tenantId = request()->get('_api_tenant_id', auth()->user()?->tenant_id ?? 0);

        return "tenant_{$tenantId}:{$key}";
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        return [
            'driver' => config('cache.default'),
            'prefix' => config('cache.prefix', 'laravel'),
            'stores' => array_keys(config('cache.stores', [])),
        ];
    }
}
