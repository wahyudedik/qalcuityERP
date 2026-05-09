<?php

namespace App\Services\ERP;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeReport;
use Carbon\Carbon;

class HrmTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name' => 'list_employees',
                'description' => 'Tampilkan daftar semua karyawan. Gunakan untuk: '
                    .'"daftar karyawan", "siapa saja karyawan kita?", "tampilkan semua pegawai", '
                    .'"karyawan aktif berapa?", "list staff", "data karyawan semua".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'department' => ['type' => 'string', 'description' => 'Filter per departemen (opsional)'],
                        'status' => ['type' => 'string', 'description' => 'Filter status: active, inactive, all (default: active)'],
                    ],
                ],
            ],
            [
                'name' => 'get_attendance_summary',
                'description' => 'Tampilkan ringkasan kehadiran karyawan.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'today, this_week, this_month'],
                        'employee_name' => ['type' => 'string', 'description' => 'Nama karyawan (opsional, kosong = semua)'],
                    ],
                    'required' => ['period'],
                ],
            ],
            [
                'name' => 'get_missing_reports',
                'description' => 'Cari karyawan yang belum mengumpulkan laporan mingguan/bulanan.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'type' => ['type' => 'string', 'description' => 'Tipe laporan: weekly atau monthly'],
                        'period' => ['type' => 'string', 'description' => 'Periode dalam format YYYY-MM-DD (tanggal mulai periode)'],
                    ],
                    'required' => ['type'],
                ],
            ],
            [
                'name' => 'get_employee_info',
                'description' => 'Cari informasi karyawan.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'employee_name' => ['type' => 'string', 'description' => 'Nama karyawan'],
                    ],
                    'required' => ['employee_name'],
                ],
            ],
            [
                'name' => 'create_employee',
                'description' => 'Tambah karyawan baru. Gunakan untuk: '
                    .'"tambah karyawan Siti posisi kasir gaji 3000000", '
                    .'"daftarkan pegawai Budi bagian gudang", '
                    .'"buat data karyawan baru Ahmad".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string', 'description' => 'Nama lengkap karyawan'],
                        'position' => ['type' => 'string', 'description' => 'Jabatan/posisi (opsional)'],
                        'department' => ['type' => 'string', 'description' => 'Departemen/bagian (opsional)'],
                        'salary' => ['type' => 'number', 'description' => 'Gaji pokok (opsional)'],
                        'phone' => ['type' => 'string', 'description' => 'Nomor telepon (opsional)'],
                        'email' => ['type' => 'string', 'description' => 'Email (opsional)'],
                        'join_date' => ['type' => 'string', 'description' => 'Tanggal bergabung YYYY-MM-DD (opsional, default hari ini)'],
                    ],
                    'required' => ['name'],
                ],
            ],
            [
                'name' => 'record_attendance',
                'description' => 'Catat kehadiran satu karyawan. Gunakan untuk: '
                    .'"Siti hadir hari ini", "Budi terlambat", "Andi izin", "Ahmad sakit hari ini".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'employee_name' => ['type' => 'string', 'description' => 'Nama karyawan'],
                        'status' => ['type' => 'string', 'description' => 'Status: present, absent, late, leave, sick'],
                        'date' => ['type' => 'string', 'description' => 'Tanggal YYYY-MM-DD (opsional, default hari ini)'],
                        'notes' => ['type' => 'string', 'description' => 'Keterangan tambahan (opsional)'],
                    ],
                    'required' => ['employee_name', 'status'],
                ],
            ],
            [
                'name' => 'record_attendance_bulk',
                'description' => 'Catat kehadiran banyak karyawan sekaligus. Gunakan untuk: '
                    .'"catat hadir: Siti, Budi, Andi", '
                    .'"yang hadir hari ini Siti dan Budi, Andi izin", '
                    .'"absensi hari ini: hadir Siti Budi, sakit Andi".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'date' => ['type' => 'string', 'description' => 'Tanggal YYYY-MM-DD (opsional, default hari ini)'],
                        'records' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'employee_name' => ['type' => 'string'],
                                    'status' => ['type' => 'string', 'description' => 'present, absent, late, leave, sick'],
                                    'notes' => ['type' => 'string'],
                                ],
                                'required' => ['employee_name', 'status'],
                            ],
                            'description' => 'Daftar absensi karyawan',
                        ],
                    ],
                    'required' => ['records'],
                ],
            ],
        ];
    }

    public function createEmployee(array $args): array
    {
        $existing = Employee::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['name']}%")
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return ['status' => 'error', 'message' => "Karyawan dengan nama '{$args['name']}' sudah terdaftar."];
        }

        // Generate employee ID: EMP-YYYYMMDD-XXXX
        $count = Employee::where('tenant_id', $this->tenantId)->count() + 1;
        $employeeId = 'EMP-'.now()->format('Ym').'-'.str_pad($count, 4, '0', STR_PAD_LEFT);

        $employee = Employee::create([
            'tenant_id' => $this->tenantId,
            'employee_id' => $employeeId,
            'name' => $args['name'],
            'position' => $args['position'] ?? null,
            'department' => $args['department'] ?? null,
            'salary' => $args['salary'] ?? null,
            'phone' => $args['phone'] ?? null,
            'email' => $args['email'] ?? null,
            'join_date' => $args['join_date'] ?? today()->toDateString(),
            'status' => 'active',
        ]);

        $detail = collect([
            $employee->position ? "Posisi: {$employee->position}" : null,
            $employee->department ? "Departemen: {$employee->department}" : null,
            $employee->salary ? 'Gaji: Rp '.number_format($employee->salary, 0, ',', '.') : null,
        ])->filter()->implode(', ');

        return [
            'status' => 'success',
            'message' => "Karyawan **{$employee->name}** (ID: {$employeeId}) berhasil ditambahkan.".
                ($detail ? " {$detail}." : ''),
        ];
    }

    public function recordAttendance(array $args): array
    {
        $employee = Employee::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['employee_name']}%")
            ->where('status', 'active')
            ->first();

        if (! $employee) {
            return ['status' => 'not_found', 'message' => "Karyawan '{$args['employee_name']}' tidak ditemukan."];
        }

        $date = $args['date'] ?? today()->toDateString();

        // Upsert — jika sudah ada, update
        $attendance = Attendance::updateOrCreate(
            ['tenant_id' => $this->tenantId, 'employee_id' => $employee->id, 'date' => $date],
            [
                'status' => $args['status'],
                'notes' => $args['notes'] ?? null,
                'check_in' => $args['status'] === 'present' ? now()->toTimeString() : null,
            ]
        );

        $statusLabel = match ($args['status']) {
            'present' => 'Hadir',
            'absent' => 'Tidak Hadir',
            'late' => 'Terlambat',
            'leave' => 'Izin',
            'sick' => 'Sakit',
            default => $args['status'],
        };

        return [
            'status' => 'success',
            'message' => "Absensi **{$employee->name}** tanggal ".Carbon::parse($date)->format('d M Y').": **{$statusLabel}**.",
        ];
    }

    public function recordAttendanceBulk(array $args): array
    {
        $date = $args['date'] ?? today()->toDateString();
        $results = ['success' => [], 'not_found' => []];

        foreach ($args['records'] as $record) {
            $employee = Employee::where('tenant_id', $this->tenantId)
                ->where('name', 'like', "%{$record['employee_name']}%")
                ->where('status', 'active')
                ->first();

            if (! $employee) {
                $results['not_found'][] = $record['employee_name'];

                continue;
            }

            Attendance::updateOrCreate(
                ['tenant_id' => $this->tenantId, 'employee_id' => $employee->id, 'date' => $date],
                [
                    'status' => $record['status'],
                    'notes' => $record['notes'] ?? null,
                    'check_in' => $record['status'] === 'present' ? now()->toTimeString() : null,
                ]
            );

            $statusLabel = match ($record['status']) {
                'present' => 'Hadir',
                'absent' => 'Tidak Hadir',
                'late' => 'Terlambat',
                'leave' => 'Izin',
                'sick' => 'Sakit',
                default => $record['status'],
            };
            $results['success'][] = "{$employee->name} ({$statusLabel})";
        }

        $msg = '';
        if (! empty($results['success'])) {
            $msg .= 'Absensi berhasil dicatat untuk: '.implode(', ', $results['success']).'.';
        }
        if (! empty($results['not_found'])) {
            $msg .= ' Karyawan tidak ditemukan: '.implode(', ', $results['not_found']).'.';
        }

        return [
            'status' => empty($results['success']) ? 'error' : 'success',
            'message' => trim($msg),
            'date' => Carbon::parse($date)->format('d M Y'),
        ];
    }

    public function getAttendanceSummary(array $args): array
    {
        $query = Attendance::where('tenant_id', $this->tenantId);

        $query = match ($args['period']) {
            'today' => $query->whereDate('date', today()),
            'this_week' => $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]),
            'this_month' => $query->whereMonth('date', now()->month)->whereYear('date', now()->year),
            default => $query->whereDate('date', today()),
        };

        if (! empty($args['employee_name'])) {
            $query->whereHas('employee', fn ($q) => $q->where('name', 'like', "%{$args['employee_name']}%"));
        }

        $records = $query->with('employee')->get();

        $summary = [
            'period' => $args['period'],
            'present' => $records->where('status', 'present')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'late' => $records->where('status', 'late')->count(),
            'leave' => $records->where('status', 'leave')->count(),
            'total' => $records->count(),
        ];

        return ['status' => 'success', 'data' => $summary];
    }

    public function getMissingReports(array $args): array
    {
        $type = $args['type'] ?? 'weekly';
        $periodStart = isset($args['period']) ? $args['period'] : now()->startOfWeek()->toDateString();

        $allEmployees = Employee::where('tenant_id', $this->tenantId)
            ->where('status', 'active')
            ->pluck('id');

        $submitted = EmployeeReport::where('tenant_id', $this->tenantId)
            ->where('type', $type)
            ->where('period_start', $periodStart)
            ->whereIn('status', ['submitted', 'reviewed'])
            ->pluck('employee_id');

        $missing = Employee::where('tenant_id', $this->tenantId)
            ->where('status', 'active')
            ->whereNotIn('id', $submitted)
            ->get(['id', 'name', 'department', 'position']);

        if ($missing->isEmpty()) {
            return ['status' => 'success', 'message' => 'Semua karyawan sudah mengumpulkan laporan.'];
        }

        return [
            'status' => 'success',
            'data' => [
                'type' => $type,
                'period' => $periodStart,
                'missing' => $missing->map(fn ($e) => [
                    'name' => $e->name,
                    'department' => $e->department,
                    'position' => $e->position,
                ])->toArray(),
            ],
        ];
    }

    public function listEmployees(array $args): array
    {
        $query = Employee::where('tenant_id', $this->tenantId);

        $status = $args['status'] ?? 'active';
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if (! empty($args['department'])) {
            $query->where('department', 'like', '%'.$args['department'].'%');
        }

        $employees = $query->orderBy('name')->get();

        if ($employees->isEmpty()) {
            return ['status' => 'success', 'message' => 'Belum ada data karyawan.', 'data' => []];
        }

        return [
            'status' => 'success',
            'total' => $employees->count(),
            'data' => $employees->map(fn ($e) => [
                'id' => $e->employee_id ?? $e->id,
                'nama' => $e->name,
                'posisi' => $e->position ?? '-',
                'departemen' => $e->department ?? '-',
                'status' => $e->status,
                'bergabung' => $e->join_date?->format('d M Y') ?? '-',
            ])->toArray(),
        ];
    }

    public function getEmployeeInfo(array $args): array
    {
        $employee = Employee::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['employee_name']}%")
            ->first();

        if (! $employee) {
            return ['status' => 'not_found', 'message' => "Karyawan '{$args['employee_name']}' tidak ditemukan."];
        }

        $thisMonthAttendance = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'status' => 'success',
            'data' => [
                'name' => $employee->name,
                'employee_id' => $employee->employee_id,
                'position' => $employee->position,
                'department' => $employee->department,
                'status' => $employee->status,
                'join_date' => $employee->join_date?->format('d M Y'),
                'this_month' => $thisMonthAttendance,
            ],
        ];
    }
}
