<?php

namespace App\Services;

use App\Models\Diagnosis;
use App\Models\Patient;
use App\Models\PatientMedicalRecord;
use App\Models\PatientVisit;
use App\Models\Prescription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * EMRService - Enhanced Electronic Medical Record management
 */
class EMRService
{
    /**
     * Get comprehensive patient dashboard data
     */
    public function getPatientDashboard(int $patientId): array
    {
        return Cache::remember("emr_dashboard_{$patientId}", 300, function () use ($patientId) {
            $patient = Patient::with(['allergies', 'activePrescriptions'])->findOrFail($patientId);

            return [
                'patient' => $patient,
                'vital_signs_trend' => $this->getVitalSignsTrend($patientId),
                'active_medications' => $this->getActiveMedications($patientId),
                'upcoming_appointments' => $this->getUpcomingAppointments($patientId),
                'recent_lab_results' => $this->getRecentLabResults($patientId),
                'allergy_alerts' => $this->getAllergyAlerts($patient),
                'statistics' => $this->getPatientStatistics($patientId),
            ];
        });
    }

    /**
     * Get vital signs trend for last 30 days
     */
    public function getVitalSignsTrend(int $patientId, int $days = 30): array
    {
        $records = PatientMedicalRecord::where('patient_id', $patientId)
            ->where('record_date', '>=', now()->subDays($days))
            ->whereNotNull('vital_signs')
            ->orderBy('record_date', 'asc')
            ->get();

        $trend = [
            'labels' => [],
            'temperature' => [],
            'blood_pressure_systolic' => [],
            'blood_pressure_diastolic' => [],
            'heart_rate' => [],
            'respiratory_rate' => [],
            'spo2' => [],
            'weight' => [],
            'bmi' => [],
        ];

        foreach ($records as $record) {
            $trend['labels'][] = $record->record_date->format('d M');
            $vitals = $record->vital_signs;

            $trend['temperature'][] = $vitals['temperature'] ?? null;
            $trend['heart_rate'][] = $vitals['heart_rate'] ?? null;
            $trend['respiratory_rate'][] = $vitals['respiratory_rate'] ?? null;
            $trend['spo2'][] = $vitals['spo2'] ?? null;
            $trend['weight'][] = $vitals['weight'] ?? null;
            $trend['bmi'][] = $record->bmi;

            // Parse blood pressure (e.g., "120/80")
            if (isset($vitals['blood_pressure'])) {
                $parts = explode('/', $vitals['blood_pressure']);
                $trend['blood_pressure_systolic'][] = isset($parts[0]) ? (int) $parts[0] : null;
                $trend['blood_pressure_diastolic'][] = isset($parts[1]) ? (int) $parts[1] : null;
            } else {
                $trend['blood_pressure_systolic'][] = null;
                $trend['blood_pressure_diastolic'][] = null;
            }
        }

        return $trend;
    }

    /**
     * Get active medications with alerts
     */
    public function getActiveMedications(int $patientId): Collection
    {
        return Prescription::where('patient_id', $patientId)
            ->where('status', 'active')
            ->where('valid_until', '>=', now())
            ->with(['doctor', 'medicine'])
            ->orderBy('prescribed_date', 'desc')
            ->get();
    }

    /**
     * Get upcoming appointments
     */
    public function getUpcomingAppointments(int $patientId, int $limit = 5): Collection
    {
        return PatientVisit::where('patient_id', $patientId)
            ->where('status', 'scheduled')
            ->where('visit_date', '>=', now())
            ->with(['doctor', 'department'])
            ->orderBy('visit_date', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent lab results
     */
    public function getRecentLabResults(int $patientId, int $limit = 5): Collection
    {
        // Placeholder - will be implemented with lab orders model
        return collect([]);
    }

    /**
     * Get allergy alerts
     */
    public function getAllergyAlerts(Patient $patient): Collection
    {
        return $patient->allergies()
            ->where('is_active', true)
            ->whereIn('severity', ['severe', 'life-threatening'])
            ->get();
    }

    /**
     * Get patient statistics
     */
    public function getPatientStatistics(int $patientId): array
    {
        return [
            'total_visits' => PatientVisit::where('patient_id', $patientId)->count(),
            'total_prescriptions' => Prescription::where('patient_id', $patientId)->count(),
            'last_visit_date' => PatientVisit::where('patient_id', $patientId)
                ->orderBy('visit_date', 'desc')
                ->value('visit_date'),
            'chronic_conditions' => Diagnosis::whereHas('medicalRecord', function ($q) use ($patientId) {
                $q->where('patient_id', $patientId);
            })
                ->where('is_chronic', true)
                ->distinct('icd10_code')
                ->count('icd10_code'),
        ];
    }

    /**
     * Clear patient dashboard cache
     */
    public function clearDashboardCache(int $patientId): void
    {
        Cache::forget("emr_dashboard_{$patientId}");
    }

    /**
     * Build SOAP format note
     */
    public function buildSOAPNote(array $data): array
    {
        return [
            'subjective' => [
                'chief_complaint' => $data['chief_complaint'] ?? '',
                'history_of_present_illness' => $data['history_of_present_illness'] ?? '',
                'review_of_systems' => $data['review_of_systems'] ?? [],
                'patient_notes' => $data['patient_notes'] ?? '',
            ],
            'objective' => [
                'vital_signs' => $data['vital_signs'] ?? [],
                'physical_examination' => $data['physical_examination'] ?? '',
                'examination_findings' => $data['examination_findings'] ?? '',
                'lab_results' => $data['lab_results'] ?? [],
                'imaging_results' => $data['imaging_results'] ?? [],
            ],
            'assessment' => [
                'diagnoses' => $data['diagnoses'] ?? [],
                'differential_diagnosis' => $data['differential_diagnosis'] ?? '',
                'severity' => $data['severity'] ?? 'moderate',
                'notes' => $data['assessment_notes'] ?? '',
            ],
            'plan' => [
                'treatment_plan' => $data['treatment_plan'] ?? '',
                'medications' => $data['medications'] ?? [],
                'procedures' => $data['procedures'] ?? [],
                'follow_up_date' => $data['follow_up_date'] ?? null,
                'follow_up_instructions' => $data['follow_up_instructions'] ?? '',
                'patient_education' => $data['patient_education'] ?? '',
                'referrals' => $data['referrals'] ?? [],
            ],
        ];
    }

    /**
     * Validate SOAP note completeness
     */
    public function validateSOAPNote(array $soapNote): array
    {
        $warnings = [];

        if (empty($soapNote['subjective']['chief_complaint'])) {
            $warnings[] = 'Chief complaint is required';
        }

        if (empty($soapNote['objective']['vital_signs'])) {
            $warnings[] = 'Vital signs should be recorded';
        }

        if (empty($soapNote['assessment']['diagnoses'])) {
            $warnings[] = 'At least one diagnosis is recommended';
        }

        return [
            'is_valid' => empty($warnings),
            'warnings' => $warnings,
        ];
    }

    /**
     * Get comprehensive patient timeline
     */
    public function getPatientTimeline(int $patientId, array $filters = []): Collection
    {
        $timeline = collect();

        // Medical records
        $recordsQuery = PatientMedicalRecord::where('patient_id', $patientId)
            ->with(['doctor']);

        if (isset($filters['date_from'])) {
            $recordsQuery->where('record_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $recordsQuery->where('record_date', '<=', $filters['date_to']);
        }

        $recordsQuery->get()->each(function ($record) use ($timeline) {
            $timeline->push([
                'id' => "record_{$record->id}",
                'date' => $record->record_date,
                'type' => 'medical_record',
                'title' => 'Medical Record',
                'description' => $record->chief_complaint,
                'doctor' => $record->doctor?->name,
                'color' => 'blue',
                'icon' => 'clipboard',
                'details' => [
                    'diagnosis' => $record->diagnosis,
                    'treatment' => $record->treatment_plan,
                ],
            ]);
        });

        // Visits
        $visitsQuery = PatientVisit::where('patient_id', $patientId)
            ->with(['doctor', 'department']);

        if (isset($filters['date_from'])) {
            $visitsQuery->where('visit_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $visitsQuery->where('visit_date', '<=', $filters['date_to']);
        }

        $visitsQuery->get()->each(function ($visit) use ($timeline) {
            $timeline->push([
                'id' => "visit_{$visit->id}",
                'date' => $visit->visit_date,
                'type' => 'visit',
                'title' => "Visit - {$visit->department?->name}",
                'description' => $visit->chief_complaint,
                'doctor' => $visit->doctor?->name,
                'color' => 'green',
                'icon' => 'calendar',
                'details' => [
                    'status' => $visit->status,
                    'visit_type' => $visit->visit_type,
                ],
            ]);
        });

        // Prescriptions
        Prescription::where('patient_id', $patientId)
            ->with(['doctor'])
            ->orderBy('prescribed_date', 'desc')
            ->get()
            ->each(function ($prescription) use ($timeline) {
                $timeline->push([
                    'id' => "rx_{$prescription->id}",
                    'date' => $prescription->prescribed_date,
                    'type' => 'prescription',
                    'title' => "Prescription: {$prescription->medication_name}",
                    'description' => "{$prescription->dosage} - {$prescription->frequency}",
                    'doctor' => $prescription->doctor?->name,
                    'color' => 'purple',
                    'icon' => 'pill',
                    'details' => [
                        'status' => $prescription->status,
                        'valid_until' => $prescription->valid_until,
                    ],
                ]);
            });

        // Sort by date descending
        $timeline = $timeline->sortByDesc('date')->values();

        // Apply type filter if provided
        if (isset($filters['type']) && $filters['type'] !== 'all') {
            $timeline = $timeline->where('type', $filters['type'])->values();
        }

        return $timeline;
    }

    /**
     * Check drug interactions
     */
    public function checkDrugInteractions(array $medicationNames): array
    {
        $interactions = [];

        // Sample drug interaction database (in production, use external API)
        $interactionDB = [
            ['drug1' => 'warfarin', 'drug2' => 'aspirin', 'severity' => 'high', 'description' => 'Increased risk of bleeding'],
            ['drug1' => 'metformin', 'drug2' => 'contrast dye', 'severity' => 'high', 'description' => 'Risk of lactic acidosis'],
            ['drug1' => 'statins', 'drug2' => 'grapefruit', 'severity' => 'moderate', 'description' => 'Increased statin levels'],
            ['drug1' => 'lisinopril', 'drug2' => 'potassium', 'severity' => 'moderate', 'description' => 'Risk of hyperkalemia'],
            ['drug1' => 'ciprofloxacin', 'drug2' => 'antacids', 'severity' => 'moderate', 'description' => 'Reduced absorption'],
        ];

        for ($i = 0; $i < count($medicationNames); $i++) {
            for ($j = $i + 1; $j < count($medicationNames); $j++) {
                $drug1 = strtolower($medicationNames[$i]);
                $drug2 = strtolower($medicationNames[$j]);

                foreach ($interactionDB as $interaction) {
                    if (
                        (strpos($drug1, $interaction['drug1']) !== false && strpos($drug2, $interaction['drug2']) !== false) ||
                        (strpos($drug1, $interaction['drug2']) !== false && strpos($drug2, $interaction['drug1']) !== false)
                    ) {
                        $interactions[] = [
                            'drug1' => $medicationNames[$i],
                            'drug2' => $medicationNames[$j],
                            'severity' => $interaction['severity'],
                            'description' => $interaction['description'],
                        ];
                    }
                }
            }
        }

        return [
            'has_interactions' => count($interactions) > 0,
            'interactions' => $interactions,
            'count' => count($interactions),
        ];
    }

    /**
     * Get ICD-10 codes with search
     */
    public function searchICD10(string $query, int $limit = 20): array
    {
        // Sample ICD-10 database (in production, use full database or API)
        $icd10Codes = $this->getICD10Database();

        $query = strtolower($query);
        $results = array_filter($icd10Codes, function ($code) use ($query) {
            return strpos(strtolower($code['code']), $query) !== false ||
                strpos(strtolower($code['description']), $query) !== false;
        });

        return array_slice(array_values($results), 0, $limit);
    }

    /**
     * Get ICD-10 database (sample)
     */
    private function getICD10Database(): array
    {
        return [
            ['code' => 'A00', 'description' => 'Cholera'],
            ['code' => 'A01', 'description' => 'Typhoid and paratyphoid fevers'],
            ['code' => 'A09', 'description' => 'Diarrhoea and gastroenteritis'],
            ['code' => 'B34', 'description' => 'Viral infection, unspecified'],
            ['code' => 'B95', 'description' => 'Streptococcus, Staphylococcus, and Enterococcus'],
            ['code' => 'C50', 'description' => 'Malignant neoplasm of breast'],
            ['code' => 'D50', 'description' => 'Iron deficiency anaemia'],
            ['code' => 'E11', 'description' => 'Type 2 diabetes mellitus'],
            ['code' => 'E78', 'description' => 'Disorders of lipoprotein metabolism'],
            ['code' => 'I10', 'description' => 'Essential (primary) hypertension'],
            ['code' => 'I20', 'description' => 'Angina pectoris'],
            ['code' => 'I21', 'description' => 'Acute myocardial infarction'],
            ['code' => 'I25', 'description' => 'Chronic ischaemic heart disease'],
            ['code' => 'I48', 'description' => 'Atrial fibrillation and flutter'],
            ['code' => 'I50', 'description' => 'Heart failure'],
            ['code' => 'J00', 'description' => 'Acute nasopharyngitis (common cold)'],
            ['code' => 'J02', 'description' => 'Acute pharyngitis'],
            ['code' => 'J03', 'description' => 'Acute tonsillitis'],
            ['code' => 'J06', 'description' => 'Acute upper respiratory infections'],
            ['code' => 'J18', 'description' => 'Pneumonia, unspecified organism'],
            ['code' => 'J20', 'description' => 'Acute bronchitis'],
            ['code' => 'J22', 'description' => 'Unspecified acute lower respiratory infection'],
            ['code' => 'J45', 'description' => 'Asthma'],
            ['code' => 'K21', 'description' => 'Gastro-oesophageal reflux disease'],
            ['code' => 'K29', 'description' => 'Gastritis and duodenitis'],
            ['code' => 'K30', 'description' => 'Functional dyspepsia'],
            ['code' => 'L20', 'description' => 'Atopic dermatitis'],
            ['code' => 'L30', 'description' => 'Other dermatitis'],
            ['code' => 'M15', 'description' => 'Polyosteoarthritis'],
            ['code' => 'M17', 'description' => 'Gonarthrosis (osteoarthritis of knee)'],
            ['code' => 'M54', 'description' => 'Dorsalgia (back pain)'],
            ['code' => 'M79', 'description' => 'Other soft tissue disorders'],
            ['code' => 'N18', 'description' => 'Chronic kidney disease'],
            ['code' => 'N39', 'description' => 'Other disorders of urinary system'],
            ['code' => 'R00', 'description' => 'Abnormalities of heart beat'],
            ['code' => 'R05', 'description' => 'Cough'],
            ['code' => 'R06', 'description' => 'Abnormalities of breathing'],
            ['code' => 'R07', 'description' => 'Pain in throat and chest'],
            ['code' => 'R10', 'description' => 'Abdominal and pelvic pain'],
            ['code' => 'R11', 'description' => 'Nausea and vomiting'],
            ['code' => 'R50', 'description' => 'Fever of unknown origin'],
            ['code' => 'R51', 'description' => 'Headache'],
            ['code' => 'R52', 'description' => 'Pain, unspecified'],
            ['code' => 'S00', 'description' => 'Superficial injury of head'],
            ['code' => 'S80', 'description' => 'Superficial injury of lower leg'],
            ['code' => 'T14', 'description' => 'Injury of unspecified body region'],
            ['code' => 'Z00', 'description' => 'General examination without complaint'],
            ['code' => 'Z23', 'description' => 'Immunization'],
        ];
    }

    /**
     * Generate prescription PDF for printing
     */
    public function generatePrescriptionPDF(int $prescriptionId): array
    {
        $prescription = Prescription::with(['patient', 'doctor', 'medicine'])
            ->findOrFail($prescriptionId);

        return [
            'prescription' => $prescription,
            'clinic_info' => config('healthcare.clinic', [
                'name' => 'Healthcare Clinic',
                'address' => 'Clinic Address',
                'phone' => '+62-xxx-xxxx',
                'logo' => null,
            ]),
        ];
    }
}
