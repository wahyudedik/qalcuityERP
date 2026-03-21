<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\ErpNotification;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\User;
use App\Notifications\PayrollProcessedNotification;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $tid    = $this->tenantId();
        $period = $request->period ?? now()->format('Y-m');

        $runs = PayrollRun::where('tenant_id', $tid)->orderByDesc('period')->get();
        $run  = PayrollRun::where('tenant_id', $tid)->where('period', $period)->first();

        $items = $run
            ? PayrollItem::where('payroll_run_id', $run->id)->with('employee')->get()
            : collect();

        $totalEmployees = Employee::where('tenant_id', $tid)->where('status', 'active')->count();

        return view('payroll.index', compact('runs', 'run', 'items', 'period', 'totalEmployees'));
    }

    public function process(Request $request)
    {
        $data = $request->validate([
            'period'       => 'required|date_format:Y-m',
            'working_days' => 'required|integer|min:1|max:31',
            'include_bpjs' => 'boolean',
        ]);

        $tid         = $this->tenantId();
        $period      = $data['period'];
        $workingDays = $data['working_days'];
        $includeBpjs = $request->boolean('include_bpjs', true);

        $existing = PayrollRun::where('tenant_id', $tid)->where('period', $period)->first();
        if ($existing && $existing->status !== 'draft') {
            return back()->withErrors(['period' => "Penggajian periode {$period} sudah diproses."]);
        }

        $run = $existing ?? PayrollRun::create([
            'tenant_id'    => $tid,
            'period'       => $period,
            'status'       => 'draft',
            'processed_by' => auth()->id(),
        ]);

        $employees = Employee::where('tenant_id', $tid)->where('status', 'active')->whereNotNull('salary')->get();

        [$year, $month] = explode('-', $period);
        $totalGross = $totalDeductions = $totalNet = 0;

        foreach ($employees as $emp) {
            $attendance = Attendance::where('tenant_id', $tid)
                ->where('employee_id', $emp->id)
                ->whereYear('date', $year)->whereMonth('date', $month)
                ->selectRaw('status, count(*) as cnt')->groupBy('status')
                ->pluck('cnt', 'status');

            $presentDays = ($attendance['present'] ?? 0) + ($attendance['late'] ?? 0);
            $absentDays  = $attendance['absent'] ?? 0;
            $lateDays    = $attendance['late'] ?? 0;
            $baseSalary  = (float) $emp->salary;

            $dailyRate    = $baseSalary / $workingDays;
            $deductAbsent = $dailyRate * $absentDays;
            $deductLate   = ($dailyRate / 8) * $lateDays;
            $grossSalary  = $baseSalary - $deductAbsent - $deductLate;

            $bpjs  = $includeBpjs ? round($grossSalary * 0.03) : 0;
            $pkp   = max(0, ($grossSalary * 12) - 54000000);
            $pph21 = round(($pkp <= 60000000 ? $pkp * 0.05 : 3000000 + ($pkp - 60000000) * 0.15) / 12);
            $net   = $grossSalary - $bpjs - $pph21;

            PayrollItem::updateOrCreate(
                ['tenant_id' => $tid, 'payroll_run_id' => $run->id, 'employee_id' => $emp->id],
                [
                    'base_salary'      => $baseSalary,
                    'working_days'     => $workingDays,
                    'present_days'     => $presentDays,
                    'absent_days'      => $absentDays,
                    'late_days'        => $lateDays,
                    'deduction_absent' => $deductAbsent,
                    'deduction_late'   => $deductLate,
                    'gross_salary'     => $grossSalary,
                    'bpjs_employee'    => $bpjs,
                    'tax_pph21'        => $pph21,
                    'net_salary'       => $net,
                    'status'           => 'pending',
                ]
            );

            $totalGross      += $grossSalary;
            $totalDeductions += $bpjs + $pph21 + $deductAbsent + $deductLate;
            $totalNet        += $net;
        }

        $run->update([
            'status'           => 'processed',
            'total_gross'      => $totalGross,
            'total_deductions' => $totalDeductions,
            'total_net'        => $totalNet,
            'processed_at'     => now(),
        ]);

        // Notifikasi email + in-app ke semua admin & manager
        $admins = User::where('tenant_id', $tid)->whereIn('role', ['admin', 'manager'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new PayrollProcessedNotification($run));

            ErpNotification::create([
                'tenant_id' => $tid,
                'user_id'   => $admin->id,
                'type'      => 'payroll_processed',
                'title'     => '💰 Penggajian Diproses',
                'body'      => "Penggajian periode {$period} untuk {$employees->count()} karyawan berhasil diproses. Total: Rp " . number_format($totalNet, 0, ',', '.'),
                'data'      => ['payroll_run_id' => $run->id, 'period' => $period],
            ]);
        }

        return back()->with('success', "Penggajian periode {$period} berhasil diproses untuk {$employees->count()} karyawan.");
    }

    public function markPaid(PayrollRun $run)
    {
        abort_unless($run->tenant_id === $this->tenantId(), 403);
        $run->update(['status' => 'paid']);
        PayrollItem::where('payroll_run_id', $run->id)->update(['status' => 'paid']);
        return back()->with('success', "Penggajian periode {$run->period} ditandai sebagai dibayar.");
    }
}
