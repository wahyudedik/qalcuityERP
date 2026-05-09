<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\LabResult;
use App\Models\PatientVisit;

class ClinicalQualityController extends Controller
{
    public function index()
    {
        $qualityMetrics = [
            'readmission_rate' => $this->calculateReadmissionRate(),
            'average_length_of_stay' => $this->calculateAverageLengthOfStay(),
            'mortality_rate' => $this->calculateMortalityRate(),
            'infection_rate' => $this->calculateInfectionRate(),
            'patient_satisfaction' => 4.5,
        ];

        return view('healthcare.clinical-quality.index', compact('qualityMetrics'));
    }

    public function readmissionRate()
    {
        $rate = $this->calculateReadmissionRate();

        return response()->json(['success' => true, 'data' => $rate]);
    }

    public function averageLengthOfStay()
    {
        $alos = $this->calculateAverageLengthOfStay();

        return response()->json(['success' => true, 'data' => $alos]);
    }

    private function calculateReadmissionRate()
    {
        $totalReadmissions = PatientVisit::where('is_readmission', true)->count();
        $totalDischarges = PatientVisit::where('status', 'discharged')->count();

        return $totalDischarges > 0 ? round(($totalReadmissions / $totalDischarges) * 100, 2) : 0;
    }

    private function calculateAverageLengthOfStay()
    {
        $avgStay = PatientVisit::whereNotNull('discharge_date')
            ->whereNotNull('admission_date')
            ->selectRaw('AVG(DATEDIFF(discharge_date, admission_date)) as avg_stay')
            ->value('avg_stay');

        return round($avgStay ?? 0, 2);
    }

    private function calculateMortalityRate()
    {
        $totalDeaths = PatientVisit::where('discharge_status', 'death')->count();
        $totalDischarges = PatientVisit::where('status', 'discharged')->count();

        return $totalDischarges > 0 ? round(($totalDeaths / $totalDischarges) * 100, 4) : 0;
    }

    private function calculateInfectionRate()
    {
        $totalInfections = LabResult::where('is_critical', true)
            ->where('result_data->infection_type', '!=', null)
            ->count();

        $totalPatients = PatientVisit::count();

        return $totalPatients > 0 ? round(($totalInfections / $totalPatients) * 100, 2) : 0;
    }
}
