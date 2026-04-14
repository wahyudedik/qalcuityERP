<?php

namespace App\Http\Controllers\Api;

use App\Models\IotDevice;
use App\Models\IotTelemetryLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Endpoint yang dipanggil langsung oleh ESP32 / Arduino / Raspberry Pi
 * Auth: X-Device-Token header atau device_token di body
 */
class IotWebhookController extends ApiBaseController
{
    /**
     * Terima data telemetry dari device
     * POST /api/webhooks/iot/telemetry
     *
     * Payload ESP32/Arduino:
     * {
     *   "device_token": "xxx",
     *   "sensors": [
     *     {"type": "temperature", "value": 28.5, "unit": "C"},
     *     {"type": "humidity", "value": 72.3, "unit": "%"}
     *   ],
     *   "recorded_at": "2026-04-14T10:00:00Z"  // opsional
     * }
     */
    public function telemetry(Request $request): JsonResponse
    {
        try {
            $device = $this->resolveDevice($request);
            if (!$device) {
                return $this->error('Device tidak ditemukan atau token tidak valid.', 401);
            }

            if (!$device->is_active) {
                return $this->error('Device tidak aktif.', 403);
            }

            $sensors    = $request->input('sensors', []);
            $recordedAt = $request->input('recorded_at')
                ? Carbon::parse($request->input('recorded_at'))
                : now();

            // Validasi minimal ada 1 sensor
            if (empty($sensors)) {
                return $this->error('Tidak ada data sensor yang dikirim.', 422);
            }

            $saved = 0;
            foreach ($sensors as $sensor) {
                if (empty($sensor['type'])) continue;

                IotTelemetryLog::create([
                    'tenant_id'    => $device->tenant_id,
                    'iot_device_id'=> $device->id,
                    'sensor_type'  => $sensor['type'],
                    'value'        => $sensor['value'] ?? null,
                    'unit'         => $sensor['unit'] ?? null,
                    'payload'      => $sensor,
                    'status'       => 'received',
                    'recorded_at'  => $recordedAt,
                ]);
                $saved++;
            }

            // Update status device
            $device->update([
                'is_connected' => true,
                'last_seen_at' => now(),
                'firmware_version' => $request->input('firmware') ?? $device->firmware_version,
            ]);

            return $this->ok([
                'saved'      => $saved,
                'device_id'  => $device->device_id,
                'server_time'=> now()->toIso8601String(),
            ], "Telemetry diterima: {$saved} sensor.");

        } catch (\Exception $e) {
            Log::error('IoT telemetry error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->error('Terjadi kesalahan server.', 500);
        }
    }

    /**
     * Heartbeat / ping dari device
     * POST /api/webhooks/iot/heartbeat
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $device = $this->resolveDevice($request);
        if (!$device) {
            return $this->error('Device tidak ditemukan.', 401);
        }

        $device->update([
            'is_connected' => true,
            'last_seen_at' => now(),
        ]);

        return $this->ok([
            'server_time' => now()->toIso8601String(),
            'device_name' => $device->name,
            'is_active'   => $device->is_active,
        ], 'Heartbeat diterima.');
    }

    /**
     * Device polling — ambil konfigurasi terbaru dari ERP
     * GET /api/webhooks/iot/config?device_token=xxx
     * Berguna untuk Raspberry Pi yang perlu sync config
     */
    public function getConfig(Request $request): JsonResponse
    {
        $device = $this->resolveDevice($request);
        if (!$device) {
            return $this->error('Device tidak ditemukan.', 401);
        }

        return $this->ok([
            'device_id'     => $device->device_id,
            'device_name'   => $device->name,
            'target_module' => $device->target_module,
            'sensor_types'  => $device->sensor_types ?? [],
            'config'        => $device->config ?? [],
            'is_active'     => $device->is_active,
            'server_time'   => now()->toIso8601String(),
        ]);
    }

    /** Resolve device dari token (header atau body) */
    private function resolveDevice(Request $request): ?IotDevice
    {
        $token = $request->header('X-Device-Token')
            ?? $request->input('device_token');

        if (!$token) return null;

        return IotDevice::withoutGlobalScope('tenant')
            ->where('device_token', $token)
            ->first();
    }
}
