<?php

namespace App\Console\Commands;

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
                            {--all-tenants : Clear all tenant settings cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear settings cache (system and/or tenant-specific)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cleared = 0;

        // Clear system settings cache
        if ($this->option('system') || !$this->option('tenant') && !$this->option('all-tenants')) {
            Cache::forget(\App\Models\SystemSetting::CACHE_KEY);
            $this->info('✅ System settings cache cleared');
            $cleared++;
        }

        // Clear specific tenant cache
        if ($tenantId = $this->option('tenant')) {
            Cache::forget("tenant_api_settings_{$tenantId}");
            $this->info("✅ Tenant {$tenantId} settings cache cleared");
            $cleared++;
        }

        // Clear all tenants cache
        if ($this->option('all-tenants')) {
            $tenants = \App\Models\Tenant::pluck('id');
            foreach ($tenants as $tid) {
                Cache::forget("tenant_api_settings_{$tid}");
                $cleared++;
            }
            $this->info("✅ All {$cleared} tenant settings caches cleared");
        }

        if ($cleared === 0) {
            $this->warn('⚠️  No cache cleared. Use --system, --tenant={id}, or --all-tenants');
        } else {
            $this->info("🎉 Total: {$cleared} cache(s) cleared");
        }

        return Command::SUCCESS;
    }
}
