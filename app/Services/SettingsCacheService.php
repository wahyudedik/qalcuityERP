<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * SettingsCacheService - Centralized settings cache management
 * 
 * Handles cache invalidation, versioning, and multi-tenant isolation
 * for all types of settings (tenant API settings, module settings, system settings).
 */
class SettingsCacheService
{
    /**
     * Cache key prefix for settings
     */
    const CACHE_PREFIX = 'settings_';

    /**
     * Cache version key - increment this to invalidate all caches globally
     */
    const CACHE_VERSION_KEY = 'settings_cache_version';

    /**
     * Default cache TTL in seconds (30 minutes)
     */
    const DEFAULT_TTL = 1800;

    /**
     * Cache tags for settings
     */
    const TAG_TENANT_SETTINGS = 'tenant_settings';
    const TAG_MODULE_SETTINGS = 'module_settings';
    const TAG_SYSTEM_SETTINGS = 'system_settings';
    const TAG_API_SETTINGS = 'api_settings';

    /**
     * Get cached settings with automatic versioning
     * 
     * @param string $key Cache key (without prefix)
     * @param callable $callback Function to execute on cache miss
     * @param int|null $ttl Custom TTL in seconds (null = use default)
     * @param array $tags Cache tags for group invalidation
     * @return mixed
     */
    public function get(string $key, callable $callback, ?int $ttl = null, array $tags = []): mixed
    {
        $cacheKey = $this->buildKey($key);
        $ttl = $ttl ?? self::DEFAULT_TTL;

        try {
            // Use tags if available (requires cache driver that supports tags)
            if (!empty($tags) && $this->supportsTags()) {
                return Cache::tags($tags)->remember($cacheKey, now()->addSeconds($ttl), $callback);
            }

            // Fallback to simple remember with version key
            return Cache::remember($cacheKey, now()->addSeconds($ttl), function () use ($key, $callback) {
                // Include cache version in the value to detect stale data
                $value = $callback();
                return [
                    'version' => $this->getVersion(),
                    'data' => $value,
                    'cached_at' => now()->toISOString(),
                ];
            });
        } catch (\Exception $e) {
            Log::warning("SettingsCacheService::get failed for key {$key}: " . $e->getMessage());
            // Return callback result directly on cache failure
            return $callback();
        }
    }

    /**
     * Clear cache for a specific key
     * 
     * @param string $key Cache key (without prefix)
     * @return bool
     */
    public function forget(string $key): bool
    {
        $cacheKey = $this->buildKey($key);

        try {
            return Cache::forget($cacheKey);
        } catch (\Exception $e) {
            Log::warning("SettingsCacheService::forget failed for key {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all tenant settings cache
     * 
     * @param int $tenantId Tenant ID
     * @return void
     */
    public function clearTenantCache(int $tenantId): void
    {
        $tags = [
            self::TAG_TENANT_SETTINGS,
            "tenant_{$tenantId}",
        ];

        $this->clearByTags($tags);

        // Also clear specific known cache keys
        $keysToClear = [
            "tenant_api_settings_{$tenantId}",
            "tenant_modules_{$tenantId}",
            "tenant_permissions_{$tenantId}",
        ];

        foreach ($keysToClear as $key) {
            $this->forget($key);
        }

        Log::info("Settings cache cleared for tenant {$tenantId}");
    }

    /**
     * Clear all module settings cache
     * 
     * @param string|null $module Specific module (null = all modules)
     * @return void
     */
    public function clearModuleCache(?string $module = null): void
    {
        $tags = [self::TAG_MODULE_SETTINGS];

        if ($module) {
            $tags[] = "module_{$module}";
            $this->forget("module_settings_{$module}");
        }

        $this->clearByTags($tags);

        Log::info("Module settings cache cleared" . ($module ? " for module: {$module}" : " (all modules)"));
    }

    /**
     * Clear all API settings cache
     * 
     * @param int|null $tenantId Specific tenant (null = all tenants)
     * @return void
     */
    public function clearApiCache(?int $tenantId = null): void
    {
        $tags = [self::TAG_API_SETTINGS];

        if ($tenantId) {
            $tags[] = "tenant_{$tenantId}";
            $this->forget("tenant_api_settings_{$tenantId}");
        }

        $this->clearByTags($tags);

        Log::info("API settings cache cleared" . ($tenantId ? " for tenant: {$tenantId}" : " (all tenants)"));
    }

    /**
     * Clear system settings cache
     * 
     * @return void
     */
    public function clearSystemCache(): void
    {
        $tags = [self::TAG_SYSTEM_SETTINGS];

        $this->clearByTags($tags);
        $this->forget('system_settings');

        Log::info("System settings cache cleared");
    }

    /**
     * Clear ALL settings cache (nuclear option)
     * 
     * @return void
     */
    public function clearAll(): void
    {
        // Increment cache version to invalidate all caches globally
        $this->incrementVersion();

        // Clear by tags
        $this->clearByTags([
            self::TAG_TENANT_SETTINGS,
            self::TAG_MODULE_SETTINGS,
            self::TAG_SYSTEM_SETTINGS,
            self::TAG_API_SETTINGS,
        ]);

        Log::warning("ALL settings cache cleared (version incremented)");
    }

    /**
     * Get current cache version
     * 
     * @return int
     */
    public function getVersion(): int
    {
        return (int) Cache::get(self::CACHE_VERSION_KEY, 1);
    }

    /**
     * Increment cache version (invalidates all versioned caches)
     * 
     * @return int New version number
     */
    public function incrementVersion(): int
    {
        $currentVersion = (int) Cache::get(self::CACHE_VERSION_KEY, 0);
        $newVersion = $currentVersion + 1;
        Cache::put(self::CACHE_VERSION_KEY, $newVersion);

        Log::info("Settings cache version incremented to: {$newVersion}");
        return $newVersion;
    }

    /**
     * Check if cache data is stale (version mismatch)
     * 
     * @param mixed $cachedData Cached data with version info
     * @return bool
     */
    public function isStale(mixed $cachedData): bool
    {
        if (!is_array($cachedData) || !isset($cachedData['version'])) {
            return true;
        }

        return $cachedData['version'] !== $this->getVersion();
    }

    /**
     * Build cache key with prefix
     * 
     * @param string $key
     * @return string
     */
    protected function buildKey(string $key): string
    {
        return self::CACHE_PREFIX . $key;
    }

    /**
     * Clear cache by tags (if supported)
     * 
     * @param array $tags
     * @return void
     */
    protected function clearByTags(array $tags): void
    {
        if (!$this->supportsTags() || empty($tags)) {
            return;
        }

        try {
            Cache::tags($tags)->flush();
        } catch (\Exception $e) {
            Log::warning("SettingsCacheService::clearByTags failed: " . $e->getMessage());
        }
    }

    /**
     * Check if cache driver supports tags
     * 
     * @return bool
     */
    protected function supportsTags(): bool
    {
        $driver = config('cache.default');
        $supported = ['redis', 'memcached', 'dynamodb'];

        return in_array($driver, $supported);
    }
}
