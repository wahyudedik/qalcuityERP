<?php

namespace App\Jobs\Telecom;

use App\Models\HotspotUser;
use App\Models\NetworkDevice;
use App\Services\Telecom\RouterAdapterFactory;
use App\Services\Telecom\RouterIntegrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Scheduled job to sync hotspot users between database and router.
 *
 * Run every hour.
 */
class SyncHotspotUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180; // 3 minutes

    public int $tries = 2;

    public function __construct(
        protected ?int $deviceId = null
    ) {
        $this->queue = 'telecom-sync';
    }

    public function handle(RouterIntegrationService $integrationService): void
    {
        Log::info('Starting hotspot users sync');

        $query = NetworkDevice::query()->whereIn('device_type', ['router', 'access_point']);

        if ($this->deviceId) {
            $query->where('id', $this->deviceId);
        }

        $devices = $query->get();

        foreach ($devices as $device) {
            try {
                $this->syncDeviceUsers($device, $integrationService);
            } catch (\Exception $e) {
                Log::error("Failed to sync users for device: {$device->name}", [
                    'device_id' => $device->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Hotspot users sync completed');
    }

    /**
     * Sync users for a specific device.
     */
    protected function syncDeviceUsers(NetworkDevice $device, RouterIntegrationService $integrationService): void
    {
        try {
            $adapter = RouterAdapterFactory::create($device);

            // Get active users from router
            $routerUsers = $adapter->getActiveUsers();
            $routerUsernames = collect($routerUsers)->pluck('user')->filter()->toArray();

            // Update online status in database
            HotspotUser::where('device_id', $device->id)
                ->where('is_online', true)
                ->whereNotIn('username', $routerUsernames)
                ->update([
                    'is_online' => false,
                    'last_logout_at' => now(),
                ]);

            // Mark router users as online
            foreach ($routerUsernames as $username) {
                $user = HotspotUser::where('device_id', $device->id)
                    ->where('username', $username)
                    ->first();

                if ($user && ! $user->is_online) {
                    $user->markAsOnline('');
                }
            }

            Log::info("Synced users for device: {$device->name}", [
                'total_router_users' => count($routerUsernames),
                'device_id' => $device->id,
            ]);

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
