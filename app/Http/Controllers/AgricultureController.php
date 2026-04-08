<?php

namespace App\Http\Controllers;

use App\Models\CropCycle;
use App\Services\WeatherIntegrationService;
use App\Services\PestDetectionService;
use App\Services\IrrigationAutomationService;
use App\Services\MarketPriceMonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AgricultureController extends Controller
{
    protected WeatherIntegrationService $weatherService;
    protected PestDetectionService $pestService;
    protected IrrigationAutomationService $irrigationService;
    protected MarketPriceMonitorService $priceService;

    /**
     * Constructor - Inject services dengan tenant_id dari user yang login
     */
    public function __construct()
    {
        $tenantId = auth()->user()?->tenant_id;

        // Inject tenant_id ke semua services
        $this->weatherService = app(WeatherIntegrationService::class, ['tenantId' => $tenantId]);
        $this->pestService = app(PestDetectionService::class, ['tenantId' => $tenantId]);
        $this->irrigationService = app(IrrigationAutomationService::class, ['tenantId' => $tenantId]);
        $this->priceService = app(MarketPriceMonitorService::class, ['tenantId' => $tenantId]);
    }

    /**
     * Dashboard overview
     */
    public function dashboard(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // Get active crops
        $activeCrops = CropCycle::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->withCount('pestDetections')
            ->get();

        // Get weather
        $weather = null;
        $recommendations = [];
        if ($request->has(['lat', 'lng'])) {
            $weather = $this->weatherService->getCurrentWeather(
                $request->input('lat'),
                $request->input('lng'),
                $tenantId
            );
            $recommendations = $weather?->getFarmingRecommendations() ?? [];
        }

        // Get irrigation schedules
        $upcomingIrrigations = $this->irrigationService->getUpcoming($tenantId, 3);

        // Get market prices
        $marketSummary = $this->priceService->getMarketSummary($tenantId);

        return view('agriculture.dashboard', compact(
            'activeCrops',
            'weather',
            'recommendations',
            'upcomingIrrigations',
            'marketSummary'
        ));
    }

    /**
     * Weather data
     */
    public function weather(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $lat = $request->input('lat');
        $lng = $request->input('lng');

        $current = $this->weatherService->getCurrentWeather($lat, $lng, $tenantId);
        $forecast = $this->weatherService->getForecast($lat, $lng, $tenantId);
        $recommendations = $this->weatherService->getFarmingRecommendations($lat, $lng, $tenantId);
        $alerts = $this->weatherService->checkSevereWeather($lat, $lng);

        return response()->json([
            'current' => $current,
            'forecast' => $forecast,
            'recommendations' => $recommendations,
            'alerts' => $alerts,
        ]);
    }

    /**
     * Pest detection - analyze photo
     */
    public function analyzePest(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // 10MB
            'crop_cycle_id' => 'nullable|exists:crop_cycles,id',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $result = $this->pestService->analyzePhoto(
            $request->file('image'),
            $tenantId,
            $request->input('crop_cycle_id')
        );

        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 500);
        }

        return response()->json($result);
    }

    /**
     * Pest detection history
     */
    public function pestHistory(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $cropCycleId = $request->input('crop_cycle_id');

        $history = $this->pestService->getHistory($tenantId, $cropCycleId);
        $stats = $this->pestService->getStatistics($tenantId);

        return response()->json([
            'history' => $history,
            'statistics' => $stats,
        ]);
    }

    /**
     * Generate irrigation schedule
     */
    public function generateIrrigationSchedule(Request $request)
    {
        $request->validate([
            'crop_type' => 'required|string',
            'area_hectares' => 'required|numeric|min:0.1',
            'growth_stage' => 'required|string',
            'irrigation_time' => 'required|date_format:H:i',
            'crop_cycle_id' => 'nullable|exists:crop_cycles,id',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $schedule = $this->irrigationService->generateSchedule($request->all(), $tenantId);

        return response()->json([
            'success' => true,
            'schedule' => $schedule,
            'message' => 'Irrigation schedule created successfully',
        ]);
    }

    /**
     * Toggle irrigation schedule
     */
    public function toggleIrrigation(int $id)
    {
        $schedule = \App\Models\IrrigationSchedule::findOrFail($id);
        $this->authorize('update', $schedule);

        $schedule->update(['is_active' => !$schedule->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $schedule->is_active,
        ]);
    }

    /**
     * Record manual irrigation
     */
    public function recordIrrigation(Request $request, int $scheduleId)
    {
        $request->validate([
            'duration_minutes' => 'required|integer|min:1',
            'water_used_liters' => 'required|numeric|min:0',
        ]);

        $this->irrigationService->recordIrrigation(
            $scheduleId,
            $request->input('duration_minutes'),
            $request->input('water_used_liters')
        );

        return response()->json(['success' => true]);
    }

    /**
     * Water usage statistics
     */
    public function waterUsageStats(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $days = $request->input('days', 30);

        $stats = $this->irrigationService->getWaterUsageStats($tenantId, $days);

        return response()->json($stats);
    }

    /**
     * Record market price
     */
    public function recordMarketPrice(Request $request)
    {
        $request->validate([
            'commodity' => 'required|string',
            'price_per_kg' => 'required|numeric|min:0',
            'market_name' => 'nullable|string',
            'quality_grade' => 'nullable|string',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $price = $this->priceService->recordPrice($request->all(), $tenantId);

        // Check alerts
        $triggeredAlerts = $this->priceService->checkAlerts($tenantId);

        return response()->json([
            'success' => true,
            'price' => $price,
            'triggered_alerts' => $triggeredAlerts,
        ]);
    }

    /**
     * Get price trends
     */
    public function priceTrends(Request $request)
    {
        $request->validate([
            'commodity' => 'required|string',
            'days' => 'nullable|integer|min:7|max:365',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $trends = $this->priceService->getPriceTrends(
            $tenantId,
            $request->input('commodity'),
            $request->input('days', 30)
        );

        return response()->json($trends);
    }

    /**
     * Set price alert
     */
    public function setPriceAlert(Request $request)
    {
        $request->validate([
            'commodity' => 'required|string',
            'target_price' => 'required|numeric|min:0',
            'condition' => 'required|in:above,below,equals',
            'notification_channels' => 'nullable|array',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $alert = $this->priceService->setAlert($request->all(), $tenantId);

        return response()->json([
            'success' => true,
            'alert' => $alert,
        ]);
    }

    /**
     * Get best selling time recommendation
     */
    public function bestSellingTime(Request $request)
    {
        $request->validate(['commodity' => 'required|string']);

        $tenantId = auth()->user()->tenant_id;

        $recommendation = $this->priceService->getBestSellingTime(
            $tenantId,
            $request->input('commodity')
        );

        return response()->json($recommendation);
    }

    /**
     * Create crop cycle
     */
    public function createCropCycle(Request $request)
    {
        $request->validate([
            'crop_name' => 'required|string',
            'area_hectares' => 'required|numeric|min:0.1',
            'planting_date' => 'required|date',
            'expected_harvest_date' => 'nullable|date|after:planting_date',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $crop = CropCycle::create(array_merge($request->all(), [
            'tenant_id' => $tenantId,
            'status' => 'active',
        ]));

        return response()->json([
            'success' => true,
            'crop' => $crop,
        ]);
    }

    /**
     * List crop cycles
     */
    public function listCrops(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $crops = CropCycle::where('tenant_id', $tenantId)
            ->orderBy('planting_date', 'desc')
            ->paginate(20);

        return response()->json($crops);
    }
}
