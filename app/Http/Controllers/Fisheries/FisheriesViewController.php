<?php

namespace App\Http\Controllers\Fisheries;

use App\Http\Controllers\Controller;
use App\Models\ColdStorageUnit;
use App\Models\TemperatureLog;
use App\Models\ColdChainAlert;
use App\Models\FishingVessel;
use App\Models\FishingTrip;
use App\Models\CatchLog;
use App\Models\AquaculturePond;
use App\Models\WaterQualityLog;
use App\Models\FeedingSchedule;
use App\Models\FishSpecies;
use App\Models\QualityGrade;
use App\Models\ExportPermit;
use App\Models\HealthCertificate;
use App\Models\CustomsDeclaration;
use App\Models\ExportShipment;
use App\Models\FishingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FisheriesViewController extends Controller
{
    /**
     * Get authenticated user's tenant ID
     */
    // tenantId() inherited from parent Controller
    /**
     * Main Fisheries Dashboard
     */
    public function index()
    {
        $tenantId = $this->tenantId();

        // Gather dashboard statistics
        $stats = [
            'cold_storage_units' => ColdStorageUnit::where('tenant_id', $tenantId)->count(),
            'temp_alerts' => ColdChainAlert::where('tenant_id', $tenantId)
                ->where('is_acknowledged', false)
                ->count(),
            'active_trips' => FishingTrip::where('tenant_id', $tenantId)
                ->whereIn('status', ['departed', 'fishing', 'returning'])
                ->count(),
            'total_catches' => CatchLog::whereHas('trip', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })->count(),
            'ponds' => AquaculturePond::where('tenant_id', $tenantId)->count(),
            'avg_pond_utilization' => AquaculturePond::where('tenant_id', $tenantId)
                ->whereIn('status', ['stocked', 'growing', 'ready_harvest'])
                ->get()
                ->map(function ($pond) {
                    return $pond->carrying_capacity > 0
                        ? ($pond->current_stock / $pond->carrying_capacity) * 100
                        : 0;
                })
                ->avg() ?? 0,
            'species_count' => FishSpecies::where('tenant_id', $tenantId)->count(),
            'export_shipments' => ExportShipment::where('tenant_id', $tenantId)
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];

        // Recent activities (mock data for now)
        $recent_activities = [];

        return view('fisheries.index', compact('stats', 'recent_activities'));
    }

    /**
     * Cold Chain Management View
     */
    public function coldChain()
    {
        $tenantId = $this->tenantId();

        $storageUnits = ColdStorageUnit::where('tenant_id', $tenantId)
            ->with(['latestTemperatureLog'])
            ->orderBy('unit_code')
            ->paginate(12);

        // Calculate stats
        $units = $storageUnits->items();
        $safeUnits = collect($units)->filter(fn($u) => $u->isTemperatureSafe())->count();
        $alerts = ColdChainAlert::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($alert) => [
                'title' => "Temperature Alert - {$alert->storageUnit->unit_code}",
                'description' => "Current: {$alert->current_temperature}°C, Range: {$alert->threshold_min}-{$alert->threshold_max}°C",
                'severity' => $alert->severity,
                'severity_color' => $alert->severity === 'critical' ? 'red' : ($alert->severity === 'warning' ? 'yellow' : 'blue'),
                'time' => $alert->created_at->diffForHumans(),
            ]);

        $stats = [
            'units' => $units,
            'alerts' => $alerts,
            'safe_units' => $safeUnits,
            'active_alerts' => count($alerts),
            'avg_utilization' => collect($units)->avg('utilization_percentage') ?? 0,
        ];

        return view('fisheries.cold-chain', compact('storageUnits', 'stats'));
    }

    /**
     * Cold Chain Detail View
     */
    public function coldChainDetail($id)
    {
        $unit = ColdStorageUnit::where('tenant_id', $this->tenantId())
            ->findOrFail($id);

        $temperatureLogs = TemperatureLog::where('storage_unit_id', $id)
            ->orderBy('logged_at', 'desc')
            ->paginate(50);

        $alerts = ColdChainAlert::where('storage_unit_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('fisheries.cold-chain-detail', compact('unit', 'temperatureLogs', 'alerts'));
    }

    /**
     * Fishing Operations View
     */
    public function operations()
    {
        $tenantId = $this->tenantId();

        $trips = FishingTrip::where('tenant_id', $tenantId)
            ->with(['vessel', 'captain', 'catches.species', 'catches.grade'])
            ->orderBy('departure_time', 'desc')
            ->paginate(10);

        // Stats
        $stats = [
            'total_vessels' => FishingVessel::where('tenant_id', $tenantId)->count(),
            'active_trips' => FishingTrip::where('tenant_id', $tenantId)
                ->whereIn('status', ['departed', 'fishing', 'returning'])
                ->count(),
            'trips_today' => FishingTrip::where('tenant_id', $tenantId)
                ->whereDate('departure_time', today())
                ->count(),
            'total_catch_weight' => CatchLog::whereHas('trip', fn($q) => $q->where('tenant_id', $tenantId))
                ->sum('total_weight'),
            'total_estimated_value' => CatchLog::whereHas('trip', fn($q) => $q->where('tenant_id', $tenantId))
                ->get()
                ->sum('estimated_value'),
        ];

        // Dropdown options
        $species_list = FishSpecies::where('tenant_id', $tenantId)->orderBy('common_name')->get();
        $grades = QualityGrade::where('tenant_id', $tenantId)->orderBy('grade_code')->get();
        $vessels = FishingVessel::where('tenant_id', $tenantId)->where('status', 'active')->get();
        $captains = \App\Models\User::where('tenant_id', $tenantId)
            ->where('role', 'captain')
            ->orWhere('role', 'crew')
            ->get();
        $zones = FishingZone::where('tenant_id', $tenantId)->get();

        return view('fisheries.operations', compact(
            'trips',
            'stats',
            'species_list',
            'grades',
            'vessels',
            'captains',
            'zones'
        ));
    }

    /**
     * Operation Detail View
     */
    public function operationDetail($id)
    {
        $trip = FishingTrip::where('tenant_id', $this->tenantId())
            ->with(['vessel', 'captain', 'crew', 'catches.species', 'catches.grade', 'fishingZone'])
            ->findOrFail($id);

        $catches = CatchLog::where('trip_id', $id)
            ->with(['species', 'grade'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('fisheries.operation-detail', compact('trip', 'catches'));
    }

    /**
     * Aquaculture Management View
     */
    public function aquaculture()
    {
        $tenantId = $this->tenantId();

        $ponds = AquaculturePond::where('tenant_id', $tenantId)
            ->with(['latestWaterQuality'])
            ->orderBy('code')
            ->paginate(12);

        // Stats
        $stats = [
            'total_ponds' => AquaculturePond::where('tenant_id', $tenantId)->count(),
            'active_ponds' => AquaculturePond::where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->count(),
            'avg_utilization' => AquaculturePond::where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->avg('utilization_percentage') ?? 0,
            'avg_fcr' => 1.5, // This would be calculated from actual data
        ];

        return view('fisheries.aquaculture', compact('ponds', 'stats'));
    }

    /**
     * Aquaculture Detail View
     */
    public function aquacultureDetail($id)
    {
        $pond = AquaculturePond::where('tenant_id', $this->tenantId())
            ->findOrFail($id);

        $waterQualityLogs = WaterQualityLog::where('pond_id', $id)
            ->orderBy('logged_at', 'desc')
            ->paginate(30);

        $feedings = FeedingSchedule::where('pond_id', $id)
            ->orderBy('feeding_time', 'desc')
            ->paginate(20);

        return view('fisheries.aquaculture-detail', compact('pond', 'waterQualityLogs', 'feedings'));
    }

    /**
     * Species & Grading Catalog View
     */
    public function species(Request $request)
    {
        $tenantId = $this->tenantId();
        $tab = $request->get('tab', 'species');

        if ($tab === 'grades') {
            $grades = QualityGrade::where('tenant_id', $tenantId)
                ->orderBy('grade_code')
                ->paginate(20);

            return view('fisheries.species', compact('grades', 'tab'));
        }

        $species = FishSpecies::where('tenant_id', $tenantId)
            ->when($request->category, fn($q, $cat) => $q->where('category', $cat))
            ->when(
                $request->search,
                fn($q, $search) =>
                $q->where(function ($q) use ($search) {
                    $q->where('common_name', 'like', "%{$search}%")
                        ->orWhere('scientific_name', 'like', "%{$search}%");
                })
            )
            ->orderBy('common_name')
            ->paginate(12);

        $grades = QualityGrade::where('tenant_id', $tenantId)->orderBy('grade_code')->get();

        return view('fisheries.species', compact('species', 'grades', 'tab'));
    }

    /**
     * Export Documentation View
     */
    public function export(Request $request)
    {
        $tenantId = $this->tenantId();
        $tab = $request->get('tab', 'permits');

        $stats = [
            'active_permits' => ExportPermit::where('tenant_id', $tenantId)
                ->where('status', 'approved')
                ->where('valid_until', '>=', now())
                ->count(),
            'health_certificates' => HealthCertificate::where('tenant_id', $tenantId)
                ->where('valid_until', '>=', now())
                ->count(),
            'customs_declarations' => CustomsDeclaration::where('tenant_id', $tenantId)
                ->where('status', 'approved')
                ->count(),
            'shipments_this_month' => ExportShipment::where('tenant_id', $tenantId)
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];

        if ($tab === 'certificates') {
            $certificates = HealthCertificate::where('tenant_id', $tenantId)
                ->orderBy('issued_date', 'desc')
                ->paginate(10);

            return view('fisheries.export', compact('certificates', 'stats', 'tab'));
        }

        if ($tab === 'customs') {
            $customsDeclarations = CustomsDeclaration::where('tenant_id', $tenantId)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view('fisheries.export', compact('customsDeclarations', 'stats', 'tab'));
        }

        if ($tab === 'shipments') {
            $shipments = ExportShipment::where('tenant_id', $tenantId)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view('fisheries.export', compact('shipments', 'stats', 'tab'));
        }

        // Default: permits tab
        $permits = ExportPermit::where('tenant_id', $tenantId)
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when(
                $request->search,
                fn($q, $search) =>
                $q->where('permit_number', 'like', "%{$search}%")
                    ->orWhere('commodity', 'like', "%{$search}%")
            )
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('fisheries.export', compact('permits', 'stats', 'tab'));
    }

    /**
     * Analytics & Reports View
     */
    public function analytics()
    {
        $tenantId = $this->tenantId();
        $period = request('period', '30d'); // 7d, 30d, 90d, 1y

        // Date range calculation
        $startDate = match ($period) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            '1y' => now()->subYear(),
            default => now()->subDays(30),
        };

        // Production Metrics
        $totalCatches = CatchLog::whereHas('trip', fn($q) => $q->where('tenant_id', $tenantId))
            ->where('created_at', '>=', $startDate)
            ->count();

        $totalCatchWeight = CatchLog::whereHas('trip', fn($q) => $q->where('tenant_id', $tenantId))
            ->where('created_at', '>=', $startDate)
            ->sum('total_weight');

        $totalRevenue = CatchLog::whereHas('trip', fn($q) => $q->where('tenant_id', $tenantId))
            ->where('created_at', '>=', $startDate)
            ->sum('estimated_value');

        $completedTrips = FishingTrip::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->where('departure_time', '>=', $startDate)
            ->count();

        // Top Species by Weight
        $topSpecies = CatchLog::whereHas('trip', fn($q) => $q->where('tenant_id', $tenantId))
            ->where('created_at', '>=', $startDate)
            ->with('species')
            ->selectRaw('species_id, SUM(total_weight) as total_weight, COUNT(*) as catch_count')
            ->groupBy('species_id')
            ->orderByDesc('total_weight')
            ->limit(5)
            ->get();

        // Daily Catch Trend (last 30 days)
        $dailyCatchTrend = CatchLog::whereHas('trip', fn($q) => $q->where('tenant_id', $tenantId))
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(total_weight) as total_weight, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue by Week
        $weeklyRevenue = CatchLog::whereHas('trip', fn($q) => $q->where('tenant_id', $tenantId))
            ->where('created_at', '>=', now()->subWeeks(12))
            ->selectRaw('YEARWEEK(created_at) as week, SUM(estimated_value) as revenue')
            ->groupBy('week')
            ->orderBy('week')
            ->get();

        // Aquaculture Stats
        $activePonds = AquaculturePond::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->count();

        $avgPondUtilization = AquaculturePond::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->avg('utilization_percentage') ?? 0;

        $totalFeedingCost = FeedingSchedule::whereHas('pond', fn($q) => $q->where('tenant_id', $tenantId))
            ->where('created_at', '>=', $startDate)
            ->sum('feed_cost');

        // Cold Chain Performance
        $tempBreaches = ColdChainAlert::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $startDate)
            ->count();

        $avgStorageUtilization = ColdStorageUnit::where('tenant_id', $tenantId)
            ->avg('utilization_percentage') ?? 0;

        // Efficiency Metrics
        $avgCatchPerTrip = $completedTrips > 0 ? $totalCatchWeight / $completedTrips : 0;
        $avgRevenuePerTrip = $completedTrips > 0 ? $totalRevenue / $completedTrips : 0;
        $revenuePerKg = $totalCatchWeight > 0 ? $totalRevenue / $totalCatchWeight : 0;

        $analytics = [
            'period' => $period,
            'production' => [
                'total_catches' => $totalCatches,
                'total_weight' => $totalCatchWeight,
                'total_revenue' => $totalRevenue,
                'completed_trips' => $completedTrips,
                'avg_catch_per_trip' => $avgCatchPerTrip,
                'avg_revenue_per_trip' => $avgRevenuePerTrip,
                'revenue_per_kg' => $revenuePerKg,
            ],
            'aquaculture' => [
                'active_ponds' => $activePonds,
                'avg_utilization' => $avgPondUtilization,
                'total_feeding_cost' => $totalFeedingCost,
            ],
            'cold_chain' => [
                'temp_breaches' => $tempBreaches,
                'avg_storage_utilization' => $avgStorageUtilization,
            ],
            'top_species' => $topSpecies,
            'daily_catch_trend' => $dailyCatchTrend,
            'weekly_revenue' => $weeklyRevenue,
        ];

        return view('fisheries.analytics', compact('analytics'));
    }
}
