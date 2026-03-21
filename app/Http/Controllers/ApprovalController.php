<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\ActivityLog;
use App\Models\ErpNotification;
use App\Notifications\ApprovalRequestNotification;
use App\Notifications\ApprovalResponseNotification;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $pending  = ApprovalRequest::where('tenant_id', $tenantId)
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

        return view('approvals.index', compact('pending', 'history'));
    }

    public function approve(Request $request, ApprovalRequest $approval)
    {
        $this->authorize_tenant($approval);
        $approval->update([
            'status'       => 'approved',
            'approved_by'  => auth()->id(),
            'notes'        => $request->notes,
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
                'user_id'   => $approval->requested_by,
                'type'      => 'approval_approved',
                'title'     => '✅ Permintaan Disetujui',
                'body'      => "Permintaan \"{$approval->workflow?->name}\" Anda telah disetujui oleh " . auth()->user()->name . ".",
                'data'      => ['approval_id' => $approval->id],
            ]);
        }

        return back()->with('success', 'Permintaan disetujui.');
    }

    public function reject(Request $request, ApprovalRequest $approval)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $this->authorize_tenant($approval);

        $approval->update([
            'status'           => 'rejected',
            'approved_by'      => auth()->id(),
            'rejection_reason' => $request->reason,
            'responded_at'     => now(),
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
                'user_id'   => $approval->requested_by,
                'type'      => 'approval_rejected',
                'title'     => '❌ Permintaan Ditolak',
                'body'      => "Permintaan \"{$approval->workflow?->name}\" Anda ditolak. Alasan: {$request->reason}",
                'data'      => ['approval_id' => $approval->id],
            ]);
        }

        return back()->with('success', 'Permintaan ditolak.');
    }

    private function authorize_tenant(ApprovalRequest $approval): void
    {
        abort_if($approval->tenant_id !== auth()->user()->tenant_id, 403);
    }
}
