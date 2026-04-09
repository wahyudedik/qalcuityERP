<?php

namespace App\Services;

use App\Models\EmergencyCase;
use App\Models\TriageAssessment;
use App\Models\EmergencyTreatment;
use App\Models\ErAlert;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TriageService
{
    /**
     * Register new emergency case
     */
    public function registerEmergencyCase(array $data): EmergencyCase
    {
        return DB::transaction(function () use ($data) {
            $case = EmergencyCase::create([
                'patient_id' => $data['patient_id'],
                'triage_nurse_id' => $data['triage_nurse_id'] ?? null,
                'arrival_time' => $data['arrival_time'] ?? now(),
                'chief_complaint' => $data['chief_complaint'],
                'mechanism_of_injury' => $data['mechanism_of_injury'] ?? null,
                'arrival_mode' => $data['arrival_mode'] ?? 'walk-in',
                'brought_by' => $data['brought_by'] ?? null,
                'status' => 'triaged',
            ]);

            Log::info("Emergency case registered", [
                'case_number' => $case->case_number,
                'patient_id' => $case->patient_id,
            ]);

            return $case;
        });
    }

    /**
     * Perform triage assessment
     */
    public function performTriageAssessment(int $caseId, array $assessmentData): TriageAssessment
    {
        return DB::transaction(function () use ($caseId, $assessmentData) {
            $case = EmergencyCase::findOrFail($caseId);

            // Calculate triage level based on assessment
            $triageLevel = $this->calculateTriageLevel($assessmentData);
            $esiLevel = $this->calculateESILevel($assessmentData);

            // Update case with triage info
            $case->update([
                'triage_time' => now(),
                'triage_level' => $triageLevel,
                'triage_code' => 'ESI-' . $esiLevel,
                'door_to_triage_minutes' => $case->calculateDoorToTriage(),
                'is_critical' => in_array($triageLevel, ['red', 'orange']),
                'requires_immediate_intervention' => $assessmentData['requires_immediate_intervention'] ?? false,
                'requires_isolation' => $assessmentData['requires_isolation'] ?? false,
                'status' => $triageLevel === 'red' ? 'critical' : 'waiting',
            ]);

            // Create triage assessment
            $assessment = TriageAssessment::create([
                'case_id' => $caseId,
                'assessed_by' => $assessmentData['assessed_by'],
                'assessment_time' => now(),
                'assessment_number' => $this->getNextAssessmentNumber($caseId),
                'vital_signs' => $assessmentData['vital_signs'] ?? [],
                'temperature' => $assessmentData['temperature'] ?? null,
                'heart_rate' => $assessmentData['heart_rate'] ?? null,
                'blood_pressure_systolic' => $assessmentData['blood_pressure_systolic'] ?? null,
                'blood_pressure_diastolic' => $assessmentData['blood_pressure_diastolic'] ?? null,
                'respiratory_rate' => $assessmentData['respiratory_rate'] ?? null,
                'oxygen_saturation' => $assessmentData['oxygen_saturation'] ?? null,
                'pain_scale' => $assessmentData['pain_scale'] ?? null,
                'gcs_eye' => $assessmentData['gcs_eye'] ?? null,
                'gcs_verbal' => $assessmentData['gcs_verbal'] ?? null,
                'gcs_motor' => $assessmentData['gcs_motor'] ?? null,
                'gcs_total' => $assessmentData['gcs_total'] ?? null,
                'urgency_level' => $this->mapTriageToUrgency($triageLevel),
                'esi_level' => $esiLevel,
                'nurse_notes' => $assessmentData['nurse_notes'],
                'chief_complaint_details' => $assessmentData['chief_complaint_details'] ?? null,
                'allergies' => $assessmentData['allergies'] ?? null,
                'current_medications' => $assessmentData['current_medications'] ?? null,
                'medical_history' => $assessmentData['medical_history'] ?? null,
                'recommended_actions' => $assessmentData['recommended_actions'] ?? null,
                'requires_immediate_intervention' => $case->requires_immediate_intervention,
                'requires_isolation' => $case->requires_isolation,
            ]);

            // Send alert for critical cases
            if ($case->is_critical) {
                $this->sendCriticalAlert($case, $assessment);
            }

            Log::info("Triage assessment completed", [
                'case_number' => $case->case_number,
                'triage_level' => $triageLevel,
                'esi_level' => $esiLevel,
            ]);

            return $assessment;
        });
    }

    /**
     * Calculate triage level based on vital signs and symptoms
     */
    public function calculateTriageLevel(array $assessmentData): string
    {
        // RED - Resuscitation (Immediate life threat)
        if ($this->isImmediateLifeThreat($assessmentData)) {
            return 'red';
        }

        // ORANGE - Emergent (High risk, confused/lethargic)
        if ($this->isEmergent($assessmentData)) {
            return 'orange';
        }

        // YELLOW - Urgent (Stable but needs multiple resources)
        if ($this->isUrgent($assessmentData)) {
            return 'yellow';
        }

        // GREEN - Less Urgent (Needs 1 resource)
        if ($this->isLessUrgent($assessmentData)) {
            return 'green';
        }

        // BLACK - Expectant (Deceased or unsalvageable)
        return 'black';
    }

    /**
     * Calculate ESI (Emergency Severity Index) Level 1-5
     */
    public function calculateESILevel(array $assessmentData): int
    {
        // ESI-1: Immediate life saving intervention needed
        if ($this->needsImmediateLifeSaving($assessmentData)) {
            return 1;
        }

        // ESI-2: High risk, confused/lethargic, severe pain
        if ($this->isHighRisk($assessmentData)) {
            return 2;
        }

        // ESI-3-5: Based on number of resources needed
        $resourcesNeeded = $this->estimateResourcesNeeded($assessmentData);

        if ($resourcesNeeded >= 3) {
            return 3;
        } elseif ($resourcesNeeded === 2) {
            return 4;
        } else {
            return 5;
        }
    }

    /**
     * Start emergency treatment
     */
    public function startTreatment(int $caseId, int $doctorId): EmergencyCase
    {
        return DB::transaction(function () use ($caseId, $doctorId) {
            $case = EmergencyCase::findOrFail($caseId);

            $case->update([
                'emergency_doctor_id' => $doctorId,
                'treatment_started_at' => now(),
                'door_to_doctor_minutes' => $case->arrival_time->diffInMinutes(now()),
                'door_to_treatment_minutes' => $case->arrival_time->diffInMinutes(now()),
                'status' => $case->is_critical ? 'critical' : 'in_treatment',
            ]);

            Log::info("Emergency treatment started", [
                'case_number' => $case->case_number,
                'doctor_id' => $doctorId,
            ]);

            return $case;
        });
    }

    /**
     * Complete emergency treatment
     */
    public function completeTreatment(int $caseId, array $treatmentData): EmergencyTreatment
    {
        return DB::transaction(function () use ($caseId, $treatmentData) {
            $case = EmergencyCase::findOrFail($caseId);

            // Create treatment record
            $treatment = EmergencyTreatment::create([
                'case_id' => $caseId,
                'patient_id' => $case->patient_id,
                'treated_by' => $treatmentData['treated_by'],
                'assisted_by' => $treatmentData['assisted_by'] ?? null,
                'treatment_start' => $case->treatment_started_at ?? now(),
                'treatment_end' => now(),
                'duration_minutes' => $case->treatment_started_at ?
                    $case->treatment_started_at->diffInMinutes(now()) : 0,
                'treatment_given' => $treatmentData['treatment_given'],
                'diagnosis' => $treatmentData['diagnosis'] ?? null,
                'icd10_code' => $treatmentData['icd10_code'] ?? null,
                'medications_given' => $treatmentData['medications_given'] ?? [],
                'procedures_performed' => $treatmentData['procedures_performed'] ?? [],
                'interventions' => $treatmentData['interventions'] ?? [],
                'response_to_treatment' => $treatmentData['response_to_treatment'] ?? null,
                'outcome' => $treatmentData['outcome'],
                'outcome_notes' => $treatmentData['outcome_notes'] ?? null,
                'disposition' => $treatmentData['disposition'],
                'disposition_notes' => $treatmentData['disposition_notes'] ?? null,
                'admitted_to_ward' => $treatmentData['admitted_to_ward'] ?? null,
                'transferred_to' => $treatmentData['transferred_to'] ?? null,
                'follow_up_instructions' => $treatmentData['follow_up_instructions'] ?? null,
                'follow_up_date' => $treatmentData['follow_up_date'] ?? null,
            ]);

            // Update case status
            $case->update([
                'treatment_ended_at' => now(),
                'disposition_time' => now(),
                'status' => $this->mapDispositionToStatus($treatmentData['disposition']),
                'disposition' => $treatmentData['disposition'],
                'total_er_duration_minutes' => $case->calculateTotalDuration(),
            ]);

            // Create admission if needed
            if ($treatmentData['disposition'] === 'admitted' && isset($treatmentData['admission_id'])) {
                $case->update(['admission_id' => $treatmentData['admission_id']]);
            }

            Log::info("Emergency treatment completed", [
                'case_number' => $case->case_number,
                'outcome' => $treatmentData['outcome'],
                'disposition' => $treatmentData['disposition'],
            ]);

            return $treatment;
        });
    }

    /**
     * Send critical patient alert
     */
    public function sendCriticalAlert(EmergencyCase $case, TriageAssessment $assessment): ErAlert
    {
        return ErAlert::create([
            'case_id' => $case->id,
            'patient_id' => $case->patient_id,
            'alerted_by' => $assessment->assessed_by,
            'alert_type' => 'critical_patient',
            'alert_title' => 'CRITICAL: ' . $case->case_number,
            'alert_message' => "Patient requires immediate attention. Triage: {$case->triage_level}. "
                . "Chief complaint: {$case->chief_complaint}",
            'priority' => 'critical',
            'status' => 'active',
            'alerted_at' => now(),
        ]);
    }

    /**
     * Get ER dashboard data
     */
    public function getERDashboard(): array
    {
        $activeCases = EmergencyCase::active()->with(['patient', 'latestTriageAssessment'])->get();

        $dashboard = [
            'total_active' => $activeCases->count(),
            'critical_count' => $activeCases->where('is_critical', true)->count(),
            'waiting_count' => $activeCases->where('status', 'waiting')->count(),
            'in_treatment_count' => $activeCases->where('status', 'in_treatment')->count(),

            'triage_distribution' => [
                'red' => $activeCases->where('triage_level', 'red')->count(),
                'orange' => $activeCases->where('triage_level', 'orange')->count(),
                'yellow' => $activeCases->where('triage_level', 'yellow')->count(),
                'green' => $activeCases->where('triage_level', 'green')->count(),
            ],

            'critical_cases' => $activeCases->filter(fn($c) => $c->requiresImmediateAttention())
                ->map(fn($c) => $c->summary),

            'waiting_patients' => $activeCases->where('status', 'waiting')
                ->sortBy('triage_level')
                ->map(fn($c) => $c->summary),

            'active_alerts' => ErAlert::where('status', 'active')
                ->orderBy('priority')
                ->limit(10)
                ->get(),

            'average_wait_time' => EmergencyCase::today()
                ->whereNotNull('door_to_triage_minutes')
                ->avg('door_to_triage_minutes') ?? 0,
        ];

        return $dashboard;
    }

    /**
     * Generate daily ER analytics
     */
    public function generateDailyAnalytics($date = null): array
    {
        $date = $date ? \Carbon\Carbon::parse($date) : today();

        $cases = EmergencyCase::whereDate('arrival_time', $date)->get();

        $analytics = [
            'analytics_date' => $date,
            'total_cases' => $cases->count(),
            'total_treated' => $cases->where('status', '!=', 'triaged')->count(),
            'total_admitted' => $cases->where('disposition', 'admitted')->count(),
            'total_discharged' => $cases->where('disposition', 'discharged_home')->count(),
            'total_transferred' => $cases->where('disposition', 'transferred')->count(),
            'total_deceased' => $cases->where('disposition', 'deceased')->count(),
            'current_in_er' => $cases->whereIn('status', ['triaged', 'waiting', 'in_treatment', 'critical', 'stable'])->count(),

            'triage_red' => $cases->where('triage_level', 'red')->count(),
            'triage_orange' => $cases->where('triage_level', 'orange')->count(),
            'triage_yellow' => $cases->where('triage_level', 'yellow')->count(),
            'triage_green' => $cases->where('triage_level', 'green')->count(),
            'triage_black' => $cases->where('triage_level', 'black')->count(),

            'avg_door_to_triage' => round($cases->avg('door_to_triage_minutes') ?? 0, 2),
            'avg_door_to_doctor' => round($cases->avg('door_to_doctor_minutes') ?? 0, 2),
            'avg_door_to_treatment' => round($cases->avg('door_to_treatment_minutes') ?? 0, 2),
            'avg_total_er_duration' => round($cases->avg('total_er_duration_minutes') ?? 0, 2),

            'outcome_improved' => EmergencyTreatment::whereDate('treatment_end', $date)
                ->where('outcome', 'improved')->count(),
            'outcome_stable' => EmergencyTreatment::whereDate('treatment_end', $date)
                ->where('outcome', 'stable')->count(),
            'outcome_worsened' => EmergencyTreatment::whereDate('treatment_end', $date)
                ->where('outcome', 'worsened')->count(),
            'outcome_deceased' => EmergencyTreatment::whereDate('treatment_end', $date)
                ->where('outcome', 'deceased')->count(),
        ];

        return $analytics;
    }

    /**
     * Get throughput metrics
     */
    public function getThroughputMetrics($startDate = null, $endDate = null): array
    {
        $startDate = $startDate ? \Carbon\Carbon::parse($startDate) : today()->startOfMonth();
        $endDate = $endDate ? \Carbon\Carbon::parse($endDate) : today()->endOfMonth();

        $cases = EmergencyCase::whereBetween('arrival_time', [$startDate, $endDate])->get();
        $treatments = EmergencyTreatment::whereBetween('treatment_end', [$startDate, $endDate])->get();

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'total_cases' => $cases->count(),
            'total_treated' => $treatments->count(),
            'admission_rate' => $cases->count() > 0
                ? round(($cases->where('disposition', 'admitted')->count() / $cases->count()) * 100, 2)
                : 0,
            'discharge_rate' => $cases->count() > 0
                ? round(($cases->where('disposition', 'discharged_home')->count() / $cases->count()) * 100, 2)
                : 0,
            'average_er_duration_minutes' => round($cases->avg('total_er_duration_minutes') ?? 0, 2),
            'average_door_to_triage_minutes' => round($cases->avg('door_to_triage_minutes') ?? 0, 2),
            'average_door_to_treatment_minutes' => round($cases->avg('door_to_treatment_minutes') ?? 0, 2),
            'mortality_rate' => $cases->count() > 0
                ? round(($cases->where('disposition', 'deceased')->count() / $cases->count()) * 100, 2)
                : 0,
        ];
    }

    // Helper Methods

    protected function isImmediateLifeThreat($data): bool
    {
        // Cardiac arrest, respiratory arrest, severe trauma
        $hr = $data['heart_rate'] ?? null;
        $spo2 = $data['oxygen_saturation'] ?? null;
        $gcs = $data['gcs_total'] ?? null;

        if ($hr && ($hr < 40 || $hr > 150))
            return true;
        if ($spo2 && $spo2 < 90)
            return true;
        if ($gcs && $gcs <= 8)
            return true;

        return false;
    }

    protected function isEmergent($data): bool
    {
        $pain = $data['pain_scale'] ?? 0;
        $hr = $data['heart_rate'] ?? null;

        if ($pain >= 8)
            return true;
        if ($hr && ($hr < 50 || $hr > 130))
            return true;

        return false;
    }

    protected function isUrgent($data): bool
    {
        return ($data['pain_scale'] ?? 0) >= 5;
    }

    protected function isLessUrgent($data): bool
    {
        return true; // Default
    }

    protected function needsImmediateLifeSaving($data): bool
    {
        return $this->isImmediateLifeThreat($data);
    }

    protected function isHighRisk($data): bool
    {
        return $this->isEmergent($data);
    }

    protected function estimateResourcesNeeded($data): int
    {
        $resources = 0;

        if (isset($data['requires_lab']))
            $resources++;
        if (isset($data['requires_imaging']))
            $resources++;
        if (isset($data['requires_iv']))
            $resources++;
        if (isset($data['requires_medication']))
            $resources++;
        if (isset($data['requires_specialist']))
            $resources++;

        return $resources;
    }

    protected function mapTriageToUrgency($triageLevel): string
    {
        $map = [
            'red' => 'resuscitation',
            'orange' => 'emergent',
            'yellow' => 'urgent',
            'green' => 'less_urgent',
            'black' => 'non_urgent',
        ];

        return $map[$triageLevel] ?? 'less_urgent';
    }

    protected function mapDispositionToStatus($disposition): string
    {
        $map = [
            'discharged' => 'discharged',
            'admitted' => 'admitted',
            'transferred' => 'transferred',
            'referred' => 'referred',
            'ama' => 'ama',
            'deceased' => 'deceased',
        ];

        return $map[$disposition] ?? 'discharged';
    }

    protected function getNextAssessmentNumber(int $caseId): int
    {
        return TriageAssessment::where('case_id', $caseId)->count() + 1;
    }
}
