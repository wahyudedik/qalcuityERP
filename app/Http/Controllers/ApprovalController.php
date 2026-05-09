<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\ErpNotification;
use App\Models\User;
use App\Notifications\ApprovalRequestNotification;
use App\Notifications\ApprovalResponseNotification;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $pending = ApprovalRequest::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->with(['requester', 'workflow'])
            ->latest()
            ->get();

        $history = ApprovalRequest::where('tenant_id', $tenantId)
            ->whereIn('status', ['approved', 'rejected'])
            ->with(['requester', 'approver', 'workflow'])
            ->latest()
            ->take(50)
            ->get();

        $workflows = ApprovalWorkflow::where('tenant_id', $tenantId)->where('is_active', true)->get();

        return view('approvals.index', compact('pending', 'history', 'workflows'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'workflow_id' => 'required|exists:approval_workflows,id',
            'amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $workflow = ApprovalWorkflow::where('tenant_id', $tenantId)->findOrFail($data['workflow_id']);

        $approval = ApprovalRequest::create([
            'tenant_id' => $tenantId,
            'workflow_id' => $workflow->id,
            'requested_by' => auth()->id(),
            'status' => 'pending',
            'amount' => $data['amount'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        // Notifikasi ke semua admin & manager
        $approvers = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['admin', 'manager'])
            ->where('id', '!=', auth()->id())
            ->get();

        foreach ($approvers as $approver) {
            $approver->notify(new ApprovalRequestNotification($approval->load('workflow', 'requester')));

            ErpNotification::create([
                'tenant_id' => $tenantId,
                'user_id' => $approver->id,
                'type' => 'approval_request',
                'title' => '📋 Permintaan Persetujuan Baru',
                'body' => auth()->user()->name." meminta persetujuan untuk: {$workflow->name}",
                'data' => ['approval_id' => $approval->id],
            ]);
        }

        return back()->with('success', 'Permintaan persetujuan berhasil dikirim.');
    }

    public function approve(Request $request, ApprovalRequest $approval)
    {
        $this->authorize_tenant($approval);
        $approval->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'notes' => $request->notes,
            'responded_at' => now(),
        ]);

        // Update the subject model's approval_status
        if ($approval->model_type && $approval->model_id) {
            $approval->model_type::find($approval->model_id)?->update(['approval_status' => 'approved']);
        }

        ActivityLog::record('approval_approved', "Disetujui: {$approval->workflow?->name}", $approval);

        // Notifikasi ke requester
        if ($approval->requester) {
            $approval->requester->notify(new ApprovalResponseNotification($approval->load('workflow', 'approver')));

            ErpNotification::create([
                'tenant_id' => $approval->tenant_id,
                'user_id' => $approval->requested_by,
                'type' => 'approval_approved',
                'title' => '✅ Permintaan Disetujui',
                'body' => "Permintaan \"{$approval->workflow?->name}\" Anda telah disetujui oleh ".auth()->user()->name.'.',
                'data' => ['approval_id' => $approval->id],
            ]);
        }

        return back()->with('success', 'Permintaan disetujui.');
    }

    public function reject(Request $request, ApprovalRequest $approval)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $this->authorize_tenant($approval);

        $approval->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'rejection_reason' => $request->reason,
            'responded_at' => now(),
        ]);

        if ($approval->model_type && $approval->model_id) {
            $approval->model_type::find($approval->model_id)?->update(['approval_status' => 'rejected']);
        }

        ActivityLog::record('approval_rejected', "Ditolak: {$approval->workflow?->name}", $approval);

        // Notifikasi ke requester
        if ($approval->requester) {
            $approval->requester->notify(new ApprovalResponseNotification($approval->load('workflow', 'approver')));

            ErpNotification::create([
                'tenant_id' => $approval->tenant_id,
                'user_id' => $approval->requested_by,
                'type' => 'approval_rejected',
                'title' => '❌ Permintaan Ditolak',
                'body' => "Permintaan \"{$approval->workflow?->name}\" Anda ditolak. Alasan: {$request->reason}",
                'data' => ['approval_id' => $approval->id],
            ]);
        }

        return back()->with('success', 'Permintaan ditolak.');
    }

    // ─── Workflow Builder ─────────────────────────────────────────────────────

    public function workflowIndex()
    {
        $tenantId = auth()->user()->tenant_id;
        $workflows = ApprovalWorkflow::where('tenant_id', $tenantId)->latest()->get();

        return view('approvals.workflows', compact('workflows'));
    }

    public function workflowStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'model_type' => 'nullable|string|max:100',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'approver_roles' => 'required|array|min:1',
            'approver_roles.*' => 'in:admin,manager,staff,kasir,gudang',
        ]);

        ApprovalWorkflow::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $data['name'],
            'model_type' => $data['model_type'] ?? null,
            'min_amount' => $data['min_amount'] ?? 0,
            'max_amount' => $data['max_amount'] ?? null,
            'approver_roles' => $data['approver_roles'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Workflow persetujuan berhasil dibuat.');
    }

    public function workflowUpdate(Request $request, ApprovalWorkflow $workflow)
    {
        abort_if($workflow->tenant_id !== auth()->user()->tenant_id, 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'model_type' => 'nullable|string|max:100',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'approver_roles' => 'required|array|min:1',
            'approver_roles.*' => 'in:admin,manager,staff,kasir,gudang',
            'is_active' => 'boolean',
        ]);

        $workflow->update([
            'name' => $data['name'],
            'model_type' => $data['model_type'] ?? null,
            'min_amount' => $data['min_amount'] ?? 0,
            'max_amount' => $data['max_amount'] ?? null,
            'approver_roles' => $data['approver_roles'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Workflow berhasil diperbarui.');
    }

    public function workflowDestroy(ApprovalWorkflow $workflow)
    {
        abort_if($workflow->tenant_id !== auth()->user()->tenant_id, 403);
        $workflow->delete();

        return back()->with('success', 'Workflow dihapus.');
    }

    private function authorize_tenant(ApprovalRequest $approval): void
    {
        abort_if($approval->tenant_id !== auth()->user()->tenant_id, 403);
    }
}
