<?php

namespace App\Services\ERP;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecipeTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    // ─── Tool Definitions ─────────────────────────────────────────

    public static function definitions(): array
    {
        return [
            [
                'name' => 'create_recipe',
                'description' => 'Buat atau perbarui resep/BOM (Bill of Materials) produk. '
                    .'Gunakan untuk: "buat resep kopi susu: kopi 10g, susu 100ml, gula 5g", '
                    .'"definisikan bahan kaos L: kain 1.5m, benang 50g, kancing 5pcs", '
                    .'"update resep nasi goreng".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'product_name' => ['type' => 'string', 'description' => 'Nama produk jadi yang dihasilkan'],
                        'recipe_name' => ['type' => 'string', 'description' => 'Nama resep (opsional, default: nama produk)'],
                        'batch_size' => ['type' => 'number',  'description' => 'Berapa unit produk jadi yang dihasilkan per batch (default: 1)'],
                        'batch_unit' => ['type' => 'string',  'description' => 'Satuan produk jadi: pcs, gelas, porsi, kg, dll (default: pcs)'],
                        'ingredients' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'name' => ['type' => 'string', 'description' => 'Nama bahan baku'],
                                    'quantity' => ['type' => 'number', 'description' => 'Jumlah bahan per batch'],
                                    'unit' => ['type' => 'string', 'description' => 'Satuan bahan: g, ml, pcs, kg, liter, dll'],
                                ],
                                'required' => ['name', 'quantity', 'unit'],
                            ],
                            'description' => 'Daftar bahan baku beserta kuantitasnya',
                        ],
                        'notes' => ['type' => 'string', 'description' => 'Catatan resep (opsional)'],
                    ],
                    'required' => ['product_name', 'ingredients'],
                ],
            ],
            [
                'name' => 'get_recipe',
                'description' => 'Tampilkan resep/BOM sebuah produk beserta semua bahan bakunya. '
                    .'Gunakan untuk: "lihat resep kopi susu", "bahan apa saja untuk kaos L", '
                    .'"komposisi produk X", "formula produk Y".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'product_name' => ['type' => 'string', 'description' => 'Nama produk'],
                    ],
                    'required' => ['product_name'],
                ],
            ],
            [
                'name' => 'get_recipe_cost',
                'description' => 'Hitung HPP (Harga Pokok Produksi) produk berdasarkan harga beli bahan baku terkini. '
                    .'Gunakan untuk: "HPP kopi susu berapa?", "harga pokok kaos L", '
                    .'"biaya bahan baku per unit", "laba menu kopi berapa?".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'product_name' => ['type' => 'string', 'description' => 'Nama produk'],
                        'quantity' => ['type' => 'number',  'description' => 'Hitung HPP untuk berapa unit (default: 1)'],
                    ],
                    'required' => ['product_name'],
                ],
            ],
            [
                'name' => 'produce_with_recipe',
                'description' => 'Produksi produk menggunakan resep — otomatis kurangi stok bahan baku dan tambah stok produk jadi. '
                    .'Gunakan untuk: "produksi 50 gelas kopi susu", "buat 100 kaos ukuran L", '
                    .'"proses produksi 20 porsi nasi goreng", "bikin 30 pcs produk X". '
                    .'Jika dry_run=true, hanya cek ketersediaan stok tanpa mengubah data.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'product_name' => ['type' => 'string',  'description' => 'Nama produk yang diproduksi'],
                        'quantity' => ['type' => 'number',  'description' => 'Jumlah unit yang diproduksi'],
                        'warehouse' => ['type' => 'string',  'description' => 'Nama gudang sumber bahan baku (opsional, default: gudang pertama)'],
                        'dry_run' => ['type' => 'boolean', 'description' => 'true = hanya cek stok tanpa produksi. Default: false'],
                        'notes' => ['type' => 'string',  'description' => 'Catatan produksi (opsional)'],
                    ],
                    'required' => ['product_name', 'quantity'],
                ],
            ],
        ];
    }

    // ─── Executors ────────────────────────────────────────────────

    public function createRecipe(array $args): array
    {
        if (empty($args['ingredients'])) {
            return ['status' => 'error', 'message' => 'Recipe harus memiliki minimal 1 bahan baku.'];
        }

        // Cari produk jadi
        $product = Product::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['product_name']}%")
            ->first();

        if (! $product) {
            return [
                'status' => 'error',
                'message' => "Produk \"{$args['product_name']}\" tidak ditemukan. Tambahkan produk terlebih dahulu dengan `create_product`.",
            ];
        }

        // Validasi semua bahan baku ada di sistem
        $ingredientData = [];
        $notFound = [];

        foreach ($args['ingredients'] as $ing) {
            $bahan = Product::where('tenant_id', $this->tenantId)
                ->where('name', 'like', "%{$ing['name']}%")
                ->first();

            if (! $bahan) {
                $notFound[] = $ing['name'];

                continue;
            }

            $ingredientData[] = [
                'product' => $bahan,
                'quantity_per_batch' => $ing['quantity'],
                'unit' => $ing['unit'],
            ];
        }

        if (! empty($notFound)) {
            return [
                'status' => 'error',
                'message' => 'Bahan baku berikut tidak ditemukan di sistem: **'.implode('**, **', $notFound).'**. '
                    .'Tambahkan produk bahan baku terlebih dahulu.',
            ];
        }

        $batchSize = $args['batch_size'] ?? 1;
        $batchUnit = $args['batch_unit'] ?? $product->unit;

        return DB::transaction(function () use ($product, $args, $ingredientData, $batchSize, $batchUnit) {
            // Deactivate recipe lama jika ada (soft-replace, history tetap tersimpan)
            $oldCount = Recipe::where('tenant_id', $this->tenantId)
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->count();

            Recipe::where('tenant_id', $this->tenantId)
                ->where('product_id', $product->id)
                ->update(['is_active' => false]);

            // Buat recipe baru
            $recipe = Recipe::create([
                'tenant_id' => $this->tenantId,
                'product_id' => $product->id,
                'name' => $args['recipe_name'] ?? $product->name,
                'batch_size' => $batchSize,
                'batch_unit' => $batchUnit,
                'notes' => $args['notes'] ?? null,
                'is_active' => true,
            ]);

            foreach ($ingredientData as $ing) {
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'product_id' => $ing['product']->id,
                    'quantity_per_batch' => $ing['quantity_per_batch'],
                    'unit' => $ing['unit'],
                ]);
            }

            $ingList = collect($ingredientData)->map(fn ($i) => "- **{$i['product']->name}**: {$i['quantity_per_batch']} {$i['unit']}"
            )->implode("\n");

            $updateMsg = $oldCount > 0 ? ' (resep lama digantikan)' : '';

            return [
                'status' => 'success',
                'message' => "Resep **{$recipe->name}** untuk produk **{$product->name}** berhasil disimpan{$updateMsg}.\n\n"
                    ."Menghasilkan: **{$batchSize} {$batchUnit}** per batch\n\n"
                    ."Bahan baku:\n{$ingList}",
                'data' => [
                    'recipe_id' => $recipe->id,
                    'product' => $product->name,
                    'batch_size' => $batchSize,
                    'ingredients' => count($ingredientData),
                ],
            ];
        });
    }

    public function getRecipe(array $args): array
    {
        $product = Product::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['product_name']}%")
            ->first();

        if (! $product) {
            return ['status' => 'not_found', 'message' => "Produk \"{$args['product_name']}\" tidak ditemukan."];
        }

        $recipe = Recipe::where('tenant_id', $this->tenantId)
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->with(['ingredients.product'])
            ->first();

        if (! $recipe) {
            return [
                'status' => 'not_found',
                'message' => "Produk **{$product->name}** belum memiliki resep. Buat resep dengan `create_recipe`.",
            ];
        }

        $hpp = $recipe->calculateHpp();

        return [
            'status' => 'success',
            'data' => [
                'produk' => $product->name,
                'resep' => $recipe->name,
                'batch_size' => "{$recipe->batch_size} {$recipe->batch_unit}",
                'hpp_per_unit' => 'Rp '.number_format($hpp, 0, ',', '.'),
                'bahan_baku' => $recipe->ingredients->map(fn ($ing) => [
                    'bahan' => $ing->product->name,
                    'qty_per_batch' => $ing->quantity_per_batch + 0, // cast ke float
                    'unit' => $ing->unit,
                    'harga_beli' => 'Rp '.number_format($ing->product->price_buy ?? 0, 0, ',', '.'),
                    'biaya_per_batch' => 'Rp '.number_format(($ing->product->price_buy ?? 0) * $ing->quantity_per_batch, 0, ',', '.'),
                ])->toArray(),
            ],
        ];
    }

    public function getRecipeCost(array $args): array
    {
        $product = Product::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['product_name']}%")
            ->first();

        if (! $product) {
            return ['status' => 'not_found', 'message' => "Produk \"{$args['product_name']}\" tidak ditemukan."];
        }

        $recipe = Recipe::where('tenant_id', $this->tenantId)
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->with(['ingredients.product'])
            ->first();

        if (! $recipe) {
            return [
                'status' => 'not_found',
                'message' => "Produk **{$product->name}** belum memiliki resep. Buat resep dulu dengan `create_recipe`.",
            ];
        }

        $qty = $args['quantity'] ?? 1;
        $hpp = $recipe->calculateHpp();
        $total = $hpp * $qty;
        $margin = $product->price_sell > 0 ? $product->price_sell - $hpp : null;
        $marginPct = ($margin !== null && $hpp > 0) ? ($margin / $hpp) * 100 : null;

        $breakdown = $recipe->ingredients->map(fn ($ing) => [
            'bahan' => $ing->product->name,
            'qty_per_unit' => round($ing->quantity_per_batch / $recipe->batch_size, 4).' '.$ing->unit,
            'harga_beli' => 'Rp '.number_format($ing->product->price_buy ?? 0, 0, ',', '.'),
            'biaya' => 'Rp '.number_format(($ing->product->price_buy ?? 0) * ($ing->quantity_per_batch / $recipe->batch_size), 0, ',', '.'),
        ])->toArray();

        $result = [
            'status' => 'success',
            'data' => [
                'produk' => $product->name,
                'qty' => $qty,
                'hpp_per_unit' => 'Rp '.number_format($hpp, 0, ',', '.'),
                'hpp_total' => 'Rp '.number_format($total, 0, ',', '.'),
                'harga_jual' => 'Rp '.number_format($product->price_sell ?? 0, 0, ',', '.'),
                'breakdown' => $breakdown,
            ],
        ];

        if ($margin !== null) {
            $result['data']['margin_per_unit'] = 'Rp '.number_format($margin, 0, ',', '.');
            $result['data']['margin_persen'] = round($marginPct, 1).'%';
            $result['data']['status_margin'] = $margin >= 0 ? 'UNTUNG' : 'RUGI';
        }

        return $result;
    }

    public function produceWithRecipe(array $args): array
    {
        $product = Product::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['product_name']}%")
            ->first();

        if (! $product) {
            return ['status' => 'not_found', 'message' => "Produk \"{$args['product_name']}\" tidak ditemukan."];
        }

        $recipe = Recipe::where('tenant_id', $this->tenantId)
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->with(['ingredients.product'])
            ->first();

        if (! $recipe) {
            return [
                'status' => 'not_found',
                'message' => "Produk **{$product->name}** belum memiliki resep. Buat resep dulu dengan `create_recipe`.",
            ];
        }

        $qty = (float) $args['quantity'];
        $dryRun = $args['dry_run'] ?? false;

        // Tentukan gudang
        $warehouse = null;
        if (! empty($args['warehouse'])) {
            $warehouse = Warehouse::where('tenant_id', $this->tenantId)
                ->where('name', 'like', "%{$args['warehouse']}%")
                ->first();
            if (! $warehouse) {
                return ['status' => 'error', 'message' => "Gudang \"{$args['warehouse']}\" tidak ditemukan."];
            }
        } else {
            $warehouse = Warehouse::where('tenant_id', $this->tenantId)->where('is_active', true)->first();
            if (! $warehouse) {
                return ['status' => 'error', 'message' => 'Tidak ada gudang aktif. Buat gudang terlebih dahulu.'];
            }
        }

        // Hitung kebutuhan bahan baku dan cek stok
        $needs = [];
        $shortage = [];

        foreach ($recipe->ingredients as $ingredient) {
            $qtyNeeded = ($ingredient->quantity_per_batch / $recipe->batch_size) * $qty;

            $stock = ProductStock::where('product_id', $ingredient->product_id)
                ->where('warehouse_id', $warehouse->id)
                ->first();

            $currentStock = $stock?->quantity ?? 0;
            $needs[] = [
                'ingredient' => $ingredient,
                'product' => $ingredient->product,
                'qty_needed' => $qtyNeeded,
                'current_stock' => $currentStock,
                'stock_record' => $stock,
            ];

            if ($currentStock < $qtyNeeded) {
                $shortage[] = [
                    'bahan' => $ingredient->product->name,
                    'dibutuhkan' => round($qtyNeeded, 3).' '.$ingredient->unit,
                    'tersedia' => $currentStock.' '.$ingredient->unit,
                    'kurang' => round($qtyNeeded - $currentStock, 3).' '.$ingredient->unit,
                ];
            }
        }

        // Jika stok tidak cukup, tolak (atomicity — tidak ada yang berubah)
        if (! empty($shortage)) {
            $shortageList = collect($shortage)->map(fn ($s) => "- **{$s['bahan']}**: butuh {$s['dibutuhkan']}, tersedia {$s['tersedia']}, kurang **{$s['kurang']}**"
            )->implode("\n");

            return [
                'status' => 'error',
                'message' => "Stok bahan baku tidak mencukupi untuk produksi **{$qty} {$product->unit} {$product->name}**:\n\n{$shortageList}",
                'shortage' => $shortage,
            ];
        }

        // Dry run — hanya laporan, tidak ubah stok
        if ($dryRun) {
            $checkList = collect($needs)->map(fn ($n) => "- **{$n['product']->name}**: butuh ".round($n['qty_needed'], 3)." {$n['ingredient']->unit}, tersedia {$n['current_stock']} ✅"
            )->implode("\n");

            return [
                'status' => 'success',
                'message' => "✅ Stok bahan baku **cukup** untuk produksi **{$qty} {$product->unit} {$product->name}**:\n\n{$checkList}\n\n"
                    .'_Dry run — stok tidak diubah._',
            ];
        }

        // Eksekusi produksi dalam satu transaksi DB (atomicity)
        return DB::transaction(function () use ($product, $qty, $warehouse, $needs) {
            $reference = 'PROD-'.strtoupper(Str::random(6));

            // Deduct stok setiap bahan baku
            foreach ($needs as $n) {
                $stock = $n['stock_record'];
                if (! $stock) {
                    // Seharusnya tidak terjadi karena sudah dicek, tapi guard tetap ada
                    throw new \RuntimeException("Stok {$n['product']->name} tidak ditemukan saat eksekusi.");
                }

                $before = $stock->quantity;
                $after = $before - $n['qty_needed'];
                $stock->update(['quantity' => $after]);

                StockMovement::create([
                    'tenant_id' => $this->tenantId,
                    'product_id' => $n['ingredient']->product_id,
                    'warehouse_id' => $warehouse->id,
                    'user_id' => $this->userId,
                    'type' => 'out',
                    'quantity' => $n['qty_needed'],
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'reference' => $reference,
                    'notes' => "Produksi {$qty} {$product->unit} {$product->name}",
                ]);
            }

            // Tambah stok produk jadi
            $finishedStock = ProductStock::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                ['quantity' => 0]
            );

            $beforeFinished = $finishedStock->quantity;
            $finishedStock->increment('quantity', $qty);

            StockMovement::create([
                'tenant_id' => $this->tenantId,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'user_id' => $this->userId,
                'type' => 'in',
                'quantity' => $qty,
                'quantity_before' => $beforeFinished,
                'quantity_after' => $beforeFinished + $qty,
                'reference' => $reference,
                'notes' => "Hasil produksi (ref: {$reference})",
            ]);

            // Hitung biaya produksi
            $materialCost = collect($needs)->sum(fn ($n) => ($n['product']->price_buy ?? 0) * $n['qty_needed']
            );

            $ingDeducted = collect($needs)->map(fn ($n) => "- **{$n['product']->name}**: -{$n['qty_needed']} {$n['ingredient']->unit} (sisa: ".($n['current_stock'] - $n['qty_needed']).')'
            )->implode("\n");

            return [
                'status' => 'success',
                'message' => "✅ Produksi **{$qty} {$product->unit} {$product->name}** berhasil!\n\n"
                    ."**Bahan baku yang digunakan:**\n{$ingDeducted}\n\n"
                    ."**Stok {$product->name}** sekarang: **".($beforeFinished + $qty)." {$product->unit}**\n"
                    .'Biaya bahan baku: **Rp '.number_format($materialCost, 0, ',', '.')."**\n"
                    ."Referensi: `{$reference}`",
                'data' => [
                    'reference' => $reference,
                    'product' => $product->name,
                    'qty_produced' => $qty,
                    'material_cost' => $materialCost,
                    'stock_after' => $beforeFinished + $qty,
                ],
            ];
        });
    }
}
