<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\InsuranceClaim;
use App\Models\MedicalBill;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth());

        $revenueByDepartment = MedicalBill::whereBetween('bill_date', [$dateFrom, $dateTo])
            ->selectRaw('department, SUM(total_amount) as total')
            ->groupBy('department')
            ->get();

        $revenueByPaymentMethod = MedicalBill::whereBetween('bill_date', [$dateFrom, $dateTo])
            ->selectRaw('payment_method, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->get();

        $statistics = [
            'total_revenue' => MedicalBill::whereBetween('bill_date', [$dateFrom, $dateTo])->sum('total_amount'),
            'total_paid' => MedicalBill::whereBetween('bill_date', [$dateFrom, $dateTo])->sum('paid_amount'),
            'total_outstanding' => MedicalBill::whereBetween('bill_date', [$dateFrom, $dateTo])->sum(\DB::raw('total_amount - paid_amount')),
            'insurance_claims' => InsuranceClaim::whereBetween('submission_date', [$dateFrom, $dateTo])->count(),
            'insurance_approved' => InsuranceClaim::whereBetween('submission_date', [$dateFrom, $dateTo])->where('status', 'approved')->sum('approved_amount'),
        ];

        return view('healthcare.financial-reports.index', compact(
            'revenueByDepartment',
            'revenueByPaymentMethod',
            'statistics',
            'dateFrom',
            'dateTo'
        ));
    }

    public function agingReport()
    {
        $aging = [
            'current' => MedicalBill::where('status', 'unpaid')
                ->whereRaw('DATEDIFF(CURDATE(), due_date) <= 0')
                ->sum('total_amount'),
            '1_30_days' => MedicalBill::where('status', 'unpaid')
                ->whereRaw('DATEDIFF(CURDATE(), due_date) BETWEEN 1 AND 30')
                ->sum('total_amount'),
            '31_60_days' => MedicalBill::where('status', 'unpaid')
                ->whereRaw('DATEDIFF(CURDATE(), due_date) BETWEEN 31 AND 60')
                ->sum('total_amount'),
            '61_90_days' => MedicalBill::where('status', 'unpaid')
                ->whereRaw('DATEDIFF(CURDATE(), due_date) BETWEEN 61 AND 90')
                ->sum('total_amount'),
            'over_90_days' => MedicalBill::where('status', 'unpaid')
                ->whereRaw('DATEDIFF(CURDATE(), due_date) > 90')
                ->sum('total_amount'),
        ];

        return view('healthcare.financial-reports.aging', compact('aging'));
    }

    public function export(Request $request)
    {
        return response()->json(['success' => true, 'message' => 'Report exported']);
    }
}
