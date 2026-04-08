<?php

namespace App\Observers;

use App\Models\TenantApiSetting;
use Illuminate\Support\Facades\Cache;

/**
 * TenantApiSettingObserver - Ensure cache is cleared on any tenant setting change
 * 
 * BUG-SET-001 FIX: Guarantees cache invalidation regardless of how settings are updated
 */
class TenantApiSettingObserver
{
    /**
     * Handle the TenantApiSetting "created" event.
     */
    public function created(TenantApiSetting $setting): void
    {
        $this->clearCache($setting->tenant_id);
    }

    /**
     * Handle the TenantApiSetting "updated" event.
     */
    public function updated(TenantApiSetting $setting): void
    {
        // Only clear cache if value actually changed
        if ($setting->isDirty(['key', 'value', 'is_encrypted', 'tenant_id'])) {
            $this->clearCache($setting->tenant_id);

            // If tenant_id changed, also clear old tenant's cache
            if ($setting->isDirty('tenant_id')) {
                $this->clearCache($setting->getOriginal('tenant_id'));
            }
        }
    }

    /**
     * Handle the TenantApiSetting "deleted" event.
     */
    public function deleted(TenantApiSetting $setting): void
    {
        $this->clearCache($setting->tenant_id);
    }

    /**
     * Handle the TenantApiSetting "restored" event.
     */
    public function restored(TenantApiSetting $setting): void
    {
        $this->clearCache($setting->tenant_id);
    }

    /**
     * Handle the TenantApiSetting "forceDeleted" event.
     */
    public function forceDeleted(TenantApiSetting $setting): void
    {
        $this->clearCache($setting->tenant_id);
    }

    /**
     * Clear tenant settings cache
     */
    protected function clearCache(int $tenantId): void
    {
        $cacheKey = "tenant_api_settings_{$tenantId}";
        Cache::forget($cacheKey);
    }
}
