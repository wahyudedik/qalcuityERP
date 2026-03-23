<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PerformanceReview;
use Illuminate\Http\Request;

class HrmController extends Controller
{
    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $tid   = $this->tenantId();
        $query = Employee::where('tenant_id', $tid);

        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('position', 'like', "%$s%")->orWhere('department', 'like', "%$s%"));
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->department) {
            $query->where('department', $request->department);
        }

        $employees   = $query->orderBy('name')->paginate(20)->withQueryString();
        $departments = Employee::where('tenant_id', $tid)->whereNotNull('department')->distinct()->pluck('department');
        $totalActive = Employee::where('tenant_id', $tid)->where('status', 'active')->count();

        return view('hrm.index', compact('employees', 'departments', 'totalActive'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'position'   => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'salary'     => 'nullable|numeric|min:0',
            'phone'      => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:255',
            'join_date'  => 'nullable|date',
            'address'    => 'nullable|string',
        ]);

        $tid   = $this->tenantId();
        $count = Employee::where('tenant_id', $tid)->count() + 1;

        $employee = Employee::create([
            'tenant_id'   => $tid,
            'employee_id' => 'EMP-' . now()->format('Ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT),
            'name'        => $data['name'],
            'position'    => $data['position'] ?? null,
            'department'  => $data['department'] ?? null,
            'salary'      => $data['salary'] ?? null,
            'phone'       => $data['phone'] ?? null,
            'email'       => $data['email'] ?? null,
            'join_date'   => $data['join_date'] ?? today(),
            'address'     => $data['address'] ?? null,
            'status'      => 'active',
        ]);

        ActivityLog::record('employee_created', "Karyawan baru: {$employee->name} ({$employee->employee_id})", $employee, [], $employee->toArray());

        return back()->with('success', "Karyawan {$data['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, Employee $employee)
    {
        abort_unless($employee->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'position'   => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'salary'     => 'nullable|numeric|min:0',
            'phone'      => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:255',
            'join_date'  => 'nullable|date',
            'address'    => 'nullable|string',
            'status'     => 'required|in:active,inactive,resigned',
        ]);

        $old = $employee->getOriginal();
        $employee->update($data);
        ActivityLog::record('employee_updated', "Data karyawan diperbarui: {$employee->name}", $employee, $old, $employee->fresh()->toArray());

        return back()->with('success', "Data {$employee->name} berhasil diperbarui.");
    }

    public function destroy(Employee $employee)
    {
        abort_unless($employee->tenant_id === $this->tenantId(), 403);
        $employee->update(['status' => 'resigned']);
        ActivityLog::record('employee_resigned', "Karyawan ditandai resign: {$employee->name} ({$employee->employee_id})", $employee);
        return back()->with('success', "{$employee->name} ditandai sebagai resign.");
    }

    public function attendance(Request $request)
    {
        $tid  = $this->tenantId();
        $date = $request->date ? \Carbon\Carbon::parse($request->date) : today();

        $employees = Employee::where('tenant_id', $tid)->where('status', 'active')->orderBy('name')->get();

        $attendances = Attendance::where('tenant_id', $tid)
            ->whereDate('date', $date)
            ->pluck('status', 'employee_id');

        $summary = Attendance::where('tenant_id', $tid)
            ->whereDate('date', $date)
            ->selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return view('hrm.attendance', compact('employees', 'attendances', 'date', 'summary'));
    }

    public function storeAttendance(Request $request)
    {
        $data = $request->validate([
            'date'    => 'required|date',
            'records' => 'required|array',
            'records.*.employee_id' => 'required|exists:employees,id',
            'records.*.status'      => 'required|in:present,absent,late,leave,sick',
            'records.*.notes'       => 'nullable|string',
        ]);

        $tid = $this->tenantId();

        foreach ($data['records'] as $rec) {
            Attendance::updateOrCreate(
                ['tenant_id' => $tid, 'employee_id' => $rec['employee_id'], 'date' => $data['date']],
                [
                    'status'   => $rec['status'],
                    'notes'    => $rec['notes'] ?? null,
                    'check_in' => $rec['status'] === 'present' ? now()->toTimeString() : null,
                ]
            );
        }

        return back()->with('success', 'Absensi berhasil disimpan.');
    }

    // ─── Leave Management ─────────────────────────────────────────

    public function leave(Request $request)
    {
        $tid = $this->tenantId();

        $query = LeaveRequest::where('tenant_id', $tid)
            ->with('employee')
            ->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        $leaves    = $query->paginate(20)->withQueryString();
        $employees = Employee::where('tenant_id', $tid)->where('status', 'active')->orderBy('name')->get();

        $stats = [
            'pending'  => LeaveRequest::where('tenant_id', $tid)->where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('tenant_id', $tid)->where('status', 'approved')
                            ->whereYear('start_date', now()->year)->count(),
            'rejected' => LeaveRequest::where('tenant_id', $tid)->where('status', 'rejected')
                            ->whereYear('start_date', now()->year)->count(),
        ];

        return view('hrm.leave', compact('leaves', 'employees', 'stats'));
    }

    public function storeLeave(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type'        => 'required|in:annual,sick,maternity,paternity,unpaid,other',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'nullable|string|max:500',
        ]);

        $tid = $this->tenantId();
        $emp = Employee::where('tenant_id', $tid)->findOrFail($data['employee_id']);

        $start = \Carbon\Carbon::parse($data['start_date']);
        $end   = \Carbon\Carbon::parse($data['end_date']);
        $days  = $start->diffInWeekdays($end) + 1;

        $leave = LeaveRequest::create([
            'tenant_id'   => $tid,
            'employee_id' => $emp->id,
            'type'        => $data['type'],
            'start_date'  => $data['start_date'],
            'end_date'    => $data['end_date'],
            'days'        => $days,
            'reason'      => $data['reason'] ?? null,
            'status'      => 'pending',
        ]);

        ActivityLog::record('leave_requested', "Pengajuan cuti: {$emp->name} ({$days} hari)", $leave);

        return back()->with('success', "Pengajuan cuti {$emp->name} berhasil disimpan.");
    }

    public function approveLeave(Request $request, LeaveRequest $leave)
    {
        $tid = $this->tenantId();
        abort_unless($leave->tenant_id === $tid, 403);

        $data = $request->validate([
            'action'           => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:action,rejected|nullable|string|max:300',
            'approved_by'      => 'nullable|exists:employees,id',
        ]);

        $leave->update([
            'status'           => $data['action'],
            'approved_by'      => $data['approved_by'] ?? null,
            'rejection_reason' => $data['rejection_reason'] ?? null,
            'approved_at'      => now(),
        ]);

        $label = $data['action'] === 'approved' ? 'disetujui' : 'ditolak';
        ActivityLog::record('leave_' . $data['action'], "Cuti {$leave->employee->name} {$label}", $leave);

        return back()->with('success', "Cuti berhasil {$label}.");
    }

    public function destroyLeave(LeaveRequest $leave)
    {
        abort_unless($leave->tenant_id === $this->tenantId(), 403);
        abort_if($leave->status !== 'pending', 403, 'Hanya pengajuan pending yang bisa dihapus.');
        $leave->delete();
        return back()->with('success', 'Pengajuan cuti dihapus.');
    }

    // ─── Performance Review ───────────────────────────────────────

    public function performance(Request $request)
    {
        $tid = $this->tenantId();

        $query = PerformanceReview::where('tenant_id', $tid)
            ->with(['employee', 'reviewer'])
            ->latest();

        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->period_type) {
            $query->where('period_type', $request->period_type);
        }

        $reviews   = $query->paginate(20)->withQueryString();
        $employees = Employee::where('tenant_id', $tid)->where('status', 'active')->orderBy('name')->get();

        return view('hrm.performance', compact('reviews', 'employees'));
    }

    public function storePerformance(Request $request)
    {
        $data = $request->validate([
            'employee_id'          => 'required|exists:employees,id',
            'reviewer_id'          => 'required|exists:employees,id',
            'period'               => 'required|string|max:20',
            'period_type'          => 'required|in:monthly,quarterly,annual',
            'score_work_quality'   => 'required|integer|min:1|max:5',
            'score_productivity'   => 'required|integer|min:1|max:5',
            'score_teamwork'       => 'required|integer|min:1|max:5',
            'score_initiative'     => 'required|integer|min:1|max:5',
            'score_attendance'     => 'required|integer|min:1|max:5',
            'strengths'            => 'nullable|string|max:1000',
            'improvements'         => 'nullable|string|max:1000',
            'goals_next_period'    => 'nullable|string|max:1000',
            'recommendation'       => 'nullable|in:promote,retain,pip,terminate',
        ]);

        $tid = $this->tenantId();
        abort_unless(
            Employee::where('tenant_id', $tid)->where('id', $data['employee_id'])->exists(),
            403
        );

        $overall = round((
            $data['score_work_quality'] + $data['score_productivity'] +
            $data['score_teamwork'] + $data['score_initiative'] + $data['score_attendance']
        ) / 5, 2);

        $review = PerformanceReview::updateOrCreate(
            [
                'employee_id' => $data['employee_id'],
                'period'      => $data['period'],
                'period_type' => $data['period_type'],
            ],
            array_merge($data, [
                'tenant_id'     => $tid,
                'overall_score' => $overall,
                'status'        => 'submitted',
                'submitted_at'  => now(),
            ])
        );

        ActivityLog::record('performance_review_created', "Review kinerja: {$review->employee->name} — {$data['period']}", $review);

        return back()->with('success', 'Penilaian kinerja berhasil disimpan.');
    }

    public function acknowledgePerformance(PerformanceReview $review)
    {
        abort_unless($review->tenant_id === $this->tenantId(), 403);
        $review->update(['status' => 'acknowledged']);
        return back()->with('success', 'Penilaian telah dikonfirmasi.');
    }

    public function destroyPerformance(PerformanceReview $review)
    {
        abort_unless($review->tenant_id === $this->tenantId(), 403);
        $review->delete();
        return back()->with('success', 'Penilaian dihapus.');
    }

    // ─── Org Chart ────────────────────────────────────────────────

    public function orgChart()
    {
        $tid = $this->tenantId();

        // Load all active employees with their manager
        $employees = Employee::where('tenant_id', $tid)
            ->where('status', 'active')
            ->with('manager')
            ->orderBy('name')
            ->get();

        // Build tree structure for JSON (used by JS renderer)
        $nodes = $employees->map(fn($e) => [
            'id'         => $e->id,
            'name'       => $e->name,
            'position'   => $e->position ?? '',
            'department' => $e->department ?? '',
            'manager_id' => $e->manager_id,
        ])->values();

        return view('hrm.orgchart', compact('employees', 'nodes'));
    }

    public function updateManager(Request $request, Employee $employee)
    {
        abort_unless($employee->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'manager_id' => 'nullable|exists:employees,id',
        ]);

        // Prevent circular reference
        if ($data['manager_id'] && $data['manager_id'] == $employee->id) {
            return back()->withErrors(['manager_id' => 'Karyawan tidak bisa menjadi atasan dirinya sendiri.']);
        }

        $employee->update(['manager_id' => $data['manager_id'] ?: null]);

        return back()->with('success', 'Struktur organisasi diperbarui.');
    }
}
