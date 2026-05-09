<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WidgetDataCache extends Model
{
    use BelongsToTenant;

    protected $table = 'widget_data_cache';

    protected $fillable = [
        'tenant_id',
        'widget_type',
        'cache_key',
        'data',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * Cek apakah cache sudah kadaluarsa.
     */
    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Cek apakah cache masih valid (belum kadaluarsa).
     */
    public function isValid(): bool
    {
        return ! $this->isExpired();
    }

    /**
     * Scope untuk mengambil cache yang masih valid.
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope untuk mengambil cache yang sudah kadaluarsa.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Scope untuk filter berdasarkan widget type.
     */
    public function scopeForWidgetType(Builder $query, string $widgetType): Builder
    {
        return $query->where('widget_type', $widgetType);
    }

    /**
     * Scope untuk filter berdasarkan cache key.
     */
    public function scopeForCacheKey(Builder $query, string $cacheKey): Builder
    {
        return $query->where('cache_key', $cacheKey);
    }

    /**
     * Ambil data cache berdasarkan cache key jika masih valid.
     */
    public static function getValidCache(string $cacheKey): ?array
    {
        $cache = static::forCacheKey($cacheKey)->valid()->first();

        return $cache?->data;
    }

    /**
     * Simpan atau update cache data.
     */
    public static function putCache(
        string $widgetType,
        string $cacheKey,
        array $data,
        ?\DateTimeInterface $expiresAt = null
    ): self {
        return static::updateOrCreate(
            ['cache_key' => $cacheKey],
            [
                'widget_type' => $widgetType,
                'data' => $data,
                'expires_at' => $expiresAt,
            ]
        );
    }

    /**
     * Hapus cache yang sudah kadaluarsa.
     */
    public static function clearExpiredCache(): int
    {
        return static::expired()->delete();
    }

    /**
     * Hapus semua cache untuk widget type tertentu.
     */
    public static function clearWidgetTypeCache(string $widgetType): int
    {
        return static::forWidgetType($widgetType)->delete();
    }
}
