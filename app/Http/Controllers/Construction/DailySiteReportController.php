<?php

namespace App\Http\Controllers\Construction;

use App\Http\Controllers\Controller;
use App\Models\DailySiteReport;
use App\Models\Project;
use App\Models\User;
use App\Notifications\Construction\DailyReportApprovedNotification;
use App\Notifications\Construction\DailyReportSubmittedNotification;
use App\Services\ConstructionPdfService;
use App\Services\DailySiteReportService;
use Illuminate\Http\Request;

class DailySiteReportController extends Controller
{
    protected $reportService;

    protected $pdfService;

    public function __construct(DailySiteReportService $reportService, ConstructionPdfService $pdfService)
    {
        $this->reportService = $reportService;
        $this->pdfService = $pdfService;
    }

    /**
     * Display daily site reports dashboard
     */
    public function index(Request $request)
    {
        $projects = Project::where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('status', ['active', 'planning'])
            ->orderBy('name')
            ->get();

        $selectedProject = $request->input('project_id');
        $period = $request->input('period', 'month');

        $summary = null;
        $laborAnalysis = null;
        $recentReports = [];

        if ($selectedProject) {
            $summary = $this->reportService->getReportsSummary($selectedProject, auth()->user()->tenant_id, $period);
            $laborAnalysis = $this->reportService->getLaborCostAnalysis($selectedProject, auth()->user()->tenant_id);

            $recentReports = DailySiteReport::where('project_id', $selectedProject)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->orderByDesc('report_date')
                ->with(['reportedBy', 'approvedBy'])
                ->paginate(20);
        }

        return view('construction.reports.index', compact(
            'projects',
            'selectedProject',
            'period',
            'summary',
            'laborAnalysis',
            'recentReports'
        ));
    }

    /**
     * Show create report form
     */
    public function create()
    {
        $projects = Project::where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('status', ['active'])
            ->orderBy('name')
            ->get();

        return view('construction.reports.create', compact('projects'));
    }

    /**
     * Store new daily site report
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'report_date' => 'required|date',
            'weather_condition' => 'nullable|string',
            'temperature' => 'nullable|numeric',
            'work_performed' => 'required|string',
            'manpower_count' => 'required|integer|min:0',
            'equipment_used' => 'nullable|string',
            'materials_received' => 'nullable|string',
            'issues_encountered' => 'nullable|string',
            'safety_incidents' => 'nullable|integer|min:0',
            'progress_percentage' => 'required|numeric|min:0|max:100',
            'photos.*' => 'nullable|image|max:5120', // 5MB max
            'notes' => 'nullable|string',
            'labor_logs' => 'nullable|array',
        ]);

        $report = $this->reportService->createReport($validated, auth()->user()->tenant_id);

        return redirect()->route('construction.reports.show', $report)
            ->with('success', 'Daily site report created successfully.');
    }

    /**
     * Display specific report
     */
    public function show(DailySiteReport $report)
    {
        $this->authorize('view', $report);

        $report->load(['laborLogs', 'reportedBy', 'approvedBy']);

        return view('construction.reports.show', compact('report'));
    }

    /**
     * Submit report for approval
     */
    public function submit(DailySiteReport $report)
    {
        $this->authorize('update', $report);

        try {
            $this->reportService->submitReport($report->id, auth()->user()->tenant_id);

            // Send notification to approvers (project managers, admins)
            $approvers = User::where('tenant_id', auth()->user()->tenant_id)
                ->whereIn('role', ['admin', 'manager'])
                ->get();

            foreach ($approvers as $approver) {
                $approver->notify(new DailyReportSubmittedNotification($report, $approver));
            }

            return back()->with('success', 'Report submitted for approval. Notifications sent.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve daily site report
     */
    public function approve(DailySiteReport $report)
    {
        $this->authorize('approve', $report);

        $this->reportService->approveReport($report->id, auth()->user()->tenant_id);

        // Send notification to reporter
        $report->reportedBy->notify(new DailyReportApprovedNotification($report));

        return back()->with('success', 'Report approved successfully. Notification sent.');
    }

    /**
     * Get labor cost analysis API
     */
    public function laborAnalysis(int $projectId)
    {
        $analysis = $this->reportService->getLaborCostAnalysis($projectId, auth()->user()->tenant_id);

        return response()->json($analysis);
    }

    /**
     * Export report to PDF
     */
    public function exportPdf(DailySiteReport $report)
    {
        $this->authorize('view', $report);

        return $this->pdfService->generateDailyReportPdf($report->id, auth()->user()->tenant_id);
    }
}
