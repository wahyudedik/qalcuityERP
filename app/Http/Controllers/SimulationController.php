<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Simulation;
use App\Services\SimulationService;
use Illuminate\Http\Request;

class SimulationController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function __construct(protected SimulationService $service) {}

    public function index()
    {
        $simulations = Simulation::where('tenant_id', $this->tid())
            ->latest()
            ->paginate(15);

        return view('simulations.index', compact('simulations'));
    }

    public function create()
    {
        return view('simulations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'scenario_type' => 'required|in:price_increase,new_branch,stock_out,cost_reduction,demand_change',
            'parameters' => 'required|array',
        ]);

        try {
            $data = $this->service->run($this->tid(), $request->scenario_type, $request->parameters);

            $simulation = Simulation::create([
                'tenant_id' => $this->tid(),
                'user_id' => auth()->id(),
                'name' => $request->name,
                'scenario_type' => $request->scenario_type,
                'parameters' => $request->parameters,
                'results' => $data['results'],
                'ai_narrative' => $data['ai_narrative'],
                'status' => 'calculated',
            ]);

            ActivityLog::record('simulation_created', 'Simulation created', $simulation, [], $simulation->toArray());

            return redirect()->route('simulations.show', $simulation)
                ->with('success', 'Simulasi berhasil dihitung.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal menjalankan simulasi: '.$e->getMessage());
        }
    }

    public function show(Simulation $simulation)
    {
        abort_if($simulation->tenant_id !== $this->tid(), 403);

        return view('simulations.show', compact('simulation'));
    }

    public function destroy(Simulation $simulation)
    {
        abort_if($simulation->tenant_id !== $this->tid(), 403);
        $simulation->delete();

        return redirect()->route('simulations.index')->with('success', 'Simulasi dihapus.');
    }
}
