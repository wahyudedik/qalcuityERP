<?php

namespace App\Traits;

use App\Services\QueryCacheService;
use Illuminate\Support\Facades\Log;

/**
 * CacheableModel Trait
 *
 * Automatically invalidates cache when models are created, updated, or deleted.
 * Integrates with QueryCacheService for intelligent cache management.
 *
 * Usage:
 * class Product extends Model
 * {
 *     use CacheableModel;
 *
 *     protected $cacheModule = 'products';
 * }
 */
trait CacheableModel
{
    /**
     * Boot the cacheable trait
     */
    public static function bootCacheableModel(): void
    {
        static::created(function ($model) {
            $model->invalidateCache('created');
        });

        static::updated(function ($model) {
            $model->invalidateCache('updated');
        });

        static::deleted(function ($model) {
            $model->invalidateCache('deleted');
        });

        // Optional: Restore for soft deletes
        if (method_exists(static::class, 'bootSoftDeletes')) {
            static::restored(function ($model) {
                $model->invalidateCache('restored');
            });
        }
    }

    /**
     * Invalidate cache for this model
     */
    public function invalidateCache(string $event): void
    {
        try {
            $cacheService = app(QueryCacheService::class);
            $module = $this->getCacheModule();
            $tenantId = $this->getTenantId();

            if ($tenantId && $module) {
                $method = 'invalidate'.ucfirst($module);

                if (method_exists($cacheService, $method)) {
                    $cacheService->{$method}($tenantId, $this->id ?? null);
                } else {
                    // Fallback: invalidate all for this module
                    $cacheService->invalidateAll($tenantId);
                }

                Log::debug("Cache invalidated for {$module}", [
                    'event' => $event,
                    'model' => get_class($this),
                    'id' => $this->id ?? null,
                    'tenant_id' => $tenantId,
                ]);
            }
        } catch (\Exception $e) {
            // Don't break the application if cache fails
            Log::error('Cache invalidation failed: '.$e->getMessage(), [
                'model' => get_class($this),
                'event' => $event,
            ]);
        }
    }

    /**
     * Get cache module name
     */
    protected function getCacheModule(): ?string
    {
        return $this->cacheModule ?? null;
    }

    /**
     * Get tenant ID from model
     */
    protected function getTenantId(): ?int
    {
        // Check if model has tenant_id column
        if (property_exists($this, 'tenant_id')) {
            return $this->tenant_id;
        }

        // Check if model has tenant relationship
        if (method_exists($this, 'tenant')) {
            return $this->tenant_id ?? null;
        }

        return null;
    }
}
