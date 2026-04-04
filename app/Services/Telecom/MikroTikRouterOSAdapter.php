<?php

namespace App\Services\Telecom;

use App\Models\NetworkDevice;
use Illuminate\Support\Facades\Http;
use Exception;

/**
 * MikroTik RouterOS API Adapter.
 * 
 * Uses MikroTik REST API (available in RouterOS 7.x+)
 * For older versions, consider using PEAR2_Net_RouterOS library.
 */
class MikroTikRouterOSAdapter extends RouterAdapter
{
    private ?string $sessionId = null;

    public function getBrand(): string
    {
        return 'mikrotik';
    }

    /**
     * Build API base URL.
     */
    protected function getBaseUrl(): string
    {
        $protocol = $this->config['use_https'] ?? false ? 'https' : 'http';
        return "{$protocol}://{$this->device->ip_address}:{$this->device->port}";
    }

    /**
     * Get authentication headers.
     */
    protected function getAuthHeaders(): array
    {
        if ($this->sessionId) {
            return [
                'Cookie' => "session={$this->sessionId}",
            ];
        }

        return [
            'Authorization' => 'Basic ' . base64_encode(
                $this->device->username . ':' . $this->device->decrypted_password
            ),
        ];
    }

    public function testConnection(): bool
    {
        try {
            $response = Http::withHeaders($this->getAuthHeaders())
                ->timeout(5)
                ->get("{$this->getBaseUrl()}/rest/system/resource");

            if ($response->successful()) {
                $this->device->markAsOnline();
                $this->log('info', 'Connection test successful');
                return true;
            }

            $this->log('warning', 'Connection test failed', ['status' => $response->status()]);
            return false;

        } catch (Exception $e) {
            $this->log('error', 'Connection test exception', ['error' => $e->getMessage()]);
            $this->device->markAsOffline();
            return false;
        }
    }

    public function getSystemInfo(): array
    {
        try {
            $resource = Http::withHeaders($this->getAuthHeaders())
                ->get("{$this->getBaseUrl()}/rest/system/resource")
                ->json();

            $identity = Http::withHeaders($this->getAuthHeaders())
                ->get("{$this->getBaseUrl()}/rest/system/identity")
                ->json();

            $clock = Http::withHeaders($this->getAuthHeaders())
                ->get("{$this->getBaseUrl()}/rest/system/clock")
                ->json();

            return [
                'brand' => 'MikroTik',
                'model' => $resource['board-name'] ?? 'Unknown',
                'version' => $resource['version'] ?? 'Unknown',
                'identity' => $identity[0]['name'] ?? 'Unknown',
                'uptime' => $resource['uptime'] ?? 'Unknown',
                'cpu_load' => $resource['cpu-load'] ?? 0,
                'free_memory' => $resource['free-memory'] ?? 0,
                'total_memory' => $resource['total-memory'] ?? 0,
                'free_hdd_space' => $resource['free-hdd-space'] ?? 0,
                'total_hdd_space' => $resource['total-hdd-space'] ?? 0,
                'cpu_count' => $resource['cpu-count'] ?? 0,
                'date_time' => $clock[0] ?? null,
                'architecture' => $resource['architecture-name'] ?? 'Unknown',
            ];

        } catch (Exception $e) {
            $this->log('error', 'Failed to get system info', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function createHotspotUser(string $username, string $password, array $profile = []): bool
    {
        try {
            $payload = [
                'name' => $username,
                'password' => $password,
            ];

            // Add profile if provided
            if (!empty($profile['profile'])) {
                $payload['profile'] = $profile['profile'];
            }

            // Add comment
            if (!empty($profile['comment'])) {
                $payload['comment'] = $profile['comment'];
            }

            // Add disabled status
            $payload['disabled'] = $profile['disabled'] ?? 'no';

            $response = Http::withHeaders($this->getAuthHeaders())
                ->post("{$this->getBaseUrl()}/rest/ip/hotspot/user", $payload);

            if ($response->successful()) {
                $this->log('info', "Created hotspot user: {$username}");
                return true;
            }

            $this->log('error', "Failed to create user: {$username}", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return false;

        } catch (Exception $e) {
            $this->log('error', "Exception creating user: {$username}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function updateHotspotUser(string $username, array $updates): bool
    {
        try {
            // First, find user ID
            $user = $this->findUser($username);

            if (!$user) {
                $this->log('warning', "User not found for update: {$username}");
                return false;
            }

            $userId = $user['.id'];

            $response = Http::withHeaders($this->getAuthHeaders())
                ->patch("{$this->getBaseUrl()}/rest/ip/hotspot/user/{$userId}", $updates);

            if ($response->successful()) {
                $this->log('info', "Updated user: {$username}");
                return true;
            }

            return false;

        } catch (Exception $e) {
            $this->log('error', "Exception updating user: {$username}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function removeHotspotUser(string $username): bool
    {
        try {
            $user = $this->findUser($username);

            if (!$user) {
                return false;
            }

            $userId = $user['.id'];

            $response = Http::withHeaders($this->getAuthHeaders())
                ->delete("{$this->getBaseUrl()}/rest/ip/hotspot/user/{$userId}");

            if ($response->successful()) {
                $this->log('info', "Removed user: {$username}");
                return true;
            }

            return false;

        } catch (Exception $e) {
            $this->log('error', "Exception removing user: {$username}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getActiveUsers(): array
    {
        try {
            $response = Http::withHeaders($this->getAuthHeaders())
                ->get("{$this->getBaseUrl()}/rest/ip/hotspot/active");

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            return [];

        } catch (Exception $e) {
            $this->log('error', 'Failed to get active users', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getUserUsage(string $username): array
    {
        try {
            // Get user info
            $user = $this->findUser($username);

            if (!$user) {
                return [];
            }

            // Get active session if exists
            $activeSessions = $this->getActiveUsers();
            $session = collect($activeSessions)->firstWhere('user', $username);

            return [
                'username' => $username,
                'bytes_in' => (int) ($user['bytes-in'] ?? 0),
                'bytes_out' => (int) ($user['bytes-out'] ?? 0),
                'bytes_total' => (int) (($user['bytes-in'] ?? 0) + ($user['bytes-out'] ?? 0)),
                'packets_in' => (int) ($user['packets-in'] ?? 0),
                'packets_out' => (int) ($user['packets-out'] ?? 0),
                'uptime' => $user['uptime'] ?? '00:00:00',
                'is_online' => $session !== null,
                'ip_address' => $session['address'] ?? $user['address'] ?? null,
                'mac_address' => $session['mac-address'] ?? $user['mac-address'] ?? null,
                'login_time' => $session['login-by'] ?? null,
            ];

        } catch (Exception $e) {
            $this->log('error', "Failed to get usage for user: {$username}", ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function setBandwidthProfile(string $name, int $maxDownloadKbps, int $maxUploadKbps, array $options = []): bool
    {
        try {
            // Convert Kbps to MikroTik format (e.g., 10M for 10Mbps)
            $downloadLimit = $this->formatMikroTikSpeed($maxDownloadKbps);
            $uploadLimit = $this->formatMikroTikSpeed($maxUploadKbps);

            $payload = [
                'name' => $name,
                'max-limit' => "{$uploadLimit}/{$downloadLimit}",
            ];

            // Add burst settings if provided
            if (!empty($options['burst_limit_download']) && !empty($options['burst_limit_upload'])) {
                $burstDownload = $this->formatMikroTikSpeed($options['burst_limit_download']);
                $burstUpload = $this->formatMikroTikSpeed($options['burst_limit_upload']);
                $burstThreshold = $this->formatMikroTikSpeed($options['burst_threshold'] ?? 0);
                $burstTime = $options['burst_time'] ?? '10s';

                $payload['burst-limit'] = "{$burstUpload}/{$burstDownload}";
                $payload['burst-threshold'] = "{$burstThreshold}/{$burstThreshold}";
                $payload['burst-time'] = $burstTime;
            }

            // Add priority (1-8, 8 is highest)
            if (isset($options['priority'])) {
                $payload['priority'] = (string) $options['priority'];
            }

            // Check if profile exists
            $existingProfile = $this->findSimpleQueue($name);

            if ($existingProfile) {
                // Update existing
                $queueId = $existingProfile['.id'];
                $response = Http::withHeaders($this->getAuthHeaders())
                    ->patch("{$this->getBaseUrl()}/rest/queue/simple/{$queueId}", $payload);
            } else {
                // Create new
                $response = Http::withHeaders($this->getAuthHeaders())
                    ->post("{$this->getBaseUrl()}/rest/queue/simple", $payload);
            }

            if ($response->successful()) {
                $this->log('info', "Set bandwidth profile: {$name}");
                return true;
            }

            return false;

        } catch (Exception $e) {
            $this->log('error', "Failed to set bandwidth profile: {$name}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function removeBandwidthProfile(string $name): bool
    {
        try {
            $queue = $this->findSimpleQueue($name);

            if (!$queue) {
                return false;
            }

            $queueId = $queue['.id'];

            $response = Http::withHeaders($this->getAuthHeaders())
                ->delete("{$this->getBaseUrl()}/rest/queue/simple/{$queueId}");

            if ($response->successful()) {
                $this->log('info', "Removed bandwidth profile: {$name}");
                return true;
            }

            return false;

        } catch (Exception $e) {
            $this->log('error', "Failed to remove profile: {$name}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getInterfaceStats(?string $interface = null): array
    {
        try {
            $url = $interface
                ? "{$this->getBaseUrl()}/rest/interface/ethernet?name={$interface}"
                : "{$this->getBaseUrl()}/rest/interface/ethernet";

            $response = Http::withHeaders($this->getAuthHeaders())->get($url);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            return [];

        } catch (Exception $e) {
            $this->log('error', 'Failed to get interface stats', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function reboot(): bool
    {
        try {
            $response = Http::withHeaders($this->getAuthHeaders())
                ->post("{$this->getBaseUrl()}/rest/system/reboot");

            if ($response->successful()) {
                $this->log('warning', 'Router reboot initiated');
                return true;
            }

            return false;

        } catch (Exception $e) {
            $this->log('error', 'Failed to reboot router', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function executeCommand(string $command)
    {
        try {
            // Note: REST API has limited command execution
            // For advanced commands, use SSH instead
            $this->log('warning', 'executeCommand called - consider using SSH for complex operations');
            return null;

        } catch (Exception $e) {
            $this->log('error', 'Command execution failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function disconnectUser(string $username): bool
    {
        try {
            $activeSessions = $this->getActiveUsers();
            $session = collect($activeSessions)->firstWhere('user', $username);

            if (!$session) {
                return false;
            }

            $sessionId = $session['.id'];

            $response = Http::withHeaders($this->getAuthHeaders())
                ->delete("{$this->getBaseUrl()}/rest/ip/hotspot/active/{$sessionId}");

            if ($response->successful()) {
                $this->log('info', "Disconnected user: {$username}");
                return true;
            }

            return false;

        } catch (Exception $e) {
            $this->log('error', "Failed to disconnect user: {$username}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getDhcpLeases(): array
    {
        try {
            $response = Http::withHeaders($this->getAuthHeaders())
                ->get("{$this->getBaseUrl()}/rest/ip/dhcp-server/lease");

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            return [];

        } catch (Exception $e) {
            $this->log('error', 'Failed to get DHCP leases', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Find hotspot user by name.
     */
    protected function findUser(string $username): ?array
    {
        try {
            $response = Http::withHeaders($this->getAuthHeaders())
                ->get("{$this->getBaseUrl()}/rest/ip/hotspot/user?name={$username}");

            if ($response->successful()) {
                $users = $response->json();
                return !empty($users) ? $users[0] : null;
            }

            return null;

        } catch (Exception $e) {
            $this->log('error', "Failed to find user: {$username}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Find simple queue by name.
     */
    protected function findSimpleQueue(string $name): ?array
    {
        try {
            $response = Http::withHeaders($this->getAuthHeaders())
                ->get("{$this->getBaseUrl()}/rest/queue/simple?name={$name}");

            if ($response->successful()) {
                $queues = $response->json();
                return !empty($queues) ? $queues[0] : null;
            }

            return null;

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Format speed for MikroTik (e.g., 10240 Kbps -> 10M).
     */
    protected function formatMikroTikSpeed(int $kbps): string
    {
        if ($kbps >= 1024) {
            return round($kbps / 1024) . 'M';
        }

        return $kbps . 'K';
    }
}
