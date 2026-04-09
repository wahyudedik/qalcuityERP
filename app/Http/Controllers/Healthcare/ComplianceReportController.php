<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\ComplianceReport;
use Illuminate\Http\Request;

class ComplianceReportController extends Controller
{
    public function index(Request $request)
    {
        $query = ComplianceReport::with(['createdBy']);

        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->orderBy('report_date', 'desc')->paginate(20);

        $statistics = [
            'total' => ComplianceReport::count(),
            'draft' => ComplianceReport::where('status', 'draft')->count(),
            'completed' => ComplianceReport::where('status', 'completed')->count(),
            'pending_review' => ComplianceReport::where('status', 'pending_review')->count(),
        ];

        return view('healthcare.compliance-reports.index', compact('reports', 'statistics'));
    }

    public function create()
    {
        return view('healthcare.compliance-reports.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:hipaa,jci,iso,regulatory,internal',
            'report_date' => 'required|date',
            'reporting_period_start' => 'required|date',
            'reporting_period_end' => 'required|date|after_or_equal:reporting_period_start',
            'findings' => 'nullable|array',
            'recommendations' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['report_number'] = 'CR-' . now()->format('Ymd') . '-' . str_pad(ComplianceReport::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        $validated['status'] = 'draft';
        $validated['created_by'] = auth()->id();

        $report = ComplianceReport::create($validated);

        return redirect()->route('healthcare.compliance-reports.show', $report)
            ->with('success', 'Compliance report created');
    }

    public function show(ComplianceReport $report)
    {
        $report->load(['createdBy', 'reviewer']);
        return view('healthcare.compliance-reports.show', compact('report'));
    }

    public function submitForReview(ComplianceReport $report)
    {
        $report->update([
            'status' => 'pending_review',
            'submitted_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Report submitted for review']);
    }

    public function approve(Request $request, ComplianceReport $report)
    {
        $validated = $request->validate([
            'review_notes' => 'nullable|string',
        ]);

        $report->update([
            'status' => 'completed',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'review_notes' => $validated['review_notes'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Report approved']);
    }

    public function print(ComplianceReport $report)
    {
        $report->load(['createdBy', 'reviewer']);
        return view('healthcare.compliance-reports.print', compact('report'));
    }

    public function destroy(ComplianceReport $report)
    {
        $report->delete();
        return response()->json(['success' => true, 'message' => 'Report deleted']);
    }
}
