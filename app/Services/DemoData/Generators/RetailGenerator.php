<?php

namespace App\Services\DemoData\Generators;

use App\Services\DemoData\BaseIndustryGenerator;
use App\Services\DemoData\CoreDataContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RetailGenerator extends BaseIndustryGenerator
{
    public function getIndustryName(): string
    {
        return 'retail';
    }

    public function generate(CoreDataContext $ctx): array
    {
        $tenantId      = $ctx->tenantId;
        $warehouseId   = $ctx->warehouseId;
        $supplierIds   = $ctx->supplierIds;
        $recordsCreated = 0;
        $generatedData  = [];

        // ── 1. Products (20 fashion / consumer goods) ──────────────────────
        try {
            $productIds = $this->seedProducts($tenantId, $warehouseId);
            $recordsCreated += count($productIds);
            $generatedData['products'] = count($productIds);
        } catch (\Throwable $e) {
            $this->logWarning('RetailGenerator: failed to seed products', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $productIds = $ctx->productIds; // fall back to core products
            $generatedData['products'] = 0;
        }

        // ── 2. Customers (10 retail customers) ─────────────────────────────
        try {
            $customerIds = $this->seedCustomers($tenantId);
            $recordsCreated += count($customerIds);
            $generatedData['customers'] = count($customerIds);
        } catch (\Throwable $e) {
            $this->logWarning('RetailGenerator: failed to seed customers', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $customerIds = $ctx->customerIds;
            $generatedData['customers'] = 0;
        }

        // ── 3. Sales Orders (10 completed transactions) ────────────────────
        try {
            $salesCount = $this->seedSalesOrders($tenantId, $customerIds, $productIds);
            $recordsCreated += $salesCount;
            $generatedData['sales_orders'] = $salesCount;
        } catch (\Throwable $e) {
            $this->logWarning('RetailGenerator: failed to seed sales orders', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['sales_orders'] = 0;
        }

        // ── 4. Loyalty Program (1 active) ──────────────────────────────────
        try {
            $loyaltyCount = $this->seedLoyaltyProgram($tenantId);
            $recordsCreated += $loyaltyCount;
            $generatedData['loyalty_programs'] = $loyaltyCount;
        } catch (\Throwable $e) {
            $this->logWarning('RetailGenerator: failed to seed loyalty program', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['loyalty_programs'] = 0;
        }

        // ── 5. Price List (1 list with ≥3 items) ───────────────────────────
        try {
            $priceListCount = $this->seedPriceList($tenantId, $productIds);
            $recordsCreated += $priceListCount;
            $generatedData['price_lists'] = $priceListCount;
        } catch (\Throwable $e) {
            $this->logWarning('RetailGenerator: failed to seed price list', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['price_lists'] = 0;
        }

        // ── 6. Purchase Orders (stock replenishment) ───────────────────────
        try {
            $poCount = $this->seedPurchaseOrders($tenantId, $supplierIds, $productIds, $warehouseId);
            $recordsCreated += $poCount;
            $generatedData['purchase_orders'] = $poCount;
        } catch (\Throwable $e) {
            $this->logWarning('RetailGenerator: failed to seed purchase orders', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['purchase_orders'] = 0;
        }

        return [
            'records_created' => $recordsCreated,
            'generated_data'  => $generatedData,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Products — 20 fashion / consumer goods
    // ─────────────────────────────────────────────────────────────

    private function seedProducts(int $tenantId, int $warehouseId): array
    {
        $products = [
            // Fashion — Pakaian
            ['name' => 'Kemeja Batik Pria Lengan Panjang',   'sku' => 'RTL-FSH-001', 'category' => 'Fashion',        'unit' => 'pcs', 'price_buy' => 85000,   'price_sell' => 175000,  'stock_min' => 20, 'qty' => 80],
            ['name' => 'Kaos Polos Cotton Combed 30s',       'sku' => 'RTL-FSH-002', 'category' => 'Fashion',        'unit' => 'pcs', 'price_buy' => 35000,   'price_sell' => 75000,   'stock_min' => 50, 'qty' => 200],
            ['name' => 'Celana Jeans Slim Fit Pria',         'sku' => 'RTL-FSH-003', 'category' => 'Fashion',        'unit' => 'pcs', 'price_buy' => 120000,  'price_sell' => 250000,  'stock_min' => 20, 'qty' => 60],
            ['name' => 'Dress Casual Wanita Motif Bunga',    'sku' => 'RTL-FSH-004', 'category' => 'Fashion',        'unit' => 'pcs', 'price_buy' => 95000,   'price_sell' => 199000,  'stock_min' => 15, 'qty' => 50],
            ['name' => 'Jaket Hoodie Fleece Unisex',         'sku' => 'RTL-FSH-005', 'category' => 'Fashion',        'unit' => 'pcs', 'price_buy' => 110000,  'price_sell' => 229000,  'stock_min' => 20, 'qty' => 70],
            ['name' => 'Sepatu Sneakers Pria Casual',        'sku' => 'RTL-FSH-006', 'category' => 'Sepatu',         'unit' => 'pcs', 'price_buy' => 180000,  'price_sell' => 375000,  'stock_min' => 10, 'qty' => 40],
            ['name' => 'Sandal Wanita Flat Kulit Sintetis',  'sku' => 'RTL-FSH-007', 'category' => 'Sepatu',         'unit' => 'pcs', 'price_buy' => 65000,   'price_sell' => 135000,  'stock_min' => 15, 'qty' => 55],
            ['name' => 'Tas Ransel Laptop 15 inch',          'sku' => 'RTL-FSH-008', 'category' => 'Tas',            'unit' => 'pcs', 'price_buy' => 145000,  'price_sell' => 299000,  'stock_min' => 10, 'qty' => 35],
            ['name' => 'Dompet Kulit Pria Slim',             'sku' => 'RTL-FSH-009', 'category' => 'Aksesoris',      'unit' => 'pcs', 'price_buy' => 55000,   'price_sell' => 115000,  'stock_min' => 20, 'qty' => 75],
            ['name' => 'Topi Baseball Cap Polos',            'sku' => 'RTL-FSH-010', 'category' => 'Aksesoris',      'unit' => 'pcs', 'price_buy' => 30000,   'price_sell' => 65000,   'stock_min' => 30, 'qty' => 100],
            // Consumer Goods — Perawatan & Rumah Tangga
            ['name' => 'Sabun Mandi Cair 500ml',             'sku' => 'RTL-CG-001',  'category' => 'Perawatan Diri', 'unit' => 'btl', 'price_buy' => 18000,   'price_sell' => 35000,   'stock_min' => 50, 'qty' => 200],
            ['name' => 'Shampo Anti Ketombe 340ml',          'sku' => 'RTL-CG-002',  'category' => 'Perawatan Diri', 'unit' => 'btl', 'price_buy' => 22000,   'price_sell' => 45000,   'stock_min' => 50, 'qty' => 180],
            ['name' => 'Pasta Gigi Whitening 120g',          'sku' => 'RTL-CG-003',  'category' => 'Perawatan Diri', 'unit' => 'pcs', 'price_buy' => 12000,   'price_sell' => 25000,   'stock_min' => 60, 'qty' => 250],
            ['name' => 'Pelembab Wajah SPF 30 50ml',         'sku' => 'RTL-CG-004',  'category' => 'Kecantikan',     'unit' => 'pcs', 'price_buy' => 45000,   'price_sell' => 95000,   'stock_min' => 30, 'qty' => 120],
            ['name' => 'Deodoran Roll-On 50ml',              'sku' => 'RTL-CG-005',  'category' => 'Perawatan Diri', 'unit' => 'pcs', 'price_buy' => 15000,   'price_sell' => 30000,   'stock_min' => 40, 'qty' => 160],
            ['name' => 'Deterjen Bubuk 1kg',                 'sku' => 'RTL-CG-006',  'category' => 'Rumah Tangga',   'unit' => 'pcs', 'price_buy' => 20000,   'price_sell' => 40000,   'stock_min' => 40, 'qty' => 150],
            ['name' => 'Pembersih Lantai 800ml',             'sku' => 'RTL-CG-007',  'category' => 'Rumah Tangga',   'unit' => 'btl', 'price_buy' => 16000,   'price_sell' => 32000,   'stock_min' => 30, 'qty' => 120],
            ['name' => 'Tisu Basah 50 lembar',               'sku' => 'RTL-CG-008',  'category' => 'Rumah Tangga',   'unit' => 'pcs', 'price_buy' => 8000,    'price_sell' => 18000,   'stock_min' => 60, 'qty' => 300],
            ['name' => 'Minyak Goreng 2 Liter',              'sku' => 'RTL-CG-009',  'category' => 'Sembako',        'unit' => 'btl', 'price_buy' => 28000,   'price_sell' => 38000,   'stock_min' => 30, 'qty' => 100],
            ['name' => 'Gula Pasir 1kg',                     'sku' => 'RTL-CG-010',  'category' => 'Sembako',        'unit' => 'kg',  'price_buy' => 13000,   'price_sell' => 18000,   'stock_min' => 50, 'qty' => 200],
        ];

        $productIds = [];
        $stockRows  = [];

        foreach ($products as $p) {
            $existing = DB::table('products')
                ->where('tenant_id', $tenantId)
                ->where('sku', $p['sku'])
                ->first();

            if ($existing) {
                $productIds[] = (int) $existing->id;
                continue;
            }

            $id = DB::table('products')->insertGetId([
                'tenant_id'  => $tenantId,
                'name'       => $p['name'],
                'sku'        => $p['sku'],
                'category'   => $p['category'],
                'unit'       => $p['unit'],
                'price_buy'  => $p['price_buy'],
                'price_sell' => $p['price_sell'],
                'stock_min'  => $p['stock_min'],
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $productIds[] = (int) $id;

            $stockRows[] = [
                'product_id'   => $id,
                'warehouse_id' => $warehouseId,
                'quantity'     => $p['qty'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        if (!empty($stockRows)) {
            DB::table('product_stocks')->insertOrIgnore($stockRows);
        }

        return $productIds;
    }

    // ─────────────────────────────────────────────────────────────
    //  Customers — 10 retail customers
    // ─────────────────────────────────────────────────────────────

    private function seedCustomers(int $tenantId): array
    {
        $customers = [
            ['name' => 'Rina Kusuma',          'email' => 'rina.kusuma@demo-retail.com',       'phone' => '0812-1111001', 'company' => null,                       'address' => 'Jl. Sudirman No. 10, Jakarta',    'credit_limit' => 5000000],
            ['name' => 'Hendra Gunawan',       'email' => 'hendra.gunawan@demo-retail.com',    'phone' => '0813-2222002', 'company' => null,                       'address' => 'Jl. Gatot Subroto No. 5, Jakarta', 'credit_limit' => 3000000],
            ['name' => 'Sari Dewi',            'email' => 'sari.dewi@demo-retail.com',         'phone' => '0814-3333003', 'company' => null,                       'address' => 'Jl. Thamrin No. 20, Jakarta',     'credit_limit' => 2000000],
            ['name' => 'Agus Setiawan',        'email' => 'agus.setiawan@demo-retail.com',     'phone' => '0815-4444004', 'company' => null,                       'address' => 'Jl. Kebon Jeruk No. 8, Jakarta',  'credit_limit' => 4000000],
            ['name' => 'Fitri Handayani',      'email' => 'fitri.handayani@demo-retail.com',   'phone' => '0816-5555005', 'company' => null,                       'address' => 'Jl. Mangga Dua No. 3, Jakarta',   'credit_limit' => 2500000],
            ['name' => 'Toko Busana Indah',    'email' => 'order@demo-busanaindah.com',        'phone' => '021-6666006',  'company' => 'Toko Busana Indah',        'address' => 'Jl. Pasar Baru No. 15, Jakarta',  'credit_limit' => 20000000],
            ['name' => 'CV Moda Fashion',      'email' => 'purchase@demo-modafashion.com',     'phone' => '022-7777007',  'company' => 'CV Moda Fashion',          'address' => 'Jl. Braga No. 22, Bandung',       'credit_limit' => 30000000],
            ['name' => 'Toko Serba Ada Jaya',  'email' => 'admin@demo-serbajaya.com',          'phone' => '031-8888008',  'company' => 'Toko Serba Ada Jaya',      'address' => 'Jl. Pemuda No. 7, Surabaya',      'credit_limit' => 15000000],
            ['name' => 'Yuni Pratiwi',         'email' => 'yuni.pratiwi@demo-retail.com',      'phone' => '0817-9999009', 'company' => null,                       'address' => 'Jl. Diponegoro No. 11, Semarang', 'credit_limit' => 1500000],
            ['name' => 'PT Retail Nusantara',  'email' => 'procurement@demo-retailnus.co.id',  'phone' => '021-1010010',  'company' => 'PT Retail Nusantara',      'address' => 'Jl. HR Rasuna Said No. 1, Jakarta','credit_limit' => 50000000],
        ];

        $customerIds = [];

        foreach ($customers as $c) {
            $existing = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->where('email', $c['email'])
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                $customerIds[] = (int) $existing->id;
                continue;
            }

            $id = DB::table('customers')->insertGetId([
                'tenant_id'    => $tenantId,
                'name'         => $c['name'],
                'email'        => $c['email'],
                'phone'        => $c['phone'],
                'company'      => $c['company'],
                'address'      => $c['address'],
                'credit_limit' => $c['credit_limit'],
                'is_active'    => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            $customerIds[] = (int) $id;
        }

        return $customerIds;
    }

    // ─────────────────────────────────────────────────────────────
    //  Sales Orders — 10 completed transactions
    // ─────────────────────────────────────────────────────────────

    private function seedSalesOrders(int $tenantId, array $customerIds, array $productIds): int
    {
        // sales_orders requires user_id
        $userId = DB::table('users')->where('tenant_id', $tenantId)->value('id');
        if (!$userId) {
            $this->logWarning('RetailGenerator: no user found for tenant, skipping sales orders', [
                'tenant_id' => $tenantId,
            ]);
            return 0;
        }

        $count = 0;

        for ($i = 1; $i <= 10; $i++) {
            $soNumber = 'RTL-SO-' . $tenantId . '-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT);

            $exists = DB::table('sales_orders')
                ->where('tenant_id', $tenantId)
                ->where('number', $soNumber)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                continue;
            }

            $customerId = $customerIds[($i - 1) % count($customerIds)];
            $orderDate  = Carbon::now()->subDays(rand(5, 90))->format('Y-m-d');

            // Pick 2–3 products for this order
            $orderProducts = array_slice($productIds, ($i - 1) % max(1, count($productIds) - 2), 2);
            if (empty($orderProducts)) {
                $orderProducts = [$productIds[0]];
            }

            $subtotal = 0;
            $items    = [];

            foreach ($orderProducts as $productId) {
                $product = DB::table('products')->where('id', $productId)->first();
                if (!$product) {
                    continue;
                }
                $qty      = rand(1, 5);
                $price    = (float) $product->price_sell;
                $total    = $qty * $price;
                $subtotal += $total;

                $items[] = [
                    'product_id' => $productId,
                    'quantity'   => $qty,
                    'price'      => $price,
                    'discount'   => 0,
                    'total'      => $total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (empty($items)) {
                continue;
            }

            $soId = DB::table('sales_orders')->insertGetId([
                'tenant_id'   => $tenantId,
                'customer_id' => $customerId,
                'user_id'     => $userId,
                'number'      => $soNumber,
                'status'      => 'delivered',
                'date'        => $orderDate,
                'subtotal'    => $subtotal,
                'discount'    => 0,
                'tax'         => 0,
                'total'       => $subtotal,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            foreach ($items as &$item) {
                $item['sales_order_id'] = $soId;
            }
            unset($item);

            $this->bulkInsert('sales_order_items', $items);
            $count++;
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────────
    //  Loyalty Program — 1 active program
    // ─────────────────────────────────────────────────────────────

    private function seedLoyaltyProgram(int $tenantId): int
    {
        $exists = DB::table('loyalty_programs')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->exists();

        if ($exists) {
            return 0;
        }

        DB::table('loyalty_programs')->insert([
            'tenant_id'         => $tenantId,
            'name'              => 'Program Loyalitas Retail',
            'points_per_idr'    => 0.001,   // 1 point per Rp 1.000
            'idr_per_point'     => 10,       // 1 point = Rp 10
            'min_redeem_points' => 500,
            'expiry_days'       => 365,
            'is_active'         => true,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return 1;
    }

    // ─────────────────────────────────────────────────────────────
    //  Price List — 1 list with ≥3 items
    // ─────────────────────────────────────────────────────────────

    private function seedPriceList(int $tenantId, array $productIds): int
    {
        $exists = DB::table('price_lists')
            ->where('tenant_id', $tenantId)
            ->where('code', 'RTL-PL-001')
            ->exists();

        if ($exists) {
            return 0;
        }

        $priceListId = DB::table('price_lists')->insertGetId([
            'tenant_id'   => $tenantId,
            'name'        => 'Harga Retail Reguler',
            'code'        => 'RTL-PL-001',
            'type'        => 'tier',
            'description' => 'Daftar harga standar untuk pelanggan retail reguler',
            'valid_from'  => Carbon::now()->startOfYear()->format('Y-m-d'),
            'valid_until' => Carbon::now()->endOfYear()->format('Y-m-d'),
            'is_active'   => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Add at least 3 items (use first 5 products or all if fewer)
        $itemProducts = array_slice($productIds, 0, min(5, count($productIds)));
        $itemRows     = [];

        foreach ($itemProducts as $productId) {
            $product = DB::table('products')->where('id', $productId)->first();
            if (!$product) {
                continue;
            }

            $itemRows[] = [
                'price_list_id'    => $priceListId,
                'product_id'       => $productId,
                'price'            => (float) $product->price_sell,
                'discount_percent' => 5.00,
                'min_qty'          => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        if (!empty($itemRows)) {
            $this->bulkInsert('price_list_items', $itemRows);
        }

        return 1;
    }

    // ─────────────────────────────────────────────────────────────
    //  Purchase Orders — stock replenishment (3 POs)
    // ─────────────────────────────────────────────────────────────

    private function seedPurchaseOrders(
        int $tenantId,
        array $supplierIds,
        array $productIds,
        int $warehouseId
    ): int {
        $userId = DB::table('users')->where('tenant_id', $tenantId)->value('id');
        if (!$userId || empty($supplierIds) || empty($productIds)) {
            $this->logWarning('RetailGenerator: missing user, suppliers or products for purchase orders', [
                'tenant_id' => $tenantId,
            ]);
            return 0;
        }

        $count = 0;

        $poDefinitions = [
            ['number' => 'RTL-PO-' . $tenantId . '-001', 'products' => array_slice($productIds, 0, 4)],
            ['number' => 'RTL-PO-' . $tenantId . '-002', 'products' => array_slice($productIds, 4, 4)],
            ['number' => 'RTL-PO-' . $tenantId . '-003', 'products' => array_slice($productIds, 8, 4)],
        ];

        foreach ($poDefinitions as $idx => $def) {
            $exists = DB::table('purchase_orders')
                ->where('tenant_id', $tenantId)
                ->where('number', $def['number'])
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                continue;
            }

            $supplierId = $supplierIds[$idx % count($supplierIds)];
            $poDate     = Carbon::now()->subDays(rand(10, 60))->format('Y-m-d');
            $subtotal   = 0;
            $items      = [];

            foreach ($def['products'] as $productId) {
                $product = DB::table('products')->where('id', $productId)->first();
                if (!$product) {
                    continue;
                }
                $qty      = rand(20, 100);
                $price    = (float) $product->price_buy;
                $total    = $qty * $price;
                $subtotal += $total;

                $items[] = [
                    'product_id'        => $productId,
                    'quantity_ordered'  => $qty,
                    'quantity_received' => 0,
                    'price'             => $price,
                    'total'             => $total,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }

            if (empty($items)) {
                continue;
            }

            $poId = DB::table('purchase_orders')->insertGetId([
                'tenant_id'     => $tenantId,
                'supplier_id'   => $supplierId,
                'user_id'       => $userId,
                'warehouse_id'  => $warehouseId,
                'number'        => $def['number'],
                'status'        => 'received',
                'date'          => $poDate,
                'expected_date' => Carbon::parse($poDate)->addDays(14)->format('Y-m-d'),
                'subtotal'      => $subtotal,
                'discount'      => 0,
                'tax'           => 0,
                'total'         => $subtotal,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            foreach ($items as &$item) {
                $item['purchase_order_id'] = $poId;
            }
            unset($item);

            $this->bulkInsert('purchase_order_items', $items);
            $count++;
        }

        return $count;
    }
}
