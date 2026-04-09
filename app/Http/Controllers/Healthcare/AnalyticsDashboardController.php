<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\Appointment;
use App\Models\MedicalBill;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticsDashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays($period);

        $patientStats = [
            'total_patients' => Patient::count(),
            'new_patients' => Patient::where('created_at', '>=', $startDate)->count(),
            'active_patients' => Patient::whereHas('visits', function ($q) use ($startDate) {
                $q->where('visit_date', '>=', $startDate);
            })->count(),
        ];

        $visitStats = [
            'total_visits' => PatientVisit::where('visit_date', '>=', $startDate)->count(),
            'outpatient' => PatientVisit::where('visit_date', '>=', $startDate)->where('visit_type', 'outpatient')->count(),
            'inpatient' => PatientVisit::where('visit_date', '>=', $startDate)->where('visit_type', 'inpatient')->count(),
            'emergency' => PatientVisit::where('visit_date', '>=', $startDate)->where('visit_type', 'emergency')->count(),
        ];

        $appointmentStats = [
            'total_appointments' => Appointment::where('appointment_date', '>=', $startDate)->count(),
            'completed' => Appointment::where('appointment_date', '>=', $startDate)->where('status', 'completed')->count(),
            'cancelled' => Appointment::where('appointment_date', '>=', $startDate)->where('status', 'cancelled')->count(),
            'no_show' => Appointment::where('appointment_date', '>=', $startDate)->where('status', 'no_show')->count(),
        ];

        $revenueStats = [
            'total_revenue' => MedicalBill::where('bill_date', '>=', $startDate)->sum('total_amount'),
            'paid' => MedicalBill::where('bill_date', '>=', $startDate)->sum('paid_amount'),
            'outstanding' => MedicalBill::where('bill_date', '>=', $startDate)->sum(\DB::raw('total_amount - paid_amount')),
        ];

        $dailyVisits = PatientVisit::where('visit_date', '>=', $startDate)
            ->selectRaw('DATE(visit_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('healthcare.analytics.dashboard', compact(
            'patientStats',
            'visitStats',
            'appointmentStats',
            'revenueStats',
            'dailyVisits'
        ));
    }

    public function patientDemographics()
    {
        $demographics = [
            'by_gender' => Patient::selectRaw('gender, COUNT(*) as count')
                ->groupBy('gender')
                ->get(),
            'by_age_group' => Patient::selectRaw("
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 18 THEN '0-17'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 35 THEN '18-34'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 50 THEN '35-49'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 65 THEN '50-64'
                    ELSE '65+'
                END as age_group,
                COUNT(*) as count
            ")->groupBy('age_group')->get(),
        ];

        return response()->json(['success' => true, 'data' => $demographics]);
    }

    public function revenueTrends(Request $request)
    {
        $period = $request->get('period', '12');
        $months = collect(range(1, $period))->map(function ($month) {
            $date = Carbon::now()->subMonths($month);
            return [
                'month' => $date->format('Y-m'),
                'revenue' => MedicalBill::whereYear('bill_date', $date->year)
                    ->whereMonth('bill_date', $date->month)
                    ->sum('total_amount'),
            ];
        });

        return response()->json(['success' => true, 'data' => $months]);
    }
}
