<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CropCycle;
use App\Models\Employee;
use App\Models\FarmPlot;
use App\Models\HarvestLog;
use App\Models\HarvestLogGrade;
use App\Models\HarvestLogWorker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HarvestLogController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = HarvestLog::where('tenant_id', $this->tid())
            ->with(['plot', 'cropCycle', 'grades']);

        if ($request->plot) $query->whereHas('plot', fn ($q) => $q->where('code', $request->plot));
        if ($request->from) $query->where('harvest_date', '>=', $request->from);
        if ($request->to) $query->where('harvest_date', '<=', $request->to);

        $logs = $query->orderByDesc('harvest_date')->paginate(20)->withQueryString();
        $plots = FarmPlot::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('code')->get();

        // Summary stats
        $allLogs = HarvestLog::where('tenant_id', $this->tid());
        $stats = [
            'total_harvests' => $allLogs->count(),
            'total_qty'      => $allLogs->sum('total_qty'),
            'total_reject'   => $allLogs->sum('reject_qty'),
            'total_cost'     => $allLogs->sum(DB::raw('labor_cost + transport_cost')),
        ];

        // Productivity per plot
        $perPlot = HarvestLog::where('harvest_logs.tenant_id', $this->tid())
            ->join('farm_plots', 'harvest_logs.farm_plot_id', '=', 'farm_plots.id')
            ->selectRaw('farm_plots.code, farm_plots.name, farm_plots.area_size, farm_plots.area_unit, SUM(harvest_logs.total_qty) as total, COUNT(*) as sessions')
            ->groupBy('farm_plots.id', 'farm_plots.code', 'farm_plots.name', 'farm_plots.area_size', 'farm_plots.area_unit')
            ->orderByDesc('total')
            ->get();

        return view('farm.harvest-logs', compact('logs', 'plots', 'stats', 'perPlot'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'farm_plot_id'     => 'required|exists:farm_plots,id',
            'harvest_date'     => 'required|date',
            'crop_name'        => 'required|string|max:255',
            'total_qty'        => 'required|numeric|min:0.001',
            'unit'             => 'required|string|max:20',
            'reject_qty'       => 'nullable|numeric|min:0',
            'moisture_pct'     => 'nullable|numeric|min:0|max:100',
            'storage_location' => 'nullable|string|max:255',
            'labor_cost'       => 'nullable|numeric|min:0',
            'transport_cost'   => 'nullable|numeric|min:0',
            'weather'          => 'nullable|string|max:50',
            'notes'            => 'nullable|string',
            // Grades
            'grades'           => 'nullable|array',
            'grades.*.grade'   => 'required|string|max:30',
            'grades.*.quantity'=> 'required|numeric|min:0.001',
            'grades.*.price'   => 'nullable|numeric|min:0',
            // Workers
            'workers'             => 'nullable|array',
            'workers.*.name'      => 'required|string|max:255',
            'workers.*.qty'       => 'nullable|numeric|min:0',
            'workers.*.wage'      => 'nullable|numeric|min:0',
        ]);

        $plot = FarmPlot::where('tenant_id', $this->tid())->findOrFail($data['farm_plot_id']);
        $cycle = $plot->activeCycle();

        DB::transaction(function () use ($data, $plot, $cycle) {
            $log = HarvestLog::create([
                'farm_plot_id'     => $plot->id,
                'crop_cycle_id'    => $cycle?->id,
                'tenant_id'        => $this->tid(),
                'user_id'          => auth()->id(),
                'number'           => HarvestLog::generateNumber($plot->code),
                'harvest_date'     => $data['harvest_date'],
                'crop_name'        => $data['crop_name'],
                'total_qty'        => (float) $data['total_qty'],
                'unit'             => $data['unit'],
                'reject_qty'       => (float) ($data['reject_qty'] ?? 0),
                'moisture_pct'     => $data['moisture_pct'] ?? null,
                'storage_location' => $data['storage_location'] ?? null,
                'labor_cost'       => (float) ($data['labor_cost'] ?? 0),
                'transport_cost'   => (float) ($data['transport_cost'] ?? 0),
                'weather'          => $data['weather'] ?? null,
                'notes'            => $data['notes'] ?? null,
            ]);

            // Save grades
            foreach ($data['grades'] ?? [] as $g) {
                HarvestLogGrade::create([
                    'harvest_log_id' => $log->id,
                    'grade'          => $g['grade'],
                    'quantity'       => (float) $g['quantity'],
                    'unit'           => $data['unit'],
                    'price_per_unit' => (float) ($g['price'] ?? 0),
                ]);
            }

            // Save workers
            foreach ($data['workers'] ?? [] as $w) {
                $emp = Employee::where('tenant_id', $this->tid())
                    ->where('name', 'like', "%{$w['name']}%")->first();
                HarvestLogWorker::create([
                    'harvest_log_id'  => $log->id,
                    'employee_id'     => $emp?->id,
                    'worker_name'     => $w['name'],
                    'quantity_picked' => (float) ($w['qty'] ?? 0),
                    'unit'            => $data['unit'],
                    'wage'            => (float) ($w['wage'] ?? 0),
                ]);
            }

            // Sync crop cycle yield
            if ($cycle) {
                $cycle->recalculate();
                // Also add as activity
                \App\Models\FarmPlotActivity::create([
                    'farm_plot_id'  => $plot->id,
                    'crop_cycle_id' => $cycle->id,
                    'tenant_id'     => $this->tid(),
                    'user_id'       => auth()->id(),
                    'activity_type' => 'harvesting',
                    'date'          => $data['harvest_date'],
                    'description'   => "Panen {$data['total_qty']} {$data['unit']} ({$log->number})",
                    'harvest_qty'   => (float) $data['total_qty'],
                    'harvest_unit'  => $data['unit'],
                    'cost'          => (float) ($data['labor_cost'] ?? 0) + (float) ($data['transport_cost'] ?? 0),
                ]);
                $cycle->recalculate();
            }

            ActivityLog::record('harvest_logged', "Panen dicatat: {$log->number} — {$data['total_qty']} {$data['unit']} dari lahan {$plot->code}");
        });

        return back()->with('success', "Panen berhasil dicatat: {$data['total_qty']} {$data['unit']} dari lahan {$plot->code}.");
    }

    public function show(HarvestLog $harvestLog)
    {
        abort_if($harvestLog->tenant_id !== $this->tid(), 403);
        $harvestLog->load(['plot', 'cropCycle', 'grades', 'workers.employee', 'user']);
        return view('farm.harvest-show', compact('harvestLog'));
    }
}
