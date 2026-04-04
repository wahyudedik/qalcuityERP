<?php

namespace App\Http\Controllers;

use App\Models\BinStock;
use App\Models\PickingList;
use App\Models\PickingListItem;
use App\Models\Product;
use App\Models\PutawayRule;
use App\Models\StockMovement;
use App\Models\StockOpnameItem;
use App\Models\StockOpnameSession;
use App\Models\Warehouse;
use App\Models\WarehouseBin;
use App\Models\WarehouseZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WmsController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    // ── Dashboard ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $warehouseId = $request->warehouse_id;
        $warehouses = Warehouse::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();

        if (!$warehouseId && $warehouses->isNotEmpty())
            $warehouseId = $warehouses->first()->id;

        $zones = WarehouseZone::where('tenant_id', $this->tid())
            ->where('warehouse_id', $warehouseId)->withCount('bins')->get();

        $bins = WarehouseBin::where('tenant_id', $this->tid())
            ->where('warehouse_id', $warehouseId)
            ->with(['zone', 'stocks.product'])
            ->when($request->zone_id, fn($q, $z) => $q->where('zone_id', $z))
            ->when($request->search, fn($q, $s) => $q->where('code', 'like', "%$s%"))
            ->orderBy('code')->paginate(30)->withQueryString();

        $stats = [
            'zones' => $zones->count(),
            'bins' => WarehouseBin::where('tenant_id', $this->tid())->where('warehouse_id', $warehouseId)->count(),
            'occupied' => BinStock::where('tenant_id', $this->tid())->whereHas('bin', fn($q) => $q->where('warehouse_id', $warehouseId))->where('quantity', '>', 0)->distinct('bin_id')->count('bin_id'),
            'products' => BinStock::where('tenant_id', $this->tid())->whereHas('bin', fn($q) => $q->where('warehouse_id', $warehouseId))->where('quantity', '>', 0)->distinct('product_id')->count('product_id'),
        ];

        return view('wms.index', compact('warehouses', 'warehouseId', 'zones', 'bins', 'stats'));
    }

    // ── Zones ─────────────────────────────────────────────────────

    public function storeZone(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'code' => 'required|string|max:10',
            'name' => 'required|string|max:255',
            'type' => 'required|in:general,cold,hazmat,staging,returns',
        ]);

        WarehouseZone::create(array_merge($data, ['tenant_id' => $this->tid(), 'is_active' => true]));
        return back()->with('success', 'Zone berhasil dibuat.');
    }

    // ── Bins ──────────────────────────────────────────────────────

    public function storeBin(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'zone_id' => 'nullable|exists:warehouse_zones,id',
            'aisle' => 'nullable|string|max:10',
            'rack' => 'nullable|string|max:10',
            'shelf' => 'nullable|string|max:10',
            'max_capacity' => 'nullable|integer|min:0',
            'bin_type' => 'required|in:storage,picking,staging,returns',
        ]);

        $zone = $data['zone_id'] ? WarehouseZone::find($data['zone_id']) : null;
        $code = ($zone ? $zone->code : 'X') . '-' . ($data['aisle'] ?? '00') . '-' . ($data['rack'] ?? '00') . '-' . ($data['shelf'] ?? '00');

        WarehouseBin::create(array_merge($data, [
            'tenant_id' => $this->tid(),
            'code' => $code,
            'max_capacity' => $data['max_capacity'] ?? 0,
            'is_active' => true,
        ]));

        return back()->with('success', "Bin {$code} berhasil dibuat.");
    }

    public function bulkCreateBins(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'zone_id' => 'nullable|exists:warehouse_zones,id',
            'aisle_from' => 'required|integer|min:1',
            'aisle_to' => 'required|integer|min:1',
            'rack_from' => 'required|integer|min:1',
            'rack_to' => 'required|integer|min:1',
            'shelf_from' => 'required|integer|min:1',
            'shelf_to' => 'required|integer|min:1',
            'bin_type' => 'required|in:storage,picking,staging,returns',
        ]);

        $zone = $data['zone_id'] ? WarehouseZone::find($data['zone_id']) : null;
        $prefix = $zone ? $zone->code : 'X';
        $created = 0;

        for ($a = $data['aisle_from']; $a <= $data['aisle_to']; $a++) {
            for ($r = $data['rack_from']; $r <= $data['rack_to']; $r++) {
                for ($s = $data['shelf_from']; $s <= $data['shelf_to']; $s++) {
                    $code = $prefix . '-' . str_pad($a, 2, '0', STR_PAD_LEFT) . '-' . str_pad($r, 2, '0', STR_PAD_LEFT) . '-' . str_pad($s, 2, '0', STR_PAD_LEFT);
                    WarehouseBin::firstOrCreate(
                        ['warehouse_id' => $data['warehouse_id'], 'code' => $code],
                        ['tenant_id' => $this->tid(), 'zone_id' => $data['zone_id'], 'aisle' => str_pad($a, 2, '0', STR_PAD_LEFT), 'rack' => str_pad($r, 2, '0', STR_PAD_LEFT), 'shelf' => str_pad($s, 2, '0', STR_PAD_LEFT), 'bin_type' => $data['bin_type'], 'is_active' => true]
                    );
                    $created++;
                }
            }
        }

        return back()->with('success', "{$created} bin berhasil dibuat.");
    }

    // ── Putaway ───────────────────────────────────────────────────

    public function putaway(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'bin_id' => 'required|exists:warehouse_bins,id',
            'quantity' => 'required|numeric|min:0.001',
        ]);

        $bin = WarehouseBin::findOrFail($data['bin_id']);
        abort_if($bin->tenant_id !== $this->tid(), 403);

        if ($bin->max_capacity > 0 && ($bin->usedCapacity() + $data['quantity']) > $bin->max_capacity) {
            return back()->with('error', 'Kapasitas bin tidak cukup.');
        }

        BinStock::updateOrCreate(
            ['bin_id' => $data['bin_id'], 'product_id' => $data['product_id']],
            ['tenant_id' => $this->tid()]
        )->increment('quantity', $data['quantity']);

        return back()->with('success', 'Putaway berhasil. ' . $data['quantity'] . ' unit ke ' . $bin->code);
    }

    public function suggestBin(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $warehouseId = $request->warehouse_id;

        // Check putaway rules
        $rule = PutawayRule::where('tenant_id', $this->tid())
            ->where('warehouse_id', $warehouseId)
            ->where('is_active', true)
            ->where(fn($q) => $q->where('product_id', $product->id)->orWhere('product_category', $product->category))
            ->orderBy('priority')->first();

        if ($rule && $rule->bin_id) {
            return response()->json(['bin_id' => $rule->bin_id, 'bin_code' => $rule->bin->code ?? '']);
        }

        // Fallback: first available bin in zone or warehouse
        $query = WarehouseBin::where('warehouse_id', $warehouseId)->where('is_active', true)->where('bin_type', 'storage');
        if ($rule && $rule->zone_id)
            $query->where('zone_id', $rule->zone_id);

        $bin = $query->whereDoesntHave('stocks', fn($q) => $q->where('quantity', '>', 0))
            ->orWhereHas('stocks', fn($q) => $q->where('product_id', $product->id))
            ->first();

        return response()->json(['bin_id' => $bin?->id, 'bin_code' => $bin?->code ?? 'Tidak ada bin tersedia']);
    }

    // ── Picking ───────────────────────────────────────────────────

    public function pickingLists(Request $request)
    {
        $lists = PickingList::with(['warehouse', 'assignee', 'items.product'])
            ->where('tenant_id', $this->tid())
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()->paginate(20)->withQueryString();

        $warehouses = Warehouse::where('tenant_id', $this->tid())->where('is_active', true)->get();
        $users = \App\Models\User::where('tenant_id', $this->tid())->whereIn('role', ['admin', 'manager', 'staff', 'gudang'])->get();

        return view('wms.picking', compact('lists', 'warehouses', 'users'));
    }

    public function createPickingList(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'assigned_to' => 'nullable|exists:users,id',
            'reference_type' => 'nullable|string|max:30',
            'reference_id' => 'nullable|integer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
        ]);

        DB::transaction(function () use ($data) {
            $list = PickingList::create([
                'tenant_id' => $this->tid(),
                'warehouse_id' => $data['warehouse_id'],
                'number' => PickingList::generateNumber($this->tid()),
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? null,
                'status' => 'pending',
                'user_id' => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                // Find best bin for this product
                $binStock = BinStock::whereHas('bin', fn($q) => $q->where('warehouse_id', $data['warehouse_id']))
                    ->where('product_id', $item['product_id'])
                    ->where('quantity', '>', 0)
                    ->where('tenant_id', $this->tid())
                    ->orderByDesc('quantity')->first();

                PickingListItem::create([
                    'picking_list_id' => $list->id,
                    'product_id' => $item['product_id'],
                    'bin_id' => $binStock?->bin_id,
                    'quantity_requested' => $item['quantity'],
                ]);
            }
        });

        return back()->with('success', 'Picking list berhasil dibuat.');
    }

    public function scanPicking(PickingList $pickingList)
    {
        abort_if($pickingList->tenant_id !== $this->tid(), 403);
        $pickingList->load(['items.product', 'items.bin', 'warehouse']);
        return view('wms.picking-scan', compact('pickingList'));
    }

    public function printBinLabel(WarehouseBin $bin)
    {
        abort_if($bin->tenant_id !== $this->tid(), 403);
        $barcodeService = app(\App\Services\BarcodeService::class);
        $barcodeImage = $barcodeService->generate($bin->code, 'code128', 'png');
        $barcodes = [['bin' => $bin, 'image' => base64_encode($barcodeImage), 'value' => $bin->code]];
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('wms.bin-label', compact('barcodes'));
        $pdf->setPaper([0, 0, 170.08, 85.04]); // 60mm x 30mm in points
        $pdf->setOption('margin-top', 0);
        $pdf->setOption('margin-right', 0);
        $pdf->setOption('margin-bottom', 0);
        $pdf->setOption('margin-left', 0);
        return $pdf->stream('bin-' . $bin->code . '.pdf');
    }

    public function printBinLabelsBatch(Request $request)
    {
        $binIds = $request->validate(['bin_ids' => 'required|array'])['bin_ids'];
        $barcodeService = app(\App\Services\BarcodeService::class);
        $bins = WarehouseBin::whereIn('id', $binIds)->where('tenant_id', $this->tid())->orderBy('code')->get();
        $barcodes = $bins->map(fn($b) => [
            'bin' => $b,
            'image' => base64_encode($barcodeService->generate($b->code, 'code128', 'png')),
            'value' => $b->code,
        ])->toArray();
        // Pad to multiple of 4 for A4 grid layout
        while (count($barcodes) % 4 !== 0) {
            $barcodes[] = null;
        }
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('wms.bin-label', compact('barcodes'));
        $pdf->setPaper('A4');
        $pdf->setOption('margin-top', 10);
        $pdf->setOption('margin-right', 10);
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-left', 10);
        return $pdf->stream('bin-labels.pdf');
    }

    public function confirmPick(Request $request, PickingListItem $pickingListItem)
    {
        $list = $pickingListItem->pickingList;
        abort_if($list->tenant_id !== $this->tid(), 403);

        $qty = $request->validate(['quantity_picked' => 'required|numeric|min:0'])['quantity_picked'];

        DB::transaction(function () use ($pickingListItem, $qty) {
            $pickingListItem->update([
                'quantity_picked' => $qty,
                'status' => $qty >= $pickingListItem->quantity_requested ? 'picked' : 'short',
            ]);

            // Deduct from bin stock
            if ($pickingListItem->bin_id && $qty > 0) {
                $binStock = BinStock::where('bin_id', $pickingListItem->bin_id)
                    ->where('product_id', $pickingListItem->product_id)->first();
                if ($binStock)
                    $binStock->decrement('quantity', min($qty, $binStock->quantity));
            }

            // Check if all items picked
            $list = $pickingListItem->pickingList;
            if ($list->items()->where('status', 'pending')->count() === 0) {
                $list->update(['status' => 'completed', 'completed_at' => now()]);
            } elseif ($list->status === 'pending') {
                $list->update(['status' => 'in_progress', 'started_at' => now()]);
            }
        });

        return back()->with('success', 'Item picked: ' . $qty);
    }

    // ── Stock Opname ──────────────────────────────────────────────

    public function opnameSessions(Request $request)
    {
        $sessions = StockOpnameSession::with('warehouse')
            ->where('tenant_id', $this->tid())
            ->latest()->paginate(20)->withQueryString();

        $warehouses = Warehouse::where('tenant_id', $this->tid())->where('is_active', true)->get();

        return view('wms.opname', compact('sessions', 'warehouses'));
    }

    public function createOpname(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'opname_date' => 'required|date',
        ]);

        $session = StockOpnameSession::create([
            'tenant_id' => $this->tid(),
            'warehouse_id' => $data['warehouse_id'],
            'number' => 'OPN-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4)),
            'opname_date' => $data['opname_date'],
            'status' => 'draft',
            'user_id' => auth()->id(),
        ]);

        // Auto-populate items from bin stocks
        $binStocks = BinStock::whereHas('bin', fn($q) => $q->where('warehouse_id', $data['warehouse_id']))
            ->where('tenant_id', $this->tid())
            ->with('bin')->get();

        foreach ($binStocks as $bs) {
            StockOpnameItem::create([
                'session_id' => $session->id,
                'product_id' => $bs->product_id,
                'bin_id' => $bs->bin_id,
                'system_qty' => $bs->quantity,
            ]);
        }

        return back()->with('success', 'Sesi opname dibuat dengan ' . $binStocks->count() . ' item.');
    }

    public function showOpname(StockOpnameSession $stockOpnameSession)
    {
        abort_if($stockOpnameSession->tenant_id !== $this->tid(), 403);
        $stockOpnameSession->load(['items.product', 'items.bin', 'warehouse']);
        return view('wms.opname-show', compact('stockOpnameSession'));
    }

    public function updateOpnameItem(Request $request, StockOpnameItem $stockOpnameItem)
    {
        $session = $stockOpnameItem->session;
        abort_if($session->tenant_id !== $this->tid(), 403);

        $data = $request->validate(['actual_qty' => 'required|numeric|min:0']);
        $stockOpnameItem->update([
            'actual_qty' => $data['actual_qty'],
            'difference' => $data['actual_qty'] - $stockOpnameItem->system_qty,
        ]);

        return back()->with('success', 'Item diperbarui.');
    }

    public function completeOpname(StockOpnameSession $stockOpnameSession)
    {
        abort_if($stockOpnameSession->tenant_id !== $this->tid(), 403);

        DB::transaction(function () use ($stockOpnameSession) {
            foreach ($stockOpnameSession->items()->whereNotNull('actual_qty')->get() as $item) {
                if ($item->difference != 0 && $item->bin_id) {
                    $binStock = BinStock::where('bin_id', $item->bin_id)
                        ->where('product_id', $item->product_id)->first();
                    if ($binStock) {
                        $binStock->update(['quantity' => max(0, $item->actual_qty)]);
                    }
                }
            }
            $stockOpnameSession->update(['status' => 'completed']);
        });

        return back()->with('success', 'Opname selesai. Stok bin diperbarui.');
    }

    // ── Putaway Rules ─────────────────────────────────────────────

    public function putawayRules(Request $request)
    {
        $rules = PutawayRule::with(['warehouse', 'product', 'zone', 'bin'])
            ->where('tenant_id', $this->tid())
            ->latest()->paginate(20);

        $warehouses = Warehouse::where('tenant_id', $this->tid())->where('is_active', true)->get();
        $products = Product::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();

        return view('wms.putaway-rules', compact('rules', 'warehouses', 'products'));
    }

    public function storePutawayRule(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_category' => 'nullable|string|max:50',
            'product_id' => 'nullable|exists:products,id',
            'zone_id' => 'nullable|exists:warehouse_zones,id',
            'bin_id' => 'nullable|exists:warehouse_bins,id',
            'priority' => 'nullable|integer|min:0',
        ]);

        PutawayRule::create(array_merge($data, ['tenant_id' => $this->tid(), 'is_active' => true, 'priority' => $data['priority'] ?? 0]));
        return back()->with('success', 'Putaway rule berhasil dibuat.');
    }

    public function destroyPutawayRule(PutawayRule $putawayRule)
    {
        abort_if($putawayRule->tenant_id !== $this->tid(), 403);
        $putawayRule->delete();
        return back()->with('success', 'Rule dihapus.');
    }
}
