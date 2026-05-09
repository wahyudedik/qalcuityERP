<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * PoApprovalService - Enforce purchase order approval workflow
 *
 * BUG-PO-001 FIX: Prevent unauthorized purchase order posting
 *
 * Security Rules:
 * 1. PO above threshold requires approval before posting
 * 2. Only authorized roles can approve (manager, admin, etc.)
 * 3. Approval workflow is mandatory, cannot be bypassed
 * 4. Audit trail for all approval actions
 */
class PoApprovalService
{
    /**
     * BUG-PO-001 FIX: Check if PO requires approval before posting
     *
     * @return array ['requires_approval' => bool, 'reason' => string]
     */
    public function requiresApproval(PurchaseOrder $po): array
    {
        // Find applicable approval workflow
        $workflow = $this->getApplicableWorkflow($po);

        if (! $workflow) {
            return [
                'requires_approval' => false,
                'reason' => 'No approval workflow configured for this PO amount.',
            ];
        }

        return [
            'requires_approval' => true,
            'reason' => 'PO total (Rp '.number_format($po->total, 0, ',', '.').') exceeds threshold (Rp '.number_format($workflow->min_amount, 0, ',', '.').'). Requires approval from: '.implode(', ', $workflow->approver_roles),
            'workflow' => $workflow,
        ];
    }

    /**
     * BUG-PO-001 FIX: Validate if user can approve PO
     *
     * @return array ['allowed' => bool, 'reason' => string]
     */
    public function canApprove(User $user, PurchaseOrder $po): array
    {
        // Check if approval is required
        $approvalCheck = $this->requiresApproval($po);

        if (! $approvalCheck['requires_approval']) {
            return [
                'allowed' => true,
                'reason' => 'No approval required for this PO.',
            ];
        }

        $workflow = $approvalCheck['workflow'];

        // Check if user has approval role
        $userRoles = [$user->role];
        if ($user->is_super_admin ?? false) {
            $userRoles[] = 'super_admin';
        }

        $allowedRoles = $workflow->approver_roles;
        $hasRole = ! empty(array_intersect($userRoles, $allowedRoles));

        if (! $hasRole) {
            return [
                'allowed' => false,
                'reason' => 'Anda tidak memiliki role yang diperlukan untuk approve PO ini. Required roles: '.implode(', ', $allowedRoles),
            ];
        }

        // Cannot approve own PO
        if ($po->user_id === $user->id) {
            return [
                'allowed' => false,
                'reason' => 'Anda tidak dapat menyetujui PO yang Anda buat sendiri.',
            ];
        }

        // Check if already approved
        $existingApproval = ApprovalRequest::where('model_type', PurchaseOrder::class)
            ->where('model_id', $po->id)
            ->where('status', 'approved')
            ->first();

        if ($existingApproval) {
            return [
                'allowed' => false,
                'reason' => 'PO ini sudah disetujui sebelumnya.',
            ];
        }

        return [
            'allowed' => true,
            'reason' => 'Approval diperbolehkan.',
        ];
    }

    /**
     * BUG-PO-001 FIX: Create approval request for PO
     */
    public function createApprovalRequest(PurchaseOrder $po): ApprovalRequest
    {
        $workflow = $this->getApplicableWorkflow($po);

        if (! $workflow) {
            throw new \Exception('No approval workflow found for this PO');
        }

        // Check if approval request already exists
        $existing = ApprovalRequest::where('model_type', PurchaseOrder::class)
            ->where('model_id', $po->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return $existing;
        }

        $approvalRequest = ApprovalRequest::create([
            'tenant_id' => $po->tenant_id,
            'workflow_id' => $workflow->id,
            'requested_by' => $po->user_id,
            'model_type' => PurchaseOrder::class,
            'model_id' => $po->id,
            'status' => 'pending',
            'amount' => $po->total,
            'notes' => "Approval required for PO {$po->number} - Total: Rp ".number_format($po->total, 0, ',', '.'),
        ]);

        Log::info('PO: Approval request created', [
            'po_id' => $po->id,
            'po_number' => $po->number,
            'total' => $po->total,
            'workflow_id' => $workflow->id,
            'approval_request_id' => $approvalRequest->id,
        ]);

        return $approvalRequest;
    }

    /**
     * BUG-PO-001 FIX: Approve PO with validation
     */
    public function approvePo(User $approver, PurchaseOrder $po, ?string $notes = null): array
    {
        // Validate approval permission
        $validation = $this->canApprove($approver, $po);

        if (! $validation['allowed']) {
            Log::warning('PO: Self-approval or unauthorized attempt blocked', [
                'approver_id' => $approver->id,
                'approver_name' => $approver->name,
                'po_id' => $po->id,
                'po_number' => $po->number,
                'reason' => $validation['reason'],
            ]);

            return [
                'success' => false,
                'message' => $validation['reason'],
            ];
        }

        // Find pending approval request
        $approvalRequest = ApprovalRequest::where('model_type', PurchaseOrder::class)
            ->where('model_id', $po->id)
            ->where('status', 'pending')
            ->first();

        if (! $approvalRequest) {
            // Create one if doesn't exist
            $approvalRequest = $this->createApprovalRequest($po);
        }

        // Approve
        $approvalRequest->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'notes' => $notes ?? $approvalRequest->notes,
        ]);

        Log::info('PO: Approved', [
            'po_id' => $po->id,
            'po_number' => $po->number,
            'approver_id' => $approver->id,
            'approver_name' => $approver->name,
            'approval_request_id' => $approvalRequest->id,
        ]);

        return [
            'success' => true,
            'message' => "PO {$po->number} telah disetujui. Sekarang bisa diposting.",
            'data' => [
                'approval_request_id' => $approvalRequest->id,
                'approved_by' => $approver->name,
                'approved_at' => now()->format('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * BUG-PO-001 FIX: Reject PO
     */
    public function rejectPo(User $rejector, PurchaseOrder $po, string $reason): array
    {
        $validation = $this->canApprove($rejector, $po);

        if (! $validation['allowed']) {
            return [
                'success' => false,
                'message' => $validation['reason'],
            ];
        }

        $approvalRequest = ApprovalRequest::where('model_type', PurchaseOrder::class)
            ->where('model_id', $po->id)
            ->where('status', 'pending')
            ->first();

        if (! $approvalRequest) {
            return [
                'success' => false,
                'message' => 'Tidak ada approval request pending untuk PO ini.',
            ];
        }

        $approvalRequest->update([
            'status' => 'rejected',
            'approved_by' => $rejector->id,
            'approved_at' => now(),
            'notes' => "Ditolak: {$reason}",
        ]);

        Log::warning('PO: Rejected', [
            'po_id' => $po->id,
            'po_number' => $po->number,
            'rejector_id' => $rejector->id,
            'reason' => $reason,
        ]);

        return [
            'success' => true,
            'message' => "PO {$po->number} ditolak. Alasan: {$reason}",
        ];
    }

    /**
     * BUG-PO-001 FIX: Check if PO can be posted
     */
    public function canPost(PurchaseOrder $po): array
    {
        $approvalCheck = $this->requiresApproval($po);

        if (! $approvalCheck['requires_approval']) {
            return [
                'can_post' => true,
                'reason' => 'No approval required.',
            ];
        }

        // Check if approved
        $approval = ApprovalRequest::where('model_type', PurchaseOrder::class)
            ->where('model_id', $po->id)
            ->where('status', 'approved')
            ->first();

        if (! $approval) {
            return [
                'can_post' => false,
                'reason' => "PO harus disetujui terlebih dahulu. {$approvalCheck['reason']}",
            ];
        }

        return [
            'can_post' => true,
            'reason' => 'PO telah disetujui dan siap diposting.',
            'approval' => $approval,
        ];
    }

    /**
     * Get applicable approval workflow for PO
     */
    protected function getApplicableWorkflow(PurchaseOrder $po): ?ApprovalWorkflow
    {
        return ApprovalWorkflow::where('tenant_id', $po->tenant_id)
            ->where('model_type', PurchaseOrder::class)
            ->where('is_active', true)
            ->where('min_amount', '<=', $po->total)
            ->where(function ($query) use ($po) {
                $query->whereNull('max_amount')
                    ->orWhere('max_amount', '>=', $po->total);
            })
            ->orderBy('min_amount', 'desc')
            ->first();
    }

    /**
     * Get approval history for PO
     */
    public function getApprovalHistory(PurchaseOrder $po): array
    {
        $approvals = ApprovalRequest::where('model_type', PurchaseOrder::class)
            ->where('model_id', $po->id)
            ->with(['requester', 'approver', 'workflow'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $approvals->map(function ($approval) {
            return [
                'id' => $approval->id,
                'status' => $approval->status,
                'requested_by' => $approval->requester->name ?? 'N/A',
                'approved_by' => $approval->approver->name ?? 'N/A',
                'approved_at' => $approval->approved_at?->format('Y-m-d H:i:s'),
                'amount' => $approval->amount,
                'notes' => $approval->notes,
                'workflow_name' => $approval->workflow->name ?? 'N/A',
            ];
        })->toArray();
    }
}
