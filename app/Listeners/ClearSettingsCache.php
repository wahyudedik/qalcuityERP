<?php

namespace App\Listeners;

use App\Events\SettingsUpdated;
use App\Services\SettingsCacheService;
use Illuminate\Support\Facades\Log;

/**
 * ClearSettingsCache Listener
 *
 * Listens for SettingsUpdated events and clears appropriate cache
 * based on the settings type and scope.
 */
class ClearSettingsCache
{
    protected SettingsCacheService $cacheService;

    /**
     * Create the event listener.
     */
    public function __construct(SettingsCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the event.
     */
    public function handle(SettingsUpdated $event): void
    {
        try {
            match ($event->type) {
                'tenant' => $this->handleTenantSettings($event),
                'module' => $this->handleModuleSettings($event),
                'system' => $this->handleSystemSettings($event),
                'api' => $this->handleApiSettings($event),
                default => Log::warning("Unknown settings type: {$event->type}"),
            };

            Log::info('Settings cache cleared successfully', [
                'type' => $event->type,
                'tenant_id' => $event->tenantId,
                'module' => $event->module,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear settings cache: '.$e->getMessage(), [
                'type' => $event->type,
                'exception' => $e,
            ]);
        }
    }

    /**
     * Handle tenant settings cache clearing
     */
    protected function handleTenantSettings(SettingsUpdated $event): void
    {
        if ($event->tenantId) {
            $this->cacheService->clearTenantCache($event->tenantId);
        } else {
            Log::warning('Tenant settings update without tenant_id');
        }
    }

    /**
     * Handle module settings cache clearing
     */
    protected function handleModuleSettings(SettingsUpdated $event): void
    {
        $this->cacheService->clearModuleCache($event->module);

        // If module belongs to specific tenant, also clear tenant cache
        if ($event->tenantId) {
            $this->cacheService->clearTenantCache($event->tenantId);
        }
    }

    /**
     * Handle system settings cache clearing
     */
    protected function handleSystemSettings(SettingsUpdated $event): void
    {
        $this->cacheService->clearSystemCache();

        // System settings affect all tenants, increment version
        $this->cacheService->incrementVersion();
    }

    /**
     * Handle API settings cache clearing
     */
    protected function handleApiSettings(SettingsUpdated $event): void
    {
        $this->cacheService->clearApiCache($event->tenantId);

        // If tenant-specific, also clear tenant cache
        if ($event->tenantId) {
            $this->cacheService->clearTenantCache($event->tenantId);
        }
    }
}
