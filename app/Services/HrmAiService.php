<?php

namespace App\Services;

use App\Enums\AiUseCase;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PayrollItem;
use App\Models\PerformanceReview;
use Illuminate\Support\Collection;

/**
 * HrmAiService — AI contextual untuk fitur HRM & Payroll.
 *
 * 1. detectAttendanceAnomalies() — deteksi pola absensi tidak wajar per karyawan
 * 2. suggestSalaryComponents()   — suggest komponen gaji berdasarkan jabatan/histori
 *
 * Use Cases:
 * - suggestSalaryComponents() uses AiUseCase::CRUD_AI
 * - detectAttendanceAnomalies() uses AiUseCase::ANOMALY_DETECTION
 */
class HrmAiService
{
    // ─── 1. Attendance Anomaly Detection ─────────────────────────

    /**
     * Deteksi pola absensi tidak wajar untuk semua karyawan aktif dalam N bulan terakhir.
     *
     * Use Case: AiUseCase::ANOMALY_DETECTION
     * When AI provider is integrated, pass: AiUseCase::ANOMALY_DETECTION->value
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
     *
     * Requirements: 8.5
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
        uasort($results, fn($a, $b) => match (true) {
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
     * Use Case: AiUseCase::CRUD_AI
     * When AI provider is integrated, pass: AiUseCase::CRUD_AI->value
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
     *
     * Requirements: 8.5
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
            'allowance_transport' => $allowances['transport'],
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

    // ─── 3. Career Path Prediction ───────────────────────────────

    /**
     * Prediksi jalur karir karyawan berdasarkan:
     * - Histori skor performance review (tren naik/turun/stabil)
     * - Lama kerja (tenure)
     * - Departemen & jabatan saat ini
     * - Rekomendasi reviewer historis
     * - Pola absensi (sebagai faktor pengurang)
     *
     * Return: [
     *   'employee'          => [...],
     *   'readiness_score'   => 0-100,
     *   'readiness_label'   => string,
     *   'promotion_eta'     => string,   // e.g. "6-12 bulan"
     *   'suggested_roles'   => [...],
     *   'trend'             => 'improving'|'stable'|'declining',
     *   'factors'           => [...],    // faktor pendukung & penghambat
     *   'action_plan'       => [...],    // rekomendasi konkret
     *   'data_quality'      => 'good'|'limited'|'insufficient',
     * ]
     */
    public function careerPathPrediction(int $tenantId, int $employeeId): array
    {
        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($employeeId);

        $reviews = PerformanceReview::where('tenant_id', $tenantId)
            ->where('employee_id', $employeeId)
            ->orderBy('submitted_at')
            ->get();

        $attendances = Attendance::where('tenant_id', $tenantId)
            ->where('employee_id', $employeeId)
            ->whereDate('date', '>=', now()->subMonths(6))
            ->get();

        // ── Tenure ───────────────────────────────────────────────
        $joinDate    = $employee->join_date ?? now()->subYear();
        $tenureMonths = (int) $joinDate->diffInMonths(now());
        $tenureYears  = round($tenureMonths / 12, 1);

        // ── Performance score analysis ────────────────────────────
        $scores = $reviews->pluck('overall_score')->map(fn($s) => (float) $s);
        $avgScore    = $scores->isNotEmpty() ? round($scores->avg(), 2) : null;
        $latestScore = $scores->isNotEmpty() ? (float) $scores->last() : null;
        $trend       = $this->computeTrend($scores);

        // ── Reviewer recommendations ──────────────────────────────
        $promoteCount  = $reviews->where('recommendation', 'promote')->count();
        $retainCount   = $reviews->where('recommendation', 'retain')->count();
        $pipCount      = $reviews->where('recommendation', 'pip')->count();
        $terminateCount = $reviews->where('recommendation', 'terminate')->count();

        // ── Attendance quality ────────────────────────────────────
        $totalAtt    = $attendances->count();
        $absentRate  = $totalAtt > 0
            ? $attendances->whereIn('status', ['absent'])->count() / $totalAtt
            : 0;
        $lateRate    = $totalAtt > 0
            ? $attendances->where('status', 'late')->count() / $totalAtt
            : 0;

        // ── Readiness score (0–100) ───────────────────────────────
        $readiness = $this->computeReadiness(
            avgScore: $avgScore,
            latestScore: $latestScore,
            trend: $trend,
            tenureMonths: $tenureMonths,
            promoteCount: $promoteCount,
            pipCount: $pipCount,
            terminateCount: $terminateCount,
            absentRate: $absentRate,
            lateRate: $lateRate,
            reviewCount: $reviews->count(),
        );

        // ── Promotion ETA ─────────────────────────────────────────
        $eta = $this->estimatePromotionEta($readiness, $trend, $tenureMonths);

        // ── Suggested next roles ──────────────────────────────────
        $suggestedRoles = $this->suggestNextRoles($employee, $readiness, $tenureMonths);

        // ── Factors ──────────────────────────────────────────────
        $factors = $this->buildFactors(
            $avgScore,
            $latestScore,
            $trend,
            $tenureMonths,
            $promoteCount,
            $pipCount,
            $absentRate,
            $lateRate,
            $reviews->count()
        );

        // ── Action plan ───────────────────────────────────────────
        $actionPlan = $this->buildActionPlan($readiness, $trend, $avgScore, $pipCount, $absentRate, $lateRate);

        // ── Data quality ──────────────────────────────────────────
        $dataQuality = match (true) {
            $reviews->count() >= 4 => 'good',
            $reviews->count() >= 2 => 'limited',
            default                => 'insufficient',
        };

        return [
            'employee' => [
                'id'           => $employee->id,
                'name'         => $employee->name,
                'position'     => $employee->position ?? '-',
                'department'   => $employee->department ?? '-',
                'tenure_months' => $tenureMonths,
                'tenure_label' => $tenureYears >= 1
                    ? "{$tenureYears} tahun"
                    : "{$tenureMonths} bulan",
                'join_date'    => $joinDate->format('d M Y'),
            ],
            'readiness_score'  => $readiness,
            'readiness_label'  => $this->readinessLabel($readiness),
            'readiness_color'  => $this->readinessColor($readiness),
            'promotion_eta'    => $eta,
            'suggested_roles'  => $suggestedRoles,
            'trend'            => $trend,
            'trend_label'      => match ($trend) {
                'improving' => 'Meningkat',
                'declining' => 'Menurun',
                default => 'Stabil',
            },
            'avg_score'        => $avgScore,
            'latest_score'     => $latestScore,
            'review_count'     => $reviews->count(),
            'promote_count'    => $promoteCount,
            'factors'          => $factors,
            'action_plan'      => $actionPlan,
            'data_quality'     => $dataQuality,
        ];
    }

    private function computeTrend(Collection $scores): string
    {
        if ($scores->count() < 2) return 'stable';

        $arr   = $scores->values()->toArray();
        $n     = count($arr);
        $first = array_slice($arr, 0, (int) ceil($n / 2));
        $last  = array_slice($arr, (int) floor($n / 2));

        $avgFirst = array_sum($first) / count($first);
        $avgLast  = array_sum($last) / count($last);
        $delta    = $avgLast - $avgFirst;

        if ($delta >= 0.3)  return 'improving';
        if ($delta <= -0.3) return 'declining';
        return 'stable';
    }

    private function computeReadiness(
        ?float $avgScore,
        ?float $latestScore,
        string $trend,
        int $tenureMonths,
        int $promoteCount,
        int $pipCount,
        int $terminateCount,
        float $absentRate,
        float $lateRate,
        int $reviewCount
    ): int {
        $score = 0;

        // Performance score (max 40 pts)
        if ($avgScore !== null) {
            $score += min(40, (int) round(($avgScore / 5) * 40));
        } else {
            $score += 15; // neutral if no data
        }

        // Trend bonus/penalty (max ±10)
        $score += match ($trend) {
            'improving' => 10,
            'declining' => -10,
            default => 0
        };

        // Tenure (max 20 pts) — sweet spot 18-60 months
        if ($tenureMonths >= 60)      $score += 20;
        elseif ($tenureMonths >= 36)  $score += 18;
        elseif ($tenureMonths >= 18)  $score += 14;
        elseif ($tenureMonths >= 12)  $score += 10;
        elseif ($tenureMonths >= 6)   $score += 6;
        else                          $score += 2;

        // Reviewer recommendations (max 20 pts)
        $score += min(20, $promoteCount * 7);
        $score -= min(15, $pipCount * 5);
        $score -= min(20, $terminateCount * 10);

        // Attendance penalty (max -15)
        $score -= (int) min(10, round($absentRate * 30));
        $score -= (int) min(5,  round($lateRate * 15));

        // Data quality bonus (max 5)
        if ($reviewCount >= 4) $score += 5;
        elseif ($reviewCount >= 2) $score += 2;

        return max(0, min(100, $score));
    }

    private function estimatePromotionEta(int $readiness, string $trend, int $tenureMonths): string
    {
        if ($readiness >= 80) return 'Siap sekarang / 1-3 bulan';
        if ($readiness >= 65) return $trend === 'improving' ? '3-6 bulan' : '6-9 bulan';
        if ($readiness >= 50) return $trend === 'improving' ? '6-12 bulan' : '12-18 bulan';
        if ($readiness >= 35) return '18-24 bulan';
        return 'Lebih dari 2 tahun (perlu perbaikan signifikan)';
    }

    private function suggestNextRoles(Employee $employee, int $readiness, int $tenureMonths): array
    {
        $pos   = strtolower($employee->position ?? '');
        $dept  = $employee->department ?? '';
        $level = $this->detectJobLevel($employee->position ?? '');

        $roles = [];

        // Level-based progression
        if ($level === 'staff') {
            $roles[] = ['title' => "Senior {$employee->position}", 'fit' => $readiness >= 50 ? 'high' : 'medium', 'note' => 'Langkah pertama — pendalaman keahlian teknis'];
            $roles[] = ['title' => "Supervisor / Team Lead {$dept}", 'fit' => $readiness >= 65 ? 'high' : 'low', 'note' => 'Membutuhkan kemampuan koordinasi tim'];
        } elseif ($level === 'senior') {
            $roles[] = ['title' => "Supervisor / Team Lead {$dept}", 'fit' => $readiness >= 55 ? 'high' : 'medium', 'note' => 'Transisi ke peran kepemimpinan'];
            $roles[] = ['title' => "Manager {$dept}", 'fit' => $readiness >= 75 ? 'high' : 'low', 'note' => 'Membutuhkan pengalaman manajemen & tenure ≥3 tahun'];
        } elseif ($level === 'manager') {
            $roles[] = ['title' => "Senior Manager / Head of {$dept}", 'fit' => $readiness >= 65 ? 'high' : 'medium', 'note' => 'Perluas scope tanggung jawab'];
            $roles[] = ['title' => "Director / VP {$dept}", 'fit' => $readiness >= 80 ? 'high' : 'low', 'note' => 'Membutuhkan track record kepemimpinan kuat'];
        } else {
            $roles[] = ['title' => "C-Level / VP", 'fit' => $readiness >= 85 ? 'high' : 'medium', 'note' => 'Posisi eksekutif senior'];
        }

        // Cross-functional suggestion if high performer
        if ($readiness >= 70 && $tenureMonths >= 24) {
            $roles[] = ['title' => "Rotasi ke departemen lain (cross-functional)", 'fit' => 'medium', 'note' => 'Memperluas perspektif bisnis — cocok untuk high performer'];
        }

        return $roles;
    }

    private function buildFactors(
        ?float $avgScore,
        ?float $latestScore,
        string $trend,
        int $tenureMonths,
        int $promoteCount,
        int $pipCount,
        float $absentRate,
        float $lateRate,
        int $reviewCount
    ): array {
        $positive = [];
        $negative = [];

        if ($avgScore !== null && $avgScore >= 4.0) $positive[] = "Rata-rata skor kinerja tinggi (" . number_format($avgScore, 1) . "/5)";
        if ($avgScore !== null && $avgScore >= 3.5 && $avgScore < 4.0) $positive[] = "Skor kinerja di atas rata-rata (" . number_format($avgScore, 1) . "/5)";
        if ($trend === 'improving') $positive[] = "Tren kinerja meningkat secara konsisten";
        if ($tenureMonths >= 24) $positive[] = "Tenure memadai (" . round($tenureMonths / 12, 1) . " tahun) — memahami budaya perusahaan";
        if ($promoteCount >= 2) $positive[] = "Direkomendasikan promosi oleh reviewer sebanyak {$promoteCount}x";
        if ($absentRate < 0.05) $positive[] = "Tingkat kehadiran sangat baik (absen < 5%)";
        if ($latestScore !== null && $latestScore >= 4.5) $positive[] = "Skor review terbaru sangat tinggi (" . number_format($latestScore, 1) . "/5)";

        if ($avgScore !== null && $avgScore < 3.0) $negative[] = "Rata-rata skor kinerja di bawah standar (" . number_format($avgScore, 1) . "/5)";
        if ($trend === 'declining') $negative[] = "Tren kinerja menurun — perlu perhatian segera";
        if ($tenureMonths < 12) $negative[] = "Tenure masih singkat ({$tenureMonths} bulan) — perlu lebih banyak pengalaman";
        if ($pipCount >= 1) $negative[] = "Pernah masuk PIP (Performance Improvement Plan) sebanyak {$pipCount}x";
        if ($absentRate > 0.15) $negative[] = "Tingkat absensi tinggi (" . round($absentRate * 100, 1) . "%)";
        if ($lateRate > 0.20) $negative[] = "Sering terlambat (" . round($lateRate * 100, 1) . "% dari hari kerja)";
        if ($reviewCount < 2) $negative[] = "Data review terbatas ({$reviewCount} review) — prediksi kurang akurat";

        return ['positive' => $positive, 'negative' => $negative];
    }

    private function buildActionPlan(
        int $readiness,
        string $trend,
        ?float $avgScore,
        int $pipCount,
        float $absentRate,
        float $lateRate
    ): array {
        $actions = [];

        if ($readiness >= 75) {
            $actions[] = ['priority' => 'high', 'action' => 'Diskusikan rencana promosi dengan atasan langsung dalam 1-2 bulan ke depan'];
            $actions[] = ['priority' => 'high', 'action' => 'Siapkan succession plan — identifikasi pengganti di posisi saat ini'];
        } elseif ($readiness >= 50) {
            $actions[] = ['priority' => 'high', 'action' => 'Tetapkan target kinerja spesifik untuk 2 kuartal ke depan'];
            $actions[] = ['priority' => 'medium', 'action' => 'Ikutkan dalam program mentoring atau leadership training'];
        } else {
            $actions[] = ['priority' => 'high', 'action' => 'Buat Performance Improvement Plan (PIP) dengan target terukur'];
            $actions[] = ['priority' => 'high', 'action' => 'Jadwalkan 1-on-1 rutin dengan atasan (minimal 2x per bulan)'];
        }

        if ($trend === 'declining') {
            $actions[] = ['priority' => 'high', 'action' => 'Identifikasi penyebab penurunan kinerja — lakukan exit interview preventif'];
        }
        if ($absentRate > 0.10) {
            $actions[] = ['priority' => 'medium', 'action' => 'Tindak lanjuti pola absensi — pertimbangkan konseling atau penyesuaian jadwal'];
        }
        if ($lateRate > 0.15) {
            $actions[] = ['priority' => 'medium', 'action' => 'Diskusikan fleksibilitas jam kerja atau identifikasi hambatan ketepatan waktu'];
        }
        if ($avgScore !== null && $avgScore >= 4.0 && $readiness < 70) {
            $actions[] = ['priority' => 'medium', 'action' => 'Karyawan berpotensi tinggi — pertimbangkan stretch assignment atau proyek lintas departemen'];
        }

        $actions[] = ['priority' => 'low', 'action' => 'Lakukan review kinerja berikutnya tepat waktu untuk memperbarui prediksi ini'];

        return $actions;
    }

    private function readinessLabel(int $score): string
    {
        return match (true) {
            $score >= 80 => 'Sangat Siap',
            $score >= 65 => 'Siap',
            $score >= 50 => 'Hampir Siap',
            $score >= 35 => 'Perlu Pengembangan',
            default      => 'Belum Siap',
        };
    }

    private function readinessColor(int $score): string
    {
        return match (true) {
            $score >= 80 => 'green',
            $score >= 65 => 'blue',
            $score >= 50 => 'amber',
            $score >= 35 => 'orange',
            default      => 'red',
        };
    }

    // ─── 4. Turnover Risk Score ───────────────────────────────────

    /**
     * Hitung skor risiko resign untuk semua karyawan aktif dalam satu tenant.
     *
     * Sinyal yang dianalisis:
     * a) Tren penurunan performance review
     * b) Pola absensi memburuk (absen/terlambat meningkat)
     * c) Lama tidak naik gaji (dibanding payroll historis)
     * d) Tenure terlalu singkat (< 1 tahun) atau terlalu lama tanpa promosi (> 5 tahun)
     * e) Penggunaan cuti berlebihan (burnout signal)
     * f) Rekomendasi PIP/terminate dari reviewer
     * g) Tidak ada review kinerja sama sekali (disengagement)
     *
     * Return: array of employees sorted by risk desc, each with:
     * [employee_id, name, position, department, risk_score (0-100),
     *  risk_level, signals, recommendations]
     */
    public function turnoverRiskScore(int $tenantId): array
    {
        $employees = Employee::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();

        if ($employees->isEmpty()) return [];

        $now = now();
        $sixMonthsAgo = $now->copy()->subMonths(6)->startOfMonth()->toDateString();

        // Bulk-load data
        $allReviews = \App\Models\PerformanceReview::where('tenant_id', $tenantId)
            ->orderBy('submitted_at')
            ->get()
            ->groupBy('employee_id');

        $allAttendances = \App\Models\Attendance::where('tenant_id', $tenantId)
            ->whereDate('date', '>=', $sixMonthsAgo)
            ->get()
            ->groupBy('employee_id');

        $allLeaves = \App\Models\LeaveRequest::where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->whereDate('start_date', '>=', $now->copy()->subYear()->toDateString())
            ->get()
            ->groupBy('employee_id');

        // Payroll: get last 2 distinct base_salary values per employee to detect salary stagnation
        $allPayroll = \App\Models\PayrollItem::where('tenant_id', $tenantId)
            ->orderBy('created_at')
            ->get()
            ->groupBy('employee_id');

        $results = [];

        foreach ($employees as $emp) {
            $reviews    = $allReviews->get($emp->id, collect());
            $attendances = $allAttendances->get($emp->id, collect());
            $leaves     = $allLeaves->get($emp->id, collect());
            $payrolls   = $allPayroll->get($emp->id, collect());

            $signals = [];
            $riskScore = 0;

            // ── a) Performance trend ──────────────────────────────
            $scores = $reviews->pluck('overall_score')->map(fn($s) => (float)$s);
            $trend  = $this->computeTrend($scores);
            $avgScore = $scores->isNotEmpty() ? round($scores->avg(), 2) : null;
            $latestScore = $scores->isNotEmpty() ? (float)$scores->last() : null;

            if ($trend === 'declining') {
                $riskScore += 20;
                $signals[] = [
                    'type' => 'performance',
                    'severity' => 'high',
                    'message' => 'Tren kinerja menurun secara konsisten — sinyal kuat disengagement.'
                ];
            }
            if ($avgScore !== null && $avgScore < 3.0) {
                $riskScore += 10;
                $signals[] = [
                    'type' => 'performance',
                    'severity' => 'medium',
                    'message' => 'Rata-rata skor kinerja rendah (' . number_format($avgScore, 1) . '/5).'
                ];
            }
            $pipCount = $reviews->where('recommendation', 'pip')->count();
            $termCount = $reviews->where('recommendation', 'terminate')->count();
            if ($pipCount >= 1) {
                $riskScore += 10;
                $signals[] = [
                    'type' => 'performance',
                    'severity' => 'medium',
                    'message' => "Masuk PIP sebanyak {$pipCount}x — risiko frustrasi & resign."
                ];
            }
            if ($termCount >= 1) {
                $riskScore += 15;
                $signals[] = [
                    'type' => 'performance',
                    'severity' => 'high',
                    'message' => "Direkomendasikan terminate {$termCount}x — situasi kritis."
                ];
            }
            if ($reviews->isEmpty()) {
                $riskScore += 8;
                $signals[] = [
                    'type' => 'engagement',
                    'severity' => 'low',
                    'message' => 'Tidak ada data penilaian kinerja — potensi disengagement tidak terdeteksi.'
                ];
            }

            // ── b) Attendance deterioration ───────────────────────
            $totalAtt   = $attendances->count();
            $absentDays = $attendances->whereIn('status', ['absent'])->count();
            $lateDays   = $attendances->where('status', 'late')->count();
            $absentRate = $totalAtt > 0 ? $absentDays / $totalAtt : 0;
            $lateRate   = $totalAtt > 0 ? $lateDays / $totalAtt : 0;

            if ($absentRate > 0.20) {
                $riskScore += 15;
                $signals[] = [
                    'type' => 'attendance',
                    'severity' => 'high',
                    'message' => 'Tingkat absensi ' . round($absentRate * 100, 1) . '% dalam 6 bulan terakhir — jauh di atas normal.'
                ];
            } elseif ($absentRate > 0.10) {
                $riskScore += 8;
                $signals[] = [
                    'type' => 'attendance',
                    'severity' => 'medium',
                    'message' => 'Tingkat absensi ' . round($absentRate * 100, 1) . '% — perlu perhatian.'
                ];
            }
            if ($lateRate > 0.25) {
                $riskScore += 10;
                $signals[] = [
                    'type' => 'attendance',
                    'severity' => 'medium',
                    'message' => 'Sering terlambat (' . round($lateRate * 100, 1) . '% hari kerja) — indikasi motivasi menurun.'
                ];
            }

            // ── c) Salary stagnation ──────────────────────────────
            $salaryStagnationMonths = 0;
            if ($payrolls->count() >= 2) {
                $uniqueSalaries = $payrolls->pluck('base_salary')->unique()->values();
                if ($uniqueSalaries->count() === 1) {
                    // Salary never changed — check how long
                    $firstPayroll = $payrolls->first();
                    $salaryStagnationMonths = (int) \Carbon\Carbon::parse($firstPayroll->created_at)->diffInMonths($now);
                }
            } elseif ($emp->join_date) {
                $tenureMonths = (int) $emp->join_date->diffInMonths($now);
                if ($tenureMonths >= 18) {
                    $salaryStagnationMonths = $tenureMonths; // assume no raise if no payroll data
                }
            }

            if ($salaryStagnationMonths >= 24) {
                $riskScore += 18;
                $signals[] = [
                    'type' => 'compensation',
                    'severity' => 'high',
                    'message' => "Gaji tidak naik selama ≥{$salaryStagnationMonths} bulan — risiko resign karena kompensasi."
                ];
            } elseif ($salaryStagnationMonths >= 12) {
                $riskScore += 8;
                $signals[] = [
                    'type' => 'compensation',
                    'severity' => 'medium',
                    'message' => "Gaji tidak naik selama {$salaryStagnationMonths} bulan — pertimbangkan review kompensasi."
                ];
            }

            // ── d) Tenure risk ────────────────────────────────────
            $tenureMonths = $emp->join_date ? (int)$emp->join_date->diffInMonths($now) : 0;
            if ($tenureMonths < 6) {
                $riskScore += 12;
                $signals[] = [
                    'type' => 'tenure',
                    'severity' => 'medium',
                    'message' => "Karyawan baru ({$tenureMonths} bulan) — periode rentan early turnover."
                ];
            } elseif ($tenureMonths < 12) {
                $riskScore += 6;
                $signals[] = [
                    'type' => 'tenure',
                    'severity' => 'low',
                    'message' => "Tenure < 1 tahun ({$tenureMonths} bulan) — masih dalam fase adaptasi."
                ];
            } elseif ($tenureMonths > 60 && $latestScore !== null && $latestScore < 3.5) {
                $riskScore += 10;
                $signals[] = [
                    'type' => 'tenure',
                    'severity' => 'medium',
                    'message' => 'Karyawan lama (>' . round($tenureMonths / 12, 1) . ' tahun) dengan kinerja menurun — risiko burnout atau stagnasi karir.'
                ];
            }

            // ── e) Leave overuse (burnout signal) ─────────────────
            $totalLeaveDays = $leaves->sum('days');
            if ($totalLeaveDays > 20) {
                $riskScore += 10;
                $signals[] = [
                    'type' => 'burnout',
                    'severity' => 'medium',
                    'message' => "Mengambil {$totalLeaveDays} hari cuti dalam 12 bulan terakhir — potensi burnout."
                ];
            }

            // ── f) No attendance records (ghost / disengaged) ─────
            if ($totalAtt === 0 && $tenureMonths >= 3) {
                $riskScore += 5;
                $signals[] = [
                    'type' => 'engagement',
                    'severity' => 'low',
                    'message' => 'Tidak ada catatan absensi 6 bulan terakhir — data tidak lengkap.'
                ];
            }

            if (empty($signals)) continue; // no risk signals, skip

            $riskScore = min(100, $riskScore);
            $riskLevel = match (true) {
                $riskScore >= 60 => 'critical',
                $riskScore >= 40 => 'high',
                $riskScore >= 20 => 'medium',
                default          => 'low',
            };

            $results[] = [
                'employee_id'   => $emp->id,
                'name'          => $emp->name,
                'position'      => $emp->position ?? '-',
                'department'    => $emp->department ?? '-',
                'tenure_label'  => $tenureMonths >= 12
                    ? round($tenureMonths / 12, 1) . ' tahun'
                    : "{$tenureMonths} bulan",
                'risk_score'    => $riskScore,
                'risk_level'    => $riskLevel,
                'risk_color'    => match ($riskLevel) {
                    'critical' => 'red',
                    'high' => 'orange',
                    'medium' => 'amber',
                    default => 'blue',
                },
                'signals'       => $signals,
                'recommendations' => $this->turnoverRecommendations($riskLevel, $signals, $tenureMonths, $salaryStagnationMonths),
            ];
        }

        // Sort by risk_score desc
        usort($results, fn($a, $b) => $b['risk_score'] <=> $a['risk_score']);

        return $results;
    }

    private function turnoverRecommendations(
        string $riskLevel,
        array $signals,
        int $tenureMonths,
        int $salaryStagnationMonths
    ): array {
        $recs = [];
        $types = array_column($signals, 'type');

        if ($riskLevel === 'critical') {
            $recs[] = ['priority' => 'high', 'action' => 'Jadwalkan stay interview segera — tanyakan langsung apa yang membuat karyawan ini mempertimbangkan resign.'];
        }
        if (in_array('compensation', $types) || $salaryStagnationMonths >= 12) {
            $recs[] = ['priority' => 'high', 'action' => 'Review kompensasi — bandingkan dengan pasar dan pertimbangkan kenaikan gaji atau bonus retensi.'];
        }
        if (in_array('performance', $types)) {
            $recs[] = ['priority' => 'high', 'action' => 'Lakukan 1-on-1 untuk memahami hambatan kinerja — bisa jadi tanda ketidakcocokan peran atau kurangnya dukungan.'];
        }
        if (in_array('attendance', $types)) {
            $recs[] = ['priority' => 'medium', 'action' => 'Diskusikan fleksibilitas kerja atau identifikasi masalah personal yang mempengaruhi kehadiran.'];
        }
        if (in_array('burnout', $types)) {
            $recs[] = ['priority' => 'medium', 'action' => 'Evaluasi beban kerja — pertimbangkan redistribusi tugas atau program wellness karyawan.'];
        }
        if ($tenureMonths < 12) {
            $recs[] = ['priority' => 'medium', 'action' => 'Perkuat program onboarding & buddy system — karyawan baru butuh dukungan ekstra di tahun pertama.'];
        }
        if (in_array('engagement', $types)) {
            $recs[] = ['priority' => 'low', 'action' => 'Pastikan data absensi & penilaian kinerja diperbarui secara rutin untuk monitoring yang akurat.'];
        }

        $recs[] = ['priority' => 'low', 'action' => 'Dokumentasikan knowledge karyawan ini sebagai langkah mitigasi risiko jika terjadi turnover.'];

        return $recs;
    }
}
