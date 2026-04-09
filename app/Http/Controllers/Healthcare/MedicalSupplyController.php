<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\MedicalSupply;
use App\Services\DashboardCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicalSupplyController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = MedicalSupply::where('tenant_id', $tenantId);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $supplies = $query->orderBy('supply_name')->paginate(20)->withQueryString();

        // Optimized stats with single query + caching
        $cacheKey = "stats:medical_supplies:{$tenantId}";
        $statistics = DashboardCacheService::getStats($cacheKey, function () use ($tenantId) {
            $supplies = MedicalSupply::where('tenant_id', $tenantId)
                ->selectRaw('
                    COUNT(*) as total_items,
                    SUM(CASE WHEN quantity > 0 THEN 1 ELSE 0 END) as in_stock,
                    SUM(CASE WHEN quantity > 0 AND quantity <= reorder_level THEN 1 ELSE 0 END) as low_stock,
                    SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
                    SUM(quantity * unit_cost) as total_value
                ')
                ->first();

            return [
                'total_items' => $supplies->total_items ?? 0,
                'in_stock' => $supplies->in_stock ?? 0,
                'low_stock' => $supplies->low_stock ?? 0,
                'out_of_stock' => $supplies->out_of_stock ?? 0,
                'total_value' => $supplies->total_value ?? 0,
            ];
        }, 300);

        return view('healthcare.medical-supplies.index', compact('supplies', 'statistics'));
    }

    public function create()
    {
        return view('healthcare.medical-supplies.create');
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'supply_code' => 'required|string|unique:medical_supplies,supply_code|max:50',
            'supply_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'unit_of_measure' => 'required|string|max:50',
            'unit_cost' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'supplier' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = $validated['quantity'] > 0 ? 'in_stock' : 'out_of_stock';
        $validated['tenant_id'] = $tenantId;

        $supply = MedicalSupply::create($validated);

        // Clear cache
        DashboardCacheService::clearStats("stats:medical_supplies:{$tenantId}");

        return redirect()->route('healthcare.medical-supplies.show', $supply)
            ->with('success', 'Supply added successfully');
    }

    public function show(MedicalSupply $supply)
    {
        $supply->load(['usageLogs']);
        return view('healthcare.medical-supplies.show', compact('supply'));
    }

    public function edit(MedicalSupply $supply)
    {
        return view('healthcare.medical-supplies.edit', compact('supply'));
    }

    public function update(Request $request, MedicalSupply $supply)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'supply_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'unit_of_measure' => 'required|string|max:50',
            'unit_cost' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'supplier' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = $validated['quantity'] > 0 ? 'in_stock' : 'out_of_stock';

        $supply->update($validated);

        // Clear cache
        DashboardCacheService::clearStats("stats:medical_supplies:{$tenantId}");

        return redirect()->route('healthcare.medical-supplies.index')
            ->with('success', 'Supply updated successfully');
    }

    public function adjustStock(Request $request, MedicalSupply $supply)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'adjustment_type' => 'required|in:add,subtract',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        $adjustment = $validated['adjustment_type'] === 'add' ? $validated['quantity'] : -$validated['quantity'];
        $supply->increment('quantity', $adjustment);
        $supply->update(['status' => $supply->quantity > 0 ? 'in_stock' : 'out_of_stock']);

        $supply->usageLogs()->create([
            'adjustment_type' => $validated['adjustment_type'],
            'quantity' => $validated['quantity'],
            'reason' => $validated['reason'],
            'user_id' => auth()->id(),
        ]);

        // Clear cache
        DashboardCacheService::clearStats("stats:medical_supplies:{$tenantId}");

        return response()->json(['success' => true, 'message' => 'Stock adjusted']);
    }

    public function destroy(MedicalSupply $supply)
    {
        $tenantId = auth()->user()->tenant_id;

        $supply->delete();

        // Clear cache
        DashboardCacheService::clearStats("stats:medical_supplies:{$tenantId}");

        return response()->json(['success' => true, 'message' => 'Supply deleted']);
    }
}
