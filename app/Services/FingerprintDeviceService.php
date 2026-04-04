<?php

namespace App\Services;

use App\Models\FingerprintDevice;
use App\Models\FingerprintAttendanceLog;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FingerprintDeviceService
{
    /**
     * Test connection to fingerprint device
     */
    public function testConnection(FingerprintDevice $device): array
    {
        try {
            // Simulasi koneksi - dalam implementasi nyata akan menggunakan SDK perangkat
            $config = $device->getConnectionConfig();

            // Untuk ZKTeco devices, biasanya menggunakan TCP/IP pada port 4370
            if ($device->vendor === 'zkteco') {
                return $this->testZktecoConnection($config);
            }

            // Default simulation
            return [
                'success' => true,
                'message' => 'Koneksi berhasil (simulasi)',
                'device_info' => [
                    'model' => $device->model ?? 'Unknown',
                    'firmware' => '1.0.0',
                    'users_count' => 0,
                    'logs_count' => 0,
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Fingerprint device connection failed', [
                'device_id' => $device->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Koneksi gagal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test connection for ZKTeco devices
     */
    private function testZktecoConnection(array $config): array
    {
        // Dalam implementasi nyata, gunakan library seperti adrobinoga/zklib
        // Untuk sekarang, simulasi saja
        return [
            'success' => true,
            'message' => 'Koneksi ke perangkat ZKTeco berhasil (simulasi)',
            'device_info' => [
                'model' => 'F18',
                'firmware' => '6.6.0',
                'users_count' => 50,
                'logs_count' => 1000,
            ]
        ];
    }

    /**
     * Sync attendance logs from device
     */
    public function syncAttendanceLogs(FingerprintDevice $device): array
    {
        try {
            $config = $device->getConnectionConfig();
            $logs = [];

            // Simulasi pengambilan data dari perangkat
            // Dalam implementasi nyata, ambil dari perangkat fingerprint
            if ($device->vendor === 'zkteco') {
                $logs = $this->fetchZktecoLogs($config);
            } else {
                $logs = $this->fetchGenericLogs($config);
            }

            $processed = 0;
            $errors = 0;

            foreach ($logs as $logData) {
                try {
                    $this->processAttendanceLog($device, $logData);
                    $processed++;
                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Failed to process attendance log', [
                        'device_id' => $device->id,
                        'error' => $e->getMessage(),
                        'data' => $logData
                    ]);
                }
            }

            // Update last sync time
            $device->update([
                'last_sync_at' => now(),
                'is_connected' => true
            ]);

            return [
                'success' => true,
                'message' => "Sinkronisasi selesai. {$processed} data diproses, {$errors} error.",
                'processed' => $processed,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            Log::error('Sync attendance logs failed', [
                'device_id' => $device->id,
                'error' => $e->getMessage()
            ]);

            $device->update(['is_connected' => false]);

            return [
                'success' => false,
                'message' => 'Sinkronisasi gagal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Fetch logs from ZKTeco device
     */
    private function fetchZktecoLogs(array $config): array
    {
        // Dalam implementasi nyata, gunakan zklib atau SDK ZKTeco
        // Simulasi data
        return [];
    }

    /**
     * Fetch logs from generic device
     */
    private function fetchGenericLogs(array $config): array
    {
        // Implementasi untuk vendor lain
        return [];
    }

    /**
     * Process a single attendance log
     */
    private function processAttendanceLog(FingerprintDevice $device, array $logData): void
    {
        $employeeUid = $logData['uid'] ?? null;
        $scanTime = $logData['timestamp'] ?? now();

        if (!$employeeUid) {
            throw new \Exception('UID karyawan tidak ditemukan');
        }

        // Cari employee berdasarkan fingerprint_uid
        $employee = Employee::where('tenant_id', $device->tenant_id)
            ->where('fingerprint_uid', $employeeUid)
            ->first();

        // Simpan ke attendance log
        $attendanceLog = FingerprintAttendanceLog::create([
            'tenant_id' => $device->tenant_id,
            'device_id' => $device->id,
            'employee_uid' => $employeeUid,
            'employee_id' => $employee ? $employee->id : null,
            'scan_time' => is_string($scanTime) ? Carbon::parse($scanTime) : $scanTime,
            'scan_type' => $this->determineScanType($employee, $scanTime),
            'is_processed' => false,
            'raw_data' => json_encode($logData),
        ]);

        // Jika employee ditemukan, proses ke attendance
        if ($employee) {
            $this->processToAttendance($attendanceLog, $employee);
        }
    }

    /**
     * Determine scan type based on time and previous scans
     */
    private function determineScanType(?Employee $employee, $scanTime): string
    {
        if (!$employee) {
            return 'check_in';
        }

        $date = is_string($scanTime) ? Carbon::parse($scanTime)->toDateString() : Carbon::instance($scanTime)->toDateString();
        $time = is_string($scanTime) ? Carbon::parse($scanTime)->toTimeString() : Carbon::instance($scanTime)->toTimeString();

        // Cek apakah sudah ada check-in hari ini
        $existingAttendance = Attendance::where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('date', $date)
            ->first();

        if (!$existingAttendance || !$existingAttendance->check_in) {
            return 'check_in';
        } elseif ($existingAttendance && !$existingAttendance->check_out) {
            return 'check_out';
        }

        return 'check_in'; // Default
    }

    /**
     * Process attendance log to attendance record
     */
    public function processToAttendance(FingerprintAttendanceLog $log, Employee $employee): void
    {
        $date = $log->scan_time->toDateString();
        $time = $log->scan_time->toTimeString();

        $attendance = Attendance::firstOrNew([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'date' => $date,
        ]);

        if ($log->scan_type === 'check_in' && !$attendance->check_in) {
            $attendance->check_in = $time;
            $attendance->status = 'present';
        } elseif ($log->scan_type === 'check_out' && !$attendance->check_out) {
            $attendance->check_out = $time;

            // Hitung durasi kerja
            if ($attendance->check_in) {
                $checkIn = Carbon::parse("{$date} {$attendance->check_in}");
                $checkOut = Carbon::parse("{$date} {$attendance->check_out}");
                $attendance->work_minutes = $checkIn->diffInMinutes($checkOut);
            }
        }

        $attendance->save();

        // Mark log as processed
        $log->update([
            'is_processed' => true,
            'processed_at' => now(),
        ]);
    }

    /**
     * Register employee fingerprint on device
     */
    public function registerEmployeeFingerprint(FingerprintDevice $device, Employee $employee, string $uid): array
    {
        try {
            // Dalam implementasi nyata, kirim perintah ke perangkat fingerprint
            // untuk mendaftarkan UID karyawan

            $employee->update([
                'fingerprint_uid' => $uid,
                'fingerprint_registered' => true,
            ]);

            return [
                'success' => true,
                'message' => 'Fingerprint karyawan berhasil didaftarkan'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to register fingerprint', [
                'device_id' => $device->id,
                'employee_id' => $employee->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mendaftarkan fingerprint: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Remove employee fingerprint from device
     */
    public function removeEmployeeFingerprint(FingerprintDevice $device, Employee $employee): array
    {
        try {
            // Dalam implementasi nyata, kirim perintah ke perangkat fingerprint
            // untuk menghapus UID karyawan

            $employee->update([
                'fingerprint_uid' => null,
                'fingerprint_registered' => false,
            ]);

            return [
                'success' => true,
                'message' => 'Fingerprint karyawan berhasil dihapus'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to remove fingerprint', [
                'device_id' => $device->id,
                'employee_id' => $employee->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghapus fingerprint: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get device status and statistics
     */
    public function getDeviceStatus(FingerprintDevice $device): array
    {
        $today = now()->toDateString();

        return [
            'device' => $device,
            'is_configured' => $device->isConfigured(),
            'is_active' => $device->is_active,
            'is_connected' => $device->is_connected,
            'last_sync_at' => $device->last_sync_at,
            'today_scans' => FingerprintAttendanceLog::where('device_id', $device->id)
                ->whereDate('scan_time', $today)
                ->count(),
            'today_processed' => FingerprintAttendanceLog::where('device_id', $device->id)
                ->whereDate('scan_time', $today)
                ->where('is_processed', true)
                ->count(),
            'registered_employees' => Employee::where('tenant_id', $device->tenant_id)
                ->where('fingerprint_registered', true)
                ->count(),
        ];
    }
}
