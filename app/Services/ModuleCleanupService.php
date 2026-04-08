<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BUG-SET-002 FIX: Module Cleanup Service
 * 
 * Handles data cleanup when modules are disabled to prevent orphaned data.
 * Provides options to: archive, soft-delete, or keep data.
 */
class ModuleCleanupService
{
    /**
     * Module to table mappings for cleanup
     */
    const MODULE_TABLES = [
        'pos' => [
            'tables' => ['pos_sessions', 'pos_transactions', 'pos_transaction_items', 'pos_payments'],
            'models' => ['App\\Models\\PosSession', 'App\\Models\\PosTransaction'],
            'impact' => 'High - All POS transaction history will be affected',
        ],
        'inventory' => [
            'tables' => ['product_stocks', 'stock_movements', 'inventory_adjustments', 'stock_counts', 'stock_count_items'],
            'models' => ['App\\Models\\ProductStock', 'App\\Models\\StockMovement'],
            'impact' => 'Critical - Stock data and movements will be affected',
        ],
        'purchasing' => [
            'tables' => ['purchase_orders', 'purchase_order_items', 'goods_receipts', 'goods_receipt_items', 'supplier_rfq_responses'],
            'models' => ['App\\Models\\PurchaseOrder', 'App\\Models\\GoodsReceipt'],
            'impact' => 'High - Purchase orders and receipts will be affected',
        ],
        'sales' => [
            'tables' => ['sales_orders', 'sales_order_items', 'quotations', 'quotation_items'],
            'models' => ['App\\Models\\SalesOrder', 'App\\Models\\Quotation'],
            'impact' => 'High - Sales orders and quotations will be affected',
        ],
        'hrm' => [
            'tables' => ['employees', 'attendances', 'leaves', 'overtime_requests', 'shifts', 'departments'],
            'models' => ['App\\Models\\Employee', 'App\\Models\\Attendance'],
            'impact' => 'Critical - Employee and attendance data will be affected',
        ],
        'payroll' => [
            'tables' => ['payrolls', 'payroll_items', 'payroll_runs', 'salary_components'],
            'models' => ['App\\Models\\Payroll', 'App\\Models\\PayrollItem'],
            'impact' => 'Critical - Payroll history will be affected',
        ],
        'crm' => [
            'tables' => ['leads', 'opportunities', 'contacts', 'activities', 'pipelines'],
            'models' => ['App\\Models\\Lead', 'App\\Models\\Opportunity'],
            'impact' => 'High - CRM pipeline and leads will be affected',
        ],
        'accounting' => [
            'tables' => ['journal_entries', 'journal_entry_lines', 'chart_of_accounts', 'fiscal_years'],
            'models' => ['App\\Models\\JournalEntry', 'App\\Models\\ChartOfAccount'],
            'impact' => 'Critical - All financial data will be affected',
        ],
        'production' => [
            'tables' => ['work_orders', 'production_outputs', 'recipes', 'recipe_ingredients'],
            'models' => ['App\\Models\\WorkOrder', 'App\\Models\\ProductionOutput'],
            'impact' => 'High - Work orders and production data will be affected',
        ],
        'manufacturing' => [
            'tables' => ['boms', 'bom_lines', 'work_centers', 'work_order_operations', 'material_reservations'],
            'models' => ['App\\Models\\Bom', 'App\\Models\\WorkOrder'],
            'impact' => 'High - BOM and manufacturing data will be affected',
        ],
        'fleet' => [
            'tables' => ['vehicles', 'drivers', 'fuel_logs', 'maintenance_logs', 'vehicle_assignments'],
            'models' => ['App\\Models\\Vehicle', 'App\\Models\\Driver'],
            'impact' => 'Medium - Fleet management data will be affected',
        ],
        'hotel' => [
            'tables' => ['rooms', 'room_types', 'reservations', 'guests', 'housekeeping_tasks', 'rate_plans'],
            'models' => ['App\\Models\\Room', 'App\\Models\\Reservation'],
            'impact' => 'Critical - All hotel operations data will be affected',
        ],
        'fnb' => [
            'tables' => ['menu_categories', 'menu_items', 'tables', 'kitchen_orders', 'recipes'],
            'models' => ['App\\Models\\MenuItem', 'App\\Models\\KitchenOrder'],
            'impact' => 'High - Restaurant operations data will be affected',
        ],
        'loyalty' => [
            'tables' => ['loyalty_programs', 'loyalty_points', 'loyalty_rewards', 'point_transactions'],
            'models' => ['App\\Models\\LoyaltyProgram', 'App\\Models\\LoyaltyPoint'],
            'impact' => 'Medium - Customer loyalty data will be affected',
        ],
        'projects' => [
            'tables' => ['projects', 'tasks', 'milestones', 'timesheets', 'project_members'],
            'models' => ['App\\Models\\Project', 'App\\Models\\Task'],
            'impact' => 'High - Project data and tasks will be affected',
        ],
        'assets' => [
            'tables' => ['fixed_assets', 'asset_depreciations', 'asset_disposals', 'asset_locations'],
            'models' => ['App\\Models\\FixedAsset', 'App\\Models\\AssetDepreciation'],
            'impact' => 'Medium - Asset tracking data will be affected',
        ],
        'telecom' => [
            'tables' => ['telecom_packages', 'telecom_subscribers', 'telecom_usage_logs', 'hotspot_sessions'],
            'models' => ['App\\Models\\TelecomPackage', 'App\\Models\\TelecomSubscriber'],
            'impact' => 'Critical - Telecom subscriber data will be affected',
        ],
    ];

    /**
     * Analyze impact of disabling a module
     */
    public function analyzeImpact(int $tenantId, string $module): array
    {
        if (!isset(self::MODULE_TABLES[$module])) {
            return ['error' => 'Module not found'];
        }

        $moduleInfo = self::MODULE_TABLES[$module];
        $analysis = [
            'module' => $module,
            'tenant_id' => $tenantId,
            'impact_level' => $moduleInfo['impact'],
            'data_summary' => [],
            'total_records' => 0,
            'recommendations' => [],
        ];

        // Count records in each table
        foreach ($moduleInfo['tables'] as $table) {
            try {
                $count = DB::table($table)
                    ->where('tenant_id', $tenantId)
                    ->count();

                $analysis['data_summary'][$table] = $count;
                $analysis['total_records'] += $count;

                // Add recommendations based on data volume
                if ($count > 0) {
                    if ($count > 10000) {
                        $analysis['recommendations'][] = "Table {$table} has {$count} records. Consider archiving before disabling.";
                    } elseif ($count > 1000) {
                        $analysis['recommendations'][] = "Table {$table} has {$count} records. Consider exporting data.";
                    }
                }
            } catch (\Exception $e) {
                $analysis['data_summary'][$table] = 0;
                Log::warning("Failed to count table {$table}: " . $e->getMessage());
            }
        }

        // Add specific recommendations
        if ($analysis['total_records'] > 0) {
            $analysis['recommendations'][] = "Total {$analysis['total_records']} records will be affected.";
            $analysis['recommendations'][] = "Choose cleanup strategy: archive, soft_delete, or keep.";
        } else {
            $analysis['recommendations'][] = "No data found. Safe to disable module.";
        }

        return $analysis;
    }

    /**
     * Cleanup module data when disabling
     */
    public function cleanupModule(int $tenantId, string $module, string $strategy = 'keep'): array
    {
        if (!isset(self::MODULE_TABLES[$module])) {
            return ['success' => false, 'message' => 'Module not found'];
        }

        if (!in_array($strategy, ['archive', 'soft_delete', 'keep'])) {
            return ['success' => false, 'message' => 'Invalid strategy. Use: archive, soft_delete, or keep'];
        }

        $moduleInfo = self::MODULE_TABLES[$module];
        $result = [
            'success' => true,
            'module' => $module,
            'strategy' => $strategy,
            'tables_processed' => 0,
            'records_affected' => 0,
            'errors' => [],
        ];

        try {
            DB::beginTransaction();

            foreach ($moduleInfo['tables'] as $table) {
                try {
                    $affected = 0;

                    switch ($strategy) {
                        case 'archive':
                            // Export data to archive table
                            $affected = $this->archiveTableData($tenantId, $table);
                            break;

                        case 'soft_delete':
                            // Soft delete records (if table supports it)
                            $affected = $this->softDeleteTableData($tenantId, $table);
                            break;

                        case 'keep':
                        default:
                            // Just count, don't delete
                            $affected = DB::table($table)
                                ->where('tenant_id', $tenantId)
                                ->count();
                            break;
                    }

                    $result['tables_processed']++;
                    $result['records_affected'] += $affected;

                } catch (\Exception $e) {
                    $result['errors'][] = "Failed to process {$table}: " . $e->getMessage();
                    Log::error("Module cleanup error for {$table}: " . $e->getMessage());
                }
            }

            DB::commit();

            Log::info("Module {$module} cleanup completed for tenant {$tenantId}", [
                'strategy' => $strategy,
                'tables_processed' => $result['tables_processed'],
                'records_affected' => $result['records_affected'],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $result['success'] = false;
            $result['errors'][] = "Transaction failed: " . $e->getMessage();
            Log::error("Module cleanup transaction failed: " . $e->getMessage());
        }

        return $result;
    }

    /**
     * Archive table data to archive table
     */
    protected function archiveTableData(int $tenantId, string $table): int
    {
        $archiveTable = $table . '_archive';

        // Check if archive table exists
        if (!$this->tableExists($archiveTable)) {
            // Create archive table with same structure
            $this->createArchiveTable($table, $archiveTable);
        }

        // Get records to archive
        $records = DB::table($table)
            ->where('tenant_id', $tenantId)
            ->get();

        if ($records->isEmpty()) {
            return 0;
        }

        // Insert into archive
        foreach ($records as $record) {
            $recordArray = (array) $record;
            $recordArray['archived_at'] = now();
            $recordArray['archive_reason'] = 'module_disabled';

            DB::table($archiveTable)->insert($recordArray);
        }

        // Delete from original table
        $count = DB::table($table)
            ->where('tenant_id', $tenantId)
            ->delete();

        return $count;
    }

    /**
     * Soft delete table data
     */
    protected function softDeleteTableData(int $tenantId, string $table): int
    {
        // Check if table has deleted_at column
        $hasSoftDelete = $this->columnExists($table, 'deleted_at');

        if (!$hasSoftDelete) {
            // Can't soft delete, just count
            return DB::table($table)
                ->where('tenant_id', $tenantId)
                ->count();
        }

        // Soft delete
        return DB::table($table)
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);
    }

    /**
     * Check if table exists
     */
    protected function tableExists(string $table): bool
    {
        return DB::getSchemaBuilder()->hasTable($table);
    }

    /**
     * Check if column exists in table
     */
    protected function columnExists(string $table, string $column): bool
    {
        if (!$this->tableExists($table)) {
            return false;
        }

        return DB::getSchemaBuilder()->hasColumn($table, $column);
    }

    /**
     * Create archive table with same structure
     */
    protected function createArchiveTable(string $sourceTable, string $archiveTable): void
    {
        // Get table structure
        $columns = DB::select("SHOW COLUMNS FROM {$sourceTable}");

        if (empty($columns)) {
            return;
        }

        // Create archive table
        $sql = "CREATE TABLE IF NOT EXISTS {$archiveTable} LIKE {$sourceTable}";
        DB::statement($sql);

        // Add archive columns
        DB::statement("ALTER TABLE {$archiveTable} ADD COLUMN IF NOT EXISTS archived_at TIMESTAMP NULL");
        DB::statement("ALTER TABLE {$archiveTable} ADD COLUMN IF NOT EXISTS archive_reason VARCHAR(255) NULL");
    }

    /**
     * Get cleanup summary for tenant
     */
    public function getTenantCleanupSummary(int $tenantId): array
    {
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return ['error' => 'Tenant not found'];
        }

        $enabledModules = $tenant->enabledModules();
        $disabledModules = array_diff(
            ModuleRecommendationService::ALL_MODULES,
            $enabledModules
        );

        $summary = [
            'tenant_id' => $tenantId,
            'tenant_name' => $tenant->name,
            'enabled_modules' => count($enabledModules),
            'disabled_modules' => count($disabledModules),
            'disabled_module_details' => [],
        ];

        foreach ($disabledModules as $module) {
            if (isset(self::MODULE_TABLES[$module])) {
                $impact = $this->analyzeImpact($tenantId, $module);
                $summary['disabled_module_details'][$module] = [
                    'total_records' => $impact['total_records'],
                    'impact_level' => $impact['impact_level'],
                    'has_data' => $impact['total_records'] > 0,
                ];
            }
        }

        return $summary;
    }

    /**
     * Restore archived data
     */
    public function restoreArchivedData(int $tenantId, string $module): array
    {
        if (!isset(self::MODULE_TABLES[$module])) {
            return ['success' => false, 'message' => 'Module not found'];
        }

        $moduleInfo = self::MODULE_TABLES[$module];
        $result = [
            'success' => true,
            'module' => $module,
            'tables_restored' => 0,
            'records_restored' => 0,
            'errors' => [],
        ];

        try {
            DB::beginTransaction();

            foreach ($moduleInfo['tables'] as $table) {
                $archiveTable = $table . '_archive';

                if (!$this->tableExists($archiveTable)) {
                    continue;
                }

                // Get archived records
                $records = DB::table($archiveTable)
                    ->where('tenant_id', $tenantId)
                    ->where('archive_reason', 'module_disabled')
                    ->get();

                if ($records->isEmpty()) {
                    continue;
                }

                // Restore to original table
                foreach ($records as $record) {
                    $recordArray = (array) $record;
                    unset($recordArray['archived_at']);
                    unset($recordArray['archive_reason']);

                    DB::table($table)->insert($recordArray);
                }

                // Delete from archive
                $count = DB::table($archiveTable)
                    ->where('tenant_id', $tenantId)
                    ->where('archive_reason', 'module_disabled')
                    ->delete();

                $result['tables_restored']++;
                $result['records_restored'] += $count;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $result['success'] = false;
            $result['errors'][] = "Restore failed: " . $e->getMessage();
        }

        return $result;
    }
}
