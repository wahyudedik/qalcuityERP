<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\FingerprintAttendanceLog;
use App\Models\FingerprintDevice;
use App\Services\FingerprintDeviceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FingerprintWebhookController extends Controller
{
    public function __construct(
        private FingerprintDeviceService $fingerprintService
    ) {}

    /**
     * Handle attendance data from fingerprint device
     * This endpoint is called by the fingerprint device or middleware
     */
    public function handleAttendance(Request $request): JsonResponse
    {
        try {
            $deviceId = $request->input('device_id');
            $deviceSecret = $request->input('secret_key');

            // Find device
            $device = FingerprintDevice::where('device_id', $deviceId)->first();

            if (! $device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perangkat tidak ditemukan',
                ], 404);
            }

            // Verify secret key if configured
            if ($device->secret_key && $device->secret_key !== $deviceSecret) {
                return response()->json([
                    'success' => false,
                    'message' => 'Autentikasi gagal',
                ], 403);
            }

            // Check if device is active
            if (! $device->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perangkat tidak aktif',
                ], 403);
            }

            // Process attendance records
            $records = $request->input('records', []);
            $processed = 0;
            $errors = 0;

            foreach ($records as $record) {
                try {
                    $this->processRecord($device, $record);
                    $processed++;
                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Failed to process fingerprint record', [
                        'device_id' => $device->id,
                        'error' => $e->getMessage(),
                        'record' => $record,
                    ]);
                }
            }

            // Update device status
            $device->update([
                'last_sync_at' => now(),
                'is_connected' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Data berhasil diproses: {$processed} record, {$errors} error",
                'processed' => $processed,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            Log::error('Fingerprint webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses data',
            ], 500);
        }
    }

    /**
     * Process a single attendance record
     */
    private function processRecord(FingerprintDevice $device, array $record): void
    {
        $employeeUid = $record['uid'] ?? null;
        $timestamp = $record['timestamp'] ?? now();
        $scanType = $record['type'] ?? 'check_in';

        if (! $employeeUid) {
            throw new \Exception('UID karyawan tidak ditemukan');
        }

        // Create attendance log
        $log = FingerprintAttendanceLog::create([
            'tenant_id' => $device->tenant_id,
            'device_id' => $device->id,
            'employee_uid' => $employeeUid,
            'scan_time' => is_string($timestamp) ? Carbon::parse($timestamp) : $timestamp,
            'scan_type' => in_array($scanType, ['check_in', 'check_out', 'break_in', 'break_out'])
                ? $scanType
                : 'check_in',
            'is_processed' => false,
            'raw_data' => json_encode($record),
        ]);

        // Try to find employee and process to attendance
        $employee = Employee::where('tenant_id', $device->tenant_id)
            ->where('fingerprint_uid', $employeeUid)
            ->first();

        if ($employee) {
            $log->update(['employee_id' => $employee->id]);
            $this->fingerprintService->processToAttendance($log, $employee);
        }
    }

    /**
     * Get pending employees to register (for device polling)
     */
    public function getPendingRegistrations(Request $request): JsonResponse
    {
        try {
            $deviceId = $request->input('device_id');
            $deviceSecret = $request->input('secret_key');

            $device = FingerprintDevice::where('device_id', $deviceId)->first();

            if (! $device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perangkat tidak ditemukan',
                ], 404);
            }

            if ($device->secret_key && $device->secret_key !== $deviceSecret) {
                return response()->json([
                    'success' => false,
                    'message' => 'Autentikasi gagal',
                ], 403);
            }

            // Get employees without fingerprint registration
            $employees = Employee::where('tenant_id', $device->tenant_id)
                ->whereNull('fingerprint_uid')
                ->orWhere('fingerprint_registered', false)
                ->select('id', 'employee_id', 'name', 'position')
                ->get();

            return response()->json([
                'success' => true,
                'employees' => $employees,
            ]);

        } catch (\Exception $e) {
            Log::error('Get pending registrations error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
            ], 500);
        }
    }

    /**
     * Device heartbeat/ping endpoint
     */
    public function heartbeat(Request $request): JsonResponse
    {
        try {
            $deviceId = $request->input('device_id');

            $device = FingerprintDevice::where('device_id', $deviceId)->first();

            if ($device) {
                $device->update([
                    'is_connected' => true,
                    'last_sync_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Heartbeat diterima',
                    'device_name' => $device->name,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Perangkat tidak ditemukan',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing heartbeat',
            ], 500);
        }
    }
}
