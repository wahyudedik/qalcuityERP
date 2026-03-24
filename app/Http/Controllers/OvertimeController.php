<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ErpNotification;
use App\Models\OvertimeRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $tid    = $this->tid();
        $status = $request->status ?? 'all';
        $month  = $request->month  ?? now()->format('Y-m');

        $query = OvertimeRequest::where('tenant_id', $tid)
            ->with('employee', 'approver')
            ->orderByDesc('date');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($month) {
            [$y, $m] = explode('-', $month);
            $query->whereYear('date', $y)->whereMonth('date', $m);
        }

        $requests  = $query->paginate(25)->withQueryString();
        $employees = Employee::where('tenant_id', $tid)->where('status', 'active')->orderBy('name')->get();

        $summary = [
            'pending'  => OvertimeRequest::where('tenant_id', $tid)->where('status', 'pending')->count(),
            'approved' => OvertimeRequest::where('tenant_id', $tid)->where('status', 'approved')
                ->whereYear('date', now()->year)->whereMonth('date', now()->month)->count(),
            'total_pay'=> OvertimeRequest::where('tenant_id', $tid)->where('status', 'approved')
                ->where('included_in_payroll', false)->sum('overtime_pay'),
        ];

        return view('hrm.overtime', compact('requests', 'employees', 'status', 'month', 'summary'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i',
            'reason'      => 'nullable|string|max:500',
        ]);

        $tid = $this->tid();
        abort_unless(Employee::where('tenant_id', $tid)->where('id', $data['employee_id'])->exists(), 403);

        // Hitung durasi
        $start    = Carbon::createFromFormat('H:i', $data['start_time']);
        $end      = Carbon::createFromFormat('H:i', $data['end_time']);
        $minutes  = $end->greaterThan($start)
            ? $start->diffInMinutes($end)
            : $start->diffInMinutes($end->addDay()); // crosses midnight

        if ($minutes < 30) {
            return back()->withErrors(['end_time' => 'Durasi lembur minimal 30 menit.']);
        }

        OvertimeRequest::create(array_merge($data, [
            'tenant_id'        => $tid,
            'duration_minutes' => $minutes,
            'status'           => 'pending',
        ]));

        // Notifikasi ke admin
        $admins = User::where('tenant_id', $tid)->where('role', 'admin')->get();
        foreach ($admins as $admin) {
            ErpNotification::create([
                'tenant_id' => $tid,
                'user_id'   => $admin->id,
                'type'      => 'overtime_request',
                'title'     => '⏰ Pengajuan Lembur Baru',
                'body'      => "Ada pengajuan lembur dari karyawan yang menunggu persetujuan.",
                'data'      => ['date' => $data['date']],
            ]);
        }

        return back()->with('success', 'Pengajuan lembur berhasil diajukan.');
    }

    public function approve(OvertimeRequest $overtime)
    {
        abort_unless($overtime->tenant_id === $this->tid(), 403);
        abort_unless($overtime->status === 'pending', 422);

        $overtime->load('employee');
        $pay = $overtime->calculatePay();

        $overtime->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'overtime_pay'=> $pay,
        ]);

        // Notifikasi ke karyawan (jika punya user)
        if ($overtime->employee->user_id) {
            ErpNotification::create([
                'tenant_id' => $this->tid(),
                'user_id'   => $overtime->employee->user_id,
                'type'      => 'overtime_approved',
                'title'     => '✅ Lembur Disetujui',
                'body'      => "Lembur Anda pada {$overtime->date->format('d M Y')} ({$overtime->durationLabel()}) telah disetujui. Upah: Rp " . number_format($pay, 0, ',', '.'),
                'data'      => ['overtime_id' => $overtime->id],
            ]);
        }

        return back()->with('success', 'Lembur disetujui. Upah Rp ' . number_format($pay, 0, ',', '.') . ' akan masuk payroll.');
    }

    public function reject(Request $request, OvertimeRequest $overtime)
    {
        abort_unless($overtime->tenant_id === $this->tid(), 403);
        abort_unless($overtime->status === 'pending', 422);

        $data = $request->validate(['rejection_reason' => 'nullable|string|max:255']);

        $overtime->update([
            'status'           => 'rejected',
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
            'rejection_reason' => $data['rejection_reason'] ?? null,
        ]);

        if ($overtime->employee->user_id) {
            ErpNotification::create([
                'tenant_id' => $this->tid(),
                'user_id'   => $overtime->employee->user_id,
                'type'      => 'overtime_rejected',
                'title'     => '❌ Lembur Ditolak',
                'body'      => "Lembur Anda pada {$overtime->date->format('d M Y')} ditolak." . ($data['rejection_reason'] ? " Alasan: {$data['rejection_reason']}" : ''),
                'data'      => ['overtime_id' => $overtime->id],
            ]);
        }

        return back()->with('success', 'Pengajuan lembur ditolak.');
    }

    public function destroy(OvertimeRequest $overtime)
    {
        abort_unless($overtime->tenant_id === $this->tid(), 403);
        abort_unless($overtime->status === 'pending', 422);
        $overtime->delete();
        return back()->with('success', 'Pengajuan lembur dihapus.');
    }
}
