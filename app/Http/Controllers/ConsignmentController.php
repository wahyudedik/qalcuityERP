<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentPartner;
use App\Models\ConsignmentSalesReport;
use App\Models\ConsignmentSettlement;
use App\Models\ConsignmentShipment;
use App\Models\ConsignmentShipmentItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\GlPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsignmentController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    // ── Dashboard ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = ConsignmentShipment::with(['partner', 'warehouse'])
            ->where('tenant_id', $this->tid());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('number', 'like', "%$s%"));
        }

        $shipments = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'partners' => ConsignmentPartner::where('tenant_id', $this->tid())->where('is_active', true)->count(),
            'active_shipments' => ConsignmentShipment::where('tenant_id', $this->tid())->whereIn('status', ['shipped', 'partial_sold'])->count(),
            'consigned_value' => ConsignmentShipment::where('tenant_id', $this->tid())->whereIn('status', ['shipped', 'partial_sold'])->sum('total_retail'),
            'pending_settlement' => ConsignmentSalesReport::where('tenant_id', $this->tid())->whereIn('status', ['draft', 'confirmed'])->sum('net_receivable'),
        ];

        $partners = ConsignmentPartner::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();
        $products = Product::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();

        return view('consignment.index', compact('shipments', 'stats', 'partners', 'warehouses', 'products'));
    }

    // ── Partners ──────────────────────────────────────────────────

    public function partners(Request $request)
    {
        $partners = ConsignmentPartner::where('tenant_id', $this->tid())
            ->withCount('shipments')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%"))
            ->latest()->paginate(20)->withQueryString();

        return view('consignment.partners', compact('partners'));
    }

    public function storePartner(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'commission_pct' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        ConsignmentPartner::create(array_merge($data, [
            'tenant_id' => $this->tid(),
            'commission_pct' => $data['commission_pct'] ?? 0,
            'is_active' => true,
        ]));

        return back()->with('success', 'Partner konsinyasi berhasil ditambahkan.');
    }

    public function destroyPartner(ConsignmentPartner $consignmentPartner)
    {
        abort_if($consignmentPartner->tenant_id !== $this->tid(), 403);
        $consignmentPartner->delete();

        return back()->with('success', 'Partner berhasil dihapus.');
    }

    // ── Shipments ─────────────────────────────────────────────────

    public function storeShipment(Request $request)
    {
        $data = $request->validate([
            'partner_id' => 'required|exists:consignment_partners,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'ship_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_sent' => 'required|numeric|min:0.001',
            'items.*.retail_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data) {
            $totalCost = 0;
            $totalRetail = 0;

            $shipment = ConsignmentShipment::create([
                'tenant_id' => $this->tid(),
                'number' => ConsignmentShipment::generateNumber($this->tid()),
                'partner_id' => $data['partner_id'],
                'warehouse_id' => $data['warehouse_id'],
                'ship_date' => $data['ship_date'],
                'status' => 'shipped',
                'user_id' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);
                $costPrice = $product->price_buy ?? 0;
                $qty = $item['quantity_sent'];

                ConsignmentShipmentItem::create([
                    'consignment_shipment_id' => $shipment->id,
                    'product_id' => $item['product_id'],
                    'quantity_sent' => $qty,
                    'cost_price' => $costPrice,
                    'retail_price' => $item['retail_price'],
                ]);

                $totalCost += $costPrice * $qty;
                $totalRetail += $item['retail_price'] * $qty;

                // Deduct stock from warehouse
                $stock = ProductStock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $data['warehouse_id'])->first();
                $before = $stock ? (float) $stock->quantity : 0;
                if ($stock) {
                    $stock->decrement('quantity', $qty);
                }

                StockMovement::create([
                    'tenant_id' => $this->tid(),
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'user_id' => auth()->id(),
                    'type' => 'out',
                    'quantity' => $qty,
                    'quantity_before' => $before,
                    'quantity_after' => max(0, $before - $qty),
                    'reference' => $shipment->number,
                    'notes' => "Konsinyasi ke {$data['partner_id']}",
                ]);
            }

            $shipment->update(['total_cost' => $totalCost, 'total_retail' => $totalRetail]);
        });

        return back()->with('success', 'Pengiriman konsinyasi berhasil. Stok telah dikurangi.');
    }

    public function show(ConsignmentShipment $consignmentShipment)
    {
        abort_if($consignmentShipment->tenant_id !== $this->tid(), 403);
        $consignmentShipment->load(['partner', 'warehouse', 'items.product', 'salesReports.settlements', 'user']);

        return view('consignment.show', compact('consignmentShipment'));
    }

    // ── Sales Reports ─────────────────────────────────────────────

    public function storeSalesReport(Request $request, ConsignmentShipment $consignmentShipment, GlPostingService $glService)
    {
        abort_if($consignmentShipment->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:consignment_shipment_items,id',
            'items.*.quantity_sold' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $partner = $consignmentShipment->partner;
        $commissionPct = $partner->commission_pct ?? 0;

        DB::transaction(function () use ($consignmentShipment, $data, $commissionPct, $glService) {
            $totalSales = 0;

            // Update sold quantities
            foreach ($data['items'] as $itemData) {
                $item = ConsignmentShipmentItem::find($itemData['item_id']);
                if (! $item || $item->consignment_shipment_id !== $consignmentShipment->id) {
                    continue;
                }

                $soldQty = min($itemData['quantity_sold'], $item->remainingQty());
                if ($soldQty <= 0) {
                    continue;
                }

                $item->increment('quantity_sold', $soldQty);
                $totalSales += $soldQty * $item->retail_price;
            }

            if ($totalSales <= 0) {
                return;
            }

            $commissionAmount = round($totalSales * $commissionPct / 100, 2);
            $netReceivable = $totalSales - $commissionAmount;

            $report = ConsignmentSalesReport::create([
                'tenant_id' => $this->tid(),
                'number' => ConsignmentSalesReport::generateNumber($this->tid()),
                'partner_id' => $consignmentShipment->partner_id,
                'consignment_shipment_id' => $consignmentShipment->id,
                'period_start' => $data['period_start'],
                'period_end' => $data['period_end'],
                'total_sales' => $totalSales,
                'commission_pct' => $commissionPct,
                'commission_amount' => $commissionAmount,
                'net_receivable' => $netReceivable,
                'status' => 'confirmed',
                'user_id' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            // Update shipment status
            $allSold = $consignmentShipment->items->every(fn ($i) => $i->fresh()->remainingQty() <= 0);
            $consignmentShipment->update(['status' => $allSold ? 'settled' : 'partial_sold']);

            // GL: Dr Piutang Konsinyasi (1104) / Cr Pendapatan (4101) + Dr Beban Komisi (5205) / Cr Piutang Konsinyasi (1104)
            if ($totalSales > 0) {
                $ref = $report->number;
                $glResult = $glService->postConsignmentSales(
                    $this->tid(), auth()->id(), $ref, $report->id,
                    $totalSales, $commissionAmount, now()->toDateString()
                );
                if ($glResult->isSuccess()) {
                    $report->update(['journal_entry_id' => $glResult->journal->id]);
                }
                if ($glResult->isFailed()) {
                    session()->flash('gl_warning', $glResult->warningMessage());
                }
            }
        });

        return back()->with('success', 'Laporan penjualan konsinyasi berhasil dicatat.');
    }

    // ── Settlements ───────────────────────────────────────────────

    public function storeSettlement(Request $request, ConsignmentSalesReport $consignmentSalesReport, GlPostingService $glService)
    {
        abort_if($consignmentSalesReport->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'settlement_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,transfer',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $remaining = $consignmentSalesReport->remainingBalance();
        if ($data['amount'] > $remaining + 0.01) {
            return back()->with('error', 'Jumlah melebihi sisa tagihan (Rp '.number_format($remaining, 0, ',', '.').').');
        }

        DB::transaction(function () use ($consignmentSalesReport, $data, $glService) {
            $settlement = ConsignmentSettlement::create(array_merge($data, [
                'tenant_id' => $this->tid(),
                'sales_report_id' => $consignmentSalesReport->id,
                'user_id' => auth()->id(),
            ]));

            // Check if fully settled
            if ($consignmentSalesReport->remainingBalance() <= 0.01) {
                $consignmentSalesReport->update(['status' => 'settled']);
            }

            // GL: Dr Kas/Bank / Cr Piutang Konsinyasi
            $cashCode = $data['payment_method'] === 'cash' ? '1101' : '1102';
            $ref = 'CSET-'.$consignmentSalesReport->number;
            $glResult = $glService->postConsignmentSettlement(
                $this->tid(), auth()->id(), $ref, $settlement->id,
                $data['amount'], $cashCode, $data['settlement_date']
            );
            if ($glResult->isSuccess()) {
                $settlement->update(['journal_entry_id' => $glResult->journal->id]);
            }
            if ($glResult->isFailed()) {
                session()->flash('gl_warning', $glResult->warningMessage());
            }
        });

        return back()->with('success', 'Settlement berhasil dicatat.');
    }

    // ── Return ────────────────────────────────────────────────────

    public function returnItems(Request $request, ConsignmentShipment $consignmentShipment)
    {
        abort_if($consignmentShipment->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:consignment_shipment_items,id',
            'items.*.quantity_returned' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($consignmentShipment, $data) {
            foreach ($data['items'] as $itemData) {
                $item = ConsignmentShipmentItem::find($itemData['item_id']);
                if (! $item || $item->consignment_shipment_id !== $consignmentShipment->id) {
                    continue;
                }

                $returnQty = min($itemData['quantity_returned'], $item->remainingQty());
                if ($returnQty <= 0) {
                    continue;
                }

                $item->increment('quantity_returned', $returnQty);

                // Return stock to warehouse
                $stock = ProductStock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $consignmentShipment->warehouse_id],
                    ['quantity' => 0]
                );
                $before = (float) $stock->quantity;
                $stock->increment('quantity', $returnQty);

                StockMovement::create([
                    'tenant_id' => $this->tid(),
                    'product_id' => $item->product_id,
                    'warehouse_id' => $consignmentShipment->warehouse_id,
                    'user_id' => auth()->id(),
                    'type' => 'in',
                    'quantity' => $returnQty,
                    'quantity_before' => $before,
                    'quantity_after' => $before + $returnQty,
                    'reference' => $consignmentShipment->number,
                    'notes' => "Retur konsinyasi dari {$consignmentShipment->partner->name}",
                ]);
            }

            // Update status
            $allDone = $consignmentShipment->items->every(fn ($i) => $i->fresh()->remainingQty() <= 0);
            if ($allDone) {
                $consignmentShipment->update(['status' => 'returned']);
            }
        });

        return back()->with('success', 'Barang retur berhasil dikembalikan ke gudang.');
    }
}
