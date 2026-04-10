<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\SterilizationCycle;
use App\Models\MedicalEquipment;
use App\Services\DashboardCacheService;
use Illuminate\Http\Request;

class SterilizationController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = SterilizationCycle::with(['equipment', 'operator'])->where('tenant_id', $tenantId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sterilization_method')) {
            $query->where('sterilization_method', $request->sterilization_method);
        }

        $cycles = $query->orderBy('start_time', 'desc')->paginate(20)->withQueryString();

        // Optimized stats with caching
        $cacheKey = "stats:sterilization:{$tenantId}";
        $statistics = DashboardCacheService::getStats($cacheKey, function () use ($tenantId) {
            $stats = SterilizationCycle::where('tenant_id', $tenantId)
                ->selectRaw('
                    COUNT(*) as total_cycles,
                    SUM(CASE WHEN status = \'completed\' AND DATE(completed_at) = CURDATE() THEN 1 ELSE 0 END) as completed_today,
                    SUM(CASE WHEN status = \'in_progress\' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = \'failed\' THEN 1 ELSE 0 END) as failed
                ')
                ->first();

            return [
                'total_cycles' => $stats->total_cycles ?? 0,
                'completed_today' => $stats->completed_today ?? 0,
                'in_progress' => $stats->in_progress ?? 0,
                'failed' => $stats->failed ?? 0,
            ];
        }, 300);

        return view('healthcare.sterilization.index', compact('cycles', 'statistics'));
    }

    public function create()
    {
        $equipment = MedicalEquipment::whereIn('status', ['available', 'in_use'])->get();
        return view('healthcare.sterilization.create', compact('equipment'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'equipment_id' => 'required|exists:medical_equipment,id',
            'sterilization_method' => 'required|in:autoclave,ethylene_oxide,steam,chemical,radiation',
            'start_time' => 'required|date',
            'expected_duration' => 'required|integer|min:15',
            'temperature' => 'nullable|numeric',
            'pressure' => 'nullable|numeric',
            'operator_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $validated['cycle_number'] = 'ST-' . now()->format('Ymd') . '-' . str_pad(SterilizationCycle::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        $validated['status'] = 'in_progress';
        $validated['tenant_id'] = $tenantId;

        $cycle = SterilizationCycle::create($validated);

        // Clear cache
        DashboardCacheService::clearStats("stats:sterilization:{$tenantId}");

        return redirect()->route('healthcare.sterilization.show', $cycle)
            ->with('success', 'Sterilization cycle started');
    }

    public function show(SterilizationCycle $cycle)
    {
        $cycle->load(['equipment', 'operator', 'qualityChecks']);
        return view('healthcare.sterilization.show', compact('cycle'));
    }

    public function complete(Request $request, SterilizationCycle $cycle)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'end_time' => 'required|date|after:start_time',
            'result' => 'required|in:pass,fail',
            'notes' => 'nullable|string',
        ]);

        $cycle->update([
            'status' => $validated['result'] === 'pass' ? 'completed' : 'failed',
            'end_time' => $validated['end_time'],
            'result' => $validated['result'],
            'notes' => $validated['notes'] ?? null,
            'completed_at' => now(),
        ]);

        // Clear cache
        DashboardCacheService::clearStats("stats:sterilization:{$tenantId}");

        return response()->json(['success' => true, 'message' => 'Cycle completed']);
    }

    public function logQualityCheck(Request $request, SterilizationCycle $cycle)
    {
        $validated = $request->validate([
            'check_type' => 'required|in:biological,chemical,mechanical',
            'result' => 'required|in:pass,fail',
            'notes' => 'nullable|string',
        ]);

        $cycle->qualityChecks()->create($validated);

        return response()->json(['success' => true, 'message' => 'Quality check logged']);
    }

    public function destroy(SterilizationCycle $cycle)
    {
        $tenantId = auth()->user()->tenant_id;

        $cycle->delete();

        // Clear cache
        DashboardCacheService::clearStats("stats:sterilization:{$tenantId}");

        return response()->json(['success' => true, 'message' => 'Cycle deleted']);
    }
    /**
     * Show the form for editing.
     * Route: healthcare/sterilization/{sterilization}/edit
     */
    public function edit($model)
    {
        $this->authorize('update', $model);
        
        return view('healthcare.sterilization.edit', compact('model'));
    }
    /**
     * Update the specified resource.
     * Route: healthcare/sterilization/{sterilization}
     */
    public function update(Request $request, $model)
    {
        $this->authorize('update', $model);
        
        $validated = $request->validate([
            // TODO: Add validation rules
        ]);
        
        $model->update($validated);
        
        return redirect()->route('healthcare.sterilization.update')
            ->with('success', 'Updated successfully.');
    }
    /**
     * QualityCheck.
     * Route: healthcare/sterilization/{cycle}/quality-check
     */
    public function qualityCheck(Request $request, $model)
    {
        $this->authorize('update', $model);
        
        $validated = $request->validate([
            // TODO: Add validation rules
        ]);
        
        // TODO: Implement QualityCheck logic
        
        return back()->with('success', 'QualityCheck completed successfully.');
    }
}
