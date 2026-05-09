<?php

namespace App\Services;

use App\Models\HospitalAnalyticsDaily;
use App\Models\PatientSatisfactionSurvey;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HospitalAnalyticsService
{
    /**
     * Calculate Bed Occupancy Rate (BOR)
     * Formula: (Occupied Beds / Total Beds) × 100%
     * Standard: 60-85% (optimal)
     */
    public function calculateBedOccupancyRate($startDate, $endDate): array
    {
        $period = $this->getDaysBetween($startDate, $endDate);

        // Total bed-days available
        $totalBeds = $this->getTotalBeds();
        $availableBedDays = $totalBeds * $period;

        // Calculate occupied bed-days
        $occupiedBedDays = $this->calculateOccupiedBedDays($startDate, $endDate);

        // BOR calculation
        $bor = $availableBedDays > 0
            ? round(($occupiedBedDays / $availableBedDays) * 100, 2)
            : 0;

        return [
            'bed_occupancy_rate' => $bor,
            'total_beds' => $totalBeds,
            'occupied_bed_days' => $occupiedBedDays,
            'available_bed_days' => $availableBedDays,
            'period_days' => $period,
            'status' => $this->getBORStatus($bor),
        ];
    }

    /**
     * Calculate Average Length of Stay (ALOS)
     * Formula: Total Patient Days / Total Discharges
     * Standard: 3-7 days (depends on hospital type)
     */
    public function calculateAverageLengthOfStay($startDate, $endDate): array
    {
        // Get all discharged patients in period
        $dischargeStats = DB::table('admissions')
            ->whereBetween('discharge_date', [$startDate, $endDate])
            ->whereNotNull('discharge_date')
            ->selectRaw('
                COUNT(*) as total_discharges,
                SUM(TIMESTAMPDIFF(HOUR, admission_date, discharge_date) / 24) as total_patient_days
            ')
            ->first();

        $totalDischarges = $dischargeStats->total_discharges ?? 0;
        $totalPatientDays = $dischargeStats->total_patient_days ?? 0;

        $alos = $totalDischarges > 0
            ? round($totalPatientDays / $totalDischarges, 2)
            : 0;

        return [
            'average_length_of_stay' => $alos,
            'total_discharges' => $totalDischarges,
            'total_patient_days' => round($totalPatientDays, 2),
            'status' => $this->getALOSStatus($alos),
        ];
    }

    /**
     * Calculate Patient Turnover Rate
     * Formula: (Total Discharges / Total Admissions) × 100%
     */
    public function calculatePatientTurnoverRate($startDate, $endDate): array
    {
        $totalAdmissions = DB::table('admissions')
            ->whereBetween('admission_date', [$startDate, $endDate])
            ->count();

        $totalDischarges = DB::table('admissions')
            ->whereBetween('discharge_date', [$startDate, $endDate])
            ->whereNotNull('discharge_date')
            ->count();

        $turnoverRate = $totalAdmissions > 0
            ? round(($totalDischarges / $totalAdmissions) * 100, 2)
            : 0;

        return [
            'patient_turnover_rate' => $turnoverRate,
            'total_admissions' => $totalAdmissions,
            'total_discharges' => $totalDischarges,
        ];
    }

    /**
     * Calculate Doctor Utilization Rate
     * Formula: (Active Consultation Hours / Available Hours) × 100%
     */
    public function calculateDoctorUtilizationRate($startDate, $endDate): array
    {
        // Total active doctors
        $activeDoctors = DB::table('doctors')
            ->where('is_active', true)
            ->count();

        // Available hours (assuming 8 hours/day)
        $period = $this->getDaysBetween($startDate, $endDate);
        $availableHours = $activeDoctors * $period * 8;

        // Calculate consultation hours from appointments
        $consultationHours = DB::table('appointments')
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->selectRaw('SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time) / 60) as total_hours')
            ->value('total_hours') ?? 0;

        $utilizationRate = $availableHours > 0
            ? round(($consultationHours / $availableHours) * 100, 2)
            : 0;

        return [
            'doctor_utilization_rate' => $utilizationRate,
            'active_doctors' => $activeDoctors,
            'consultation_hours' => round($consultationHours, 2),
            'available_hours' => $availableHours,
            'total_consultations' => DB::table('appointments')
                ->whereBetween('appointment_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->count(),
        ];
    }

    /**
     * Calculate Revenue per Patient
     * Formula: Total Revenue / Total Patients
     */
    public function calculateRevenuePerPatient($startDate, $endDate): array
    {
        // Total revenue from billing
        $totalRevenue = DB::table('medical_bills')
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->where('status', 'paid')
            ->sum('total_amount');

        // Total unique patients
        $totalPatients = DB::table('medical_bills')
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->distinct('patient_id')
            ->count('patient_id');

        $revenuePerPatient = $totalPatients > 0
            ? round($totalRevenue / $totalPatients, 2)
            : 0;

        return [
            'revenue_per_patient' => $revenuePerPatient,
            'total_revenue' => $totalRevenue,
            'total_patients' => $totalPatients,
            'total_bills' => DB::table('medical_bills')
                ->whereBetween('bill_date', [$startDate, $endDate])
                ->count(),
        ];
    }

    /**
     * Calculate Mortality Rate
     * Formula: (Total Deaths / Total Discharges) × 100%
     * Standard: < 3%
     */
    public function calculateMortalityRate($startDate, $endDate): array
    {
        $totalDeaths = DB::table('admissions')
            ->whereBetween('discharge_date', [$startDate, $endDate])
            ->where('discharge_status', 'death')
            ->count();

        $totalDischarges = DB::table('admissions')
            ->whereBetween('discharge_date', [$startDate, $endDate])
            ->whereNotNull('discharge_date')
            ->count();

        $mortalityRate = $totalDischarges > 0
            ? round(($totalDeaths / $totalDischarges) * 100, 2)
            : 0;

        return [
            'mortality_rate' => $mortalityRate,
            'total_deaths' => $totalDeaths,
            'total_discharges' => $totalDischarges,
            'status' => $this->getMortalityStatus($mortalityRate),
        ];
    }

    /**
     * Calculate Hospital Acquired Infection (HAI) Rate
     * Formula: (New HAI Cases / Total Patient Days) × 1000
     * Standard: < 5 per 1000 patient-days
     */
    public function calculateInfectionRate($startDate, $endDate): array
    {
        // Get total patient days
        $dischargeStats = DB::table('admissions')
            ->whereBetween('discharge_date', [$startDate, $endDate])
            ->whereNotNull('discharge_date')
            ->selectRaw('SUM(TIMESTAMPDIFF(HOUR, admission_date, discharge_date) / 24) as total_patient_days')
            ->first();

        $totalPatientDays = $dischargeStats->total_patient_days ?? 0;

        // Count HAI cases (from clinical quality indicators or infection logs)
        $totalInfections = DB::table('clinical_quality_indicators')
            ->where('category', 'HAI')
            ->whereBetween('measurement_date', [$startDate, $endDate])
            ->sum('numerator');

        $infectionRate = $totalPatientDays > 0
            ? round(($totalInfections / $totalPatientDays) * 1000, 2)
            : 0;

        return [
            'infection_rate' => $infectionRate,
            'total_infections' => $totalInfections,
            'total_patient_days' => round($totalPatientDays, 2),
            'per_1000_patient_days' => $infectionRate,
            'status' => $this->getInfectionStatus($infectionRate),
        ];
    }

    /**
     * Calculate Readmission Rate
     * Formula: (Patients Readmitted within 30 days / Total Discharges) × 100%
     */
    public function calculateReadmissionRate($startDate, $endDate): array
    {
        $totalDischarges = DB::table('admissions')
            ->whereBetween('discharge_date', [$startDate, $endDate])
            ->whereNotNull('discharge_date')
            ->count();

        // Count readmissions within 30 days
        $readmissions = DB::table('admissions as a1')
            ->join('admissions as a2', 'a1.patient_id', '=', 'a2.patient_id')
            ->whereBetween('a1.discharge_date', [$startDate, $endDate])
            ->whereNotNull('a1.discharge_date')
            ->whereColumn('a2.admission_date', '>', 'a1.discharge_date')
            ->whereColumn('a2.admission_date', '<=', DB::raw('DATE_ADD(a1.discharge_date, INTERVAL 30 DAY)'))
            ->distinct('a2.patient_id')
            ->count('a2.id');

        $readmissionRate = $totalDischarges > 0
            ? round(($readmissions / $totalDischarges) * 100, 2)
            : 0;

        return [
            'readmission_rate' => $readmissionRate,
            'total_readmissions' => $readmissions,
            'total_discharges' => $totalDischarges,
        ];
    }

    /**
     * Calculate Surgery Cancellation Rate
     * Formula: (Cancelled Surgeries / Total Scheduled Surgeries) × 100%
     */
    public function calculateSurgeryCancellationRate($startDate, $endDate): array
    {
        $totalSurgeries = DB::table('surgery_schedules')
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->count();

        $cancelledSurgeries = DB::table('surgery_schedules')
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->where('status', 'cancelled')
            ->count();

        $cancellationRate = $totalSurgeries > 0
            ? round(($cancelledSurgeries / $totalSurgeries) * 100, 2)
            : 0;

        return [
            'surgery_cancelation_rate' => $cancellationRate,
            'total_surgeries' => $totalSurgeries,
            'cancelled_surgeries' => $cancelledSurgeries,
        ];
    }

    /**
     * Get Patient Satisfaction Metrics
     */
    public function getPatientSatisfactionMetrics($startDate, $endDate): array
    {
        $averageRating = PatientSatisfactionSurvey::getAverageRating($startDate, $endDate);
        $npsScore = PatientSatisfactionSurvey::calculateNPS($startDate, $endDate);

        $totalSurveys = PatientSatisfactionSurvey::whereBetween('submitted_date', [$startDate, $endDate])
            ->count();

        // Rating distribution
        $ratingDistribution = PatientSatisfactionSurvey::whereBetween('submitted_date', [$startDate, $endDate])
            ->selectRaw('overall_rating, COUNT(*) as count')
            ->groupBy('overall_rating')
            ->pluck('count', 'overall_rating')
            ->toArray();

        return [
            'average_satisfaction_rating' => round($averageRating ?? 0, 2),
            'nps_score' => $npsScore,
            'total_surveys' => $totalSurveys,
            'rating_distribution' => $ratingDistribution,
            'response_rate' => $this->calculateSurveyResponseRate($startDate, $endDate),
        ];
    }

    /**
     * Generate Financial Reports - Revenue by Department
     */
    public function getRevenueByDepartment($startDate, $endDate): array
    {
        return DB::table('medical_bills')
            ->join('departments', 'medical_bills.department_id', '=', 'departments.id')
            ->whereBetween('medical_bills.bill_date', [$startDate, $endDate])
            ->select(
                'departments.name as department_name',
                DB::raw('COUNT(*) as total_bills'),
                DB::raw('SUM(medical_bills.total_amount) as total_revenue'),
                DB::raw('AVG(medical_bills.total_amount) as average_revenue'),
                DB::raw('SUM(CASE WHEN medical_bills.status = "paid" THEN 1 ELSE 0 END) as paid_bills'),
                DB::raw('SUM(CASE WHEN medical_bills.status = "pending" THEN 1 ELSE 0 END) as pending_bills')
            )
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('total_revenue')
            ->get()
            ->toArray();
    }

    /**
     * Generate Financial Reports - Insurance Mix
     */
    public function getInsuranceMix($startDate, $endDate): array
    {
        return DB::table('medical_bills')
            ->leftJoin('insurance_claims', 'medical_bills.id', '=', 'insurance_claims.bill_id')
            ->whereBetween('medical_bills.bill_date', [$startDate, $endDate])
            ->select(
                DB::raw('CASE 
                    WHEN medical_bills.payment_type = "insurance" THEN insurance_claims.insurance_provider
                    ELSE "Self Pay" 
                END as payer'),
                DB::raw('COUNT(*) as total_bills'),
                DB::raw('SUM(medical_bills.total_amount) as total_revenue'),
                DB::raw('ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM medical_bills WHERE bill_date BETWEEN ? AND ?), 2) as percentage'),
                DB::raw('AVG(medical_bills.total_amount) as average_bill')
            )
            ->setBindings([$startDate, $endDate, $startDate, $endDate])
            ->groupBy('payer')
            ->orderByDesc('total_revenue')
            ->get()
            ->toArray();
    }

    /**
     * Generate Clinical Quality Indicators Dashboard
     */
    public function getClinicalQualityDashboard($startDate, $endDate): array
    {
        return [
            'mortality_rate' => $this->calculateMortalityRate($startDate, $endDate),
            'infection_rate' => $this->calculateInfectionRate($startDate, $endDate),
            'readmission_rate' => $this->calculateReadmissionRate($startDate, $endDate),
            'surgery_cancellation_rate' => $this->calculateSurgeryCancellationRate($startDate, $endDate),
            'patient_satisfaction' => $this->getPatientSatisfactionMetrics($startDate, $endDate),
        ];
    }

    /**
     * Generate Ministry of Health Report (SIRS/SIMRS)
     */
    public function generateMinistryReport(array $reportData): array
    {
        $reportType = $reportData['report_type']; // RL1, RL2, etc.
        $periodStart = $reportData['period_start'];
        $periodEnd = $reportData['period_end'];

        // Collect data based on report type
        $data = match ($reportType) {
            'RL1' => $this->collectRL1Data($periodStart, $periodEnd),
            'RL2' => $this->collectRL2Data($periodStart, $periodEnd),
            'RL3' => $this->collectRL3Data($periodStart, $periodEnd),
            'RL4a' => $this->collectRL4aData($periodStart, $periodEnd),
            'RL4b' => $this->collectRL4bData($periodStart, $periodEnd),
            default => [],
        };

        return [
            'report_type' => $reportType,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'generated_at' => now(),
            'data' => $data,
        ];
    }

    /**
     * Generate complete KPI Dashboard
     */
    public function generateKPIDashboard($startDate, $endDate): array
    {
        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
                'days' => $this->getDaysBetween($startDate, $endDate),
            ],
            'bed_occupancy' => $this->calculateBedOccupancyRate($startDate, $endDate),
            'average_length_of_stay' => $this->calculateAverageLengthOfStay($startDate, $endDate),
            'patient_turnover' => $this->calculatePatientTurnoverRate($startDate, $endDate),
            'doctor_utilization' => $this->calculateDoctorUtilizationRate($startDate, $endDate),
            'revenue_per_patient' => $this->calculateRevenuePerPatient($startDate, $endDate),
            'mortality_rate' => $this->calculateMortalityRate($startDate, $endDate),
            'infection_rate' => $this->calculateInfectionRate($startDate, $endDate),
            'readmission_rate' => $this->calculateReadmissionRate($startDate, $endDate),
            'surgery_cancellation' => $this->calculateSurgeryCancellationRate($startDate, $endDate),
            'patient_satisfaction' => $this->getPatientSatisfactionMetrics($startDate, $endDate),
        ];
    }

    /**
     * Record daily analytics snapshot
     */
    public function recordDailyAnalytics($date): HospitalAnalyticsDaily
    {
        $kpi = $this->generateKPIDashboard($date, $date);

        return HospitalAnalyticsDaily::updateOrCreate(
            ['analytics_date' => $date],
            [
                'bed_occupancy_rate' => $kpi['bed_occupancy']['bed_occupancy_rate'],
                'total_beds' => $kpi['bed_occupancy']['total_beds'],
                'occupied_beds' => $kpi['bed_occupancy']['occupied_bed_days'],
                'average_length_of_stay' => $kpi['average_length_of_stay']['average_length_of_stay'],
                'patient_turnover_rate' => $kpi['patient_turnover']['patient_turnover_rate'],
                'doctor_utilization_rate' => $kpi['doctor_utilization']['doctor_utilization_rate'],
                'revenue_per_patient' => $kpi['revenue_per_patient']['revenue_per_patient'],
                'mortality_rate' => $kpi['mortality_rate']['mortality_rate'],
                'infection_rate' => $kpi['infection_rate']['infection_rate'],
                'readmission_rate' => $kpi['readmission_rate']['readmission_rate'],
                'surgery_cancelation_rate' => $kpi['surgery_cancellation']['surgery_cancelation_rate'],
                'average_satisfaction_rating' => $kpi['patient_satisfaction']['average_satisfaction_rating'],
                'nps_score' => $kpi['patient_satisfaction']['nps_score'],
            ]
        );
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    protected function getDaysBetween($startDate, $endDate): int
    {
        return Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
    }

    protected function getTotalBeds(): int
    {
        return DB::table('beds')->where('is_active', true)->count();
    }

    protected function calculateOccupiedBedDays($startDate, $endDate): float
    {
        return DB::table('admissions')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('admission_date', [$startDate, $endDate])
                    ->orWhereBetween('discharge_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('admission_date', '<=', $startDate)
                            ->where(function ($q3) use ($endDate) {
                                $q3->where('discharge_date', '>=', $endDate)
                                    ->orWhereNull('discharge_date');
                            });
                    });
            })
            ->selectRaw('SUM(
                TIMESTAMPDIFF(HOUR, 
                    GREATEST(admission_date, ?),
                    LEAST(COALESCE(discharge_date, ?), ?)
                ) / 24
            ) as total_days', [$startDate, $endDate, $endDate])
            ->value('total_days') ?? 0;
    }

    protected function getBORStatus($bor): string
    {
        return match (true) {
            $bor < 60 => 'underutilized',
            $bor <= 85 => 'optimal',
            default => 'overutilized',
        };
    }

    protected function getALOSStatus($alos): string
    {
        return match (true) {
            $alos < 3 => 'short',
            $alos <= 7 => 'normal',
            default => 'long',
        };
    }

    protected function getMortalityStatus($rate): string
    {
        return match (true) {
            $rate < 3 => 'acceptable',
            $rate < 5 => 'warning',
            default => 'critical',
        };
    }

    protected function getInfectionStatus($rate): string
    {
        return match (true) {
            $rate < 5 => 'acceptable',
            $rate < 10 => 'warning',
            default => 'critical',
        };
    }

    protected function calculateSurveyResponseRate($startDate, $endDate): float
    {
        $totalPatients = DB::table('patient_visits')
            ->whereBetween('visit_date', [$startDate, $endDate])
            ->distinct('patient_id')
            ->count('patient_id');

        $totalSurveys = PatientSatisfactionSurvey::whereBetween('submitted_date', [$startDate, $endDate])
            ->count();

        return $totalPatients > 0
            ? round(($totalSurveys / $totalPatients) * 100, 2)
            : 0;
    }

    // Ministry Report Data Collection Methods
    protected function collectRL1Data($startDate, $endDate): array
    {
        return [
            'bed_occupancy' => $this->calculateBedOccupancyRate($startDate, $endDate),
            'average_length_of_stay' => $this->calculateAverageLengthOfStay($startDate, $endDate),
        ];
    }

    protected function collectRL2Data($startDate, $endDate): array
    {
        return [
            'mortality_rate' => $this->calculateMortalityRate($startDate, $endDate),
            'infection_rate' => $this->calculateInfectionRate($startDate, $endDate),
        ];
    }

    protected function collectRL3Data($startDate, $endDate): array
    {
        return [
            'revenue_by_department' => $this->getRevenueByDepartment($startDate, $endDate),
            'insurance_mix' => $this->getInsuranceMix($startDate, $endDate),
        ];
    }

    protected function collectRL4aData($startDate, $endDate): array
    {
        return [
            'patient_satisfaction' => $this->getPatientSatisfactionMetrics($startDate, $endDate),
        ];
    }

    protected function collectRL4bData($startDate, $endDate): array
    {
        return [
            'surgery_metrics' => $this->calculateSurgeryCancellationRate($startDate, $endDate),
            'readmission_rate' => $this->calculateReadmissionRate($startDate, $endDate),
        ];
    }
}
