<?php

namespace App\Services;

use App\Models\SmartScale;
use App\Models\ScaleWeighLog;
use Illuminate\Support\Facades\Log;

class SmartScaleService
{
    /**
     * Connect to smart scale and read weight
     */
    public function readWeight(SmartScale $scale): array
    {
        try {
            $config = $scale->getConnectionConfig();

            $weight = match ($config['type']) {
                'serial', 'usb' => $this->readFromSerial($config),
                'bluetooth' => $this->readFromBluetooth($config),
                'network' => $this->readFromNetwork($config),
                default => throw new \Exception("Unsupported connection type: {$config['type']}"),
            };

            // Update scale status
            $scale->update([
                'is_connected' => true,
                'last_reading' => $weight['weight'],
                'last_sync_at' => now(),
            ]);

            return [
                'success' => true,
                'weight' => $weight['weight'],
                'unit' => $weight['unit'],
                'stable' => $weight['stable'] ?? true,
                'raw_data' => $weight['raw_data'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Smart Scale Read Error', [
                'scale_id' => $scale->id,
                'error' => $e->getMessage(),
            ]);

            $scale->update(['is_connected' => false]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Tare the scale (set zero point)
     */
    public function tareScale(SmartScale $scale): bool
    {
        try {
            $config = $scale->getConnectionConfig();

            match ($config['type']) {
                'serial', 'usb' => $this->sendCommand($config, "T\r\n"),
                'bluetooth' => $this->sendCommand($config, "T\r\n"),
                'network' => $this->sendCommand($config, "T\r\n"),
                default => throw new \Exception("Unsupported connection type"),
            };

            return true;
        } catch (\Exception $e) {
            Log::error('Smart Scale Tare Error', [
                'scale_id' => $scale->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Record a weigh operation
     */
    public function recordWeigh(array $data): ScaleWeighLog
    {
        $scale = SmartScale::findOrFail($data['scale_id']);

        $netWeight = $data['weight'] - ($data['tare_weight'] ?? 0);
        $user = auth()->user();

        $log = ScaleWeighLog::create([
            'tenant_id' => $user ? $user->tenant_id : null,
            'scale_id' => $data['scale_id'],
            'product_id' => $data['product_id'] ?? null,
            'warehouse_id' => $data['warehouse_id'] ?? null,
            'weight' => $data['weight'],
            'unit' => $data['unit'] ?? $scale->unit,
            'tare_weight' => $data['tare_weight'] ?? 0,
            'net_weight' => $netWeight,
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'weighed_by' => $user ? $user->id : null,
            'weigh_time' => now(),
            'raw_data' => $data['raw_data'] ?? null,
            'status' => 'pending',
        ]);

        return $log;
    }

    /**
     * Process pending weigh logs
     */
    public function processWeighLog(ScaleWeighLog $log): bool
    {
        try {
            // Implement business logic based on reference_type
            match ($log->reference_type) {
                'goods_receipt' => $this->processGoodsReceipt($log),
                'stock_opname' => $this->processStockOpname($log),
                'production' => $this->processProduction($log),
                default => null,
            };

            $log->update(['status' => 'processed']);
            return true;

        } catch (\Exception $e) {
            $log->update([
                'status' => 'error',
                'error_message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Test scale connection
     */
    public function testConnection(SmartScale $scale): array
    {
        try {
            $result = $this->readWeight($scale);

            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Koneksi berhasil! Berat terbaca: ' . $result['weight'] . ' ' . $result['unit'],
                    'weight' => $result['weight'],
                    'unit' => $result['unit'],
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Gagal membaca berat: ' . $result['error'],
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error koneksi: ' . $e->getMessage(),
            ];
        }
    }

    // ==================== PRIVATE METHODS ====================

    /**
     * Read weight from serial/USB connection
     */
    private function readFromSerial(array $config): array
    {
        // Note: PHP serial communication requires php_serial extension or dio extension
        // For production, consider using a middleware service or Python script

        if (!function_exists('dio_open')) {
            throw new \Exception('PHP DIO extension not installed. Please install php-dio for serial communication.');
        }

        $port = $config['port'];
        $baudRate = $config['baud_rate'];

        // Add proper port prefix for Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $port = '\\\\.\\' . $port;
        }

        // Define constants if not already defined
        if (!defined('O_RDWR'))
            define('O_RDWR', 2);
        if (!defined('O_NONBLOCK'))
            define('O_NONBLOCK', 2048);
        if (!defined('F_SETFL'))
            define('F_SETFL', 4);
        if (!defined('O_SYNC'))
            define('O_SYNC', 16);

        $device = @dio_open($port, O_RDWR | O_NONBLOCK);

        if (!$device) {
            throw new \Exception("Cannot open port: {$port}");
        }

        @dio_fcntl($device, F_SETFL, O_SYNC);
        @dio_tcsetattr($device, [
            'baud' => $baudRate,
            'bits' => $config['data_bits'],
            'stop' => $config['stop_bits'],
            'parity' => $this->getParityConstant($config['parity']),
        ]);

        // Read data from scale
        $data = '';
        $timeout = 5; // seconds
        $startTime = time();

        while (time() - $startTime < $timeout) {
            $chunk = @dio_read($device, 256);
            if ($chunk) {
                $data .= $chunk;

                // Check for end of transmission (carriage return or newline)
                if (strpos($data, "\r") !== false || strpos($data, "\n") !== false) {
                    break;
                }
            }
            usleep(100000); // 100ms delay
        }

        @dio_close($device);

        if (empty($data)) {
            throw new \Exception('No data received from scale');
        }

        return $this->parseWeightData(trim($data), $config['vendor']);
    }

    /**
     * Read weight from Bluetooth connection
     */
    private function readFromBluetooth(array $config): array
    {
        // Bluetooth typically uses RFCOMM serial protocol
        // On Linux: /dev/rfcomm0
        // On Windows: COM port assigned to Bluetooth device

        return $this->readFromSerial($config);
    }

    /**
     * Read weight from network connection (TCP/IP)
     */
    private function readFromNetwork(array $config): array
    {
        $ip = $config['port']; // IP address stored in port field for network
        $port = $config['config']['network_port'] ?? 4000;
        $timeout = 5;

        $socket = @fsockopen($ip, $port, $errno, $errstr, $timeout);

        if (!$socket) {
            throw new \Exception("Cannot connect to {$ip}:{$port} - {$errstr}");
        }

        stream_set_timeout($socket, $timeout);

        // Send request command (varies by vendor)
        $command = $config['config']['read_command'] ?? "W\r\n";
        fwrite($socket, $command);

        // Read response
        $data = '';
        while (!feof($socket)) {
            $chunk = fgets($socket, 256);
            if ($chunk === false)
                break;
            $data .= $chunk;

            if (strpos($data, "\r") !== false || strpos($data, "\n") !== false) {
                break;
            }
        }

        fclose($socket);

        if (empty($data)) {
            throw new \Exception('No data received from network scale');
        }

        return $this->parseWeightData(trim($data), $config['vendor']);
    }

    /**
     * Send command to scale
     */
    private function sendCommand(array $config, string $command): bool
    {
        switch ($config['type']) {
            case 'serial':
            case 'usb':
            case 'bluetooth':
                if (!function_exists('dio_open')) {
                    throw new \Exception('PHP DIO extension not installed');
                }

                // Define constants if not already defined
                if (!defined('O_RDWR'))
                    define('O_RDWR', 2);

                $port = $config['port'];
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $port = '\\\\.\\' . $port;
                }

                $device = @dio_open($port, O_RDWR);
                if (!$device) {
                    throw new \Exception("Cannot open port: {$port}");
                }

                @dio_write($device, $command);
                @dio_close($device);
                return true;

            case 'network':
                $ip = $config['port'];
                $port = $config['config']['network_port'] ?? 4000;

                $socket = @fsockopen($ip, $port, $errno, $errstr, 5);
                if (!$socket) {
                    throw new \Exception("Cannot connect to {$ip}:{$port}");
                }

                fwrite($socket, $command);
                fclose($socket);
                return true;

            default:
                throw new \Exception("Unsupported connection type");
        }
    }

    /**
     * Parse weight data from scale (vendor-specific)
     */
    private function parseWeightData(string $data, string $vendor): array
    {
        // Different vendors have different data formats
        // Common formats:
        // - "ST,GS,+    1234.5g" (Mettler Toledo)
        // - "1234.5 g" (Generic)
        // - "W 1234.5" (Some Chinese scales)

        $weight = null;
        $unit = 'g';
        $stable = true;

        // Try to extract weight using regex
        if (preg_match('/[+-]?\d+\.?\d*/', $data, $matches)) {
            $weight = floatval($matches[0]);
        }

        // Try to detect unit
        if (preg_match('/(kg|g|lb|oz)/i', $data, $matches)) {
            $unit = strtolower($matches[1]);
        }

        // Check stability indicator
        if (preg_match('/(US|UNSTABLE)/i', $data)) {
            $stable = stripos($data, 'US') !== false;
        }

        if ($weight === null) {
            throw new \Exception("Cannot parse weight from data: {$data}");
        }

        return [
            'weight' => $weight,
            'unit' => $unit,
            'stable' => $stable,
            'raw_data' => $data,
        ];
    }

    /**
     * Get parity constant for dio extension
     */
    private function getParityConstant(string $parity): int
    {
        return match (strtolower($parity)) {
            'even' => 2,
            'odd' => 1,
            'none' => 0,
            default => 0,
        };
    }

    /**
     * Process goods receipt weigh log
     */
    private function processGoodsReceipt(ScaleWeighLog $log): void
    {
        // Integration with Goods Receipt module
        // Auto-update quantity based on weight
        // This is a placeholder for actual implementation
    }

    /**
     * Process stock opname weigh log
     */
    private function processStockOpname(ScaleWeighLog $log): void
    {
        // Integration with Stock Opname module
        // Auto-update stock count based on weight
    }

    /**
     * Process production weigh log
     */
    private function processProduction(ScaleWeighLog $log): void
    {
        // Integration with Production module
        // Track material usage by weight
    }
}
