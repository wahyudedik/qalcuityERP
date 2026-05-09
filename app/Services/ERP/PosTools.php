<?php

namespace App\Services\ERP;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Str;

class PosTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name' => 'create_quick_sale',
                'description' => 'Catat penjualan cepat (POS) langsung dari percakapan. '
                    .'Gunakan ini untuk perintah seperti: "jual kopi 2 gelas 15000 cash", '
                    .'"catat penjualan mie ayam 3 porsi", "jual kopi 2 dan teh 1 total 25000 transfer". '
                    .'Bisa multi-item sekaligus. Stok otomatis berkurang.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'items' => [
                            'type' => 'array',
                            'description' => 'Daftar item yang dijual',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'product_name' => ['type' => 'string', 'description' => 'Nama produk'],
                                    'quantity' => ['type' => 'integer', 'description' => 'Jumlah terjual'],
                                    'price' => ['type' => 'number',  'description' => 'Harga per unit (opsional, pakai harga jual default jika kosong)'],
                                ],
                                'required' => ['product_name', 'quantity'],
                            ],
                        ],
                        'payment_method' => [
                            'type' => 'string',
                            'description' => 'Metode pembayaran: cash, transfer, qris. Default: cash',
                        ],
                        'customer_name' => [
                            'type' => 'string',
                            'description' => 'Nama pelanggan (opsional, untuk walk-in bisa dikosongkan)',
                        ],
                        'total_override' => [
                            'type' => 'number',
                            'description' => 'Total yang dibayar pelanggan jika berbeda dari kalkulasi (misal sudah termasuk diskon)',
                        ],
                        'notes' => [
                            'type' => 'string',
                            'description' => 'Catatan tambahan',
                        ],
                    ],
                    'required' => ['items'],
                ],
            ],
            [
                'name' => 'get_pos_summary',
                'description' => 'Tampilkan ringkasan penjualan POS hari ini atau periode tertentu. '
                    .'Gunakan untuk: "total penjualan hari ini", "rekap kasir hari ini", "omzet hari ini".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => [
                            'type' => 'string',
                            'description' => 'today, this_week, this_month, atau YYYY-MM-DD',
                        ],
                    ],
                    'required' => ['period'],
                ],
            ],
        ];
    }

    // ─── create_quick_sale ────────────────────────────────────────

    public function createQuickSale(array $args): array
    {
        $items = $args['items'] ?? [];
        $paymentMethod = strtolower($args['payment_method'] ?? 'cash');
        $customerName = $args['customer_name'] ?? null;
        $totalOverride = $args['total_override'] ?? null;
        $notes = $args['notes'] ?? null;

        if (empty($items)) {
            return ['status' => 'error', 'message' => 'Tidak ada item yang dijual.'];
        }

        // Resolve customer (opsional)
        $customerId = null;
        if ($customerName) {
            $customer = Customer::where('tenant_id', $this->tenantId)
                ->where('name', 'like', "%{$customerName}%")
                ->first();
            $customerId = $customer?->id;
        }

        // Resolve warehouse default (ambil yang pertama)
        $warehouse = Warehouse::where('tenant_id', $this->tenantId)->first();
        if (! $warehouse) {
            return ['status' => 'error', 'message' => 'Belum ada gudang terdaftar. Buat gudang terlebih dahulu.'];
        }

        // Resolve semua produk & hitung total
        $resolvedItems = [];
        $subtotal = 0;

        foreach ($items as $item) {
            $product = Product::where('tenant_id', $this->tenantId)
                ->where('is_active', true)
                ->where(fn ($q) => $q
                    ->where('name', 'like', "%{$item['product_name']}%")
                    ->orWhere('sku', $item['product_name'])
                    ->orWhere('barcode', $item['product_name'])
                )
                ->first();

            if (! $product) {
                return [
                    'status' => 'error',
                    'message' => "Produk \"{$item['product_name']}\" tidak ditemukan. "
                        .'Pastikan nama produk sudah terdaftar di sistem.',
                ];
            }

            // Cek stok
            $stock = ProductStock::where('product_id', $product->id)
                ->where('warehouse_id', $warehouse->id)
                ->first();

            $currentStock = $stock?->quantity ?? 0;
            $qty = (int) $item['quantity'];

            if ($currentStock < $qty) {
                return [
                    'status' => 'error',
                    'message' => "Stok {$product->name} tidak cukup. "
                        ."Tersedia: {$currentStock} {$product->unit}, diminta: {$qty} {$product->unit}.",
                ];
            }

            $price = isset($item['price']) && $item['price'] > 0
                ? (float) $item['price']
                : (float) $product->price_sell;
            $lineTotal = $price * $qty;
            $subtotal += $lineTotal;

            $resolvedItems[] = [
                'product' => $product,
                'warehouse' => $warehouse,
                'stock' => $stock,
                'qty' => $qty,
                'price' => $price,
                'total' => $lineTotal,
            ];
        }

        $finalTotal = $totalOverride ?? $subtotal;

        // Buat SalesOrder
        $order = SalesOrder::create([
            'tenant_id' => $this->tenantId,
            'customer_id' => $customerId,
            'user_id' => $this->userId,
            'number' => 'POS-'.strtoupper(Str::random(8)),
            'status' => 'delivered', // POS langsung selesai
            'source' => 'pos',
            'date' => today(),
            'subtotal' => $subtotal,
            'discount' => max(0, $subtotal - $finalTotal),
            'total' => $finalTotal,
            'payment_method' => $paymentMethod,
            'notes' => $notes,
        ]);

        // Buat items & kurangi stok
        foreach ($resolvedItems as $ri) {
            $order->items()->create([
                'product_id' => $ri['product']->id,
                'quantity' => $ri['qty'],
                'price' => $ri['price'],
                'discount' => 0,
                'total' => $ri['total'],
            ]);

            // Kurangi stok
            $before = $ri['stock']?->quantity ?? 0;
            if ($ri['stock']) {
                $ri['stock']->decrement('quantity', $ri['qty']);
            } else {
                ProductStock::create([
                    'product_id' => $ri['product']->id,
                    'warehouse_id' => $ri['warehouse']->id,
                    'quantity' => 0,
                ]);
            }

            StockMovement::create([
                'tenant_id' => $this->tenantId,
                'product_id' => $ri['product']->id,
                'warehouse_id' => $ri['warehouse']->id,
                'user_id' => $this->userId,
                'type' => 'out',
                'quantity' => $ri['qty'],
                'quantity_before' => $before,
                'quantity_after' => max(0, $before - $ri['qty']),
                'reference' => $order->number,
                'notes' => 'POS sale',
            ]);
        }

        // Susun ringkasan untuk AI
        $itemLines = array_map(fn ($ri) => sprintf(
            '%s x%d @ Rp %s = Rp %s',
            $ri['product']->name,
            $ri['qty'],
            number_format($ri['price'], 0, ',', '.'),
            number_format($ri['total'], 0, ',', '.'),
        ), $resolvedItems);

        $paymentLabel = match ($paymentMethod) {
            'transfer' => 'Transfer Bank',
            'qris' => 'QRIS',
            default => 'Cash',
        };

        return [
            'status' => 'success',
            'order_number' => $order->number,
            'message' => '✅ Penjualan berhasil dicatat!',
            'detail' => [
                'nomor' => $order->number,
                'items' => $itemLines,
                'subtotal' => 'Rp '.number_format($subtotal, 0, ',', '.'),
                'diskon' => $subtotal > $finalTotal
                    ? 'Rp '.number_format($subtotal - $finalTotal, 0, ',', '.')
                    : null,
                'total' => 'Rp '.number_format($finalTotal, 0, ',', '.'),
                'bayar' => $paymentLabel,
                'pelanggan' => $customerName ?? 'Walk-in',
            ],
        ];
    }

    // ─── get_pos_summary ─────────────────────────────────────────

    public function getPosSummary(array $args): array
    {
        $period = $args['period'] ?? 'today';

        $query = SalesOrder::where('tenant_id', $this->tenantId)
            ->where('source', 'pos')
            ->whereIn('status', ['delivered', 'completed']);

        $query = match ($period) {
            'today' => $query->whereDate('date', today()),
            'this_week' => $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]),
            'this_month' => $query->whereMonth('date', now()->month)->whereYear('date', now()->year),
            default => strlen($period) === 10
                ? $query->whereDate('date', $period)
                : $query->whereDate('date', today()),
        };

        $orders = $query->get();

        if ($orders->isEmpty()) {
            return ['status' => 'success', 'message' => "Belum ada transaksi POS untuk periode {$period}."];
        }

        $byPayment = $orders->groupBy('payment_method')->map(fn ($g) => [
            'count' => $g->count(),
            'total' => 'Rp '.number_format($g->sum('total'), 0, ',', '.'),
        ]);

        return [
            'status' => 'success',
            'data' => [
                'period' => $period,
                'total_transaksi' => $orders->count(),
                'total_omzet' => 'Rp '.number_format($orders->sum('total'), 0, ',', '.'),
                'rata_rata' => 'Rp '.number_format($orders->avg('total'), 0, ',', '.'),
                'per_metode_bayar' => $byPayment->toArray(),
            ],
        ];
    }
}
