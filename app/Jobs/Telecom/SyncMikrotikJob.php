<?php

namespace App\Jobs\Telecom;

use App\Models\NetworkDevice;
use App\Services\Telecom\RouterAdapterFactory;
use App\Services\Telecom\RouterIntegrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

/**
 * SyncMikrotikJob
 *
 * Syncs usage data from a MikroTik router device.
 *
 * BUG-TELECOM-001 FIX (Bug 1.21): Added graceful timeout handling and
 * exponential backoff. ConnectionException is caught and the job is
 * released back to the queue with increasing delays instead of failing
 * permanently. Non-connection errors are re-thrown so they surface properly.
 *
 * Bug Condition: module = 'telecom' AND NOT gracefulTimeout(input) — job fail tanpa retry
 * Expected Behavior: job di-release dengan backoff, tidak fail permanen karena timeout
 */
class SyncMikrotikJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum number of attempts before the job is marked as failed.
     */
    public int $tries = 5;

    /**
     * Exponential backoff delays in seconds between retries.
     * Attempt 1 → 30s, 2 → 60s, 3 → 120s, 4 → 300s, 5 → 600s
     */
    public array $backoff = [30, 60, 120, 300, 600];

    /**
     * Job-level timeout (seconds). Prevents the worker from hanging indefinitely.
     */
    public int $timeout = 60;

    public function __construct(
        protected int $deviceId
    ) {
        $this->queue = 'telecom-sync';
    }

    /**
     * Execute the job.
     *
     * @throws \Throwable for non-connection errors
     */
    public function handle(RouterIntegrationService $integrationService): void
    {
        $device = NetworkDevice::findOrFail($this->deviceId);

        try {
            // Verify connectivity with a short timeout before syncing
            $adapter = RouterAdapterFactory::create($device);

            // Sync usage data — the adapter uses Http::timeout() internally;
            // ConnectionException is thrown when the host is unreachable or times out.
            $syncedCount = $integrationService->syncUsageData($device);

            Log::info("MikroTik sync completed for device {$device->name} (ID: {$this->deviceId})", [
                'synced_records' => $syncedCount,
            ]);

        } catch (ConnectionException $e) {
            // Graceful handling: release back to queue with backoff instead of failing
            $attempt = $this->attempts();
            $delay   = $this->backoff[$attempt - 1] ?? 600;

            Log::warning("MikroTik sync connection failed for device {$device->name} (ID: {$this->deviceId}), " .
                "attempt {$attempt}/{$this->tries}. Retrying in {$delay}s.", [
                'error' => $e->getMessage(),
            ]);

            $this->release($delay);

        } catch (\Throwable $e) {
            // Re-throw unexpected errors so they are properly logged and marked as failed
            Log::error("MikroTik sync unexpected error for device {$device->name} (ID: {$this->deviceId})", [
                'error'   => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SyncMikrotikJob permanently failed for device ID {$this->deviceId}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
