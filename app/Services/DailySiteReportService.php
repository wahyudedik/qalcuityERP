<?php

namespace App\Services;

use App\Models\DailySiteReport;
use App\Models\Project;
use App\Models\SiteLaborLog;
use Illuminate\Http\UploadedFile;

/**
 * Daily Site Report Service untuk Konstruksi
 */
class DailySiteReportService
{
    /**
     * Create daily site report with photos
     */
    public function createReport(array $data, int $tenantId): DailySiteReport
    {
        $report = DailySiteReport::create([
            'tenant_id' => $tenantId,
            'project_id' => $data['project_id'],
            'report_date' => $data['report_date'] ?? now(),
            'reported_by' => auth()->id(),
            'weather_condition' => $data['weather_condition'] ?? null,
            'temperature' => $data['temperature'] ?? null,
            'work_performed' => $data['work_performed'] ?? null,
            'manpower_count' => $data['manpower_count'] ?? 0,
            'equipment_used' => $data['equipment_used'] ?? null,
            'materials_received' => $data['materials_received'] ?? null,
            'issues_encountered' => $data['issues_encountered'] ?? null,
            'safety_incidents' => $data['safety_incidents'] ?? 0,
            'progress_percentage' => $data['progress_percentage'] ?? 0,
            'photos' => $this->handlePhotoUpload($data['photos'] ?? []),
            'status' => 'draft',
            'notes' => $data['notes'] ?? null,
        ]);

        // Create labor logs if provided
        if (! empty($data['labor_logs'])) {
            foreach ($data['labor_logs'] as $laborData) {
                SiteLaborLog::create([
                    'tenant_id' => $tenantId,
                    'daily_report_id' => $report->id,
                    'worker_name' => $laborData['worker_name'],
                    'worker_type' => $laborData['worker_type'] ?? 'unskilled',
                    'trade' => $laborData['trade'] ?? null,
                    'hours_worked' => $laborData['hours_worked'] ?? 8,
                    'hourly_rate' => $laborData['hourly_rate'] ?? 0,
                    'total_cost' => ($laborData['hours_worked'] ?? 8) * ($laborData['hourly_rate'] ?? 0),
                    'attendance_status' => $laborData['attendance_status'] ?? 'present',
                ]);
            }
        }

        return $report->load(['laborLogs', 'reportedBy']);
    }

    /**
     * Submit report for approval
     */
    public function submitReport(int $reportId, int $tenantId): DailySiteReport
    {
        $report = DailySiteReport::where('id', $reportId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        if (! $report->isComplete()) {
            throw new \Exception('Report is incomplete. Please fill all required fields.');
        }

        $report->update(['status' => 'submitted']);

        return $report;
    }

    /**
     * Approve daily site report
     */
    public function approveReport(int $reportId, int $tenantId): DailySiteReport
    {
        $report = DailySiteReport::where('id', $reportId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $report->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Update project progress based on latest report
        $this->updateProjectProgress($report->project_id);

        return $report->load('approvedBy');
    }

    /**
     * Get reports summary for a project
     */
    public function getReportsSummary(int $projectId, int $tenantId, ?string $period = 'month'): array
    {
        $query = DailySiteReport::where('project_id', $projectId)
            ->where('tenant_id', $tenantId)
            ->where('status', 'approved');

        // Apply period filter
        if ($period === 'week') {
            $query->whereBetween('report_date', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereMonth('report_date', now()->month)
                ->whereYear('report_date', now()->year);
        }

        $reports = $query->orderByDesc('report_date')->get();

        return [
            'total_reports' => $reports->count(),
            'avg_progress' => $reports->avg('progress_percentage') ?? 0,
            'total_manpower' => $reports->sum('manpower_count'),
            'total_labor_cost' => $reports->flatMap->laborLogs->sum('total_cost'),
            'safety_incidents' => $reports->sum('safety_incidents'),
            'weather_summary' => $reports->groupBy('weather_condition')
                ->map->count()
                ->toArray(),
            'recent_reports' => $reports->take(10)->map(fn ($r) => [
                'id' => $r->id,
                'date' => $r->report_date->format('Y-m-d'),
                'progress' => $r->progress_percentage,
                'manpower' => $r->manpower_count,
                'weather' => $r->weather_condition,
                'has_photos' => ! empty($r->photos),
                'photo_count' => count($r->photos ?? []),
            ]),
        ];
    }

    /**
     * Get labor cost analysis
     */
    public function getLaborCostAnalysis(int $projectId, int $tenantId): array
    {
        $laborLogs = SiteLaborLog::whereHas('dailyReport', function ($query) use ($projectId, $tenantId) {
            $query->where('project_id', $projectId)
                ->where('tenant_id', $tenantId)
                ->where('status', 'approved');
        })
            ->with('dailyReport')
            ->get();

        return [
            'total_workers' => $laborLogs->unique('worker_name')->count(),
            'total_hours' => $laborLogs->sum('hours_worked'),
            'total_cost' => $laborLogs->sum('total_cost'),
            'by_trade' => $laborLogs->groupBy('trade')
                ->map(fn ($logs) => [
                    'count' => $logs->count(),
                    'total_hours' => $logs->sum('hours_worked'),
                    'total_cost' => $logs->sum('total_cost'),
                    'avg_hourly_rate' => $logs->avg('hourly_rate'),
                ])
                ->toArray(),
            'by_type' => $laborLogs->groupBy('worker_type')
                ->map(fn ($logs) => [
                    'count' => $logs->count(),
                    'total_cost' => $logs->sum('total_cost'),
                ])
                ->toArray(),
        ];
    }

    /**
     * Handle photo uploads
     */
    private function handlePhotoUpload(array $photos): array
    {
        $uploadedPaths = [];

        foreach ($photos as $photo) {
            if ($photo instanceof UploadedFile) {
                $path = $photo->store('site-reports', 'public');
                $uploadedPaths[] = $path;
            }
        }

        return $uploadedPaths;
    }

    /**
     * Update project progress based on latest approved report
     */
    private function updateProjectProgress(int $projectId): void
    {
        $latestReport = DailySiteReport::where('project_id', $projectId)
            ->where('status', 'approved')
            ->orderByDesc('report_date')
            ->first();

        if ($latestReport) {
            $project = Project::find($projectId);
            if ($project) {
                $project->update(['progress' => $latestReport->progress_percentage]);
            }
        }
    }
}
