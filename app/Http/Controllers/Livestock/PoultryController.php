<?php

namespace App\Http\Controllers\Livestock;

use App\Http\Controllers\Controller;
use App\Models\LivestockHerd;
use App\Models\PoultryEggProduction;
use App\Models\PoultryFlockPerformance;
use App\Services\LivestockIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PoultryController extends Controller
{
    protected LivestockIntegrationService $integrationService;

    public function __construct(LivestockIntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    /**
     * Get authenticated user's tenant ID
     */
    // tenantId() inherited from parent Controller

    /**
     * Get authenticated user's ID
     */
    private function userId(): int
    {
        return Auth::id() ?? abort(401, 'Unauthenticated.');
    }

    /**
     * Display poultry flocks list
     */
    public function flocks(Request $request)
    {
        $tenantId = $this->tenantId();

        $stats = [
            'total_flocks' => LivestockHerd::where('tenant_id', $tenantId)
                ->whereIn('animal_type', ['ayam_broiler', 'ayam_layer', 'bebek'])
                ->count(),
            'active_flocks' => LivestockHerd::where('tenant_id', $tenantId)
                ->whereIn('animal_type', ['ayam_broiler', 'ayam_layer', 'bebek'])
                ->active()
                ->count(),
            'total_birds' => LivestockHerd::where('tenant_id', $tenantId)
                ->whereIn('animal_type', ['ayam_broiler', 'ayam_layer', 'bebek'])
                ->active()
                ->sum('initial_count'),
        ];

        $flocks = LivestockHerd::where('tenant_id', $tenantId)
            ->whereIn('animal_type', ['ayam_broiler', 'ayam_layer', 'bebek'])
            ->withCount(['eggProductions', 'flockPerformances'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livestock.poultry.flocks', compact('stats', 'flocks'));
    }

    /**
     * Display egg production records
     */
    public function eggProduction(Request $request)
    {
        $tenantId = $this->tenantId();

        $stats = [
            'total_records' => PoultryEggProduction::where('tenant_id', $tenantId)->count(),
            'today_eggs' => PoultryEggProduction::where('tenant_id', $tenantId)
                ->whereDate('record_date', today())
                ->sum('eggs_collected'),
            'avg_laying_rate' => PoultryEggProduction::where('tenant_id', $tenantId)
                ->whereBetween('record_date', [now()->subDays(7), now()])
                ->avg('laying_rate_percentage') ?? 0,
            'avg_breakage_rate' => 0,
        ];

        $records = PoultryEggProduction::with(['herd', 'recordedBy'])
            ->where('tenant_id', $tenantId)
            ->orderByDesc('record_date')
            ->paginate(20);

        $herds = LivestockHerd::where('tenant_id', $tenantId)
            ->where('animal_type', 'ayam_layer')
            ->active()
            ->get();

        return view('livestock.poultry.egg-production', compact('stats', 'records', 'herds'));
    }

    /**
     * Store egg production record
     */
    public function storeEggRecord(Request $request)
    {
        $validated = $request->validate([
            'livestock_herd_id' => 'required|exists:livestock_herds,id',
            'record_date' => 'required|date',
            'eggs_collected' => 'required|integer|min:0',
            'eggs_broken' => 'nullable|integer|min:0',
            'eggs_double_yolk' => 'nullable|integer|min:0',
            'eggs_small' => 'nullable|integer|min:0',
            'eggs_medium' => 'nullable|integer|min:0',
            'eggs_large' => 'nullable|integer|min:0',
            'eggs_extra_large' => 'nullable|integer|min:0',
            'total_weight_kg' => 'nullable|numeric|min:0',
            'laying_rate_percentage' => 'nullable|numeric|min:0|max:100',
            'feed_consumed_kg' => 'nullable|numeric|min:0',
            'feed_conversion_ratio' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            $record = new PoultryEggProduction;
            $record->tenant_id = $this->tenantId();
            $record->fill($validated);
            $record->eggs_broken = $validated['eggs_broken'] ?? 0;
            $record->recorded_by = $this->userId();
            $record->save();

            // Post journal entry for egg production (integration with Accounting)
            // Price per egg can be configured in tenant settings or passed via request
            $pricePerEgg = $request->input('price_per_egg', 0);
            if ($pricePerEgg > 0) {
                $result = $this->integrationService->postEggProduction(
                    $this->tenantId(),
                    $this->userId(),
                    $record,
                    (float) $pricePerEgg
                );
                if ($result->isFailed()) {
                    Log::warning('Egg production journal failed: '.$result->reason);
                }
            }

            return back()->with('success', 'Egg production recorded successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display flock performance
     */
    public function flockPerformance(Request $request)
    {
        $tenantId = $this->tenantId();

        $stats = [
            'total_flocks' => LivestockHerd::where('tenant_id', $tenantId)
                ->whereIn('animal_type', ['ayam_broiler', 'ayam_layer', 'bebek'])
                ->count(),
            'avg_mortality_rate' => PoultryFlockPerformance::where('tenant_id', $tenantId)
                ->whereBetween('record_date', [now()->subDays(30), now()])
                ->avg('mortality_rate_percentage') ?? 0,
            'avg_fcr' => PoultryFlockPerformance::where('tenant_id', $tenantId)
                ->whereBetween('record_date', [now()->subDays(30), now()])
                ->avg('feed_conversion_ratio') ?? 0,
        ];

        $performances = PoultryFlockPerformance::with(['herd', 'recordedBy'])
            ->where('tenant_id', $tenantId)
            ->orderByDesc('record_date')
            ->paginate(20);

        $herds = LivestockHerd::where('tenant_id', $tenantId)
            ->whereIn('animal_type', ['ayam_broiler', 'ayam_layer', 'bebek'])
            ->active()
            ->get();

        return view('livestock.poultry.flock-performance', compact('stats', 'performances', 'herds'));
    }

    /**
     * Store flock performance record
     */
    public function storePerformance(Request $request)
    {
        $validated = $request->validate([
            'livestock_herd_id' => 'required|exists:livestock_herds,id',
            'record_date' => 'required|date',
            'birds_alive' => 'required|integer|min:0',
            'mortality_count' => 'nullable|integer|min:0',
            'average_weight_kg' => 'nullable|numeric|min:0',
            'feed_consumed_kg' => 'required|numeric|min:0',
            'water_consumed_liters' => 'nullable|numeric|min:0',
            'average_daily_gain' => 'nullable|numeric',
            'feed_conversion_ratio' => 'nullable|numeric|min:0',
            'health_status' => 'nullable|in:healthy,sick,quarantine',
            'observations' => 'nullable|string',
        ]);

        try {
            $performance = new PoultryFlockPerformance;
            $performance->tenant_id = $this->tenantId();
            $performance->fill($validated);
            $performance->mortality_count = $validated['mortality_count'] ?? 0;
            $performance->health_status = $validated['health_status'] ?? 'healthy';
            $performance->recorded_by = $this->userId();

            // Calculate mortality rate
            if ($performance->birds_alive > 0) {
                $totalBirds = $performance->birds_alive + $performance->mortality_count;
                $calculatedRate = ($performance->mortality_count / $totalBirds) * 100;
                $performance->mortality_rate_percentage = round($calculatedRate, 2);
            }

            $performance->save();

            return back()->with('success', 'Flock performance recorded!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
