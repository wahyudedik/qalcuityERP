<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheableModel
{
    /**
     * Boot the cacheable trait
     */
    public static function bootCacheableModel()
    {
        // Invalidate cache on model changes
        static::created(fn($model) => $model->invalidateCache());
        static::updated(fn($model) => $model->invalidateCache());
        static::deleted(fn($model) => $model->invalidateCache());
    }

    /**
     * Get cached query results
     * 
     * @param string $cacheKey
     * @param int $ttl Minutes
     * @return mixed
     */
    public static function cached($cacheKey = null, $ttl = 60)
    {
        $key = $cacheKey ?? 'query_' . md5(debug_backtrace()[0]['class'] . '_' . microtime());
        $tags = static::getCacheTags();

        return Cache::tags($tags)->remember($key, now()->addMinutes($ttl), function () {
            return static::query()->get();
        });
    }

    /**
     * Get cache tags for this model
     * 
     * @return array
     */
    protected static function getCacheTags(): array
    {
        $tenantId = request()->get('_api_tenant_id', auth()->user()?->tenant_id ?? 0);

        return [
            'tenant_' . $tenantId,
            'module_' . static::getModelModule(),
            'model_' . static::getModelName(),
        ];
    }

    /**
     * Invalidate all cache for this model
     */
    public function invalidateCache()
    {
        $tags = static::getCacheTags();
        foreach ($tags as $tag) {
            Cache::tags([$tag])->flush();
        }
    }

    /**
     * Get model name for caching
     * 
     * @return string
     */
    protected static function getModelName(): string
    {
        return class_basename(static::class);
    }

    /**
     * Get module name for caching
     * 
     * @return string
     */
    protected static function getModelModule(): string
    {
        $namespace = static::class;
        if (preg_match('/\\\\([A-Za-z]+)\\\\/', $namespace, $matches)) {
            return strtolower($matches[1]);
        }
        return 'general';
    }
}
