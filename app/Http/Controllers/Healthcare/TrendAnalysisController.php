<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\PatientVisit;
use App\Models\MedicalBill;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TrendAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', '12');

        $visitTrends = $this->getVisitTrends($period);
        $revenueTrends = $this->getRevenueTrends($period);
        $diagnosisTrends = $this->getTopDiagnoses();

        return view('healthcare.trend-analysis.index', compact(
            'visitTrends',
            'revenueTrends',
            'diagnosisTrends',
            'period'
        ));
    }

    public function visitTrends(Request $request)
    {
        $period = $request->get('period', '12');
        $trends = $this->getVisitTrends($period);

        return response()->json(['success' => true, 'data' => $trends]);
    }

    public function revenueTrends(Request $request)
    {
        $period = $request->get('period', '12');
        $trends = $this->getRevenueTrends($period);

        return response()->json(['success' => true, 'data' => $trends]);
    }

    private function getVisitTrends($months)
    {
        $trends = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = PatientVisit::whereYear('visit_date', $date->year)
                ->whereMonth('visit_date', $date->month)
                ->count();

            $trends[] = [
                'period' => $date->format('Y-m'),
                'count' => $count,
            ];
        }

        return $trends;
    }

    private function getRevenueTrends($months)
    {
        $trends = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = MedicalBill::whereYear('bill_date', $date->year)
                ->whereMonth('bill_date', $date->month)
                ->sum('total_amount');

            $trends[] = [
                'period' => $date->format('Y-m'),
                'revenue' => $revenue,
            ];
        }

        return $trends;
    }

    private function getTopDiagnoses()
    {
        return PatientVisit::selectRaw('diagnosis, COUNT(*) as count')
            ->whereNotNull('diagnosis')
            ->groupBy('diagnosis')
            ->orderByDesc('count')
            ->limit(10)
            ->get();
    }
}
