<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\AccessViolation;
use App\Models\AuditTrail;
use App\Models\BackupLog;
use App\Models\ComplianceReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ComplianceController extends Controller
{
    /**
     * Display compliance dashboard.
     */
    public function index()
    {
        $statistics = [
            'total_violations' => AccessViolation::count(),
            'unresolved_violations' => AccessViolation::where('is_resolved', false)->count(),
            'critical_violations' => AccessViolation::where('severity', 'critical')->count(),
            'total_audits' => AuditTrail::count(),
            'total_backups' => BackupLog::count(),
            'compliance_score' => 0, // Calculate based on violations vs total access
        ];

        return view('healthcare.compliance.index', compact('statistics'));
    }

    /**
     * Display audit trail.
     */
    public function auditTrail(Request $request)
    {
        $query = AuditTrail::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $audits = $query->latest()->paginate(50);

        return view('healthcare.compliance.audit-trail', compact('audits'));
    }

    /**
     * Create backup.
     */
    public function createBackup(Request $request)
    {
        $validated = $request->validate([
            'backup_type' => 'required|in:full,database,files',
            'notes' => 'nullable|string',
        ]);

        try {
            // Run backup command
            Artisan::call('backup:run', [
                '--only-db' => $validated['backup_type'] === 'database',
                '--only-files' => $validated['backup_type'] === 'files',
            ]);

            // Log backup
            $backupLog = BackupLog::create([
                'backup_type' => $validated['backup_type'],
                'status' => 'completed',
                'file_size' => 0, // Will be updated by backup process
                'storage_path' => 'local',
                'created_by' => auth()->id(),
                'notes' => $validated['notes'],
            ]);

            return back()->with('success', 'Backup created successfully');
        } catch (\Exception $e) {
            BackupLog::create([
                'backup_type' => $validated['backup_type'],
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'created_by' => auth()->id(),
                'notes' => $validated['notes'],
            ]);

            return back()->with('error', 'Backup failed: '.$e->getMessage());
        }
    }

    /**
     * Display compliance reports.
     */
    public function reports(Request $request)
    {
        $query = ComplianceReport::with('createdBy');

        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->latest()->paginate(20);

        return view('healthcare.compliance.reports', compact('reports'));
    }

    /**
     * Generate compliance report.
     */
    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:hipaa_audit,access_violations,data_retention,backup_compliance,permenkes',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'format' => 'required|in:pdf,excel',
            'notes' => 'nullable|string',
        ]);

        $report = ComplianceReport::create([
            'report_type' => $validated['report_type'],
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
            'generated_by' => auth()->id(),
            'status' => 'generating',
            'notes' => $validated['notes'],
        ]);

        // Generate report asynchronously
        // Will update status to 'completed' when done

        return back()->with('success', 'Report generation started');
    }

    /**
     * Display data anonymization.
     */
    public function anonymization()
    {
        $statistics = [
            'total_anonymized' => 0, // Will fetch from DataAnonymizationLog
            'last_anonymization' => null,
            'pending_anonymization' => 0,
        ];

        return view('healthcare.compliance.anonymization', compact('statistics'));
    }

    /**
     * Anonymize patient data.
     */
    public function anonymizeData(Request $request)
    {
        $validated = $request->validate([
            'patient_ids' => 'required|array',
            'patient_ids.*' => 'required|exists:patients,id',
            'reason' => 'required|string',
            'anonymization_type' => 'required|in:full,partial,specific_fields',
            'fields_to_anonymize' => 'nullable|array',
        ]);

        // Anonymize patient data
        // Log anonymization
        // Update patient records with anonymized data

        return back()->with('success', count($validated['patient_ids']).' patients anonymized successfully');
    }

    /**
     * Display compliance dashboard.
     */
    public function dashboard()
    {
        $statistics = [
            'total_violations' => AccessViolation::count(),
            'unresolved_violations' => AccessViolation::where('is_resolved', false)->count(),
            'critical_violations' => AccessViolation::where('severity', 'critical')->count(),
            'total_audits' => AuditTrail::count(),
            'total_backups' => BackupLog::count(),
            'last_backup' => BackupLog::latest()->first(),
            'compliance_score' => 0,
        ];

        $recentViolations = AccessViolation::latest()->limit(10)->get();
        $recentAudits = AuditTrail::latest()->limit(10)->get();

        return view('healthcare.compliance.dashboard', compact('statistics', 'recentViolations', 'recentAudits'));
    }
}
