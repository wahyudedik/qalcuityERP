<?php

namespace App\Services\ERP;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Illuminate\Support\Str;

class WarehouseTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    // ─── Tool Definitions ─────────────────────────────────────────

    public static function definitions(): array
    {
        return [
            [
                'name' => 'get_warehouse_stock',
                'description' => 'Lihat stok semua produk di satu gudang tertentu, atau bandingkan stok antar gudang. '
                    .'Gunakan untuk: "stok gudang A berapa?", "isi gudang Surabaya", '
                    .'"perbandingan stok gudang Jakarta vs Surabaya", "semua gudang dan stoknya".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'warehouse_name' => ['type' => 'string', 'description' => 'Nama gudang (opsional, kosong = semua gudang)'],
                        'product_name' => ['type' => 'string', 'description' => 'Filter produk tertentu (opsional)'],
                    ],
                ],
            ],
            [
                'name' => 'transfer_stock',
                'description' => 'Transfer/pindahkan stok produk dari satu gudang ke gudang lain. '
                    .'Gunakan untuk: "transfer 100 pcs kaos dari gudang A ke B", '
                    .'"kirim 50 kg beras dari gudang Jakarta ke Surabaya", '
                    .'"pindahkan stok kopi 200 pcs ke gudang cabang".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'product_name' => ['type' => 'string',  'description' => 'Nama atau SKU produk'],
                        'from_warehouse' => ['type' => 'string',  'description' => 'Nama gudang asal'],
                        'to_warehouse' => ['type' => 'string',  'description' => 'Nama gudang tujuan'],
                        'quantity' => ['type' => 'integer', 'description' => 'Jumlah yang ditransfer'],
                        'notes' => ['type' => 'string',  'description' => 'Catatan pengiriman (opsional)'],
                        'immediate' => ['type' => 'boolean', 'description' => 'true = langsung selesai (default), false = buat transfer pending dulu'],
                    ],
                    'required' => ['product_name', 'from_warehouse', 'to_warehouse', 'quantity'],
                ],
            ],
            [
                'name' => 'get_transfer_status',
                'description' => 'Cek status transfer/pengiriman barang antar gudang. '
                    .'Gunakan untuk: "status transfer TRF-XXX", "pengiriman ke Surabaya sudah sampai?", '
                    .'"daftar transfer pending", "transfer yang belum selesai".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'transfer_number' => ['type' => 'string', 'description' => 'Nomor transfer TRF-XXXX (opsional)'],
                        'status' => ['type' => 'string', 'description' => 'Filter status: pending, in_transit, completed, cancelled (opsional)'],
                        'warehouse_name' => ['type' => 'string', 'description' => 'Filter berdasarkan gudang asal atau tujuan (opsional)'],
                    ],
                ],
            ],
            [
                'name' => 'receive_transfer',
                'description' => 'Konfirmasi penerimaan barang dari transfer yang sedang in_transit. '
                    .'Gunakan untuk: "terima transfer TRF-XXX", "barang dari gudang A sudah sampai", '
                    .'"konfirmasi penerimaan TRF-001", "barang kiriman sudah diterima".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'transfer_number' => ['type' => 'string', 'description' => 'Nomor transfer TRF-XXXX'],
                        'notes' => ['type' => 'string',  'description' => 'Catatan penerimaan (opsional)'],
                    ],
                    'required' => ['transfer_number'],
                ],
            ],
            [
                'name' => 'list_warehouses',
                'description' => 'Tampilkan semua gudang beserta total stok dan nilainya. '
                    .'Gunakan untuk: "daftar gudang", "ada berapa gudang?", '
                    .'"gudang mana saja yang aktif", "ringkasan semua gudang".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'include_stock' => ['type' => 'boolean', 'description' => 'true = sertakan ringkasan stok per gudang (default: true)'],
                    ],
                ],
            ],
            [
                'name' => 'adjust_stock',
                'description' => 'Koreksi/penyesuaian stok (stock opname). '
                    .'Gunakan untuk: "koreksi stok kopi di gudang A jadi 150", '
                    .'"stock opname: kaos L seharusnya 80 pcs", '
                    .'"sesuaikan stok beras gudang B menjadi 500 kg".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'product_name' => ['type' => 'string',  'description' => 'Nama atau SKU produk'],
                        'warehouse_name' => ['type' => 'string',  'description' => 'Nama gudang'],
                        'actual_qty' => ['type' => 'integer', 'description' => 'Jumlah stok aktual hasil opname'],
                        'notes' => ['type' => 'string',  'description' => 'Alasan penyesuaian (opsional)'],
                    ],
                    'required' => ['product_name', 'warehouse_name', 'actual_qty'],
                ],
            ],
        ];
    }

    // ─── Executors ────────────────────────────────────────────────

    public function getWarehouseStock(array $args): array
    {
        $warehouseQuery = Warehouse::where('tenant_id', $this->tenantId)->where('is_active', true);

        if (! empty($args['warehouse_name'])) {
            $warehouseQuery->where('name', 'like', "%{$args['warehouse_name']}%");
        }

        $warehouses = $warehouseQuery->with(['productStocks.product'])->get();

        if ($warehouses->isEmpty()) {
            return ['status' => 'not_found', 'message' => 'Gudang tidak ditemukan.'];
        }

        $result = [];
        foreach ($warehouses as $wh) {
            $stocks = $wh->productStocks;

            if (! empty($args['product_name'])) {
                $stocks = $stocks->filter(fn ($s) => str_contains(strtolower($s->product->name ?? ''), strtolower($args['product_name']))
                );
            }

            $items = $stocks->filter(fn ($s) => $s->product !== null && $s->quantity > 0)
                ->map(fn ($s) => [
                    'produk' => $s->product->name,
                    'sku' => $s->product->sku,
                    'stok' => $s->quantity.' '.$s->product->unit,
                    'min_stok' => $s->product->stock_min,
                    'status' => $s->quantity <= $s->product->stock_min ? '⚠️ LOW' : '✅ OK',
                    'nilai' => 'Rp '.number_format($s->quantity * ($s->product->price_buy ?? 0), 0, ',', '.'),
                ])->values()->toArray();

            $totalNilai = $stocks->filter(fn ($s) => $s->product !== null)
                ->sum(fn ($s) => $s->quantity * ($s->product->price_buy ?? 0));

            $result[] = [
                'gudang' => $wh->name,
                'kode' => $wh->code,
                'alamat' => $wh->address ?? '-',
                'total_sku' => count($items),
                'total_nilai' => 'Rp '.number_format($totalNilai, 0, ',', '.'),
                'produk' => $items,
            ];
        }

        return ['status' => 'success', 'data' => $result];
    }

    public function transferStock(array $args): array
    {
        $product = $this->findProduct($args['product_name']);
        if (! $product) {
            return ['status' => 'error', 'message' => "Produk \"{$args['product_name']}\" tidak ditemukan."];
        }

        $fromWh = $this->findWarehouse($args['from_warehouse']);
        if (! $fromWh) {
            return ['status' => 'error', 'message' => "Gudang asal \"{$args['from_warehouse']}\" tidak ditemukan."];
        }

        $toWh = $this->findWarehouse($args['to_warehouse']);
        if (! $toWh) {
            return ['status' => 'error', 'message' => "Gudang tujuan \"{$args['to_warehouse']}\" tidak ditemukan."];
        }

        if ($fromWh->id === $toWh->id) {
            return ['status' => 'error', 'message' => 'Gudang asal dan tujuan tidak boleh sama.'];
        }

        $qty = (int) $args['quantity'];

        // Cek stok di gudang asal
        $fromStock = ProductStock::firstOrCreate(
            ['product_id' => $product->id, 'warehouse_id' => $fromWh->id],
            ['quantity' => 0]
        );

        if ($fromStock->quantity < $qty) {
            return [
                'status' => 'error',
                'message' => "Stok **{$product->name}** di gudang **{$fromWh->name}** tidak cukup.\n"
                    ."Tersedia: **{$fromStock->quantity} {$product->unit}**, dibutuhkan: **{$qty} {$product->unit}**.",
            ];
        }

        $immediate = $args['immediate'] ?? true;
        $trfNumber = 'TRF-'.strtoupper(Str::random(6));

        if ($immediate) {
            // Langsung selesaikan transfer
            $toStock = ProductStock::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $toWh->id],
                ['quantity' => 0]
            );

            $fromBefore = $fromStock->quantity;
            $toBefore = $toStock->quantity;

            $fromStock->decrement('quantity', $qty);
            $toStock->increment('quantity', $qty);

            // Catat movement keluar dari gudang asal
            StockMovement::create([
                'tenant_id' => $this->tenantId,
                'product_id' => $product->id,
                'warehouse_id' => $fromWh->id,
                'to_warehouse_id' => $toWh->id,
                'user_id' => $this->userId,
                'type' => 'transfer',
                'quantity' => -$qty,
                'quantity_before' => $fromBefore,
                'quantity_after' => $fromBefore - $qty,
                'reference' => $trfNumber,
                'notes' => $args['notes'] ?? "Transfer ke {$toWh->name}",
            ]);

            // Catat movement masuk ke gudang tujuan
            StockMovement::create([
                'tenant_id' => $this->tenantId,
                'product_id' => $product->id,
                'warehouse_id' => $toWh->id,
                'to_warehouse_id' => null,
                'user_id' => $this->userId,
                'type' => 'in',
                'quantity' => $qty,
                'quantity_before' => $toBefore,
                'quantity_after' => $toBefore + $qty,
                'reference' => $trfNumber,
                'notes' => "Transfer dari {$fromWh->name}",
            ]);

            StockTransfer::create([
                'tenant_id' => $this->tenantId,
                'user_id' => $this->userId,
                'product_id' => $product->id,
                'from_warehouse_id' => $fromWh->id,
                'to_warehouse_id' => $toWh->id,
                'transfer_number' => $trfNumber,
                'quantity' => $qty,
                'status' => 'completed',
                'notes' => $args['notes'] ?? null,
                'shipped_at' => now(),
                'received_at' => now(),
            ]);

            return [
                'status' => 'success',
                'message' => "Transfer **{$qty} {$product->unit} {$product->name}** berhasil.\n"
                    ."Dari: **{$fromWh->name}** (sisa: ".($fromBefore - $qty)." {$product->unit})\n"
                    ."Ke: **{$toWh->name}** (sekarang: ".($toBefore + $qty)." {$product->unit})\n"
                    ."No. Transfer: **{$trfNumber}**",
                'data' => ['transfer_number' => $trfNumber, 'status' => 'completed'],
            ];
        }

        // Buat transfer pending (untuk pengiriman jarak jauh)
        $fromBefore = $fromStock->quantity;
        $fromStock->decrement('quantity', $qty); // Kurangi stok asal saat dikirim

        StockMovement::create([
            'tenant_id' => $this->tenantId,
            'product_id' => $product->id,
            'warehouse_id' => $fromWh->id,
            'to_warehouse_id' => $toWh->id,
            'user_id' => $this->userId,
            'type' => 'transfer',
            'quantity' => -$qty,
            'quantity_before' => $fromBefore,
            'quantity_after' => $fromBefore - $qty,
            'reference' => $trfNumber,
            'notes' => $args['notes'] ?? "Dikirim ke {$toWh->name}",
        ]);

        StockTransfer::create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'product_id' => $product->id,
            'from_warehouse_id' => $fromWh->id,
            'to_warehouse_id' => $toWh->id,
            'transfer_number' => $trfNumber,
            'quantity' => $qty,
            'status' => 'in_transit',
            'notes' => $args['notes'] ?? null,
            'shipped_at' => now(),
        ]);

        return [
            'status' => 'success',
            'message' => "Transfer **{$qty} {$product->unit} {$product->name}** sedang dalam pengiriman.\n"
                ."Dari: **{$fromWh->name}** → **{$toWh->name}**\n"
                ."No. Transfer: **{$trfNumber}** (status: **in_transit**)\n"
                .'Gunakan `receive_transfer` dengan nomor ini saat barang sudah diterima.',
            'data' => ['transfer_number' => $trfNumber, 'status' => 'in_transit'],
        ];
    }

    public function getTransferStatus(array $args): array
    {
        $query = StockTransfer::where('tenant_id', $this->tenantId)
            ->with(['product', 'fromWarehouse', 'toWarehouse', 'user']);

        if (! empty($args['transfer_number'])) {
            $query->where('transfer_number', $args['transfer_number']);
        }

        if (! empty($args['status'])) {
            $query->where('status', $args['status']);
        }

        if (! empty($args['warehouse_name'])) {
            $query->where(function ($q) use ($args) {
                $q->whereHas('fromWarehouse', fn ($w) => $w->where('name', 'like', "%{$args['warehouse_name']}%"))
                    ->orWhereHas('toWarehouse', fn ($w) => $w->where('name', 'like', "%{$args['warehouse_name']}%"));
            });
        }

        $transfers = $query->latest()->limit(20)->get();

        if ($transfers->isEmpty()) {
            return ['status' => 'success', 'message' => 'Tidak ada transfer yang ditemukan.'];
        }

        $list = $transfers->map(fn ($t) => [
            'nomor' => $t->transfer_number,
            'produk' => $t->product->name,
            'qty' => $t->quantity.' '.$t->product->unit,
            'dari' => $t->fromWarehouse->name,
            'ke' => $t->toWarehouse->name,
            'status' => $t->status,
            'dikirim' => $t->shipped_at?->format('d M Y H:i') ?? '-',
            'diterima' => $t->received_at?->format('d M Y H:i') ?? '-',
            'catatan' => $t->notes ?? '-',
        ])->toArray();

        return ['status' => 'success', 'data' => $list];
    }

    public function receiveTransfer(array $args): array
    {
        $transfer = StockTransfer::where('tenant_id', $this->tenantId)
            ->where('transfer_number', $args['transfer_number'])
            ->with(['product', 'fromWarehouse', 'toWarehouse'])
            ->first();

        if (! $transfer) {
            return ['status' => 'not_found', 'message' => "Transfer \"{$args['transfer_number']}\" tidak ditemukan."];
        }

        if ($transfer->status !== 'in_transit') {
            return [
                'status' => 'error',
                'message' => "Transfer **{$transfer->transfer_number}** tidak bisa dikonfirmasi.\n"
                    ."Status saat ini: **{$transfer->status}** (harus in_transit).",
            ];
        }

        // Tambah stok di gudang tujuan
        $toStock = ProductStock::firstOrCreate(
            ['product_id' => $transfer->product_id, 'warehouse_id' => $transfer->to_warehouse_id],
            ['quantity' => 0]
        );

        $toBefore = $toStock->quantity;
        $toStock->increment('quantity', $transfer->quantity);

        StockMovement::create([
            'tenant_id' => $this->tenantId,
            'product_id' => $transfer->product_id,
            'warehouse_id' => $transfer->to_warehouse_id,
            'user_id' => $this->userId,
            'type' => 'in',
            'quantity' => $transfer->quantity,
            'quantity_before' => $toBefore,
            'quantity_after' => $toBefore + $transfer->quantity,
            'reference' => $transfer->transfer_number,
            'notes' => "Diterima dari {$transfer->fromWarehouse->name}".($args['notes'] ? " — {$args['notes']}" : ''),
        ]);

        $transfer->update([
            'status' => 'completed',
            'received_at' => now(),
            'notes' => $transfer->notes.($args['notes'] ? "\nPenerimaan: {$args['notes']}" : ''),
        ]);

        return [
            'status' => 'success',
            'message' => "Transfer **{$transfer->transfer_number}** berhasil diterima.\n"
                ."Produk: **{$transfer->product->name}** — {$transfer->quantity} {$transfer->product->unit}\n"
                ."Gudang tujuan: **{$transfer->toWarehouse->name}** (stok sekarang: ".($toBefore + $transfer->quantity)." {$transfer->product->unit})",
            'data' => ['transfer_number' => $transfer->transfer_number, 'status' => 'completed'],
        ];
    }

    public function listWarehouses(array $args): array
    {
        $warehouses = Warehouse::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->with(['productStocks.product'])
            ->get();

        if ($warehouses->isEmpty()) {
            return ['status' => 'success', 'message' => 'Belum ada gudang yang terdaftar.'];
        }

        $includeStock = $args['include_stock'] ?? true;

        $list = $warehouses->map(function ($wh) use ($includeStock) {
            $row = [
                'nama' => $wh->name,
                'kode' => $wh->code,
                'alamat' => $wh->address ?? '-',
                'total_sku' => $wh->productStocks->where('quantity', '>', 0)->count(),
            ];

            if ($includeStock) {
                $totalNilai = $wh->productStocks->filter(fn ($s) => $s->product !== null)
                    ->sum(fn ($s) => $s->quantity * ($s->product->price_buy ?? 0));
                $row['total_nilai_stok'] = 'Rp '.number_format($totalNilai, 0, ',', '.');
            }

            return $row;
        })->toArray();

        return [
            'status' => 'success',
            'data' => [
                'total_gudang' => $warehouses->count(),
                'gudang' => $list,
            ],
        ];
    }

    public function adjustStock(array $args): array
    {
        $product = $this->findProduct($args['product_name']);
        if (! $product) {
            return ['status' => 'error', 'message' => "Produk \"{$args['product_name']}\" tidak ditemukan."];
        }

        $warehouse = $this->findWarehouse($args['warehouse_name']);
        if (! $warehouse) {
            return ['status' => 'error', 'message' => "Gudang \"{$args['warehouse_name']}\" tidak ditemukan."];
        }

        $stock = ProductStock::firstOrCreate(
            ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
            ['quantity' => 0]
        );

        $before = $stock->quantity;
        $actualQty = (int) $args['actual_qty'];
        $diff = $actualQty - $before;

        if ($diff === 0) {
            return ['status' => 'success', 'message' => "Stok **{$product->name}** di **{$warehouse->name}** sudah sesuai: **{$before} {$product->unit}**. Tidak ada perubahan."];
        }

        $stock->update(['quantity' => $actualQty]);

        StockMovement::create([
            'tenant_id' => $this->tenantId,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'user_id' => $this->userId,
            'type' => 'adjustment',
            'quantity' => $diff,
            'quantity_before' => $before,
            'quantity_after' => $actualQty,
            'notes' => $args['notes'] ?? 'Stock opname / penyesuaian stok',
        ]);

        $diffStr = $diff > 0 ? "+{$diff}" : "{$diff}";
        $icon = $diff > 0 ? '📈' : '📉';

        return [
            'status' => 'success',
            'message' => "{$icon} Stok **{$product->name}** di **{$warehouse->name}** disesuaikan.\n"
                ."Sebelum: **{$before} {$product->unit}** → Sekarang: **{$actualQty} {$product->unit}** ({$diffStr})\n"
                .'Alasan: '.($args['notes'] ?? 'Stock opname'),
            'data' => [
                'product' => $product->name,
                'warehouse' => $warehouse->name,
                'before' => $before,
                'after' => $actualQty,
                'diff' => $diff,
            ],
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────

    protected function findProduct(string $nameOrSku): ?Product
    {
        return Product::where('tenant_id', $this->tenantId)
            ->where(fn ($q) => $q->where('name', 'like', "%{$nameOrSku}%")
                ->orWhere('sku', $nameOrSku))
            ->first();
    }

    protected function findWarehouse(string $name): ?Warehouse
    {
        return Warehouse::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$name}%")
            ->where('is_active', true)
            ->first();
    }
}
