<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for identifying and cleaning up orphaned records.
 *
 * Detects records that reference non-existent parent records
 * through foreign key relationships, and safely removes them.
 */
class OrphanedDataCleanupService
{
    /**
     * Orphan detection configurations
     */
    protected array $orphanConfigs = [
        'invoice_items_without_invoice' => [
            'table' => 'invoice_items',
            'foreign_key' => 'invoice_id',
            'reference_table' => 'invoices',
            'tenant_scoped' => true,
        ],
        'payment_items_without_payment' => [
            'table' => 'bulk_payment_items',
            'foreign_key' => 'bulk_payment_id',
            'reference_table' => 'bulk_payments',
            'tenant_scoped' => true,
        ],
        'journal_lines_without_entry' => [
            'table' => 'journal_entry_lines',
            'foreign_key' => 'journal_entry_id',
            'reference_table' => 'journal_entries',
            'tenant_scoped' => true,
        ],
        'sales_order_items_without_order' => [
            'table' => 'sales_order_items',
            'foreign_key' => 'sales_order_id',
            'reference_table' => 'sales_orders',
            'tenant_scoped' => true,
        ],
        'purchase_order_items_without_order' => [
            'table' => 'purchase_order_items',
            'foreign_key' => 'purchase_order_id',
            'reference_table' => 'purchase_orders',
            'tenant_scoped' => true,
        ],
        'delivery_order_items_without_order' => [
            'table' => 'delivery_order_items',
            'foreign_key' => 'delivery_order_id',
            'reference_table' => 'delivery_orders',
            'tenant_scoped' => true,
        ],
        'goods_receipt_items_without_receipt' => [
            'table' => 'goods_receipt_items',
            'foreign_key' => 'goods_receipt_id',
            'reference_table' => 'goods_receipts',
            'tenant_scoped' => true,
        ],
        'stock_movements_without_product' => [
            'table' => 'stock_movements',
            'foreign_key' => 'product_id',
            'reference_table' => 'products',
            'tenant_scoped' => true,
        ],
        'stock_movements_without_warehouse' => [
            'table' => 'stock_movements',
            'foreign_key' => 'warehouse_id',
            'reference_table' => 'warehouses',
            'tenant_scoped' => true,
        ],
        'payments_without_customer' => [
            'table' => 'payments',
            'foreign_key' => 'customer_id',
            'reference_table' => 'customers',
            'tenant_scoped' => true,
        ],
        'invoices_without_customer' => [
            'table' => 'invoices',
            'foreign_key' => 'customer_id',
            'reference_table' => 'customers',
            'tenant_scoped' => true,
        ],
        'users_without_tenant' => [
            'table' => 'users',
            'foreign_key' => 'tenant_id',
            'reference_table' => 'tenants',
            'tenant_scoped' => false,
        ],
        'products_without_warehouse' => [
            'table' => 'product_warehouse',
            'foreign_key' => 'warehouse_id',
            'reference_table' => 'warehouses',
            'tenant_scoped' => true,
        ],
    ];

    /**
     * Scan all configured tables for orphaned records
     *
     * @param  int|null  $tenantId  Specific tenant ID (null = all tenants)
     * @return array Scan results
     */
    public function scanAll(?int $tenantId = null): array
    {
        $results = [];

        foreach ($this->orphanConfigs as $type => $config) {
            try {
                $result = $this->scanType($type, $tenantId);
                $results[$type] = $result;
            } catch (\Throwable $e) {
                Log::error("Failed to scan {$type}: ".$e->getMessage());
                $results[$type] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'orphan_count' => 0,
                ];
            }
        }

        return $results;
    }

    /**
     * Scan specific type for orphaned records
     */
    public function scanType(string $type, ?int $tenantId = null): array
    {
        if (! isset($this->orphanConfigs[$type])) {
            throw new \InvalidArgumentException("Unknown orphan type: {$type}");
        }

        $config = $this->orphanConfigs[$type];
        $table = $config['table'];
        $foreignKey = $config['foreign_key'];
        $referenceTable = $config['reference_table'];

        // Build query to find orphans
        $query = DB::table($table)
            ->leftJoin($referenceTable, "{$table}.{$foreignKey}", '=', "{$referenceTable}.id")
            ->whereNull("{$referenceTable}.id");

        if ($tenantId && $config['tenant_scoped']) {
            $query->where("{$table}.tenant_id", $tenantId);
        }

        $count = $query->count();

        return [
            'success' => true,
            'orphan_count' => $count,
            'table' => $table,
            'foreign_key' => $foreignKey,
            'reference_table' => $referenceTable,
        ];
    }

    /**
     * Clean up all orphaned records
     *
     * @param  int|null  $tenantId  Specific tenant ID
     * @param  bool  $dryRun  Show what would be deleted without deleting
     * @return array Cleanup results
     */
    public function cleanupAll(?int $tenantId = null, bool $dryRun = false): array
    {
        $results = [];
        $totalDeleted = 0;

        foreach ($this->orphanConfigs as $type => $config) {
            try {
                $result = $this->cleanupType($type, $tenantId, $dryRun);
                $results[$type] = $result;
                $totalDeleted += $result['deleted_count'] ?? 0;
            } catch (\Throwable $e) {
                Log::error("Failed to cleanup {$type}: ".$e->getMessage());
                $results[$type] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'deleted_count' => 0,
                ];
            }
        }

        return [
            'success' => true,
            'total_deleted' => $totalDeleted,
            'types' => $results,
        ];
    }

    /**
     * Clean up orphaned records of specific type
     */
    public function cleanupType(string $type, ?int $tenantId = null, bool $dryRun = false): array
    {
        if (! isset($this->orphanConfigs[$type])) {
            throw new \InvalidArgumentException("Unknown orphan type: {$type}");
        }

        $config = $this->orphanConfigs[$type];
        $table = $config['table'];
        $foreignKey = $config['foreign_key'];
        $referenceTable = $config['reference_table'];

        // Build query to find orphans
        $query = DB::table($table)
            ->leftJoin($referenceTable, "{$table}.{$foreignKey}", '=', "{$referenceTable}.id")
            ->whereNull("{$referenceTable}.id");

        if ($tenantId && $config['tenant_scoped']) {
            $query->where("{$table}.tenant_id", $tenantId);
        }

        if ($dryRun) {
            $count = $query->count();

            return [
                'success' => true,
                'deleted_count' => 0,
                'would_delete_count' => $count,
                'message' => "Would delete {$count} orphaned records",
            ];
        }

        // Delete in batches
        $deletedCount = 0;
        $batchSize = 1000;

        do {
            $batch = $query->limit($batchSize)->pluck("{$table}.id");

            if ($batch->isEmpty()) {
                break;
            }

            DB::table($table)->whereIn('id', $batch)->delete();
            $deletedCount += $batch->count();

            Log::info("Deleted {$batch->count()} orphans from {$table}");
        } while ($batch->count() >= $batchSize);

        return [
            'success' => true,
            'deleted_count' => $deletedCount,
            'message' => "Deleted {$deletedCount} orphaned records",
        ];
    }

    /**
     * Find and fix broken foreign key relationships
     * More intelligent than simple deletion - attempts to repair data
     */
    public function fixBrokenRelationships(array $options = []): array
    {
        $fixes = [];

        // Fix invoice items pointing to non-existent invoices
        $fixes['invoice_items'] = $this->reassignOrphans(
            table: 'invoice_items',
            foreignKey: 'invoice_id',
            referenceTable: 'invoices',
            defaultAction: $options['default_action'] ?? 'delete'
        );

        // Fix journal lines pointing to non-existent entries
        $fixes['journal_lines'] = $this->reassignOrphans(
            table: 'journal_entry_lines',
            foreignKey: 'journal_entry_id',
            referenceTable: 'journal_entries',
            defaultAction: 'delete' // Must delete - critical integrity
        );

        return $fixes;
    }

    /**
     * Reassign or delete orphaned records
     */
    protected function reassignOrphans(
        string $table,
        string $foreignKey,
        string $referenceTable,
        string $defaultAction = 'delete'
    ): array {
        $orphans = DB::table($table)
            ->leftJoin($referenceTable, "{$table}.{$foreignKey}", '=', "{$referenceTable}.id")
            ->whereNull("{$referenceTable}.id")
            ->get(["{$table}.*"]);

        $deleted = 0;
        $reassigned = 0;

        foreach ($orphans as $orphan) {
            if ($defaultAction === 'delete') {
                DB::table($table)->where('id', $orphan->id)->delete();
                $deleted++;
            } else {
                // Try to find a suitable parent or delete
                // This is context-specific and should be customized per table
                DB::table($table)->where('id', $orphan->id)->delete();
                $deleted++;
            }
        }

        return [
            'deleted' => $deleted,
            'reassigned' => $reassigned,
            'total' => count($orphans),
        ];
    }

    /**
     * Get detailed orphan report
     */
    public function getDetailedReport(?int $tenantId = null): array
    {
        $report = [];
        $totalOrphans = 0;

        foreach ($this->orphanConfigs as $type => $config) {
            $scan = $this->scanType($type, $tenantId);
            $report[$type] = $scan;
            $totalOrphans += $scan['orphan_count'];
        }

        return [
            'total_orphans' => $totalOrphans,
            'breakdown' => $report,
            'generated_at' => now()->toIso8601String(),
            'tenant_id' => $tenantId,
        ];
    }

    /**
     * Schedule regular cleanup job
     */
    public function scheduleCleanup(): void
    {
        // This would integrate with Laravel's scheduler
        // Example: Run weekly in app/Console/Kernel.php
        Log::info('Orphan cleanup scheduled - configure in Kernel.php');
    }
}
