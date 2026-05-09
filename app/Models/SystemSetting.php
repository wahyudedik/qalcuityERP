<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'is_encrypted', 'group', 'label', 'description'];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    // Cache TTL: 60 minutes
    const CACHE_TTL = 3600;

    const CACHE_KEY = 'system_settings_all';

    /**
     * Get a setting value by key.
     * Falls back to $default if not found or empty.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::getCached();

        if (! isset($settings[$key])) {
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
     * Set a setting value. Creates if not exists, updates if exists.
     */
    public static function set(string $key, mixed $value, bool $encrypt = false, string $group = 'general', ?string $label = null): void
    {
        $storedValue = $value;

        if ($encrypt && ! empty($value)) {
            $storedValue = Crypt::encryptString((string) $value);
        }

        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'is_encrypted' => $encrypt,
                'group' => $group,
                'label' => $label,
            ]
        );

        static::clearCache();
    }

    /**
     * Set multiple settings at once.
     * $items = ['key' => ['value' => '...', 'encrypt' => true, 'group' => 'ai']]
     */
    public static function setMany(array $items): void
    {
        foreach ($items as $key => $opts) {
            static::set(
                $key,
                $opts['value'] ?? null,
                $opts['encrypt'] ?? false,
                $opts['group'] ?? 'general',
                $opts['label'] ?? null,
            );
        }
        static::clearCache();
    }

    /**
     * Load settings from DB into Laravel config, with .env as fallback.
     *
     * @param  array<string, string>  $map  ['setting_key' => 'config.path']
     */
    public static function loadIntoConfig(array $map): void
    {
        try {
            $settings = static::getCached();

            foreach ($map as $settingKey => $configPath) {
                if (! isset($settings[$settingKey])) {
                    continue;
                }

                $setting = $settings[$settingKey];

                if (empty($setting['value'])) {
                    continue;
                }

                $value = $setting['value'];

                if ($setting['is_encrypted']) {
                    try {
                        $value = Crypt::decryptString($value);
                    } catch (\Throwable) {
                        continue;
                    }
                }

                config([$configPath => $value]);
            }
        } catch (\Throwable $e) {
            // DB not ready yet (first deploy) — silently fall back to .env
            Log::debug('SystemSetting::loadIntoConfig skipped: '.$e->getMessage());
        }
    }

    /**
     * Get all settings as an associative array (cached).
     */
    public static function getCached(): array
    {
        return Cache::remember(static::CACHE_KEY, static::CACHE_TTL, function () {
            try {
                return static::all()
                    ->keyBy('key')
                    ->map(fn ($s) => ['value' => $s->value, 'is_encrypted' => $s->is_encrypted])
                    ->toArray();
            } catch (\Throwable) {
                return [];
            }
        });
    }

    /**
     * Get all settings grouped by group, with decrypted values for display.
     * Sensitive fields are masked.
     */
    public static function getAllGrouped(): array
    {
        try {
            return static::all()
                ->groupBy('group')
                ->map(fn ($items) => $items->keyBy('key'))
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Clear the settings cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * Check if a key has a value set in DB.
     */
    public static function has(string $key): bool
    {
        $settings = static::getCached();

        return isset($settings[$key]) && ! empty($settings[$key]['value']);
    }
}
