<?php

namespace App\Services;

use App\Models\RestorePoint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RestorePointService
{
    /**
     * Create restore point before major change
     */
    public function createRestorePoint(string $name, string $description = '', string $triggerEvent = 'manual', array $affectedModels = []): array
    {
        $tenantId = auth()->user()->tenant_id;
        $userId = auth()->id();

        try {
            // Capture snapshot of critical data
            $snapshotData = $this->captureSnapshot($affectedModels);

            $restorePoint = RestorePoint::create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'name' => $name,
                'description' => $description,
                'trigger_event' => $triggerEvent,
                'affected_models' => $affectedModels,
                'snapshot_data' => $snapshotData,
                'is_active' => true,
                'expires_at' => now()->addDays(7), // Expire after 7 days
            ]);

            return [
                'success' => true,
                'restore_point_id' => $restorePoint->id,
                'name' => $name,
            ];

        } catch (\Throwable $e) {
            Log::error('Failed to create restore point: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Restore from restore point
     */
    public function restoreFromPoint(int $restorePointId): array
    {
        $tenantId = auth()->user()->tenant_id;

        $restorePoint = RestorePoint::where('tenant_id', $tenantId)
            ->where('id', $restorePointId)
            ->first();

        if (!$restorePoint) {
            return ['success' => false, 'error' => 'Restore point not found'];
        }

        if (!$restorePoint->is_active) {
            return ['success' => false, 'error' => 'Restore point is not active'];
        }

        if ($restorePoint->used) {
            return ['success' => false, 'error' => 'Restore point already used'];
        }

        if ($restorePoint->isExpired()) {
            return ['success' => false, 'error' => 'Restore point has expired'];
        }

        try {
            $snapshotData = $restorePoint->snapshot_data;
            $restoredTables = 0;
            $restoredRecords = 0;

            foreach ($snapshotData as $table => $records) {
                try {
                    // Clear current data
                    DB::table($table)->where('tenant_id', $tenantId)->delete();

                    // Restore snapshot data
                    foreach ($records as $record) {
                        DB::table($table)->insert((array) $record);
                        $restoredRecords++;
                    }

                    $restoredTables++;
                } catch (\Throwable $e) {
                    Log::error("Failed to restore table {$table}: " . $e->getMessage());
                }
            }

            // Mark as used
            $restorePoint->markAsUsed();

            return [
                'success' => true,
                'tables_restored' => $restoredTables,
                'records_restored' => $restoredRecords,
            ];

        } catch (\Throwable $e) {
            Log::error('Restore failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get active restore points
     */
    public function getActiveRestorePoints(): array
    {
        $tenantId = auth()->user()->tenant_id;

        return RestorePoint::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('used', false)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Capture snapshot of models
     */
    protected function captureSnapshot(array $models): array
    {
        $tenantId = auth()->user()->tenant_id;
        $snapshot = [];

        foreach ($models as $modelClass) {
            try {
                $data = app($modelClass)::where('tenant_id', $tenantId)->get()->toArray();
                $tableName = app($modelClass)::first()->getTable();
                $snapshot[$tableName] = $data;
            } catch (\Throwable $e) {
                Log::warning("Failed to snapshot {$modelClass}: " . $e->getMessage());
            }
        }

        return $snapshot;
    }

    /**
     * Cleanup expired restore points
     */
    public function cleanupExpiredPoints(): int
    {
        return RestorePoint::where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();
    }
}
