<?php

namespace App\Observers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

/**
 * SystemSettingObserver - Ensure cache is cleared on any setting change
 *
 * BUG-SET-001 FIX: Guarantees cache invalidation regardless of how settings are updated
 */
class SystemSettingObserver
{
    /**
     * Handle the SystemSetting "created" event.
     */
    public function created(SystemSetting $setting): void
    {
        $this->clearCache();
    }

    /**
     * Handle the SystemSetting "updated" event.
     */
    public function updated(SystemSetting $setting): void
    {
        // Only clear cache if value actually changed
        if ($setting->isDirty(['key', 'value', 'is_encrypted'])) {
            $this->clearCache();
        }
    }

    /**
     * Handle the SystemSetting "deleted" event.
     */
    public function deleted(SystemSetting $setting): void
    {
        $this->clearCache();
    }

    /**
     * Handle the SystemSetting "restored" event.
     */
    public function restored(SystemSetting $setting): void
    {
        $this->clearCache();
    }

    /**
     * Handle the SystemSetting "forceDeleted" event.
     */
    public function forceDeleted(SystemSetting $setting): void
    {
        $this->clearCache();
    }

    /**
     * Clear system settings cache
     */
    protected function clearCache(): void
    {
        Cache::forget(SystemSetting::CACHE_KEY);

        // Also clear any config that was loaded from settings
        // This ensures fresh values on next request
        if (function_exists('opcache_reset')) {
            // Optional: Clear OPcache for production environments
            // opcache_reset();
        }
    }
}
