<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\InventoryCostingService;
use Illuminate\Http\Request;

class InventoryCostingController extends Controller
{
    public function __construct(private InventoryCostingService $costing) {}

    // tenantId() inherited from parent Controller

    /** Valuation report page */
    public function valuation()
    {
        $report = $this->costing->valuationReport($this->tenantId());
        $tenant = Tenant::find($this->tenantId());
        return view('inventory.costing.valuation', compact('report', 'tenant'));
    }

    /** COGS report page */
    public function cogs(Request $request)
    {
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();
        $report = $this->costing->cogsReport($this->tenantId(), $from, $to);
        $tenant = Tenant::find($this->tenantId());
        return view('inventory.costing.cogs', compact('report', 'tenant', 'from', 'to'));
    }

    /** Update costing method setting */
    public function updateMethod(Request $request)
    {
        $request->validate(['costing_method' => 'required|in:simple,avco,fifo']);
        Tenant::find($this->tenantId())->update(['costing_method' => $request->costing_method]);
        return back()->with('success', 'Metode kalkulasi biaya berhasil diperbarui.');
    }

    /** API: current cost for a product (used by inventory UI) */
    public function currentCost(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|integer',
            'warehouse_id' => 'required|integer',
        ]);
        $cost = $this->costing->getCurrentCost(
            $this->tenantId(),
            $request->product_id,
            $request->warehouse_id,
        );
        return response()->json(['cost' => $cost]);
    }
}
