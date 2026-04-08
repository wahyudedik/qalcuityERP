<?php

namespace App\Services;

use App\Models\OvertimeRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * OvertimeApprovalService - Secure overtime approval with separation of duties
 * 
 * BUG-HRM-004 FIX: Prevent self-approval and enforce approval hierarchy
 * 
 * Security Rules:
 * 1. Employee CANNOT approve their own overtime
 * 2. Must be manager/admin role to approve
 * 3. Approval must be from different user than requester
 * 4. Audit trail for all approval actions
 * 5. Hierarchy-based approval (direct manager preferred)
 */
class OvertimeApprovalService
{
    /**
     * BUG-HRM-004 FIX: Validate if user can approve overtime request
     * 
     * @param User $approver User attempting to approve
     * @param OvertimeRequest $overtime Overtime request to approve
     * @return array ['allowed' => bool, 'reason' => string]
     */
    public function canApprove(User $approver, OvertimeRequest $overtime): array
    {
        // Rule 1: Cannot approve own overtime
        if ($this->isSelfApproval($approver, $overtime)) {
            return [
                'allowed' => false,
                'reason' => 'Anda tidak dapat menyetujui lembur Anda sendiri. Harap minta persetujuan atasan.',
            ];
        }

        // Rule 2: Must be admin or manager
        if (!$this->hasApprovalRole($approver)) {
            return [
                'allowed' => false,
                'reason' => 'Hanya Admin atau Manager yang dapat menyetujui lembur.',
            ];
        }

        // Rule 3: Same tenant only
        if ($approver->tenant_id !== $overtime->tenant_id) {
            return [
                'allowed' => false,
                'reason' => 'Anda tidak memiliki akses ke pengajuan lembur ini.',
            ];
        }

        // Rule 4: Overtime must be pending
        if ($overtime->status !== 'pending') {
            return [
                'allowed' => false,
                'reason' => 'Pengajuan lembur ini sudah diproses (status: ' . $overtime->status . ').',
            ];
        }

        // All checks passed
        return [
            'allowed' => true,
            'reason' => 'Approval diperbolehkan.',
        ];
    }

    /**
     * BUG-HRM-004 FIX: Approve overtime with security validation
     * 
     * @param User $approver
     * @param OvertimeRequest $overtime
     * @return array Result with success status and message
     */
    public function approve(User $approver, OvertimeRequest $overtime): array
    {
        // Validate approval permission
        $validation = $this->canApprove($approver, $overtime);

        if (!$validation['allowed']) {
            Log::warning('Overtime: Self-approval attempt blocked', [
                'approver_id' => $approver->id,
                'approver_name' => $approver->name,
                'overtime_id' => $overtime->id,
                'employee_id' => $overtime->employee_id,
                'reason' => $validation['reason'],
            ]);

            return [
                'success' => false,
                'message' => $validation['reason'],
            ];
        }

        // Calculate overtime pay
        $overtime->load('employee');
        $pay = $overtime->calculatePay();

        // Approve with audit trail
        $overtime->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'overtime_pay' => $pay,
        ]);

        // Log approval action
        Log::info('Overtime: Approved', [
            'overtime_id' => $overtime->id,
            'employee_id' => $overtime->employee_id,
            'employee_name' => $overtime->employee->name,
            'approver_id' => $approver->id,
            'approver_name' => $approver->name,
            'approver_role' => $approver->role,
            'date' => $overtime->date->format('Y-m-d'),
            'duration_minutes' => $overtime->duration_minutes,
            'overtime_pay' => $pay,
        ]);

        return [
            'success' => true,
            'message' => "Lembur disetujui. Upah Rp " . number_format($pay, 0, ',', '.') . " akan masuk payroll.",
            'data' => [
                'overtime_id' => $overtime->id,
                'overtime_pay' => $pay,
                'approved_by' => $approver->name,
            ],
        ];
    }

    /**
     * BUG-HRM-004 FIX: Reject overtime with security validation
     * 
     * @param User $rejector
     * @param OvertimeRequest $overtime
     * @param string|null $reason
     * @return array
     */
    public function reject(User $rejector, OvertimeRequest $overtime, ?string $reason = null): array
    {
        // Validate approval permission (same rules as approve)
        $validation = $this->canApprove($rejector, $overtime);

        if (!$validation['allowed']) {
            Log::warning('Overtime: Self-rejection attempt blocked', [
                'rejector_id' => $rejector->id,
                'rejector_name' => $rejector->name,
                'overtime_id' => $overtime->id,
                'employee_id' => $overtime->employee_id,
                'reason' => $validation['reason'],
            ]);

            return [
                'success' => false,
                'message' => $validation['reason'],
            ];
        }

        // Reject with audit trail
        $overtime->update([
            'status' => 'rejected',
            'approved_by' => $rejector->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        // Log rejection action
        Log::info('Overtime: Rejected', [
            'overtime_id' => $overtime->id,
            'employee_id' => $overtime->employee_id,
            'rejector_id' => $rejector->id,
            'rejector_name' => $rejector->name,
            'rejector_role' => $rejector->role,
            'reason' => $reason,
        ]);

        return [
            'success' => true,
            'message' => 'Pengajuan lembur ditolak.',
        ];
    }

    /**
     * Get eligible approvers for an overtime request
     * 
     * @param OvertimeRequest $overtime
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEligibleApprovers(OvertimeRequest $overtime)
    {
        // Get all admins and managers in same tenant, excluding the employee
        return User::where('tenant_id', $overtime->tenant_id)
            ->whereIn('role', ['admin', 'manager'])
            ->where('id', '!=', $overtime->employee->user_id) // Exclude employee themselves
            ->get();
    }

    /**
     * BUG-HRM-004 FIX: Check if this is a self-approval attempt
     * 
     * @param User $approver
     * @param OvertimeRequest $overtime
     * @return bool
     */
    protected function isSelfApproval(User $approver, OvertimeRequest $overtime): bool
    {
        // Direct self-approval: Approver IS the employee
        if ($overtime->employee->user_id && $approver->id === $overtime->employee->user_id) {
            return true;
        }

        // Indirect self-approval: Approver owns the employee record
        if ($approver->employee && $approver->employee->id === $overtime->employee_id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user has approval role
     * 
     * @param User $user
     * @return bool
     */
    protected function hasApprovalRole(User $user): bool
    {
        $allowedRoles = ['admin', 'manager', 'super_admin'];

        return in_array($user->role, $allowedRoles) || $user->is_super_admin;
    }

    /**
     * Get approval audit log for overtime request
     * 
     * @param OvertimeRequest $overtime
     * @return array
     */
    public function getApprovalAudit(OvertimeRequest $overtime): array
    {
        return [
            'overtime_id' => $overtime->id,
            'employee' => $overtime->employee->name ?? 'N/A',
            'date' => $overtime->date->format('Y-m-d'),
            'duration' => $overtime->durationLabel(),
            'status' => $overtime->status,
            'approved_by' => $overtime->approver->name ?? 'N/A',
            'approved_by_role' => $overtime->approver->role ?? 'N/A',
            'approved_at' => $overtime->approved_at?->format('Y-m-d H:i:s'),
            'rejection_reason' => $overtime->rejection_reason,
            'overtime_pay' => $overtime->overtime_pay,
        ];
    }
}
