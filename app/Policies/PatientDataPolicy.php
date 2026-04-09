<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Auth\Access\Response;

class PatientDataPolicy
{
    /**
     * Determine if user can view patient data
     */
    public function view(User $user, Patient $patient): Response
    {
        // Superadmin has full access
        if ($user->hasRole('superadmin') || $user->is_superadmin) {
            return Response::allow();
        }

        // Patient can view their own data
        if ($user->patient && $user->patient->id === $patient->id) {
            return Response::allow();
        }

        // Tenant isolation check
        if ($user->tenant_id !== $patient->tenant_id) {
            return Response::deny('Cannot access patients outside your tenant.');
        }

        // Admin can view all patients in tenant
        if ($user->hasRole('admin')) {
            return Response::allow();
        }

        // Doctor can view patients
        if ($user->hasRole('doctor')) {
            return Response::allow();
        }

        // Nurse can view patients in their department/ward
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

        // Receptionist can view basic patient info
        if ($user->hasRole('receptionist')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to view this patient data.');
    }

    /**
     * Determine if user can view sensitive patient data (SSN, financial, etc.)
     */
    public function viewSensitive(User $user, Patient $patient): Response
    {
        // Base view permission required first
        $basePermission = $this->view($user, $patient);
        if ($basePermission->denied()) {
            return $basePermission;
        }

        // Only specific roles can view sensitive data
        $allowedRoles = ['superadmin', 'admin', 'doctor', 'billing_staff'];
        $hasRole = collect($allowedRoles)->some(fn($role) => $user->hasRole($role));

        if (!$hasRole && !$user->is_superadmin) {
            return Response::deny('You do not have permission to view sensitive patient data.');
        }

        return Response::allow();
    }

    /**
     * Determine if user can create patients
     */
    public function create(User $user): Response
    {
        if ($user->hasRole('superadmin') || $user->is_superadmin) {
            return Response::allow();
        }

        $allowedRoles = ['admin', 'doctor', 'receptionist'];
        $hasRole = collect($allowedRoles)->some(fn($role) => $user->hasRole($role));

        if (!$hasRole) {
            return Response::deny('You do not have permission to create patients.');
        }

        return Response::allow();
    }

    /**
     * Determine if user can update patient data
     */
    public function update(User $user, Patient $patient): Response
    {
        // Base view permission required
        $basePermission = $this->view($user, $patient);
        if ($basePermission->denied()) {
            return $basePermission;
        }

        // Patient cannot update their own data (must contact admin)
        if ($user->patient && $user->patient->id === $patient->id) {
            return Response::deny('Please contact administration to update your data.');
        }

        // Only admin, doctors, and receptionists can update
        $allowedRoles = ['superadmin', 'admin', 'doctor', 'receptionist'];
        $hasRole = collect($allowedRoles)->some(fn($role) => $user->hasRole($role));

        if (!$hasRole) {
            return Response::deny('You do not have permission to update patient data.');
        }

        return Response::allow();
    }

    /**
     * Determine if user can delete patients
     */
    public function delete(User $user, Patient $patient): Response
    {
        // Only superadmin and admin can delete (soft delete)
        if ($user->hasRole('superadmin') || $user->is_superadmin) {
            return Response::allow();
        }

        if ($user->hasRole('admin') && $user->tenant_id === $patient->tenant_id) {
            return Response::allow();
        }

        return Response::deny('Patients cannot be deleted. Contact administrator.');
    }

    /**
     * Determine if user can export patient data
     */
    public function export(User $user, Patient $patient): Response
    {
        // Base view permission required
        $basePermission = $this->view($user, $patient);
        if ($basePermission->denied()) {
            return $basePermission;
        }

        // Only admin and doctors can export
        if (
            !$user->hasRole('admin') &&
            !$user->hasRole('doctor') &&
            !$user->hasRole('superadmin')
        ) {
            return Response::deny('You do not have permission to export patient data.');
        }

        return Response::allow();
    }

    /**
     * Determine if user can view patient's financial data
     */
    public function viewFinancial(User $user, Patient $patient): Response
    {
        // Base view permission required
        $basePermission = $this->view($user, $patient);
        if ($basePermission->denied()) {
            return $basePermission;
        }

        // Only billing staff, admin, and doctors can view financial data
        $allowedRoles = ['superadmin', 'admin', 'doctor', 'billing_staff'];
        $hasRole = collect($allowedRoles)->some(fn($role) => $user->hasRole($role));

        if (!$hasRole) {
            return Response::deny('You do not have permission to view patient financial data.');
        }

        return Response::allow();
    }

    /**
     * Determine if user can view patient's contact information
     */
    public function viewContactInfo(User $user, Patient $patient): Response
    {
        // Base view permission required
        $basePermission = $this->view($user, $patient);
        if ($basePermission->denied()) {
            return $basePermission;
        }

        return Response::allow();
    }

    /**
     * Determine if user can share patient data
     */
    public function share(User $user, Patient $patient): Response
    {
        // Only doctors and admins can share patient data
        if (
            !$user->hasRole('doctor') &&
            !$user->hasRole('admin') &&
            !$user->hasRole('superadmin')
        ) {
            return Response::deny('You do not have permission to share patient data.');
        }

        // Must have view permission first
        return $this->view($user, $patient);
    }

    /**
     * Determine if user can anonymize patient data (for research)
     */
    public function anonymize(User $user): Response
    {
        // Only superadmin and researchers can anonymize
        if (
            !$user->hasRole('superadmin') &&
            !$user->hasRole('admin') &&
            !$user->hasRole('researcher')
        ) {
            return Response::deny('You do not have permission to anonymize patient data.');
        }

        return Response::allow();
    }

    /**
     * Determine if user can access patient portal
     */
    public function accessPortal(User $user): Response
    {
        // Patient can access their own portal
        if ($user->patient) {
            return Response::allow();
        }

        // Staff can access patient portal with proper permissions
        if ($user->hasRole('admin') || $user->hasPermission('healthcare.portal.access')) {
            return Response::allow();
        }

        return Response::deny('You do not have access to the patient portal.');
    }

    /**
     * Determine if user can view patient audit trail
     */
    public function viewAuditTrail(User $user, Patient $patient): Response
    {
        // Only admin and superadmin can view audit trails
        if (
            !$user->hasRole('admin') &&
            !$user->hasRole('superadmin') &&
            !$user->is_superadmin
        ) {
            return Response::deny('You do not have permission to view audit trails.');
        }

        // Must have view permission first
        return $this->view($user, $patient);
    }
}
