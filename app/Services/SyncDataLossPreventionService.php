<?php

namespace App\Services;

use App\Models\MarketplaceSyncLog;
use App\Models\EcommerceChannel;
use App\Models\EcommerceProductMapping;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SyncDataLossPreventionService - Track and prevent marketplace sync data loss
 * 
 * BUG-API-003 FIX: Comprehensive sync failure tracking with data validation
 * 
 * Problems Fixed:
 * 1. Sync failures not tracked - silent data loss
 * 2. No before/after validation - can't detect data corruption
 * 3. Missing response logging - can't debug failures
 * 4. Partial sync failures not detected
 * 5. No data reconciliation mechanism
 */
class SyncDataLossPreventionService
{
    /**
     * BUG-API-003 FIX: Track sync attempt with full audit trail
     * 
     * @param EcommerceChannel $channel
     * @param string $type (stock, price, product, order)
     * @param array $items Items being synced
     * @param callable $syncFunction Function that performs actual sync
     * @return array
     */
    public function trackSyncAttempt(
        EcommerceChannel $channel,
        string $type,
        array $items,
        callable $syncFunction
    ): array {
        $syncId = 'sync_' . $type . '_' . $channel->id . '_' . now()->timestamp;

        Log::info('BUG-API-003: Sync started', [
            'sync_id' => $syncId,
            'channel_id' => $channel->id,
            'platform' => $channel->platform,
            'type' => $type,
            'items_count' => count($items),
        ]);

        $result = [
            'sync_id' => $syncId,
            'total' => count($items),
            'success' => 0,
            'failed' => 0,
            'errors' => [],
            'data_loss_detected' => false,
            'validation_results' => [],
        ];

        try {
            // Validate before sync
            $beforeValidation = $this->validateBeforeSync($channel, $type, $items);
            $result['validation_results']['before'] = $beforeValidation;

            if (!$beforeValidation['valid']) {
                Log::error('BUG-API-003: Pre-sync validation failed', [
                    'sync_id' => $syncId,
                    'errors' => $beforeValidation['errors'],
                ]);

                $this->createSyncLog($channel, $type, 'validation_failed', [
                    'total_items' => count($items),
                    'validation_errors' => $beforeValidation['errors'],
                ], null, $beforeValidation['errors'][0] ?? 'Pre-sync validation failed');

                return array_merge($result, [
                    'failed' => count($items),
                    'errors' => $beforeValidation['errors'],
                ]);
            }

            // Execute sync function
            $syncResult = $syncFunction();

            // Validate after sync
            $afterValidation = $this->validateAfterSync($channel, $type, $items, $syncResult);
            $result['validation_results']['after'] = $afterValidation;

            // Detect data loss
            $dataLoss = $this->detectDataLoss($beforeValidation, $afterValidation, $syncResult);
            $result['data_loss_detected'] = $dataLoss['detected'];

            if ($dataLoss['detected']) {
                Log::error('BUG-API-003: Data loss detected during sync', [
                    'sync_id' => $syncId,
                    'details' => $dataLoss['details'],
                ]);
            }

            // Process results
            if (isset($syncResult['success']) && isset($syncResult['failed'])) {
                $result['success'] = $syncResult['success'];
                $result['failed'] = $syncResult['failed'];
                $result['errors'] = $syncResult['errors'] ?? [];

                // Log individual failures
                foreach ($syncResult['errors'] as $index => $error) {
                    $failedItem = $items[$index] ?? null;
                    if ($failedItem) {
                        $this->createSyncLog(
                            $channel,
                            $type,
                            'failed',
                            $failedItem,
                            $syncResult['response'] ?? null,
                            $error
                        );
                    }
                }
            }

            // Log overall result
            if ($result['failed'] > 0) {
                Log::warning('BUG-API-003: Sync completed with failures', [
                    'sync_id' => $syncId,
                    'success' => $result['success'],
                    'failed' => $result['failed'],
                    'data_loss' => $dataLoss['detected'],
                ]);
            }

            return $result;

        } catch (\Throwable $e) {
            Log::error('BUG-API-003: Sync crashed', [
                'sync_id' => $syncId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Log all items as failed
            foreach ($items as $item) {
                $this->createSyncLog(
                    $channel,
                    $type,
                    'error',
                    $item,
                    null,
                    'Sync crashed: ' . $e->getMessage()
                );
            }

            return array_merge($result, [
                'failed' => count($items),
                'errors' => ['Sync crashed: ' . $e->getMessage()],
                'data_loss_detected' => true,
            ]);
        }
    }

    /**
     * Validate data before sync
     */
    protected function validateBeforeSync(EcommerceChannel $channel, string $type, array $items): array
    {
        $errors = [];
        $valid = true;

        // Check channel is active
        if (!$channel->is_active) {
            $errors[] = 'Channel is not active';
            $valid = false;
        }

        // Check credentials
        if (empty($channel->api_key) || empty($channel->access_token)) {
            $errors[] = 'Missing API credentials';
            $valid = false;
        }

        // Validate items
        foreach ($items as $index => $item) {
            $mapping = $item['mapping'] ?? null;
            if (!$mapping) {
                $errors[] = "Item {$index}: Missing product mapping";
                $valid = false;
                continue;
            }

            if ($type === 'stock') {
                $stock = ProductStock::where('product_id', $mapping->product_id)->sum('quantity');
                if ($stock < 0) {
                    $errors[] = "Item {$index}: Negative stock ({$stock})";
                    $valid = false;
                }
            }

            if ($type === 'price') {
                $price = $mapping->price_override ?? $mapping->product->price_sell ?? 0;
                if ($price <= 0) {
                    $errors[] = "Item {$index}: Invalid price ({$price})";
                    $valid = false;
                }
            }
        }

        return [
            'valid' => $valid,
            'errors' => $errors,
            'items_count' => count($items),
        ];
    }

    /**
     * Validate data after sync
     */
    protected function validateAfterSync(EcommerceChannel $channel, string $type, array $items, array $syncResult): array
    {
        $errors = [];
        $warnings = [];

        // Check if all items were processed
        $expectedCount = count($items);
        $actualSuccess = $syncResult['success'] ?? 0;
        $actualFailed = $syncResult['failed'] ?? 0;

        if ($actualSuccess + $actualFailed !== $expectedCount) {
            $errors[] = "Item count mismatch: expected {$expectedCount}, got " . ($actualSuccess + $actualFailed);
        }

        // Check success rate
        if ($expectedCount > 0) {
            $successRate = ($actualSuccess / $expectedCount) * 100;
            if ($successRate < 50) {
                $warnings[] = "Low success rate: {$successRate}%";
            }
        }

        // Check for silent failures (no error but no success either)
        if ($actualSuccess === 0 && $actualFailed === 0 && $expectedCount > 0) {
            $errors[] = 'Silent failure: no items processed';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'success_count' => $actualSuccess,
            'failed_count' => $actualFailed,
        ];
    }

    /**
     * Detect data loss during sync
     */
    protected function detectDataLoss(array $before, array $after, array $syncResult): array
    {
        $detected = false;
        $details = [];

        // Check 1: Items disappeared
        if ($before['items_count'] > 0 && $after['success_count'] === 0) {
            $detected = true;
            $details[] = 'All items failed to sync - possible data loss';
        }

        // Check 2: High failure rate
        if ($before['items_count'] > 10) {
            $failureRate = ($after['failed_count'] / $before['items_count']) * 100;
            if ($failureRate > 80) {
                $detected = true;
                $details[] = "Critical failure rate: {$failureRate}%";
            }
        }

        // Check 3: Validation errors increased
        if (count($after['errors']) > count($before['errors']) + 2) {
            $detected = true;
            $details[] = 'Multiple validation errors after sync';
        }

        return [
            'detected' => $detected,
            'details' => $details,
        ];
    }

    /**
     * Create detailed sync log entry
     */
    protected function createSyncLog(
        EcommerceChannel $channel,
        string $type,
        string $status,
        ?array $payload,
        ?array $response,
        ?string $errorMessage = null
    ): void {
        try {
            MarketplaceSyncLog::create([
                'tenant_id' => $channel->tenant_id,
                'channel_id' => $channel->id,
                'mapping_id' => $payload['mapping_id'] ?? $payload['mapping']?->id ?? null,
                'type' => $type,
                'status' => $status,
                'error_message' => $errorMessage,
                'attempt_count' => 1,
                'next_retry_at' => in_array($status, ['failed', 'error']) ? now()->addMinutes(5) : null,
                'payload' => $payload,
                'response' => $response,
            ]);
        } catch (\Throwable $e) {
            Log::error('BUG-API-003: Failed to create sync log', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get sync failure statistics
     */
    public function getSyncFailureStats(int $tenantId, int $days = 7): array
    {
        $since = now()->subDays($days);

        $total = MarketplaceSyncLog::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $since)
            ->count();

        $failed = MarketplaceSyncLog::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $since)
            ->whereIn('status', ['failed', 'error', 'validation_failed'])
            ->count();

        $dataLoss = MarketplaceSyncLog::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $since)
            ->where('status', 'error')
            ->count();

        $byType = MarketplaceSyncLog::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $since)
            ->groupBy('type')
            ->selectRaw('type, COUNT(*) as total, SUM(CASE WHEN status IN ("failed", "error") THEN 1 ELSE 0 END) as failed')
            ->get()
            ->mapWithKeys(fn($row) => [$row->type => ['total' => $row->total, 'failed' => $row->failed]]);

        $byChannel = MarketplaceSyncLog::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $since)
            ->groupBy('channel_id')
            ->selectRaw('channel_id, COUNT(*) as total, SUM(CASE WHEN status IN ("failed", "error") THEN 1 ELSE 0 END) as failed')
            ->get()
            ->mapWithKeys(fn($row) => [$row->channel_id => ['total' => $row->total, 'failed' => $row->failed]]);

        return [
            'period_days' => $days,
            'total_syncs' => $total,
            'failed_syncs' => $failed,
            'data_loss_events' => $dataLoss,
            'failure_rate' => $total > 0 ? round(($failed / $total) * 100, 2) : 0,
            'by_type' => $byType,
            'by_channel' => $byChannel,
        ];
    }

    /**
     * Get failed syncs that need retry
     */
    public function getPendingRetries(int $tenantId, int $limit = 50): array
    {
        return MarketplaceSyncLog::where('tenant_id', $tenantId)
            ->whereIn('status', ['failed', 'error'])
            ->where(function ($q) {
                $q->whereNull('next_retry_at')
                    ->orWhere('next_retry_at', '<=', now());
            })
            ->where('attempt_count', '<', 5) // Max 5 retries
            ->with(['channel', 'mapping'])
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
