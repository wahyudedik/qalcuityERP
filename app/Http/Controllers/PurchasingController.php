<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Warehouse;
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

        $po = PurchaseOrder::create([
            'tenant_id'    => $tid,
            'supplier_id'  => $data['supplier_id'],
            'user_id'      => auth()->id(),
            'warehouse_id' => $data['warehouse_id'],
            'number'       => 'PO-' . strtoupper(Str::random(8)),
            'status'       => 'draft',
            'date'         => $data['date'],
            'expected_date'=> $data['expected_date'] ?? null,
            'subtotal'     => $subtotal,
            'total'        => $subtotal,
            'payment_type' => $data['payment_type'],
            'notes'        => $data['notes'] ?? null,
        ]);

        $po->items()->createMany($itemsData);

        return back()->with('success', "PO {$po->number} berhasil dibuat.");
    }

    public function updateOrderStatus(Request $request, PurchaseOrder $order)
    {
        abort_unless($order->tenant_id === $this->tenantId(), 403);

        $data = $request->validate(['status' => 'required|in:draft,sent,partial,received,cancelled']);
        $order->update(['status' => $data['status']]);

        return back()->with('success', "Status PO {$order->number} diperbarui.");
    }
}
