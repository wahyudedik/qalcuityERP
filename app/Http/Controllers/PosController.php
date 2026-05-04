<?php

namespace App\Http\Controllers;

use App\Mail\PosReceiptMail;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\ActivityLog;
use App\Models\StockMovement;
use App\Models\CashierSession;
use App\Services\LoyaltyService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PosController extends Controller
{
    use \App\Traits\DispatchesWebhooks;

    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $userId   = auth()->id();

        // Cek sesi kasir yang sedang aktif
        $activeSession = CashierSession::where('user_id', $userId)
            ->where('status', CashierSession::STATUS_OPEN)
            ->first();

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

        return view('pos.index', compact('products', 'customers', 'activeSession'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items'                   => 'required|array|min:1',
            'items.*.id'              => 'required|integer',
            'items.*.qty'             => 'required|integer|min:1',
            'items.*.price'           => 'required|numeric|min:0',
            'payment_method'          => 'required|string|in:cash,card,credit,qris,transfer,bank_transfer,split',
            'paid_amount'             => 'required|numeric|min:0',
            'split_payments'          => 'nullable|array',
            'split_payments.*.method' => 'required_with:split_payments|string|in:cash,card,credit,qris,transfer,bank_transfer',
            'split_payments.*.amount' => 'required_with:split_payments|numeric|min:0',
            'loyalty_points_redeemed' => 'nullable|integer|min:0',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $subtotal = collect($request->items)->sum(fn($i) => $i['qty'] * $i['price']);
        $discount = $request->discount ?? 0;
        $tax      = $request->tax ?? 0;

        // Loyalty: hitung diskon dari penukaran poin
        $loyaltyPointsRedeemed = (int) ($request->loyalty_points_redeemed ?? 0);
        $loyaltyDiscount = 0.0;

        if ($loyaltyPointsRedeemed > 0 && $request->customer_id) {
            $loyaltyService = app(LoyaltyService::class);
            $subtotalForLoyalty = max(0, $subtotal - $discount + $tax);
            $validation = $loyaltyService->validateRedeem(
                $tenantId,
                (int) $request->customer_id,
                $loyaltyPointsRedeemed,
                $subtotalForLoyalty
            );
            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validation['message'],
                ], 422);
            }
            $loyaltyDiscount = $validation['discount'];
        }

        $total = max(0, $subtotal - $discount + $tax - $loyaltyDiscount);

        // Map payment_method to valid payment_type enum values
        $validPaymentTypes = ['cash', 'credit', 'transfer', 'qris', 'card', 'bank_transfer', 'split'];
        $paymentType = in_array($request->payment_method, $validPaymentTypes)
            ? $request->payment_method
            : 'cash';

        // Validate split payment totals
        if ($paymentType === 'split') {
            if (empty($request->split_payments)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data split payment tidak boleh kosong.',
                ], 422);
            }
            $splitTotal = collect($request->split_payments)->sum('amount');
            if (abs($splitTotal - $total) > 1) { // allow 1 rupiah rounding tolerance
                return response()->json([
                    'success' => false,
                    'message' => 'Total split payment (Rp ' . number_format($splitTotal, 0, ',', '.') . ') tidak sama dengan total transaksi (Rp ' . number_format($total, 0, ',', '.') . ').',
                ], 422);
            }
        }

        // Validate cash: paid must be >= total
        if ($paymentType === 'cash' && $request->paid_amount < $total) {
            return response()->json([
                'success' => false,
                'message' => 'Uang yang diterima (Rp ' . number_format($request->paid_amount, 0, ',', '.') . ') kurang dari total (Rp ' . number_format($total, 0, ',', '.') . ').',
            ], 422);
        }

        // BUG-SALES-004 FIX: Check credit limit if payment type is credit/transfer
        if (in_array($paymentType, ['credit', 'transfer']) && $request->customer_id) {
            $customer = \App\Models\Customer::find($request->customer_id);
            if ($customer && $customer->wouldExceedCreditLimit($total)) {
                $available = number_format($customer->availableCredit(), 0, ',', '.');
                return response()->json([
                    'success' => false,
                    'message' => "Batas kredit pelanggan terlampaui. Kredit tersedia: Rp {$available}.",
                    'error_code' => 'CREDIT_LIMIT_EXCEEDED',
                ], 422);
            }
        }

        try {
            $order = DB::transaction(function () use ($request, $tenantId, $total, $subtotal, $discount, $tax, $paymentType, $loyaltyPointsRedeemed, $loyaltyDiscount) {
                // Cari sesi kasir aktif
                $activeSession = CashierSession::where('user_id', auth()->id())
                    ->where('status', CashierSession::STATUS_OPEN)
                    ->first();

                // Calculate paid_amount and change_amount
                $paidAmount   = (float) $request->paid_amount;
                $changeAmount = $paymentType === 'cash' ? max(0, $paidAmount - $total) : 0;

                $order = SalesOrder::create([
                    'tenant_id'          => $tenantId,
                    'customer_id'        => $request->customer_id ?: null,
                    'user_id'            => auth()->id(),
                    'cashier_session_id' => $activeSession?->id,
                    'number'             => 'POS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                    'date'               => now(),
                    'status'             => 'completed',
                    'payment_type'       => $paymentType,
                    'payment_method'     => $request->payment_method,
                    'source'             => 'pos',
                    'subtotal'           => $subtotal,
                    'discount'           => $discount + $loyaltyDiscount,
                    'tax'                => $tax,
                    'total'              => $total,
                    'paid_amount'        => $paidAmount,
                    'change_amount'      => $changeAmount,
                    'split_payments'     => $paymentType === 'split' ? $request->split_payments : null,
                    'completed_at'       => now(),
                    'notes'              => 'POS Transaction',
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

                    // BUG-SALES-002 FIX: Atomic stock deduction with pessimistic locking
                    // Lock ALL stock rows for this product to prevent race conditions
                    $stocks = ProductStock::where('product_id', $item['id'])
                        ->where('quantity', '>', 0)
                        ->orderBy('quantity', 'desc') // Deduct from largest stock first
                        ->lockForUpdate()
                        ->get();

                    if ($stocks->isEmpty()) {
                        throw new \Exception("Stok produk tidak tersedia.");
                    }

                    $totalAvailable = $stocks->sum('quantity');
                    if ($totalAvailable < $item['qty']) {
                        throw new \Exception("Stok produk tidak mencukupi (tersisa {$totalAvailable}).");
                    }

                    // Deduct stock across warehouses atomically
                    $remainingToDeduct = $item['qty'];
                    foreach ($stocks as $stock) {
                        if ($remainingToDeduct <= 0)
                            break;

                        $deductFromThis = min($remainingToDeduct, $stock->quantity);
                        $before = $stock->quantity;

                        // BUG-SALES-002 FIX: Atomic update with condition to prevent negative stock
                        $updated = ProductStock::where('id', $stock->id)
                            ->where('quantity', '>=', $deductFromThis)
                            ->decrement('quantity', $deductFromThis);

                        if (!$updated) {
                            throw new \Exception("Gagal mengurangi stok untuk produk. Silakan coba lagi.");
                        }

                        StockMovement::create([
                            'tenant_id' => $tenantId,
                            'product_id' => $item['id'],
                            'warehouse_id' => $stock->warehouse_id,
                            'user_id' => auth()->id(),
                            'type' => 'out',
                            'quantity' => $deductFromThis,
                            'quantity_before' => $before,
                            'quantity_after' => $before - $deductFromThis,
                            'reference' => $order->number,
                            'notes' => 'POS Checkout',
                        ]);

                        $remainingToDeduct -= $deductFromThis;
                    }
                }

                return $order;
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('POS Checkout Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }

        ActivityLog::record('pos_checkout', "POS checkout #{$order->number}", $order);

        $this->fireWebhook('order.created', $order->load('items')->toArray());

        // Loyalty: proses penukaran dan pemberian poin (di luar transaksi DB utama)
        $loyaltyService = app(LoyaltyService::class);
        $earnedPoints = 0;

        if ($request->customer_id) {
            $customerId = (int) $request->customer_id;

            // Catat penukaran poin jika ada
            if ($loyaltyPointsRedeemed > 0 && $loyaltyDiscount > 0) {
                try {
                    $loyaltyService->redeemPoints(
                        $tenantId,
                        $customerId,
                        $loyaltyPointsRedeemed,
                        $subtotal,
                        $order->number
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("LoyaltyService redeem failed for order {$order->number}: " . $e->getMessage());
                }
            }

            // Berikan poin dari transaksi (berdasarkan subtotal sebelum diskon loyalty)
            try {
                $txn = $loyaltyService->awardPoints($tenantId, $customerId, $subtotal, $order->number);
                if ($txn) {
                    $earnedPoints = $txn->points;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("LoyaltyService award failed for order {$order->number}: " . $e->getMessage());
            }
        }

        return response()->json([
            'status'          => 'success',
            'order_id'        => $order->id,
            'order_number'    => $order->number,
            'total'           => $order->total,
            'paid_amount'     => $order->paid_amount,
            'change'          => $order->change_amount,
            'payment_method'  => $order->payment_method,
            'earned_points'   => $earnedPoints,
            'loyalty_discount' => $loyaltyDiscount,
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
                // Cari sesi kasir aktif
                $activeSession = CashierSession::where('user_id', auth()->id())
                    ->where('status', CashierSession::STATUS_OPEN)
                    ->first();

                // Create order with pending payment status
                $order = SalesOrder::create([
                    'tenant_id'          => $tenantId,
                    'customer_id'        => $request->customer_id ?: null,
                    'user_id'            => auth()->id(),
                    'cashier_session_id' => $activeSession?->id,
                    'number'             => 'POS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                    'date'               => now(),
                    'status'             => 'pending_payment',
                    'payment_type'       => null, // Will be set after payment
                    'payment_method'     => null, // Will be set after payment
                    'source'             => 'pos',
                    'subtotal'           => $subtotal,
                    'discount'           => $discount,
                    'tax'                => $tax,
                    'total'              => $total,
                    'notes'              => 'POS Transaction - Awaiting Payment',
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
                    // BUG-SALES-002 FIX: Atomic stock deduction with pessimistic locking
                    // Lock ALL stock rows for this product to prevent race conditions
                    $stocks = ProductStock::where('product_id', $item->product_id)
                        ->where('quantity', '>', 0)
                        ->orderBy('quantity', 'desc')
                        ->lockForUpdate()
                        ->get();

                    if ($stocks->isEmpty()) {
                        throw new \Exception("Insufficient stock for product ID {$item->product_id}");
                    }

                    $totalAvailable = $stocks->sum('quantity');
                    if ($totalAvailable < $item->quantity) {
                        throw new \Exception("Insufficient stock for {$item->product->name} (available: {$totalAvailable})");
                    }

                    // Deduct stock across warehouses atomically
                    $remainingToDeduct = $item->quantity;
                    foreach ($stocks as $stock) {
                        if ($remainingToDeduct <= 0)
                            break;

                        $deductFromThis = min($remainingToDeduct, $stock->quantity);
                        $before = $stock->quantity;

                        // BUG-SALES-002 FIX: Atomic update with condition to prevent negative stock
                        $updated = ProductStock::where('id', $stock->id)
                            ->where('quantity', '>=', $deductFromThis)
                            ->decrement('quantity', $deductFromThis);

                        if (!$updated) {
                            throw new \Exception("Failed to deduct stock for {$item->product->name}. Please try again.");
                        }

                        StockMovement::create([
                            'tenant_id' => $order->tenant_id,
                            'product_id' => $item->product_id,
                            'warehouse_id' => $stock->warehouse_id,
                            'user_id' => auth()->id(),
                            'type' => 'out',
                            'quantity' => $deductFromThis,
                            'quantity_before' => $before,
                            'quantity_after' => $before - $deductFromThis,
                            'reference' => $order->number,
                            'notes' => 'POS Payment Completed',
                        ]);

                        $remainingToDeduct -= $deductFromThis;
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

    /**
     * Kirim struk via email
     */
    public function sendReceiptEmail(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'email'    => 'required|email|max:255',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $order = SalesOrder::where('tenant_id', $tenantId)
            ->with(['items.product', 'customer', 'user'])
            ->findOrFail($request->order_id);

        try {
            Mail::to($request->email)->queue(new PosReceiptMail($order));

            ActivityLog::record('pos_receipt_email', "Struk #{$order->number} dikirim ke {$request->email}", $order);

            return response()->json([
                'success' => true,
                'message' => "Struk berhasil dikirim ke {$request->email}",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Kirim struk via WhatsApp
     */
    public function sendReceiptWhatsApp(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'phone'    => 'required|string|max:20',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $order = SalesOrder::where('tenant_id', $tenantId)
            ->with(['items.product', 'customer', 'user'])
            ->findOrFail($request->order_id);

        try {
            $wa = new WhatsAppService($tenantId);

            if (!$wa->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'WhatsApp belum dikonfigurasi. Silakan setup di Pengaturan → WhatsApp.',
                ], 422);
            }

            $message = $this->buildWhatsAppReceiptMessage($order);
            $result  = $wa->sendMessage($request->phone, $message);

            if ($result['status'] === 'success') {
                ActivityLog::record('pos_receipt_whatsapp', "Struk #{$order->number} dikirim ke WA {$request->phone}", $order);

                return response()->json([
                    'success' => true,
                    'message' => "Struk berhasil dikirim ke WhatsApp {$request->phone}",
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengirim WhatsApp',
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim WhatsApp: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build pesan WhatsApp untuk struk
     */
    private function buildWhatsAppReceiptMessage(SalesOrder $order): string
    {
        $storeName = $order->tenant?->name ?? config('app.name', 'Toko');
        $lines     = [];

        $lines[] = "🧾 *STRUK PEMBELIAN*";
        $lines[] = "*{$storeName}*";
        $lines[] = str_repeat('─', 28);
        $lines[] = "No. Transaksi: *#{$order->number}*";
        $lines[] = "Tanggal: " . ($order->date ? \Carbon\Carbon::parse($order->date)->format('d/m/Y H:i') : now()->format('d/m/Y H:i'));
        if ($order->customer) {
            $lines[] = "Pelanggan: {$order->customer->name}";
        }
        $lines[] = str_repeat('─', 28);

        foreach ($order->items as $item) {
            $name  = $item->product?->name ?? 'Produk';
            $qty   = $item->quantity;
            $total = number_format($item->total, 0, ',', '.');
            $lines[] = "{$name} x{$qty}";
            $lines[] = "  Rp {$total}";
        }

        $lines[] = str_repeat('─', 28);

        if ($order->discount > 0) {
            $lines[] = "Diskon: -Rp " . number_format($order->discount, 0, ',', '.');
        }
        if ($order->tax > 0) {
            $lines[] = "Pajak: Rp " . number_format($order->tax, 0, ',', '.');
        }

        $lines[] = "*TOTAL: Rp " . number_format($order->total, 0, ',', '.') . "*";
        $lines[] = "Metode: " . strtoupper($order->payment_method ?? '-');

        if ($order->paid_amount) {
            $lines[] = "Dibayar: Rp " . number_format($order->paid_amount, 0, ',', '.');
        }
        if ($order->change_amount > 0) {
            $lines[] = "Kembalian: Rp " . number_format($order->change_amount, 0, ',', '.');
        }

        $lines[] = str_repeat('─', 28);
        $lines[] = "_Terima kasih atas kunjungan Anda!_ 🙏";

        return implode("\n", $lines);
    }

    /**
     * Ambil saldo poin loyalty pelanggan untuk ditampilkan di POS.
     */
    public function getLoyaltyBalance(\App\Models\Customer $customer)
    {
        $tenantId = auth()->user()->tenant_id;

        if ($customer->tenant_id !== $tenantId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $loyaltyService = app(LoyaltyService::class);
        $balance        = $loyaltyService->getBalance($tenantId, $customer->id);
        $earnPreview    = 0;

        // Hitung poin yang akan diperoleh jika ada program aktif
        $program = $loyaltyService->getActiveProgram($tenantId);
        $idrPerPoint = $program ? (float) $program->idr_per_point : 0;
        $pointsPerAmount = $program ? (float) ($program->points_per_amount ?? 1) : 1;
        $minRedeem = $program ? (int) $program->min_redeem_points : 100;

        return response()->json([
            'balance'          => $balance,
            'idr_per_point'    => $idrPerPoint,
            'min_redeem'       => $minRedeem,
            'points_per_amount' => $pointsPerAmount,
            'program_active'   => $program !== null,
        ]);
    }

    public function findByBarcode(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $barcode  = trim($request->barcode ?? '');

        if (!$barcode) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $cacheKey = "pos_product_{$tenantId}_{$barcode}";

        $product = Cache::remember($cacheKey, 60, function () use ($tenantId, $barcode) {
            return Product::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->where(function ($q) use ($barcode) {
                    $q->where('barcode', $barcode)
                        ->orWhere('sku', $barcode);
                })
                ->select('id', 'name', 'sku', 'barcode', 'price_sell', 'stock_min', 'category', 'image', 'unit')
                ->withSum('productStocks', 'quantity')
                ->first();
        });

        if (!$product) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $product->total_stock = (int) ($product->product_stocks_sum_quantity ?? 0);

        return response()->json([
            'id'        => $product->id,
            'name'      => $product->name,
            'sku'       => $product->sku,
            'barcode'   => $product->barcode,
            'price'     => (float) $product->price_sell,
            'stock'     => $product->total_stock,
            'unit'      => $product->unit,
            'image_url' => $product->image,
        ]);
    }

    /**
     * Pencarian produk cepat untuk POS — mendukung barcode, SKU, dan teks.
     * Hasil muncul dalam < 500ms dengan dukungan cache.
     */
    public function searchProducts(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $query    = trim($request->get('q', ''));

        if ($query === '') {
            return response()->json([]);
        }

        $cacheKey = "pos_product_{$tenantId}_" . md5($query);

        $products = Cache::remember($cacheKey, 60, function () use ($tenantId, $query) {
            return Product::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('barcode', $query)          // exact barcode match (fast, indexed)
                        ->orWhere('sku', $query)             // exact SKU match (fast, indexed)
                        ->orWhere('name', 'like', "%{$query}%")    // text search on name
                        ->orWhere('sku', 'like', "%{$query}%")     // partial SKU
                        ->orWhere('barcode', 'like', "%{$query}%"); // partial barcode
                })
                ->select('id', 'name', 'sku', 'barcode', 'price_sell', 'stock_min', 'category', 'image', 'unit')
                ->withSum('productStocks', 'quantity')
                ->orderByRaw("CASE WHEN barcode = ? OR sku = ? THEN 0 ELSE 1 END", [$query, $query]) // exact matches first
                ->limit(20)
                ->get()
                ->map(function ($p) {
                    return [
                        'id'        => $p->id,
                        'name'      => $p->name,
                        'sku'       => $p->sku,
                        'barcode'   => $p->barcode,
                        'price'     => (float) $p->price_sell,
                        'stock'     => (int) ($p->product_stocks_sum_quantity ?? 0),
                        'unit'      => $p->unit,
                        'image_url' => $p->image,
                    ];
                });
        });

        return response()->json($products);
    }

    /**
     * Sinkronisasi transaksi offline POS.
     * Menerima array transaksi yang dibuat saat offline dan memprosesnya satu per satu.
     * Idempoten: transaksi yang sudah ada (berdasarkan offline_id) dilewati.
     */
    public function syncOffline(Request $request)
    {
        $request->validate([
            'transactions'              => 'required|array|min:1|max:100',
            'transactions.*.offline_id' => 'required|string|max:100',
            'transactions.*.items'      => 'required|array|min:1',
            'transactions.*.items.*.id'    => 'required|integer',
            'transactions.*.items.*.qty'   => 'required|integer|min:1',
            'transactions.*.items.*.price' => 'required|numeric|min:0',
            'transactions.*.payment_method' => 'required|string|in:cash,card,credit,qris,transfer,bank_transfer,split',
            'transactions.*.paid_amount'    => 'required|numeric|min:0',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $results  = [];

        foreach ($request->transactions as $txData) {
            $offlineId = $txData['offline_id'];

            // Idempoten: cek apakah sudah pernah diproses
            $existing = SalesOrder::where('tenant_id', $tenantId)
                ->where('notes', 'like', "%offline_id:{$offlineId}%")
                ->first();

            if ($existing) {
                $results[] = [
                    'offline_id'   => $offlineId,
                    'success'      => true,
                    'skipped'      => true,
                    'order_number' => $existing->number,
                    'message'      => 'Transaksi sudah pernah disinkronisasi.',
                ];
                continue;
            }

            try {
                $subtotal = collect($txData['items'])->sum(fn($i) => $i['qty'] * $i['price']);
                $discount = $txData['discount'] ?? 0;
                $tax      = $txData['tax'] ?? 0;
                $total    = max(0, $subtotal - $discount + $tax);

                $validPaymentTypes = ['cash', 'credit', 'transfer', 'qris', 'card', 'bank_transfer', 'split'];
                $paymentType = in_array($txData['payment_method'], $validPaymentTypes)
                    ? $txData['payment_method']
                    : 'cash';

                $order = DB::transaction(function () use ($txData, $tenantId, $total, $subtotal, $discount, $tax, $paymentType, $offlineId) {
                    $activeSession = CashierSession::where('user_id', auth()->id())
                        ->where('status', CashierSession::STATUS_OPEN)
                        ->first();

                    $paidAmount   = (float) $txData['paid_amount'];
                    $changeAmount = $paymentType === 'cash' ? max(0, $paidAmount - $total) : 0;

                    $order = SalesOrder::create([
                        'tenant_id'          => $tenantId,
                        'customer_id'        => $txData['customer_id'] ?? null,
                        'user_id'            => auth()->id(),
                        'cashier_session_id' => $activeSession?->id,
                        'number'             => 'POS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                        'date'               => $txData['created_at'] ?? now(),
                        'status'             => 'completed',
                        'payment_type'       => $paymentType,
                        'payment_method'     => $txData['payment_method'],
                        'source'             => 'pos',
                        'subtotal'           => $subtotal,
                        'discount'           => $discount,
                        'tax'                => $tax,
                        'total'              => $total,
                        'paid_amount'        => $paidAmount,
                        'change_amount'      => $changeAmount,
                        'split_payments'     => $paymentType === 'split' ? ($txData['split_payments'] ?? null) : null,
                        'completed_at'       => $txData['created_at'] ?? now(),
                        'notes'              => "POS Offline Sync | offline_id:{$offlineId}",
                    ]);

                    foreach ($txData['items'] as $item) {
                        SalesOrderItem::create([
                            'sales_order_id' => $order->id,
                            'product_id'     => $item['id'],
                            'quantity'       => $item['qty'],
                            'price'          => $item['price'],
                            'discount'       => 0,
                            'total'          => $item['qty'] * $item['price'],
                        ]);

                        $stocks = ProductStock::where('product_id', $item['id'])
                            ->where('quantity', '>', 0)
                            ->orderBy('quantity', 'desc')
                            ->lockForUpdate()
                            ->get();

                        if ($stocks->isEmpty()) {
                            throw new \Exception("Stok produk ID {$item['id']} tidak tersedia.");
                        }

                        $totalAvailable = $stocks->sum('quantity');
                        if ($totalAvailable < $item['qty']) {
                            throw new \Exception("Stok produk ID {$item['id']} tidak mencukupi (tersisa {$totalAvailable}).");
                        }

                        $remainingToDeduct = $item['qty'];
                        foreach ($stocks as $stock) {
                            if ($remainingToDeduct <= 0) break;

                            $deductFromThis = min($remainingToDeduct, $stock->quantity);
                            $before = $stock->quantity;

                            $updated = ProductStock::where('id', $stock->id)
                                ->where('quantity', '>=', $deductFromThis)
                                ->decrement('quantity', $deductFromThis);

                            if (!$updated) {
                                throw new \Exception("Gagal mengurangi stok produk ID {$item['id']}. Silakan coba lagi.");
                            }

                            StockMovement::create([
                                'tenant_id'       => $tenantId,
                                'product_id'      => $item['id'],
                                'warehouse_id'    => $stock->warehouse_id,
                                'user_id'         => auth()->id(),
                                'type'            => 'out',
                                'quantity'        => $deductFromThis,
                                'quantity_before' => $before,
                                'quantity_after'  => $before - $deductFromThis,
                                'reference'       => $order->number,
                                'notes'           => 'POS Offline Sync',
                            ]);

                            $remainingToDeduct -= $deductFromThis;
                        }
                    }

                    return $order;
                });

                ActivityLog::record('pos_offline_sync', "POS offline sync #{$order->number} (offline_id: {$offlineId})", $order);

                $results[] = [
                    'offline_id'   => $offlineId,
                    'success'      => true,
                    'skipped'      => false,
                    'order_number' => $order->number,
                    'order_id'     => $order->id,
                    'message'      => 'Transaksi berhasil disinkronisasi.',
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'offline_id' => $offlineId,
                    'success'    => false,
                    'skipped'    => false,
                    'message'    => $e->getMessage(),
                ];
            }
        }

        $successCount = collect($results)->where('success', true)->count();
        $failCount    = collect($results)->where('success', false)->count();

        return response()->json([
            'status'        => $failCount === 0 ? 'success' : ($successCount > 0 ? 'partial' : 'error'),
            'synced'        => $successCount,
            'failed'        => $failCount,
            'results'       => $results,
            'message'       => "{$successCount} transaksi berhasil disinkronisasi" . ($failCount > 0 ? ", {$failCount} gagal." : "."),
        ]);
    }
}
