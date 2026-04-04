<?php

namespace App\Jobs\Telecom;

use App\Models\NetworkDevice;
use App\Services\Telecom\RouterIntegrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Scheduled job to poll router usage data.
 * 
 * Run every 5-15 minutes via scheduler.
 */
class PollRouterUsageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120; // 2 minutes timeout
    public int $tries = 3;

    public function __construct(
        protected ?int $deviceId = null
    ) {
        $this->queue = 'telecom-polling';
    }

    public function handle(RouterIntegrationService $integrationService): void
    {
        $query = NetworkDevice::query()->where('status', '!=', 'maintenance');

        if ($this->deviceId) {
            $query->where('id', $this->deviceId);
        }

        $devices = $query->get();

        Log::info("Starting router usage polling for {$devices->count()} devices");

        foreach ($devices as $device) {
            try {
                // Sync usage data from router
                $syncedCount = $integrationService->syncUsageData($device);

                // Check device health
                $integrationService->checkDeviceHealth($device);

                Log::info("Polled device: {$device->name}, synced {$syncedCount} records");

            } catch (\Exception $e) {
                Log::error("Failed to poll device: {$device->name}", [
                    'device_id' => $device->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        Log::info("Router usage polling completed");
    }
}
