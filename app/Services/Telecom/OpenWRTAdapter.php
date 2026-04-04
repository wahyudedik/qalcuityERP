<?php

namespace App\Services\Telecom;

use App\Models\NetworkDevice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenWRT Router Adapter
 * 
 * Integrates with OpenWRT routers via LuCI RPC API or SSH.
 * Supports standard OpenWRT installations with luci-rpc package.
 */
class OpenWRTAdapter implements RouterAdapterInterface
{
    protected NetworkDevice $device;
    protected string $baseUrl;
    protected ?string $authToken = null;
    protected string $rpcPath = '/cgi-bin/luci/rpc';

    public function __construct(NetworkDevice $device)
    {
        $this->device = $device;

        // Validate required fields
        if (!$device->ip_address || !$device->username || !$device->password) {
            throw new \InvalidArgumentException('Device must have ip_address, username, and password');
        }

        // Build base URL for OpenWRT
        $protocol = $device->port === 443 ? 'https' : 'http';
        $this->baseUrl = "{$protocol}://{$device->ip_address}:{$device->port}";
    }

    /**
     * Get router brand identifier
     */
    public function getBrand(): string
    {
        return 'openwrt';
    }

    /**
     * Get default API port for OpenWRT (LuCI)
     */
    public function getApiPort(): int
    {
        return 80; // Default HTTP port for LuCI
    }

    /**
     * Test connection to OpenWRT router
     */
    public function testConnection(): array
    {
        $startTime = microtime(true);

        try {
            // Attempt to authenticate
            $authSuccess = $this->authenticate();

            if (!$authSuccess) {
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
                'message' => 'Connected to OpenWRT router',
                'latency_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'hostname' => $info['hostname'] ?? 'Unknown',
                'firmware_version' => $info['firmware_version'] ?? 'Unknown',
            ];
        } catch (\Exception $e) {
            Log::error('OpenWRT connection test failed', [
                'device_id' => $this->device->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'latency_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ];
        }
    }

    /**
     * Get system information from OpenWRT
     */
    public function getSystemInfo(): array
    {
        try {
            $this->ensureAuthenticated();

            $response = $this->rpcCall('system', 'board');

            if ($response && isset($response['result'])) {
                $board = $response['result'];
                return [
                    'hostname' => $board['hostname'] ?? 'Unknown',
                    'model' => $board['model'] ?? 'Unknown',
                    'firmware_version' => $board['release']['description'] ?? 'Unknown',
                    'kernel_version' => $board['kernel'] ?? 'Unknown',
                    'uptime' => $this->getUptime(),
                ];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to get OpenWRT system info', ['error' => $e->getMessage()]);
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

            $response = $this->rpcCall('network.interface', 'dump');

            if ($response && isset($response['result'])) {
                $interfaces = $response['result']['interface'] ?? [];

                return collect($interfaces)->map(function ($iface) {
                    return [
                        'name' => $iface['interface'] ?? 'unknown',
                        'type' => $iface['proto'] ?? 'unknown',
                        'up' => $iface['up'] ?? false,
                        'ipv4_addresses' => $iface['ipv4-address'] ?? [],
                        'mac_address' => $iface['macaddr'] ?? 'N/A',
                        'rx_bytes' => $iface['data']['rx_bytes'] ?? 0,
                        'tx_bytes' => $iface['data']['tx_bytes'] ?? 0,
                    ];
                })->toArray();
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to get OpenWRT interface list', ['error' => $e->getMessage()]);
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

            // Get DHCP leases
            $response = $this->rpcCall('luci-rpc', 'getDHCPLeases');

            if ($response && isset($response['result'])) {
                $leases = $response['result'] ?? [];

                return collect($leases)->map(function ($lease) {
                    return [
                        'mac' => $lease['macaddr'] ?? 'N/A',
                        'ip' => $lease['ipaddr'] ?? 'N/A',
                        'hostname' => $lease['hostname'] ?? 'Unknown',
                        'expires_at' => $lease['expires'] ?? 0,
                    ];
                })->toArray();
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to get OpenWRT active users', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Create a new user (via firewall rule or hotspot)
     */
    public function createUser(array $userData): bool
    {
        try {
            $this->ensureAuthenticated();

            // For OpenWRT, we can create firewall rules or use CoovaChilli/Nodogsplash
            // This example creates a simple bandwidth limit using tc (traffic control)

            $mac = $userData['mac_address'] ?? null;
            $limitDown = $userData['download_limit_kbps'] ?? 0;
            $limitUp = $userData['upload_limit_kbps'] ?? 0;

            if (!$mac) {
                Log::warning('Cannot create OpenWRT user without MAC address');
                return false;
            }

            // Execute shell command via RPC (requires luci-exec package)
            $command = sprintf(
                'tc qdisc add dev br-lan root handle 1: htb default 10 && ' .
                'tc class add dev br-lan parent 1: classid 1:1 htb rate %dkbit && ' .
                'tc filter add dev br-lan protocol all parent 1:0 prio 1 u32 match ether src %s flowid 1:1',
                $limitDown,
                $mac
            );

            $response = $this->rpcCall('luci-rpc', 'exec', ['command' => $command]);

            return $response && isset($response['result']);
        } catch (\Exception $e) {
            Log::error('Failed to create OpenWRT user', [
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

            // Remove firewall rule or traffic control for this user
            $command = sprintf(
                'iptables -D FORWARD -m mac --mac-source %s -j DROP 2>/dev/null || true',
                $username
            );

            $response = $this->rpcCall('luci-rpc', 'exec', ['command' => $command]);

            return $response !== false;
        } catch (\Exception $e) {
            Log::error('Failed to remove OpenWRT user', [
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

            // Block user via iptables
            $command = sprintf(
                'iptables -I FORWARD -m mac --mac-source %s -j DROP',
                $username
            );

            $response = $this->rpcCall('luci-rpc', 'exec', ['command' => $command]);

            return $response !== false;
        } catch (\Exception $e) {
            Log::error('Failed to disconnect OpenWRT user', [
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

            // Get network statistics from /proc/net/dev
            $response = $this->rpcCall('luci-rpc', 'exec', [
                'command' => 'cat /proc/net/dev'
            ]);

            if ($response && isset($response['result']['stdout'])) {
                $output = $response['result']['stdout'];
                $lines = explode("\n", trim($output));

                $totalRx = 0;
                $totalTx = 0;

                // Parse output (skip first 2 header lines)
                foreach (array_slice($lines, 2) as $line) {
                    if (preg_match('/(\w+):\s+(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/', $line, $matches)) {
                        $interface = $matches[1];
                        // Skip loopback
                        if ($interface !== 'lo') {
                            $totalRx += (int) $matches[2];
                            $totalTx += (int) $matches[3];
                        }
                    }
                }

                return [
                    'total_bytes_in' => $totalRx,
                    'total_bytes_out' => $totalTx,
                    'total_bytes' => $totalRx + $totalTx,
                ];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to get OpenWRT bandwidth usage', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Authenticate with OpenWRT LuCI RPC
     */
    protected function authenticate(): bool
    {
        try {
            $response = Http::post("{$this->baseUrl}{$this->rpcPath}/auth", [
                'username' => $this->device->username,
                'password' => $this->device->decrypted_password,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->authToken = $data['result'] ?? null;

                return !empty($this->authToken);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('OpenWRT authentication failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Ensure session is authenticated
     */
    protected function ensureAuthenticated(): void
    {
        if (empty($this->authToken)) {
            $authSuccess = $this->authenticate();

            if (!$authSuccess) {
                throw new \RuntimeException('Failed to authenticate with OpenWRT router');
            }
        }
    }

    /**
     * Make RPC call to OpenWRT
     */
    protected function rpcCall(string $module, string $method, array $params = []): ?array
    {
        try {
            $payload = [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'call',
                'params' => [
                    $this->authToken,
                    $module,
                    $method,
                    $params,
                ],
            ];

            $response = Http::post("{$this->baseUrl}{$this->rpcPath}", $payload);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('OpenWRT RPC call failed', [
                'module' => $module,
                'method' => $method,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get system uptime
     */
    protected function getUptime(): int
    {
        try {
            $response = $this->rpcCall('system', 'info');

            if ($response && isset($response['result']['uptime'])) {
                return (int) $response['result']['uptime'];
            }

            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Logout and clear session
     */
    public function logout(): void
    {
        if (!empty($this->authToken)) {
            try {
                $this->rpcCall('session', 'destroy');
            } catch (\Exception $e) {
                Log::warning('OpenWRT logout failed', ['error' => $e->getMessage()]);
            }
        }

        $this->authToken = null;
    }

    /**
     * Destructor - ensure logout
     */
    public function __destruct()
    {
        $this->logout();
    }
}
