<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\MedicalWaste;
use App\Services\DashboardCacheService;
use Illuminate\Http\Request;

class MedicalWasteController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = MedicalWaste::with(['generatedBy', 'disposedBy'])->where('tenant_id', $tenantId);

        if ($request->filled('waste_type')) {
            $query->where('waste_type', $request->waste_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $waste = $query->orderBy('generated_at', 'desc')->paginate(20)->withQueryString();

        // Optimized stats with single query + caching
        $cacheKey = "stats:medical_waste:{$tenantId}";
        $statistics = DashboardCacheService::getStats($cacheKey, function () use ($tenantId) {
            $stats = MedicalWaste::where('tenant_id', $tenantId)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = \'collected\' THEN 1 ELSE 0 END) as collected,
                    SUM(CASE WHEN status = \'disposed\' THEN 1 ELSE 0 END) as disposed,
                    SUM(weight_kg) as total_weight_kg
                ')
                ->first();

            return [
                'total' => $stats->total ?? 0,
                'pending' => $stats->pending ?? 0,
                'collected' => $stats->collected ?? 0,
                'disposed' => $stats->disposed ?? 0,
                'total_weight_kg' => $stats->total_weight_kg ?? 0,
            ];
        }, 300);

        return view('healthcare.medical-waste.index', compact('waste', 'statistics'));
    }

    public function create()
    {
        return view('healthcare.medical-waste.create');
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'waste_type' => 'required|in:infectious,hazardous,pharmaceutical,sharps,general,pathological',
            'weight_kg' => 'required|numeric|min:0',
            'generated_at' => 'required|date',
            'location' => 'required|string|max:255',
            'generated_by' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $validated['waste_code'] = 'MW-' . now()->format('Ymd') . '-' . str_pad(MedicalWaste::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        $validated['status'] = 'pending';
        $validated['tenant_id'] = $tenantId;

        $waste = MedicalWaste::create($validated);

        // Clear cache
        DashboardCacheService::clearStats("stats:medical_waste:{$tenantId}");

        return redirect()->route('healthcare.medical-waste.show', $waste)
            ->with('success', 'Medical waste logged');
    }

    public function show(MedicalWaste $waste)
    {
        $waste->load(['generatedBy', 'disposedBy']);
        return view('healthcare.medical-waste.show', compact('waste'));
    }

    public function collect(MedicalWaste $waste)
    {
        $tenantId = auth()->user()->tenant_id;

        $waste->update([
            'status' => 'collected',
            'collected_at' => now(),
            'collected_by' => auth()->id(),
        ]);

        // Clear cache
        DashboardCacheService::clearStats("stats:medical_waste:{$tenantId}");

        return response()->json(['success' => true, 'message' => 'Waste collected']);
    }

    public function dispose(Request $request, MedicalWaste $waste)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'disposal_method' => 'required|in:incineration,autoclaving,landfill,chemical_treatment',
            'disposal_location' => 'required|string|max:255',
            'disposed_by' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $waste->update([
            'status' => 'disposed',
            'disposal_method' => $validated['disposal_method'],
            'disposal_location' => $validated['disposal_location'],
            'disposed_by' => $validated['disposed_by'],
            'disposed_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Clear cache
        DashboardCacheService::clearStats("stats:medical_waste:{$tenantId}");

        return response()->json(['success' => true, 'message' => 'Waste disposed']);
    }

    public function destroy(MedicalWaste $waste)
    {
        $tenantId = auth()->user()->tenant_id;

        $waste->delete();

        // Clear cache
        DashboardCacheService::clearStats("stats:medical_waste:{$tenantId}");

        return response()->json(['success' => true, 'message' => 'Waste record deleted']);
    }
    /**
     * Show the form for editing.
     * Route: healthcare/medical-waste/{medical_waste}/edit
     */
    public function edit($model)
    {
        $this->authorize('update', $model);
        
        return view('healthcare.medical-waste.edit', compact('model'));
    }
    /**
     * Update the specified resource.
     * Route: healthcare/medical-waste/{medical_waste}
     */
    public function update(Request $request, $model)
    {
        $this->authorize('update', $model);
        
        $validated = $request->validate([
            // TODO: Add validation rules
        ]);
        
        $model->update($validated);
        
        return redirect()->route('healthcare.medical-waste.update')
            ->with('success', 'Updated successfully.');
    }
}
