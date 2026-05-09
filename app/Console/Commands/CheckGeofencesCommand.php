<?php

namespace App\Console\Commands;

use App\Models\NetworkDevice;
use App\Services\Telecom\GeofencingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckGeofencesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geofencing:check
                            {--device-id= : Check specific device only}
                            {--tenant-id= : Check devices for specific tenant}
                            {--send-notifications : Force send notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all devices for geofence violations and create alerts';

    protected GeofencingService $geofencingService;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->geofencingService = new GeofencingService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting geofence check...');

        $deviceId = $this->option('device-id');
        $tenantId = $this->option('tenant-id');

        try {
            if ($deviceId) {
                // Check specific device
                $this->checkSpecificDevice($deviceId);
            } elseif ($tenantId) {
                // Check specific tenant
                $this->checkTenantDevices($tenantId);
            } else {
                // Check all devices
                $this->checkAllDevices();
            }

            $this->info('Geofence check completed successfully!');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Geofence check failed: '.$e->getMessage());
            Log::error('Geofence check command failed: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Check all devices across all tenants.
     */
    protected function checkAllDevices(): void
    {
        $this->info('Checking all devices...');

        $results = $this->geofencingService->checkAllDevices();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Devices Checked', $results['total_checked']],
                ['Alerts Created', $results['alerts_created']],
                ['Devices with Alerts', $results['devices_with_alerts']],
            ]
        );

        if ($results['alerts_created'] > 0) {
            $this->warn("⚠️  {$results['alerts_created']} geofence alert(s) created!");
        } else {
            $this->info('✅ No geofence violations detected.');
        }
    }

    /**
     * Check devices for specific tenant.
     */
    protected function checkTenantDevices(int $tenantId): void
    {
        $this->info("Checking devices for tenant ID: {$tenantId}");

        $devicesOutside = $this->geofencingService->getDevicesOutsideZones($tenantId);
        $devicesInside = $this->geofencingService->getDevicesInsideZones($tenantId);

        $this->info('Devices inside zones: '.count($devicesInside));

        if (count($devicesOutside) > 0) {
            $this->warn('Devices outside zones: '.count($devicesOutside));

            foreach ($devicesOutside as $item) {
                $device = $item['device'];
                $this->error("❌ Device: {$device->name} ({$device->ip_address})");

                foreach ($item['outside_zones'] as $zone) {
                    $this->line("   - Outside zone: {$zone['zone_name']}");
                    $this->line('     Distance from center: '.round($zone['distance_from_center'] / 1000, 2).' km');
                }
            }
        } else {
            $this->info('✅ All devices are within their assigned zones.');
        }
    }

    /**
     * Check specific device.
     */
    protected function checkSpecificDevice(int $deviceId): void
    {
        $this->info("Checking device ID: {$deviceId}");

        $device = NetworkDevice::with('geofenceZones')->find($deviceId);

        if (! $device) {
            $this->error('Device not found!');

            return;
        }

        if (! $device->hasCoordinates()) {
            $this->warn('Device has no coordinates. Skipping geofence check.');

            return;
        }

        if ($device->geofenceZones->count() === 0) {
            $this->warn('Device has no assigned geofence zones.');

            return;
        }

        $this->info("Device: {$device->name}");
        $this->info("Location: {$device->latitude}, {$device->longitude}");
        $this->info("Assigned zones: {$device->geofenceZones->count()}");

        $result = $this->geofencingService->checkDeviceGeofences($device);

        if ($result['alerts_created'] > 0) {
            $this->warn("⚠️  {$result['alerts_created']} alert(s) created:");
            foreach ($result['alerts'] as $alert) {
                $this->line("   - {$alert->message}");
            }
        } else {
            $this->info('✅ No geofence violations detected.');
        }

        // Show zone status
        foreach ($device->geofenceZones as $zone) {
            $isInside = $zone->containsPoint($device->latitude, $device->longitude);
            $distance = $zone->getDistanceFromCenter($device->latitude, $device->longitude);

            $status = $isInside ? '✅ Inside' : '❌ Outside';
            $distanceKm = round($distance / 1000, 2);

            $this->line("Zone: {$zone->name} - {$status} ({$distanceKm} km from center)");
        }
    }
}
