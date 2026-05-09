<?php

namespace App\Services;

use App\Models\DailySiteReport;
use App\Models\MaterialDelivery;
use App\Models\Project;
use App\Models\SubcontractorContract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

/**
 * PDF Export Service untuk Construction Reports
 */
class ConstructionPdfService
{
    /**
     * Generate PDF for daily site report
     */
    public function generateDailyReportPdf(int $reportId, int $tenantId): Response
    {
        $report = DailySiteReport::where('id', $reportId)
            ->where('tenant_id', $tenantId)
            ->with(['project', 'reportedBy', 'approvedBy', 'laborLogs'])
            ->firstOrFail();

        $pdf = Pdf::loadView('construction.pdf.daily-report', compact('report'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "daily-report-{$report->report_date->format('Y-m-d')}.pdf"
        );
    }

    /**
     * Generate PDF for subcontractor contract
     */
    public function generateContractPdf(int $contractId, int $tenantId): Response
    {
        $contract = SubcontractorContract::where('id', $contractId)
            ->where('tenant_id', $tenantId)
            ->with(['subcontractor', 'project', 'payments'])
            ->firstOrFail();

        $pdf = Pdf::loadView('construction.pdf.contract', compact('contract'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
            ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "contract-{$contract->contract_number}.pdf"
        );
    }

    /**
     * Generate PDF summary report for project
     */
    public function generateProjectSummaryPdf(int $projectId, int $tenantId): Response
    {
        $project = Project::where('id', $projectId)
            ->where('tenant_id', $tenantId)
            ->with(['tasks', 'rabItems'])
            ->firstOrFail();

        $reports = DailySiteReport::where('project_id', $projectId)
            ->where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->orderByDesc('report_date')
            ->get();

        $deliveries = MaterialDelivery::where('project_id', $projectId)
            ->where('tenant_id', $tenantId)
            ->get();

        $pdf = Pdf::loadView('construction.pdf.project-summary', compact('project', 'reports', 'deliveries'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
            ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "project-summary-{$project->number}.pdf"
        );
    }
}
