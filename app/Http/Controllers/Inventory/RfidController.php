<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\RfidTag;
use App\Models\RfidScannerDevice;
use App\Models\RfidScanLog;
use Illuminate\Http\Request;

class RfidController extends Controller
{
    /**
     * Display RFID tags
     */
    public function index()
    {
        $tags = RfidTag::where('tenant_id', auth()->user()->tenant_id)
            ->with('taggable')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('inventory.rfid.tags.index', compact('tags'));
    }

    /**
     * Store new RFID tag
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tag_uid' => 'required|string|unique:rfid_tags,tag_uid',
            'tag_type' => 'required|in:rfid,nfc,barcode_qr',
            'frequency' => 'required|in:LF,HF,UHF',
            'protocol' => 'nullable|string',
            'encoded_data' => 'nullable|string',
            'is_encrypted' => 'boolean',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['status'] = 'active';

        RfidTag::create($validated);

        return redirect()->route('warehouses.rfid.tags.index')
            ->with('success', 'RFID tag berhasil ditambahkan');
    }

    /**
     * Assign tag to product/asset
     */
    public function assignTag(Request $request, RfidTag $tag)
    {
        $validated = $request->validate([
            'taggable_type' => 'required|string',
            'taggable_id' => 'required|integer',
        ]);

        $modelClass = $validated['taggable_type'];
        $model = $modelClass::findOrFail($validated['taggable_id']);

        $tag->assignTo($model);

        return response()->json([
            'success' => true,
            'message' => 'Tag berhasil di-assign ke ' . class_basename($model),
        ]);
    }

    /**
     * Scan RFID tag
     */
    public function scanTag(Request $request)
    {
        $validated = $request->validate([
            'tag_uid' => 'required|string',
            'scanner_device_id' => 'nullable|exists:rfid_scanner_devices,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'location_id' => 'nullable|exists:warehouse_bins,id',
            'scan_type' => 'required|in:check_in,check_out,transfer,audit,movement',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // Find tag
        $tag = RfidTag::where('tag_uid', $validated['tag_uid'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => 'Tag tidak ditemukan',
            ], 404);
        }

        // Record scan
        $scanLog = $tag->recordScan($validated);

        // Update scanner device
        if ($validated['scanner_device_id']) {
            $scanner = RfidScannerDevice::find($validated['scanner_device_id']);
            if ($scanner) {
                $scanner->recordScan();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Scan berhasil',
            'tag' => [
                'uid' => $tag->tag_uid,
                'type' => $tag->tag_type,
                'assigned_to' => $tag->taggable_type ? class_basename($tag->taggable) : null,
                'assigned_id' => $tag->taggable_id,
            ],
            'scan_log_id' => $scanLog->id,
        ]);
    }

    /**
     * Display scanner devices
     */
    public function scanners()
    {
        $scanners = RfidScannerDevice::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->paginate(20);

        return view('inventory.rfid.scanners.index', compact('scanners'));
    }

    /**
     * Store scanner device
     */
    public function storeScanner(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'device_id' => 'required|string|unique:rfid_scanner_devices,device_id',
            'vendor' => 'required|string',
            'model' => 'nullable|string',
            'scanner_type' => 'required|in:handheld,fixed,portal,mobile',
            'frequency' => 'required|in:LF,HF,UHF',
            'connection_type' => 'required|in:usb,bluetooth,wifi,ethernet',
            'port' => 'nullable|string',
            'ip_address' => 'nullable|string',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['is_active'] = true;

        RfidScannerDevice::create($validated);

        return redirect()->route('warehouses.rfid.scanners.index')
            ->with('success', 'Scanner berhasil ditambahkan');
    }

    /**
     * Display scan logs
     */
    public function logs(Request $request)
    {
        $query = RfidScanLog::where('tenant_id', auth()->user()->tenant_id)
            ->with(['tag', 'scannerDevice', 'warehouse', 'scannedBy'])
            ->latest('scan_time');

        if ($request->filled('scan_type')) {
            $query->where('scan_type', $request->scan_type);
        }

        if ($request->filled('tag_id')) {
            $query->where('tag_id', $request->tag_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scan_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scan_time', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);

        return view('inventory.rfid.logs.index', compact('logs'));
    }
}
