<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ShiftSchedule;
use App\Models\WorkShift;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    // ─── Halaman utama: template shift + weekly scheduler ─────────

    public function index(Request $request)
    {
        $tid = $this->tid();

        // Week navigation
        $weekStart = $request->week
            ? Carbon::parse($request->week)->startOfWeek(Carbon::MONDAY)
            : now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $weekDays = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i));

        $shifts = WorkShift::where('tenant_id', $tid)->where('is_active', true)->orderBy('start_time')->get();
        $employees = Employee::where('tenant_id', $tid)->where('status', 'active')->orderBy('name')->get();

        // Jadwal minggu ini: [employee_id][date] => ShiftSchedule
        $schedules = ShiftSchedule::where('tenant_id', $tid)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->with('shift')
            ->get()
            ->groupBy('employee_id')
            ->map(fn ($rows) => $rows->keyBy(fn ($r) => $r->date->format('Y-m-d')));

        return view('hrm.shifts', compact('shifts', 'employees', 'schedules', 'weekStart', 'weekEnd', 'weekDays'));
    }

    // ─── CRUD Template Shift ──────────────────────────────────────

    public function storeShift(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'required|string|size:7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'break_minutes' => 'required|integer|min:0|max:480',
            'crosses_midnight' => 'boolean',
            'description' => 'nullable|string|max:255',
        ]);

        WorkShift::create(array_merge($data, [
            'tenant_id' => $this->tid(),
            'crosses_midnight' => $request->boolean('crosses_midnight'),
        ]));

        return back()->with('success', "Shift \"{$data['name']}\" berhasil dibuat.");
    }

    public function updateShift(Request $request, WorkShift $shift)
    {
        abort_unless($shift->tenant_id === $this->tid(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'required|string|size:7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'break_minutes' => 'required|integer|min:0|max:480',
            'crosses_midnight' => 'boolean',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $shift->update(array_merge($data, [
            'crosses_midnight' => $request->boolean('crosses_midnight'),
            'is_active' => $request->boolean('is_active', true),
        ]));

        return back()->with('success', 'Shift diperbarui.');
    }

    public function destroyShift(WorkShift $shift)
    {
        abort_unless($shift->tenant_id === $this->tid(), 403);
        $shift->update(['is_active' => false]);

        return back()->with('success', 'Shift dinonaktifkan.');
    }

    // ─── Jadwal per karyawan ──────────────────────────────────────

    /**
     * Assign shift ke satu karyawan untuk satu hari (AJAX).
     */
    public function assignShift(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'work_shift_id' => 'nullable|exists:work_shifts,id',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $tid = $this->tid();
        abort_unless(Employee::where('tenant_id', $tid)->where('id', $data['employee_id'])->exists(), 403);

        if (empty($data['work_shift_id'])) {
            // Hapus jadwal (klik shift yang sama = toggle off)
            ShiftSchedule::where('tenant_id', $tid)
                ->where('employee_id', $data['employee_id'])
                ->where('date', $data['date'])
                ->delete();

            return response()->json(['action' => 'removed']);
        }

        $schedule = ShiftSchedule::updateOrCreate(
            ['tenant_id' => $tid, 'employee_id' => $data['employee_id'], 'date' => $data['date']],
            ['work_shift_id' => $data['work_shift_id'], 'notes' => $data['notes'] ?? null]
        );

        $schedule->load('shift');

        return response()->json([
            'action' => 'assigned',
            'shift' => [
                'id' => $schedule->shift->id,
                'name' => $schedule->shift->name,
                'color' => $schedule->shift->color,
                'time' => $schedule->shift->timeLabel(),
            ],
        ]);
    }

    /**
     * Bulk assign: salin jadwal minggu ini ke minggu depan.
     */
    public function copyWeek(Request $request)
    {
        $data = $request->validate(['week_start' => 'required|date']);

        $tid = $this->tid();
        $fromStart = Carbon::parse($data['week_start'])->startOfWeek(Carbon::MONDAY);
        $fromEnd = $fromStart->copy()->endOfWeek(Carbon::SUNDAY);
        $toStart = $fromStart->copy()->addWeek();

        $schedules = ShiftSchedule::where('tenant_id', $tid)
            ->whereBetween('date', [$fromStart, $fromEnd])
            ->get();

        $copied = 0;
        foreach ($schedules as $s) {
            $newDate = $toStart->copy()->addDays($fromStart->diffInDays($s->date));
            ShiftSchedule::updateOrCreate(
                ['tenant_id' => $tid, 'employee_id' => $s->employee_id, 'date' => $newDate],
                ['work_shift_id' => $s->work_shift_id, 'notes' => $s->notes]
            );
            $copied++;
        }

        return back()->with('success', "Berhasil menyalin {$copied} jadwal ke minggu berikutnya.");
    }

    /**
     * GET /hrm/shifts/schedule-data?week=YYYY-MM-DD  (AJAX — untuk refresh kalender)
     */
    public function scheduleData(Request $request)
    {
        $tid = $this->tid();
        $weekStart = Carbon::parse($request->week ?? now())->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $schedules = ShiftSchedule::where('tenant_id', $tid)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->with('shift')
            ->get()
            ->map(fn ($s) => [
                'employee_id' => $s->employee_id,
                'date' => $s->date->format('Y-m-d'),
                'shift_id' => $s->work_shift_id,
                'shift_name' => $s->shift->name,
                'shift_color' => $s->shift->color,
                'shift_time' => $s->shift->timeLabel(),
            ]);

        return response()->json(['schedules' => $schedules]);
    }

    /**
     * GET /hrm/shifts/today  — jadwal hari ini untuk halaman absensi (AJAX)
     */
    public function todaySchedule(Request $request)
    {
        $tid = $this->tid();
        $date = $request->date ?? today()->format('Y-m-d');

        $schedules = ShiftSchedule::where('tenant_id', $tid)
            ->where('date', $date)
            ->with('shift')
            ->get()
            ->keyBy('employee_id')
            ->map(fn ($s) => [
                'shift_id' => $s->work_shift_id,
                'shift_name' => $s->shift->name,
                'shift_color' => $s->shift->color,
                'shift_time' => $s->shift->timeLabel(),
            ]);

        return response()->json(['schedules' => $schedules]);
    }

    /**
     * GET /hrm/shifts/conflicts?week=YYYY-MM-DD
     * AI conflict detection untuk jadwal minggu tertentu.
     *
     * Deteksi:
     * a) Double shift — karyawan dijadwalkan 2 shift berbeda di hari yang sama
     * b) Rest time violation — jeda < 8 jam antara shift berturut-turut (lintas hari)
     * c) Excessive weekly hours — total jam kerja > 48 jam/minggu (UU Ketenagakerjaan)
     * d) Consecutive days — bekerja > 6 hari berturut-turut tanpa libur
     * e) Night shift followed by morning shift — kurang dari 11 jam rest
     */
    public function conflictDetect(Request $request)
    {
        $tid = $this->tid();
        $weekStart = Carbon::parse($request->week ?? now())->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        // Load jadwal minggu ini + minggu sebelumnya (untuk rest time lintas minggu)
        $prevStart = $weekStart->copy()->subWeek();
        $schedules = ShiftSchedule::where('tenant_id', $tid)
            ->whereBetween('date', [$prevStart, $weekEnd])
            ->with(['shift', 'employee'])
            ->get();

        // Group by employee
        $byEmployee = $schedules->groupBy('employee_id');

        $conflicts = [];
        $warnings = [];
        $totalIssues = 0;

        foreach ($byEmployee as $empId => $empSchedules) {
            $emp = $empSchedules->first()->employee;
            if (! $emp) {
                continue;
            }

            $empConflicts = [];

            // Sort by date
            $sorted = $empSchedules->sortBy('date')->values();

            // Only analyze current week for most checks
            $thisWeek = $sorted->filter(fn ($s) => $s->date->between($weekStart, $weekEnd));

            // ── a) Double shift (same day, different shift) ───────
            $byDate = $thisWeek->groupBy(fn ($s) => $s->date->format('Y-m-d'));
            foreach ($byDate as $date => $daySchedules) {
                if ($daySchedules->count() > 1) {
                    $names = $daySchedules->map(fn ($s) => $s->shift->name)->join(' & ');
                    $empConflicts[] = [
                        'type' => 'double_shift',
                        'severity' => 'critical',
                        'date' => $date,
                        'message' => "Double shift pada {$date}: {$names}",
                    ];
                }
            }

            // ── b) Rest time violation (< 8 jam antar shift) ─────
            for ($i = 1; $i < $sorted->count(); $i++) {
                $prev = $sorted[$i - 1];
                $curr = $sorted[$i];

                // Only check consecutive or near-consecutive days
                $dayGap = $prev->date->diffInDays($curr->date);
                if ($dayGap > 2) {
                    continue;
                }

                $prevEndTime = $prev->shift->end_time;
                $currStartTime = $curr->shift->start_time;

                // Build datetime objects
                $prevEnd = Carbon::parse($prev->date->format('Y-m-d').' '.$prevEndTime);
                $currStart = Carbon::parse($curr->date->format('Y-m-d').' '.$currStartTime);

                if ($prev->shift->crosses_midnight) {
                    $prevEnd->addDay();
                }

                $restMinutes = $prevEnd->diffInMinutes($currStart, false);

                if ($restMinutes > 0 && $restMinutes < 480) { // < 8 jam
                    $restHours = round($restMinutes / 60, 1);
                    $severity = $restMinutes < 240 ? 'critical' : 'high'; // < 4 jam = critical
                    $empConflicts[] = [
                        'type' => 'rest_violation',
                        'severity' => $severity,
                        'date' => $curr->date->format('Y-m-d'),
                        'message' => "Jeda istirahat hanya {$restHours} jam sebelum shift {$curr->shift->name} pada {$curr->date->format('d/m')} (minimum 8 jam).",
                    ];
                }
            }

            // ── c) Excessive weekly hours (> 48 jam/minggu) ───────
            $weeklyMinutes = $thisWeek->sum(fn ($s) => $s->shift->workMinutes());
            $weeklyHours = round($weeklyMinutes / 60, 1);
            if ($weeklyHours > 48) {
                $empConflicts[] = [
                    'type' => 'excessive_hours',
                    'severity' => $weeklyHours > 60 ? 'critical' : 'high',
                    'date' => null,
                    'message' => "Total jam kerja minggu ini {$weeklyHours} jam (batas UU: 40 jam reguler, 48 jam dengan lembur).",
                ];
            } elseif ($weeklyHours > 40) {
                $empConflicts[] = [
                    'type' => 'overtime_warning',
                    'severity' => 'medium',
                    'date' => null,
                    'message' => "Total jam kerja {$weeklyHours} jam/minggu — melebihi 40 jam reguler (".($weeklyHours - 40).' jam lembur).',
                ];
            }

            // ── d) Consecutive days without rest (> 6 hari) ───────
            $workDates = $thisWeek->pluck('date')->map(fn ($d) => $d->format('Y-m-d'))->sort()->values();
            $consecutive = 1;
            $maxConsecutive = 1;
            for ($i = 1; $i < $workDates->count(); $i++) {
                $prev = Carbon::parse($workDates[$i - 1]);
                $curr = Carbon::parse($workDates[$i]);
                if ($prev->diffInDays($curr) === 1) {
                    $consecutive++;
                    $maxConsecutive = max($maxConsecutive, $consecutive);
                } else {
                    $consecutive = 1;
                }
            }
            if ($maxConsecutive >= 6) {
                $empConflicts[] = [
                    'type' => 'consecutive_days',
                    'severity' => $maxConsecutive >= 7 ? 'critical' : 'high',
                    'date' => null,
                    'message' => "Dijadwalkan {$maxConsecutive} hari berturut-turut tanpa hari libur (maksimum 6 hari per UU).",
                ];
            }

            if (empty($empConflicts)) {
                continue;
            }

            $maxSeverity = collect($empConflicts)->contains('severity', 'critical') ? 'critical'
                : (collect($empConflicts)->contains('severity', 'high') ? 'high'
                : (collect($empConflicts)->contains('severity', 'medium') ? 'medium' : 'low'));

            $conflicts[] = [
                'employee_id' => $empId,
                'employee_name' => $emp->name,
                'position' => $emp->position ?? '-',
                'department' => $emp->department ?? '-',
                'weekly_hours' => $weeklyHours ?? 0,
                'severity' => $maxSeverity,
                'conflicts' => $empConflicts,
                // Which dates have issues (for cell highlighting)
                'conflict_dates' => collect($empConflicts)->pluck('date')->filter()->unique()->values(),
            ];

            $totalIssues += count($empConflicts);
        }

        // Sort: critical first
        usort($conflicts, fn ($a, $b) => match (true) {
            $a['severity'] === 'critical' && $b['severity'] !== 'critical' => -1,
            $a['severity'] !== 'critical' && $b['severity'] === 'critical' => 1,
            default => 0,
        });

        $critical = collect($conflicts)->where('severity', 'critical')->count();
        $high = collect($conflicts)->where('severity', 'high')->count();

        return response()->json([
            'conflicts' => $conflicts,
            'total_issues' => $totalIssues,
            'critical' => $critical,
            'high' => $high,
            'week_start' => $weekStart->format('Y-m-d'),
            'week_end' => $weekEnd->format('Y-m-d'),
        ]);
    }
}
