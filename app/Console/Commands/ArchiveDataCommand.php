<?php

namespace App\Console\Commands;

use App\Services\DataArchivalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ArchiveDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'archive:run {--type= : Specific type to archive}
                            {--tenant= : Archive only for specific tenant}
                            {--days= : Override retention days}
                            {--dry-run : Show what would be archived without deleting}
                            {--stats : Show archival statistics only}';

    /**
     * The console command description.
     */
    protected $description = 'Archive old data based on retention policies. Supports per-type and per-tenant archival.';

    /**
     * Execute the console command.
     */
    public function handle(DataArchivalService $archivalService): int
    {
        $tenantId = $this->option('tenant') ? (int) $this->option('tenant') : null;
        $dryRun = $this->option('dry-run');
        $showStats = $this->option('stats');

        // Show statistics
        if ($showStats) {
            return $this->showStatistics($archivalService, $tenantId);
        }

        // Archive specific type or all types
        $type = $this->option('type');

        if ($type) {
            return $this->archiveSpecificType($archivalService, $type, $tenantId, $dryRun);
        }

        return $this->archiveAllTypes($archivalService, $tenantId, $dryRun);
    }

    /**
     * Archive all configured types
     */
    protected function archiveAllTypes(
        DataArchivalService $archivalService,
        ?int $tenantId,
        bool $dryRun
    ): int {
        $this->info("Starting data archival process...");
        $this->line("Tenant: " . ($tenantId ?? 'All'));
        $this->line("Mode: " . ($dryRun ? 'DRY RUN' : 'LIVE'));
        $this->newLine();

        $results = $archivalService->archiveAll($tenantId, $dryRun);

        $totalArchived = 0;
        $failed = 0;

        foreach ($results as $type => $result) {
            if ($result['success'] ?? false) {
                $count = $result['archived_count'] ?? 0;
                $wouldCount = $result['would_archive_count'] ?? 0;

                if ($dryRun) {
                    $this->line("✓ <comment>{$type}</comment>: Would archive <info>{$wouldCount}</info> records");
                } else {
                    $this->line("✓ <comment>{$type}</comment>: Archived <info>{$count}</info> records");
                    $totalArchived += $count;
                }
            } else {
                $this->error("✗ {$type}: " . ($result['error'] ?? 'Unknown error'));
                $failed++;
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info("Dry run completed. No data was deleted.");
        } else {
            $this->info("Archival completed successfully!");
            $this->line("Total archived: <info>{$totalArchived}</info> records");

            if ($failed > 0) {
                $this->warn("Failed types: {$failed}");
            }
        }

        Log::info("Data archival completed", [
            'tenant_id' => $tenantId,
            'dry_run' => $dryRun,
            'total_archived' => $totalArchived,
            'failed_types' => $failed,
        ]);

        return self::SUCCESS;
    }

    /**
     * Archive specific type
     */
    protected function archiveSpecificType(
        DataArchivalService $archivalService,
        string $type,
        ?int $tenantId,
        bool $dryRun
    ): int {
        $this->info("Archiving {$type}...");

        try {
            $result = $archivalService->archiveType($type, $tenantId, $dryRun);

            if ($result['success']) {
                if ($dryRun) {
                    $count = $result['would_archive_count'] ?? 0;
                    $this->info("Would archive {$count} {$type} records");
                } else {
                    $count = $result['archived_count'] ?? 0;
                    $this->info("Successfully archived {$count} {$type} records");
                }
                return self::SUCCESS;
            } else {
                $this->error("Failed: " . ($result['message'] ?? 'Unknown error'));
                return self::FAILURE;
            }

        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
            $this->line("Available types: " . implode(', ', $this->getAvailableTypes()));
            return self::INVALID;
        }
    }

    /**
     * Show archival statistics
     */
    protected function showStatistics(
        DataArchivalService $archivalService,
        ?int $tenantId
    ): int {
        $this->info("Data Archival Statistics");
        $this->line("Tenant: " . ($tenantId ?? 'All'));
        $this->newLine();

        $stats = $archivalService->getStatistics($tenantId);

        $headers = ['Type', 'Ready for Archival', 'Retention Days', 'Cutoff Date'];
        $rows = [];

        foreach ($stats as $type => $data) {
            $rows[] = [
                $type,
                $data['ready_for_archival'],
                $data['retention_days'],
                $data['cutoff_date'],
            ];
        }

        $this->table($headers, $rows);

        $totalReady = array_sum(array_column($stats, 'ready_for_archival'));
        $this->newLine();
        $this->info("Total records ready for archival: <info>{$totalReady}</info>");

        return self::SUCCESS;
    }

    /**
     * Get list of available archival types
     */
    protected function getAvailableTypes(): array
    {
        // These should match the keys in DataArchivalService::$archivalConfigs
        return [
            'activity_logs',
            'ai_usage_logs',
            'anomaly_alerts',
            'chat_messages',
            'chat_sessions',
            'notifications',
            'error_logs',
            'harvest_logs',
            'livestock_health_records',
            'stock_movements',
        ];
    }
}
