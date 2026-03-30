<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CropCycle;
use App\Models\FarmPlot;
use App\Models\FarmPlotActivity;
use Illuminate\Http\Request;

class CropCycleController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = CropCycle::where('tenant_id', $this->tid())
            ->with('plot');

        if ($request->phase) $query->where('phase', $request->phase);
        if ($request->search) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('crop_name', 'like', "%$s%")
                ->orWhere('number', 'like', "%$s%")
                ->orWhereHas('plot', fn ($p) => $p->where('code', 'like', "%$s%")));
        }

        $cycles = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $plots = FarmPlot::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('code')->get();

        $stats = [
            'active'    => CropCycle::where('tenant_id', $this->tid())->whereNotIn('phase', ['completed', 'cancelled'])->count(),
            'completed' => CropCycle::where('tenant_id', $this->tid())->where('phase', 'completed')->count(),
            'overdue'   => CropCycle::where('tenant_id', $this->tid())
                ->whereNotNull('plan_harvest_date')
                ->where('plan_harvest_date', '<', today())
                ->whereNotIn('phase', ['harvest', 'post_harvest', 'completed', 'cancelled'])
                ->count(),
        ];

        return view('farm.cycles', compact('cycles', 'plots', 'stats'));
    }

    public function show(CropCycle $cropCycle)
    {
        abort_if($cropCycle->tenant_id !== $this->tid(), 403);
        $cropCycle->load(['plot', 'activities.user']);

        $costByType = $cropCycle->activities()
            ->selectRaw("activity_type, SUM(cost) as total_cost, COUNT(*) as count")
            ->groupBy('activity_type')
            ->get();

        return view('farm.cycle-show', compact('cropCycle', 'costByType'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'farm_plot_id'      => 'required|exists:farm_plots,id',
            'crop_name'         => 'required|string|max:255',
            'crop_variety'      => 'nullable|string|max:100',
            'season'            => 'nullable|string|max:100',
            'plan_prep_start'   => 'nullable|date',
            'plan_plant_date'   => 'nullable|date',
            'plan_harvest_date' => 'nullable|date',
            'target_yield_qty'  => 'nullable|numeric|min:0',
            'target_yield_unit' => 'nullable|string|max:20',
            'estimated_budget'  => 'nullable|numeric|min:0',
            'seed_quantity'     => 'nullable|numeric|min:0',
            'seed_unit'         => 'nullable|string|max:20',
            'seed_source'       => 'nullable|string|max:255',
            'notes'             => 'nullable|string',
        ]);

        $plot = FarmPlot::where('tenant_id', $this->tid())->findOrFail($data['farm_plot_id']);

        $cycle = CropCycle::create(array_merge($data, [
            'tenant_id' => $this->tid(),
            'number'    => CropCycle::generateNumber($this->tid(), $plot->code),
            'phase'     => 'planning',
        ]));

        // Update plot with crop info
        $plot->update([
            'current_crop'    => $data['crop_name'],
            'expected_harvest'=> $data['plan_harvest_date'] ?? null,
        ]);

        ActivityLog::record('crop_cycle_created', "Siklus tanam dibuat: {$cycle->number} — {$data['crop_name']} di lahan {$plot->code}");
        return redirect()->route('farm.cycles.show', $cycle)->with('success', "Siklus tanam {$cycle->number} berhasil dibuat.");
    }

    public function advancePhase(Request $request, CropCycle $cropCycle)
    {
        abort_if($cropCycle->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'phase' => 'nullable|in:land_prep,planting,vegetative,generative,harvest,post_harvest,completed,cancelled',
        ]);

        $targetPhase = $data['phase'] ?? null;
        $oldPhase = $cropCycle->phase;

        if ($targetPhase) {
            $cropCycle->update(['phase' => $targetPhase]);
        } else {
            $cropCycle->advancePhase();
        }

        $cropCycle->refresh();

        // Set actual dates based on phase
        $dateUpdates = [];
        if ($cropCycle->phase === 'land_prep' && !$cropCycle->actual_prep_start) {
            $dateUpdates['actual_prep_start'] = today();
        }
        if ($cropCycle->phase === 'planting' && !$cropCycle->actual_plant_date) {
            $dateUpdates['actual_plant_date'] = today();
        }
        if ($cropCycle->phase === 'harvest' && !$cropCycle->actual_harvest_date) {
            $dateUpdates['actual_harvest_date'] = today();
        }
        if (in_array($cropCycle->phase, ['completed', 'cancelled']) && !$cropCycle->actual_end_date) {
            $dateUpdates['actual_end_date'] = today();
        }
        if ($dateUpdates) $cropCycle->update($dateUpdates);

        // Sync plot status
        $plotStatus = match ($cropCycle->phase) {
            'land_prep'    => 'preparing',
            'planting'     => 'planted',
            'vegetative', 'generative' => 'growing',
            'harvest'      => 'harvesting',
            'post_harvest' => 'post_harvest',
            'completed'    => 'idle',
            default        => null,
        };
        if ($plotStatus) {
            $cropCycle->plot->update(['status' => $plotStatus]);
        }

        ActivityLog::record('crop_phase_advanced', "Siklus {$cropCycle->number}: {$oldPhase} → {$cropCycle->phase}");
        return back()->with('success', "Fase diperbarui ke {$cropCycle->phaseLabel()}.");
    }

    public function storeActivity(Request $request, CropCycle $cropCycle)
    {
        abort_if($cropCycle->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'activity_type'  => 'required|in:planting,fertilizing,spraying,watering,weeding,pruning,harvesting,soil_prep,other',
            'date'           => 'required|date',
            'description'    => 'required|string|max:255',
            'input_product'  => 'nullable|string|max:100',
            'input_quantity' => 'nullable|numeric|min:0',
            'input_unit'     => 'nullable|string|max:20',
            'cost'           => 'nullable|numeric|min:0',
            'harvest_qty'    => 'nullable|numeric|min:0',
            'harvest_unit'   => 'nullable|string|max:20',
            'harvest_grade'  => 'nullable|string|max:30',
            'notes'          => 'nullable|string',
        ]);

        FarmPlotActivity::create(array_merge($data, [
            'farm_plot_id'  => $cropCycle->farm_plot_id,
            'crop_cycle_id' => $cropCycle->id,
            'tenant_id'     => $this->tid(),
            'user_id'       => auth()->id(),
        ]));

        // Recalculate cycle totals
        $cropCycle->recalculate();

        return back()->with('success', 'Aktivitas berhasil dicatat.');
    }
}
