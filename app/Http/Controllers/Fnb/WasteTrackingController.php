<?php

namespace App\Http\Controllers\Fnb;

use App\Http\Controllers\Controller;
use App\Models\IngredientWaste;
use App\Models\InventoryItem;
use App\Services\IngredientWasteTrackingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WasteTrackingController extends Controller
{
    protected $wasteService;

    public function __construct(IngredientWasteTrackingService $wasteService)
    {
        $this->wasteService = $wasteService;
    }

    /**
     * Display waste tracking dashboard
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : now()->startOfMonth();
        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))
            : now()->endOfMonth();

        $stats = $this->wasteService->getWasteStats($tenantId, $startDate, $endDate);
        $trends = $this->wasteService->getWasteTrends($tenantId, 30);
        $recommendations = $this->wasteService->generateRecommendations($tenantId);

        $recentWastes = IngredientWaste::where('tenant_id', $tenantId)
            ->dateRange($startDate, $endDate)
            ->orderBy('wasted_at', 'desc')
            ->limit(20)
            ->get();

        return view('fnb.waste.index', compact('stats', 'trends', 'recommendations', 'recentWastes', 'startDate', 'endDate'));
    }

    /**
     * Record new waste
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'nullable|exists:inventory_items,id',
            'item_name' => 'required|string|max:255',
            'quantity_wasted' => 'required|numeric|min:0.001',
            'unit' => 'required|string|max:50',
            'cost_per_unit' => 'required|numeric|min:0',
            'waste_type' => 'required|in:spoilage,over_production,preparation_error,expired,other',
            'reason' => 'nullable|string|max:500',
            'department' => 'required|in:kitchen,bar,storage',
            'preventive_action' => 'nullable|string|max:500',
            'wasted_at' => 'nullable|date',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        // If inventory item selected, get its cost
        if ($validated['inventory_item_id']) {
            $item = InventoryItem::find($validated['inventory_item_id']);
            if ($item && !$request->filled('cost_per_unit')) {
                $validated['cost_per_unit'] = $item->unit_cost ?? 0;
            }
            if ($item && !$request->filled('item_name')) {
                $validated['item_name'] = $item->name;
            }
        }

        $waste = $this->wasteService->recordWaste($validated);

        return redirect()->route('fnb.waste.index')
            ->with('success', 'Waste recorded successfully');
    }

    /**
     * Show waste by item report
     */
    public function wasteByItem(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $daysBack = $request->input('days', 30);

        $wasteByItem = $this->wasteService->getWasteByItem($tenantId, $daysBack);

        return view('fnb.waste.by-item', compact('wasteByItem', 'daysBack'));
    }

    /**
     * Show common waste reasons
     */
    public function wasteReasons(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $daysBack = $request->input('days', 30);

        $reasons = $this->wasteService->getCommonWasteReasons($tenantId, $daysBack);

        return view('fnb.waste.reasons', compact('reasons', 'daysBack'));
    }

    /**
     * Export waste report
     */
    public function export(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : now()->startOfMonth();
        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))
            : now()->endOfMonth();

        $report = $this->wasteService->exportWasteReport($tenantId, $startDate, $endDate);

        return response()->json($report);
    }

    /**
     * Delete waste record
     */
    public function destroy(IngredientWaste $waste)
    {
        $this->authorizeAccess($waste);

        $waste->delete();

        return back()->with('success', 'Waste record deleted');
    }

    private function authorizeAccess($model): void
    {
        if ($model->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access');
        }
    }
}
