<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Patient;
use App\Models\Emr;
use App\Models\Diagnosis;
use App\Models\Prescription;
use App\Models\LabResult;
use Illuminate\Auth\Access\Response;

class MedicalRecordPolicy
{
    /**
     * Determine if user can view medical records
     */
    public function view(User $user, Patient $patient): Response
    {
        // Superadmin has full access
        if ($user->hasRole('superadmin') || $user->is_superadmin) {
            return Response::allow();
        }

        // Patient can view their own records
        if ($user->patient && $user->patient->id === $patient->id) {
            return Response::allow();
        }

        // Doctor can view records of their patients
        if ($user->hasRole('doctor')) {
            if ($this->isAssignedDoctor($user, $patient)) {
                return Response::allow();
            }

            // Allow if in same department
            if ($user->department_id && $patient->assigned_department_id === $user->department_id) {
                return Response::allow();
            }
        }

        // Nurse can view records for patients in their ward/department
        if ($user->hasRole('nurse')) {
            if ($user->department_id && $patient->assigned_department_id === $user->department_id) {
                return Response::allow();
            }

            // Check if patient is admitted to nurse's ward
            if ($patient->currentAdmission && $user->ward_id) {
                if ($patient->currentAdmission->ward_id === $user->ward_id) {
                    return Response::allow();
                }
            }
        }

        // Admin can view all records in their tenant
        if ($user->hasRole('admin') && $user->tenant_id === $patient->tenant_id) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to view this medical record.');
    }

    /**
     * Determine if user can create medical records
     */
    public function create(User $user, ?Patient $patient = null): Response
    {
        if ($user->hasRole('superadmin') || $user->is_superadmin) {
            return Response::allow();
        }

        // Only doctors and nurses can create medical records
        if (!$user->hasRole('doctor') && !$user->hasRole('nurse')) {
            return Response::deny('Only medical staff can create medical records.');
        }

        // Check tenant isolation
        if ($patient && $user->tenant_id !== $patient->tenant_id) {
            return Response::deny('Cannot create records for patients outside your tenant.');
        }

        return Response::allow();
    }

    /**
     * Determine if user can update medical records
     */
    public function update(User $user, Emr $emr): Response
    {
        // Superadmin has full access
        if ($user->hasRole('superadmin') || $user->is_superadmin) {
            return Response::allow();
        }

        // Only the attending doctor or authorized staff can update
        if ($user->hasRole('doctor')) {
            if ($emr->doctor_id === $user->id) {
                return Response::allow();
            }

            // Allow if same department
            if ($user->department_id && $emr->department_id === $user->department_id) {
                return Response::allow();
            }
        }

        // Nurses can update vital signs but not diagnoses
        if ($user->hasRole('nurse')) {
            if ($emr->department_id === $user->department_id) {
                return Response::allow();
            }
        }

        return Response::deny('You do not have permission to update this medical record.');
    }

    /**
     * Determine if user can delete medical records
     */
    public function delete(User $user, Emr $emr): Response
    {
        // Only superadmin or admin can delete (soft delete)
        if ($user->hasRole('superadmin') || $user->is_superadmin) {
            return Response::allow();
        }

        if ($user->hasRole('admin') && $user->tenant_id === $emr->tenant_id) {
            return Response::allow();
        }

        return Response::deny('Medical records cannot be deleted. Contact administrator.');
    }

    /**
     * Determine if user can view diagnoses
     */
    public function viewDiagnoses(User $user, Patient $patient): Response
    {
        return $this->view($user, $patient);
    }

    /**
     * Determine if user can create diagnoses
     */
    public function createDiagnosis(User $user, Patient $patient): Response
    {
        // Only doctors can create diagnoses
        if (!$user->hasRole('doctor')) {
            return Response::deny('Only doctors can create diagnoses.');
        }

        return $this->view($user, $patient);
    }

    /**
     * Determine if user can view prescriptions
     */
    public function viewPrescriptions(User $user, Patient $patient): Response
    {
        return $this->view($user, $patient);
    }

    /**
     * Determine if user can create prescriptions
     */
    public function createPrescription(User $user, Patient $patient): Response
    {
        // Only doctors can prescribe medication
        if (!$user->hasRole('doctor')) {
            return Response::deny('Only doctors can create prescriptions.');
        }

        return $this->view($user, $patient);
    }

    /**
     * Determine if user can fulfill prescriptions (pharmacist)
     */
    public function fulfillPrescription(User $user, Prescription $prescription): Response
    {
        // Pharmacists can fulfill prescriptions
        if ($user->hasRole('pharmacist')) {
            return Response::allow();
        }

        // Doctors can fulfill their own prescriptions
        if ($user->hasRole('doctor') && $prescription->doctor_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to fulfill this prescription.');
    }

    /**
     * Determine if user can view lab results
     */
    public function viewLabResults(User $user, Patient $patient): Response
    {
        return $this->view($user, $patient);
    }

    /**
     * Determine if user can create lab results
     */
    public function createLabResult(User $user, LabResult $labResult): Response
    {
        // Lab technicians can create results
        if ($user->hasRole('lab_technician')) {
            return Response::allow();
        }

        // Doctors can view and add notes
        if ($user->hasRole('doctor')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to create lab results.');
    }

    /**
     * Determine if user can export medical records
     */
    public function export(User $user, Patient $patient): Response
    {
        // Only doctors and admins can export
        if (
            !$user->hasRole('doctor') &&
            !$user->hasRole('admin') &&
            !$user->hasRole('superadmin')
        ) {
            return Response::deny('You do not have permission to export medical records.');
        }

        return $this->view($user, $patient);
    }

    /**
     * Determine if user can print medical records
     */
    public function print(User $user, Patient $patient): Response
    {
        return $this->view($user, $patient);
    }

    /**
     * Check if user is the assigned doctor for patient
     */
    protected function isAssignedDoctor(User $user, Patient $patient): bool
    {
        // Check if doctor is assigned to patient's current visit
        if ($patient->currentVisit && $patient->currentVisit->doctor_id === $user->id) {
            return true;
        }

        // Check if doctor is assigned to patient's admission
        if ($patient->currentAdmission && $patient->currentAdmission->attending_doctor_id === $user->id) {
            return true;
        }

        // Check recent appointments
        $recentAppointment = \App\Models\Appointment::where('patient_id', $patient->id)
            ->where('doctor_id', $user->id)
            ->where('appointment_date', '>=', now()->subDays(30))
            ->exists();

        return $recentAppointment;
    }
}
