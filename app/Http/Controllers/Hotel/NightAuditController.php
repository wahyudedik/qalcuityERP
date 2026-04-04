<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\NightAuditBatch;
use App\Models\RevenuePosting;
use App\Models\DailyOccupancyStat;
use App\Models\DailyRateStat;
use App\Services\NightAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NightAuditController extends Controller
{
    protected $auditService;

    public function __construct(NightAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Dashboard - Overview of night audit
     */
    public function index()
    {
        $tenantId = auth()->user()->current_tenant_id;

        // Get recent audit batches
        $recentBatches = NightAuditBatch::where('tenant_id', $tenantId)
            ->orderBy('audit_date', 'desc')
            ->limit(10)
            ->get();

        // Get today's stats if available
        $todayStats = DailyOccupancyStat::where('tenant_id', $tenantId)
            ->where('stat_date', today())
            ->first();

        // Get current month ADR
        $monthlyADR = DailyRateStat::where('tenant_id', $tenantId)
            ->whereMonth('stat_date', now()->month)
            ->avg('adr') ?? 0;

        return view('hotel.night-audit.index', compact('recentBatches', 'todayStats', 'monthlyADR'));
    }

    /**
     * Start new audit batch
     */
    public function startAudit(Request $request)
    {
        $validated = $request->validate([
            'audit_date' => 'required|date',
        ]);

        try {
            $batch = $this->auditService->startAudit(
                auth()->user()->current_tenant_id,
                \Carbon\Carbon::parse($validated['audit_date'])
            );

            return redirect()->route('hotel.night-audit.batch', $batch->id)
                ->with('success', 'Audit batch started successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show batch processing page
     */
    public function showBatch(int $id)
    {
        $batch = NightAuditBatch::with(['revenuePostings', 'auditLogs.performedBy'])
            ->findOrFail($id);

        return view('hotel.night-audit.batch', compact('batch'));
    }

    /**
     * Execute room charge posting
     */
    public function postRoomCharges(int $batchId)
    {
        $batch = NightAuditBatch::findOrFail($batchId);

        try {
            $result = $this->auditService->postRoomCharges($batch);

            return back()->with('success', "Posted {$result['posted_count']} room charges. Total: Rp " . number_format($result['total_revenue'], 0, ',', '.'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to post room charges: ' . $e->getMessage());
        }
    }

    /**
     * Execute F&B revenue posting
     */
    public function postFBRevenue(int $batchId)
    {
        $batch = NightAuditBatch::findOrFail($batchId);

        try {
            $result = $this->auditService->postFBRevenue($batch);

            return back()->with('success', "Posted {$result['posted_count']} F&B transactions. Total: Rp " . number_format($result['total_revenue'], 0, ',', '.'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to post F&B revenue: ' . $e->getMessage());
        }
    }

    /**
     * Execute minibar charge posting
     */
    public function postMinibarCharges(int $batchId)
    {
        $batch = NightAuditBatch::findOrFail($batchId);

        try {
            $result = $this->auditService->postMinibarCharges($batch);

            return back()->with('success', "Posted {$result['posted_count']} minibar charges. Total: Rp " . number_format($result['total_revenue'], 0, ',', '.'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to post minibar charges: ' . $e->getMessage());
        }
    }

    /**
     * Calculate occupancy statistics
     */
    public function calculateOccupancy(int $batchId)
    {
        $batch = NightAuditBatch::findOrFail($batchId);

        try {
            $this->auditService->calculateOccupancyStats($batch);

            return back()->with('success', 'Occupancy statistics calculated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to calculate occupancy: ' . $e->getMessage());
        }
    }

    /**
     * Complete the audit batch
     */
    public function completeAudit(int $batchId)
    {
        $batch = NightAuditBatch::findOrFail($batchId);

        try {
            $this->auditService->completeAudit($batch);

            return redirect()->route('hotel.night-audit.index')
                ->with('success', 'Night audit completed successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to complete audit: ' . $e->getMessage());
        }
    }

    /**
     * Revenue postings list
     */
    public function revenuePostings(Request $request)
    {
        $tenantId = auth()->user()->current_tenant_id;

        $query = RevenuePosting::where('tenant_id', $tenantId)
            ->with(['reservation.guest', 'auditBatch']);

        // Filters
        if ($request->filled('date_from')) {
            $query->whereDate('posting_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('posting_date', '<=', $request->date_to);
        }

        if ($request->filled('revenue_type')) {
            $query->where('revenue_type', $request->revenue_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $postings = $query->orderBy('posting_date', 'desc')
            ->paginate(50);

        $revenueTypes = [
            'room_charge',
            'room_tax',
            'minibar',
            'restaurant',
            'room_service',
            'laundry',
            'telephone',
            'parking',
            'spa',
            'other',
        ];

        return view('hotel.night-audit.revenue-postings', compact('postings', 'revenueTypes'));
    }

    /**
     * Void a revenue posting
     */
    public function voidPosting(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $posting = RevenuePosting::findOrFail($id);

        if (!$posting->canBeVoided()) {
            return back()->with('error', 'This posting cannot be voided');
        }

        $posting->void($validated['reason']);

        return back()->with('success', 'Revenue posting voided successfully');
    }

    /**
     * Statistics and ADR report
     */
    public function statistics(Request $request)
    {
        $tenantId = auth()->user()->current_tenant_id;

        // Date range
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Get occupancy stats for period
        $occupancyStats = DailyOccupancyStat::where('tenant_id', $tenantId)
            ->whereBetween('stat_date', [$dateFrom, $dateTo])
            ->orderBy('stat_date')
            ->get();

        // Get rate stats for period
        $rateStats = DailyRateStat::where('tenant_id', $tenantId)
            ->whereBetween('stat_date', [$dateFrom, $dateTo])
            ->orderBy('stat_date')
            ->get();

        // Calculate averages
        $avgOccupancy = $occupancyStats->avg('occupancy_percentage') ?? 0;
        $avgADR = $rateStats->avg('adr') ?? 0;
        $avgRevPAR = $rateStats->avg('revpar') ?? 0;

        // Total revenue for period
        $totalRevenue = $rateStats->sum('total_room_revenue');

        return view('hotel.night-audit.statistics', compact(
            'occupancyStats',
            'rateStats',
            'avgOccupancy',
            'avgADR',
            'avgRevPAR',
            'totalRevenue',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Recalculate ADR and RevPAR for a date
     */
    public function recalculateRates(Request $request)
    {
        $validated = $request->validate([
            'stat_date' => 'required|date',
        ]);

        $tenantId = auth()->user()->current_tenant_id;
        $date = \Carbon\Carbon::parse($validated['stat_date']);

        $stats = DailyRateStat::where('tenant_id', $tenantId)
            ->where('stat_date', $date)
            ->first();

        if ($stats) {
            $stats->calculateMetrics();
            return back()->with('success', 'Rates recalculated successfully');
        }

        return back()->with('error', 'No data found for this date');
    }
}
