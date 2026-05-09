<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * CachableModel Trait
 *
 * Provides automatic query caching for Eloquent models.
 * Automatically clears cache when model is saved or deleted.
 *
 * Usage:
 * class Product extends Model {
 *     use CachableModel;
 *
 *     protected $cacheTTL = 3600; // 1 hour
 * }
 *
 * // Cache a query
 * $products = Product::cacheQuery('active_products', now()->addHour(), function() {
 *     return Product::where('is_active', true)->get();
 * });
 */
trait CachableModel
{
    /**
     * Cache TTL in seconds (override in model).
     *
     * @var int
     */
    protected $cacheTTL = 3600;

    /**
     * Boot the trait and register model events.
     */
    protected static function bootCachableModel(): void
    {
        // Clear cache when model is created/updated
        static::saved(function ($model) {
            $model->clearCache();
        });

        // Clear cache when model is deleted
        static::deleted(function ($model) {
            $model->clearCache();
        });
    }

    /**
     * Clear all cache keys associated with this model.
     */
    public function clearCache(): void
    {
        $tags = $this->getCacheTags();

        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags($tags)->flush();
        } else {
            // Fallback for cache drivers that don't support tags
            foreach ($tags as $tag) {
                Cache::forget($tag);
            }
        }
    }

    /**
     * Get cache tags for this model.
     */
    protected function getCacheTags(): array
    {
        $table = $this->getTable();
        $tenantId = $this->tenant_id ?? null;

        $tags = [
            "model:{$table}",
            "model:{$table}:all",
        ];

        if ($tenantId) {
            $tags[] = "model:{$table}:tenant:{$tenantId}";
        }

        return $tags;
    }

    /**
     * Cache a query result.
     *
     * @param  string  $key  Cache key
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl  Cache TTL
     * @param  callable  $callback  Query callback
     * @param  array  $tags  Additional cache tags
     */
    public static function cacheQuery(string $key, $ttl, callable $callback, array $tags = []): mixed
    {
        $instance = new static;
        $table = $instance->getTable();
        $fullKey = "model:{$table}:{$key}";
        $allTags = array_merge(["model:{$table}"], $tags);

        // Use tags if supported
        if (method_exists(Cache::getStore(), 'tags') && ! empty($allTags)) {
            return Cache::tags($allTags)->remember($fullKey, $ttl, $callback);
        }

        return Cache::remember($fullKey, $ttl, $callback);
    }

    /**
     * Cache query with tenant isolation.
     *
     * @param  int  $tenantId  Tenant ID
     * @param  string  $key  Cache key
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl  Cache TTL
     * @param  callable  $callback  Query callback
     */
    public static function cacheTenantQuery(int $tenantId, string $key, $ttl, callable $callback): mixed
    {
        $instance = new static;
        $table = $instance->getTable();
        $fullKey = "model:{$table}:tenant:{$tenantId}:{$key}";
        $tags = ["model:{$table}", "model:{$table}:tenant:{$tenantId}"];

        if (method_exists(Cache::getStore(), 'tags')) {
            return Cache::tags($tags)->remember($fullKey, $ttl, $callback);
        }

        return Cache::remember($fullKey, $ttl, $callback);
    }

    /**
     * Forget a specific cache key.
     */
    public static function forgetCache(string $key): bool
    {
        $instance = new static;
        $table = $instance->getTable();
        $fullKey = "model:{$table}:{$key}";

        return Cache::forget($fullKey);
    }

    /**
     * Get cached query result without callback (returns null if not cached).
     */
    public static function getCached(string $key): mixed
    {
        $instance = new static;
        $table = $instance->getTable();
        $fullKey = "model:{$table}:{$key}";

        return Cache::get($fullKey);
    }
}
