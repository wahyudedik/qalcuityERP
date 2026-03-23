<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\DocumentNumberService;
use App\Services\GlPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['supplier', 'purchaseOrder'])
            ->where('tenant_id', $this->tid());

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('number', 'like', "%$s%")
                ->orWhereHas('supplier', fn($c) => $c->where('name', 'like', "%$s%")));
        }

        $returns = $query->latest()->paginate(20)->withQueryString();
        $stats = [
            'draft'     => PurchaseReturn::where('tenant_id', $this->tid())->where('status', 'draft')->count(),
            'sent'      => PurchaseReturn::where('tenant_id', $this->tid())->where('status', 'sent')->count(),
            'completed' => PurchaseReturn::where('tenant_id', $this->tid())->where('status', 'completed')->count(),
        ];

        return view('purchase-returns.index', compact('returns', 'stats'));
    }

    public function create()
    {
        $tid    = $this->tid();
        $suppliers  = Supplier::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();
        $orders     = PurchaseOrder::with('supplier')
            ->where('tenant_id', $tid)
            ->whereIn('status', ['received', 'completed'])
            ->orderBy('number')
            ->get();
        $warehouses = Warehouse::where('tenant_id', $tid)->where('is_active', true)->get();

        return view('purchase-returns.create', compact('suppliers', 'orders', 'warehouses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'supplier_id'       => 'required|exists:suppliers,id',
            'warehouse_id'      => 'required|exists:warehouses,id',
            'return_date'       => 'required|date',
            'reason'            => 'required|string|max:500',
            'refund_method'     => 'required|in:debit_note,cash,bank_transfer',
            'notes'             => 'nullable|string|max:1000',
            'items'             => 'required|array|min:1',
            'items.*.product_id'=> 'required|exists:products,id',
            'items.*.quantity'  => 'required|numeric|min:0.001',
            'items.*.price'     => 'required|numeric|min:0',
            'items.*.reason'    => 'nullable|string|max:255',
        ]);

        $tid = $this->tid();
        $po  = PurchaseOrder::where('tenant_id', $tid)->findOrFail($data['purchase_order_id']);

        DB::transaction(function () use ($data, $tid, $po) {
            $subtotal  = 0;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                $total     = $item['quantity'] * $item['price'];
                $subtotal += $total;
                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'total'      => $total,
                    'reason'     => $item['reason'] ?? null,
                ];
            }

            $number = app(DocumentNumberService::class)->generate($tid, 'PR');

            $return = PurchaseReturn::create([
                'tenant_id'         => $tid,
                'purchase_order_id' => $data['purchase_order_id'],
                'supplier_id'       => $data['supplier_id'],
                'warehouse_id'      => $data['warehouse_id'],
                'created_by'        => auth()->id(),
                'number'            => $number,
                'return_date'       => $data['return_date'],
                'reason'            => $data['reason'],
                'status'            => 'draft',
                'subtotal'          => $subtotal,
                'tax_amount'        => 0,
                'total'             => $subtotal,
                'refund_method'     => $data['refund_method'],
                'refund_amount'     => $subtotal,
                'is_cross_period'   => false,
                'notes'             => $data['notes'] ?? null,
            ]);

            $return->items()->createMany($itemsData);
            ActivityLog::record('purchase_return_created', "Retur pembelian dibuat: {$number}", $return);
        });

        return redirect()->route('purchase-returns.index')->with('success', 'Retur pembelian berhasil dibuat.');
    }

    public function send(PurchaseReturn $purchaseReturn)
    {
        abort_if($purchaseReturn->tenant_id !== $this->tid(), 403);
        abort_if($purchaseReturn->status !== 'draft', 422, 'Hanya retur draft yang bisa dikirim.');

        // Kurangi stok saat dikirim ke supplier
        DB::transaction(function () use ($purchaseReturn) {
            $tid = $this->tid();

            foreach ($purchaseReturn->items as $item) {
                $stock = ProductStock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $purchaseReturn->warehouse_id)
                    ->first();

                if ($stock && $stock->quantity >= $item->quantity) {
                    $before = $stock->quantity;
                    $stock->decrement('quantity', $item->quantity);

                    StockMovement::create([
                        'tenant_id'       => $tid,
                        'product_id'      => $item->product_id,
                        'warehouse_id'    => $purchaseReturn->warehouse_id,
                        'user_id'         => auth()->id(),
                        'type'            => 'out',
                        'quantity'        => $item->quantity,
                        'quantity_before' => $before,
                        'quantity_after'  => $before - $item->quantity,
                        'reference'       => $purchaseReturn->number,
                        'notes'           => "Retur pembelian {$purchaseReturn->number}",
                    ]);
                }
            }

            $purchaseReturn->update(['status' => 'sent']);
            ActivityLog::record('purchase_return_sent', "Retur {$purchaseReturn->number} dikirim ke supplier", $purchaseReturn);
        });

        return back()->with('success', "Retur {$purchaseReturn->number} dikirim ke supplier.");
    }

    public function complete(PurchaseReturn $purchaseReturn)
    {
        abort_if($purchaseReturn->tenant_id !== $this->tid(), 403);
        abort_if($purchaseReturn->status !== 'sent', 422, 'Hanya retur berstatus dikirim yang bisa diselesaikan.');

        DB::transaction(function () use ($purchaseReturn) {
            // GL Posting
            app(GlPostingService::class)->postPurchaseReturn(
                tenantId:     $this->tid(),
                userId:       auth()->id(),
                returnNumber: $purchaseReturn->number,
                returnId:     $purchaseReturn->id,
                subtotal:     (float) $purchaseReturn->subtotal,
                taxAmount:    (float) $purchaseReturn->tax_amount,
                total:        (float) $purchaseReturn->total,
                date:         $purchaseReturn->return_date->toDateString(),
            );

            $purchaseReturn->update(['status' => 'completed']);
            ActivityLog::record('purchase_return_completed', "Retur {$purchaseReturn->number} selesai", $purchaseReturn);
        });

        return back()->with('success', "Retur {$purchaseReturn->number} selesai.");
    }

    public function cancel(PurchaseReturn $purchaseReturn)
    {
        abort_if($purchaseReturn->tenant_id !== $this->tid(), 403);
        abort_if($purchaseReturn->status === 'completed', 422, 'Retur yang sudah selesai tidak bisa dibatalkan.');

        $purchaseReturn->update(['status' => 'cancelled']);
        ActivityLog::record('purchase_return_cancelled', "Retur {$purchaseReturn->number} dibatalkan", $purchaseReturn);

        return back()->with('success', "Retur {$purchaseReturn->number} dibatalkan.");
    }

    /** AJAX: ambil items dari PO */
    public function poItems(PurchaseOrder $purchaseOrder)
    {
        abort_if($purchaseOrder->tenant_id !== $this->tid(), 403);
        $purchaseOrder->load('items.product');
        $items = $purchaseOrder->items->map(fn($i) => [
            'product_id'   => $i->product_id,
            'product_name' => $i->product->name,
            'quantity'     => $i->quantity,
            'price'        => $i->price,
            'unit'         => $i->product->unit ?? 'pcs',
        ]);

        return response()->json($items);
    }
}
