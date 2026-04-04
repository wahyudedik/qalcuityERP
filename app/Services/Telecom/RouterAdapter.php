<?php

namespace App\Services\Telecom;

use App\Models\NetworkDevice;
use Illuminate\Support\Facades\Log;

/**
 * Abstract base class for router adapters.
 * 
 * All router brand implementations must extend this class
 * and implement the required methods.
 */
abstract class RouterAdapter
{
    protected NetworkDevice $device;
    protected array $config;

    public function __construct(NetworkDevice $device)
    {
        $this->device = $device;
        $this->config = $device->configuration ?? [];
    }

    /**
     * Test connection to router.
     * 
     * @return bool True if connection successful
     */
    abstract public function testConnection(): bool;

    /**
     * Get router system information.
     * 
     * @return array System info (version, uptime, cpu, memory, etc)
     */
    abstract public function getSystemInfo(): array;

    /**
     * Create a hotspot user.
     * 
     * @param string $username
     * @param string $password
     * @param array $profile Bandwidth profile settings
     * @return bool Success status
     */
    abstract public function createHotspotUser(string $username, string $password, array $profile = []): bool;

    /**
     * Update hotspot user.
     * 
     * @param string $username
     * @param array $updates Fields to update
     * @return bool Success status
     */
    abstract public function updateHotspotUser(string $username, array $updates): bool;

    /**
     * Remove hotspot user.
     * 
     * @param string $username
     * @return bool Success status
     */
    abstract public function removeHotspotUser(string $username): bool;

    /**
     * Get active hotspot users.
     * 
     * @return array List of active users with session info
     */
    abstract public function getActiveUsers(): array;

    /**
     * Get user usage statistics.
     * 
     * @param string $username
     * @return array Usage data (bytes in/out, uptime, etc)
     */
    abstract public function getUserUsage(string $username): array;

    /**
     * Create or update bandwidth queue/profile.
     * 
     * @param string $name Profile name
     * @param int $maxDownloadKbps Max download speed in Kbps
     * @param int $maxUploadKbps Max upload speed in Kbps
     * @param array $options Additional options (burst, priority, etc)
     * @return bool Success status
     */
    abstract public function setBandwidthProfile(string $name, int $maxDownloadKbps, int $maxUploadKbps, array $options = []): bool;

    /**
     * Remove bandwidth profile.
     * 
     * @param string $name
     * @return bool Success status
     */
    abstract public function removeBandwidthProfile(string $name): bool;

    /**
     * Get interface statistics.
     * 
     * @param string|null $interface Specific interface name (null for all)
     * @return array Interface stats (traffic, errors, etc)
     */
    abstract public function getInterfaceStats(?string $interface = null): array;

    /**
     * Reboot the router.
     * 
     * @return bool Success status
     */
    abstract public function reboot(): bool;

    /**
     * Execute custom command (for advanced operations).
     * 
     * @param string $command
     * @return mixed Command result
     */
    abstract public function executeCommand(string $command);

    /**
     * Get device brand identifier.
     * 
     * @return string Brand name (mikrotik, ubiquiti, openwrt, etc)
     */
    abstract public function getBrand(): string;

    /**
     * Disconnect user session.
     * 
     * @param string $username
     * @return bool Success status
     */
    abstract public function disconnectUser(string $username): bool;

    /**
     * Get DHCP leases.
     * 
     * @return array Active DHCP leases
     */
    abstract public function getDhcpLeases(): array;

    /**
     * Check if user is currently online.
     * 
     * @param string $username
     * @return bool Online status
     */
    public function isUserOnline(string $username): bool
    {
        $activeUsers = $this->getActiveUsers();

        foreach ($activeUsers as $user) {
            if ($user['username'] === $username || $user['name'] === $username) {
                return true;
            }
        }

        return false;
    }

    /**
     * Format bytes to human readable.
     * 
     * @param int $bytes
     * @return string Formatted string
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

    /**
     * Log adapter activity.
     * 
     * @param string $level
     * @param string $message
     * @param array $context
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        Log::channel('daily')->$level(
            "[Router Adapter][{$this->getBrand()}] {$message}",
            array_merge(['device_id' => $this->device->id], $context)
        );
    }
}
