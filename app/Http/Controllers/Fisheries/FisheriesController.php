<?php

namespace App\Http\Controllers\Fisheries;

use App\Http\Controllers\Controller;
use App\Models\AquaculturePond;
use App\Models\ColdStorageUnit;
use App\Models\FishingVessel;
use App\Services\Fisheries\AquacultureManagementService;
use App\Services\Fisheries\CatchTrackingService;
use App\Services\Fisheries\ColdChainMonitoringService;
use App\Services\Fisheries\ExportDocumentationService;
use App\Services\Fisheries\SpeciesCatalogService;
use Illuminate\Http\Request;

class FisheriesController extends Controller
{
    protected $coldChainService;

    protected $catchService;

    protected $speciesService;

    protected $aquacultureService;

    protected $exportService;

    public function __construct()
    {
        $this->coldChainService = new ColdChainMonitoringService;
        $this->catchService = new CatchTrackingService;
        $this->speciesService = new SpeciesCatalogService;
        $this->aquacultureService = new AquacultureManagementService;
        $this->exportService = new ExportDocumentationService;
    }

    // ==========================================
    // COLD CHAIN MANAGEMENT ENDPOINTS
    // ==========================================

    /**
     * List cold storage units
     */
    public function listColdStorageUnits()
    {
        $units = ColdStorageUnit::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'data' => $units]);
    }

    /**
     * Create cold storage unit
     */
    public function createColdStorageUnit(Request $request)
    {
        $validated = $request->validate([
            'unit_code' => 'required|string|unique:cold_storage_units',
            'name' => 'required|string',
            'type' => 'nullable|string',
            'capacity' => 'required|numeric',
            'min_temperature' => 'required|numeric',
            'max_temperature' => 'required|numeric',
            'location' => 'nullable|string',
            'sensor_id' => 'nullable|string',
        ]);

        $unit = ColdStorageUnit::create([
            'tenant_id' => auth()->user()->tenant_id,
            ...$validated,
        ]);

        return response()->json(['success' => true, 'data' => $unit], 201);
    }

    /**
     * Log temperature
     */
    public function logTemperature(Request $request, int $storageUnitId)
    {
        $validated = $request->validate([
            'temperature' => 'required|numeric',
            'humidity' => 'nullable|numeric',
            'sensor_id' => 'nullable|string',
        ]);

        $result = $this->coldChainService->monitorTemperature(
            $storageUnitId,
            $validated['temperature'],
            $validated['humidity'] ?? null,
            $validated['sensor_id'] ?? null
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get temperature history
     */
    public function getTemperatureHistory(Request $request, int $storageUnitId)
    {
        $history = $this->coldChainService->getTemperatureHistory(
            $storageUnitId,
            $request->start_date,
            $request->end_date
        );

        return response()->json(['success' => true, 'data' => $history]);
    }

    /**
     * Get active alerts
     */
    public function getActiveAlerts(Request $request)
    {
        $alerts = $this->coldChainService->getActiveAlerts(
            auth()->user()->tenant_id,
            $request->severity
        );

        return response()->json(['success' => true, 'data' => $alerts]);
    }

    /**
     * Acknowledge alert
     */
    public function acknowledgeAlert(int $alertId)
    {
        $success = $this->coldChainService->acknowledgeAlert($alertId, auth()->id());

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Alert acknowledged' : 'Failed to acknowledge alert',
        ]);
    }

    /**
     * Resolve alert
     */
    public function resolveAlert(Request $request, int $alertId)
    {
        $validated = $request->validate(['resolution_notes' => 'required|string']);
        $success = $this->coldChainService->resolveAlert($alertId, $validated['resolution_notes']);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Alert resolved' : 'Failed to resolve alert',
        ]);
    }

    /**
     * Generate compliance report
     */
    public function generateComplianceReport(Request $request)
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $report = $this->coldChainService->generateComplianceReport(
            auth()->user()->tenant_id,
            $validated['period_start'],
            $validated['period_end']
        );

        return response()->json(['success' => true, 'data' => $report]);
    }

    // ==========================================
    // FISHING OPERATIONS ENDPOINTS
    // ==========================================

    /**
     * List fishing vessels
     */
    public function listVessels()
    {
        $vessels = FishingVessel::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('vessel_name')
            ->get();

        return response()->json(['success' => true, 'data' => $vessels]);
    }

    /**
     * Register vessel
     */
    public function registerVessel(Request $request)
    {
        $validated = $request->validate([
            'vessel_name' => 'required|string',
            'registration_number' => 'required|string|unique:fishing_vessels',
            'vessel_type' => 'nullable|string',
            'gross_tonnage' => 'nullable|numeric',
            'crew_capacity' => 'nullable|integer',
            'fuel_capacity' => 'nullable|numeric',
            'storage_capacity' => 'nullable|numeric',
            'home_port' => 'nullable|string',
            'license_expiry_date' => 'nullable|date',
        ]);

        $vessel = FishingVessel::create([
            'tenant_id' => auth()->user()->tenant_id,
            ...$validated,
        ]);

        return response()->json(['success' => true, 'data' => $vessel], 201);
    }

    /**
     * Plan fishing trip
     */
    public function planTrip(Request $request)
    {
        $validated = $request->validate([
            'vessel_id' => 'required|exists:fishing_vessels,id',
            'captain_id' => 'required|exists:employees,id',
            'fishing_zone_id' => 'nullable|exists:fishing_zones,id',
            'crew_ids' => 'nullable|array',
            'departure_time' => 'nullable|date',
        ]);

        $result = $this->catchService->planTrip(
            auth()->user()->tenant_id,
            $validated['vessel_id'],
            $validated['captain_id'],
            $validated['fishing_zone_id'] ?? null,
            $validated['crew_ids'] ?? [],
            $validated['departure_time'] ?? null
        );

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Start fishing trip
     */
    public function startTrip(int $tripId)
    {
        $success = $this->catchService->startTrip($tripId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Trip started' : 'Failed to start trip',
        ]);
    }

    /**
     * Record catch
     */
    public function recordCatch(Request $request, int $tripId)
    {
        $validated = $request->validate([
            'species_id' => 'required|exists:fish_species,id',
            'quantity' => 'required|numeric|min:0',
            'total_weight' => 'required|numeric|min:0',
            'grade_id' => 'nullable|exists:quality_grades,id',
            'freshness_score' => 'nullable|numeric|min:0|max:10',
            'catch_method' => 'nullable|string',
            'depth' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $result = $this->catchService->recordCatch(
            $tripId,
            $validated['species_id'],
            $validated['quantity'],
            $validated['total_weight'],
            $validated['grade_id'] ?? null,
            $validated['freshness_score'] ?? null,
            $validated['catch_method'] ?? null,
            $validated['depth'] ?? null,
            $validated['latitude'] ?? null,
            $validated['longitude'] ?? null
        );

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Update trip position
     */
    public function updatePosition(Request $request, int $tripId)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $success = $this->catchService->updatePosition(
            $tripId,
            $validated['latitude'],
            $validated['longitude']
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Position updated' : 'Failed to update position',
        ]);
    }

    /**
     * Complete trip
     */
    public function completeTrip(Request $request, int $tripId)
    {
        $validated = $request->validate([
            'fuel_consumed' => 'nullable|numeric|min:0',
            'return_time' => 'nullable|date',
        ]);

        $result = $this->catchService->completeTrip(
            $tripId,
            $validated['fuel_consumed'] ?? null,
            $validated['return_time'] ?? null
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get trip summary
     */
    public function getTripSummary(int $tripId)
    {
        $summary = $this->catchService->getTripSummary($tripId);

        return response()->json(['success' => true, 'data' => $summary]);
    }

    /**
     * Get catch analytics
     */
    public function getCatchAnalytics(Request $request)
    {
        $analytics = $this->catchService->getCatchAnalytics(
            auth()->user()->tenant_id,
            $request->period
        );

        return response()->json(['success' => true, 'data' => $analytics]);
    }

    // ==========================================
    // SPECIES & QUALITY ENDPOINTS
    // ==========================================

    /**
     * List species
     */
    public function listSpecies(Request $request)
    {
        $species = $this->speciesService->listSpecies(
            auth()->user()->tenant_id,
            $request->category,
            $request->search
        );

        return response()->json(['success' => true, 'data' => $species]);
    }

    /**
     * Add species
     */
    public function addSpecies(Request $request)
    {
        $validated = $request->validate([
            'species_code' => 'required|string|unique:fish_species',
            'common_name' => 'required|string',
            'scientific_name' => 'nullable|string',
            'category' => 'nullable|string',
            'market_price_per_kg' => 'nullable|numeric|min:0',
        ]);

        $species = $this->speciesService->addSpecies(auth()->user()->tenant_id, $validated);

        return response()->json(['success' => true, 'data' => $species], 201);
    }

    /**
     * Add quality grade
     */
    public function addQualityGrade(Request $request)
    {
        $validated = $request->validate([
            'grade_code' => 'required|string|max:10',
            'grade_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price_multiplier' => 'nullable|numeric|min:0',
        ]);

        $grade = \App\Models\QualityGrade::create([
            'tenant_id' => auth()->user()->tenant_id,
            ...$validated,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $grade], 201);
        }

        return back()->with('success', 'Grade berhasil ditambahkan.');
    }

    /**
     * Assess freshness
     */
    public function assessFreshness(Request $request, int $catchLogId)
    {
        $validated = $request->validate([
            'overall_score' => 'required|numeric|min:0|max:10',
            'criteria' => 'nullable|array',
            'assessment_type' => 'nullable|string',
        ]);

        $assessment = $this->speciesService->assessFreshness(
            $catchLogId,
            $validated['overall_score'],
            $validated['criteria'] ?? [],
            auth()->id(),
            $validated['assessment_type'] ?? 'visual'
        );

        return response()->json(['success' => true, 'data' => $assessment], 201);
    }

    /**
     * Calculate market value
     */
    public function calculateMarketValue(Request $request)
    {
        $validated = $request->validate([
            'species_id' => 'required|exists:fish_species,id',
            'weight' => 'required|numeric|min:0',
            'grade_id' => 'nullable|exists:quality_grades,id',
        ]);

        $value = $this->speciesService->calculateMarketValue(
            $validated['species_id'],
            $validated['weight'],
            $validated['grade_id'] ?? null
        );

        return response()->json(['success' => true, 'estimated_value' => $value]);
    }

    // ==========================================
    // AQUACULTURE ENDPOINTS
    // ==========================================

    /**
     * List ponds
     */
    public function listPonds()
    {
        $ponds = AquaculturePond::where('tenant_id', auth()->user()->tenant_id)
            ->with('currentSpecies')
            ->orderBy('pond_name')
            ->get();

        return response()->json(['success' => true, 'data' => $ponds]);
    }

    /**
     * Create pond
     */
    public function createPond(Request $request)
    {
        $validated = $request->validate([
            'pond_code' => 'required|string|unique:aquaculture_ponds',
            'pond_name' => 'required|string',
            'surface_area' => 'required|numeric|min:0',
            'depth' => 'required|numeric|min:0',
            'volume' => 'required|numeric|min:0',
            'carrying_capacity' => 'required|numeric|min:0',
        ]);

        $pond = $this->aquacultureService->createPond(auth()->user()->tenant_id, $validated);

        return response()->json(['success' => true, 'data' => $pond], 201);
    }

    /**
     * Stock pond
     */
    public function stockPond(Request $request, int $pondId)
    {
        $validated = $request->validate([
            'species_id' => 'required|exists:fish_species,id',
            'quantity' => 'required|numeric|min:0',
            'stocking_date' => 'nullable|date',
        ]);

        $success = $this->aquacultureService->stockPond(
            $pondId,
            $validated['species_id'],
            $validated['quantity'],
            $validated['stocking_date'] ?? null
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Pond stocked' : 'Failed to stock pond',
        ]);
    }

    /**
     * Log water quality
     */
    public function logWaterQuality(Request $request, int $pondId)
    {
        $validated = $request->validate([
            'ph_level' => 'nullable|numeric|min:0|max:14',
            'dissolved_oxygen' => 'nullable|numeric|min:0',
            'temperature' => 'nullable|numeric',
            'salinity' => 'nullable|numeric|min:0',
            'ammonia' => 'nullable|numeric|min:0',
        ]);

        $log = $this->aquacultureService->logWaterQuality(
            auth()->user()->tenant_id,
            $pondId,
            null,
            $validated,
            auth()->id()
        );

        return response()->json(['success' => true, 'data' => $log], 201);
    }

    /**
     * Get pond dashboard
     */
    public function getPondDashboard(int $pondId)
    {
        $dashboard = $this->aquacultureService->getPondDashboard($pondId);

        return response()->json(['success' => true, 'data' => $dashboard]);
    }

    /**
     * Record feeding
     */
    public function recordFeeding(Request $request, int $scheduleId)
    {
        $validated = $request->validate([
            'actual_quantity' => 'required|numeric|min:0',
        ]);

        $success = $this->aquacultureService->recordFeeding(
            $scheduleId,
            $validated['actual_quantity'],
            auth()->id()
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Feeding recorded' : 'Failed to record feeding',
        ]);
    }

    /**
     * Record mortality
     */
    public function recordMortality(Request $request)
    {
        $validated = $request->validate([
            'pond_id' => 'nullable|exists:aquaculture_ponds,id',
            'count' => 'required|integer|min:1',
            'total_weight' => 'nullable|numeric|min:0',
            'cause_of_death' => 'nullable|string',
            'symptoms' => 'nullable|string',
        ]);

        $mortality = $this->aquacultureService->recordMortality(
            auth()->user()->tenant_id,
            $validated['pond_id'] ?? null,
            null,
            $validated['count'],
            $validated['total_weight'] ?? null,
            $validated['cause_of_death'] ?? null,
            $validated['symptoms'] ?? null,
            auth()->id()
        );

        return response()->json(['success' => true, 'data' => $mortality], 201);
    }

    // ==========================================
    // EXPORT DOCUMENTATION ENDPOINTS
    // ==========================================

    /**
     * Apply for export permit
     */
    public function applyForPermit(Request $request)
    {
        $validated = $request->validate([
            'destination_country' => 'required|string',
            'expiry_date' => 'required|date|after:today',
            'issuing_authority' => 'required|string',
        ]);

        $permit = $this->exportService->applyForPermit(auth()->user()->tenant_id, $validated);

        return response()->json(['success' => true, 'data' => $permit], 201);
    }

    /**
     * Issue health certificate
     */
    public function issueHealthCertificate(Request $request)
    {
        $validated = $request->validate([
            'catch_log_id' => 'nullable|exists:catch_logs,id',
            'expiry_date' => 'required|date|after:today',
            'issued_by' => 'required|string',
            'issuing_authority' => 'required|string',
        ]);

        $certificate = $this->exportService->issueHealthCertificate(
            auth()->user()->tenant_id,
            $validated
        );

        return response()->json(['success' => true, 'data' => $certificate], 201);
    }

    /**
     * Create customs declaration
     */
    public function createCustomsDeclaration(Request $request)
    {
        $validated = $request->validate([
            'hs_code' => 'required|string',
            'destination_country' => 'required|string',
            'declared_value' => 'required|numeric|min:0',
            'total_weight' => 'required|numeric|min:0',
            'goods_description' => 'required|string',
        ]);

        $declaration = $this->exportService->createCustomsDeclaration(
            auth()->user()->tenant_id,
            $validated
        );

        return response()->json(['success' => true, 'data' => $declaration], 201);
    }

    /**
     * Submit customs declaration
     */
    public function submitCustomsDeclaration(int $declarationId)
    {
        $success = $this->exportService->submitCustomsDeclaration($declarationId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Declaration submitted' : 'Failed to submit',
        ]);
    }

    /**
     * Create shipment
     */
    public function createShipment(Request $request)
    {
        $validated = $request->validate([
            'origin_port' => 'required|string',
            'destination_port' => 'required|string',
            'shipping_method' => 'nullable|string',
        ]);

        $shipment = $this->exportService->createShipment(auth()->user()->tenant_id, $validated);

        return response()->json(['success' => true, 'data' => $shipment], 201);
    }

    /**
     * Update shipment status
     */
    public function updateShipmentStatus(Request $request, int $shipmentId)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:preparing,in_transit,arrived,delivered,cancelled',
            'actual_arrival' => 'nullable|date',
        ]);

        $success = $this->exportService->updateShipmentStatus(
            $shipmentId,
            $validated['status'],
            $validated['actual_arrival'] ?? null
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Status updated' : 'Failed to update status',
        ]);
    }

    /**
     * Get export documents
     */
    public function getExportDocuments(Request $request)
    {
        $documents = $this->exportService->getExportDocuments(
            auth()->user()->tenant_id,
            $request->type
        );

        return response()->json(['success' => true, 'data' => $documents]);
    }

    /**
     * Validate export readiness
     */
    public function validateExportReadiness(int $shipmentId)
    {
        $readiness = $this->exportService->validateExportReadiness($shipmentId);

        return response()->json(['success' => true, 'data' => $readiness]);
    }
}
