<?php

namespace App\Http\Controllers;

use App\Services\Security\GdprComplianceService;
use App\Models\GdprDataExport;
use App\Models\GdprDeletionRequest;
use App\Models\GdprConsent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GdprController extends Controller
{
    protected $gdprService;

    public function __construct(GdprComplianceService $gdprService)
    {
        $this->gdprService = $gdprService;
    }

    /**
     * Show GDPR dashboard
     */
    public function index()
    {
        $userId = auth()->id();

        $exports = GdprDataExport::where('user_id', $userId)
            ->latest()
            ->take(10)
            ->get();

        $deletions = GdprDeletionRequest::where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        $consents = GdprConsent::where('user_id', $userId)
            ->latest()
            ->get();

        return view('gdpr.dashboard', compact('exports', 'deletions', 'consents'));
    }

    /**
     * Request data export
     */
    public function requestExport(Request $request)
    {
        $validated = $request->validate([
            'export_type' => 'required|in:personal_data,all_data,specific_module',
            'modules' => 'nullable|array',
        ]);

        $export = $this->gdprService->createDataExportRequest(
            auth()->id(),
            $validated['export_type'],
            $validated['modules'] ?? []
        );

        return back()->with('success', 'Data export request submitted. You will be notified when ready.');
    }

    /**
     * Download exported data
     */
    public function downloadExport($id)
    {
        $export = GdprDataExport::where('user_id', auth()->id())
            ->where('id', $id)
            ->where('status', 'completed')
            ->firstOrFail();

        if (now()->gt($export->expires_at)) {
            return back()->with('error', 'Export link has expired. Please request a new export.');
        }

        if (!Storage::disk('local')->exists($export->file_path)) {
            return back()->with('error', 'Export file not found.');
        }

        return Storage::disk('local')->download(
            $export->file_path,
            "my_data_export_{$export->id}.json"
        );
    }

    /**
     * Request data deletion (Right to be Forgotten)
     */
    public function requestDeletion(Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        $deletion = $this->gdprService->createDeletionRequest(
            auth()->id(),
            $validated['reason']
        );

        return back()->with('success', 'Data deletion request submitted. An administrator will review your request.');
    }

    /**
     * Give consent
     */
    public function giveConsent(Request $request)
    {
        $validated = $request->validate([
            'consent_type' => 'required|string',
        ]);

        $consent = $this->gdprService->recordConsent(
            auth()->id(),
            $validated['consent_type'],
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Consent recorded successfully.');
    }

    /**
     * Withdraw consent
     */
    public function withdrawConsent($consentType)
    {
        $this->gdprService->revokeConsent(auth()->id(), $consentType);

        return back()->with('success', 'Consent withdrawn successfully.');
    }

    /**
     * Check consent status
     */
    public function checkConsent($consentType)
    {
        $hasConsent = $this->gdprService->hasConsent(auth()->id(), $consentType);

        return response()->json([
            'has_consent' => $hasConsent,
            'consent_type' => $consentType,
        ]);
    }

    /**
     * Admin: Approve deletion request
     */
    public function approveDeletion(Request $request, $id)
    {
        $deletion = GdprDeletionRequest::findOrFail($id);

        $this->gdprService->processDeletionRequest($deletion, auth()->id());

        return back()->with('success', 'Deletion request processed successfully.');
    }
}
