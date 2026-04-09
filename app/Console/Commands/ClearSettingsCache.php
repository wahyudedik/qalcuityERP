<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\SettingsCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearSettingsCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:clear-cache
                            {--system : Clear only system settings cache}
                            {--tenant= : Clear specific tenant settings cache (ID)}
                            {--all-tenants : Clear all tenant settings cache}
                            {--modules : Clear module settings cache}
                            {--api : Clear API settings cache}
                            {--all : Clear ALL settings cache and increment version}
                            {--version : Show current cache version}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear settings cache with advanced options (system, tenant, module, API)';

    protected SettingsCacheService $cacheService;

    /**
     * Execute the console command.
     */
    public function handle(SettingsCacheService $cacheService): int
    {
        $this->cacheService = $cacheService;
        $cleared = 0;

        // Show version if requested
        if ($this->option('version')) {
            $version = $this->cacheService->getVersion();
            $this->info("📌 Current cache version: {$version}");
            return Command::SUCCESS;
        }

        // Clear ALL settings cache (nuclear option)
        if ($this->option('all')) {
            $this->warn('⚠️  Clearing ALL settings cache...');
            $this->cacheService->clearAll();
            $this->info('✅ All settings cache cleared (version incremented)');
            return Command::SUCCESS;
        }

        // Clear system settings cache
        if ($this->option('system') || (!$this->option('tenant') && !$this->option('all-tenants') && !$this->option('modules') && !$this->option('api'))) {
            $this->cacheService->clearSystemCache();
            $this->info('✅ System settings cache cleared');
            $cleared++;
        }

        // Clear module settings cache
        if ($this->option('modules')) {
            $this->cacheService->clearModuleCache();
            $this->info('✅ Module settings cache cleared (all modules)');
            $cleared++;
        }

        // Clear API settings cache
        if ($this->option('api')) {
            $this->cacheService->clearApiCache();
            $this->info('✅ API settings cache cleared (all tenants)');
            $cleared++;
        }

        // Clear specific tenant cache
        if ($tenantId = $this->option('tenant')) {
            $this->cacheService->clearTenantCache((int) $tenantId);
            $this->info("✅ Tenant {$tenantId} settings cache cleared");
            $cleared++;
        }

        // Clear all tenants cache
        if ($this->option('all-tenants')) {
            $tenantCount = 0;
            Tenant::chunk(100, function ($tenants) use (&$tenantCount) {
                foreach ($tenants as $tenant) {
                    $this->cacheService->clearTenantCache($tenant->id);
                    $tenantCount++;
                }
            });
            $this->info("✅ All {$tenantCount} tenant settings caches cleared");
            $cleared += $tenantCount;
        }

        if ($cleared === 0 && !$this->option('all')) {
            $this->warn('⚠️  No cache cleared. Use options: --system, --tenant={id}, --all-tenants, --modules, --api, or --all');
        } else {
            $newVersion = $this->cacheService->getVersion();
            $this->info("🎉 Total: {$cleared} cache group(s) cleared | Version: {$newVersion}");
        }

        return Command::SUCCESS;
    }
}
