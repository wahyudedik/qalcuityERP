<?php

namespace App\Jobs;

use App\Models\MarketplaceSyncLog;
use App\Models\EcommerceChannel;
use App\Models\EcommerceProductMapping;
use App\Models\ErpNotification;
use App\Models\User;
use App\Services\MarketplaceSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetryFailedMarketplaceSyncs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $failedLogs = MarketplaceSyncLog::where('status', 'failed')
            ->where('next_retry_at', '<=', now())
            ->where('attempt_count', '<', 5)
            ->with(['channel', 'mapping.product'])
            ->limit(50) // Process max 50 per run
            ->get();

        if ($failedLogs->isEmpty()) {
            return;
        }

        $service = app(MarketplaceSyncService::class);

        foreach ($failedLogs as $log) {
            try {
                if (!$log->channel || !$log->channel->is_active) {
                    $log->update(['status' => 'abandoned', 'error_message' => 'Channel inactive or deleted']);
                    continue;
                }

                if (!$log->mapping || !$log->mapping->is_active) {
                    $log->update(['status' => 'abandoned', 'error_message' => 'Mapping inactive or deleted']);
                    continue;
                }

                // Re-attempt based on type
                $success = false;
                if ($log->type === 'stock') {
                    $result = $service->syncStock($log->channel);
                    $success = ($result['failed'] ?? 0) === 0;
                } elseif ($log->type === 'price') {
                    $result = $service->syncPrices($log->channel);
                    $success = ($result['failed'] ?? 0) === 0;
                }

                if ($success) {
                    $log->update(['status' => 'success']);
                    Log::info("Retry succeeded for sync log #{$log->id}");
                } else {
                    $this->markRetryFailed($log);
                }
            } catch (\Throwable $e) {
                $this->markRetryFailed($log, $e->getMessage());
                Log::error("Retry failed for sync log #{$log->id}: {$e->getMessage()}");
            }
        }
    }

    private function markRetryFailed(MarketplaceSyncLog $log, ?string $errorMessage = null): void
    {
        $newAttempt = $log->attempt_count + 1;

        // Exponential backoff: 10s, 30s, 90s, 270s, 810s
        $delays = [10, 30, 90, 270, 810];
        $delaySeconds = $delays[min($newAttempt - 1, count($delays) - 1)];

        if ($newAttempt >= 5) {
            $log->update([
                'status'        => 'abandoned',
                'attempt_count' => $newAttempt,
                'error_message' => $errorMessage ?? $log->error_message,
                'next_retry_at' => null,
            ]);

            // Notify admin
            $admin = User::where('tenant_id', $log->tenant_id)
                ->whereHas('roles', fn($q) => $q->where('name', 'admin'))
                ->first();

            if ($admin) {
                ErpNotification::create([
                    'tenant_id' => $log->tenant_id,
                    'user_id'   => $admin->id,
                    'type'      => 'marketplace_sync',
                    'title'     => 'Sync Marketplace Gagal Permanen',
                    'body'      => "Sync {$log->type} untuk mapping #{$log->mapping_id} gagal setelah {$newAttempt} percobaan.",
                    'data'      => json_encode(['log_id' => $log->id, 'type' => $log->type]),
                ]);
            }

            Log::warning("Sync log #{$log->id} abandoned after {$newAttempt} attempts");
        } else {
            $log->update([
                'status'        => 'failed',
                'attempt_count' => $newAttempt,
                'error_message' => $errorMessage ?? $log->error_message,
                'next_retry_at' => now()->addSeconds($delaySeconds),
            ]);
        }
    }
}
