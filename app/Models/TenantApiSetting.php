<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class TenantApiSetting extends Model
{
    use BelongsToTenant;
    protected $fillable = ['tenant_id', 'key', 'value', 'is_encrypted', 'group', 'label'];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    const CACHE_TTL = 1800; // 30 minutes

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get a setting value for a specific tenant.
     * Falls back to $default if not set.
     */
    public static function get(int $tenantId, string $key, mixed $default = null): mixed
    {
        $settings = static::getCached($tenantId);

        if (!isset($settings[$key])) {
            return $default;
        }

        $setting = $settings[$key];

        if (empty($setting['value'])) {
            return $default;
        }

        if ($setting['is_encrypted']) {
            try {
                return Crypt::decryptString($setting['value']);
            } catch (\Throwable) {
                return $default;
            }
        }

        return $setting['value'];
    }

    /**
     * Set or update a setting for a specific tenant.
     */
    public static function set(int $tenantId, string $key, mixed $value, bool $encrypt = false, string $group = 'general', ?string $label = null): void
    {
        $storedValue = $value;

        if ($encrypt && !empty($value)) {
            $storedValue = Crypt::encryptString((string) $value);
        }

        static::updateOrCreate(
            ['tenant_id' => $tenantId, 'key' => $key],
            [
                'value' => $storedValue,
                'is_encrypted' => $encrypt,
                'group' => $group,
                'label' => $label,
            ]
        );

        static::clearCache($tenantId);
    }

    /**
     * Set multiple settings at once for a tenant.
     */
    public static function setMany(int $tenantId, array $items): void
    {
        foreach ($items as $key => $opts) {
            static::set(
                $tenantId,
                $key,
                $opts['value'] ?? null,
                $opts['encrypt'] ?? false,
                $opts['group'] ?? 'general',
                $opts['label'] ?? null,
            );
        }
        static::clearCache($tenantId);
    }

    /**
     * Check if a setting is configured for a tenant.
     */
    public static function has(int $tenantId, string $key): bool
    {
        $settings = static::getCached($tenantId);
        return isset($settings[$key]) && !empty($settings[$key]['value']);
    }

    /**
     * Get all settings for a tenant (cached).
     */
    public static function getCached(int $tenantId): array
    {
        $cacheKey = "tenant_api_settings_{$tenantId}";

        return Cache::remember($cacheKey, static::CACHE_TTL, function () use ($tenantId) {
            try {
                return static::where('tenant_id', $tenantId)
                    ->get()
                    ->keyBy('key')
                    ->map(fn($s) => ['value' => $s->value, 'is_encrypted' => $s->is_encrypted, 'group' => $s->group])
                    ->toArray();
            } catch (\Throwable) {
                return [];
            }
        });
    }

    /**
     * Get all settings for a tenant grouped by group (for UI display).
     */
    public static function getAllForTenant(int $tenantId): array
    {
        try {
            return static::where('tenant_id', $tenantId)
                ->get()
                ->groupBy('group')
                ->map(fn($items) => $items->keyBy('key'))
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Clear cache for a specific tenant.
     */
    public static function clearCache(int $tenantId): void
    {
        Cache::forget("tenant_api_settings_{$tenantId}");
    }

    /**
     * Delete a setting for a tenant.
     */
    public static function remove(int $tenantId, string $key): void
    {
        static::where('tenant_id', $tenantId)->where('key', $key)->delete();
        static::clearCache($tenantId);
    }
}
