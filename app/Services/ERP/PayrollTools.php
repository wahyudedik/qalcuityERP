<?php

namespace App\Services\ERP;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PayrollItem;
use App\Models\PayrollRun;

class PayrollTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name' => 'run_payroll',
                'description' => 'Hitung gaji karyawan otomatis berdasarkan data absensi untuk periode tertentu.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'Periode penggajian YYYY-MM (default: bulan ini)'],
                        'working_days' => ['type' => 'integer', 'description' => 'Jumlah hari kerja dalam periode (default: 26)'],
                        'include_bpjs' => ['type' => 'boolean', 'description' => 'Hitung potongan BPJS (default: true)'],
                        'employee_name' => ['type' => 'string', 'description' => 'Nama karyawan tertentu (opsional, kosong = semua)'],
                    ],
                ],
            ],
            [
                'name' => 'get_payroll_summary',
                'description' => 'Lihat ringkasan penggajian untuk periode tertentu.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'Periode YYYY-MM (default: bulan ini)'],
                    ],
                ],
            ],
            [
                'name' => 'get_payslip',
                'description' => 'Tampilkan slip gaji karyawan tertentu.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'employee_name' => ['type' => 'string', 'description' => 'Nama karyawan'],
                        'period' => ['type' => 'string', 'description' => 'Periode YYYY-MM (default: bulan ini)'],
                    ],
                    'required' => ['employee_name'],
                ],
            ],
            [
                'name' => 'mark_payroll_paid',
                'description' => 'Tandai penggajian periode tertentu sebagai sudah dibayar.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'Periode YYYY-MM'],
                    ],
                    'required' => ['period'],
                ],
            ],
        ];
    }

    public function runPayroll(array $args): array
    {
        $period = $args['period'] ?? now()->format('Y-m');
        $workingDays = $args['working_days'] ?? 26;
        $includeBpjs = $args['include_bpjs'] ?? true;

        // Cek apakah sudah ada payroll run untuk periode ini
        $existing = PayrollRun::where('tenant_id', $this->tenantId)->where('period', $period)->first();
        if ($existing && $existing->status !== 'draft') {
            return ['status' => 'error', 'message' => "Penggajian periode {$period} sudah diproses (status: {$existing->status})."];
        }

        $run = $existing ?? PayrollRun::create([
            'tenant_id' => $this->tenantId,
            'period' => $period,
            'status' => 'draft',
            'processed_by' => $this->userId,
        ]);

        $employeeQuery = Employee::where('tenant_id', $this->tenantId)->where('status', 'active');
        if (! empty($args['employee_name'])) {
            $employeeQuery->where('name', 'like', "%{$args['employee_name']}%");
        }
        $employees = $employeeQuery->get();

        if ($employees->isEmpty()) {
            return ['status' => 'error', 'message' => 'Tidak ada karyawan aktif.'];
        }

        [$year, $month] = explode('-', $period);
        $totalGross = 0;
        $totalDeductions = 0;
        $totalNet = 0;
        $items = [];

        foreach ($employees as $emp) {
            // Hitung absensi dari tabel attendance
            $attendance = Attendance::where('tenant_id', $this->tenantId)
                ->where('employee_id', $emp->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->selectRaw('status, count(*) as cnt')
                ->groupBy('status')
                ->pluck('cnt', 'status');

            $presentDays = ($attendance['present'] ?? 0) + ($attendance['late'] ?? 0);
            $absentDays = $attendance['absent'] ?? 0;
            $lateDays = $attendance['late'] ?? 0;

            $baseSalary = $emp->salary ?? 0;
            if ($baseSalary == 0) {
                continue;
            }

            // Hitung komponen gaji
            $dailyRate = $baseSalary / $workingDays;
            $deductAbsent = $dailyRate * $absentDays;
            $deductLate = ($dailyRate / 8) * $lateDays; // 1 jam per terlambat
            $grossSalary = $baseSalary - $deductAbsent - $deductLate;

            // BPJS: 2% JHT + 1% JP employee
            $bpjs = $includeBpjs ? round($grossSalary * 0.03) : 0;

            // PPh 21 sederhana: 5% untuk penghasilan > 4.5jt/bln (PTKP TK/0)
            $annualGross = $grossSalary * 12;
            $ptkp = 54000000; // TK/0
            $pkp = max(0, $annualGross - $ptkp);
            $pph21 = round(($pkp <= 60000000 ? $pkp * 0.05 : 3000000 + ($pkp - 60000000) * 0.15) / 12);

            $netSalary = $grossSalary - $bpjs - $pph21;

            PayrollItem::updateOrCreate(
                ['tenant_id' => $this->tenantId, 'payroll_run_id' => $run->id, 'employee_id' => $emp->id],
                [
                    'base_salary' => $baseSalary,
                    'working_days' => $workingDays,
                    'present_days' => $presentDays,
                    'absent_days' => $absentDays,
                    'late_days' => $lateDays,
                    'deduction_absent' => $deductAbsent,
                    'deduction_late' => $deductLate,
                    'gross_salary' => $grossSalary,
                    'bpjs_employee' => $bpjs,
                    'tax_pph21' => $pph21,
                    'net_salary' => $netSalary,
                    'status' => 'pending',
                ]
            );

            $totalGross += $grossSalary;
            $totalDeductions += $bpjs + $pph21 + $deductAbsent + $deductLate;
            $totalNet += $netSalary;

            $items[] = [
                'name' => $emp->name,
                'gaji_pokok' => 'Rp '.number_format($baseSalary, 0, ',', '.'),
                'potongan' => 'Rp '.number_format($bpjs + $pph21 + $deductAbsent + $deductLate, 0, ',', '.'),
                'gaji_bersih' => 'Rp '.number_format($netSalary, 0, ',', '.'),
            ];
        }

        $run->update([
            'status' => 'processed',
            'total_gross' => $totalGross,
            'total_deductions' => $totalDeductions,
            'total_net' => $totalNet,
            'processed_at' => now(),
        ]);

        return [
            'status' => 'success',
            'period' => $period,
            'total_karyawan' => count($items),
            'total_gaji_kotor' => 'Rp '.number_format($totalGross, 0, ',', '.'),
            'total_potongan' => 'Rp '.number_format($totalDeductions, 0, ',', '.'),
            'total_gaji_bersih' => 'Rp '.number_format($totalNet, 0, ',', '.'),
            'data' => $items,
        ];
    }

    public function getPayrollSummary(array $args): array
    {
        $period = $args['period'] ?? now()->format('Y-m');
        $run = PayrollRun::where('tenant_id', $this->tenantId)->where('period', $period)->first();

        if (! $run) {
            return ['status' => 'not_found', 'message' => "Belum ada data penggajian untuk periode {$period}. Gunakan run_payroll untuk menghitung."];
        }

        $items = PayrollItem::where('payroll_run_id', $run->id)->with('employee')->get();

        return [
            'status' => 'success',
            'period' => $period,
            'run_status' => $run->status,
            'total_karyawan' => $items->count(),
            'total_gaji_kotor' => 'Rp '.number_format($run->total_gross, 0, ',', '.'),
            'total_potongan' => 'Rp '.number_format($run->total_deductions, 0, ',', '.'),
            'total_gaji_bersih' => 'Rp '.number_format($run->total_net, 0, ',', '.'),
            'data' => $items->map(fn ($i) => [
                'nama' => $i->employee->name,
                'gaji_pokok' => 'Rp '.number_format($i->base_salary, 0, ',', '.'),
                'hadir' => $i->present_days.' hari',
                'absen' => $i->absent_days.' hari',
                'gaji_bersih' => 'Rp '.number_format($i->net_salary, 0, ',', '.'),
                'status' => $i->status,
            ])->toArray(),
        ];
    }

    public function getPayslip(array $args): array
    {
        $period = $args['period'] ?? now()->format('Y-m');
        $emp = Employee::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['employee_name']}%")
            ->first();

        if (! $emp) {
            return ['status' => 'error', 'message' => "Karyawan '{$args['employee_name']}' tidak ditemukan."];
        }

        $run = PayrollRun::where('tenant_id', $this->tenantId)->where('period', $period)->first();
        $item = $run ? PayrollItem::where('payroll_run_id', $run->id)->where('employee_id', $emp->id)->first() : null;

        if (! $item) {
            return ['status' => 'not_found', 'message' => "Slip gaji {$emp->name} untuk periode {$period} belum tersedia."];
        }

        return [
            'status' => 'success',
            'payslip' => [
                'nama' => $emp->name,
                'jabatan' => $emp->position ?? '-',
                'departemen' => $emp->department ?? '-',
                'periode' => $period,
                'gaji_pokok' => 'Rp '.number_format($item->base_salary, 0, ',', '.'),
                'hari_kerja' => $item->working_days,
                'hari_hadir' => $item->present_days,
                'hari_absen' => $item->absent_days,
                'tunjangan' => 'Rp '.number_format($item->allowances, 0, ',', '.'),
                'lembur' => 'Rp '.number_format($item->overtime_pay, 0, ',', '.'),
                'pot_absen' => '- Rp '.number_format($item->deduction_absent, 0, ',', '.'),
                'pot_terlambat' => '- Rp '.number_format($item->deduction_late, 0, ',', '.'),
                'bpjs_karyawan' => '- Rp '.number_format($item->bpjs_employee, 0, ',', '.'),
                'pph21' => '- Rp '.number_format($item->tax_pph21, 0, ',', '.'),
                'gaji_bersih' => 'Rp '.number_format($item->net_salary, 0, ',', '.'),
                'status' => $item->status,
            ],
        ];
    }

    public function markPayrollPaid(array $args): array
    {
        $run = PayrollRun::where('tenant_id', $this->tenantId)->where('period', $args['period'])->first();

        if (! $run) {
            return ['status' => 'error', 'message' => "Data penggajian periode {$args['period']} tidak ditemukan."];
        }

        $run->update(['status' => 'paid']);
        PayrollItem::where('payroll_run_id', $run->id)->update(['status' => 'paid']);

        return [
            'status' => 'success',
            'message' => "Penggajian periode **{$args['period']}** telah ditandai sebagai **DIBAYAR**. "
                .'Total: Rp '.number_format($run->total_net, 0, ',', '.')." untuk {$run->items()->count()} karyawan.",
        ];
    }
}
