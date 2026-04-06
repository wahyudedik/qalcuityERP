<?php

namespace App\Console\Commands;

use App\Services\AutomatedBackupService;
use Illuminate\Console\Command;

class CreateDailyBackup extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:create {--type=daily : Backup type (daily, weekly, monthly)}';

    /**
     * The console command description.
     */
    protected $description = 'Create automated backup for all tenants';

    protected $backupService;

    public function __construct(AutomatedBackupService $backupService)
    {
        parent::__construct();
        $this->backupService = $backupService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automated backup...');

        $type = $this->option('type');

        // Get all tenants
        $tenants = \App\Models\Tenant::all();

        $this->info("Found {$tenants->count()} tenants to backup");

        $successCount = 0;
        $failCount = 0;

        foreach ($tenants as $tenant) {
            try {
                // Switch to tenant context
                auth()->loginUsingId($tenant->users()->first()?->id);

                $result = $this->backupService->createBackup($type);

                if ($result['success']) {
                    $this->info("✓ Tenant {$tenant->id}: Backup created successfully ({$result['records_count']} records)");
                    $successCount++;
                } else {
                    $this->error("✗ Tenant {$tenant->id}: {$result['error']}");
                    $failCount++;
                }

            } catch (\Throwable $e) {
                $this->error("✗ Tenant {$tenant->id}: " . $e->getMessage());
                $failCount++;
            }
        }

        $this->info("\nBackup Summary:");
        $this->info("Success: {$successCount}");
        $this->error("Failed: {$failCount}");
        $this->info("Total: " . ($successCount + $failCount));

        return 0;
    }
}
