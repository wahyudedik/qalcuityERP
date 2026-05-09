<?php

namespace App\Http\Controllers\Cosmetic;

use App\Http\Controllers\Controller;
use App\Models\BatchRecall;
use App\Models\CosmeticBatchRecord;
use App\Models\ExpiryAlert;
use App\Models\ExpiryReport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpiryController extends Controller
{
    public function dashboard(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // Stats
        $stats = [
            'total_alerts' => ExpiryAlert::where('tenant_id', $tenantId)->count(),
            'unread_alerts' => ExpiryAlert::where('tenant_id', $tenantId)->unread()->count(),
            'expired_batches' => ExpiryAlert::where('tenant_id', $tenantId)->expired()->count(),
            'critical_alerts' => ExpiryAlert::where('tenant_id', $tenantId)->critical()->count(),
            'active_recalls' => BatchRecall::where('tenant_id', $tenantId)->active()->count(),
            'total_recalls' => BatchRecall::where('tenant_id', $tenantId)->count(),
        ];

        // Alerts by severity
        $alertsBySeverity = ExpiryAlert::where('tenant_id', $tenantId)
            ->selectRaw('severity, COUNT(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity');

        // Recent alerts
        $alerts = ExpiryAlert::where('tenant_id', $tenantId)
            ->with('batch')
            ->latest()
            ->paginate(20);

        // Batches expiring soon (next 90 days)
        $expiringSoon = CosmeticBatchRecord::where('tenant_id', $tenantId)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays(90)])
            ->with('formula')
            ->orderBy('expiry_date', 'asc')
            ->get();

        return view('cosmetic.expiry.dashboard', compact('stats', 'alertsBySeverity', 'alerts', 'expiringSoon'));
    }

    public function markAlertRead($id)
    {
        $tenantId = auth()->user()->tenant_id;
        $alert = ExpiryAlert::where('tenant_id', $tenantId)->findOrFail($id);
        $alert->markAsRead();

        return back()->with('success', 'Alert marked as read!');
    }

    public function markAlertActioned(Request $request, $id)
    {
        $validated = $request->validate([
            'action' => 'required|in:discounted,disposed,recalled,returned',
        ]);
        $tenantId = auth()->user()->tenant_id;
        $alert = ExpiryAlert::where('tenant_id', $tenantId)->findOrFail($id);
        $alert->markAsActioned($validated['action']);

        return back()->with('success', 'Alert action recorded!');
    }

    public function recallsIndex(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $stats = [
            'total_recalls' => BatchRecall::where('tenant_id', $tenantId)->count(),
            'active_recalls' => BatchRecall::where('tenant_id', $tenantId)->active()->count(),
            'completed_recalls' => BatchRecall::where('tenant_id', $tenantId)->where('status', 'completed')->count(),
            'critical_recalls' => BatchRecall::where('tenant_id', $tenantId)->critical()->count(),
        ];

        $recalls = BatchRecall::where('tenant_id', $tenantId)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->severity, fn ($q) => $q->where('severity', $request->severity))
            ->with('batch')
            ->latest()
            ->paginate(15);

        return view('cosmetic.expiry.recalls', compact('stats', 'recalls'));
    }

    public function storeRecall(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:cosmetic_batch_records,id',
            'recall_reason' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:minor,major,critical',
            'recall_date' => 'required|date',
            'affected_regions' => 'nullable|string',
            'total_units' => 'required|integer|min:0',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['recall_number'] = BatchRecall::getNextRecallNumber();
        $validated['status'] = 'initiated';
        $validated['initiated_by'] = auth()->id();

        BatchRecall::create($validated);

        return back()->with('success', 'Batch recall initiated!');
    }

    public function updateRecallProgress(Request $request, $id)
    {
        $validated = $request->validate([
            'units_returned' => 'nullable|integer|min:0',
            'units_destroyed' => 'nullable|integer|min:0',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $recall = BatchRecall::where('tenant_id', $tenantId)->findOrFail($id);

        if ($request->has('units_returned')) {
            $recall->units_returned = $validated['units_returned'];
        }
        if ($request->has('units_destroyed')) {
            $recall->units_destroyed = $validated['units_destroyed'];
        }
        $recall->status = 'in_progress';
        $recall->save();

        return back()->with('success', 'Recall progress updated!');
    }

    public function completeRecall(Request $request, $id)
    {
        $validated = $request->validate(['notes' => 'nullable|string']);
        $tenantId = auth()->user()->tenant_id;
        $recall = BatchRecall::where('tenant_id', $tenantId)->findOrFail($id);
        $recall->complete($validated['notes'] ?? '');

        return back()->with('success', 'Recall completed!');
    }

    public function cancelRecall(Request $request, $id)
    {
        $validated = $request->validate(['notes' => 'nullable|string']);
        $tenantId = auth()->user()->tenant_id;
        $recall = BatchRecall::where('tenant_id', $tenantId)->findOrFail($id);
        $recall->cancel($validated['notes'] ?? '');

        return back()->with('success', 'Recall cancelled!');
    }

    public function reportsIndex(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $reports = ExpiryReport::where('tenant_id', $tenantId)
            ->when($request->type, fn ($q) => $q->where('report_type', $request->type))
            ->latest()
            ->paginate(15);

        return view('cosmetic.expiry.reports', compact('reports'));
    }

    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:monthly,quarterly,annual,ad_hoc',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $tenantId = auth()->user()->tenant_id;

        // Calculate statistics
        $totalMonitored = CosmeticBatchRecord::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$validated['start_date'], $validated['end_date']])
            ->count();

        $batchesExpired = ExpiryAlert::where('tenant_id', $tenantId)
            ->whereBetween('alert_date', [$validated['start_date'], $validated['end_date']])
            ->expired()
            ->count();

        $batchesRecalled = BatchRecall::where('tenant_id', $tenantId)
            ->whereBetween('recall_date', [$validated['start_date'], $validated['end_date']])
            ->count();

        $report = ExpiryReport::create([
            'tenant_id' => $tenantId,
            'report_number' => ExpiryReport::getNextReportNumber(),
            'report_type' => $validated['report_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_batches_monitored' => $totalMonitored,
            'batches_expired' => $batchesExpired,
            'batches_recalled' => $batchesRecalled,
            'generated_by' => auth()->id(),
            'summary_data' => [
                'generated_at' => now()->toDateTimeString(),
                'period' => Carbon::parse($validated['start_date'])->format('d M Y').' - '.Carbon::parse($validated['end_date'])->format('d M Y'),
            ],
        ]);

        return back()->with('success', 'Expiry report generated!');
    }
}
