<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\HrmAiService;
use Illuminate\Http\Request;

class HrmAiController extends Controller
{
    public function __construct(private HrmAiService $ai) {}

    private function tid(): int { return auth()->user()->tenant_id; }

    /**
     * GET /hrm/ai/attendance-anomalies?months=3
     * Deteksi pola absensi tidak wajar untuk semua karyawan aktif.
     */
    public function attendanceAnomalies(Request $request)
    {
        $months = (int) $request->input('months', 3);
        $months = max(1, min(12, $months));

        $anomalies = $this->ai->detectAttendanceAnomalies($this->tid(), $months);

        return response()->json([
            'anomalies' => array_values($anomalies),
            'total'     => count($anomalies),
            'months'    => $months,
        ]);
    }

    /**
     * GET /hrm/ai/salary-suggest/{employee}
     * Suggest komponen gaji untuk karyawan tertentu.
     */
    public function salarySuggest(Employee $employee)
    {
        abort_unless($employee->tenant_id === $this->tid(), 403);

        $suggestion = $this->ai->suggestSalaryComponents($this->tid(), $employee->id);

        return response()->json(['suggestion' => $suggestion]);
    }
}
