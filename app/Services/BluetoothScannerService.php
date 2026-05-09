<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class BluetoothScannerService
{
    /**
     * Available Bluetooth devices (cached)
     */
    protected array $pairedDevices = [];

    /**
     * Scan for nearby Bluetooth barcode scanners
     */
    public function scanForDevices(): array
    {
        try {
            // On Linux, use bluetoothctl
            if (PHP_OS === 'Linux') {
                return $this->scanLinux();
            }

            // On Windows, use PowerShell
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                return $this->scanWindows();
            }

            return [
                'success' => false,
                'message' => 'Bluetooth scanning not supported on this OS',
                'devices' => [],
            ];
        } catch (\Exception $e) {
            Log::error('Bluetooth Scan Error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'devices' => [],
            ];
        }
    }

    /**
     * Connect to Bluetooth scanner
     */
    public function connectToDevice(string $deviceAddress): bool
    {
        try {
            if (PHP_OS === 'Linux') {
                return $this->connectLinux($deviceAddress);
            }

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                return $this->connectWindows($deviceAddress);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Bluetooth Connect Error', [
                'device' => $deviceAddress,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Read barcode from connected scanner
     */
    public function readBarcode(string $port): ?string
    {
        try {
            // For HID mode scanners, they act as keyboard input
            // This method is for serial-based Bluetooth scanners

            if (! function_exists('dio_open')) {
                throw new \Exception('PHP DIO extension required for serial Bluetooth');
            }

            $device = @dio_open($port, O_RDWR | O_NONBLOCK);

            if (! $device) {
                throw new \Exception("Cannot open Bluetooth port: {$port}");
            }

            $barcode = '';
            $timeout = 5;
            $startTime = time();

            while (time() - $startTime < $timeout) {
                $data = @dio_read($device, 1);

                if ($data) {
                    $char = ord($data);

                    // Enter key (end of barcode)
                    if ($char === 13 || $char === 10) {
                        break;
                    }

                    $barcode .= $data;
                }

                usleep(10000); // 10ms delay
            }

            @dio_close($device);

            return ! empty($barcode) ? trim($barcode) : null;
        } catch (\Exception $e) {
            Log::error('Barcode Read Error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Disconnect from Bluetooth device
     */
    public function disconnect(string $deviceAddress): bool
    {
        try {
            if (PHP_OS === 'Linux') {
                exec("bluetoothctl disconnect {$deviceAddress}");

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Bluetooth Disconnect Error', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Get paired Bluetooth devices
     */
    public function getPairedDevices(): array
    {
        if (empty($this->pairedDevices)) {
            $this->pairedDevices = $this->scanForDevices()['devices'] ?? [];
        }

        return $this->pairedDevices;
    }

    // ==================== PRIVATE METHODS ====================

    /**
     * Scan for devices on Linux
     */
    private function scanLinux(): array
    {
        // Use bluetoothctl to scan
        exec('bluetoothctl devices 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('Failed to scan Bluetooth devices');
        }

        $devices = [];
        foreach ($output as $line) {
            if (preg_match('/Device ([0-9A-F:]+)\s+(.+)/i', $line, $matches)) {
                $devices[] = [
                    'address' => $matches[1],
                    'name' => trim($matches[2]),
                    'type' => $this->detectDeviceType(trim($matches[2])),
                ];
            }
        }

        return [
            'success' => true,
            'devices' => $devices,
        ];
    }

    /**
     * Scan for devices on Windows
     */
    private function scanWindows(): array
    {
        // Use PowerShell to get Bluetooth devices
        $command = 'Get-PnpDevice -Class Bluetooth | Where-Object {$_.Status -eq "OK"} | Select-Object FriendlyName, DeviceID | ConvertTo-Json';

        exec("powershell -Command \"{$command}\" 2>&1", $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('Failed to scan Bluetooth devices');
        }

        $json = implode("\n", $output);
        $devices = json_decode($json, true) ?? [];

        $formatted = [];
        foreach ($devices as $device) {
            $formatted[] = [
                'name' => $device['FriendlyName'] ?? 'Unknown',
                'device_id' => $device['DeviceID'] ?? '',
                'type' => $this->detectDeviceType($device['FriendlyName'] ?? ''),
            ];
        }

        return [
            'success' => true,
            'devices' => $formatted,
        ];
    }

    /**
     * Connect on Linux
     */
    private function connectLinux(string $deviceAddress): bool
    {
        exec("bluetoothctl connect {$deviceAddress} 2>&1", $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("Failed to connect to {$deviceAddress}");
        }

        // Check if connection successful
        $connected = false;
        foreach ($output as $line) {
            if (stripos($line, 'Connection successful') !== false) {
                $connected = true;
                break;
            }
        }

        return $connected;
    }

    /**
     * Connect on Windows
     */
    private function connectWindows(string $deviceAddress): bool
    {
        // Windows typically auto-connects paired devices
        // Just verify the device is available
        $paired = $this->getPairedDevices();

        foreach ($paired as $device) {
            if (
                $device['device_id'] === $deviceAddress ||
                stripos($device['name'], $deviceAddress) !== false
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect device type from name
     */
    private function detectDeviceType(string $name): string
    {
        $name = strtolower($name);

        if (
            strpos($name, 'scanner') !== false ||
            strpos($name, 'barcode') !== false ||
            strpos($name, 'zebra') !== false ||
            strpos($name, 'honeywell') !== false
        ) {
            return 'barcode_scanner';
        }

        if (strpos($name, 'printer') !== false) {
            return 'printer';
        }

        if (strpos($name, 'scale') !== false) {
            return 'scale';
        }

        return 'unknown';
    }
}
