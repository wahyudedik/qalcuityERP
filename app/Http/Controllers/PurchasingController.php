<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\Rfq;
use App\Models\RfqItem;
use App\Models\RfqResponse;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\DocumentNumberService;
use App\Services\GlPostingService;
use App\Services\InventoryCostingService;
use App\Services\TransactionStateMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchasingController extends Controller
{
    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    // ── Suppliers ──────────────────────────────────────────────────

    public function suppliers(Request $request)
    {
        $tid   = $this->tenantId();
        $query = Supplier::where('tenant_id', $tid);

        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('company', 'like', "%$s%"));
        }

        $suppliers = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('purchasing.suppliers', compact('suppliers'));
    }

    public function storeSupplier(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $tid = $this->tenantId();

        if (Supplier::where('tenant_id', $tid)->where('name', $data['name'])->exists()) {
            return back()->withErrors(['name' => 'Supplier dengan nama ini sudah ada.'])->withInput();
        }

        Supplier::create(['tenant_id' => $tid, 'is_active' => true] + $data);

        ActivityLog::record('supplier_created', "Supplier baru: {$data['name']}", null, [], $data);

        return back()->with('success', "Supplier {$data['name']} berhasil ditambahkan.");
    }

    public function updateSupplier(Request $request, Supplier $supplier)
    {
        abort_unless($supplier->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'company'   => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:255',
            'address'   => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier->update($data);

        return back()->with('success', "Supplier {$supplier->name} berhasil diperbarui.");
    }

    // ── Purchase Orders ────────────────────────────────────────────

    public function orders(Request $request)
    {
        $tid   = $this->tenantId();
        $query = PurchaseOrder::where('tenant_id', $tid)->with(['supplier', 'warehouse']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('number', 'like', "%$s%")
                ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%$s%")));
        }

        $orders     = $query->latest('date')->paginate(20)->withQueryString();
        $suppliers  = Supplier::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('tenant_id', $tid)->where('is_active', true)->get();
        $products   = Product::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();

        return view('purchasing.orders', compact('orders', 'suppliers', 'warehouses', 'products'));
    }

    public function storeOrder(Request $request)
    {
        $data = $request->validate([
            'supplier_id'   => 'required|exists:suppliers,id',
            'warehouse_id'  => 'required|exists:warehouses,id',
            'date'          => 'required|date',
            'expected_date' => 'nullable|date',
            'payment_type'  => 'required|in:cash,credit',
            'notes'         => 'nullable|string',
            'items'         => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        $tid      = $this->tenantId();

        // Cek period lock
        app(\App\Services\PeriodLockService::class)->assertNotLocked($tid, $data['date'], 'Purchase Order');

        $subtotal = 0;
        $itemsData = [];

        foreach ($data['items'] as $item) {
            $total     = $item['price'] * $item['quantity'];
            $subtotal += $total;
            $itemsData[] = [
                'product_id'        => $item['product_id'],
                'quantity_ordered'  => $item['quantity'],
                'quantity_received' => 0,
                'price'             => $item['price'],
                'total'             => $total,
            ];
        }

        // Task 37: Nomor sequential via DocumentNumberService
        $numberSvc = app(DocumentNumberService::class);
        $poNumber  = $numberSvc->generate($tid, 'po');

        $po = PurchaseOrder::create([
            'tenant_id'    => $tid,
            'supplier_id'  => $data['supplier_id'],
            'user_id'      => auth()->id(),
            'warehouse_id' => $data['warehouse_id'],
            'number'       => $poNumber,
            'doc_sequence' => (int) substr($poNumber, strrpos($poNumber, '-') + 1),
            'doc_year'     => date('Y'),
            'status'       => 'draft',
            'posting_status' => 'draft',  // Task 35
            'date'         => $data['date'],
            'expected_date'=> $data['expected_date'] ?? null,
            'subtotal'     => $subtotal,
            'total'        => $subtotal,
            'payment_type' => $data['payment_type'],
            'notes'        => $data['notes'] ?? null,
        ]);

        $po->items()->createMany($itemsData);

        $supplierName = $po->supplier->name ?? '-';
        ActivityLog::record('purchase_order_created', "PO dibuat: {$po->number} (Supplier: {$supplierName}, Total: Rp " . number_format($subtotal, 0, ',', '.') . ")", $po, [], $po->toArray());

        return back()->with('success', "PO {$po->number} berhasil dibuat.");
    }

    public function updateOrderStatus(Request $request, PurchaseOrder $order)
    {
        abort_unless($order->tenant_id === $this->tenantId(), 403);

        $data      = $request->validate(['status' => 'required|in:draft,sent,partial,received,cancelled']);
        $oldStatus = $order->status;

        // Task 35: Cek apakah boleh ubah status operasional
        // Status operasional (sent/partial/received) hanya bisa diubah jika sudah posted
        if (in_array($data['status'], ['sent', 'partial', 'received']) && ! $order->isPosted()) {
            return back()->with('error', 'PO harus diposting terlebih dahulu sebelum bisa diproses.');
        }

        $order->update(['status' => $data['status']]);
        ActivityLog::record('purchase_order_status_changed', "Status PO {$order->number} berubah: {$oldStatus} → {$data['status']}", $order, ['status' => $oldStatus], ['status' => $data['status']]);

        // GL Auto-Posting saat PO diterima
        if ($data['status'] === 'received' && $oldStatus !== 'received') {
            app(GlPostingService::class)->postPurchaseReceived(
                tenantId:    $order->tenant_id,
                userId:      auth()->id(),
                poNumber:    $order->number,
                poId:        $order->id,
                total:       (float) $order->total,
                taxAmount:   (float) ($order->tax_amount ?? 0),
                paymentType: $order->payment_type ?? 'credit',
                date:        today()->toDateString(),
            );
        }

        return back()->with('success', "Status PO {$order->number} diperbarui.");
    }

    // Task 35: Post PO
    public function postOrder(PurchaseOrder $order)
    {
        abort_unless($order->tenant_id === $this->tenantId(), 403);

        try {
            app(TransactionStateMachine::class)->postPurchaseOrder($order, auth()->id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "PO {$order->number} berhasil diposting.");
    }

    // Task 35: Cancel PO
    public function cancelOrder(Request $request, PurchaseOrder $order)
    {
        abort_unless($order->tenant_id === $this->tenantId(), 403);

        $data = $request->validate(['reason' => 'required|string|max:255']);

        try {
            app(TransactionStateMachine::class)->cancelPurchaseOrder($order, auth()->id(), $data['reason']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "PO {$order->number} berhasil dibatalkan.");
    }

    // ── Purchase Requisition ───────────────────────────────────────

    public function requisitions(Request $request)
    {
        $tid   = $this->tenantId();
        $query = PurchaseRequisition::where('tenant_id', $tid)->with('requester');

        if ($request->status) $query->where('status', $request->status);

        $requisitions = $query->latest()->paginate(20)->withQueryString();
        $products     = Product::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();

        $stats = [
            'pending'  => PurchaseRequisition::where('tenant_id', $tid)->where('status', 'pending')->count(),
            'approved' => PurchaseRequisition::where('tenant_id', $tid)->where('status', 'approved')->count(),
            'total'    => PurchaseRequisition::where('tenant_id', $tid)->count(),
        ];

        return view('purchasing.requisitions', compact('requisitions', 'products', 'stats'));
    }

    public function storeRequisition(Request $request)
    {
        $data = $request->validate([
            'department'    => 'nullable|string|max:100',
            'required_date' => 'nullable|date',
            'purpose'       => 'nullable|string|max:500',
            'items'         => 'required|array|min:1',
            'items.*.description'     => 'required|string|max:255',
            'items.*.product_id'      => 'nullable|exists:products,id',
            'items.*.quantity'        => 'required|numeric|min:0.01',
            'items.*.unit'            => 'nullable|string|max:20',
            'items.*.estimated_price' => 'nullable|numeric|min:0',
        ]);

        $tid   = $this->tenantId();
        // Task 37: Nomor sequential
        $total = 0;
        $itemsData = [];
        foreach ($data['items'] as $item) {
            $price = (float) ($item['estimated_price'] ?? 0);
            $qty   = (float) $item['quantity'];
            $sub   = $price * $qty;
            $total += $sub;
            $itemsData[] = [
                'product_id'      => $item['product_id'] ?? null,
                'description'     => $item['description'],
                'quantity'        => $qty,
                'unit'            => $item['unit'] ?? null,
                'estimated_price' => $price,
                'estimated_total' => $sub,
            ];
        }

        $pr = PurchaseRequisition::create([
            'tenant_id'       => $tid,
            'requested_by'    => auth()->id(),
            'number'          => app(DocumentNumberService::class)->generate($tid, 'pr'),
            'department'      => $data['department'] ?? null,
            'required_date'   => $data['required_date'] ?? null,
            'purpose'         => $data['purpose'] ?? null,
            'status'          => 'pending',
            'estimated_total' => $total,
        ]);

        $pr->items()->createMany($itemsData);

        ActivityLog::record('pr_created', "PR dibuat: {$pr->number}", $pr);

        return back()->with('success', "PR {$pr->number} berhasil dibuat.");
    }

    public function approveRequisition(Request $request, PurchaseRequisition $requisition)
    {
        abort_unless($requisition->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'action'           => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:action,rejected|nullable|string|max:300',
        ]);

        $requisition->update([
            'status'           => $data['action'],
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
            'rejection_reason' => $data['rejection_reason'] ?? null,
        ]);

        $label = $data['action'] === 'approved' ? 'disetujui' : 'ditolak';
        ActivityLog::record('pr_' . $data['action'], "PR {$requisition->number} {$label}", $requisition);

        return back()->with('success', "PR {$requisition->number} berhasil {$label}.");
    }

    public function convertRequisitionToPo(Request $request, PurchaseRequisition $requisition)
    {
        abort_unless($requisition->tenant_id === $this->tenantId(), 403);
        abort_unless($requisition->status === 'approved', 403, 'Hanya PR yang sudah disetujui yang bisa dikonversi.');

        $data = $request->validate([
            'supplier_id'  => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'date'         => 'required|date',
            'payment_type' => 'required|in:cash,credit',
        ]);

        $tid = $this->tenantId();

        $po = PurchaseOrder::create([
            'tenant_id'                => $tid,
            'supplier_id'              => $data['supplier_id'],
            'user_id'                  => auth()->id(),
            'warehouse_id'             => $data['warehouse_id'],
            'purchase_requisition_id'  => $requisition->id,
            'number'                   => 'PO-' . strtoupper(Str::random(8)),
            'status'                   => 'draft',
            'date'                     => $data['date'],
            'subtotal'                 => $requisition->estimated_total,
            'total'                    => $requisition->estimated_total,
            'payment_type'             => $data['payment_type'],
        ]);

        foreach ($requisition->items as $item) {
            $po->items()->create([
                'product_id'        => $item->product_id,
                'quantity_ordered'  => $item->quantity,
                'quantity_received' => 0,
                'price'             => $item->estimated_price,
                'total'             => $item->estimated_total,
            ]);
        }

        $requisition->update(['status' => 'converted']);

        ActivityLog::record('pr_converted', "PR {$requisition->number} dikonversi ke PO {$po->number}", $po);

        return redirect()->route('purchasing.orders')
            ->with('success', "PR {$requisition->number} berhasil dikonversi ke PO {$po->number}.");
    }

    // ── RFQ ───────────────────────────────────────────────────────

    public function rfqs(Request $request)
    {
        $tid  = $this->tenantId();
        $rfqs = Rfq::where('tenant_id', $tid)
            ->with(['creator', 'responses.supplier'])
            ->latest()
            ->paginate(20);

        $suppliers    = Supplier::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();
        $products     = Product::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();
        $requisitions = PurchaseRequisition::where('tenant_id', $tid)->where('status', 'approved')->get();

        return view('purchasing.rfq', compact('rfqs', 'suppliers', 'products', 'requisitions'));
    }

    public function storeRfq(Request $request)
    {
        $data = $request->validate([
            'purchase_requisition_id' => 'nullable|exists:purchase_requisitions,id',
            'issue_date'              => 'required|date',
            'deadline'                => 'required|date|after_or_equal:issue_date',
            'notes'                   => 'nullable|string|max:500',
            'items'                   => 'required|array|min:1',
            'items.*.description'     => 'required|string|max:255',
            'items.*.product_id'      => 'nullable|exists:products,id',
            'items.*.quantity'        => 'required|numeric|min:0.01',
            'items.*.unit'            => 'nullable|string|max:20',
        ]);

        $tid   = $this->tenantId();

        $rfq = Rfq::create([
            'tenant_id'               => $tid,
            'purchase_requisition_id' => $data['purchase_requisition_id'] ?? null,
            'created_by'              => auth()->id(),
            'number'                  => app(DocumentNumberService::class)->generate($tid, 'rfq'),
            'issue_date'              => $data['issue_date'],
            'deadline'                => $data['deadline'],
            'notes'                   => $data['notes'] ?? null,
            'status'                  => 'open',
        ]);

        $rfq->items()->createMany(array_map(fn($i) => [
            'product_id'  => $i['product_id'] ?? null,
            'description' => $i['description'],
            'quantity'    => $i['quantity'],
            'unit'        => $i['unit'] ?? null,
        ], $data['items']));

        ActivityLog::record('rfq_created', "RFQ dibuat: {$rfq->number}", $rfq);

        return back()->with('success', "RFQ {$rfq->number} berhasil dibuat.");
    }

    public function storeRfqResponse(Request $request, Rfq $rfq)
    {
        abort_unless($rfq->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'supplier_id'   => 'required|exists:suppliers,id',
            'response_date' => 'required|date',
            'total_price'   => 'required|numeric|min:0',
            'delivery_days' => 'nullable|integer|min:1',
            'payment_terms' => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:500',
        ]);

        RfqResponse::updateOrCreate(
            ['rfq_id' => $rfq->id, 'supplier_id' => $data['supplier_id']],
            $data
        );

        return back()->with('success', 'Penawaran supplier berhasil disimpan.');
    }

    public function selectRfqResponse(RfqResponse $response)
    {
        $rfq = $response->rfq;
        abort_unless($rfq->tenant_id === $this->tenantId(), 403);

        // Deselect all, then select this one
        RfqResponse::where('rfq_id', $rfq->id)->update(['is_selected' => false]);
        $response->update(['is_selected' => true]);

        ActivityLog::record('rfq_response_selected', "Penawaran {$response->supplier->name} dipilih untuk RFQ {$rfq->number}", $response);

        return back()->with('success', "Penawaran {$response->supplier->name} dipilih.");
    }

    public function convertRfqToPo(Request $request, Rfq $rfq)
    {
        abort_unless($rfq->tenant_id === $this->tenantId(), 403);

        $selected = $rfq->selectedResponse();
        abort_unless($selected, 422, 'Pilih penawaran supplier terlebih dahulu.');

        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'date'         => 'required|date',
            'payment_type' => 'required|in:cash,credit',
        ]);

        $tid = $this->tenantId();

        $po = PurchaseOrder::create([
            'tenant_id'               => $tid,
            'supplier_id'             => $selected->supplier_id,
            'user_id'                 => auth()->id(),
            'warehouse_id'            => $data['warehouse_id'],
            'rfq_id'                  => $rfq->id,
            'purchase_requisition_id' => $rfq->purchase_requisition_id,
            'number'                  => 'PO-' . strtoupper(Str::random(8)),
            'status'                  => 'draft',
            'date'                    => $data['date'],
            'subtotal'                => $selected->total_price,
            'total'                   => $selected->total_price,
            'payment_type'            => $data['payment_type'],
        ]);

        // Create PO items from RFQ items using selected response item_prices
        $itemPrices = collect($selected->item_prices ?? []);
        foreach ($rfq->items as $rfqItem) {
            $priceData = $itemPrices->firstWhere('rfq_item_id', $rfqItem->id);
            $unitPrice = (float) ($priceData['unit_price'] ?? 0);
            $po->items()->create([
                'product_id'        => $rfqItem->product_id,
                'quantity_ordered'  => $rfqItem->quantity,
                'quantity_received' => 0,
                'price'             => $unitPrice,
                'total'             => $unitPrice * $rfqItem->quantity,
            ]);
        }

        $rfq->update(['status' => 'converted']);

        ActivityLog::record('rfq_converted', "RFQ {$rfq->number} dikonversi ke PO {$po->number}", $po);

        return redirect()->route('purchasing.orders')
            ->with('success', "RFQ {$rfq->number} berhasil dikonversi ke PO {$po->number}.");
    }

    // ── Goods Receipt ─────────────────────────────────────────────

    public function goodsReceipts(Request $request)
    {
        $tid      = $this->tenantId();
        $receipts = GoodsReceipt::where('tenant_id', $tid)
            ->with(['purchaseOrder.supplier', 'warehouse'])
            ->latest('receipt_date')
            ->paginate(20);

        $openPos = PurchaseOrder::where('tenant_id', $tid)
            ->whereIn('status', ['sent', 'partial'])
            ->with(['supplier', 'items.product'])
            ->orderBy('date')
            ->get();

        $warehouses = Warehouse::where('tenant_id', $tid)->where('is_active', true)->get();

        return view('purchasing.goods-receipts', compact('receipts', 'openPos', 'warehouses'));
    }

    public function storeGoodsReceipt(Request $request)
    {
        $data = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'warehouse_id'      => 'required|exists:warehouses,id',
            'receipt_date'      => 'required|date',
            'delivery_note'     => 'nullable|string|max:100',
            'notes'             => 'nullable|string|max:500',
            'items'             => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.product_id'             => 'required|exists:products,id',
            'items.*.quantity_received'      => 'required|numeric|min:0',
            'items.*.quantity_accepted'      => 'required|numeric|min:0',
            'items.*.quantity_rejected'      => 'nullable|numeric|min:0',
            'items.*.rejection_reason'       => 'nullable|string|max:255',
        ]);

        $tid = $this->tenantId();
        $po  = PurchaseOrder::where('tenant_id', $tid)->findOrFail($data['purchase_order_id']);

        $gr = GoodsReceipt::create([
            'tenant_id'         => $tid,
            'purchase_order_id' => $po->id,
            'warehouse_id'      => $data['warehouse_id'],
            'received_by'       => auth()->id(),
            'number'            => app(DocumentNumberService::class)->generate($tid, 'gr'),
            'receipt_date'      => $data['receipt_date'],
            'delivery_note'     => $data['delivery_note'] ?? null,
            'status'            => 'confirmed',
            'notes'             => $data['notes'] ?? null,
        ]);

        $costing = app(InventoryCostingService::class);

        foreach ($data['items'] as $item) {
            $gr->items()->create([
                'purchase_order_item_id' => $item['purchase_order_item_id'],
                'product_id'             => $item['product_id'],
                'quantity_received'      => $item['quantity_received'],
                'quantity_accepted'      => $item['quantity_accepted'],
                'quantity_rejected'      => $item['quantity_rejected'] ?? 0,
                'rejection_reason'       => $item['rejection_reason'] ?? null,
            ]);

            // Update PO item received qty
            $poItem = PurchaseOrderItem::find($item['purchase_order_item_id']);
            if ($poItem) {
                $poItem->increment('quantity_received', $item['quantity_accepted']);

                // Record cost-in for AVCO/FIFO (no-op for 'simple' tenants)
                if ($item['quantity_accepted'] > 0) {
                    $movement = \App\Models\StockMovement::where('tenant_id', $tid)
                        ->where('product_id', $item['product_id'])
                        ->where('warehouse_id', $data['warehouse_id'])
                        ->where('type', 'in')
                        ->where('reference', $gr->number)
                        ->latest()
                        ->first();

                    if ($movement) {
                        $costing->recordStockIn($movement, (float) $poItem->price);
                    }
                }
            }
        }
        $po->load('items');
        $allReceived = $po->items->every(fn($i) => $i->quantity_received >= $i->quantity_ordered);
        $anyReceived = $po->items->some(fn($i) => $i->quantity_received > 0);
        $po->update(['status' => $allReceived ? 'received' : ($anyReceived ? 'partial' : $po->status)]);

        // GL posting if fully received
        if ($allReceived) {
            app(GlPostingService::class)->postPurchaseReceived(
                tenantId:    $tid,
                userId:      auth()->id(),
                poNumber:    $po->number,
                poId:        $po->id,
                total:       (float) $po->total,
                taxAmount:   (float) ($po->tax_amount ?? 0),
                paymentType: $po->payment_type ?? 'credit',
                date:        $data['receipt_date'],
            );
        }

        ActivityLog::record('goods_receipt_created', "GR dibuat: {$gr->number} untuk PO {$po->number}", $gr);

        return back()->with('success', "Goods Receipt {$gr->number} berhasil dicatat.");
    }

    // ── 3-Way Matching ────────────────────────────────────────────

    public function matching(Request $request)
    {
        $tid = $this->tenantId();

        $query = PurchaseOrder::where('tenant_id', $tid)
            ->with(['supplier', 'items.product', 'goodsReceipts.items', 'payable'])
            ->whereIn('status', ['sent', 'partial', 'received']);

        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('number', 'like', "%$s%")
                ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%$s%")));
        }

        $orders = $query->latest('date')->paginate(15)->withQueryString();

        // Compute matching status for each PO
        $matchingData = $orders->map(function (PurchaseOrder $po) {
            $poTotal  = (float) $po->total;
            $grTotal  = $po->goodsReceipts->where('status', 'confirmed')
                ->flatMap->items->sum(fn($i) => $i->quantity_accepted * ($po->items->firstWhere('id', $i->purchase_order_item_id)?->price ?? 0));
            $invTotal = $po->payable->sum('amount');

            $poQty = $po->items->sum('quantity_ordered');
            $grQty = $po->goodsReceipts->where('status', 'confirmed')->flatMap->items->sum('quantity_accepted');

            $poMatch  = true; // PO is the reference
            $grMatch  = $poQty > 0 && abs($grQty - $poQty) / $poQty <= 0.02; // ±2% tolerance
            $invMatch = $poTotal > 0 && abs($invTotal - $poTotal) / $poTotal <= 0.02;

            return [
                'po'        => $po,
                'po_total'  => $poTotal,
                'gr_total'  => $grTotal,
                'inv_total' => $invTotal,
                'gr_qty'    => $grQty,
                'po_qty'    => $poQty,
                'po_match'  => $poMatch,
                'gr_match'  => $grMatch,
                'inv_match' => $invMatch,
                'status'    => $poMatch && $grMatch && $invMatch ? 'matched' : ($grMatch ? 'partial' : 'unmatched'),
            ];
        });

        return view('purchasing.matching', compact('orders', 'matchingData'));
    }
}
