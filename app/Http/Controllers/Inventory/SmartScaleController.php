<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\SmartScale;
use App\Models\ScaleWeighLog;
use App\Services\SmartScaleService;
use Illuminate\Http\Request;

class SmartScaleController extends Controller
{
    protected $scaleService;

    public function __construct(SmartScaleService $scaleService)
    {
        $this->scaleService = $scaleService;
    }

    /**
     * Display list of smart scales
     */
    public function index()
    {
        $scales = SmartScale::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->paginate(20);

        return view('inventory.smart-scales.index', compact('scales'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('inventory.smart-scales.create');
    }

    /**
     * Store new scale
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'device_id' => 'required|string|unique:smart_scales,device_id',
            'vendor' => 'required|string',
            'model' => 'nullable|string',
            'connection_type' => 'required|in:serial,usb,bluetooth,network',
            'port' => 'required|string',
            'baud_rate' => 'nullable|integer',
            'max_capacity' => 'required|numeric|min:1',
            'unit' => 'required|in:g,kg,lb,oz',
            'precision' => 'nullable|integer|min:0|max:4',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['is_active'] = $request->boolean('is_active', true);

        SmartScale::create($validated);

        return redirect()->route('inventory.smart-scales.index')
            ->with('success', 'Timbangan digital berhasil ditambahkan');
    }

    /**
     * Show scale details
     */
    public function show(SmartScale $smartScale)
    {
        $this->authorize('view', $smartScale);

        $logs = $smartScale->weighLogs()
            ->latest('weigh_time')
            ->paginate(50);

        return view('inventory.smart-scales.show', compact('smartScale', 'logs'));
    }

    /**
     * Test scale connection
     */
    public function testConnection(SmartScale $smartScale)
    {
        $this->authorize('view', $smartScale);

        $result = $this->scaleService->testConnection($smartScale);

        return response()->json($result);
    }

    /**
     * Read current weight
     */
    public function readWeight(SmartScale $smartScale)
    {
        $this->authorize('view', $smartScale);

        $result = $this->scaleService->readWeight($smartScale);

        return response()->json($result);
    }

    /**
     * Tare scale
     */
    public function tare(SmartScale $smartScale)
    {
        $this->authorize('update', $smartScale);

        $success = $this->scaleService->tareScale($smartScale);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Tare berhasil' : 'Tare gagal',
        ]);
    }

    /**
     * Record weigh operation
     */
    public function recordWeigh(Request $request)
    {
        $validated = $request->validate([
            'scale_id' => 'required|exists:smart_scales,id',
            'product_id' => 'nullable|exists:products,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'weight' => 'required|numeric|min:0',
            'tare_weight' => 'nullable|numeric|min:0',
            'unit' => 'nullable|in:g,kg,lb,oz',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'raw_data' => 'nullable|string',
        ]);

        $log = $this->scaleService->recordWeigh($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data timbangan berhasil dicatat',
            'log_id' => $log->id,
            'net_weight' => $log->net_weight,
        ]);
    }

    /**
     * Process weigh log
     */
    public function processLog(ScaleWeighLog $weighLog)
    {
        $this->authorize('update', $weighLog);

        $success = $this->scaleService->processWeighLog($weighLog);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Data berhasil diproses' : 'Gagal memproses data',
        ]);
    }

    /**
     * Show weigh logs
     */
    public function logs(Request $request)
    {
        $query = ScaleWeighLog::where('tenant_id', auth()->user()->tenant_id)
            ->with(['scale', 'product', 'warehouse', 'weighedBy'])
            ->latest('weigh_time');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('scale_id')) {
            $query->where('scale_id', $request->scale_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('weigh_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('weigh_time', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);

        return view('inventory.smart-scales.logs', compact('logs'));
    }

    /**
     * Update scale
     */
    public function update(Request $request, SmartScale $smartScale)
    {
        $this->authorize('update', $smartScale);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vendor' => 'required|string',
            'port' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $smartScale->update($validated);

        return redirect()->route('inventory.smart-scales.index')
            ->with('success', 'Timbangan berhasil diupdate');
    }

    /**
     * Delete scale
     */
    public function destroy(SmartScale $smartScale)
    {
        $this->authorize('delete', $smartScale);

        $smartScale->delete();

        return redirect()->route('inventory.smart-scales.index')
            ->with('success', 'Timbangan berhasil dihapus');
    }
}
