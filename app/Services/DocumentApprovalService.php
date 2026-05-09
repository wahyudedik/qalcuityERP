<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentApprovalRequest;
use App\Models\DocumentApprovalWorkflow;
use App\Models\User;
use App\Notifications\DocumentApprovalNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Document Approval Service
 *
 * Manages document approval workflows, approval requests, and notifications.
 */
class DocumentApprovalService
{
    /**
     * Submit document for approval
     */
    public function submitForApproval(Document $document, ?DocumentApprovalWorkflow $workflow = null): bool
    {
        return DB::transaction(function () use ($document, $workflow) {
            // Determine workflow
            $workflow = $workflow ?? $this->getApplicableWorkflow($document);

            if (! $workflow) {
                throw new \Exception('No applicable approval workflow found');
            }

            // Update document status
            $document->update([
                'status' => 'pending_approval',
            ]);

            // Create approval requests for each step
            foreach ($workflow->approval_steps as $stepNumber => $step) {
                DocumentApprovalRequest::create([
                    'document_id' => $document->id,
                    'workflow_id' => $workflow->id,
                    'step_number' => $stepNumber + 1,
                    'approver_id' => $step['user_id'] ?? null,
                    'approver_role' => $step['role_id'] ?? null,
                    'status' => 'pending',
                ]);
            }

            // Notify first approver
            $this->notifyApprover($document, 1);

            return true;
        });
    }

    /**
     * Approve document at current step
     */
    public function approveStep(Document $document, int $stepNumber, string $comments = ''): bool
    {
        return DB::transaction(function () use ($document, $stepNumber, $comments) {
            $approvalRequest = DocumentApprovalRequest::where('document_id', $document->id)
                ->where('step_number', $stepNumber)
                ->where('status', 'pending')
                ->firstOrFail();

            $approvalRequest->approve(Auth::id(), $comments);

            // Check if all steps are approved
            $allApproved = $this->checkAllStepsApproved($document);

            if ($allApproved) {
                $document->approve(Auth::id(), 'Fully approved through workflow');

                // Notify document owner
                $this->notifyOwner($document, 'approved');
            } else {
                // Notify next approver
                $this->notifyApprover($document, $stepNumber + 1);
            }

            return true;
        });
    }

    /**
     * Reject document at current step
     */
    public function rejectStep(Document $document, int $stepNumber, string $comments): bool
    {
        return DB::transaction(function () use ($document, $stepNumber, $comments) {
            $approvalRequest = DocumentApprovalRequest::where('document_id', $document->id)
                ->where('step_number', $stepNumber)
                ->where('status', 'pending')
                ->firstOrFail();

            $approvalRequest->reject(Auth::id(), $comments);

            // Update document status
            $document->reject($comments);

            // Notify document owner
            $this->notifyOwner($document, 'rejected', $comments);

            return true;
        });
    }

    /**
     * Get pending approvals for a user
     */
    public function getPendingApprovalsForUser(int $userId, int $limit = 20): array
    {
        $approvals = DocumentApprovalRequest::with(['document', 'workflow'])
            ->pending()
            ->where(function ($query) use ($userId) {
                $query->where('approver_id', $userId)
                    ->orWhere('approver_role', function ($q) use ($userId) {
                        $user = User::find($userId);
                        $q->whereIn('id', $user?->roles()->pluck('id') ?? []);
                    });
            })
            ->latest()
            ->limit($limit)
            ->get();

        return [
            'total_pending' => DocumentApprovalRequest::pending()
                ->where('approver_id', $userId)
                ->count(),
            'approvals' => $approvals->map(function ($approval) {
                return [
                    'id' => $approval->id,
                    'document_title' => $approval->document->title,
                    'document_type' => $approval->document->category,
                    'step_number' => $approval->step_number,
                    'workflow_name' => $approval->workflow->name,
                    'submitted_at' => $approval->created_at->format('d M Y H:i'),
                    'priority' => $this->calculatePriority($approval),
                ];
            }),
        ];
    }

    /**
     * Get approval history for a document
     */
    public function getApprovalHistory(Document $document): array
    {
        $requests = DocumentApprovalRequest::with(['approver:id,name,email', 'workflow'])
            ->where('document_id', $document->id)
            ->orderBy('step_number')
            ->get();

        return [
            'document_status' => $document->status,
            'approved_by' => $document->approver?->name ?? null,
            'approved_at' => $document->approved_at?->format('d M Y H:i'),
            'approval_notes' => $document->approval_notes,
            'steps' => $requests->map(function ($request) {
                return [
                    'step_number' => $request->step_number,
                    'status' => $request->status,
                    'approver' => $request->approver?->name ?? ($request->approver_role ? 'Role-based' : 'Unassigned'),
                    'comments' => $request->comments,
                    'actioned_at' => $request->actioned_at?->format('d M Y H:i'),
                ];
            }),
        ];
    }

    /**
     * Create approval workflow
     */
    public function createWorkflow(array $data): DocumentApprovalWorkflow
    {
        return DocumentApprovalWorkflow::create([
            'tenant_id' => Auth::user()->tenant_id,
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'document_type' => $data['document_type'] ?? null,
            'approval_steps' => $data['approval_steps'],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update approval workflow
     */
    public function updateWorkflow(DocumentApprovalWorkflow $workflow, array $data): bool
    {
        return $workflow->update([
            'name' => $data['name'] ?? $workflow->name,
            'description' => $data['description'] ?? $workflow->description,
            'document_type' => $data['document_type'] ?? $workflow->document_type,
            'approval_steps' => $data['approval_steps'] ?? $workflow->approval_steps,
            'is_active' => $data['is_active'] ?? $workflow->is_active,
        ]);
    }

    /**
     * Get applicable workflow for document
     */
    protected function getApplicableWorkflow(Document $document): ?DocumentApprovalWorkflow
    {
        return DocumentApprovalWorkflow::active()
            ->forDocumentType($document->category ?? '')
            ->where('tenant_id', $document->tenant_id)
            ->first();
    }

    /**
     * Check if all approval steps are approved
     */
    protected function checkAllStepsApproved(Document $document): bool
    {
        $totalSteps = DocumentApprovalRequest::where('document_id', $document->id)->count();
        $approvedSteps = DocumentApprovalRequest::where('document_id', $document->id)
            ->approved()
            ->count();

        return $totalSteps > 0 && $totalSteps === $approvedSteps;
    }

    /**
     * Notify approver
     */
    protected function notifyApprover(Document $document, int $stepNumber): void
    {
        $approvalRequest = DocumentApprovalRequest::where('document_id', $document->id)
            ->where('step_number', $stepNumber)
            ->first();

        if ($approvalRequest && $approvalRequest->approver_id) {
            $approver = User::find($approvalRequest->approver_id);
            if ($approver) {
                $approver->notify(new DocumentApprovalNotification($document, 'pending_approval'));
            }
        }
    }

    /**
     * Notify document owner
     */
    protected function notifyOwner(Document $document, string $status, string $comments = ''): void
    {
        $owner = User::find($document->uploaded_by);
        if ($owner) {
            $owner->notify(new DocumentApprovalNotification($document, $status, $comments));
        }
    }

    /**
     * Calculate approval priority
     */
    protected function calculatePriority(DocumentApprovalRequest $approval): string
    {
        $daysWaiting = $approval->created_at->diffInDays(now());

        if ($daysWaiting > 7) {
            return 'urgent';
        }
        if ($daysWaiting > 3) {
            return 'high';
        }
        if ($daysWaiting > 1) {
            return 'medium';
        }

        return 'normal';
    }

    /**
     * Get workflow statistics
     */
    public function getWorkflowStatistics(DocumentApprovalWorkflow $workflow): array
    {
        $totalRequests = $workflow->approvalRequests()->count();
        $approvedRequests = $workflow->approvalRequests()->approved()->count();
        $rejectedRequests = $workflow->approvalRequests()->rejected()->count();
        $pendingRequests = $workflow->approvalRequests()->pending()->count();

        return [
            'total_requests' => $totalRequests,
            'approved' => $approvedRequests,
            'rejected' => $rejectedRequests,
            'pending' => $pendingRequests,
            'approval_rate' => $totalRequests > 0 ? round(($approvedRequests / $totalRequests) * 100, 2) : 0,
            'avg_approval_time' => $this->calculateAverageApprovalTime($workflow),
        ];
    }

    /**
     * Calculate average approval time
     */
    protected function calculateAverageApprovalTime(DocumentApprovalWorkflow $workflow): ?string
    {
        $completedRequests = DocumentApprovalRequest::where('workflow_id', $workflow->id)
            ->whereNotNull('actioned_at')
            ->get();

        if ($completedRequests->isEmpty()) {
            return null;
        }

        $totalHours = $completedRequests->sum(function ($request) {
            return $request->created_at->diffInHours($request->actioned_at);
        });

        $avgHours = $totalHours / $completedRequests->count();

        if ($avgHours < 24) {
            return round($avgHours, 1).' hours';
        }

        return round($avgHours / 24, 1).' days';
    }
}
