<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\PatientInsurance;
use App\Models\PatientMedicalRecord;
use App\Models\PatientVisit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PatientService
{
    /**
     * Create new patient with full medical profile
     */
    public function createPatient(array $data): Patient
    {
        return DB::transaction(function () use ($data) {
            // Create patient
            $patient = Patient::create([
                'medical_record_number' => $data['medical_record_number'] ?? null,
                'nik' => $data['nik'] ?? null,
                'full_name' => $data['full_name'],
                'short_name' => $data['short_name'] ?? null,
                'birth_date' => $data['birth_date'],
                'birth_place' => $data['birth_place'] ?? null,
                'gender' => $data['gender'],
                'blood_type' => $data['blood_type'] ?? null,
                'religion' => $data['religion'] ?? null,
                'marital_status' => $data['marital_status'] ?? null,
                'occupation' => $data['occupation'] ?? null,
                'nationality' => $data['nationality'] ?? 'Indonesian',
                'phone_primary' => $data['phone_primary'],
                'phone_secondary' => $data['phone_secondary'] ?? null,
                'email' => $data['email'] ?? null,
                'address_street' => $data['address_street'] ?? null,
                'address_rt' => $data['address_rt'] ?? null,
                'address_rw' => $data['address_rw'] ?? null,
                'address_kelurahan' => $data['address_kelurahan'] ?? null,
                'address_kecamatan' => $data['address_kecamatan'] ?? null,
                'address_city' => $data['address_city'] ?? null,
                'address_province' => $data['address_province'] ?? null,
                'address_postal_code' => $data['address_postal_code'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'emergency_contact_relation' => $data['emergency_contact_relation'] ?? null,
                'insurance_provider' => $data['insurance_provider'] ?? null,
                'insurance_policy_number' => $data['insurance_policy_number'] ?? null,
                'insurance_class' => $data['insurance_class'] ?? null,
                'known_allergies' => $data['known_allergies'] ?? null,
                'chronic_diseases' => $data['chronic_diseases'] ?? null,
                'current_medications' => $data['current_medications'] ?? null,
                'medical_notes' => $data['medical_notes'] ?? null,
                'registered_by' => $data['registered_by'] ?? null,
                'primary_doctor_id' => $data['primary_doctor_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Upload photo if provided
            if (isset($data['photo']) && $data['photo']) {
                $path = $data['photo']->store('patients/photos', 'public');
                $patient->update(['photo_path' => $path]);
            }

            // Upload ID card if provided
            if (isset($data['id_card']) && $data['id_card']) {
                $path = $data['id_card']->store('patients/documents', 'public');
                $patient->update(['id_card_path' => $path]);
            }

            // Upload insurance card if provided
            if (isset($data['insurance_card']) && $data['insurance_card']) {
                $path = $data['insurance_card']->store('patients/insurance', 'public');
                $patient->update(['insurance_card_path' => $path]);
            }

            // Generate QR Code
            $this->generateQrCode($patient);

            return $patient;
        });
    }

    /**
     * Update patient data
     */
    public function updatePatient(Patient $patient, array $data): Patient
    {
        return DB::transaction(function () use ($patient, $data) {
            $patient->update($data);

            // Upload new photo if provided
            if (isset($data['photo']) && $data['photo']) {
                // Delete old photo
                if ($patient->photo_path) {
                    Storage::disk('public')->delete($patient->photo_path);
                }
                $path = $data['photo']->store('patients/photos', 'public');
                $patient->update(['photo_path' => $path]);
            }

            // Upload new ID card if provided
            if (isset($data['id_card']) && $data['id_card']) {
                if ($patient->id_card_path) {
                    Storage::disk('public')->delete($patient->id_card_path);
                }
                $path = $data['id_card']->store('patients/documents', 'public');
                $patient->update(['id_card_path' => $path]);
            }

            return $patient->fresh();
        });
    }

    /**
     * Get patient by ID with relationships
     */
    public function getPatientById(int $id, array $relations = []): Patient
    {
        $query = Patient::query();

        if (! empty($relations)) {
            $query->with($relations);
        } else {
            // Default eager loading
            $query->with(['registeredBy', 'primaryDoctor', 'allergyRecords']);
        }

        return $query->findOrFail($id);
    }

    /**
     * Search patients
     */
    public function searchPatients(string $searchTerm, array $filters = [], int $perPage = 20)
    {
        $query = Patient::query();

        // Search by name, MRN, NIK, or phone
        if ($searchTerm) {
            $query->search($searchTerm);
        }

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['blood_type'])) {
            $query->bloodType($filters['blood_type']);
        }

        if (isset($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (isset($filters['has_allergies']) && $filters['has_allergies']) {
            $query->withAllergies();
        }

        if (isset($filters['has_chronic_diseases']) && $filters['has_chronic_diseases']) {
            $query->withChronicDiseases();
        }

        if (isset($filters['age_min']) && isset($filters['age_max'])) {
            $query->ageRange($filters['age_min'], $filters['age_max']);
        }

        if (isset($filters['insurance_provider'])) {
            $query->where('insurance_provider', $filters['insurance_provider']);
        }

        return $query->orderBy('full_name')->paginate($perPage);
    }

    /**
     * Get patient by medical record number
     */
    public function getByMedicalRecordNumber(string $mrn): ?Patient
    {
        return Patient::where('medical_record_number', $mrn)->first();
    }

    /**
     * Get patient by NIK
     */
    public function getByNik(string $nik): ?Patient
    {
        return Patient::where('nik', $nik)->first();
    }

    /**
     * Get patient by QR code
     */
    public function getByQrCode(string $qrCode): ?Patient
    {
        return Patient::where('qr_code', $qrCode)->first();
    }

    /**
     * Add allergy to patient
     */
    public function addAllergy(Patient $patient, array $data): PatientAllergy
    {
        $allergy = $patient->allergyRecords()->create([
            'allergen' => $data['allergen'],
            'allergen_type' => $data['allergen_type'],
            'severity' => $data['severity'],
            'reaction_description' => $data['reaction_description'] ?? null,
            'treatment_if_exposed' => $data['treatment_if_exposed'] ?? null,
            'diagnosed_date' => $data['diagnosed_date'] ?? now(),
            'diagnosed_by' => $data['diagnosed_by'] ?? null,
            'diagnosis_method' => $data['diagnosis_method'] ?? 'self_reported',
            'is_verified' => $data['is_verified'] ?? false,
            'notes' => $data['notes'] ?? null,
        ]);

        // Update patient's known_allergies JSON field
        $allergies = $patient->known_allergies ?? [];
        if (! in_array($data['allergen'], $allergies)) {
            $allergies[] = $data['allergen'];
            $patient->update(['known_allergies' => $allergies]);
        }

        return $allergy;
    }

    /**
     * Remove allergy from patient
     */
    public function removeAllergy(PatientAllergy $allergy): void
    {
        $patient = $allergy->patient;

        $allergy->delete();

        // Update patient's known_allergies JSON field
        $allergies = $patient->known_allergies ?? [];
        $allergies = array_diff($allergies, [$allergy->allergen]);
        $patient->update(['known_allergies' => array_values($allergies)]);
    }

    /**
     * Add insurance to patient
     */
    public function addInsurance(Patient $patient, array $data): PatientInsurance
    {
        // If this is set as primary, unset others
        if (isset($data['is_primary']) && $data['is_primary']) {
            $patient->insuranceRecords()->update(['is_primary' => false]);
        }

        return $patient->insuranceRecords()->create([
            'insurance_provider' => $data['insurance_provider'],
            'insurance_type' => $data['insurance_type'],
            'policy_number' => $data['policy_number'],
            'group_number' => $data['group_number'] ?? null,
            'member_id' => $data['member_id'] ?? null,
            'plan_name' => $data['plan_name'] ?? null,
            'plan_class' => $data['plan_class'] ?? null,
            'coverage_limit' => $data['coverage_limit'] ?? null,
            'deductible' => $data['deductible'] ?? 0,
            'copay_percentage' => $data['copay_percentage'] ?? 0,
            'covered_services' => $data['covered_services'] ?? null,
            'excluded_services' => $data['excluded_services'] ?? null,
            'effective_date' => $data['effective_date'],
            'expiry_date' => $data['expiry_date'],
            'is_primary' => $data['is_primary'] ?? false,
            'employer_name' => $data['employer_name'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Record patient visit
     */
    public function recordVisit(Patient $patient, array $data): PatientVisit
    {
        return DB::transaction(function () use ($patient, $data) {
            $visit = $patient->visits()->create([
                'doctor_id' => $data['doctor_id'] ?? null,
                'registered_by' => $data['registered_by'] ?? null,
                'visit_type' => $data['visit_type'],
                'visit_date' => $data['visit_date'] ?? now(),
                'visit_time' => $data['visit_time'] ?? now()->format('H:i:s'),
                'chief_complaint' => $data['chief_complaint'] ?? null,
                'visit_reason' => $data['visit_reason'] ?? null,
                'visit_status' => 'registered',
                'queue_number' => $data['queue_number'] ?? null,
                'department' => $data['department'] ?? null,
                'is_referral' => $data['is_referral'] ?? false,
                'referral_from' => $data['referral_from'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Increment patient's visit counter
            $patient->incrementVisits();

            return $visit;
        });
    }

    /**
     * Create medical record for visit
     */
    public function createMedicalRecord(PatientVisit $visit, array $data): PatientMedicalRecord
    {
        return $visit->medicalRecords()->create([
            'doctor_id' => $data['doctor_id'] ?? null,
            'record_type' => $data['record_type'] ?? 'consultation',
            'chief_complaint' => $data['chief_complaint'] ?? null,
            'history_of_present_illness' => $data['history_of_present_illness'] ?? null,
            'past_medical_history' => $data['past_medical_history'] ?? null,
            'family_history' => $data['family_history'] ?? null,
            'social_history' => $data['social_history'] ?? null,
            'vital_signs' => $data['vital_signs'] ?? null,
            'physical_examination' => $data['physical_examination'] ?? null,
            'examination_findings' => $data['examination_findings'] ?? null,
            'diagnosis' => $data['diagnosis'] ?? null,
            'differential_diagnosis' => $data['differential_diagnosis'] ?? null,
            'treatment_plan' => $data['treatment_plan'] ?? null,
            'medications_prescribed' => $data['medications_prescribed'] ?? null,
            'procedures_performed' => $data['procedures_performed'] ?? null,
            'doctor_notes' => $data['doctor_notes'] ?? null,
            'patient_instructions' => $data['patient_instructions'] ?? null,
            'follow_up_date' => $data['follow_up_date'] ?? null,
            'follow_up_instructions' => $data['follow_up_instructions'] ?? null,
            'is_emergency' => $data['is_emergency'] ?? false,
            'requires_follow_up' => $data['requires_follow_up'] ?? false,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Get patient statistics
     */
    public function getPatientStatistics(Patient $patient): array
    {
        return [
            'total_visits' => $patient->total_visits,
            'total_admissions' => $patient->total_admissions,
            'last_visit_date' => $patient->last_visit_date,
            'total_allergies' => $patient->allergyRecords()->active()->count(),
            'total_insurances' => $patient->insuranceRecords()->active()->count(),
            'active_insurance' => $patient->insuranceRecords()->valid()->first(),
            'upcoming_appointments' => $patient->appointments()->upcoming()->count(),
            'pending_follow_ups' => PatientMedicalRecord::where('patient_id', $patient->id)
                ->followUpDue()
                ->count(),
        ];
    }

    /**
     * Generate QR code data for patient
     * Note: For actual QR code image generation, install package:
     * composer require simplesoftwareio/simple-qrcode
     */
    public function generateQrCode(Patient $patient): string
    {
        $qrData = json_encode([
            'mrn' => $patient->medical_record_number,
            'name' => $patient->full_name,
            'dob' => $patient->birth_date->format('Y-m-d'),
            'qr_id' => $patient->qr_code,
        ]);

        // Store QR data as JSON file (can be converted to image later)
        $filename = 'patients/qr/'.$patient->qr_code.'.json';
        Storage::disk('public')->put($filename, $qrData);

        return $filename;
    }

    /**
     * Get patient timeline (visits, records, appointments)
     */
    public function getPatientTimeline(Patient $patient, int $limit = 50): array
    {
        $visits = $patient->visits()
            ->latest('visit_date')
            ->limit($limit)
            ->get()
            ->map(function ($visit) {
                return [
                    'type' => 'visit',
                    'date' => $visit->visit_date,
                    'title' => $visit->visit_type_label,
                    'description' => $visit->chief_complaint,
                    'status' => $visit->visit_status_label,
                    'data' => $visit,
                ];
            });

        $appointments = $patient->appointments()
            ->latest('appointment_date')
            ->limit($limit)
            ->get()
            ->map(function ($appointment) {
                return [
                    'type' => 'appointment',
                    'date' => $appointment->appointment_date,
                    'title' => $appointment->appointment_type_label,
                    'description' => $appointment->reason_for_visit,
                    'status' => $appointment->status_label,
                    'data' => $appointment,
                ];
            });

        $timeline = $visits->merge($appointments)
            ->sortByDesc('date')
            ->take($limit)
            ->values();

        return $timeline->toArray();
    }

    /**
     * Check if patient has active allergies
     */
    public function hasActiveAllergies(Patient $patient): bool
    {
        return $patient->allergyRecords()->active()->exists();
    }

    /**
     * Get patient's active allergies
     */
    public function getActiveAllergies(Patient $patient)
    {
        return $patient->allergyRecords()
            ->active()
            ->orderBy('severity')
            ->get();
    }

    /**
     * Get patient's valid insurance
     */
    public function getValidInsurance(Patient $patient): ?PatientInsurance
    {
        return $patient->insuranceRecords()
            ->valid()
            ->orderBy('is_primary', 'desc')
            ->first();
    }

    /**
     * Deactivate patient
     */
    public function deactivatePatient(Patient $patient, ?string $reason = null): void
    {
        $patient->update([
            'status' => 'inactive',
            'notes' => $reason ? ($patient->notes."\n\nDeactivated: ".$reason) : $patient->notes,
        ]);
    }

    /**
     * Blacklist patient
     */
    public function blacklistPatient(Patient $patient, string $reason): void
    {
        $patient->update([
            'is_blacklisted' => true,
            'blacklist_reason' => $reason,
            'status' => 'inactive',
        ]);
    }

    /**
     * Remove blacklist
     */
    public function removeBlacklist(Patient $patient): void
    {
        $patient->update([
            'is_blacklisted' => false,
            'blacklist_reason' => null,
        ]);
    }
}
