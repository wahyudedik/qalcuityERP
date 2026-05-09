<?php

namespace App\Console\Commands;

use App\Services\OrphanedDataCleanupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupOrphanedDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cleanup:orphaned {--type= : Specific orphan type to clean}
                            {--tenant= : Clean only for specific tenant}
                            {--dry-run : Show what would be deleted without deleting}
                            {--scan-only : Only scan and report, don\'t delete}
                            {--report : Generate detailed orphan report}';

    /**
     * The console command description.
     */
    protected $description = 'Scan and clean up orphaned records that reference non-existent parent records';

    /**
     * Execute the console command.
     */
    public function handle(OrphanedDataCleanupService $cleanupService): int
    {
        $tenantId = $this->option('tenant') ? (int) $this->option('tenant') : null;
        $dryRun = $this->option('dry-run');
        $scanOnly = $this->option('scan-only');
        $generateReport = $this->option('report');

        // Generate detailed report
        if ($generateReport) {
            return $this->generateReport($cleanupService, $tenantId);
        }

        // Scan only mode
        if ($scanOnly) {
            return $this->scanOnly($cleanupService, $tenantId);
        }

        // Clean specific type or all types
        $type = $this->option('type');

        if ($type) {
            return $this->cleanSpecificType($cleanupService, $type, $tenantId, $dryRun);
        }

        return $this->cleanAllTypes($cleanupService, $tenantId, $dryRun);
    }

    /**
     * Scan and show results without deleting
     */
    protected function scanOnly(
        OrphanedDataCleanupService $cleanupService,
        ?int $tenantId
    ): int {
        $this->info('Scanning for orphaned records...');
        $this->line('Tenant: '.($tenantId ?? 'All'));
        $this->newLine();

        $results = $cleanupService->scanAll($tenantId);

        $totalOrphans = 0;
        $typesWithOrphans = 0;

        $headers = ['Type', 'Table', 'Foreign Key', 'Reference Table', 'Orphan Count'];
        $rows = [];

        foreach ($results as $type => $result) {
            if ($result['success'] ?? false) {
                $count = $result['orphan_count'] ?? 0;
                $totalOrphans += $count;

                if ($count > 0) {
                    $typesWithOrphans++;
                    $rows[] = [
                        $type,
                        $result['table'],
                        $result['foreign_key'],
                        $result['reference_table'],
                        "<error>{$count}</error>",
                    ];
                }
            } else {
                $this->error("✗ {$type}: ".($result['error'] ?? 'Unknown error'));
            }
        }

        if (empty($rows)) {
            $this->info('✓ No orphaned records found!');

            return self::SUCCESS;
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->warn("Total orphaned records found: {$totalOrphans}");
        $this->warn("Types with orphans: {$typesWithOrphans}");
        $this->newLine();
        $this->line('Use <info>php artisan cleanup:orphaned --dry-run</info> to preview deletion');

        Log::info('Orphan scan completed', [
            'tenant_id' => $tenantId,
            'total_orphans' => $totalOrphans,
            'types_with_orphans' => $typesWithOrphans,
        ]);

        return self::SUCCESS;
    }

    /**
     * Clean all orphan types
     */
    protected function cleanAllTypes(
        OrphanedDataCleanupService $cleanupService,
        ?int $tenantId,
        bool $dryRun
    ): int {
        $this->info('Starting orphan cleanup...');
        $this->line('Tenant: '.($tenantId ?? 'All'));
        $this->line('Mode: '.($dryRun ? 'DRY RUN' : 'LIVE'));
        $this->newLine();

        $results = $cleanupService->cleanupAll($tenantId, $dryRun);

        $totalDeleted = $results['total_deleted'] ?? 0;
        $failed = 0;

        foreach ($results['types'] ?? [] as $type => $result) {
            if ($result['success'] ?? false) {
                $count = $result['deleted_count'] ?? 0;
                $wouldCount = $result['would_delete_count'] ?? 0;

                if ($dryRun) {
                    $this->line("✓ <comment>{$type}</comment>: Would delete <info>{$wouldCount}</info> orphans");
                } else {
                    if ($count > 0) {
                        $this->line("✓ <comment>{$type}</comment>: Deleted <info>{$count}</info> orphans");
                    } else {
                        $this->line("✓ <comment>{$type}</comment>: No orphans found");
                    }
                }
            } else {
                $this->error("✗ {$type}: ".($result['error'] ?? 'Unknown error'));
                $failed++;
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info('Dry run completed. No data was deleted.');
            $this->line("Would delete <comment>{$totalDeleted}</comment> orphaned records total");
        } else {
            $this->info('Orphan cleanup completed successfully!');
            $this->line("Total deleted: <info>{$totalDeleted}</info> orphaned records");

            if ($failed > 0) {
                $this->warn("Failed types: {$failed}");
            }
        }

        Log::info('Orphan cleanup completed', [
            'tenant_id' => $tenantId,
            'dry_run' => $dryRun,
            'total_deleted' => $totalDeleted,
            'failed_types' => $failed,
        ]);

        return self::SUCCESS;
    }

    /**
     * Clean specific orphan type
     */
    protected function cleanSpecificType(
        OrphanedDataCleanupService $cleanupService,
        string $type,
        ?int $tenantId,
        bool $dryRun
    ): int {
        $this->info("Cleaning orphaned {$type}...");

        try {
            // First scan
            $scanResult = $cleanupService->scanType($type, $tenantId);
            $orphanCount = $scanResult['orphan_count'] ?? 0;

            if ($orphanCount === 0) {
                $this->info("✓ No orphaned {$type} found");

                return self::SUCCESS;
            }

            $this->line("Found {$orphanCount} orphaned {$type}");

            if ($dryRun) {
                $this->info("Would delete {$orphanCount} orphaned {$type}");

                return self::SUCCESS;
            }

            // Perform cleanup
            $result = $cleanupService->cleanupType($type, $tenantId, false);

            if ($result['success']) {
                $deletedCount = $result['deleted_count'] ?? 0;
                $this->info("Successfully deleted {$deletedCount} orphaned {$type}");

                return self::SUCCESS;
            } else {
                $this->error('Failed: '.($result['message'] ?? 'Unknown error'));

                return self::FAILURE;
            }

        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
            $this->line('Available types: '.implode(', ', $this->getAvailableTypes()));

            return self::INVALID;
        }
    }

    /**
     * Generate detailed orphan report
     */
    protected function generateReport(
        OrphanedDataCleanupService $cleanupService,
        ?int $tenantId
    ): int {
        $this->info('Generating Detailed Orphan Report');
        $this->line('Generated at: '.now()->toIso8601String());
        $this->line('Tenant: '.($tenantId ?? 'All'));
        $this->newLine();

        $report = $cleanupService->getDetailedReport($tenantId);

        $this->line("<fg=cyan>Total Orphaned Records: {$report['total_orphans']}</fg=cyan>");
        $this->newLine();

        $headers = ['Type', 'Orphan Count', 'Table', 'Foreign Key', 'Reference Table'];
        $rows = [];

        foreach ($report['breakdown'] ?? [] as $type => $data) {
            if (($data['orphan_count'] ?? 0) > 0) {
                $rows[] = [
                    $type,
                    "<error>{$data['orphan_count']}</error>",
                    $data['table'],
                    $data['foreign_key'],
                    $data['reference_table'],
                ];
            }
        }

        if (empty($rows)) {
            $this->info('✓ Database is clean - no orphaned records detected!');

            return self::SUCCESS;
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->warn("Recommendation: Run 'php artisan cleanup:orphaned --dry-run' to preview cleanup");

        return self::SUCCESS;
    }

    /**
     * Get list of available orphan types
     */
    protected function getAvailableTypes(): array
    {
        // These should match the keys in OrphanedDataCleanupService::$orphanConfigs
        return [
            'invoice_items_without_invoice',
            'payment_items_without_payment',
            'journal_lines_without_entry',
            'sales_order_items_without_order',
            'purchase_order_items_without_order',
            'delivery_order_items_without_order',
            'goods_receipt_items_without_receipt',
            'stock_movements_without_product',
            'stock_movements_without_warehouse',
            'payments_without_customer',
            'invoices_without_customer',
            'users_without_tenant',
            'products_without_warehouse',
        ];
    }
}
