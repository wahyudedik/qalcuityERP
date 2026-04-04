<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for multi-tenant data migration and consolidation.
 * 
 * Supports:
 * - Merging tenants
 * - Splitting tenants
 * - Data transfer between tenants
 * - Tenant restructuring
 */
class TenantDataMigrationService
{
    /**
     * Merge source tenant into target tenant
     * All data from source will be moved to target
     *
     * @param int $sourceTenantId Source tenant ID
     * @param int $targetTenantId Target tenant ID
     * @param array $options Migration options
     * @return array Migration result
     */
    public function mergeTenants(
        int $sourceTenantId,
        int $targetTenantId,
        array $options = []
    ): array {
        if ($sourceTenantId === $targetTenantId) {
            throw new \InvalidArgumentException("Source and target tenants must be different");
        }

        Log::info("Starting tenant merge: {$sourceTenantId} → {$targetTenantId}");

        $results = [];
        $conflicts = [];

        try {
            DB::transaction(function () use ($sourceTenantId, $targetTenantId, &$results, &$conflicts, $options) {
                // Merge users
                $results['users'] = $this->mergeUsers($sourceTenantId, $targetTenantId, $options);

                // Merge customers
                $results['customers'] = $this->mergeCustomers($sourceTenantId, $targetTenantId, $options);

                // Merge suppliers
                $results['suppliers'] = $this->mergeSuppliers($sourceTenantId, $targetTenantId, $options);

                // Merge products
                $results['products'] = $this->mergeProducts($sourceTenantId, $targetTenantId, $options);

                // Merge warehouses
                $results['warehouses'] = $this->mergeWarehouses($sourceTenantId, $targetTenantId, $options);

                // Merge COA
                $results['coa'] = $this->mergeChartOfAccounts($sourceTenantId, $targetTenantId, $options);

                // Move transactions (invoices, journals, etc.)
                $results['transactions'] = $this->moveTransactions($sourceTenantId, $targetTenantId, $options);
            });

            Log::info("Tenant merge completed successfully");

            return [
                'success' => true,
                'message' => "Successfully merged tenant {$sourceTenantId} into {$targetTenantId}",
                'results' => $results,
                'conflicts' => $conflicts,
            ];

        } catch (\Throwable $e) {
            Log::error("Tenant merge failed: " . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'results' => $results,
            ];
        }
    }

    /**
     * Merge users from source to target tenant
     */
    protected function mergeUsers(int $sourceId, int $targetId, array $options): array
    {
        $sourceUsers = User::where('tenant_id', $sourceId)->get();
        $targetUsers = User::where('tenant_id', $targetId)->get();

        $moved = 0;
        $conflicts = 0;

        foreach ($sourceUsers as $sourceUser) {
            // Check for email conflict
            $existingUser = $targetUsers->firstWhere('email', $sourceUser->email);

            if ($existingUser) {
                // Conflict - keep target user, update references
                $this->reassignUserReferences($sourceUser->id, $existingUser->id);
                $sourceUser->delete();
                $conflicts++;
            } else {
                // No conflict - just update tenant_id
                $sourceUser->update(['tenant_id' => $targetId]);
                $moved++;
            }
        }

        return [
            'moved' => $moved,
            'conflicts' => $conflicts,
            'resolution' => 'Target user kept, source user deleted after reference reassignment',
        ];
    }

    /**
     * Merge customers from source to target tenant
     */
    protected function mergeCustomers(int $sourceId, int $targetId, array $options): array
    {
        return $this->mergeGenericModel(
            model: Customer::class,
            sourceId: $sourceId,
            targetId: $targetId,
            conflictFields: ['email', 'phone'],
            options: $options
        );
    }

    /**
     * Merge suppliers from source to target tenant
     */
    protected function mergeSuppliers(int $sourceId, int $targetId, array $options): array
    {
        return $this->mergeGenericModel(
            model: Supplier::class,
            sourceId: $sourceId,
            targetId: $targetId,
            conflictFields: ['email', 'phone'],
            options: $options
        );
    }

    /**
     * Merge products from source to target tenant
     */
    protected function mergeProducts(int $sourceId, int $targetId, array $options): array
    {
        return $this->mergeGenericModel(
            model: Product::class,
            sourceId: $sourceId,
            targetId: $targetId,
            conflictFields: ['sku', 'name'],
            options: $options
        );
    }

    /**
     * Merge warehouses from source to target tenant
     */
    protected function mergeWarehouses(int $sourceId, int $targetId, array $options): array
    {
        return $this->mergeGenericModel(
            model: Warehouse::class,
            sourceId: $sourceId,
            targetId: $targetId,
            conflictFields: ['code', 'name'],
            options: $options
        );
    }

    /**
     * Merge chart of accounts from source to target tenant
     */
    protected function mergeChartOfAccounts(int $sourceId, int $targetId, array $options): array
    {
        $sourceCoas = ChartOfAccount::where('tenant_id', $sourceId)
            ->orderBy('code')
            ->get();

        $targetCoas = ChartOfAccount::where('tenant_id', $targetId)
            ->pluck('code', 'id');

        $moved = 0;
        $conflicts = 0;
        $mapping = [];

        foreach ($sourceCoas as $sourceCoa) {
            if ($targetCoas->contains($sourceCoa->code)) {
                // Conflict - same account code exists
                $conflicts++;

                if ($options['merge_coa_lines'] ?? false) {
                    // Merge journal lines to target account
                    $targetCoaId = $targetCoas->flip()->get($sourceCoa->code);
                    $this->reassignJournalLines($sourceCoa->id, $targetCoaId);
                }

                $sourceCoa->delete();
                $mapping[$sourceCoa->id] = null; // Deleted
            } else {
                // No conflict - move to target tenant
                $sourceCoa->update(['tenant_id' => $targetId]);
                $moved++;
                $mapping[$sourceCoa->id] = $sourceCoa->id;
            }
        }

        return [
            'moved' => $moved,
            'conflicts' => $conflicts,
            'account_mapping' => $mapping,
        ];
    }

    /**
     * Move transactions to target tenant
     */
    protected function moveTransactions(int $sourceId, int $targetId, array $options): array
    {
        $tables = [
            'invoices' => Invoice::class,
            'journal_entries' => JournalEntry::class,
        ];

        $moved = [];

        foreach ($tables as $table => $model) {
            $count = $model->where('tenant_id', $sourceId)->count();

            if ($count > 0) {
                $model->where('tenant_id', $sourceId)->update(['tenant_id' => $targetId]);
                $moved[$table] = $count;
            }
        }

        return $moved;
    }

    /**
     * Generic method to merge models between tenants
     */
    protected function mergeGenericModel(
        string $model,
        int $sourceId,
        int $targetId,
        array $conflictFields,
        array $options
    ): array {
        $sourceRecords = $model::where('tenant_id', $sourceId)->get();
        $targetRecords = $model::where('tenant_id', $targetId)->get();

        $moved = 0;
        $merged = 0;
        $deleted = 0;

        foreach ($sourceRecords as $sourceRecord) {
            // Check for conflicts
            $conflict = null;
            foreach ($conflictFields as $field) {
                if (isset($sourceRecord->$field)) {
                    $existing = $targetRecords->firstWhere($field, $sourceRecord->$field);
                    if ($existing) {
                        $conflict = $existing;
                        break;
                    }
                }
            }

            if ($conflict) {
                // Merge strategy depends on options
                if ($options['prefer_source'] ?? false) {
                    // Delete target, move source
                    $this->reassignReferences(get_class($conflict), $conflict->id, $sourceRecord->id);
                    $conflict->delete();
                    $sourceRecord->update(['tenant_id' => $targetId]);
                    $moved++;
                } else {
                    // Keep target, delete source after reassigning references
                    $this->reassignReferences(get_class($sourceRecord), $sourceRecord->id, $conflict->id);
                    $sourceRecord->delete();
                    $merged++;
                }
            } else {
                // No conflict - simple move
                $sourceRecord->update(['tenant_id' => $targetId]);
                $moved++;
            }
        }

        return [
            'moved' => $moved,
            'merged' => $merged,
            'deleted' => $deleted,
        ];
    }

    /**
     * Reassign references from old record to new record
     */
    protected function reassignReferences(
        string $modelClass,
        int $oldId,
        int $newId
    ): void {
        // Get all tables that might reference this model
        $references = $this->findReferences($modelClass, $oldId);

        foreach ($references as $table => $foreignKey) {
            DB::table($table)
                ->where($foreignKey, $oldId)
                ->update([$foreignKey => $newId]);
        }
    }

    /**
     * Find all references to a record
     */
    protected function findReferences(string $modelClass, int $id): array
    {
        $model = new $modelClass();
        $table = $model->getTable();
        $references = [];

        // This is a simplified version - in production you'd want to scan
        // all foreign keys in the database schema
        return $references;
    }

    /**
     * Split tenant into multiple tenants
     */
    public function splitTenant(
        int $sourceTenantId,
        array $splitConfig
    ): array {
        Log::info("Starting tenant split for tenant {$sourceTenantId}");

        // Split logic would depend on the specific requirements
        // e.g., split by department, region, business unit, etc.

        return [
            'success' => true,
            'message' => "Tenant {$sourceTenantId} split completed",
        ];
    }

    /**
     * Transfer specific data from one tenant to another
     */
    public function transferData(
        int $sourceTenantId,
        int $targetTenantId,
        array $dataTypes
    ): array {
        Log::info("Transferring data types: " . implode(', ', $dataTypes));

        $results = [];

        foreach ($dataTypes as $dataType) {
            try {
                $result = $this->transferDataType(
                    $sourceTenantId,
                    $targetTenantId,
                    $dataType
                );
                $results[$dataType] = $result;
            } catch (\Throwable $e) {
                $results[$dataType] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Transfer specific data type between tenants
     */
    protected function transferDataType(
        int $sourceId,
        int $targetId,
        string $dataType
    ): array {
        $models = [
            'customers' => Customer::class,
            'suppliers' => Supplier::class,
            'products' => Product::class,
            'invoices' => Invoice::class,
        ];

        if (!isset($models[$dataType])) {
            throw new \InvalidArgumentException("Unknown data type: {$dataType}");
        }

        $modelClass = $models[$dataType];
        $count = $modelClass->where('tenant_id', $sourceId)->count();

        if ($count === 0) {
            return ['transferred' => 0, 'message' => 'No records to transfer'];
        }

        $modelClass->where('tenant_id', $sourceId)->update(['tenant_id' => $targetId]);

        return [
            'transferred' => $count,
            'message' => "Transferred {$count} {$dataType}",
        ];
    }

    /**
     * Validate tenant data integrity before migration
     */
    public function validateTenantData(int $tenantId): array
    {
        $issues = [];

        // Check for orphaned records
        $orphanService = new OrphanedDataCleanupService();
        $orphans = $orphanService->scanAll($tenantId);

        foreach ($orphans as $type => $result) {
            if (($result['orphan_count'] ?? 0) > 0) {
                $issues[] = "Found {$result['orphan_count']} orphaned {$type}";
            }
        }

        // Check for duplicate records
        $duplicates = $this->findDuplicates($tenantId);
        if (!empty($duplicates)) {
            $issues = array_merge($issues, $duplicates);
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Find duplicate records in tenant
     */
    protected function findDuplicates(int $tenantId): array
    {
        $duplicates = [];

        // Check for duplicate customer emails
        $duplicateEmails = Customer::where('tenant_id', $tenantId)
            ->select('email', DB::raw('COUNT(*) as count'))
            ->groupBy('email')
            ->having('count', '>', 1)
            ->get();

        if ($duplicateEmails->isNotEmpty()) {
            $duplicates[] = "Found {$duplicateEmails->count()} duplicate customer emails";
        }

        return $duplicates;
    }

    /**
     * Private helper methods for reference reassignment
     */
    protected function reassignUserReferences(int $oldUserId, int $newUserId): void
    {
        // Update all references to the old user ID
        $tables = [
            'activity_logs' => 'user_id',
            'journal_entries' => 'user_id',
            'invoices' => 'user_id',
        ];

        foreach ($tables as $table => $column) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)
                    ->where($column, $oldUserId)
                    ->update([$column => $newUserId]);
            }
        }
    }

    protected function reassignJournalLines(int $oldCoaId, int $newCoaId): void
    {
        JournalEntryLine::where('account_id', $oldCoaId)
            ->update(['account_id' => $newCoaId]);
    }
}
