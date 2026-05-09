<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\CustomerBalance;
use App\Models\Invoice;
use App\Models\ProductStock;
use App\Models\SalesReturn;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\DocumentNumberService;
use App\Services\GlPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $query = SalesReturn::with(['customer', 'invoice'])
            ->where('tenant_id', $this->tid());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('number', 'like', "%$s%")
                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%$s%")));
        }

        $returns = $query->latest()->paginate(20)->withQueryString();
        $stats = [
            'draft' => SalesReturn::where('tenant_id', $this->tid())->where('status', 'draft')->count(),
            'approved' => SalesReturn::where('tenant_id', $this->tid())->where('status', 'approved')->count(),
            'completed' => SalesReturn::where('tenant_id', $this->tid())->where('status', 'completed')->count(),
        ];

        return view('sales-returns.index', compact('returns', 'stats'));
    }

    public function create(Request $request)
    {
        $tid = $this->tid();
        $customers = Customer::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();
        $invoices = Invoice::with('customer')
            ->where('tenant_id', $tid)
            ->whereIn('status', ['paid', 'partial'])
            ->orderBy('number')
            ->get();
        $warehouses = Warehouse::where('tenant_id', $tid)->where('is_active', true)->get();

        // Jika ada invoice_id di query string, pre-load items
        $selectedInvoice = null;
        if ($request->filled('invoice_id')) {
            $selectedInvoice = Invoice::with(['salesOrder.items.product'])
                ->where('tenant_id', $tid)
                ->find($request->invoice_id);
        }

        return view('sales-returns.create', compact('customers', 'invoices', 'warehouses', 'selectedInvoice'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'return_date' => 'required|date',
            'reason' => 'required|string|max:500',
            'refund_method' => 'required|in:credit_note,cash,bank_transfer,customer_balance',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string|max:255',
        ]);

        $tid = $this->tid();

        // Validasi invoice milik tenant
        if (! empty($data['invoice_id'])) {
            $invoice = Invoice::where('tenant_id', $tid)->findOrFail($data['invoice_id']);
        }

        DB::transaction(function () use ($data, $tid) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                $total = $item['quantity'] * $item['price'];
                $subtotal += $total;
                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $total,
                    'reason' => $item['reason'] ?? null,
                ];
            }

            $number = app(DocumentNumberService::class)->generate($tid, 'SR');

            $return = SalesReturn::create([
                'tenant_id' => $tid,
                'invoice_id' => $data['invoice_id'] ?? null,
                'sales_order_id' => $data['sales_order_id'] ?? null,
                'customer_id' => $data['customer_id'],
                'warehouse_id' => $data['warehouse_id'],
                'created_by' => auth()->id(),
                'number' => $number,
                'return_date' => $data['return_date'],
                'reason' => $data['reason'],
                'status' => 'draft',
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'total' => $subtotal,
                'refund_method' => $data['refund_method'],
                'refund_amount' => $subtotal,
                'is_cross_period' => false,
                'notes' => $data['notes'] ?? null,
            ]);

            $return->items()->createMany($itemsData);

            ActivityLog::record('sales_return_created', "Retur penjualan dibuat: {$number}", $return);
        });

        return redirect()->route('sales-returns.index')->with('success', 'Retur penjualan berhasil dibuat.');
    }

    public function approve(SalesReturn $salesReturn)
    {
        abort_if($salesReturn->tenant_id !== $this->tid(), 403);
        abort_if($salesReturn->status !== 'draft', 422, 'Hanya retur berstatus draft yang bisa disetujui.');

        $salesReturn->update(['status' => 'approved']);
        ActivityLog::record('sales_return_approved', "Retur {$salesReturn->number} disetujui", $salesReturn);

        return back()->with('success', "Retur {$salesReturn->number} berhasil disetujui.");
    }

    public function complete(SalesReturn $salesReturn)
    {
        abort_if($salesReturn->tenant_id !== $this->tid(), 403);
        abort_if($salesReturn->status !== 'approved', 422, 'Hanya retur berstatus disetujui yang bisa diselesaikan.');

        DB::transaction(function () use ($salesReturn) {
            $tid = $this->tid();

            // Kembalikan stok ke gudang
            foreach ($salesReturn->items as $item) {
                $stock = ProductStock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $salesReturn->warehouse_id],
                    ['quantity' => 0]
                );
                $before = $stock->quantity;
                $stock->increment('quantity', $item->quantity);

                StockMovement::create([
                    'tenant_id' => $tid,
                    'product_id' => $item->product_id,
                    'warehouse_id' => $salesReturn->warehouse_id,
                    'user_id' => auth()->id(),
                    'type' => 'in',
                    'quantity' => $item->quantity,
                    'quantity_before' => $before,
                    'quantity_after' => $before + $item->quantity,
                    'reference' => $salesReturn->number,
                    'notes' => "Retur penjualan {$salesReturn->number}",
                ]);
            }

            // GL Posting
            $glResult = app(GlPostingService::class)->postSalesReturn(
                tenantId: $tid,
                userId: auth()->id(),
                returnNumber: $salesReturn->number,
                returnId: $salesReturn->id,
                subtotal: (float) $salesReturn->subtotal,
                taxAmount: (float) $salesReturn->tax_amount,
                total: (float) $salesReturn->total,
                date: $salesReturn->return_date->toDateString(),
            );
            if ($glResult->isFailed()) {
                session()->flash('warning', $glResult->warningMessage());
            }

            // Jika refund ke customer balance
            if ($salesReturn->refund_method === 'customer_balance') {
                $balance = CustomerBalance::firstOrCreate(
                    ['tenant_id' => $tid, 'customer_id' => $salesReturn->customer_id],
                    ['balance' => 0]
                );
                $balance->credit($salesReturn->total, 'sales_return', $salesReturn->number, $salesReturn->id);
            }

            $salesReturn->update(['status' => 'completed']);
            ActivityLog::record('sales_return_completed', "Retur {$salesReturn->number} selesai, stok dikembalikan", $salesReturn);
        });

        return back()->with('success', "Retur {$salesReturn->number} selesai. Stok telah dikembalikan.");
    }

    public function cancel(SalesReturn $salesReturn)
    {
        abort_if($salesReturn->tenant_id !== $this->tid(), 403);
        abort_if($salesReturn->status === 'completed', 422, 'Retur yang sudah selesai tidak bisa dibatalkan.');

        $salesReturn->update(['status' => 'cancelled']);
        ActivityLog::record('sales_return_cancelled', "Retur {$salesReturn->number} dibatalkan", $salesReturn);

        return back()->with('success', "Retur {$salesReturn->number} dibatalkan.");
    }

    /** AJAX: ambil items dari invoice */
    public function invoiceItems(Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== $this->tid(), 403);
        $invoice->load('salesOrder.items.product');
        $items = $invoice->salesOrder?->items->map(fn ($i) => [
            'product_id' => $i->product_id,
            'product_name' => $i->product->name,
            'quantity' => $i->quantity,
            'price' => $i->price,
            'unit' => $i->product->unit ?? 'pcs',
        ]) ?? collect();

        return response()->json($items);
    }
}
