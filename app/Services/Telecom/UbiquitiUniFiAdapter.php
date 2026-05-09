<?php

namespace App\Services\Telecom;

use App\Models\NetworkDevice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Ubiquiti UniFi Controller Adapter
 *
 * Integrates with Ubiquiti UniFi Controller API for network management.
 * Supports UniFi OS (Cloud Key, Dream Machine) and traditional Controller.
 */
class UbiquitiUniFiAdapter implements RouterAdapterInterface
{
    protected NetworkDevice $device;

    protected string $baseUrl;

    protected ?string $sessionId = null;

    protected string $apiVersion = 'v1';

    public function __construct(NetworkDevice $device)
    {
        $this->device = $device;

        // Validate required fields
        if (! $device->ip_address || ! $device->username || ! $device->password) {
            throw new \InvalidArgumentException('Device must have ip_address, username, and password');
        }

        // Build base URL for UniFi Controller
        $protocol = $device->port === 443 ? 'https' : 'http';
        $this->baseUrl = "{$protocol}://{$device->ip_address}:{$device->port}";
    }

    /**
     * Get router brand identifier
     */
    public function getBrand(): string
    {
        return 'ubiquiti';
    }

    /**
     * Get default API port for UniFi Controller
     */
    public function getApiPort(): int
    {
        return 8443; // Default UniFi Controller port
    }

    /**
     * Test connection to UniFi Controller
     */
    public function testConnection(): array
    {
        $startTime = microtime(true);

        try {
            // Attempt to login
            $loginSuccess = $this->login();

            if (! $loginSuccess) {
                return [
                    'success' => false,
                    'message' => 'Authentication failed',
                    'latency_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ];
            }

            // Try to get system info
            $info = $this->getSystemInfo();

            return [
                'success' => true,
                'message' => 'Connected to UniFi Controller',
                'latency_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'controller_version' => $info['version'] ?? 'Unknown',
            ];
        } catch (\Exception $e) {
            Log::error('UniFi connection test failed', [
                'device_id' => $this->device->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Connection failed: '.$e->getMessage(),
                'latency_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ];
        }
    }

    /**
     * Get system information from UniFi Controller
     */
    public function getSystemInfo(): array
    {
        try {
            $this->ensureAuthenticated();

            $response = Http::withCookies([$this->getSessionCookieName() => $this->sessionId], parse_url($this->baseUrl, PHP_URL_HOST))
                ->get("{$this->baseUrl}/api/{$this->apiVersion}/status");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'version' => $data['meta']['server_version'] ?? 'Unknown',
                    'uptime' => $data['data'][0]['uptime'] ?? 0,
                    'devices_count' => $data['data'][0]['num_ap'] ?? 0,
                    'users_count' => $data['data'][0]['num_user'] ?? 0,
                ];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to get UniFi system info', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get list of network interfaces
     */
    public function getInterfaceList(): array
    {
        try {
            $this->ensureAuthenticated();

            $response = Http::withCookies([$this->getSessionCookieName() => $this->sessionId], parse_url($this->baseUrl, PHP_URL_HOST))
                ->get("{$this->baseUrl}/api/{$this->apiVersion}/stat/device");

            if ($response->successful()) {
                $devices = $response->json()['data'] ?? [];

                return collect($devices)->map(function ($device) {
                    return [
                        'name' => $device['name'] ?? $device['mac'],
                        'type' => $device['type'] ?? 'unknown',
                        'model' => $device['model'] ?? 'Unknown',
                        'ip' => $device['ip'] ?? 'N/A',
                        'mac' => $device['mac'] ?? 'N/A',
                        'status' => $device['state'] === 1 ? 'online' : 'offline',
                        'users' => $device['user-num_sta'] ?? 0,
                    ];
                })->toArray();
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to get UniFi device list', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get active users/clients
     */
    public function getActiveUsers(): array
    {
        try {
            $this->ensureAuthenticated();

            $response = Http::withCookies([$this->getSessionCookieName() => $this->sessionId], parse_url($this->baseUrl, PHP_URL_HOST))
                ->get("{$this->baseUrl}/api/{$this->apiVersion}/stat/sta");

            if ($response->successful()) {
                $clients = $response->json()['data'] ?? [];

                return collect($clients)->filter(function ($client) {
                    return isset($client['is_guest']) && ! $client['is_guest'];
                })->map(function ($client) {
                    return [
                        'mac' => $client['mac'] ?? 'N/A',
                        'ip' => $client['ip'] ?? 'N/A',
                        'hostname' => $client['hostname'] ?? 'Unknown',
                        'essid' => $client['essid'] ?? 'N/A',
                        'channel' => $client['channel'] ?? 0,
                        'rx_bytes' => $client['rx_bytes'] ?? 0,
                        'tx_bytes' => $client['tx_bytes'] ?? 0,
                        'uptime' => $client['uptime'] ?? 0,
                    ];
                })->toArray();
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to get UniFi active users', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Create a new user/client (Guest or Voucher)
     */
    public function createUser(array $userData): bool
    {
        try {
            $this->ensureAuthenticated();

            // For UniFi, we create guest vouchers or authorize users
            $payload = [
                'cmd' => 'create-voucher',
                'expire' => $userData['validity_minutes'] ?? 1440, // Default 24 hours
                'n' => 1, // Number of vouchers
                'quota' => $userData['quota_mb'] ?? 0, // 0 = unlimited
                'byte_quota' => $userData['byte_quota'] ?? 0,
            ];

            $response = Http::withCookies([$this->getSessionCookieName() => $this->sessionId], parse_url($this->baseUrl, PHP_URL_HOST))
                ->post("{$this->baseUrl}/api/{$this->apiVersion}/cmd/hotspot", $payload);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to create UniFi user', [
                'error' => $e->getMessage(),
                'user_data' => $userData,
            ]);

            return false;
        }
    }

    /**
     * Remove/delete a user
     */
    public function removeUser(string $username): bool
    {
        try {
            $this->ensureAuthenticated();

            // Block/unauthorize user by MAC address
            $response = Http::withCookies([$this->getSessionCookieName() => $this->sessionId], parse_url($this->baseUrl, PHP_URL_HOST))
                ->post("{$this->baseUrl}/api/{$this->apiVersion}/cmd/stamgr", [
                    'cmd' => 'block-sta',
                    'mac' => $username, // In UniFi, we use MAC address
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to remove UniFi user', [
                'error' => $e->getMessage(),
                'username' => $username,
            ]);

            return false;
        }
    }

    /**
     * Disconnect a user
     */
    public function disconnectUser(string $username): bool
    {
        try {
            $this->ensureAuthenticated();

            // Kick/disconnect client
            $response = Http::withCookies([$this->getSessionCookieName() => $this->sessionId], parse_url($this->baseUrl, PHP_URL_HOST))
                ->post("{$this->baseUrl}/api/{$this->apiVersion}/cmd/stamgr", [
                    'cmd' => 'kick-sta',
                    'mac' => $username,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to disconnect UniFi user', [
                'error' => $e->getMessage(),
                'username' => $username,
            ]);

            return false;
        }
    }

    /**
     * Get bandwidth usage statistics
     */
    public function getBandwidthUsage(): array
    {
        try {
            $this->ensureAuthenticated();

            $response = Http::withCookies([$this->getSessionCookieName() => $this->sessionId], parse_url($this->baseUrl, PHP_URL_HOST))
                ->get("{$this->baseUrl}/api/{$this->apiVersion}/stat/sysinfo");

            if ($response->successful()) {
                $data = $response->json()['data'][0] ?? [];

                return [
                    'total_bytes_in' => $data['rx_bytes-r'] ?? 0,
                    'total_bytes_out' => $data['tx_bytes-r'] ?? 0,
                    'total_bytes' => ($data['rx_bytes-r'] ?? 0) + ($data['tx_bytes-r'] ?? 0),
                    'cpu_usage' => $data['system-stats']['cpu'] ?? 0,
                    'memory_usage' => $data['system-stats']['mem'] ?? 0,
                ];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to get UniFi bandwidth usage', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Authenticate with UniFi Controller
     */
    protected function login(): bool
    {
        try {
            $response = Http::post("{$this->baseUrl}/api/login", [
                'username' => $this->device->username,
                'password' => $this->device->decrypted_password,
            ]);

            if ($response->successful()) {
                // Extract session cookie
                $cookies = $response->cookies();
                $cookieName = $this->getSessionCookieName();
                $this->sessionId = $cookies->getCookieByName($cookieName)?->getValue();

                return ! empty($this->sessionId);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('UniFi login failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Ensure session is authenticated
     */
    protected function ensureAuthenticated(): void
    {
        if (empty($this->sessionId)) {
            $loginSuccess = $this->login();

            if (! $loginSuccess) {
                throw new \RuntimeException('Failed to authenticate with UniFi Controller');
            }
        }
    }

    /**
     * Get session cookie name based on UniFi version
     */
    protected function getSessionCookieName(): string
    {
        // UniFi OS uses different cookie names
        return 'unifises';
    }

    /**
     * Logout and clear session
     */
    public function logout(): void
    {
        if (! empty($this->sessionId)) {
            try {
                Http::withCookies([$this->getSessionCookieName() => $this->sessionId], parse_url($this->baseUrl, PHP_URL_HOST))
                    ->post("{$this->baseUrl}/api/logout");
            } catch (\Exception $e) {
                Log::warning('UniFi logout failed', ['error' => $e->getMessage()]);
            }
        }

        $this->sessionId = null;
    }

    /**
     * Destructor - ensure logout
     */
    public function __destruct()
    {
        $this->logout();
    }
}
