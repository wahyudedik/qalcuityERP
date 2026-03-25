<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeSalaryComponent;
use App\Models\ErpNotification;
use App\Models\OvertimeRequest;
use App\Models\PayrollItem;
use App\Models\PayrollItemComponent;
use App\Models\PayrollRun;
use App\Models\User;
use App\Notifications\PayrollProcessedNotification;
use App\Services\PayrollGlService;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(private PayrollGlService $glService) {}

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

            // Overtime pay: sum approved lembur bulan ini yang belum masuk payroll
            $overtimePay = OvertimeRequest::where('tenant_id', $tid)
                ->where('employee_id', $emp->id)
                ->where('status', 'approved')
                ->where('included_in_payroll', false)
                ->whereYear('date', $year)->whereMonth('date', $month)
                ->sum('overtime_pay');

            $dailyRate    = $baseSalary / $workingDays;
            $deductAbsent = $dailyRate * $absentDays;
            $deductLate   = ($dailyRate / 8) * $lateDays;

            // Salary components (tunjangan & potongan fleksibel)
            $empComponents = EmployeeSalaryComponent::where('employee_id', $emp->id)
                ->where('is_active', true)
                ->where(fn($q) => $q->whereNull('effective_from')->orWhere('effective_from', '<=', now()))
                ->where(fn($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', now()))
                ->with('component')
                ->get();

            $totalAllowances  = 0;
            $totalCompDeduct  = 0;
            $componentSnapshots = [];

            foreach ($empComponents as $ec) {
                $comp = $ec->component;
                if (!$comp || !$comp->is_active) continue;

                $amount = $comp->calc_type === 'percent_base'
                    ? round($baseSalary * $ec->amount / 100)
                    : $ec->amount;

                $componentSnapshots[] = [
                    'salary_component_id' => $comp->id,
                    'name'                => $comp->name,
                    'type'                => $comp->type,
                    'amount'              => $amount,
                ];

                if ($comp->type === 'allowance') {
                    $totalAllowances += $amount;
                } else {
                    $totalCompDeduct += $amount;
                }
            }

            $grossSalary = $baseSalary + $totalAllowances + $overtimePay - $deductAbsent - $deductLate - $totalCompDeduct;

            $bpjs  = $includeBpjs ? round($grossSalary * 0.03) : 0;
            $pkp   = max(0, ($grossSalary * 12) - 54000000);
            $pph21 = round(($pkp <= 60000000 ? $pkp * 0.05 : 3000000 + ($pkp - 60000000) * 0.15) / 12);
            $net   = $grossSalary - $bpjs - $pph21;

            $payrollItem = PayrollItem::updateOrCreate(
                ['tenant_id' => $tid, 'payroll_run_id' => $run->id, 'employee_id' => $emp->id],
                [
                    'base_salary'      => $baseSalary,
                    'working_days'     => $workingDays,
                    'present_days'     => $presentDays,
                    'absent_days'      => $absentDays,
                    'late_days'        => $lateDays,
                    'allowances'       => $totalAllowances,
                    'overtime_pay'     => $overtimePay,
                    'deduction_absent' => $deductAbsent,
                    'deduction_late'   => $deductLate,
                    'deduction_other'  => $totalCompDeduct,
                    'gross_salary'     => $grossSalary,
                    'bpjs_employee'    => $bpjs,
                    'tax_pph21'        => $pph21,
                    'net_salary'       => $net,
                    'status'           => 'pending',
                ]
            );

            // Snapshot komponen ke payroll_item_components
            PayrollItemComponent::where('payroll_item_id', $payrollItem->id)->delete();
            foreach ($componentSnapshots as $snap) {
                PayrollItemComponent::create(array_merge($snap, ['payroll_item_id' => $payrollItem->id]));
            }

            // Mark overtime requests as included in this payroll
            OvertimeRequest::where('tenant_id', $tid)
                ->where('employee_id', $emp->id)
                ->where('status', 'approved')
                ->where('included_in_payroll', false)
                ->whereYear('date', $year)->whereMonth('date', $month)
                ->update(['included_in_payroll' => true, 'payroll_period' => $period]);

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

        // ── GL Reconciliation — buat jurnal akuntansi otomatis ────
        $glWarning = null;
        try {
            $this->glService->createJournal($run->fresh(), auth()->id());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('PayrollGL failed: ' . $e->getMessage());
            $glWarning = "⚠️ Jurnal GL tidak terbuat otomatis: {$e->getMessage()}. "
                . "Gunakan tombol \"Buat Jurnal GL\" di halaman detail payroll.";
        }

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

        return back()->with('success', "Penggajian periode {$period} berhasil diproses untuk {$employees->count()} karyawan.")
            ->with('warning', $glWarning);
    }

    public function markPaid(PayrollRun $run)
    {
        abort_unless($run->tenant_id === $this->tenantId(), 403);
        abort_unless($run->status === 'processed', 403, 'Hanya payroll berstatus processed yang bisa ditandai dibayar.');

        $run->update(['status' => 'paid', 'paid_at' => now(), 'paid_by' => auth()->id()]);
        PayrollItem::where('payroll_run_id', $run->id)->update(['status' => 'paid']);

        // GL: Dr Hutang Gaji / Cr Kas/Bank (pembayaran aktual ke karyawan)
        $glWarning = null;
        try {
            $this->glService->createPaymentJournal($run->fresh(), auth()->id());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('PayrollGL payment failed: ' . $e->getMessage());
            $glWarning = "⚠️ Jurnal pembayaran gaji tidak terbuat: {$e->getMessage()}. Buat jurnal manual di menu Jurnal.";
        }

        return back()
            ->with('success', "Penggajian periode {$run->period} ditandai sebagai dibayar.")
            ->with('warning', $glWarning);
    }

    /**
     * Buat jurnal pembayaran GL manual (Dr Hutang Gaji / Cr Bank).
     */
    public function createPaymentGlJournal(PayrollRun $run)
    {
        abort_unless($run->tenant_id === $this->tenantId(), 403);
        abort_unless($run->status === 'paid', 403, 'Payroll belum ditandai dibayar.');

        try {
            if ($run->payment_journal_entry_id) {
                $je = $run->paymentJournalEntry;
                if ($je && $je->status !== 'reversed') {
                    return back()->with('info', "Jurnal pembayaran sudah ada: {$je->number}.");
                }
                $run->update(['payment_journal_entry_id' => null]);
            }

            $journal = $this->glService->createPaymentJournal($run->fresh(), auth()->id());
            return back()->with('success', "Jurnal pembayaran {$journal->number} berhasil dibuat.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuat jurnal pembayaran: ' . $e->getMessage());
        }
    }
    public function createGlJournal(PayrollRun $run)
    {
        abort_unless($run->tenant_id === $this->tenantId(), 403);
        abort_unless(in_array($run->status, ['processed', 'paid']), 403, 'Payroll belum diproses.');

        try {
            if ($run->journal_entry_id) {
                $je = $run->journalEntry;
                if ($je && $je->status !== 'reversed') {
                    return back()->with('info', "Jurnal GL sudah ada: {$je->number}. Reverse jurnal terlebih dahulu untuk membuat ulang.");
                }
                // Journal was reversed — allow re-creation
                $run->update(['journal_entry_id' => null]);
            }

            $journal = $this->glService->createJournal($run->fresh(), auth()->id());
            return back()->with('success', "Jurnal GL {$journal->number} berhasil dibuat dan diposting.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuat jurnal GL: ' . $e->getMessage());
        }
    }
}
