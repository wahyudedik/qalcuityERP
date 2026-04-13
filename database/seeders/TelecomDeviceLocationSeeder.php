<?php

namespace Database\Seeders;

use App\Models\NetworkDevice;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TelecomDeviceLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first tenant or use default
        $tenant = Tenant::first();

        if (!$tenant) {
            $this->command->error('No tenant found. Please seed tenants first.');
            return;
        }

        $tenantId = $tenant->id;

        // Sample devices with Jakarta area coordinates
        $devices = [
            [
                'tenant_id' => $tenantId,
                'name' => 'Main Router - Kantor Pusat',
                'device_type' => 'router',
                'brand' => 'mikrotik',
                'model' => 'RB4011iGS+RM',
                'ip_address' => '192.168.1.1',
                'port' => 8728,
                'username' => 'admin',
                'password_encrypted' => encrypt('admin123'),
                'status' => 'online',
                'location' => 'Gedung A Lt.3 - Jakarta Pusat',
                'latitude' => -6.1805000,
                'longitude' => 106.8283000,
                'coverage_radius' => 500,
                'last_seen_at' => now(),
            ],
            [
                'tenant_id' => $tenantId,
                'name' => 'Access Point - Lobby',
                'device_type' => 'access_point',
                'brand' => 'ubiquiti',
                'model' => 'U6-Pro',
                'ip_address' => '192.168.1.10',
                'port' => 443,
                'username' => 'admin',
                'password_encrypted' => encrypt('admin123'),
                'status' => 'online',
                'location' => 'Lobby Utama - Jakarta Pusat',
                'latitude' => -6.1810000,
                'longitude' => 106.8290000,
                'coverage_radius' => 100,
                'last_seen_at' => now(),
            ],
            [
                'tenant_id' => $tenantId,
                'name' => 'Router - Branch Jakarta Selatan',
                'device_type' => 'router',
                'brand' => 'mikrotik',
                'model' => 'RB750Gr3',
                'ip_address' => '192.168.2.1',
                'port' => 8728,
                'username' => 'admin',
                'password_encrypted' => encrypt('admin123'),
                'status' => 'online',
                'location' => 'Kantor Cabang - Jakarta Selatan',
                'latitude' => -6.2605000,
                'longitude' => 106.8106000,
                'coverage_radius' => 1000,
                'last_seen_at' => now(),
            ],
            [
                'tenant_id' => $tenantId,
                'name' => 'Tower Access Point - Blok M',
                'device_type' => 'access_point',
                'brand' => 'mikrotik',
                'model' => 'LHG 5',
                'ip_address' => '192.168.2.50',
                'port' => 8728,
                'username' => 'admin',
                'password_encrypted' => encrypt('admin123'),
                'status' => 'online',
                'location' => 'Tower A - Blok M Plaza',
                'latitude' => -6.2440000,
                'longitude' => 106.7990000,
                'coverage_radius' => 2000,
                'last_seen_at' => now(),
            ],
            [
                'tenant_id' => $tenantId,
                'name' => 'Switch - Server Room',
                'device_type' => 'switch',
                'brand' => 'cisco',
                'model' => 'Catalyst 2960',
                'ip_address' => '192.168.1.5',
                'port' => 443,
                'username' => 'admin',
                'password_encrypted' => encrypt('admin123'),
                'status' => 'online',
                'location' => 'Server Room - Jakarta Pusat',
                'latitude' => -6.1808000,
                'longitude' => 106.8285000,
                'coverage_radius' => null,
                'last_seen_at' => now(),
            ],
            [
                'tenant_id' => $tenantId,
                'name' => 'Router - Branch Bekasi (Offline)',
                'device_type' => 'router',
                'brand' => 'mikrotik',
                'model' => 'RB750Gr3',
                'ip_address' => '192.168.3.1',
                'port' => 8728,
                'username' => 'admin',
                'password_encrypted' => encrypt('admin123'),
                'status' => 'offline',
                'location' => 'Kantor Cabang - Bekasi',
                'latitude' => -6.2349000,
                'longitude' => 106.9896000,
                'coverage_radius' => 1500,
                'last_seen_at' => now()->subHours(3),
            ],
            [
                'tenant_id' => $tenantId,
                'name' => 'Access Point - Maintenance',
                'device_type' => 'access_point',
                'brand' => 'ubiquiti',
                'model' => 'U6-Lite',
                'ip_address' => '192.168.1.20',
                'port' => 443,
                'username' => 'admin',
                'password_encrypted' => encrypt('admin123'),
                'status' => 'maintenance',
                'location' => 'Gedung B Lt.2 - Jakarta Pusat',
                'latitude' => -6.1815000,
                'longitude' => 106.8295000,
                'coverage_radius' => 150,
                'last_seen_at' => now()->subMinutes(30),
            ],
        ];

        foreach ($devices as $deviceData) {
            NetworkDevice::create($deviceData);
        }

        $this->command->info('Sample telecom devices with location data seeded successfully!');
        $this->command->info('Created ' . count($devices) . ' devices for tenant ID: ' . $tenantId);
    }
}
