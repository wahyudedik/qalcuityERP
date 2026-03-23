<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PayrollItem;
use Illuminate\Support\Collection;

/**
 * HrmAiService — AI contextual untuk fitur HRM & Payroll.
 *
 * 1. detectAttendanceAnomalies() — deteksi pola absensi tidak wajar per karyawan
 * 2. suggestSalaryComponents()   — suggest komponen gaji berdasarkan jabatan/histori
 */
class HrmAiService
{
    // ─── 1. Attendance Anomaly Detection ─────────────────────────

    /**
     * Deteksi pola absensi tidak wajar untuk semua karyawan aktif dalam N bulan terakhir.
     *
     * Pola yang dideteksi:
     * a) Absen berulang di hari tertentu (Senin/Jumat — "long weekend pattern")
     * b) Tingkat absensi > 20% dari hari kerja
     * c) Terlambat berulang (> 5x dalam sebulan)
     * d) Pola absen setelah/sebelum hari libur nasional (weekend adjacency)
     * e) Absen mendadak berulang (sick/absent bergantian tanpa pola)
     * f) Tidak ada catatan absensi sama sekali (ghost employee)
     *
     * Return: array keyed by employee_id => [
     *   'employee_name', 'anomalies' => [['type', 'severity', 'message']], 'risk' => 'high'|'medium'|'low'
     * ]
     */
    public function detectAttendanceAnomalies(int $tenantId, int $months = 3): array
    {
        $from = now()->subMonths($months)->startOfMonth()->toDateString();
        $to   = now()->endOfMonth()->toDateString();

        $employees = Employee::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();

        if ($employees->isEmpty()) return [];

        // Ambil semua absensi sekaligus
        $allAttendances = Attendance::where('tenant_id', $tenantId)
            ->whereBetween('date', [$from, $to])
            ->get()
            ->groupBy('employee_id');

        $results = [];

        foreach ($employees as $emp) {
            $records   = $allAttendances->get($emp->id, collect());
            $anomalies = [];

            // ── a) Ghost employee (tidak ada catatan) ─────────────
            if ($records->isEmpty()) {
                $anomalies[] = [
                    'type'     => 'no_records',
                    'severity' => 'medium',
                    'message'  => "Tidak ada catatan absensi dalam {$months} bulan terakhir.",
                ];
            } else {
                $totalDays   = $records->count();
                $absentDays  = $records->whereIn('status', ['absent'])->count();
                $lateDays    = $records->where('status', 'late')->count();
                $sickDays    = $records->where('status', 'sick')->count();
                $presentDays = $records->whereIn('status', ['present', 'late'])->count();

                // ── b) Tingkat absensi tinggi ──────────────────────
                $absentRate = $totalDays > 0 ? $absentDays / $totalDays : 0;
                if ($absentRate > 0.20) {
                    $pct = round($absentRate * 100, 1);
                    $anomalies[] = [
                        'type'     => 'high_absence',
                        'severity' => $absentRate > 0.35 ? 'high' : 'medium',
                        'message'  => "Tingkat absensi {$pct}% (>{$absentDays} hari absen dari {$totalDays} hari tercatat).",
                    ];
                }

                // ── c) Terlambat berulang ──────────────────────────
                if ($lateDays >= 5) {
                    $anomalies[] = [
                        'type'     => 'frequent_late',
                        'severity' => $lateDays >= 10 ? 'high' : 'medium',
                        'message'  => "Terlambat {$lateDays}x dalam {$months} bulan terakhir.",
                    ];
                }

                // ── d) Pola absen Senin/Jumat ──────────────────────
                $mondayAbsent = $records->whereIn('status', ['absent', 'sick'])
                    ->filter(fn($r) => $r->date->dayOfWeek === 1)->count(); // 1 = Monday
                $fridayAbsent = $records->whereIn('status', ['absent', 'sick'])
                    ->filter(fn($r) => $r->date->dayOfWeek === 5)->count(); // 5 = Friday

                if ($mondayAbsent >= 3) {
                    $anomalies[] = [
                        'type'     => 'monday_pattern',
                        'severity' => 'medium',
                        'message'  => "Absen/sakit di hari Senin sebanyak {$mondayAbsent}x — pola long weekend.",
                    ];
                }
                if ($fridayAbsent >= 3) {
                    $anomalies[] = [
                        'type'     => 'friday_pattern',
                        'severity' => 'medium',
                        'message'  => "Absen/sakit di hari Jumat sebanyak {$fridayAbsent}x — pola long weekend.",
                    ];
                }

                // ── e) Sakit berulang (> 8 hari) ──────────────────
                if ($sickDays > 8) {
                    $anomalies[] = [
                        'type'     => 'excessive_sick',
                        'severity' => $sickDays > 15 ? 'high' : 'medium',
                        'message'  => "Izin sakit {$sickDays} hari dalam {$months} bulan. Pertimbangkan verifikasi surat dokter.",
                    ];
                }

                // ── f) Absen berturut-turut tanpa izin ────────────
                $consecutiveAbsent = $this->maxConsecutive($records, 'absent');
                if ($consecutiveAbsent >= 3) {
                    $anomalies[] = [
                        'type'     => 'consecutive_absent',
                        'severity' => $consecutiveAbsent >= 5 ? 'high' : 'medium',
                        'message'  => "Absen tanpa keterangan {$consecutiveAbsent} hari berturut-turut.",
                    ];
                }
            }

            if (empty($anomalies)) continue;

            $maxSeverity = collect($anomalies)->contains('severity', 'high') ? 'high'
                : (collect($anomalies)->contains('severity', 'medium') ? 'medium' : 'low');

            $results[$emp->id] = [
                'employee_id'   => $emp->id,
                'employee_name' => $emp->name,
                'position'      => $emp->position,
                'department'    => $emp->department,
                'anomalies'     => $anomalies,
                'risk'          => $maxSeverity,
            ];
        }

        // Sort by risk: high first
        uasort($results, fn($a, $b) => match(true) {
            $a['risk'] === 'high' && $b['risk'] !== 'high' => -1,
            $a['risk'] !== 'high' && $b['risk'] === 'high' => 1,
            default => 0,
        });

        return $results;
    }

    /**
     * Hitung jumlah hari berturut-turut dengan status tertentu.
     */
    private function maxConsecutive(Collection $records, string $status): int
    {
        $sorted = $records->sortBy('date')->values();
        $max = $current = 0;

        foreach ($sorted as $i => $rec) {
            if ($rec->status === $status) {
                $current++;
                $max = max($max, $current);
            } else {
                $current = 0;
            }
        }

        return $max;
    }

    // ─── 2. Salary Component Suggestion ──────────────────────────

    /**
     * Suggest komponen gaji untuk karyawan berdasarkan jabatan + histori payroll.
     *
     * Komponen yang disarankan:
     * - Gaji pokok (benchmark dari karyawan jabatan/departemen sama)
     * - Tunjangan transport (flat berdasarkan level jabatan)
     * - Tunjangan makan (flat)
     * - Tunjangan jabatan (% dari gaji pokok berdasarkan level)
     * - Overtime estimate (dari histori)
     *
     * Return: [
     *   'base_salary'        => ['suggested', 'basis', 'confidence'],
     *   'allowance_transport'=> ['suggested', 'basis'],
     *   'allowance_meal'     => ['suggested', 'basis'],
     *   'allowance_position' => ['suggested', 'basis'],
     *   'total_suggested'    => float,
     *   'benchmark_note'     => string,
     * ]
     */
    public function suggestSalaryComponents(int $tenantId, int $employeeId): array
    {
        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($employeeId);

        $baseSuggestion = $this->suggestBaseSalary($tenantId, $employee);
        $allowances     = $this->suggestAllowances($employee, $baseSuggestion['suggested']);

        $total = $baseSuggestion['suggested']
            + $allowances['transport']['suggested']
            + $allowances['meal']['suggested']
            + $allowances['position']['suggested'];

        return [
            'employee_id'        => $employee->id,
            'employee_name'      => $employee->name,
            'position'           => $employee->position,
            'department'         => $employee->department,
            'current_salary'     => (float) $employee->salary,
            'base_salary'        => $baseSuggestion,
            'allowance_transport'=> $allowances['transport'],
            'allowance_meal'     => $allowances['meal'],
            'allowance_position' => $allowances['position'],
            'total_suggested'    => round($total, -3),
            'benchmark_note'     => $baseSuggestion['benchmark_note'] ?? '',
        ];
    }

    /**
     * Suggest gaji pokok berdasarkan benchmark karyawan sejabatan.
     */
    private function suggestBaseSalary(int $tenantId, Employee $employee): array
    {
        // Benchmark: karyawan dengan jabatan sama
        $samePosition = Employee::where('tenant_id', $tenantId)
            ->where('id', '!=', $employee->id)
            ->where('status', 'active')
            ->whereNotNull('salary')
            ->where('salary', '>', 0)
            ->when($employee->position, fn($q) => $q->where('position', $employee->position))
            ->pluck('salary');

        if ($samePosition->isNotEmpty()) {
            $avg    = $samePosition->avg();
            $median = $this->median($samePosition->toArray());
            $suggested = round($median, -3);
            return [
                'suggested'      => $suggested,
                'confidence'     => $samePosition->count() >= 3 ? 'high' : 'medium',
                'basis'          => "Median gaji {$samePosition->count()} karyawan jabatan \"{$employee->position}\"",
                'benchmark_note' => "Min: Rp " . number_format($samePosition->min(), 0, ',', '.') .
                                    " | Median: Rp " . number_format($median, 0, ',', '.') .
                                    " | Max: Rp " . number_format($samePosition->max(), 0, ',', '.'),
            ];
        }

        // Fallback: benchmark departemen
        if ($employee->department) {
            $sameDept = Employee::where('tenant_id', $tenantId)
                ->where('id', '!=', $employee->id)
                ->where('status', 'active')
                ->whereNotNull('salary')
                ->where('salary', '>', 0)
                ->where('department', $employee->department)
                ->pluck('salary');

            if ($sameDept->isNotEmpty()) {
                $avg = round($sameDept->avg(), -3);
                return [
                    'suggested'      => $avg,
                    'confidence'     => 'low',
                    'basis'          => "Rata-rata gaji departemen \"{$employee->department}\" ({$sameDept->count()} karyawan)",
                    'benchmark_note' => "Tidak ada karyawan dengan jabatan yang sama. Menggunakan rata-rata departemen.",
                ];
            }
        }

        // Fallback: gaji saat ini atau UMR estimasi
        $current = (float) $employee->salary;
        return [
            'suggested'      => $current > 0 ? $current : 5_000_000,
            'confidence'     => 'low',
            'basis'          => $current > 0 ? 'Gaji saat ini (tidak ada data benchmark)' : 'Estimasi minimum (tidak ada histori)',
            'benchmark_note' => 'Tidak cukup data untuk benchmark. Sesuaikan secara manual.',
        ];
    }

    /**
     * Suggest tunjangan berdasarkan level jabatan.
     */
    private function suggestAllowances(Employee $employee, float $baseSalary): array
    {
        $level = $this->detectJobLevel($employee->position ?? '');

        $transportMap = ['senior' => 750_000, 'manager' => 1_000_000, 'director' => 1_500_000, 'staff' => 500_000];
        $mealMap      = ['senior' => 600_000, 'manager' => 750_000,   'director' => 1_000_000, 'staff' => 450_000];
        $positionPct  = ['senior' => 0.10,    'manager' => 0.15,      'director' => 0.20,      'staff' => 0.05];

        $transport = $transportMap[$level] ?? 500_000;
        $meal      = $mealMap[$level] ?? 450_000;
        $position  = round($baseSalary * ($positionPct[$level] ?? 0.05), -3);

        return [
            'transport' => [
                'suggested' => $transport,
                'basis'     => "Standar tunjangan transport level {$level}",
            ],
            'meal' => [
                'suggested' => $meal,
                'basis'     => "Standar tunjangan makan level {$level}",
            ],
            'position' => [
                'suggested' => $position,
                'basis'     => "Tunjangan jabatan " . (($positionPct[$level] ?? 0.05) * 100) . "% dari gaji pokok (level {$level})",
            ],
        ];
    }

    /**
     * Deteksi level jabatan dari string posisi.
     */
    private function detectJobLevel(string $position): string
    {
        $pos = strtolower($position);
        if (preg_match('/director|ceo|cfo|cto|president|vp|vice/i', $pos)) return 'director';
        if (preg_match('/manager|kepala|head|lead|supervisor|spv/i', $pos))  return 'manager';
        if (preg_match('/senior|sr\.|specialist|expert|analyst/i', $pos))    return 'senior';
        return 'staff';
    }

    private function median(array $values): float
    {
        sort($values);
        $count = count($values);
        $mid   = (int) floor($count / 2);
        return $count % 2 === 0
            ? ($values[$mid - 1] + $values[$mid]) / 2
            : $values[$mid];
    }
}
