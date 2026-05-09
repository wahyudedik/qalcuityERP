<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\FarmPlot;
use App\Models\FarmPlotActivity;
use App\Services\FarmAnalyticsService;
use Illuminate\Http\Request;

class FarmPlotController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $query = FarmPlot::where('tenant_id', $this->tid())->where('is_active', true);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('code', 'like', "%$s%")
                ->orWhere('name', 'like', "%$s%")
                ->orWhere('current_crop', 'like', "%$s%"));
        }

        $plots = $query->withCount('activities')->orderBy('code')->paginate(20)->withQueryString();

        $stats = [
            'total' => FarmPlot::where('tenant_id', $this->tid())->where('is_active', true)->count(),
            'total_area' => FarmPlot::where('tenant_id', $this->tid())->where('is_active', true)->sum('area_size'),
            'planted' => FarmPlot::where('tenant_id', $this->tid())->whereIn('status', ['planted', 'growing'])->count(),
            'ready_harvest' => FarmPlot::where('tenant_id', $this->tid())->where('status', 'ready_harvest')->count(),
            'idle' => FarmPlot::where('tenant_id', $this->tid())->where('status', 'idle')->count(),
        ];

        return view('farm.plots', compact('plots', 'stats'));
    }

    public function show(FarmPlot $farmPlot)
    {
        abort_if($farmPlot->tenant_id !== $this->tid(), 403);
        $farmPlot->load(['activities.user']);

        $costByType = FarmPlotActivity::where('farm_plot_id', $farmPlot->id)
            ->selectRaw('activity_type, SUM(cost) as total_cost, COUNT(*) as count')
            ->groupBy('activity_type')
            ->get();

        return view('farm.plot-show', compact('farmPlot', 'costByType'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:255',
            'area_size' => 'required|numeric|min:0.001',
            'area_unit' => 'nullable|in:ha,m2,are',
            'location' => 'nullable|string|max:255',
            'soil_type' => 'nullable|string|max:50',
            'irrigation_type' => 'nullable|string|max:50',
            'ownership' => 'nullable|in:owned,rented,shared',
            'rent_cost' => 'nullable|numeric|min:0',
            'current_crop' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $tid = $this->tid();
        if (FarmPlot::where('tenant_id', $tid)->where('code', $data['code'])->exists()) {
            return back()->withErrors(['code' => "Kode lahan \"{$data['code']}\" sudah digunakan."])->withInput();
        }

        FarmPlot::create(array_merge($data, [
            'tenant_id' => $tid,
            'status' => 'idle',
            'is_active' => true,
        ]));

        ActivityLog::record('farm_plot_created', "Lahan dibuat: {$data['code']} — {$data['name']}");

        return back()->with('success', "Lahan {$data['code']} berhasil ditambahkan.");
    }

    public function update(Request $request, FarmPlot $farmPlot)
    {
        abort_if($farmPlot->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'area_size' => 'required|numeric|min:0.001',
            'area_unit' => 'nullable|in:ha,m2,are',
            'location' => 'nullable|string|max:255',
            'soil_type' => 'nullable|string|max:50',
            'irrigation_type' => 'nullable|string|max:50',
            'ownership' => 'nullable|in:owned,rented,shared',
            'rent_cost' => 'nullable|numeric|min:0',
            'current_crop' => 'nullable|string|max:100',
            'status' => 'nullable|in:idle,preparing,planted,growing,ready_harvest,harvesting,post_harvest',
            'planted_at' => 'nullable|date',
            'expected_harvest' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $farmPlot->update($data);

        return back()->with('success', "Lahan {$farmPlot->code} berhasil diperbarui.");
    }

    public function updateStatus(Request $request, FarmPlot $farmPlot)
    {
        abort_if($farmPlot->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'status' => 'required|in:idle,preparing,planted,growing,ready_harvest,harvesting,post_harvest',
            'current_crop' => 'nullable|string|max:100',
            'planted_at' => 'nullable|date',
            'expected_harvest' => 'nullable|date',
        ]);

        $farmPlot->update($data);

        ActivityLog::record('farm_plot_status', "Status lahan {$farmPlot->code} → {$farmPlot->statusLabel()}");

        return back()->with('success', "Status lahan {$farmPlot->code} diperbarui ke {$farmPlot->statusLabel()}.");
    }

    public function destroy(FarmPlot $farmPlot)
    {
        abort_if($farmPlot->tenant_id !== $this->tid(), 403);
        $farmPlot->update(['is_active' => false]);

        return back()->with('success', "Lahan {$farmPlot->code} dinonaktifkan.");
    }

    // ── Analytics ──────────────────────────────────────────────────

    public function analytics()
    {
        $svc = app(FarmAnalyticsService::class);
        $tid = $this->tid();

        $comparison = $svc->comparePlots($tid);

        // Sort by HPP (lowest = most efficient)
        $ranked = collect($comparison)->filter(fn ($p) => $p['hpp_per_kg'] !== null)->sortBy('hpp_per_kg')->values();

        // Totals
        $totalCost = collect($comparison)->sum('total_cost');
        $totalHarvest = collect($comparison)->sum('total_harvest');
        $totalArea = FarmPlot::where('tenant_id', $tid)->where('is_active', true)->sum('area_size');
        $avgHpp = $totalHarvest > 0 ? round($totalCost / $totalHarvest, 2) : null;
        $avgYieldPerHa = $totalArea > 0 ? round($totalHarvest / $totalArea, 1) : 0;

        return view('farm.analytics', compact('comparison', 'ranked', 'totalCost', 'totalHarvest', 'totalArea', 'avgHpp', 'avgYieldPerHa'));
    }

    // ── Activities ─────────────────────────────────────────────────

    public function storeActivity(Request $request, FarmPlot $farmPlot)
    {
        abort_if($farmPlot->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'activity_type' => 'required|in:planting,fertilizing,spraying,watering,weeding,pruning,harvesting,soil_prep,other',
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'input_product' => 'nullable|string|max:100',
            'input_quantity' => 'nullable|numeric|min:0',
            'input_unit' => 'nullable|string|max:20',
            'cost' => 'nullable|numeric|min:0',
            'harvest_qty' => 'nullable|numeric|min:0',
            'harvest_unit' => 'nullable|string|max:20',
            'harvest_grade' => 'nullable|string|max:30',
            'notes' => 'nullable|string',
        ]);

        FarmPlotActivity::create(array_merge($data, [
            'farm_plot_id' => $farmPlot->id,
            'tenant_id' => $this->tid(),
            'user_id' => auth()->id(),
        ]));

        // Auto-update plot status based on activity
        $autoStatus = match ($data['activity_type']) {
            'soil_prep' => 'preparing',
            'planting' => 'planted',
            'harvesting' => 'harvesting',
            default => null,
        };
        if ($autoStatus && $farmPlot->status !== $autoStatus) {
            $updates = ['status' => $autoStatus];
            if ($data['activity_type'] === 'planting') {
                $updates['planted_at'] = $data['date'];
            }
            $farmPlot->update($updates);
        }

        ActivityLog::record('farm_activity', "{$data['activity_type']} di lahan {$farmPlot->code}: {$data['description']}");

        return back()->with('success', 'Aktivitas berhasil dicatat.');
    }
}
