<?php

namespace App\Http\Controllers\Livestock;

use App\Http\Controllers\Controller;
use App\Models\WasteManagementLog;
use App\Models\LivestockHerd;
use Illuminate\Http\Request;

class WasteManagementController extends Controller
{
    /**
     * Display waste management logs
     */
    public function index(Request $request)
    {
        $stats = [
            'total_logs' => WasteManagementLog::where('tenant_id', auth()->user()->tenant_id)->count(),
            'total_waste_kg' => WasteManagementLog::where('tenant_id', auth()->user()->tenant_id)
                ->sum('quantity_kg'),
            'eco_friendly_percentage' => 0,
            'total_revenue' => WasteManagementLog::where('tenant_id', auth()->user()->tenant_id)
                ->whereNotNull('revenue_amount')
                ->sum('revenue_amount'),
        ];

        // Calculate eco-friendly percentage
        $totalLogs = WasteManagementLog::where('tenant_id', auth()->user()->tenant_id)->count();
        if ($totalLogs > 0) {
            $ecoFriendly = WasteManagementLog::where('tenant_id', auth()->user()->tenant_id)
                ->whereIn('disposal_method', ['composting', 'biogas', 'field_application'])
                ->count();
            $stats['eco_friendly_percentage'] = round(($ecoFriendly / $totalLogs) * 100, 2);
        }

        $logs = WasteManagementLog::with(['herd', 'recordedBy'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderByDesc('collection_date')
            ->paginate(20);

        $herds = LivestockHerd::where('tenant_id', auth()->user()->tenant_id)
            ->active()
            ->get();

        return view('livestock.waste.logs', compact('stats', 'logs', 'herds'));
    }

    /**
     * Store waste management log
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'livestock_herd_id' => 'nullable|exists:livestock_herds,id',
            'collection_date' => 'required|date',
            'waste_type' => 'required|in:manure_solid,manure_liquid,urine,bedding,mortality,other',
            'quantity_kg' => 'required|numeric|min:0',
            'volume_liters' => 'nullable|numeric|min:0',
            'disposal_method' => 'required|in:composting,biogas,field_application,sale,disposal,storage',
            'storage_location' => 'nullable|string',
            'processing_date' => 'nullable|date|after_or_equal:collection_date',
            'processed_quantity_kg' => 'nullable|numeric|min:0',
            'end_product' => 'nullable|string',
            'revenue_amount' => 'nullable|numeric|min:0',
            'environmental_impact' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $log = new WasteManagementLog();
            $log->tenant_id = auth()->user()->tenant_id;
            $log->fill($validated);
            $log->recorded_by = auth()->id();
            $log->save();

            return back()->with('success', 'Waste log recorded successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display composting batches
     */
    public function composting(Request $request)
    {
        $stats = [
            'active_batches' => \App\Models\CompostingBatch::where('tenant_id', auth()->user()->tenant_id)
                ->where('status', 'active')->count(),
            'total_compost_kg' => \App\Models\CompostingBatch::where('tenant_id', auth()->user()->tenant_id)
                ->where('status', 'completed')
                ->sum('final_weight_kg'),
            'avg_quality_score' => \App\Models\CompostingBatch::where('tenant_id', auth()->user()->tenant_id)
                ->where('status', 'completed')
                ->whereNotNull('quality_score')
                ->avg('quality_score') ?? 0,
        ];

        $batches = \App\Models\CompostingBatch::where('tenant_id', auth()->user()->tenant_id)
            ->orderByDesc('start_date')
            ->paginate(20);

        return view('livestock.waste.composting', compact('stats', 'batches'));
    }

    /**
     * Store composting batch
     */
    public function storeBatch(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'expected_end_date' => 'nullable|date|after:start_date',
            'initial_weight_kg' => 'required|numeric|min:0',
            'moisture_percentage' => 'nullable|numeric|min:0|max:100',
            'temperature_celsius' => 'nullable|numeric',
            'ph_level' => 'nullable|numeric|min:0|max:14',
            'ingredients' => 'nullable|array',
            'turning_schedule' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $batch = new \App\Models\CompostingBatch();
            $batch->tenant_id = auth()->user()->tenant_id;
            $batch->batch_code = 'COMP-' . now()->format('Y') . '-' . str_pad(\App\Models\CompostingBatch::count() + 1, 4, '0', STR_PAD_LEFT);
            $batch->fill($validated);
            $batch->status = 'active';
            $batch->managed_by = auth()->id();
            $batch->current_weight_kg = $validated['initial_weight_kg'];
            $batch->save();

            return back()->with('success', 'Composting batch started!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Update composting batch
     */
    public function updateBatch(Request $request, $id)
    {
        $validated = $request->validate([
            'current_weight_kg' => 'nullable|numeric|min:0',
            'moisture_percentage' => 'nullable|numeric|min:0|max:100',
            'temperature_celsius' => 'nullable|numeric',
            'ph_level' => 'nullable|numeric|min:0|max:14',
            'quality_score' => 'nullable|numeric|min:0|max:10',
            'status' => 'nullable|in:active,curing,completed',
            'final_weight_kg' => 'nullable|numeric|min:0',
            'actual_end_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $batch = \App\Models\CompostingBatch::findOrFail($id);
            $batch->fill($validated);

            if ($validated['status'] === 'completed' && !$batch->actual_end_date) {
                $batch->actual_end_date = now();
            }

            $batch->save();

            return back()->with('success', 'Composting batch updated!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
