<?php

namespace Database\Seeders;

use App\Models\GeofenceZone;
use App\Models\NetworkDevice;
use App\Models\GeofenceAlert;
use App\Models\LocationHistory;
use App\Models\MobileDeviceTrack;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GeofencingSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            $this->command->error('No tenant found. Please run main seeder first.');
            return;
        }

        $tenantId = $tenant->id;
        $this->command->info("Creating geofencing sample data for tenant ID: {$tenantId}");

        // Get devices with coordinates
        $devices = NetworkDevice::where('tenant_id', $tenantId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        if ($devices->count() === 0) {
            $this->command->warn('No devices with coordinates found. Running device location seeder first...');
            $this->call(TelecomDeviceLocationSeeder::class);
            $devices = NetworkDevice::where('tenant_id', $tenantId)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get();
        }

        // Create Geofence Zones
        $this->command->info('Creating geofence zones...');

        $zone1 = GeofenceZone::create([
            'tenant_id' => $tenantId,
            'name' => 'Jakarta Operational Area',
            'description' => 'Main operational zone for Jakarta devices',
            'zone_type' => 'circular',
            'center_latitude' => -6.1805,
            'center_longitude' => 106.8283,
            'radius_meters' => 5000, // 5km radius
            'is_active' => true,
            'alert_settings' => [
                'enabled' => true,
                'channels' => ['database', 'email'],
                'email_recipients' => ['admin@example.com'],
            ],
        ]);

        $zone2 = GeofenceZone::create([
            'tenant_id' => $tenantId,
            'name' => 'Tangerang Coverage Zone',
            'description' => 'Coverage area for Tangerang region',
            'zone_type' => 'circular',
            'center_latitude' => -6.1781,
            'center_longitude' => 106.6319,
            'radius_meters' => 8000, // 8km radius
            'is_active' => true,
            'alert_settings' => [
                'enabled' => true,
                'channels' => ['database'],
            ],
        ]);

        $zone3 = GeofenceZone::create([
            'tenant_id' => $tenantId,
            'name' => 'Bekasi Restricted Zone',
            'description' => 'Restricted area - alert on exit',
            'zone_type' => 'circular',
            'center_latitude' => -6.2349,
            'center_longitude' => 106.9896,
            'radius_meters' => 3000, // 3km radius
            'is_active' => true,
            'alert_settings' => [
                'enabled' => true,
                'channels' => ['database', 'webhook'],
                'webhook_url' => 'https://example.com/webhook/geofence',
            ],
        ]);

        $this->command->info("✓ Created 3 geofence zones");

        // Assign devices to zones
        $this->command->info('Assigning devices to zones...');

        if ($devices->count() > 0) {
            $zone1->devices()->attach($devices->take(3)->pluck('id')->toArray(), [
                'alert_type' => 'both',
                'is_enabled' => true,
            ]);

            if ($devices->count() > 3) {
                $zone2->devices()->attach($devices->slice(3, 2)->pluck('id')->toArray(), [
                    'alert_type' => 'exit',
                    'is_enabled' => true,
                ]);
            }

            if ($devices->count() > 5) {
                $zone3->devices()->attach($devices->slice(5, 2)->pluck('id')->toArray(), [
                    'alert_type' => 'both',
                    'is_enabled' => true,
                ]);
            }
        }

        $this->command->info("✓ Devices assigned to zones");

        // Create sample location history
        $this->command->info('Creating location history...');

        if ($devices->count() > 0) {
            $device = $devices->first();

            for ($i = 24; $i >= 0; $i--) {
                LocationHistory::create([
                    'tenant_id' => $tenantId,
                    'device_id' => $device->id,
                    'latitude' => $device->latitude + (rand(-50, 50) / 100000),
                    'longitude' => $device->longitude + (rand(-50, 50) / 100000),
                    'accuracy_meters' => rand(5, 20),
                    'altitude_meters' => rand(10, 50),
                    'speed_kmh' => rand(0, 60),
                    'heading_degrees' => rand(0, 360),
                    'source' => ['gps', 'api', 'mobile_app'][array_rand(['gps', 'api', 'mobile_app'])],
                    'metadata' => ['note' => 'Sample tracking data'],
                    'recorded_at' => now()->subHours($i),
                ]);
            }
        }

        $this->command->info("✓ Created location history records");

        // Create sample mobile tracks
        $this->command->info('Creating mobile device tracks...');

        if ($devices->count() > 1) {
            $device = $devices->skip(1)->first();
            $sessionId = 'session-' . Str::uuid();

            for ($i = 0; $i < 50; $i++) {
                MobileDeviceTrack::create([
                    'tenant_id' => $tenantId,
                    'device_id' => $device->id,
                    'session_id' => $sessionId,
                    'latitude' => $device->latitude + ($i * 0.0001),
                    'longitude' => $device->longitude + ($i * 0.0001),
                    'accuracy_meters' => rand(5, 15),
                    'battery_level' => max(0, 100 - ($i * 2)),
                    'network_type' => ['wifi', '4g', '5g'][array_rand(['wifi', '4g', '5g'])],
                    'route_metadata' => ['route_name' => 'Sample Route'],
                    'tracked_at' => now()->subMinutes(50 - $i),
                ]);
            }
        }

        $this->command->info("✓ Created mobile tracking session");

        // Create sample geofence alerts
        $this->command->info('Creating sample alerts...');

        if ($devices->count() > 0) {
            $device = $devices->first();

            GeofenceAlert::create([
                'tenant_id' => $tenantId,
                'device_id' => $device->id,
                'zone_id' => $zone1->id,
                'event_type' => 'enter',
                'latitude' => $device->latitude,
                'longitude' => $device->longitude,
                'distance_from_center_meters' => rand(100, 1000),
                'message' => "Device '{$device->name}' entered zone '{$zone1->name}'",
                'is_notified' => true,
                'triggered_at' => now()->subHours(2),
            ]);

            GeofenceAlert::create([
                'tenant_id' => $tenantId,
                'device_id' => $device->id,
                'zone_id' => $zone1->id,
                'event_type' => 'exit',
                'latitude' => $device->latitude + 0.01,
                'longitude' => $device->longitude + 0.01,
                'distance_from_center_meters' => rand(5000, 6000),
                'message' => "Device '{$device->name}' exited zone '{$zone1->name}'",
                'is_notified' => true,
                'triggered_at' => now()->subHour(),
            ]);
        }

        $this->command->info("✓ Created sample alerts");

        $this->command->info("\n✅ Geofencing sample data created successfully!");
        $this->command->info("\nSample Data Summary:");
        $this->command->table(
            ['Type', 'Count'],
            [
                ['Geofence Zones', GeofenceZone::where('tenant_id', $tenantId)->count()],
                ['Location History', LocationHistory::where('tenant_id', $tenantId)->count()],
                ['Mobile Tracks', MobileDeviceTrack::where('tenant_id', $tenantId)->count()],
                ['Geofence Alerts', GeofenceAlert::where('tenant_id', $tenantId)->count()],
            ]
        );
    }
}
