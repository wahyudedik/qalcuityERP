<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BinStock;
use App\Models\FarmPlot;
use App\Models\FarmPlotActivity;
use App\Models\LivestockHerd;
use App\Models\PickingList;
use App\Models\PickingListItem;
use App\Models\StockMovement;
use App\Models\StockOpnameItem;
use App\Models\StockOpnameSession;
use App\Models\Warehouse;
use App\Models\WarehouseBin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    // ── Hub (Dashboard Mode Lapangan) ─────────────────────────────

    public function hub(Request $request)
    {
        $user = $request->user();
        $tenantId = $this->tid();

        $pendingPicking = PickingList::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        $pendingOpname = StockOpnameSession::where('tenant_id', $tenantId)
            ->whereIn('status', ['draft', 'in_progress'])
            ->count();

        $myPicking = PickingList::where('tenant_id', $tenantId)
            ->where('assigned_to', $user->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        return view('mobile.hub', compact('user', 'pendingPicking', 'pendingOpname', 'myPicking'));
    }

    // ── Picking List ──────────────────────────────────────────────

    public function picking(Request $request)
    {
        $user = $request->user();

        $pickingLists = PickingList::with(['warehouse', 'assignee', 'items'])
            ->where('tenant_id', $this->tid())
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('mobile.picking', compact('pickingLists'));
    }

    public function pickingShow(Request $request, $id)
    {
        $user = $request->user();

        $pickingList = PickingList::with(['warehouse', 'assignee', 'items.product', 'items.bin'])
            ->where('tenant_id', $this->tid())
            ->findOrFail($id);

        $items = $pickingList->items;
        $pickedCount = $items->whereIn('status', ['picked', 'short'])->count();
        $totalCount = $items->count();

        return view('mobile.picking-show', compact('pickingList', 'items', 'pickedCount', 'totalCount'));
    }

    public function pickingConfirm(Request $request, $id)
    {
        $item = PickingListItem::with('pickingList')->findOrFail($id);
        $list = $item->pickingList;

        abort_if($list->tenant_id !== $this->tid(), 403);

        $qty = $request->validate([
            'quantity_picked' => 'required|numeric|min:0',
        ])['quantity_picked'];

        DB::transaction(function () use ($item, $qty) {
            $item->update([
                'quantity_picked' => $qty,
                'status' => $qty >= $item->quantity_requested ? 'picked' : 'short',
            ]);

            // Deduct from bin stock
            if ($item->bin_id && $qty > 0) {
                $binStock = BinStock::where('bin_id', $item->bin_id)
                    ->where('product_id', $item->product_id)
                    ->first();
                if ($binStock) {
                    $binStock->decrement('quantity', min($qty, $binStock->quantity));
                }
            }

            // Update picking list status
            $list = $item->pickingList;
            if ($list->items()->where('status', 'pending')->count() === 0) {
                $list->update(['status' => 'completed', 'completed_at' => now()]);
            } elseif ($list->status === 'pending') {
                $list->update(['status' => 'in_progress', 'started_at' => now()]);
            }
        });

        return back()->with('success', 'Item berhasil dikonfirmasi.');
    }

    // ── Stock Opname ──────────────────────────────────────────────

    public function opname(Request $request)
    {
        $opnameSessions = StockOpnameSession::with('warehouse')
            ->where('tenant_id', $this->tid())
            ->whereIn('status', ['draft', 'in_progress'])
            ->latest()
            ->get();

        return view('mobile.opname', compact('opnameSessions'));
    }

    public function opnameShow(Request $request, $id)
    {
        $opname = StockOpnameSession::with(['warehouse', 'items.product', 'items.bin'])
            ->where('tenant_id', $this->tid())
            ->findOrFail($id);

        $items = $opname->items;
        $countedItems = $items->whereNotNull('actual_qty')->count();
        $totalItems = $items->count();

        return view('mobile.opname-show', compact('opname', 'items', 'countedItems', 'totalItems'));
    }

    public function opnameUpdate(Request $request, $id)
    {
        $item = StockOpnameItem::with('session')->findOrFail($id);

        abort_if($item->session->tenant_id !== $this->tid(), 403);

        $validated = $request->validate([
            'actual_qty' => 'required|numeric|min:0',
        ]);

        $item->update([
            'actual_qty' => $validated['actual_qty'],
            'difference' => $validated['actual_qty'] - $item->system_qty,
        ]);

        // Auto-transition session to in_progress when first item is counted
        if ($item->session->status === 'draft') {
            $item->session->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Jumlah aktual berhasil disimpan.');
    }

    public function opnameComplete(int $id)
    {
        $session = StockOpnameSession::where('tenant_id', $this->tid())
            ->findOrFail($id);

        $session->update(['status' => 'completed']);

        return redirect()->route('mobile.opname')
            ->with('success', 'Opname berhasil diselesaikan.');
    }

    // ── Farm Activity ─────────────────────────────────────────────

    public function farmActivity(Request $request)
    {
        $plots = FarmPlot::where('tenant_id', $this->tid())
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $herds = LivestockHerd::where('tenant_id', $this->tid())
            ->where('status', 'active')
            ->orderBy('code')
            ->get();

        return view('mobile.farm-activity', compact('plots', 'herds'));
    }

    public function farmActivityStore(Request $request)
    {
        $validated = $request->validate([
            'farm_plot_id' => 'required|exists:farm_plots,id',
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

        $farmPlot = FarmPlot::where('tenant_id', $this->tid())->findOrFail($validated['farm_plot_id']);

        FarmPlotActivity::create(array_merge($validated, [
            'tenant_id' => $this->tid(),
            'user_id' => auth()->id(),
        ]));

        // Auto-update plot status based on activity type
        $autoStatus = match ($validated['activity_type']) {
            'soil_prep' => 'preparing',
            'planting' => 'planted',
            'harvesting' => 'harvesting',
            default => null,
        };
        if ($autoStatus && $farmPlot->status !== $autoStatus) {
            $updates = ['status' => $autoStatus];
            if ($validated['activity_type'] === 'planting') {
                $updates['planted_at'] = $validated['date'];
            }
            $farmPlot->update($updates);
        }

        ActivityLog::record('farm_activity', "{$validated['activity_type']} di lahan {$farmPlot->code}: {$validated['description']}");

        return back()->with('success', 'Aktivitas lapangan berhasil dicatat.');
    }

    // ── Batch Opname Update (for bulk inventory updates) ───────────────────────

    public function opnameBatchUpdate(Request $request, int $id)
    {
        $session = StockOpnameSession::where('tenant_id', $this->tid())->findOrFail($id);
        abort_if($session->status === 'completed', 403, 'Sesi opname sudah selesai');

        $user = $request->user();
        $itemsData = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:stock_opname_items,id',
            'items.*.actual_qty' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($session, $itemsData, $user) {
            foreach ($itemsData['items'] as $itemData) {
                $item = StockOpnameItem::findOrFail($itemData['item_id']);
                abort_if($item->session_id !== $session->id, 403);

                $item->update([
                    'actual_qty' => $itemData['actual_qty'],
                    'difference' => $itemData['actual_qty'] - $item->system_qty,
                ]);

                ActivityLog::record(
                    'opname_batch',
                    "Opname batch item #{$item->id}: sistem={$item->system_qty}, aktual={$itemData['actual_qty']}",
                    ['user_id' => $user->id]
                );
            }

            // Auto-transition to in_progress
            if ($session->status === 'draft') {
                $session->update(['status' => 'in_progress']);
            }
        });

        return response()->json(['ok' => true, 'count' => count($itemsData['items'])]);
    }

    // ── Batch Picking Confirm (for bulk picking updates) ────────────────────────

    public function pickingBatchConfirm(Request $request, int $id)
    {
        $pickingList = PickingList::where('tenant_id', $this->tid())->findOrFail($id);
        abort_if(! in_array($pickingList->status, ['pending', 'in_progress']), 403, 'Picking list sudah selesai');

        $user = $request->user();
        $itemsData = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:picking_list_items,id',
            'items.*.quantity_picked' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($pickingList, $itemsData, $user) {
            foreach ($itemsData['items'] as $itemData) {
                $item = PickingListItem::findOrFail($itemData['item_id']);
                abort_if($item->picking_list_id !== $pickingList->id, 403);

                $qty = $itemData['quantity_picked'];
                $item->update([
                    'quantity_picked' => $qty,
                    'status' => $qty >= $item->quantity_requested ? 'picked' : 'short',
                ]);

                // Deduct from bin stock
                if ($item->bin_id && $qty > 0) {
                    $binStock = BinStock::where('bin_id', $item->bin_id)
                        ->where('product_id', $item->product_id)
                        ->first();
                    if ($binStock) {
                        $binStock->decrement('quantity', min($qty, $binStock->quantity));
                    }
                }

                ActivityLog::record(
                    'picking_batch',
                    "Picking batch item #{$item->id}: qty={$qty}",
                    ['user_id' => $user->id]
                );
            }

            // Update picking list status
            if ($pickingList->items()->where('status', 'pending')->count() === 0) {
                $pickingList->update(['status' => 'completed', 'completed_at' => now()]);
            } elseif ($pickingList->status === 'pending') {
                $pickingList->update(['status' => 'in_progress', 'started_at' => now()]);
            }
        });

        return response()->json(['ok' => true, 'count' => count($itemsData['items'])]);
    }

    // ── Transfer Stok (Warehouse/Bin transfer) ──────────────────────────────────

    public function transfer(Request $request)
    {
        $user = $request->user();
        $tenantId = $this->tid();

        $warehouses = Warehouse::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $bins = WarehouseBin::with('warehouse')
            ->whereHas('warehouse', fn ($q) => $q->where('tenant_id', $tenantId))
            ->orderBy('code')
            ->get(['id', 'code', 'warehouse_id']);

        return view('mobile.transfer', compact('warehouses', 'bins'));
    }

    public function transferStore(Request $request)
    {
        $user = $request->user();
        $tenantId = $this->tid();

        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_bin_id' => 'required|exists:warehouse_bins,id',
            'to_bin_id' => 'required|exists:warehouse_bins,id|different:from_bin_id',
            'quantity' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        // Verify bins belong to tenant
        $fromBin = WarehouseBin::where('tenant_id', $tenantId)->findOrFail($data['from_bin_id']);
        $toBin = WarehouseBin::where('tenant_id', $tenantId)->findOrFail($data['to_bin_id']);

        // Check source bin has enough stock
        $sourceStock = BinStock::where('bin_id', $fromBin->id)
            ->where('product_id', $data['product_id'])
            ->first();
        abort_if(! $sourceStock || $sourceStock->quantity < $data['quantity'], 422, 'Stok tidak mencukupi di bin asal');

        DB::transaction(function () use ($data, $tenantId, $user, $fromBin, $toBin, $sourceStock) {
            // Decrement source bin
            $sourceStock->decrement('quantity', $data['quantity']);

            // Increment destination bin
            $destStock = BinStock::firstOrCreate(
                ['bin_id' => $toBin->id, 'product_id' => $data['product_id'], 'tenant_id' => $tenantId],
                ['quantity' => 0]
            );
            $destStock->increment('quantity', $data['quantity']);

            // Log stock movement
            StockMovement::create([
                'tenant_id' => $tenantId,
                'product_id' => $data['product_id'],
                'warehouse_id' => $fromBin->warehouse_id,
                'to_warehouse_id' => $toBin->warehouse_id,
                'user_id' => $user->id,
                'type' => 'transfer',
                'quantity' => $data['quantity'],
                'reference' => 'TRF-'.now()->format('YmdHis'),
                'notes' => $data['notes'],
                'quantity_before' => $sourceStock->quantity,
                'quantity_after' => $sourceStock->quantity - $data['quantity'],
            ]);

            ActivityLog::record('stock_transfer', "Transfer stok: {$data['quantity']} dari bin {$fromBin->code} ke bin {$toBin->code}");
        });

        return redirect()->route('mobile.transfer')->with('success', 'Transfer stok berhasil.');
    }
}
