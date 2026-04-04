<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\RatePlan;
use App\Models\DynamicPricingRule;
use App\Models\OccupancyForecast;
use App\Models\CompetitorRate;
use App\Models\SpecialEvent;
use App\Models\PricingRecommendation;
use App\Models\RevenueSnapshot;
use App\Models\RoomType;
use App\Services\DynamicPricingEngine;
use App\Services\OccupancyForecastingService;
use App\Services\RateOptimizationService;
use App\Services\CompetitorRateTrackingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevenueManagementController extends Controller
{
    private function getTenantId(): int
    {
        $user = request()->user();
        return $user->current_tenant_id ?? $user->tenant_id;
    }

    /**
     * Revenue Management Dashboard
     */
    public function dashboard()
    {
        $startDate = now();
        $endDate = now()->addDays(30);

        // Get KPIs
        $optimizationService = new RateOptimizationService($this->getTenantId());
        $kpis = $optimizationService->getKPIs(now()->subDays(30), now());

        // Get forecasts
        $forecastingService = new OccupancyForecastingService($this->getTenantId());
        $forecasts = $forecastingService->generateForecast($startDate, $endDate);

        // Get pending recommendations
        $recommendations = PricingRecommendation::where('tenant_id', $this->getTenantId())
            ->where('status', 'pending')
            ->where('recommendation_date', '>=', now())
            ->orderBy('recommendation_date')
            ->limit(10)
            ->get();

        // Get competitor alerts
        $competitorService = new CompetitorRateTrackingService($this->getTenantId());
        $rateAlerts = $competitorService->getRateAlerts();

        // Get demand indicators
        $demandIndicators = $forecastingService->getDemandIndicators($startDate, $endDate);

        // Get recent snapshots
        $recentSnapshots = RevenueSnapshot::where('tenant_id', $this->getTenantId())
            ->orderBy('snapshot_date', 'desc')
            ->limit(7)
            ->get();

        return view('hotel.revenue.dashboard', compact(
            'kpis',
            'forecasts',
            'recommendations',
            'rateAlerts',
            'demandIndicators',
            'recentSnapshots'
        ));
    }

    /**
     * Rate Plans Management
     */
    public function ratePlans()
    {
        $ratePlans = RatePlan::where('tenant_id', $this->getTenantId())
            ->with('roomType', 'pricingRules')
            ->orderBy('name')
            ->paginate(20);

        $roomTypes = RoomType::where('tenant_id', $this->getTenantId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('hotel.revenue.rate-plans', compact('ratePlans', 'roomTypes'));
    }

    /**
     * Store new rate plan
     */
    public function storeRatePlan(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:rate_plans,code',
            'description' => 'nullable|string',
            'type' => 'required|in:standard,non_refundable,package,corporate,promotional',
            'base_rate' => 'required|numeric|min:0',
            'min_stay' => 'nullable|integer|min:1',
            'max_stay' => 'nullable|integer|min:1',
            'advance_booking_days' => 'nullable|integer|min:0',
            'is_refundable' => 'boolean',
            'cancellation_hours' => 'nullable|integer|min:0',
            'includes_breakfast' => 'boolean',
            'inclusions' => 'nullable|array',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ]);

        $validated['tenant_id'] = $this->getTenantId();
        $validated['is_active'] = true;

        RatePlan::create($validated);

        return redirect()->route('revenue.rate-plans')
            ->with('success', 'Rate plan created successfully');
    }

    /**
     * Update rate plan
     */
    public function updateRatePlan(Request $request, RatePlan $ratePlan)
    {
        $this->authorizeAccess($ratePlan);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_rate' => 'required|numeric|min:0',
            'min_stay' => 'nullable|integer|min:1',
            'max_stay' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ]);

        $ratePlan->update($validated);

        return redirect()->route('revenue.rate-plans')
            ->with('success', 'Rate plan updated successfully');
    }

    /**
     * Dynamic Pricing Rules
     */
    public function pricingRules()
    {
        $rules = DynamicPricingRule::where('tenant_id', $this->getTenantId())
            ->with('ratePlan')
            ->orderBy('priority')
            ->orderBy('name')
            ->paginate(20);

        $ratePlans = RatePlan::where('tenant_id', $this->getTenantId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('hotel.revenue.pricing-rules', compact('rules', 'ratePlans'));
    }

    /**
     * Store pricing rule
     */
    public function storePricingRule(Request $request)
    {
        $validated = $request->validate([
            'rate_plan_id' => 'nullable|exists:rate_plans,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rule_type' => 'required|in:occupancy_based,seasonal,day_of_week,length_of_stay,advance_booking,competitor_based,event_based',
            'conditions' => 'required|array',
            'adjustment_type' => 'required|in:percentage,fixed_amount',
            'adjustment_value' => 'required|numeric',
            'priority' => 'required|in:low,medium,high',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ]);

        $validated['tenant_id'] = $this->getTenantId();
        $validated['is_active'] = true;

        DynamicPricingRule::create($validated);

        return redirect()->route('revenue.pricing-rules')
            ->with('success', 'Pricing rule created successfully');
    }

    /**
     * Occupancy Forecasts
     */
    public function forecasts(Request $request)
    {
        $days = $request->input('days', 30);
        $startDate = now();
        $endDate = now()->addDays($days);

        $service = new OccupancyForecastingService($this->getTenantId());

        // Generate or refresh forecasts
        $forecasts = $service->generateForecast($startDate, $endDate);

        // Get room types for filtering
        $roomTypes = RoomType::where('tenant_id', $this->getTenantId())
            ->where('is_active', true)
            ->get();

        // Get demand indicators
        $demandIndicators = $service->getDemandIndicators($startDate, $endDate);

        return view('hotel.revenue.forecasts', compact(
            'forecasts',
            'roomTypes',
            'demandIndicators',
            'days'
        ));
    }

    /**
     * Generate fresh forecasts
     */
    public function generateForecasts(Request $request)
    {
        $days = $request->input('days', 30);
        $startDate = now();
        $endDate = now()->addDays($days);

        $service = new OccupancyForecastingService($this->getTenantId());
        $forecasts = $service->generateForecast($startDate, $endDate);

        return redirect()->route('revenue.forecasts')
            ->with('success', "Generated {$forecasts->count()} forecasts");
    }

    /**
     * Competitor Rates
     */
    public function competitorRates(Request $request)
    {
        $days = $request->input('days', 30);
        $startDate = now()->subDays($days);
        $endDate = now()->addDays(30);

        $service = new CompetitorRateTrackingService($this->getTenantId());

        $competitors = $service->getCompetitors();
        $analysis = $service->getRateAnalysis($startDate, $endDate);
        $comparison = $service->compareWithCompetitors(now());
        $positioning = $service->getPositioningReport();

        $recentRates = CompetitorRate::where('tenant_id', $this->getTenantId())
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('hotel.revenue.competitor-rates', compact(
            'competitors',
            'analysis',
            'comparison',
            'positioning',
            'recentRates',
            'days'
        ));
    }

    /**
     * Store competitor rate
     */
    public function storeCompetitorRate(Request $request)
    {
        $validated = $request->validate([
            'competitor_name' => 'required|string|max:255',
            'source' => 'required|string|max:255',
            'rate_date' => 'required|date',
            'rate' => 'required|numeric|min:0',
            'room_type' => 'nullable|string|max:255',
            'amenities' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $service = new CompetitorRateTrackingService($this->getTenantId());
        $service->recordRate($validated);

        return redirect()->route('revenue.competitor-rates')
            ->with('success', 'Competitor rate recorded successfully');
    }

    /**
     * Special Events
     */
    public function specialEvents()
    {
        $events = SpecialEvent::where('tenant_id', $this->getTenantId())
            ->where('end_date', '>=', now())
            ->orderBy('start_date')
            ->paginate(20);

        return view('hotel.revenue.special-events', compact('events'));
    }

    /**
     * Store special event
     */
    public function storeSpecialEvent(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'impact_level' => 'required|in:low,medium,high,very_high',
            'expected_demand_increase' => 'nullable|numeric|min:0|max:100',
            'affects_pricing' => 'boolean',
        ]);

        $validated['tenant_id'] = $this->getTenantId();

        SpecialEvent::create($validated);

        return redirect()->route('revenue.special-events')
            ->with('success', 'Special event created successfully');
    }

    /**
     * Pricing Recommendations
     */
    public function recommendations()
    {
        $service = new RateOptimizationService($this->getTenantId());

        // Generate fresh recommendations
        $service->generateRecommendations(now(), now()->addDays(30));

        $recommendations = PricingRecommendation::where('tenant_id', $this->getTenantId())
            ->with('roomType')
            ->orderBy('recommendation_date')
            ->orderBy('status')
            ->paginate(30);

        $stats = [
            'pending' => PricingRecommendation::where('tenant_id', $this->getTenantId())->where('status', 'pending')->count(),
            'applied' => PricingRecommendation::where('tenant_id', $this->getTenantId())->where('status', 'applied')->count(),
            'rejected' => PricingRecommendation::where('tenant_id', $this->getTenantId())->where('status', 'rejected')->count(),
        ];

        return view('hotel.revenue.recommendations', compact('recommendations', 'stats'));
    }

    /**
     * Apply recommendation
     */
    public function applyRecommendation(PricingRecommendation $recommendation)
    {
        $this->authorizeAccess($recommendation);

        $recommendation->apply(request()->user()?->id);

        // Update the actual room type rate
        if ($recommendation->roomType) {
            $recommendation->roomType->update([
                'base_rate' => $recommendation->recommended_rate
            ]);
        }

        return redirect()->route('revenue.recommendations')
            ->with('success', 'Recommendation applied successfully');
    }

    /**
     * Reject recommendation
     */
    public function rejectRecommendation(PricingRecommendation $recommendation)
    {
        $this->authorizeAccess($recommendation);

        $recommendation->reject(request()->user()?->id);

        return redirect()->route('revenue.recommendations')
            ->with('success', 'Recommendation rejected');
    }

    /**
     * Rate Calendar
     */
    public function rateCalendar(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : now();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))
            : now()->addDays(30);

        $engine = new DynamicPricingEngine($this->getTenantId());
        $calendar = $engine->getRateCalendar($startDate, $endDate);

        $roomTypes = RoomType::where('tenant_id', $this->getTenantId())
            ->where('is_active', true)
            ->get();

        return view('hotel.revenue.rate-calendar', compact(
            'calendar',
            'roomTypes',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Yield Optimization
     */
    public function yieldOptimization(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : now();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))
            : now()->addDays(30);

        $service = new RateOptimizationService($this->getTenantId());

        $optimization = $service->optimizeYield($startDate, $endDate);
        $losRestrictions = $service->calculateLosRestrictions(now());
        $channelMix = $service->optimizeChannelMix(now()->subDays(30), now());
        $overbooking = $service->calculateOverbookingRecommendation();

        return view('hotel.revenue.yield-optimization', compact(
            'optimization',
            'losRestrictions',
            'channelMix',
            'overbooking',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Revenue Reports
     */
    public function reports(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : now()->subDays(30);

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))
            : now();

        $service = new RateOptimizationService($this->getTenantId());
        $kpis = $service->getKPIs($startDate, $endDate);

        $snapshots = RevenueSnapshot::where('tenant_id', $this->getTenantId())
            ->whereBetween('snapshot_date', [$startDate, $endDate])
            ->orderBy('snapshot_date')
            ->get();

        return view('hotel.revenue.reports', compact(
            'kpis',
            'snapshots',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Bulk rate update
     */
    public function bulkRateUpdate(Request $request)
    {
        $validated = $request->validate([
            'updates' => 'required|array',
            'updates.*.room_type_id' => 'required|exists:room_types,id',
            'updates.*.new_rate' => 'required|numeric|min:0',
        ]);

        $service = new RateOptimizationService($this->getTenantId());
        $results = $service->applyBulkRateUpdate($validated['updates']);

        $successCount = count($results['success']);
        $failCount = count($results['failed']);

        if ($failCount === 0) {
            return redirect()->back()
                ->with('success', "Successfully updated {$successCount} rates");
        }

        return redirect()->back()
            ->with('warning', "Updated {$successCount} rates, {$failCount} failed");
    }

    /**
     * API: Get optimal rate
     */
    public function getOptimalRate(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'date' => 'required|date',
            'rate_plan_id' => 'nullable|exists:rate_plans,id',
        ]);

        $engine = new DynamicPricingEngine($this->getTenantId());

        $rate = $engine->calculateOptimalRate(
            $validated['room_type_id'],
            Carbon::parse($validated['date']),
            $validated['rate_plan_id'] ?? null
        );

        return response()->json($rate);
    }

    /**
     * API: Get rate range
     */
    public function getRateRange(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $engine = new DynamicPricingEngine($this->getTenantId());

        $rates = $engine->calculateRateRange(
            $validated['room_type_id'],
            Carbon::parse($validated['start_date']),
            Carbon::parse($validated['end_date'])
        );

        return response()->json($rates);
    }

    /**
     * Authorize access to tenant resources
     */
    private function authorizeAccess($model): void
    {
        if ($model->tenant_id !== $this->getTenantId()) {
            abort(403, 'Unauthorized access');
        }
    }
}
