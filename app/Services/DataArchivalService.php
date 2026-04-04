<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AiUsageLog;
use App\Models\AnomalyAlert;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\ErpNotification;
use App\Models\ErrorLog;
use App\Models\HarvestLog;
use App\Models\LivestockHealthRecord;
use App\Models\MaintenanceLog;
use App\Models\QualityControlLog;
use App\Models\StockMovement;
use App\Models\UserPointLog;
use App\Models\WarehouseTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for archiving old data to improve database performance.
 * 
 * Moves historical data from active tables to archive tables based on
 * configurable retention policies. Supports multi-tenant archival.
 */
class DataArchivalService
{
    /**
     * Archival configurations for different entity types
     */
    protected array $archivalConfigs = [
        'activity_logs' => [
            'model' => ActivityLog::class,
            'retention_days' => 365,
            'archive_table' => 'archived_activity_logs',
            'date_column' => 'created_at',
            'tenant_scoped' => true,
        ],
        'ai_usage_logs' => [
            'model' => AiUsageLog::class,
            'retention_days' => 180,
            'archive_table' => 'archived_ai_usage_logs',
            'date_column' => 'created_at',
            'tenant_scoped' => true,
        ],
        'anomaly_alerts' => [
            'model' => AnomalyAlert::class,
            'retention_days' => 90,
            'archive_table' => 'archived_anomaly_alerts',
            'date_column' => 'created_at',
            'tenant_scoped' => true,
        ],
        'chat_messages' => [
            'model' => ChatMessage::class,
            'retention_days' => 180,
            'archive_table' => 'archived_chat_messages',
            'date_column' => 'created_at',
            'tenant_scoped' => true,
        ],
        'chat_sessions' => [
            'model' => ChatSession::class,
            'retention_days' => 180,
            'archive_table' => 'archived_chat_sessions',
            'date_column' => 'created_at',
            'tenant_scoped' => true,
        ],
        'notifications' => [
            'model' => ErpNotification::class,
            'retention_days' => 90,
            'archive_table' => 'archived_notifications',
            'date_column' => 'created_at',
            'tenant_scoped' => true,
        ],
        'error_logs' => [
            'model' => ErrorLog::class,
            'retention_days' => 180,
            'archive_table' => 'archived_error_logs',
            'date_column' => 'created_at',
            'tenant_scoped' => true,
        ],
        'harvest_logs' => [
            'model' => HarvestLog::class,
            'retention_days' => 730, // 2 years for agricultural data
            'archive_table' => 'archived_harvest_logs',
            'date_column' => 'harvest_date',
            'tenant_scoped' => true,
        ],
        'livestock_health_records' => [
            'model' => LivestockHealthRecord::class,
            'retention_days' => 1095, // 3 years for livestock records
            'archive_table' => 'archived_livestock_health_records',
            'date_column' => 'treatment_date',
            'tenant_scoped' => true,
        ],
        'stock_movements' => [
            'model' => StockMovement::class,
            'retention_days' => 365,
            'archive_table' => 'archived_stock_movements',
            'date_column' => 'movement_date',
            'tenant_scoped' => true,
        ],
    ];

    /**
     * Archive all configured data types
     *
     * @param int|null $tenantId Specific tenant ID (null = all tenants)
     * @param bool $dryRun Show what would be archived without deleting
     * @return array Archival results
     */
    public function archiveAll(?int $tenantId = null, bool $dryRun = false): array
    {
        $results = [];

        foreach ($this->archivalConfigs as $type => $config) {
            Log::info("Starting archival for {$type}");

            try {
                $result = $this->archiveType($type, $tenantId, $dryRun);
                $results[$type] = $result;
            } catch (\Throwable $e) {
                Log::error("Failed to archive {$type}: " . $e->getMessage());
                $results[$type] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'archived_count' => 0,
                ];
            }
        }

        return $results;
    }

    /**
     * Archive specific data type
     *
     * @param string $type Type of data to archive
     * @param int|null $tenantId Specific tenant ID
     * @param bool $dryRun Dry run mode
     * @return array Archival result
     */
    public function archiveType(string $type, ?int $tenantId = null, bool $dryRun = false): array
    {
        if (!isset($this->archivalConfigs[$type])) {
            throw new \InvalidArgumentException("Unknown archival type: {$type}");
        }

        $config = $this->archivalConfigs[$type];
        $modelClass = $config['model'];
        $retentionDays = config("data_retention.archival.{$type}_days", $config['retention_days']);
        $cutoffDate = now()->subDays($retentionDays);
        $dateColumn = $config['date_column'];

        Log::info("Archiving {$type} older than {$retentionDays} days (cutoff: {$cutoffDate->toDateString()})");

        // Build query
        $query = $modelClass::where($dateColumn, '<', $cutoffDate);

        if ($tenantId && $config['tenant_scoped']) {
            $query->where('tenant_id', $tenantId);
        }

        // Get count first
        $count = $query->count();

        if ($count === 0) {
            Log::info("No {$type} found for archival");
            return ['success' => true, 'archived_count' => 0, 'message' => 'No records to archive'];
        }

        Log::info("Found {$count} {$type} to archive");

        if ($dryRun) {
            return [
                'success' => true,
                'archived_count' => 0,
                'would_archive_count' => $count,
                'message' => "Would archive {$count} {$type}",
            ];
        }

        // Archive in batches
        $archivedCount = 0;
        $batchSize = 1000;

        DB::transaction(function () use ($query, $config, $batchSize, &$archivedCount) {
            do {
                $batch = $query->limit($batchSize)->get();

                if ($batch->isEmpty()) {
                    break;
                }

                // Insert into archive table
                $this->insertIntoArchive($batch, $config);

                // Delete from original table
                $modelClass = $config['model'];
                $ids = $batch->pluck('id')->toArray();
                $modelClass::destroy($ids);

                $archivedCount += $batch->count();

                Log::info("Archived batch of {$batch->count()} {$config['archive_table']}");
            } while ($batch->count() >= $batchSize);
        });

        Log::info("Successfully archived {$archivedCount} {$type}");

        return [
            'success' => true,
            'archived_count' => $archivedCount,
            'message' => "Archived {$archivedCount} records",
        ];
    }

    /**
     * Insert records into archive table
     */
    protected function insertIntoArchive($records, array $config): void
    {
        $archiveTable = $config['archive_table'];

        // Check if archive table exists
        if (!$this->archiveTableExists($archiveTable)) {
            Log::warning("Archive table {$archiveTable} does not exist. Creating...");
            $this->createArchiveTable($config);
        }

        $data = $records->map(function ($record) {
            return collect($record->getAttributes())
                ->merge(['archived_at' => now()])
                ->toArray();
        })->toArray();

        // Use chunked insert to avoid memory issues
        collect($data)->chunk(500)->each(function ($chunk) use ($archiveTable) {
            DB::table($archiveTable)->insert($chunk->toArray());
        });
    }

    /**
     * Check if archive table exists
     */
    protected function archiveTableExists(string $table): bool
    {
        return DB::getSchemaBuilder()->hasTable($table);
    }

    /**
     * Create archive table dynamically
     */
    protected function createArchiveTable(array $config): void
    {
        $archiveTable = $config['archive_table'];
        $modelClass = $config['model'];

        $model = new $modelClass();
        $table = $model->getTable();

        // Get table structure
        $columns = DB::select(DB::raw("DESCRIBE {$table}"));

        // Create archive table with same structure plus archived_at
        DB::statement("CREATE TABLE IF NOT EXISTS {$archiveTable} LIKE {$table}");

        // Add archived_at column if not exists
        if (!collect($columns)->contains('Field', 'archived_at')) {
            DB::statement("ALTER TABLE {$archiveTable} ADD COLUMN archived_at TIMESTAMP NULL");
        }
    }

    /**
     * Get archival statistics
     */
    public function getStatistics(?int $tenantId = null): array
    {
        $stats = [];

        foreach ($this->archivalConfigs as $type => $config) {
            $modelClass = $config['model'];
            $retentionDays = config("data_retention.archival.{$type}_days", $config['retention_days']);
            $cutoffDate = now()->subDays($retentionDays);
            $dateColumn = $config['date_column'];

            $query = $modelClass::where($dateColumn, '<', $cutoffDate);

            if ($tenantId && $config['tenant_scoped']) {
                $query->where('tenant_id', $tenantId);
            }

            $stats[$type] = [
                'ready_for_archival' => $query->count(),
                'retention_days' => $retentionDays,
                'cutoff_date' => $cutoffDate->toDateString(),
            ];
        }

        return $stats;
    }

    /**
     * Restore archived data back to main table
     */
    public function restore(string $type, int $tenantId, int $limit = 1000): int
    {
        if (!isset($this->archivalConfigs[$type])) {
            throw new \InvalidArgumentException("Unknown archival type: {$type}");
        }

        $config = $this->archivalConfigs[$type];
        $modelClass = $config['model'];
        $archiveTable = $config['archive_table'];

        if (!$this->archiveTableExists($archiveTable)) {
            throw new \RuntimeException("Archive table {$archiveTable} does not exist");
        }

        $restored = 0;
        $batchSize = 100;

        do {
            $batch = DB::table($archiveTable)
                ->where('tenant_id', $tenantId)
                ->limit($batchSize)
                ->get();

            if ($batch->isEmpty()) {
                break;
            }

            // Remove archived_at and insert back to main table
            $data = $batch->map(function ($record) {
                $arr = (array) $record;
                unset($arr['archived_at']);
                return $arr;
            })->toArray();

            foreach ($data as $row) {
                try {
                    $modelClass::create($row);
                    $restored++;
                } catch (\Throwable $e) {
                    Log::error("Failed to restore record: " . $e->getMessage());
                }
            }

            // Delete from archive
            DB::table($archiveTable)
                ->whereIn('id', $batch->pluck('id')->toArray())
                ->delete();

        } while ($batch->count() >= $batchSize);

        return $restored;
    }
}
