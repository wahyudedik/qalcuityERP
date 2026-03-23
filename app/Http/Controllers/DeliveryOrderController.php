<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\Invoice;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryOrderController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = DeliveryOrder::with(['salesOrder.customer', 'warehouse'])
            ->where('tenant_id', $this->tid());

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('number', 'like', "%$s%")
                ->orWhereHas('salesOrder', fn($so) => $so->where('number', 'like', "%$s%")));
        }

        $orders = $query->latest('delivery_date')->paginate(20)->withQueryString();
        $stats = [
            'draft'     => DeliveryOrder::where('tenant_id', $this->tid())->where('status', 'draft')->count(),
            'shipped'   => DeliveryOrder::where('tenant_id', $this->tid())->where('status', 'shipped')->count(),
            'delivered' => DeliveryOrder::where('tenant_id', $this->tid())->where('status', 'delivered')->count(),
        ];

        return view('delivery-orders.index', compact('orders', 'stats'));
    }

    public function create(Request $request)
    {
        $tid        = $this->tid();
        $salesOrders = SalesOrder::with(['customer', 'items.product'])
            ->where('tenant_id', $tid)
            ->whereIn('status', ['confirmed', 'processing'])
            ->orderBy('date', 'desc')
            ->get();
        $warehouses = Warehouse::where('tenant_id', $tid)->where('is_active', true)->get();

        $selectedSo = null;
        if ($request->filled('sales_order_id')) {
            $selectedSo = SalesOrder::with(['customer', 'items.product', 'deliveryOrders.items'])
                ->where('tenant_id', $tid)
                ->find($request->sales_order_id);
        }

        return view('delivery-orders.create', compact('salesOrders', 'warehouses', 'selectedSo'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sales_order_id'   => 'required|exists:sales_orders,id',
            'warehouse_id'     => 'required|exists:warehouses,id',
            'delivery_date'    => 'required|date',
            'shipping_address' => 'nullable|string|max:500',
            'courier'          => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:1000',
            'items'            => 'required|array|min:1',
            'items.*.sales_order_item_id' => 'required|exists:sales_order_items,id',
            'items.*.product_id'          => 'required|exists:products,id',
            'items.*.quantity_delivered'  => 'required|numeric|min:0.001',
        ]);

        $tid = $this->tid();
        $so  = SalesOrder::where('tenant_id', $tid)->findOrFail($data['sales_order_id']);

        DB::transaction(function () use ($data, $tid, $so) {
            $number = app(DocumentNumberService::class)->generate($tid, 'DO');

            $do = DeliveryOrder::create([
                'tenant_id'        => $tid,
                'sales_order_id'   => $so->id,
                'warehouse_id'     => $data['warehouse_id'],
                'created_by'       => auth()->id(),
                'number'           => $number,
                'delivery_date'    => $data['delivery_date'],
                'status'           => 'draft',
                'shipping_address' => $data['shipping_address'] ?? $so->shipping_address,
                'courier'          => $data['courier'] ?? null,
                'notes'            => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $soItem = SalesOrderItem::find($item['sales_order_item_id']);
                DeliveryOrderItem::create([
                    'delivery_order_id'  => $do->id,
                    'sales_order_item_id'=> $item['sales_order_item_id'],
                    'product_id'         => $item['product_id'],
                    'quantity_ordered'   => $soItem->quantity,
                    'quantity_delivered' => $item['quantity_delivered'],
                    'unit'               => $soItem->product->unit ?? 'pcs',
                ]);
            }

            ActivityLog::record('delivery_order_created', "Surat jalan {$number} dibuat dari SO {$so->number}", $do);
        });

        return redirect()->route('delivery-orders.index')->with('success', 'Surat jalan berhasil dibuat.');
    }

    public function ship(DeliveryOrder $deliveryOrder)
    {
        abort_if($deliveryOrder->tenant_id !== $this->tid(), 403);
        abort_if($deliveryOrder->status !== 'draft', 422, 'Hanya surat jalan draft yang bisa dikirim.');

        DB::transaction(function () use ($deliveryOrder) {
            $tid = $this->tid();

            // Kurangi stok
            foreach ($deliveryOrder->items as $item) {
                $stock = ProductStock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $deliveryOrder->warehouse_id],
                    ['quantity' => 0]
                );
                $before = $stock->quantity;
                $stock->decrement('quantity', $item->quantity_delivered);

                StockMovement::create([
                    'tenant_id'       => $tid,
                    'product_id'      => $item->product_id,
                    'warehouse_id'    => $deliveryOrder->warehouse_id,
                    'user_id'         => auth()->id(),
                    'type'            => 'out',
                    'quantity'        => $item->quantity_delivered,
                    'quantity_before' => $before,
                    'quantity_after'  => $before - $item->quantity_delivered,
                    'reference'       => $deliveryOrder->number,
                    'notes'           => "Pengiriman {$deliveryOrder->number}",
                ]);
            }

            $deliveryOrder->update(['status' => 'shipped']);
            $deliveryOrder->salesOrder->update(['status' => 'shipped']);

            ActivityLog::record('delivery_order_shipped', "Surat jalan {$deliveryOrder->number} dikirim", $deliveryOrder);
        });

        return back()->with('success', "Surat jalan {$deliveryOrder->number} berhasil dikirim.");
    }

    public function deliver(DeliveryOrder $deliveryOrder)
    {
        abort_if($deliveryOrder->tenant_id !== $this->tid(), 403);
        abort_if($deliveryOrder->status !== 'shipped', 422, 'Hanya surat jalan yang sudah dikirim yang bisa dikonfirmasi terkirim.');

        $deliveryOrder->update(['status' => 'delivered']);
        ActivityLog::record('delivery_order_delivered', "Surat jalan {$deliveryOrder->number} terkirim", $deliveryOrder);

        return back()->with('success', "Surat jalan {$deliveryOrder->number} dikonfirmasi terkirim.");
    }

    /** Buat invoice dari delivery order (partial delivery support) */
    public function createInvoice(DeliveryOrder $deliveryOrder)
    {
        abort_if($deliveryOrder->tenant_id !== $this->tid(), 403);
        abort_if($deliveryOrder->status !== 'delivered', 422, 'Surat jalan harus berstatus terkirim untuk membuat invoice.');

        $tid = $this->tid();
        $so  = $deliveryOrder->salesOrder;

        DB::transaction(function () use ($deliveryOrder, $so, $tid) {
            // Hitung total dari items yang dikirim
            $subtotal = 0;
            foreach ($deliveryOrder->items as $doItem) {
                $soItem    = $doItem->salesOrderItem;
                $subtotal += $doItem->quantity_delivered * $soItem->price;
            }

            $taxRate   = $so->tax_rate_id ? \App\Models\TaxRate::find($so->tax_rate_id) : null;
            $taxAmount = $taxRate ? round($subtotal * ($taxRate->rate / 100), 2) : 0;
            $total     = $subtotal + $taxAmount;

            $number = app(DocumentNumberService::class)->generate($tid, 'INV');

            $invoice = Invoice::create([
                'tenant_id'        => $tid,
                'number'           => $number,
                'customer_id'      => $so->customer_id,
                'sales_order_id'   => $so->id,
                'subtotal_amount'  => $subtotal,
                'tax_rate_id'      => $so->tax_rate_id,
                'tax_amount'       => $taxAmount,
                'total_amount'     => $total,
                'paid_amount'      => 0,
                'remaining_amount' => $total,
                'status'           => 'unpaid',
                'due_date'         => $so->due_date ?? today()->addDays(30),
                'currency_code'    => $so->currency_code ?? 'IDR',
                'currency_rate'    => $so->currency_rate ?? 1,
                'notes'            => "Invoice dari surat jalan {$deliveryOrder->number}",
            ]);

            ActivityLog::record('invoice_from_do', "Invoice {$number} dibuat dari surat jalan {$deliveryOrder->number}", $invoice);
        });

        return back()->with('success', 'Invoice berhasil dibuat dari surat jalan.');
    }

    /** AJAX: ambil items dari SO */
    public function soItems(SalesOrder $salesOrder)
    {
        abort_if($salesOrder->tenant_id !== $this->tid(), 403);
        $salesOrder->load(['items.product', 'deliveryOrders.items']);

        // Hitung qty yang sudah dikirim per item
        $deliveredQty = [];
        foreach ($salesOrder->deliveryOrders->whereNotIn('status', ['cancelled']) as $do) {
            foreach ($do->items as $doi) {
                $deliveredQty[$doi->sales_order_item_id] = ($deliveredQty[$doi->sales_order_item_id] ?? 0) + $doi->quantity_delivered;
            }
        }

        $items = $salesOrder->items->map(fn($i) => [
            'id'           => $i->id,
            'product_id'   => $i->product_id,
            'product_name' => $i->product->name,
            'quantity'     => $i->quantity,
            'delivered'    => $deliveredQty[$i->id] ?? 0,
            'remaining'    => $i->quantity - ($deliveredQty[$i->id] ?? 0),
            'unit'         => $i->product->unit ?? 'pcs',
        ])->filter(fn($i) => $i['remaining'] > 0)->values();

        return response()->json($items);
    }
}
