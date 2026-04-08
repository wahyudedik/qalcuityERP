<?php

namespace App\Jobs\Integrations;

use App\Models\Integration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Integration instance
     */
    protected Integration $integration;

    /**
     * Job timeout in seconds
     */
    public $timeout = 300; // 5 minutes

    /**
     * Max exceptions before failing
     */
    public $tries = 3;

    /**
     * Backoff in seconds
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(Integration $integration)
    {
        $this->integration = $integration;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting product sync job', [
            'integration' => $this->integration->slug,
            'tenant_id' => $this->integration->tenant_id,
        ]);

        try {
            // Get connector instance
            $connectorClass = $this->integration->getConnectorClass();
            $connector = new $connectorClass($this->integration);

            // Check if connected
            if (!$connector->isConnected()) {
                Log::error('Integration not connected', [
                    'integration' => $this->integration->slug,
                ]);
                return;
            }

            // Sync products
            $result = $connector->syncProducts();

            // Update last sync time
            $this->integration->updateLastSync();

            // Schedule next sync
            $this->scheduleNextSync();

            Log::info('Product sync job completed', [
                'integration' => $this->integration->slug,
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('Product sync job failed', [
                'integration' => $this->integration->slug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Schedule next sync based on frequency
     */
    protected function scheduleNextSync(): void
    {
        $frequency = $this->integration->sync_frequency;

        $nextSyncAt = match ($frequency) {
            'realtime' => now()->addMinutes(5),
            'hourly' => now()->addHour(),
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            default => now()->addHour(),
        };

        $this->integration->update(['next_sync_at' => $nextSyncAt]);
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        $this->integration->markAsError();

        Log::critical('Product sync job failed permanently', [
            'integration' => $this->integration->slug,
            'error' => $exception->getMessage(),
        ]);
    }
}
