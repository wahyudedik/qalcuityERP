<?php

namespace App\Services;

use App\Models\AutomatedBackup;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AutomatedBackupService
{
    /**
     * Create automated backup
     */
    public function createBackup(string $type = 'manual', array $tables = []): array
    {
        $tenantId = auth()->user()->tenant_id;

        try {
            // Create backup record
            $backup = AutomatedBackup::create([
                'tenant_id' => $tenantId,
                'backup_type' => $type,
                'status' => 'processing',
                'started_at' => now(),
                'tables_included' => $tables,
            ]);

            // Get tables to backup
            if (empty($tables)) {
                $tables = $this->getImportantTables();
            }

            // Perform backup
            $backupData = [];
            $totalRecords = 0;

            foreach ($tables as $table) {
                try {
                    $data = DB::table($table)
                        ->where('tenant_id', $tenantId)
                        ->get()
                        ->toArray();

                    $backupData[$table] = $data;
                    $totalRecords += count($data);
                } catch (\Throwable $e) {
                    Log::warning("Failed to backup table {$table}: ".$e->getMessage());
                }
            }

            // Save backup file
            $fileName = "backups/tenant_{$tenantId}_{$type}_".now()->format('Y-m-d_H-i-s').'.json';
            Storage::put($fileName, json_encode($backupData, JSON_PRETTY_PRINT));

            $fileSize = Storage::size($fileName) / 1024 / 1024; // MB

            // Update backup record
            $backup->update([
                'status' => 'completed',
                'file_path' => $fileName,
                'file_size_mb' => round($fileSize, 2),
                'records_count' => $totalRecords,
                'completed_at' => now(),
                'expires_at' => $this->getExpiryDate($type),
            ]);

            return [
                'success' => true,
                'backup_id' => $backup->id,
                'file_path' => $fileName,
                'records_count' => $totalRecords,
                'file_size_mb' => round($fileSize, 2),
            ];

        } catch (\Throwable $e) {
            Log::error('Backup creation failed: '.$e->getMessage());

            if (isset($backup)) {
                $backup->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                ]);
            }

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Restore from backup
     */
    public function restoreFromBackup(int $backupId): array
    {
        $tenantId = auth()->user()->tenant_id;

        $backup = AutomatedBackup::where('tenant_id', $tenantId)
            ->where('id', $backupId)
            ->first();

        if (! $backup) {
            return ['success' => false, 'error' => 'Backup not found'];
        }

        if ($backup->status !== 'completed') {
            return ['success' => false, 'error' => 'Backup is not completed'];
        }

        try {
            // Read backup file
            $backupData = json_decode(Storage::get($backup->file_path), true);

            if (! $backupData) {
                return ['success' => false, 'error' => 'Invalid backup file'];
            }

            // Restore data
            $restoredTables = 0;
            $restoredRecords = 0;

            foreach ($backupData as $table => $records) {
                try {
                    // Clear existing data for tenant
                    DB::table($table)->where('tenant_id', $tenantId)->delete();

                    // Insert backup data
                    foreach ($records as $record) {
                        $recordArray = (array) $record;
                        DB::table($table)->insert($recordArray);
                        $restoredRecords++;
                    }

                    $restoredTables++;
                } catch (\Throwable $e) {
                    Log::error("Failed to restore table {$table}: ".$e->getMessage());
                }
            }

            return [
                'success' => true,
                'tables_restored' => $restoredTables,
                'records_restored' => $restoredRecords,
            ];

        } catch (\Throwable $e) {
            Log::error('Restore failed: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get backup history
     */
    public function getBackupHistory(int $limit = 50): array
    {
        $tenantId = auth()->user()->tenant_id;

        return AutomatedBackup::where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Delete old backups
     */
    public function cleanupOldBackups(): int
    {
        $deleted = 0;

        $expiredBackups = AutomatedBackup::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredBackups as $backup) {
            $backup->deleteFile();
            $deleted++;
        }

        return $deleted;
    }

    /**
     * Get important tables for backup
     */
    protected function getImportantTables(): array
    {
        return [
            'products',
            'customers',
            'invoices',
            'payments',
            'purchase_orders',
            'inventory_items',
            'users',
            'roles',
            'permissions',
            'settings',
        ];
    }

    /**
     * Get expiry date based on backup type
     */
    protected function getExpiryDate(string $type): Carbon
    {
        return match ($type) {
            'daily' => now()->addDays(7),
            'weekly' => now()->addWeeks(4),
            'monthly' => now()->addMonths(3),
            default => now()->addDays(30)
        };
    }
}
