<?php

namespace App\Services\ERP;

use App\Models\Product;

class BulkTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'bulk_update_products',
                'description' => 'Update massal produk sekaligus. Gunakan untuk: '
                    . '"naikkan harga semua produk kategori minuman 10%", '
                    . '"nonaktifkan semua produk stok 0", '
                    . '"turunkan harga semua produk 5%", '
                    . '"aktifkan semua produk kategori makanan", '
                    . '"set harga minimum stok semua produk jadi 5".',
                'parameters' => [
                    'type'       => 'object',
                    'properties' => [
                        'action'         => [
                            'type'        => 'string',
                            'description' => 'Aksi: price_increase (naik %), price_decrease (turun %), deactivate_zero_stock, activate_all, set_stock_min',
                        ],
                        'value'          => ['type' => 'number', 'description' => 'Nilai: persentase untuk price_increase/decrease, atau angka untuk set_stock_min'],
                        'category_filter'=> ['type' => 'string', 'description' => 'Filter kategori produk (opsional, kosong = semua)'],
                        'dry_run'        => ['type' => 'boolean', 'description' => 'true = preview saja tanpa eksekusi (default: false)'],
                    ],
                    'required' => ['action'],
                ],
            ],
        ];
    }

    public function bulkUpdateProducts(array $args): array
    {
        $action   = $args['action'];
        $value    = (float) ($args['value'] ?? 0);
        $category = $args['category_filter'] ?? null;
        $dryRun   = (bool) ($args['dry_run'] ?? false);

        $query = Product::where('tenant_id', $this->tenantId)->where('is_active', true);
        if ($category) {
            $query->where('category', 'like', "%{$category}%");
        }

        $products = $query->get();
        if ($products->isEmpty()) {
            return ['status' => 'error', 'message' => 'Tidak ada produk yang sesuai filter.'];
        }

        $preview = [];
        $updated = 0;

        foreach ($products as $product) {
            $changes = [];

            switch ($action) {
                case 'price_increase':
                    $newPrice = round($product->price_sell * (1 + $value / 100));
                    $changes = ['price_sell' => $newPrice];
                    $preview[] = ['produk' => $product->name, 'harga_lama' => 'Rp ' . number_format($product->price_sell, 0, ',', '.'), 'harga_baru' => 'Rp ' . number_format($newPrice, 0, ',', '.')];
                    break;

                case 'price_decrease':
                    $newPrice = round($product->price_sell * (1 - $value / 100));
                    $changes = ['price_sell' => max(0, $newPrice)];
                    $preview[] = ['produk' => $product->name, 'harga_lama' => 'Rp ' . number_format($product->price_sell, 0, ',', '.'), 'harga_baru' => 'Rp ' . number_format($newPrice, 0, ',', '.')];
                    break;

                case 'deactivate_zero_stock':
                    $stock = $product->stocks()->sum('quantity');
                    if ($stock <= 0) {
                        $changes = ['is_active' => false];
                        $preview[] = ['produk' => $product->name, 'aksi' => 'Dinonaktifkan (stok 0)'];
                    }
                    break;

                case 'activate_all':
                    $changes = ['is_active' => true];
                    $preview[] = ['produk' => $product->name, 'aksi' => 'Diaktifkan'];
                    break;

                case 'set_stock_min':
                    $changes = ['stock_min' => (int) $value];
                    $preview[] = ['produk' => $product->name, 'stok_min_baru' => (int) $value];
                    break;
            }

            if (!empty($changes) && !$dryRun) {
                $product->update($changes);
                $updated++;
            } elseif (!empty($changes)) {
                $updated++;
            }
        }

        $label = match ($action) {
            'price_increase'       => "Harga dinaikkan {$value}%",
            'price_decrease'       => "Harga diturunkan {$value}%",
            'deactivate_zero_stock'=> "Produk stok 0 dinonaktifkan",
            'activate_all'         => "Semua produk diaktifkan",
            'set_stock_min'        => "Stok minimum diset ke {$value}",
            default                => $action,
        };

        return [
            'status'   => 'success',
            'dry_run'  => $dryRun,
            'message'  => $dryRun
                ? "Preview: {$updated} produk akan diupdate ({$label})."
                : "{$updated} produk berhasil diupdate ({$label}).",
            'total'    => $updated,
            'preview'  => array_slice($preview, 0, 20),
        ];
    }
}
