<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Models\PayrollItem;
use Barryvdh\DomPDF\Facade\Pdf;

class PayslipController extends Controller
{
    /** Resolve the employee record for the logged-in user. */
    private function myEmployee(): ?Employee
    {
        $user = auth()->user();

        return Employee::where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->first();
    }

    /** List semua slip gaji milik karyawan yang login. */
    public function index()
    {
        $employee = $this->myEmployee();

        if (! $employee) {
            return view('payroll.slip-index', ['items' => collect(), 'employee' => null]);
        }

        $items = PayrollItem::where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->with('payrollRun')
            ->orderByDesc('created_at')
            ->get();

        return view('payroll.slip-index', compact('items', 'employee'));
    }

    /** Detail slip gaji satu periode. */
    public function show(PayrollItem $item)
    {
        $employee = $this->myEmployee();
        $user = auth()->user();

        // Admin/manager boleh lihat slip siapapun di tenant mereka
        if ($user->isAdmin() || $user->isManager()) {
            abort_unless($item->tenant_id === $user->tenant_id, 403);
        } else {
            // Karyawan biasa hanya boleh lihat slip milik sendiri
            abort_unless($employee && $item->employee_id === $employee->id, 403);
            abort_unless($item->tenant_id === $user->tenant_id, 403);
        }

        $item->load('employee', 'payrollRun', 'components');

        // Ambil detail lembur yang masuk ke periode ini
        $overtimes = OvertimeRequest::where('tenant_id', $item->tenant_id)
            ->where('employee_id', $item->employee_id)
            ->where('status', 'approved')
            ->where('payroll_period', $item->payrollRun?->period)
            ->get();

        // Ambil data perusahaan untuk kop slip
        $companyName = config('app.name', 'Perusahaan');
        try {
            $profile = CompanyProfile::where('tenant_id', $item->tenant_id)->first();
        } catch (\Throwable) {
            $profile = null;
        }

        return view('payroll.slip-show', compact('item', 'overtimes', 'companyName', 'profile'));
    }

    /**
     * Download slip gaji sebagai PDF menggunakan barryvdh/laravel-dompdf.
     * Template: resources/views/pdf/payslip.blade.php
     */
    public function downloadPdf(PayrollItem $item)
    {
        $employee = $this->myEmployee();
        $user = auth()->user();

        // Otorisasi: admin/manager boleh download slip siapapun di tenant mereka
        if ($user->isAdmin() || $user->isManager()) {
            abort_unless($item->tenant_id === $user->tenant_id, 403);
        } else {
            abort_unless($employee && $item->employee_id === $employee->id, 403);
            abort_unless($item->tenant_id === $user->tenant_id, 403);
        }

        $item->load('employee', 'payrollRun', 'components');

        $overtimes = OvertimeRequest::where('tenant_id', $item->tenant_id)
            ->where('employee_id', $item->employee_id)
            ->where('status', 'approved')
            ->where('payroll_period', $item->payrollRun?->period)
            ->get();

        $companyName = config('app.name', 'Perusahaan');
        try {
            $profile = CompanyProfile::where('tenant_id', $item->tenant_id)->first();
        } catch (\Throwable) {
            $profile = null;
        }

        $pdf = Pdf::loadView('pdf.payslip', compact('item', 'overtimes', 'companyName', 'profile'))
            ->setPaper('a4', 'portrait');

        $period = $item->payrollRun?->period ?? 'slip';
        $empName = str_replace(' ', '_', $item->employee?->name ?? 'karyawan');
        $filename = "slip_gaji_{$empName}_{$period}.pdf";

        return $pdf->download($filename);
    }
}
