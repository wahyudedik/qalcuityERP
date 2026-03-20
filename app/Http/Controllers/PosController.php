<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
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
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|integer',
            'items.*.qty'    => 'required|integer|min:1',
            'items.*.price'  => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'paid_amount'    => 'required|numeric|min:0',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $total    = collect($request->items)->sum(fn($i) => $i['qty'] * $i['price']);

        // Map payment_method to valid payment_type enum values
        $paymentType = in_array($request->payment_method, ['cash', 'credit', 'transfer', 'qris'])
            ? $request->payment_method
            : 'cash';

        try {
            $order = DB::transaction(function () use ($request, $tenantId, $total, $paymentType) {
                $order = SalesOrder::create([
                    'tenant_id'      => $tenantId,
                    'customer_id'    => $request->customer_id ?: null,
                    'user_id'        => auth()->id(),
                    'number'         => 'POS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                    'date'           => now(),
                    'status'         => 'completed',
                    'payment_type'   => $paymentType,
                    'payment_method' => $request->payment_method,
                    'source'         => 'pos',
                    'subtotal'       => $total,
                    'discount'       => $request->discount ?? 0,
                    'tax'            => $request->tax ?? 0,
                    'total'          => $total - ($request->discount ?? 0) + ($request->tax ?? 0),
                    'notes'          => 'POS Transaction',
                ]);

                foreach ($request->items as $item) {
                    SalesOrderItem::create([
                        'sales_order_id' => $order->id,
                        'product_id'     => $item['id'],
                        'quantity'       => $item['qty'],
                        'price'          => $item['price'],
                        'discount'       => 0,
                        'total'          => $item['qty'] * $item['price'],
                    ]);

                    // Deduct stock from product_stocks (first available warehouse)
                    $stock = ProductStock::where('product_id', $item['id'])->first();
                    if ($stock) {
                        $stock->decrement('quantity', $item['qty']);
                    }
                }

                return $order;
            });
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }

        ActivityLog::record('pos_checkout', "POS checkout #{$order->number}", $order);

        return response()->json([
            'status'       => 'success',
            'order_number' => $order->number,
            'total'        => $order->total,
            'change'       => $request->paid_amount - $order->total,
        ]);
    }

    public function findByBarcode(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $product  = Product::where('tenant_id', $tenantId)
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
