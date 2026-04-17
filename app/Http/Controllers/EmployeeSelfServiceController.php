<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PayrollItem;
use App\Models\PerformanceReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Self-service portal untuk karyawan:
 *  - Dashboard ringkasan
 *  - Pengajuan & tracking cuti
 *  - Clock in / clock out absensi harian
 *  - Lihat slip gaji
 *  - Update data pribadi
 */
class EmployeeSelfServiceController extends Controller
{
    private function myEmployee(): ?Employee
    {
        $user = auth()->user();
        if (!$user) return null;
        return Employee::where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->first();
    }

    // ── Dashboard ─────────────────────────────────────────────────

    public function dashboard()
    {
        $user = auth()->user();
        $employee = $this->myEmployee();

        if (!$employee) {
            return view('self-service.dashboard', ['employee' => null]);
        }

        // Cuti
        $leaveQuota = 12;
        $leaveUsed = LeaveRequest::where('employee_id', $employee->id)
            ->where('type', 'annual')->where('status', 'approved')
            ->whereYear('start_date', now()->year)->sum('days');
        $leavePending = LeaveRequest::where('employee_id', $employee->id)->where('status', 'pending')->count();

        // Absensi bulan ini
        $monthStats = Attendance::where('employee_id', $employee->id)
            ->whereYear('date', now()->year)->whereMonth('date', now()->month)
            ->selectRaw('status, count(*) as cnt')->groupBy('status')
            ->pluck('cnt', 'status');

        // Absensi hari ini
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())->first();

        // Slip gaji terbaru
        $latestPayslip = PayrollItem::where('employee_id', $employee->id)
            ->with('payrollRun')->orderByDesc('created_at')->first();

        // Lembur pending
        $overtimePending = OvertimeRequest::where('employee_id', $employee->id)
            ->where('status', 'pending')->count();

        // Review kinerja terbaru
        $latestReview = PerformanceReview::where('employee_id', $employee->id)
            ->orderByDesc('submitted_at')->first();

        return view('self-service.dashboard', compact(
            'employee',
            'leaveQuota',
            'leaveUsed',
            'leavePending',
            'monthStats',
            'todayAttendance',
            'latestPayslip',
            'overtimePending',
            'latestReview'
        ));
    }

    // ── Profil Pribadi ────────────────────────────────────────────

    public function profile()
    {
        $employee = $this->myEmployee();
        $user = auth()->user();
        return view('self-service.profile', compact('employee', 'user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $employee = $this->myEmployee();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Update User
        $userUpdate = ['name' => $data['name'], 'phone' => $data['phone'] ?? $user->phone];
        if (!empty($data['password'])) {
            $userUpdate['password'] = Hash::make($data['password']);
        }
        if ($request->hasFile('avatar')) {
            if ($user->avatar)
                Storage::disk('public')->delete($user->avatar);
            $userUpdate['avatar'] = $request->file('avatar')->store("avatars/{$user->tenant_id}", 'public');
        }
        $user->update($userUpdate);

        // Update Employee record jika ada
        if ($employee) {
            $employee->update([
                'phone' => $data['phone'] ?? $employee->phone,
                'address' => $data['address'] ?? $employee->address,
            ]);
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    // ── Cuti Self-Service ─────────────────────────────────────────

    public function leaveIndex()
    {
        $employee = $this->myEmployee();

        if (!$employee) {
            return view('self-service.leave', ['employee' => null, 'leaves' => collect(), 'quota' => 0, 'usedDays' => 0]);
        }

        $leaves = LeaveRequest::where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        // BUG-HRM-003 FIX: Use accurate leave balance calculation
        $leaveService = new \App\Services\LeaveBalanceService();
        $leaveBalance = $leaveService->calculateLeaveBalance($employee);

        $quota = $leaveBalance['total_available'];
        $usedDays = $leaveBalance['used'];

        return view('self-service.leave', compact('employee', 'leaves', 'quota', 'usedDays', 'leaveBalance'));
    }

    public function leaveStore(Request $request)
    {
        $employee = $this->myEmployee();
        abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');

        $data = $request->validate([
            'type' => 'required|in:annual,sick,maternity,paternity,unpaid,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ]);

        $start = \Carbon\Carbon::parse($data['start_date']);
        $end = \Carbon\Carbon::parse($data['end_date']);
        $days = $start->diffInWeekdays($end) + 1;

        // BUG-HRM-003 FIX: Check leave balance with pro-rata calculation
        if ($data['type'] === 'annual') {
            $leaveService = new \App\Services\LeaveBalanceService();
            $balanceCheck = $leaveService->checkLeaveBalance($employee, $days);

            if (!$balanceCheck['has_enough']) {
                return back()->withErrors([
                    'quota' => "Sisa kuota cuti tahunan tidak mencukupi. " .
                        "Tersedia: {$balanceCheck['available']} hari, " .
                        "Dibutuhkan: {$days} hari. " .
                        "Kurang: {$balanceCheck['shortage']} hari."
                ]);
            }
        }

        LeaveRequest::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'type' => $data['type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'days' => $days,
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', "Pengajuan cuti {$days} hari berhasil dikirim. Menunggu persetujuan.");
    }

    public function leaveCancel(LeaveRequest $leave)
    {
        $employee = $this->myEmployee();
        abort_unless($employee && $leave->employee_id === $employee->id, 403);
        abort_if($leave->status !== 'pending', 403, 'Hanya pengajuan pending yang bisa dibatalkan.');

        $leave->delete();
        return back()->with('success', 'Pengajuan cuti dibatalkan.');
    }

    // ── Absensi Self-Service (Clock In/Out) ───────────────────────

    public function attendanceIndex()
    {
        $employee = $this->myEmployee();

        if (!$employee) {
            return view('self-service.attendance', ['employee' => null, 'today' => null, 'history' => collect()]);
        }

        $today = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();

        $history = Attendance::where('employee_id', $employee->id)
            ->orderByDesc('date')
            ->limit(30)
            ->get();

        // Stats bulan ini
        $monthStats = Attendance::where('employee_id', $employee->id)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return view('self-service.attendance', compact('employee', 'today', 'history', 'monthStats'));
    }

    public function clockIn(Request $request)
    {
        $employee = $this->myEmployee();
        abort_unless($employee, 403);

        $existing = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();

        if ($existing && $existing->check_in) {
            return back()->withErrors(['clock' => 'Anda sudah clock in hari ini.']);
        }

        // BUG-HRM-002 FIX: Use timezone-aware time and employee's shift
        $attendanceService = new \App\Services\AttendanceService();
        $result = $attendanceService->clockIn($employee);

        if ($result['error']) {
            return back()->withErrors(['clock' => $result['message']]);
        }

        return back()->with('success', $result['message']);
    }

    public function clockOut(Request $request)
    {
        $employee = $this->myEmployee();
        abort_unless($employee, 403);

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();

        if (!$attendance || !$attendance->check_in) {
            return back()->withErrors(['clock' => 'Anda belum clock in hari ini.']);
        }

        if ($attendance->check_out) {
            return back()->withErrors(['clock' => 'Anda sudah clock out hari ini.']);
        }

        $now = now();
        $attendance->update(['check_out' => $now->format('H:i:s')]);

        return back()->with('success', 'Clock out berhasil pukul ' . $now->format('H:i') . '.');
    }
}
