<?php

namespace App\Services\ERP;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesOrderItem;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Storage;

class InventoryTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    // ─── Tool Definitions (dikirim ke Gemini) ────────────────────

    public static function definitions(): array
    {
        return [
            [
                'name' => 'check_inventory',
                'description' => 'Cek stok produk di gudang tertentu atau semua gudang.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'product_name' => ['type' => 'string', 'description' => 'Nama atau SKU produk'],
                        'warehouse' => ['type' => 'string', 'description' => 'Nama gudang (opsional, kosong = semua gudang)'],
                    ],
                    'required' => ['product_name'],
                ],
            ],
            [
                'name' => 'get_low_stock',
                'description' => 'Tampilkan produk yang stoknya di bawah batas minimum.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'warehouse' => ['type' => 'string', 'description' => 'Filter per gudang (opsional)'],
                    ],
                ],
            ],
            [
                'name' => 'list_products',
                'description' => 'Tampilkan semua produk milik tenant beserta stok total dan statusnya. Gunakan ini ketika user ingin melihat daftar produk, semua barang, atau inventori secara keseluruhan.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'category' => ['type' => 'string', 'description' => 'Filter berdasarkan kategori (opsional)'],
                        'search' => ['type' => 'string', 'description' => 'Kata kunci pencarian nama/SKU (opsional)'],
                    ],
                ],
            ],
            [
                'name' => 'add_stock',
                'description' => 'Tambah stok produk ke gudang (stock in).',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'product_name' => ['type' => 'string', 'description' => 'Nama atau SKU produk'],
                        'warehouse' => ['type' => 'string', 'description' => 'Nama gudang tujuan'],
                        'quantity' => ['type' => 'integer', 'description' => 'Jumlah yang ditambahkan'],
                        'reference' => ['type' => 'string', 'description' => 'Nomor referensi (PO, dll)'],
                        'notes' => ['type' => 'string', 'description' => 'Catatan tambahan'],
                    ],
                    'required' => ['product_name', 'warehouse', 'quantity'],
                ],
            ],
            [
                'name' => 'create_product',
                'description' => 'Tambah produk baru ke sistem. Gunakan untuk perintah seperti: '
                    .'"tambah produk Kopi Hitam harga jual 8000 stok awal 100", '
                    .'"daftarkan barang Teh Botol harga 5000", '
                    .'"buat produk baru Mie Ayam satuan porsi harga 15000".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string', 'description' => 'Nama produk'],
                        'price_sell' => ['type' => 'number', 'description' => 'Harga jual'],
                        'price_buy' => ['type' => 'number', 'description' => 'Harga beli/modal (opsional)'],
                        'unit' => ['type' => 'string', 'description' => 'Satuan: pcs, kg, liter, porsi, gelas, dll. Default: pcs'],
                        'category' => ['type' => 'string', 'description' => 'Kategori produk (opsional)'],
                        'sku' => ['type' => 'string', 'description' => 'Kode SKU (opsional, auto-generate jika kosong)'],
                        'stock_min' => ['type' => 'integer', 'description' => 'Stok minimum sebelum notifikasi menipis. Default: 5'],
                        'initial_stock' => ['type' => 'integer', 'description' => 'Stok awal yang langsung dimasukkan ke gudang (opsional)'],
                        'description' => ['type' => 'string', 'description' => 'Deskripsi produk (opsional)'],
                    ],
                    'required' => ['name', 'price_sell'],
                ],
            ],
            [
                'name' => 'update_product',
                'description' => 'Ubah data produk yang sudah ada. Gunakan untuk: '
                    .'"ubah harga Kopi Hitam jadi 9000", '
                    .'"ganti nama Teh Manis jadi Teh Botol", '
                    .'"update stok minimum Kopi jadi 20", '
                    .'"nonaktifkan produk Sirup Merah".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'product_name' => ['type' => 'string', 'description' => 'Nama atau SKU produk yang ingin diubah'],
                        'new_name' => ['type' => 'string', 'description' => 'Nama baru (opsional)'],
                        'price_sell' => ['type' => 'number', 'description' => 'Harga jual baru (opsional)'],
                        'price_buy' => ['type' => 'number', 'description' => 'Harga beli baru (opsional)'],
                        'unit' => ['type' => 'string', 'description' => 'Satuan baru (opsional)'],
                        'category' => ['type' => 'string', 'description' => 'Kategori baru (opsional)'],
                        'stock_min' => ['type' => 'integer', 'description' => 'Stok minimum baru (opsional)'],
                        'is_active' => ['type' => 'boolean', 'description' => 'true = aktif, false = nonaktif'],
                        'description' => ['type' => 'string', 'description' => 'Deskripsi baru (opsional)'],
                    ],
                    'required' => ['product_name'],
                ],
            ],
            [
                'name' => 'delete_product',
                'description' => 'Hapus atau nonaktifkan produk. Gunakan untuk: '
                    .'"hapus produk Teh Tawar", "nonaktifkan Kopi Susu". '
                    .'Produk yang sudah pernah terjual akan dinonaktifkan, bukan dihapus permanen.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'product_name' => ['type' => 'string', 'description' => 'Nama atau SKU produk'],
                        'permanent' => ['type' => 'boolean', 'description' => 'true = hapus permanen (hanya jika belum pernah terjual), false = nonaktifkan saja. Default: false'],
                    ],
                    'required' => ['product_name'],
                ],
            ],
            [
                'name' => 'create_warehouse',
                'description' => 'Buat gudang baru. Gunakan untuk: '
                    .'"buat gudang Toko Utama", "tambah gudang Cabang Selatan", "setup gudang baru".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string', 'description' => 'Nama gudang'],
                        'code' => ['type' => 'string', 'description' => 'Kode gudang (opsional, auto-generate jika kosong)'],
                        'address' => ['type' => 'string', 'description' => 'Alamat gudang (opsional)'],
                    ],
                    'required' => ['name'],
                ],
            ],
            [
                'name' => 'update_product_image',
                'description' => 'Simpan gambar ke produk tertentu. Gunakan setelah user mengirim foto produk via chat. '
                    .'Sistem akan otomatis menyediakan image_url dari file yang diupload. '
                    .'Contoh: "simpan gambar ini untuk produk Kopi Hitam", "set foto produk Teh Botol".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'product_name' => ['type' => 'string', 'description' => 'Nama atau SKU produk yang akan diupdate fotonya'],
                        'image_url' => ['type' => 'string', 'description' => 'URL gambar — diisi otomatis oleh sistem dari file yang diupload user'],
                    ],
                    'required' => ['product_name'],
                ],
            ],
            [
                'name' => 'identify_product_from_image',
                'description' => 'Identifikasi produk dari gambar yang dikirim user, lalu cocokkan dengan produk yang ada di sistem. '
                    .'Gunakan tool ini PERTAMA KALI ketika user mengirim foto produk tanpa menyebut nama produk secara eksplisit. '
                    .'Tool ini akan mencari produk yang paling cocok berdasarkan nama yang kamu deteksi dari gambar.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'detected_name' => ['type' => 'string', 'description' => 'Nama produk yang kamu deteksi/identifikasi dari gambar'],
                        'confidence' => ['type' => 'string', 'description' => 'Tingkat keyakinan: high, medium, low'],
                        'description' => ['type' => 'string', 'description' => 'Deskripsi singkat apa yang terlihat di gambar'],
                    ],
                    'required' => ['detected_name'],
                ],
            ],
        ];
    }

    // ─── Executors ────────────────────────────────────────────────

    public function listProducts(array $args): array
    {
        // BUG-INV-002 FIX: Eager load with selective columns to reduce memory
        $query = Product::where('tenant_id', $this->tenantId)
            ->with([
                'productStocks' => function ($q) {
                    $q->select('id', 'product_id', 'warehouse_id', 'quantity');
                },
            ]);

        if (! empty($args['search'])) {
            $kw = $args['search'];
            $query->where(fn ($q) => $q->where('name', 'like', "%{$kw}%")->orWhere('sku', 'like', "%{$kw}%"));
        }

        if (! empty($args['category'])) {
            $query->where('category', 'like', "%{$args['category']}%");
        }

        $products = $query->orderBy('name')->get();

        if ($products->isEmpty()) {
            return ['status' => 'success', 'message' => 'Belum ada produk yang terdaftar.'];
        }

        return [
            'status' => 'success',
            'total' => $products->count(),
            'data' => $products->map(fn ($p) => [
                'name' => $p->name,
                'sku' => $p->sku,
                'category' => $p->category ?? '-',
                'unit' => $p->unit,
                'price_sell' => 'Rp '.number_format($p->price_sell ?? 0, 0, ',', '.'),
                'total_stock' => $p->productStocks->sum('quantity'),
                'stock_min' => $p->stock_min,
                'status' => $p->productStocks->sum('quantity') <= $p->stock_min ? 'LOW' : 'OK',
            ])->toArray(),
        ];
    }

    public function createWarehouse(array $args): array
    {
        $existing = Warehouse::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['name']}%")
            ->first();

        if ($existing) {
            return ['status' => 'error', 'message' => "Gudang '{$args['name']}' sudah ada."];
        }

        $code = $args['code'] ?? strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $args['name']), 0, 4)).'-'.rand(10, 99);

        $warehouse = Warehouse::create([
            'tenant_id' => $this->tenantId,
            'name' => $args['name'],
            'code' => $code,
            'address' => $args['address'] ?? null,
            'is_active' => true,
        ]);

        return [
            'status' => 'success',
            'message' => "Gudang **{$warehouse->name}** (kode: `{$warehouse->code}`) berhasil dibuat.",
            'data' => ['id' => $warehouse->id, 'name' => $warehouse->name, 'code' => $warehouse->code],
        ];
    }

    public function checkInventory(array $args): array
    {
        $query = Product::where('tenant_id', $this->tenantId)
            ->where(fn ($q) => $q->where('name', 'like', "%{$args['product_name']}%")
                ->orWhere('sku', $args['product_name']));

        // BUG-INV-002 FIX: Eager load warehouse relationship to prevent N+1
        $products = $query->with([
            'productStocks' => function ($q) {
                $q->select('id', 'product_id', 'warehouse_id', 'quantity');
                $q->with([
                    'warehouse' => function ($q2) {
                        $q2->select('id', 'name');
                    },
                ]);
            },
        ])->get();

        if ($products->isEmpty()) {
            return ['status' => 'not_found', 'message' => "Produk '{$args['product_name']}' tidak ditemukan."];
        }

        $result = [];
        foreach ($products as $product) {
            $stocks = $product->productStocks;

            if (! empty($args['warehouse'])) {
                $stocks = $stocks->filter(fn ($s) => str_contains(
                    strtolower($s->warehouse->name),
                    strtolower($args['warehouse'])
                ));
            }

            $result[] = [
                'product' => $product->name,
                'sku' => $product->sku,
                'unit' => $product->unit,
                'stock_min' => $product->stock_min,
                'stocks' => $stocks->map(fn ($s) => [
                    'warehouse' => $s->warehouse->name,
                    'quantity' => $s->quantity,
                    'status' => $s->quantity <= $product->stock_min ? 'LOW' : 'OK',
                ])->values()->toArray(),
            ];
        }

        return ['status' => 'success', 'data' => $result];
    }

    public function getLowStock(array $args): array
    {
        $stocks = ProductStock::with(['product', 'warehouse'])
            ->whereHas('product', fn ($q) => $q->where('tenant_id', $this->tenantId))
            ->whereColumn('quantity', '<=', 'products.stock_min')
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->select('product_stocks.*')
            ->get();

        if (! empty($args['warehouse'])) {
            $stocks = $stocks->filter(fn ($s) => str_contains(
                strtolower($s->warehouse->name),
                strtolower($args['warehouse'])
            ));
        }

        if ($stocks->isEmpty()) {
            return ['status' => 'success', 'message' => 'Semua stok dalam kondisi aman.'];
        }

        return [
            'status' => 'success',
            'data' => $stocks->map(fn ($s) => [
                'product' => $s->product->name,
                'warehouse' => $s->warehouse->name,
                'quantity' => $s->quantity,
                'min_stock' => $s->product->stock_min,
            ])->values()->toArray(),
        ];
    }

    public function addStock(array $args): array
    {
        $product = Product::where('tenant_id', $this->tenantId)
            ->where(fn ($q) => $q->where('name', 'like', "%{$args['product_name']}%")
                ->orWhere('sku', $args['product_name']))
            ->first();

        if (! $product) {
            return ['status' => 'error', 'message' => "Produk '{$args['product_name']}' tidak ditemukan."];
        }

        $warehouse = Warehouse::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['warehouse']}%")
            ->first();

        if (! $warehouse) {
            return ['status' => 'error', 'message' => "Gudang '{$args['warehouse']}' tidak ditemukan."];
        }

        $stock = ProductStock::firstOrCreate(
            ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
            ['quantity' => 0]
        );

        $before = $stock->quantity;
        $stock->increment('quantity', $args['quantity']);

        StockMovement::create([
            'tenant_id' => $this->tenantId,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'user_id' => $this->userId,
            'type' => 'in',
            'quantity' => $args['quantity'],
            'quantity_before' => $before,
            'quantity_after' => $before + $args['quantity'],
            'reference' => $args['reference'] ?? null,
            'notes' => $args['notes'] ?? null,
        ]);

        return [
            'status' => 'success',
            'message' => "Stok {$product->name} di {$warehouse->name} berhasil ditambah {$args['quantity']} {$product->unit}. Total sekarang: ".($before + $args['quantity'])." {$product->unit}.",
        ];
    }

    public function createProduct(array $args): array
    {
        // Cek duplikat nama dalam tenant
        $exists = Product::where('tenant_id', $this->tenantId)
            ->where('name', $args['name'])
            ->exists();

        if ($exists) {
            return ['status' => 'error', 'message' => "Produk \"{$args['name']}\" sudah terdaftar. Gunakan update_product untuk mengubahnya."];
        }

        $sku = $args['sku'] ?? strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $args['name']), 0, 6)).'-'.rand(100, 999);

        $product = Product::create([
            'tenant_id' => $this->tenantId,
            'name' => $args['name'],
            'sku' => $sku,
            'price_sell' => $args['price_sell'],
            'price_buy' => $args['price_buy'] ?? 0,
            'unit' => $args['unit'] ?? 'pcs',
            'category' => $args['category'] ?? null,
            'stock_min' => $args['stock_min'] ?? 5,
            'description' => $args['description'] ?? null,
            'is_active' => true,
        ]);

        // Jika ada stok awal, masukkan ke gudang default
        $stockMsg = '';
        if (! empty($args['initial_stock']) && $args['initial_stock'] > 0) {
            $warehouse = Warehouse::where('tenant_id', $this->tenantId)->first();
            if ($warehouse) {
                ProductStock::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $args['initial_stock'],
                ]);
                StockMovement::create([
                    'tenant_id' => $this->tenantId,
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'user_id' => $this->userId,
                    'type' => 'in',
                    'quantity' => $args['initial_stock'],
                    'quantity_before' => 0,
                    'quantity_after' => $args['initial_stock'],
                    'notes' => 'Stok awal produk baru',
                ]);
                $stockMsg = " Stok awal **{$args['initial_stock']} {$product->unit}** sudah dimasukkan ke gudang {$warehouse->name}.";
            }
        }

        return [
            'status' => 'success',
            'message' => "✅ Produk **{$product->name}** berhasil ditambahkan. SKU: `{$product->sku}`, Harga jual: Rp ".number_format($product->price_sell, 0, ',', '.').".{$stockMsg}",
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price_sell' => $product->price_sell,
                'unit' => $product->unit,
            ],
        ];
    }

    public function updateProduct(array $args): array
    {
        $product = Product::where('tenant_id', $this->tenantId)
            ->where(fn ($q) => $q->where('name', 'like', "%{$args['product_name']}%")
                ->orWhere('sku', $args['product_name']))
            ->first();

        if (! $product) {
            return ['status' => 'error', 'message' => "Produk \"{$args['product_name']}\" tidak ditemukan."];
        }

        $changes = [];
        $updates = [];

        if (isset($args['new_name']) && $args['new_name'] !== $product->name) {
            $updates['name'] = $args['new_name'];
            $changes[] = "nama: **{$product->name}** → **{$args['new_name']}**";
        }
        if (isset($args['price_sell'])) {
            $old = number_format($product->price_sell, 0, ',', '.');
            $new = number_format($args['price_sell'], 0, ',', '.');
            $updates['price_sell'] = $args['price_sell'];
            $changes[] = "harga jual: Rp {$old} → Rp {$new}";
        }
        if (isset($args['price_buy'])) {
            $updates['price_buy'] = $args['price_buy'];
            $changes[] = 'harga beli: Rp '.number_format($args['price_buy'], 0, ',', '.');
        }
        if (isset($args['unit'])) {
            $updates['unit'] = $args['unit'];
            $changes[] = "satuan: **{$args['unit']}**";
        }
        if (isset($args['category'])) {
            $updates['category'] = $args['category'];
            $changes[] = "kategori: **{$args['category']}**";
        }
        if (isset($args['stock_min'])) {
            $updates['stock_min'] = $args['stock_min'];
            $changes[] = "stok minimum: **{$args['stock_min']}**";
        }
        if (isset($args['is_active'])) {
            $updates['is_active'] = $args['is_active'];
            $changes[] = $args['is_active'] ? 'status: **Aktif**' : 'status: **Nonaktif**';
        }
        if (isset($args['description'])) {
            $updates['description'] = $args['description'];
            $changes[] = 'deskripsi diperbarui';
        }

        if (empty($updates)) {
            return ['status' => 'error', 'message' => 'Tidak ada perubahan yang diberikan.'];
        }

        $product->update($updates);

        return [
            'status' => 'success',
            'message' => "✅ Produk **{$product->name}** berhasil diperbarui.\nPerubahan: ".implode(', ', $changes).'.',
        ];
    }

    public function deleteProduct(array $args): array
    {
        $product = Product::where('tenant_id', $this->tenantId)
            ->where(fn ($q) => $q->where('name', 'like', "%{$args['product_name']}%")
                ->orWhere('sku', $args['product_name']))
            ->first();

        if (! $product) {
            return ['status' => 'error', 'message' => "Produk \"{$args['product_name']}\" tidak ditemukan."];
        }

        $permanent = $args['permanent'] ?? false;

        // Cek apakah produk pernah terjual
        $hasSales = SalesOrderItem::where('product_id', $product->id)->exists();

        if ($permanent && $hasSales) {
            // Tidak bisa hapus permanen, nonaktifkan saja
            $product->update(['is_active' => false]);

            return [
                'status' => 'success',
                'message' => "Produk **{$product->name}** tidak bisa dihapus permanen karena sudah pernah terjual. Produk telah **dinonaktifkan** sebagai gantinya.",
            ];
        }

        if ($permanent && ! $hasSales) {
            $name = $product->name;
            $product->productStocks()->delete();
            $product->delete();

            return [
                'status' => 'success',
                'message' => "🗑️ Produk **{$name}** berhasil dihapus permanen.",
            ];
        }

        // Default: nonaktifkan saja
        $product->update(['is_active' => false]);

        return [
            'status' => 'success',
            'message' => "Produk **{$product->name}** berhasil dinonaktifkan. Produk tidak akan muncul di daftar aktif, tapi riwayat transaksinya tetap tersimpan.",
        ];
    }

    public function updateProductImage(array $args): array
    {
        $product = Product::where('tenant_id', $this->tenantId)
            ->where(fn ($q) => $q->where('name', 'like', "%{$args['product_name']}%")
                ->orWhere('sku', $args['product_name']))
            ->first();

        if (! $product) {
            return ['status' => 'error', 'message' => "Produk \"{$args['product_name']}\" tidak ditemukan."];
        }

        $imageUrl = $args['image_url'] ?? null;

        // Jika tidak ada URL (AI tidak kirim), cek apakah ada pending_image_url di session
        if (! $imageUrl) {
            return [
                'status' => 'error',
                'message' => 'URL gambar tidak tersedia. Pastikan kamu mengirim gambar bersamaan dengan perintah.',
            ];
        }

        // Jika URL adalah path lokal storage (dari upload chat), gunakan langsung
        if (str_starts_with($imageUrl, '/storage/') || str_starts_with($imageUrl, 'http')) {
            // Hapus gambar lama jika ada
            if ($product->image && str_starts_with($product->image, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $product->image);
                Storage::disk('public')->delete($oldPath);
            }

            $product->update(['image' => $imageUrl]);

            return [
                'status' => 'success',
                'message' => "✅ Foto produk **{$product->name}** berhasil disimpan.",
                'data' => ['product_id' => $product->id, 'product' => $product->name, 'image_url' => $imageUrl],
                'actions' => [
                    ['label' => 'Lihat di Inventori', 'message' => "tampilkan detail produk {$product->name}", 'style' => 'primary', 'icon' => '📦'],
                    ['label' => 'Update Produk Lain', 'message' => 'kirim foto produk lain untuk diupdate', 'style' => 'default', 'icon' => '📷'],
                ],
            ];
        }

        // Fallback: download dari URL eksternal
        try {
            $contents = @file_get_contents($imageUrl);
            if ($contents === false) {
                $product->update(['image' => $imageUrl]);

                return ['status' => 'success', 'message' => "✅ Foto produk **{$product->name}** berhasil diperbarui."];
            }

            $ext = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $ext = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? strtolower($ext) : 'jpg';
            $filename = 'products/'.uniqid('prod_').'.'.$ext;

            Storage::disk('public')->put($filename, $contents);

            if ($product->image && str_starts_with($product->image, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $product->image);
                Storage::disk('public')->delete($oldPath);
            }

            $storedUrl = Storage::url($filename);
            $product->update(['image' => $storedUrl]);

            return [
                'status' => 'success',
                'message' => "✅ Foto produk **{$product->name}** berhasil disimpan.",
                'data' => ['product_id' => $product->id, 'product' => $product->name, 'image_url' => $storedUrl],
            ];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => 'Gagal menyimpan gambar: '.$e->getMessage()];
        }
    }

    public function identifyProductFromImage(array $args): array
    {
        $detectedName = $args['detected_name'] ?? '';
        $confidence = $args['confidence'] ?? 'medium';
        $description = $args['description'] ?? '';

        if (! $detectedName) {
            return ['status' => 'error', 'message' => 'Tidak dapat mendeteksi nama produk dari gambar.'];
        }

        // Cari produk yang cocok di database
        $products = Product::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->where(
                fn ($q) => $q
                    ->where('name', 'like', "%{$detectedName}%")
                    ->orWhere('sku', 'like', "%{$detectedName}%")
                    ->orWhere('category', 'like', "%{$detectedName}%")
            )
            ->limit(5)
            ->get(['id', 'name', 'sku', 'category', 'unit', 'price_sell']);

        if ($products->isEmpty()) {
            // Coba fuzzy match — ambil semua produk dan cari yang paling mirip
            $allProducts = Product::where('tenant_id', $this->tenantId)
                ->where('is_active', true)
                ->get(['id', 'name', 'sku', 'category']);

            $bestMatch = null;
            $bestScore = 0;
            foreach ($allProducts as $p) {
                similar_text(strtolower($detectedName), strtolower($p->name), $pct);
                if ($pct > $bestScore) {
                    $bestScore = $pct;
                    $bestMatch = $p;
                }
            }

            if ($bestMatch && $bestScore > 40) {
                $products = collect([$bestMatch->fresh(['id', 'name', 'sku', 'category', 'unit', 'price_sell'])]);
            }
        }

        $confidenceLabel = match ($confidence) {
            'high' => 'tinggi',
            'medium' => 'sedang',
            default => 'rendah',
        };

        if ($products->isEmpty()) {
            return [
                'status' => 'not_found',
                'detected' => $detectedName,
                'description' => $description,
                'message' => "Gambar terdeteksi sebagai **{$detectedName}** (keyakinan: {$confidenceLabel}), tapi tidak ada produk yang cocok di sistem.",
                'actions' => [
                    ['label' => "Buat Produk Baru: {$detectedName}", 'message' => "buat produk baru bernama {$detectedName}", 'style' => 'primary', 'icon' => '➕'],
                    ['label' => 'Cari Produk Manual', 'message' => 'tampilkan semua produk', 'style' => 'default', 'icon' => '🔍'],
                ],
            ];
        }

        $matchList = $products->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'category' => $p->category ?? '-',
        ])->toArray();

        return [
            'status' => 'found',
            'detected' => $detectedName,
            'confidence' => $confidence,
            'description' => $description,
            'matches' => $matchList,
            'message' => "Gambar terdeteksi sebagai **{$detectedName}** (keyakinan: {$confidenceLabel}). Ditemukan ".count($matchList).' produk yang cocok.',
        ];
    }
}
