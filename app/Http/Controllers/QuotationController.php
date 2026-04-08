<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuotationController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $query = Quotation::with('customer')
            ->where('tenant_id', $this->tid());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('number', 'like', "%$s%")
                ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%$s%")));
        }

        $quotations = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'draft' => Quotation::where('tenant_id', $this->tid())->where('status', 'draft')->count(),
            'sent' => Quotation::where('tenant_id', $this->tid())->where('status', 'sent')->count(),
            'accepted' => Quotation::where('tenant_id', $this->tid())->where('status', 'accepted')->count(),
            'expired' => Quotation::where('tenant_id', $this->tid())->where('status', 'expired')
                ->orWhere(fn($q) => $q->where('tenant_id', $this->tid())
                    ->where('status', 'draft')->where('valid_until', '<', today()))->count(),
        ];

        $customers = Customer::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();
        $products = Product::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();

        return view('quotations.index', compact('quotations', 'stats', 'customers', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'valid_days' => 'required|integer|min:1|max:365',
            'notes' => 'nullable|string|max:1000',
            'discount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                $total = $item['quantity'] * $item['price'];
                $subtotal += $total;
                $itemsData[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => 0,
                    'total' => $total,
                ];
            }

            $discount = $data['discount'] ?? 0;
            $grandTotal = $subtotal - $discount;

            $number = 'QT-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            $quotation = Quotation::create([
                'tenant_id' => $this->tid(),
                'customer_id' => $data['customer_id'],
                'user_id' => auth()->id(),
                'number' => $number,
                'status' => 'draft',
                'date' => today(),
                'valid_until' => today()->addDays($data['valid_days']),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => 0,
                'total' => $grandTotal,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($itemsData as $item) {
                QuotationItem::create(array_merge($item, ['quotation_id' => $quotation->id]));
            }
        });

        return back()->with('success', 'Penawaran berhasil dibuat.');
    }

    public function show(Quotation $quotation)
    {
        abort_if($quotation->tenant_id !== $this->tid(), 403);
        $quotation->load(['customer', 'items.product', 'user', 'salesOrders']);
        return view('quotations.show', compact('quotation'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        abort_if($quotation->tenant_id !== $this->tid(), 403);
        abort_if($quotation->status === 'accepted', 403, 'Penawaran yang sudah diterima tidak bisa diedit.');

        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'valid_days' => 'required|integer|min:1|max:365',
            'notes' => 'nullable|string|max:1000',
            'discount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data, $quotation) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                $total = $item['quantity'] * $item['price'];
                $subtotal += $total;
                $itemsData[] = [
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => 0,
                    'total' => $total,
                ];
            }

            $discount = $data['discount'] ?? 0;
            $grandTotal = $subtotal - $discount;

            $quotation->update([
                'customer_id' => $data['customer_id'],
                'valid_until' => $quotation->date->addDays($data['valid_days']),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $grandTotal,
                'notes' => $data['notes'] ?? null,
            ]);

            $quotation->items()->delete();
            QuotationItem::insert($itemsData);
        });

        return back()->with('success', 'Penawaran berhasil diperbarui.');
    }

    public function updateStatus(Request $request, Quotation $quotation)
    {
        abort_if($quotation->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'status' => 'required|in:draft,sent,accepted,rejected,expired',
        ]);

        $quotation->update(['status' => $data['status']]);

        return back()->with('success', 'Status penawaran diperbarui.');
    }

    public function convertToOrder(Quotation $quotation)
    {
        abort_if($quotation->tenant_id !== $this->tid(), 403);

        if ($quotation->status === 'rejected' || $quotation->status === 'expired') {
            return back()->with('error', 'Penawaran yang ditolak atau kadaluarsa tidak bisa dikonversi.');
        }

        // BUG-SALES-004 FIX: Check credit limit before converting to SO
        $customer = $quotation->customer;
        if ($customer && $customer->wouldExceedCreditLimit($quotation->total)) {
            $available = number_format($customer->availableCredit(), 0, ',', '.');
            return back()->withErrors([
                'credit_limit' => "Batas kredit pelanggan terlampaui. Kredit tersedia: Rp {$available}. " .
                    "Silakan hubungi finance untuk peningkatan limit atau minta pembayaran DP."
            ])->withInput();
        }

        DB::transaction(function () use ($quotation) {
            $quotation->load('items');

            $so = SalesOrder::create([
                'tenant_id' => $this->tid(),
                'customer_id' => $quotation->customer_id,
                'user_id' => auth()->id(),
                'quotation_id' => $quotation->id,
                'number' => 'SO-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'status' => 'confirmed',
                'date' => today(),
                'subtotal' => $quotation->subtotal,
                'discount' => $quotation->discount,
                'tax' => $quotation->tax,
                'total' => $quotation->total,
                'notes' => $quotation->notes,
                'payment_type' => 'cash',
            ]);

            foreach ($quotation->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'discount' => $item->discount,
                    'total' => $item->total,
                ]);
            }

            $quotation->update(['status' => 'accepted']);
        });

        return back()->with('success', 'Penawaran berhasil dikonversi ke Sales Order.');
    }

    public function destroy(Quotation $quotation)
    {
        abort_if($quotation->tenant_id !== $this->tid(), 403);
        abort_if($quotation->status === 'accepted', 403, 'Penawaran yang sudah diterima tidak bisa dihapus.');

        $quotation->delete();
        return back()->with('success', 'Penawaran berhasil dihapus.');
    }
}
