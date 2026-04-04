<?php

namespace App\Services\Telecom;

use App\Models\NetworkDevice;
use App\Models\HotspotUser;
use App\Models\UsageTracking;
use App\Models\BandwidthAllocation;
use App\Models\NetworkAlert;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrator service for router operations.
 * 
 * This service provides a high-level interface for common router operations,
 * handling error recovery, logging, and database synchronization.
 */
class RouterIntegrationService
{
    /**
     * Test connection to a device and update status.
     * 
     * @param NetworkDevice $device
     * @return array Test result with details
     */
    public function testDeviceConnection(NetworkDevice $device): array
    {
        try {
            $adapter = RouterAdapterFactory::create($device);
            $isConnected = $adapter->testConnection();

            if ($isConnected) {
                $systemInfo = $adapter->getSystemInfo();

                // Update device info
                $device->update([
                    'firmware_version' => $systemInfo['version'] ?? $device->firmware_version,
                    'status' => 'online',
                    'last_seen_at' => now(),
                ]);

                return [
                    'success' => true,
                    'connected' => true,
                    'system_info' => $systemInfo,
                ];
            }

            return [
                'success' => true,
                'connected' => false,
                'message' => 'Device unreachable',
            ];

        } catch (Exception $e) {
            $device->markAsOffline();

            return [
                'success' => false,
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create hotspot user on router and database.
     * 
     * @param NetworkDevice $device
     * @param string $username
     * @param string $password
     * @param array $options Additional options
     * @return array Result with success status
     */
    public function createHotspotUser(NetworkDevice $device, string $username, string $password, array $options = []): array
    {
        DB::beginTransaction();

        try {
            $adapter = RouterAdapterFactory::create($device);

            // Prepare profile
            $profile = [];

            if (!empty($options['bandwidth_profile'])) {
                $profile['profile'] = $options['bandwidth_profile'];
            } else {
                // Create bandwidth profile if speeds provided
                if (!empty($options['download_speed_kbps']) && !empty($options['upload_speed_kbps'])) {
                    $profileName = "user_{$username}";
                    $adapter->setBandwidthProfile(
                        $profileName,
                        $options['download_speed_kbps'],
                        $options['upload_speed_kbps'],
                        $options['burst_options'] ?? []
                    );
                    $profile['profile'] = $profileName;
                }
            }

            $profile['comment'] = $options['comment'] ?? "Created by Qalcuity ERP";

            // Create on router
            $routerSuccess = $adapter->createHotspotUser($username, $password, $profile);

            if (!$routerSuccess) {
                throw new Exception("Failed to create user on router");
            }

            // Create in database
            $hotspotUser = HotspotUser::create([
                'tenant_id' => $device->tenant_id,
                'device_id' => $device->id,
                'subscription_id' => $options['subscription_id'] ?? null,
                'username' => $username,
                'password' => $password,
                'auth_type' => $options['auth_type'] ?? 'hotspot',
                'is_active' => true,
                'activated_at' => now(),
                'expires_at' => $options['expires_at'] ?? null,
                'rate_limit_download_kbps' => $options['download_speed_kbps'] ?? null,
                'rate_limit_upload_kbps' => $options['upload_speed_kbps'] ?? null,
                'quota_bytes' => $options['quota_bytes'] ?? 0,
            ]);

            DB::commit();

            return [
                'success' => true,
                'user' => $hotspotUser,
                'message' => 'User created successfully',
            ];

        } catch (Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Remove hotspot user from router and database.
     * 
     * @param HotspotUser $hotspotUser
     * @return array Result
     */
    public function removeHotspotUser(HotspotUser $hotspotUser): array
    {
        try {
            $device = $hotspotUser->device;
            $adapter = RouterAdapterFactory::create($device);

            // Remove from router
            $adapter->removeHotspotUser($hotspotUser->username);

            // Soft delete in database
            $hotspotUser->delete();

            return [
                'success' => true,
                'message' => 'User removed successfully',
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sync usage data from router to database.
     * 
     * @param NetworkDevice $device
     * @return int Number of records synced
     */
    public function syncUsageData(NetworkDevice $device): int
    {
        try {
            $adapter = RouterAdapterFactory::create($device);
            $activeUsers = $adapter->getActiveUsers();
            $syncedCount = 0;

            foreach ($activeUsers as $activeUser) {
                $username = $activeUser['user'] ?? $activeUser['name'] ?? null;

                if (!$username) {
                    continue;
                }

                // Get detailed usage
                $usage = $adapter->getUserUsage($username);

                if (empty($usage)) {
                    continue;
                }

                // Find or create usage tracking record
                $hotspotUser = HotspotUser::where('username', $username)
                    ->where('device_id', $device->id)
                    ->first();

                if ($hotspotUser) {
                    // Update online status
                    if (!empty($usage['is_online'])) {
                        $hotspotUser->markAsOnline($usage['ip_address'] ?? '');
                    } else {
                        $hotspotUser->markAsOffline($usage['uptime_seconds'] ?? 0);
                    }

                    // Update quota
                    if ($usage['bytes_total'] > 0) {
                        $hotspotUser->addUsage($usage['bytes_total']);
                    }

                    // Create usage tracking record
                    UsageTracking::create([
                        'tenant_id' => $device->tenant_id,
                        'subscription_id' => $hotspotUser->subscription_id,
                        'device_id' => $device->id,
                        'bytes_in' => $usage['bytes_in'] ?? 0,
                        'bytes_out' => $usage['bytes_out'] ?? 0,
                        'bytes_total' => $usage['bytes_total'] ?? 0,
                        'packets_in' => $usage['packets_in'] ?? 0,
                        'packets_out' => $usage['packets_out'] ?? 0,
                        'session_duration_seconds' => $this->parseUptime($usage['uptime'] ?? '00:00:00'),
                        'period_type' => 'daily',
                        'period_start' => now()->startOfDay(),
                        'period_end' => now()->endOfDay(),
                        'ip_address' => $usage['ip_address'] ?? null,
                        'mac_address' => $usage['mac_address'] ?? null,
                        'additional_data' => $usage,
                    ]);

                    $syncedCount++;
                }
            }

            return $syncedCount;

        } catch (Exception $e) {
            \Log::error("Failed to sync usage data", [
                'device_id' => $device->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Apply bandwidth allocation to router.
     * 
     * @param BandwidthAllocation $allocation
     * @return bool Success status
     */
    public function applyBandwidthAllocation(BandwidthAllocation $allocation): bool
    {
        try {
            $device = $allocation->device;
            $adapter = RouterAdapterFactory::create($device);

            $success = $adapter->setBandwidthProfile(
                $allocation->allocation_name,
                $allocation->max_download_kbps,
                $allocation->max_upload_kbps,
                [
                    'burst_limit_download' => $allocation->queue_parameters['burst_download'] ?? null,
                    'burst_limit_upload' => $allocation->queue_parameters['burst_upload'] ?? null,
                    'burst_threshold' => $allocation->queue_parameters['burst_threshold'] ?? null,
                    'burst_time' => $allocation->queue_parameters['burst_time'] ?? null,
                    'priority' => $allocation->priority,
                ]
            );

            if ($success) {
                $allocation->update(['last_updated_at' => now()]);
            }

            return $success;

        } catch (Exception $e) {
            \Log::error("Failed to apply bandwidth allocation", [
                'allocation_id' => $allocation->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check device health and create alerts if needed.
     * 
     * @param NetworkDevice $device
     * @return array Health check result
     */
    public function checkDeviceHealth(NetworkDevice $device): array
    {
        $result = $this->testDeviceConnection($device);

        if (!$result['connected']) {
            // Create alert for offline device
            $this->createAlert($device, 'device_offline', 'high', [
                'title' => "Device Offline: {$device->name}",
                'message' => "Device {$device->name} ({$device->ip_address}) is not responding.",
            ]);
        } else {
            $systemInfo = $result['system_info'] ?? [];

            // Check CPU load
            if (($systemInfo['cpu_load'] ?? 0) > 80) {
                $this->createAlert($device, 'high_cpu', 'medium', [
                    'title' => "High CPU Usage: {$device->name}",
                    'message' => "CPU usage is at {$systemInfo['cpu_load']}%",
                    'current_metrics' => ['cpu_load' => $systemInfo['cpu_load']],
                ]);
            }

            // Check memory
            $totalMemory = $systemInfo['total_memory'] ?? 0;
            $freeMemory = $systemInfo['free_memory'] ?? 0;

            if ($totalMemory > 0) {
                $memoryUsagePercent = (($totalMemory - $freeMemory) / $totalMemory) * 100;

                if ($memoryUsagePercent > 85) {
                    $this->createAlert($device, 'high_memory', 'medium', [
                        'title' => "High Memory Usage: {$device->name}",
                        'message' => "Memory usage is at " . round($memoryUsagePercent, 2) . "%",
                        'current_metrics' => [
                            'memory_usage_percent' => round($memoryUsagePercent, 2),
                            'free_memory' => $freeMemory,
                            'total_memory' => $totalMemory,
                        ],
                    ]);
                }
            }
        }

        return $result;
    }

    /**
     * Create network alert.
     * 
     * @param NetworkDevice $device
     * @param string $type
     * @param string $severity
     * @param array $data
     */
    protected function createAlert(NetworkDevice $device, string $type, string $severity, array $data): void
    {
        // Check if there's already an unacknowledged alert of same type
        $existingAlert = NetworkAlert::where('device_id', $device->id)
            ->where('alert_type', $type)
            ->whereIn('status', ['new', 'acknowledged'])
            ->where('created_at', '>', now()->subHours(1)) // Don't duplicate within 1 hour
            ->first();

        if (!$existingAlert) {
            NetworkAlert::create([
                'tenant_id' => $device->tenant_id,
                'device_id' => $device->id,
                'alert_type' => $type,
                'severity' => $severity,
                'title' => $data['title'],
                'message' => $data['message'],
                'current_metrics' => $data['current_metrics'] ?? null,
            ]);
        }
    }

    /**
     * Parse uptime string to seconds.
     * 
     * @param string $uptime Uptime string (e.g., "1d02:30:45")
     * @return int Seconds
     */
    protected function parseUptime(string $uptime): int
    {
        // MikroTik format: 1d02:30:45 or 02:30:45
        preg_match('/(?:(\d+)d)?(?:(\d+):)?(\d+):(\d+)/', $uptime, $matches);

        $days = isset($matches[1]) ? (int) $matches[1] : 0;
        $hours = isset($matches[2]) && !empty($matches[2]) ? (int) $matches[2] : 0;
        $minutes = isset($matches[3]) ? (int) $matches[3] : 0;
        $seconds = isset($matches[4]) ? (int) $matches[4] : 0;

        return ($days * 86400) + ($hours * 3600) + ($minutes * 60) + $seconds;
    }
}
