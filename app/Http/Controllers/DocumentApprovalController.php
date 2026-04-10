<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentApprovalWorkflow;
use App\Services\DocumentApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentApprovalController extends Controller
{
    protected DocumentApprovalService $approvalService;

    public function __construct(DocumentApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Display approval workflow page
     */
    public function index(Document $document)
    {
        $this->authorize('view', $document);

        $history = $this->approvalService->getApprovalHistory($document);

        return view('documents.approval-workflow', compact('document', 'history'));
    }

    /**
     * Submit document for approval
     */
    public function submit(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'workflow_id' => 'nullable|exists:document_approval_workflows,id',
        ]);

        $workflow = null;
        if ($request->filled('workflow_id')) {
            $workflow = DocumentApprovalWorkflow::findOrFail($request->workflow_id);
        }

        $this->approvalService->submitForApproval($document, $workflow);

        return redirect()->back()
            ->with('success', 'Document submitted for approval');
    }

    /**
     * Approve document at step
     */
    public function approve(Request $request, Document $document, int $stepNumber)
    {
        $this->authorize('approve', $document);

        $validated = $request->validate([
            'comments' => 'nullable|string|max:500',
        ]);

        $this->approvalService->approveStep(
            $document,
            $stepNumber,
            $validated['comments'] ?? ''
        );

        return redirect()->back()
            ->with('success', "Step {$stepNumber} approved");
    }

    /**
     * Reject document at step
     */
    public function reject(Request $request, Document $document, int $stepNumber)
    {
        $this->authorize('approve', $document);

        $validated = $request->validate([
            'comments' => 'required|string|max:500',
        ]);

        $this->approvalService->rejectStep(
            $document,
            $stepNumber,
            $validated['comments']
        );

        return redirect()->back()
            ->with('error', "Step {$stepNumber} rejected");
    }

    /**
     * Get pending approvals for current user
     */
    public function pendingApprovals(Request $request)
    {
        $approvals = $this->approvalService->getPendingApprovalsForUser(Auth::id());

        if ($request->expectsJson()) {
            return response()->json($approvals);
        }

        return view('documents.pending-approvals', compact('approvals'));
    }

    /**
     * Get approval history via API
     */
    public function getHistory(Document $document)
    {
        $this->authorize('view', $document);

        $history = $this->approvalService->getApprovalHistory($document);

        return response()->json($history);
    }

    /**
     * Display workflows list
     */
    public function workflows()
    {
        $workflows = DocumentApprovalWorkflow::where('tenant_id', Auth::user()->tenant_id)
            ->withCount('approvalRequests')
            ->latest()
            ->paginate(20);

        return view('documents.workflows.index', compact('workflows'));
    }

    /**
     * Store new workflow
     */
    public function storeWorkflow(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_type' => 'nullable|string|max:100',
            'approval_steps' => 'required|array|min:1',
            'approval_steps.*.user_id' => 'nullable|exists:users,id',
            'approval_steps.*.role_id' => 'nullable|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $workflow = $this->approvalService->createWorkflow($validated);

        return redirect()->route('documents.workflows')
            ->with('success', 'Approval workflow created successfully');
    }

    /**
     * Update workflow
     */
    public function updateWorkflow(Request $request, DocumentApprovalWorkflow $workflow)
    {
        $this->authorize('update', $workflow);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_type' => 'nullable|string|max:100',
            'approval_steps' => 'required|array|min:1',
            'approval_steps.*.user_id' => 'nullable|exists:users,id',
            'approval_steps.*.role_id' => 'nullable|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $this->approvalService->updateWorkflow($workflow, $validated);

        return redirect()->back()
            ->with('success', 'Approval workflow updated successfully');
    }

    /**
     * Delete workflow
     */
    public function destroyWorkflow(DocumentApprovalWorkflow $workflow)
    {
        $this->authorize('delete', $workflow);

        $workflow->delete();

        return redirect()->route('documents.workflows')
            ->with('success', 'Approval workflow deleted');
    }

    /**
     * Get workflow statistics
     */
    public function workflowStatistics(DocumentApprovalWorkflow $workflow)
    {
        $this->authorize('view', $workflow);

        $statistics = $this->approvalService->getWorkflowStatistics($workflow);

        return response()->json($statistics);
    }
}
