<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\ErpNotification;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockMovement;
use App\Models\TaxRate;
use App\Models\Warehouse;
use App\Services\GlPostingService;
use App\Services\TaxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesOrderController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $query = SalesOrder::with(['customer', 'user'])
            ->where('tenant_id', $this->tid());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('number', 'like', "%$s%")
                ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%$s%")));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $orders = $query->latest('date')->paginate(20)->withQueryString();

        $stats = [
            'pending'   => SalesOrder::where('tenant_id', $this->tid())->where('status', 'pending')->count(),
            'confirmed' => SalesOrder::where('tenant_id', $this->tid())->where('status', 'confirmed')->count(),
            'shipped'   => SalesOrder::where('tenant_id', $this->tid())->where('status', 'shipped')->count(),
            'completed' => SalesOrder::where('tenant_id', $this->tid())->where('status', 'completed')->count(),
            'this_month'=> SalesOrder::where('tenant_id', $this->tid())
                ->whereNotIn('status', ['cancelled'])
                ->whereMonth('date', now()->month)->whereYear('date', now()->year)
                ->sum('total'),
        ];

        return view('sales.index', compact('orders', 'stats'));
    }

    public function create()
    {
        $tid        = $this->tid();
        $customers  = Customer::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();
        $products   = Product::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('tenant_id', $tid)->where('is_active', true)->get();
        $taxRates   = TaxRate::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();

        return view('sales.create', compact('customers', 'products', 'warehouses', 'taxRates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'date'           => 'required|date',
            'delivery_date'  => 'nullable|date|after_or_equal:date',
            'payment_type'   => 'required|in:cash,credit',
            'due_date'       => 'nullable|date|required_if:payment_type,credit',
            'warehouse_id'   => 'required|exists:warehouses,id',
            'tax_rate_id'    => 'nullable|exists:tax_rates,id',
            'discount'       => 'nullable|numeric|min:0',
            'shipping_address' => 'nullable|string|max:500',
            'notes'          => 'nullable|string|max:1000',
            'items'          => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.001',
            'items.*.price'      => 'required|numeric|min:0',
            'items.*.discount'   => 'nullable|numeric|min:0',
        ]);

        $tid = $this->tid();

        // Cek period lock
        app(\App\Services\PeriodLockService::class)->assertNotLocked($tid, $data['date'], 'Sales Order');

        // Cek credit limit customer
        $customer = Customer::find($data['customer_id']);
        if ($customer && $data['payment_type'] === 'credit') {
            $subtotalEstimate = collect($data['items'])->sum(fn($i) => ($i['quantity'] * $i['price']) - ($i['discount'] ?? 0));
            if ($customer->wouldExceedCreditLimit($subtotalEstimate)) {
                $available = number_format($customer->availableCredit(), 0, ',', '.');
                return back()->withErrors([
                    'customer_id' => "Batas kredit pelanggan terlampaui. Kredit tersedia: Rp {$available}."
                ])->withInput();
            }
        }

        // Cek stok tersedia
        foreach ($data['items'] as $item) {
            $stock = ProductStock::where('product_id', $item['product_id'])
                ->where('warehouse_id', $data['warehouse_id'])
                ->value('quantity') ?? 0;

            $product = Product::find($item['product_id']);
            if ($stock < $item['quantity']) {
                return back()->withErrors([
                    'items' => "Stok {$product->name} tidak cukup. Tersedia: {$stock} {$product->unit}."
                ])->withInput();
            }
        }

        DB::transaction(function () use ($data, $tid, $request) {
            $subtotal  = 0;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                $itemDiscount = $item['discount'] ?? 0;
                $total        = ($item['quantity'] * $item['price']) - $itemDiscount;
                $subtotal    += $total;
                $itemsData[]  = [
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'discount'   => $itemDiscount,
                    'total'      => $total,
                ];
            }

            $discount  = $data['discount'] ?? 0;
            $taxAmount = 0;
            if (!empty($data['tax_rate_id'])) {
                $taxAmount = (new TaxService())->calculate($subtotal - $discount, (int) $data['tax_rate_id']);
            }
            $total = $subtotal - $discount + $taxAmount;

            $so = SalesOrder::create([
                'tenant_id'       => $tid,
                'customer_id'     => $data['customer_id'],
                'user_id'         => auth()->id(),
                'number'          => 'SO-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'status'          => 'confirmed',
                'date'            => $data['date'],
                'delivery_date'   => $data['delivery_date'] ?? null,
                'subtotal'        => $subtotal,
                'discount'        => $discount,
                'tax_rate_id'     => $data['tax_rate_id'] ?? null,
                'tax_amount'      => $taxAmount,
                'tax'             => $taxAmount,
                'total'           => $total,
                'payment_type'    => $data['payment_type'],
                'due_date'        => $data['due_date'] ?? null,
                'shipping_address'=> $data['shipping_address'] ?? null,
                'notes'           => $data['notes'] ?? null,
                'source'          => 'manual',
            ]);

            $so->items()->createMany($itemsData);

            // Kurangi stok dari gudang
            foreach ($itemsData as $item) {
                $stock = ProductStock::firstOrCreate(
                    ['product_id' => $item['product_id'], 'warehouse_id' => $data['warehouse_id']],
                    ['quantity' => 0]
                );
                $before = $stock->quantity;
                $stock->decrement('quantity', $item['quantity']);

                StockMovement::create([
                    'tenant_id'       => $tid,
                    'product_id'      => $item['product_id'],
                    'warehouse_id'    => $data['warehouse_id'],
                    'user_id'         => auth()->id(),
                    'type'            => 'out',
                    'quantity'        => $item['quantity'],
                    'quantity_before' => $before,
                    'quantity_after'  => $before - $item['quantity'],
                    'reference'       => $so->number,
                    'notes'           => "Sales Order {$so->number}",
                ]);
            }

            ActivityLog::record('sales_order_created', "SO dibuat: {$so->number} (Rp " . number_format($total, 0, ',', '.') . ")", $so);

            // GL Auto-Posting
            $glResult = app(GlPostingService::class)->postSalesOrder(
                tenantId:    $tid,
                userId:      auth()->id(),
                soNumber:    $so->number,
                soId:        $so->id,
                subtotal:    $subtotal - $discount,
                taxAmount:   $taxAmount,
                total:       $total,
                paymentType: $data['payment_type'],
                date:        $data['date'],
            );

            // Store GL result for flash message after transaction commits
            $GLOBALS['_gl_result'] = $glResult;
        });

        $successMsg = 'Sales Order berhasil dibuat.';
        if (isset($GLOBALS['_gl_result']) && $GLOBALS['_gl_result']->isFailed()) {
            $warning = $GLOBALS['_gl_result']->warningMessage();
            unset($GLOBALS['_gl_result']);
            return redirect()->route('sales.index')
                ->with('success', $successMsg)
                ->with('warning', $warning);
        }
        unset($GLOBALS['_gl_result']);

        return redirect()->route('sales.index')->with('success', $successMsg);
    }

    public function show(SalesOrder $salesOrder)
    {
        abort_if($salesOrder->tenant_id !== $this->tid(), 403);
        $salesOrder->load(['customer', 'items.product', 'user', 'invoice', 'quotation']);
        return view('sales.show', compact('salesOrder'));
    }

    public function updateStatus(Request $request, SalesOrder $salesOrder)
    {
        abort_if($salesOrder->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,completed,cancelled',
        ]);

        $old = $salesOrder->status;
        $salesOrder->update(['status' => $data['status']]);

        ActivityLog::record('sales_order_status_changed',
            "Status SO {$salesOrder->number}: {$old} → {$data['status']}", $salesOrder);

        // Notifikasi jika completed
        if ($data['status'] === 'completed') {
            ErpNotification::create([
                'tenant_id' => $this->tid(),
                'user_id'   => auth()->id(),
                'type'      => 'so_completed',
                'title'     => '✅ Sales Order Selesai',
                'body'      => "SO {$salesOrder->number} telah selesai. Total: Rp " . number_format($salesOrder->total, 0, ',', '.'),
                'data'      => ['so_id' => $salesOrder->id],
            ]);
        }

        return back()->with('success', "Status SO {$salesOrder->number} diperbarui ke {$data['status']}.");
    }

    public function createInvoice(SalesOrder $salesOrder)
    {
        abort_if($salesOrder->tenant_id !== $this->tid(), 403);

        if ($salesOrder->invoice()->where('status', '!=', 'cancelled')->exists()) {
            return back()->with('error', 'Invoice untuk SO ini sudah ada.');
        }

        $number = 'INV-' . date('Ymd') . '-' . str_pad(
            Invoice::where('tenant_id', $this->tid())->whereDate('created_at', today())->count() + 1,
            3, '0', STR_PAD_LEFT
        );

        $invoice = Invoice::create([
            'tenant_id'        => $this->tid(),
            'number'           => $number,
            'customer_id'      => $salesOrder->customer_id,
            'sales_order_id'   => $salesOrder->id,
            'subtotal_amount'  => $salesOrder->subtotal,
            'tax_rate_id'      => $salesOrder->tax_rate_id,
            'tax_amount'       => $salesOrder->tax_amount,
            'total_amount'     => $salesOrder->total,
            'paid_amount'      => 0,
            'remaining_amount' => $salesOrder->total,
            'status'           => 'unpaid',
            'due_date'         => $salesOrder->due_date ?? today()->addDays(30),
            'currency_code'    => $salesOrder->currency_code ?? 'IDR',
            'currency_rate'    => $salesOrder->currency_rate ?? 1,
            'notes'            => "Invoice untuk SO {$salesOrder->number}",
        ]);

        ActivityLog::record('invoice_from_so', "Invoice {$number} dibuat dari SO {$salesOrder->number}", $invoice);

        return redirect()->route('invoices.show', $invoice)->with('success', "Invoice {$number} berhasil dibuat.");
    }

    public function destroy(SalesOrder $salesOrder)
    {
        abort_if($salesOrder->tenant_id !== $this->tid(), 403);
        abort_if(in_array($salesOrder->status, ['shipped', 'completed']), 403, 'SO yang sudah dikirim/selesai tidak bisa dihapus.');

        ActivityLog::record('sales_order_deleted', "SO dihapus: {$salesOrder->number}", $salesOrder, $salesOrder->toArray());
        $salesOrder->items()->delete();
        $salesOrder->delete();

        return redirect()->route('sales.index')->with('success', 'Sales Order berhasil dihapus.');
    }
}
