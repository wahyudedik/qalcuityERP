<?php

namespace App\Services\Telecom;

use App\Models\NetworkDevice;
use App\Models\HotspotUser;
use App\Models\TelecomSubscription;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Service for managing hotspot users.
 */
class HotspotManagementService
{
    protected RouterIntegrationService $integrationService;

    public function __construct()
    {
        $this->integrationService = new RouterIntegrationService();
    }

    /**
     * Create a new hotspot user.
     * 
     * @param NetworkDevice $device
     * @param array $data User data
     * @return array Result with user object or error
     */
    public function createUser(NetworkDevice $device, array $data): array
    {
        $username = $data['username'] ?? Str::random(8);
        $password = $data['password'] ?? Str::random(12);

        $result = $this->integrationService->createHotspotUser(
            $device,
            $username,
            $password,
            [
                'download_speed_kbps' => $data['download_speed_kbps'] ?? null,
                'upload_speed_kbps' => $data['upload_speed_kbps'] ?? null,
                'quota_bytes' => $data['quota_bytes'] ?? 0,
                'expires_at' => $data['expires_at'] ?? null,
                'comment' => $data['comment'] ?? null,
                'subscription_id' => $data['subscription_id'] ?? null,
            ]
        );

        return $result;
    }

    /**
     * Update hotspot user.
     * 
     * @param HotspotUser $user
     * @param array $updates Fields to update
     * @return array Result
     */
    public function updateUser(HotspotUser $user, array $updates): array
    {
        try {
            $adapter = RouterAdapterFactory::create($user->device);

            // Update on router
            $routerUpdates = [];

            if (isset($updates['password'])) {
                $routerUpdates['password'] = $updates['password'];
                $updates['password'] = $updates['password']; // Will be encrypted by model mutator
            }

            if (isset($updates['rate_limit_download_kbps']) || isset($updates['rate_limit_upload_kbps'])) {
                // Update bandwidth profile
                $profileName = "user_{$user->username}";
                $adapter->setBandwidthProfile(
                    $profileName,
                    $updates['rate_limit_download_kbps'] ?? $user->rate_limit_download_kbps,
                    $updates['rate_limit_upload_kbps'] ?? $user->rate_limit_upload_kbps
                );
                $routerUpdates['profile'] = $profileName;
            }

            if (!empty($routerUpdates)) {
                $adapter->updateHotspotUser($user->username, $routerUpdates);
            }

            // Update in database
            $user->update($updates);

            return [
                'success' => true,
                'user' => $user->fresh(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete hotspot user.
     * 
     * @param HotspotUser $user
     * @return array Result
     */
    public function deleteUser(HotspotUser $user): array
    {
        return $this->integrationService->removeHotspotUser($user);
    }

    /**
     * Suspend user temporarily.
     * 
     * @param HotspotUser $user
     * @return bool Success status
     */
    public function suspendUser(HotspotUser $user): bool
    {
        try {
            $adapter = RouterAdapterFactory::create($user->device);

            // Disable user on router
            $adapter->updateHotspotUser($user->username, ['disabled' => 'yes']);

            // Disconnect if online
            if ($user->is_online) {
                $adapter->disconnectUser($user->username);
            }

            // Update database
            $user->update(['is_active' => false]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to suspend user", [
                'username' => $user->username,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Reactivate suspended user.
     * 
     * @param HotspotUser $user
     * @return bool Success status
     */
    public function reactivateUser(HotspotUser $user): bool
    {
        try {
            $adapter = RouterAdapterFactory::create($user->device);

            // Enable user on router
            $adapter->updateHotspotUser($user->username, ['disabled' => 'no']);

            // Update database
            $user->update(['is_active' => true]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to reactivate user", [
                'username' => $user->username,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get user statistics.
     * 
     * @param HotspotUser $user
     * @return array User stats
     */
    public function getUserStats(HotspotUser $user): array
    {
        try {
            $adapter = RouterAdapterFactory::create($user->device);
            $usage = $adapter->getUserUsage($user->username);

            return [
                'username' => $user->username,
                'is_online' => $usage['is_online'] ?? false,
                'ip_address' => $usage['ip_address'] ?? null,
                'mac_address' => $usage['mac_address'] ?? null,
                'bytes_in' => $usage['bytes_in'] ?? 0,
                'bytes_out' => $usage['bytes_out'] ?? 0,
                'bytes_total' => $usage['bytes_total'] ?? 0,
                'bytes_in_formatted' => $this->formatBytes($usage['bytes_in'] ?? 0),
                'bytes_out_formatted' => $this->formatBytes($usage['bytes_out'] ?? 0),
                'bytes_total_formatted' => $this->formatBytes($usage['bytes_total'] ?? 0),
                'uptime' => $usage['uptime'] ?? '00:00:00',
                'quota_used' => $user->quota_used_bytes,
                'quota_limit' => $user->quota_bytes,
                'quota_remaining' => $user->remaining_quota,
                'quota_remaining_formatted' => $user->remaining_quota_formatted,
                'total_sessions' => $user->total_sessions,
                'total_uptime' => $user->total_uptime_seconds,
            ];

        } catch (\Exception $e) {
            return [
                'username' => $user->username,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format bytes to human readable.
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
