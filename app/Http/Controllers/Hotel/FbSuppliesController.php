<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\FbSupply;
use App\Models\FbSupplyTransaction;
use Illuminate\Http\Request;

class FbSuppliesController extends Controller
{
    // tenantId() inherited from parent Controller

    /**
     * Display F&B supplies inventory
     */
    public function index()
    {
        $supplies = FbSupply::where('tenant_id', $this->tenantId())
            ->with('category')
            ->orderBy('name')
            ->paginate(30);

        $lowStock = FbSupply::getLowStockSupplies($this->tenantId());

        $stats = [
            'total_supplies' => FbSupply::where('tenant_id', $this->tenantId())->count(),
            'low_stock_count' => $lowStock->count(),
            'out_of_stock' => FbSupply::where('tenant_id', $this->tenantId())
                ->where('current_stock', '<=', 0)
                ->count(),
            'total_inventory_value' => FbSupply::where('tenant_id', $this->tenantId())
                ->get()
                ->sum(fn($s) => $s->inventory_value),
        ];

        return view('hotel.fb.supplies.index', compact('supplies', 'lowStock', 'stats'));
    }

    /**
     * Store new supply
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'current_stock' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
            'supplier_name' => 'nullable|string|max:255',
        ]);

        $validated['tenant_id'] = $this->tenantId();

        FbSupply::create($validated);

        return redirect()->route('hotel.fb.supplies.index')
            ->with('success', 'Supply added successfully');
    }

    /**
     * Update supply
     */
    public function update(Request $request, FbSupply $supply)
    {
        if ($supply->tenant_id !== $this->tenantId()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'minimum_stock' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
            'supplier_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $supply->update($validated);

        return back()->with('success', 'Supply updated successfully');
    }

    /**
     * Add stock (restock)
     */
    public function addStock(Request $request, FbSupply $supply)
    {
        if ($supply->tenant_id !== $this->tenantId()) {
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:255',
        ]);

        $supply->addStock(
            $validated['quantity'],
            $validated['unit_cost'] ?? null,
            $validated['reference'] ?? null
        );

        return back()->with('success', 'Stock added successfully');
    }

    /**
     * Record usage/waste
     */
    public function recordUsage(Request $request, FbSupply $supply)
    {
        if ($supply->tenant_id !== $this->tenantId()) {
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'transaction_type' => 'required|in:usage,waste,adjustment',
            'notes' => 'nullable|string',
        ]);

        try {
            $supply->deductStock(
                $validated['quantity'],
                null,
                $validated['notes']
            );

            // Update transaction type
            $latestTransaction = $supply->transactions()->latest()->first();
            if ($latestTransaction) {
                $latestTransaction->update(['transaction_type' => $validated['transaction_type']]);
            }

            return back()->with('success', 'Usage recorded successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * View supply transactions
     */
    public function transactions(FbSupply $supply)
    {
        if ($supply->tenant_id !== $this->tenantId()) {
            abort(403);
        }

        $transactions = $supply->transactions()
            ->with('createdBy')
            ->orderBy('transaction_date', 'desc')
            ->paginate(50);

        return view('hotel.fb.supplies.transactions', compact('supply', 'transactions'));
    }
}
