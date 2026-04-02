<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Warehouse;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DemoProductSeeder extends Seeder
{
    public function run(): void
    {
        // Seed untuk semua tenant yang ada
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) { 
            // Pastikan ada warehouse
            $warehouse = Warehouse::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'Gudang Utama'],
                ['code' => 'GDG-01', 'address' => 'Gudang Utama', 'is_active' => true]
            );

            $products = [
                // Minuman
                ['name' => 'Kopi Hitam', 'sku' => 'KOP-001', 'category' => 'Minuman', 'price_buy' => 3000, 'price_sell' => 8000, 'stock' => 100],
                ['name' => 'Kopi Susu', 'sku' => 'KOP-002', 'category' => 'Minuman', 'price_buy' => 5000, 'price_sell' => 12000, 'stock' => 80],
                ['name' => 'Teh Manis', 'sku' => 'TEH-001', 'category' => 'Minuman', 'price_buy' => 2000, 'price_sell' => 6000, 'stock' => 120],
                ['name' => 'Es Jeruk', 'sku' => 'JRK-001', 'category' => 'Minuman', 'price_buy' => 3000, 'price_sell' => 8000, 'stock' => 60],
                ['name' => 'Air Mineral 600ml', 'sku' => 'AIR-001', 'category' => 'Minuman', 'price_buy' => 2500, 'price_sell' => 5000, 'stock' => 200],
                // Makanan
                ['name' => 'Nasi Goreng', 'sku' => 'NSG-001', 'category' => 'Makanan', 'price_buy' => 8000, 'price_sell' => 18000, 'stock' => 50],
                ['name' => 'Mie Goreng', 'sku' => 'MIG-001', 'category' => 'Makanan', 'price_buy' => 6000, 'price_sell' => 15000, 'stock' => 50],
                ['name' => 'Roti Bakar', 'sku' => 'RTB-001', 'category' => 'Makanan', 'price_buy' => 5000, 'price_sell' => 12000, 'stock' => 30],
                ['name' => 'Pisang Goreng', 'sku' => 'PSG-001', 'category' => 'Makanan', 'price_buy' => 3000, 'price_sell' => 8000, 'stock' => 40],
                ['name' => 'Kentang Goreng', 'sku' => 'KTG-001', 'category' => 'Makanan', 'price_buy' => 5000, 'price_sell' => 13000, 'stock' => 35],
                // Snack
                ['name' => 'Keripik Singkong', 'sku' => 'KRP-001', 'category' => 'Snack', 'price_buy' => 4000, 'price_sell' => 8000, 'stock' => 60],
                ['name' => 'Biskuit Coklat', 'sku' => 'BSK-001', 'category' => 'Snack', 'price_buy' => 5000, 'price_sell' => 10000, 'stock' => 45],
                // Rokok
                ['name' => 'Rokok Surya 12', 'sku' => 'RKK-001', 'category' => 'Rokok', 'price_buy' => 18000, 'price_sell' => 22000, 'stock' => 30],
                ['name' => 'Rokok Gudang Garam', 'sku' => 'RKK-002', 'category' => 'Rokok', 'price_buy' => 20000, 'price_sell' => 24000, 'stock' => 25],
            ];

            foreach ($products as $data) {
                $stock = $data['stock'];
                unset($data['stock']);

                $product = Product::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'sku' => $data['sku']],
                    array_merge($data, [
                        'tenant_id' => $tenant->id,
                        'unit' => 'pcs',
                        'stock_min' => 5,
                        'is_active' => true,
                    ])
                );

                ProductStock::updateOrCreate(
                    ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                    ['quantity' => $stock]
                );
            }
        }

        $this->command->info('Demo products seeded successfully.');
    }
}
