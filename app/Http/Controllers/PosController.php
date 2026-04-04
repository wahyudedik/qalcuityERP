<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\ActivityLog;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    use \App\Traits\DispatchesWebhooks;

    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        $products = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->select('id', 'name', 'sku', 'barcode', 'price_sell', 'stock_min', 'category', 'image')
            ->withSum('productStocks', 'quantity')
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                $p->total_stock = (int) ($p->product_stocks_sum_quantity ?? 0);
                return $p;
            });

        $customers = Customer::where('tenant_id', $tenantId)
            ->select('id', 'name', 'phone')
            ->orderBy('name')
            ->get();

        return view('pos.index', compact('products', 'customers'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $total = collect($request->items)->sum(fn($i) => $i['qty'] * $i['price']);

        // Map payment_method to valid payment_type enum values
        $paymentType = in_array($request->payment_method, ['cash', 'credit', 'transfer', 'qris'])
            ? $request->payment_method
            : 'cash';

        try {
            $order = DB::transaction(function () use ($request, $tenantId, $total, $paymentType) {
                $order = SalesOrder::create([
                    'tenant_id' => $tenantId,
                    'customer_id' => $request->customer_id ?: null,
                    'user_id' => auth()->id(),
                    'number' => 'POS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                    'date' => now(),
                    'status' => 'completed',
                    'payment_type' => $paymentType,
                    'payment_method' => $request->payment_method,
                    'source' => 'pos',
                    'subtotal' => $total,
                    'discount' => $request->discount ?? 0,
                    'tax' => $request->tax ?? 0,
                    'total' => $total - ($request->discount ?? 0) + ($request->tax ?? 0),
                    'notes' => 'POS Transaction',
                ]);

                foreach ($request->items as $item) {
                    SalesOrderItem::create([
                        'sales_order_id' => $order->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['qty'],
                        'price' => $item['price'],
                        'discount' => 0,
                        'total' => $item['qty'] * $item['price'],
                    ]);

                    // Deduct stock — lock row to prevent race condition
                    $stock = ProductStock::where('product_id', $item['id'])
                        ->lockForUpdate()
                        ->first();

                    if ($stock) {
                        if ($stock->quantity < $item['qty']) {
                            throw new \Exception("Stok produk tidak mencukupi (tersisa {$stock->quantity}).");
                        }

                        $before = $stock->quantity;
                        $stock->decrement('quantity', $item['qty']);

                        StockMovement::create([
                            'tenant_id' => $tenantId,
                            'product_id' => $item['id'],
                            'warehouse_id' => $stock->warehouse_id,
                            'user_id' => auth()->id(),
                            'type' => 'out',
                            'quantity' => $item['qty'],
                            'quantity_before' => $before,
                            'quantity_after' => $before - $item['qty'],
                            'reference' => $order->number,
                            'notes' => 'POS Checkout',
                        ]);
                    }
                }

                return $order;
            });
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }

        ActivityLog::record('pos_checkout', "POS checkout #{$order->number}", $order);

        $this->fireWebhook('order.created', $order->load('items')->toArray());

        return response()->json([
            'status' => 'success',
            'order_number' => $order->number,
            'total' => $order->total,
            'change' => $request->paid_amount - $order->total,
        ]);
    }

    /**
     * Initiate payment (create order in pending state)
     */
    public function initiatePayment(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'customer_id' => 'nullable|integer',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $subtotal = collect($request->items)->sum(fn($i) => $i['qty'] * $i['price']);
        $discount = $request->discount ?? 0;
        $tax = $request->tax ?? 0;
        $total = $subtotal - $discount + $tax;

        try {
            $order = DB::transaction(function () use ($request, $tenantId, $subtotal, $discount, $tax, $total) {
                // Create order with pending payment status
                $order = SalesOrder::create([
                    'tenant_id' => $tenantId,
                    'customer_id' => $request->customer_id ?: null,
                    'user_id' => auth()->id(),
                    'number' => 'POS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                    'date' => now(),
                    'status' => 'pending_payment',
                    'payment_type' => null, // Will be set after payment
                    'payment_method' => null, // Will be set after payment
                    'source' => 'pos',
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'total' => $total,
                    'notes' => 'POS Transaction - Awaiting Payment',
                ]);

                foreach ($request->items as $item) {
                    SalesOrderItem::create([
                        'sales_order_id' => $order->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['qty'],
                        'price' => $item['price'],
                        'discount' => 0,
                        'total' => $item['qty'] * $item['price'],
                    ]);
                }

                return $order;
            });

            ActivityLog::record('pos_payment_initiated', "POS payment initiated #{$order->number}", $order);

            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'number' => $order->number,
                    'total' => $order->total,
                    'items_count' => $order->items->count(),
                ],
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete payment after successful transaction
     */
    public function completePayment(Request $request, SalesOrder $order)
    {
        // Authorization check
        if ($order->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'payment_method' => 'required|in:cash,qris,card,bank_transfer',
            'amount_paid' => 'required|numeric|min:0',
            'change' => 'nullable|numeric',
            'transaction_number' => 'nullable|string', // For QRIS
        ]);

        try {
            DB::transaction(function () use ($request, $order) {
                // Update order with payment info
                $order->update([
                    'status' => 'completed',
                    'payment_type' => $request->payment_method === 'qris' ? 'qris' : 'cash',
                    'payment_method' => $request->payment_method,
                    'paid_amount' => $request->amount_paid,
                    'change_amount' => $request->change ?? 0,
                    'payment_reference' => $request->transaction_number ?? null,
                    'completed_at' => now(),
                ]);

                // Deduct stock for all items
                foreach ($order->items as $item) {
                    $stock = ProductStock::where('product_id', $item->product_id)
                        ->lockForUpdate()
                        ->first();

                    if ($stock) {
                        if ($stock->quantity < $item->quantity) {
                            throw new \Exception("Insufficient stock for {$item->product->name}");
                        }

                        $before = $stock->quantity;
                        $stock->decrement('quantity', $item->quantity);

                        StockMovement::create([
                            'tenant_id' => $order->tenant_id,
                            'product_id' => $item->product_id,
                            'warehouse_id' => $stock->warehouse_id,
                            'user_id' => auth()->id(),
                            'type' => 'out',
                            'quantity' => $item->quantity,
                            'quantity_before' => $before,
                            'quantity_after' => $before - $item->quantity,
                            'reference' => $order->number,
                            'notes' => 'POS Payment Completed',
                        ]);
                    }
                }
            });

            ActivityLog::record('pos_payment_completed', "POS payment completed #{$order->number}", $order);

            $this->fireWebhook('order.completed', $order->load('items')->toArray());

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_number' => $order->number,
                'message' => 'Payment completed successfully',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function findByBarcode(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $product = Product::where('tenant_id', $tenantId)
            ->where('barcode', $request->barcode)
            ->withSum('productStocks', 'quantity')
            ->first();

        if (!$product) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $product->total_stock = (int) ($product->product_stocks_sum_quantity ?? 0);

        return response()->json($product);
    }
}
