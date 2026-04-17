<?php

namespace App\Services\DemoData\Generators;

use App\Services\DemoData\BaseIndustryGenerator;
use App\Services\DemoData\CoreDataContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RestaurantGenerator extends BaseIndustryGenerator
{
    public function getIndustryName(): string
    {
        return 'restaurant';
    }

    public function generate(CoreDataContext $ctx): array
    {
        $tenantId       = $ctx->tenantId;
        $recordsCreated = 0;
        $generatedData  = [];

        // 1. Restaurant Menu (container)
        $menuId = null;
        try {
            $menuId = $this->seedRestaurantMenu($tenantId);
            $recordsCreated += 1;
            $generatedData['menus'] = 1;
        } catch (\Throwable $e) {
            $this->logWarning('RestaurantGenerator: failed to seed restaurant menu', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['menus'] = 0;
        }

        // 2. Menu Items (15 items: makanan utama, minuman, dessert)
        $menuItemIds = [];
        try {
            $menuItemIds = $this->seedMenuItems($tenantId, $menuId);
            $recordsCreated += count($menuItemIds);
            $generatedData['menu_items'] = count($menuItemIds);
        } catch (\Throwable $e) {
            $this->logWarning('RestaurantGenerator: failed to seed menu items', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['menu_items'] = 0;
        }

        // 3. Restaurant Tables (8 tables)
        try {
            $tableCount = $this->seedRestaurantTables($tenantId);
            $recordsCreated += $tableCount;
            $generatedData['tables'] = $tableCount;
        } catch (\Throwable $e) {
            $this->logWarning('RestaurantGenerator: failed to seed restaurant tables', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['tables'] = 0;
        }

        // 4. F&B Orders (10 completed orders)
        try {
            $orderCount = $this->seedFbOrders($tenantId, $menuItemIds);
            $recordsCreated += $orderCount;
            $generatedData['fb_orders'] = $orderCount;
        } catch (\Throwable $e) {
            $this->logWarning('RestaurantGenerator: failed to seed F&B orders', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['fb_orders'] = 0;
        }

        // 5. Kitchen Inventory (bahan baku dapur via fb_supplies)
        try {
            $supplyCount = $this->seedKitchenInventory($tenantId);
            $recordsCreated += $supplyCount;
            $generatedData['kitchen_supplies'] = $supplyCount;
        } catch (\Throwable $e) {
            $this->logWarning('RestaurantGenerator: failed to seed kitchen inventory', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['kitchen_supplies'] = 0;
        }

        // 6. Employees (3: kasir, pelayan, koki)
        try {
            $empCount = $this->seedEmployees($tenantId);
            $recordsCreated += $empCount;
            $generatedData['employees'] = $empCount;
        } catch (\Throwable $e) {
            $this->logWarning('RestaurantGenerator: failed to seed employees', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['employees'] = 0;
        }

        return [
            'records_created' => $recordsCreated,
            'generated_data'  => $generatedData,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Restaurant Menu — 1 all-day menu container
    // ─────────────────────────────────────────────────────────────

    private function seedRestaurantMenu(int $tenantId): int
    {
        $existing = DB::table('restaurant_menus')
            ->where('tenant_id', $tenantId)
            ->where('name', 'Menu Utama Restoran')
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            return (int) $existing->id;
        }

        return (int) DB::table('restaurant_menus')->insertGetId([
            'tenant_id'       => $tenantId,
            'name'            => 'Menu Utama Restoran',
            'description'     => 'Menu lengkap restoran mencakup makanan utama, minuman, dan dessert',
            'type'            => 'all_day',
            'available_from'  => '07:00:00',
            'available_until' => '22:00:00',
            'is_active'       => true,
            'display_order'   => 1,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Menu Items — 15 items (5 makanan utama, 5 minuman, 5 dessert)
    // ─────────────────────────────────────────────────────────────

    private function seedMenuItems(int $tenantId, ?int $menuId): array
    {
        if (!$menuId) {
            $this->logWarning('RestaurantGenerator: no menu_id available, skipping menu items', [
                'tenant_id' => $tenantId,
            ]);
            return [];
        }

        $items = [
            // Makanan Utama (5 items)
            ['name' => 'Nasi Goreng Spesial',    'category' => 'Makanan Utama', 'price' => 35000, 'cost' => 12000, 'prep_time' => 15, 'order' => 1],
            ['name' => 'Mie Goreng Seafood',      'category' => 'Makanan Utama', 'price' => 40000, 'cost' => 15000, 'prep_time' => 15, 'order' => 2],
            ['name' => 'Ayam Bakar Madu',         'category' => 'Makanan Utama', 'price' => 45000, 'cost' => 18000, 'prep_time' => 20, 'order' => 3],
            ['name' => 'Soto Ayam Kampung',       'category' => 'Makanan Utama', 'price' => 30000, 'cost' => 10000, 'prep_time' => 10, 'order' => 4],
            ['name' => 'Steak Daging Sapi Lokal', 'category' => 'Makanan Utama', 'price' => 85000, 'cost' => 40000, 'prep_time' => 25, 'order' => 5],
            // Minuman (5 items)
            ['name' => 'Es Teh Manis',            'category' => 'Minuman',       'price' => 8000,  'cost' => 2000,  'prep_time' => 3,  'order' => 6],
            ['name' => 'Jus Alpukat',             'category' => 'Minuman',       'price' => 18000, 'cost' => 6000,  'prep_time' => 5,  'order' => 7],
            ['name' => 'Kopi Susu Kekinian',      'category' => 'Minuman',       'price' => 22000, 'cost' => 7000,  'prep_time' => 5,  'order' => 8],
            ['name' => 'Air Mineral Botol',       'category' => 'Minuman',       'price' => 5000,  'cost' => 2000,  'prep_time' => 1,  'order' => 9],
            ['name' => 'Jus Jeruk Segar',         'category' => 'Minuman',       'price' => 15000, 'cost' => 5000,  'prep_time' => 5,  'order' => 10],
            // Dessert (5 items)
            ['name' => 'Es Krim Vanilla Scoop',   'category' => 'Dessert',       'price' => 20000, 'cost' => 7000,  'prep_time' => 5,  'order' => 11],
            ['name' => 'Pudding Coklat',          'category' => 'Dessert',       'price' => 15000, 'cost' => 5000,  'prep_time' => 3,  'order' => 12],
            ['name' => 'Pisang Goreng Keju',      'category' => 'Dessert',       'price' => 18000, 'cost' => 6000,  'prep_time' => 10, 'order' => 13],
            ['name' => 'Brownies Kukus',          'category' => 'Dessert',       'price' => 22000, 'cost' => 8000,  'prep_time' => 5,  'order' => 14],
            ['name' => 'Klepon Isi Gula Merah',   'category' => 'Dessert',       'price' => 12000, 'cost' => 4000,  'prep_time' => 5,  'order' => 15],
        ];

        $menuItemIds = [];

        foreach ($items as $item) {
            $existing = DB::table('menu_items')
                ->where('tenant_id', $tenantId)
                ->where('menu_id', $menuId)
                ->where('name', $item['name'])
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                $menuItemIds[] = (int) $existing->id;
                continue;
            }

            $id = DB::table('menu_items')->insertGetId([
                'tenant_id'        => $tenantId,
                'menu_id'          => $menuId,
                'category_id'      => null,
                'name'             => $item['name'],
                'description'      => 'Menu ' . $item['name'] . ' pilihan restoran',
                'price'            => $item['price'],
                'cost'             => $item['cost'],
                'category'         => $item['category'],
                'allergens'        => null,
                'dietary_info'     => null,
                'preparation_time' => $item['prep_time'],
                'is_available'     => true,
                'daily_limit'      => null,
                'sold_today'       => 0,
                'display_order'    => $item['order'],
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $menuItemIds[] = (int) $id;
        }

        return $menuItemIds;
    }

    // ─────────────────────────────────────────────────────────────
    //  Restaurant Tables — 8 tables with varying capacity & status
    // ─────────────────────────────────────────────────────────────

    private function seedRestaurantTables(int $tenantId): int
    {
        $tables = [
            ['table_number' => 1, 'capacity' => 2,  'location' => 'Indoor',  'status' => 'available'],
            ['table_number' => 2, 'capacity' => 2,  'location' => 'Indoor',  'status' => 'available'],
            ['table_number' => 3, 'capacity' => 4,  'location' => 'Indoor',  'status' => 'occupied'],
            ['table_number' => 4, 'capacity' => 4,  'location' => 'Indoor',  'status' => 'available'],
            ['table_number' => 5, 'capacity' => 6,  'location' => 'Outdoor', 'status' => 'reserved'],
            ['table_number' => 6, 'capacity' => 6,  'location' => 'Outdoor', 'status' => 'available'],
            ['table_number' => 7, 'capacity' => 8,  'location' => 'Terrace', 'status' => 'available'],
            ['table_number' => 8, 'capacity' => 10, 'location' => 'Terrace', 'status' => 'maintenance'],
        ];

        $count = 0;

        foreach ($tables as $t) {
            $existing = DB::table('restaurant_tables')
                ->where('tenant_id', $tenantId)
                ->where('table_number', $t['table_number'])
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                continue;
            }

            DB::table('restaurant_tables')->insert([
                'tenant_id'    => $tenantId,
                'table_number' => $t['table_number'],
                'capacity'     => $t['capacity'],
                'location'     => $t['location'],
                'status'       => $t['status'],
                'is_active'    => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            $count++;
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────────
    //  F&B Orders — 10 completed dine-in orders
    // ─────────────────────────────────────────────────────────────

    private function seedFbOrders(int $tenantId, array $menuItemIds): int
    {
        if (empty($menuItemIds)) {
            $this->logWarning('RestaurantGenerator: no menu items available for F&B orders', [
                'tenant_id' => $tenantId,
            ]);
            return 0;
        }

        // Resolve a user ID for created_by (required FK on fb_orders)
        $userId = DB::table('users')
            ->where('tenant_id', $tenantId)
            ->value('id');

        if (!$userId) {
            $this->logWarning('RestaurantGenerator: no user found for tenant, skipping F&B orders', [
                'tenant_id' => $tenantId,
            ]);
            return 0;
        }

        $count = 0;

        for ($i = 1; $i <= 10; $i++) {
            $orderNumber = 'RST-DIN-' . $tenantId . '-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT);

            $exists = DB::table('fb_orders')
                ->where('tenant_id', $tenantId)
                ->where('order_number', $orderNumber)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                continue;
            }

            $tableNumber = ($i % 6) + 1; // rotate through tables 1–6
            $orderedAt   = Carbon::now()->subDays(rand(1, 60))->subHours(rand(0, 8));

            // Pick 2 menu items per order (rotate through available items)
            $offset        = ($i - 1) % max(1, count($menuItemIds) - 1);
            $selectedItems = array_slice($menuItemIds, $offset, 2);
            if (empty($selectedItems)) {
                $selectedItems = [$menuItemIds[0]];
            }

            $subtotal   = 0;
            $orderItems = [];

            foreach ($selectedItems as $menuItemId) {
                $menuItem = DB::table('menu_items')->where('id', $menuItemId)->first();
                if (!$menuItem) {
                    continue;
                }
                $qty       = rand(1, 3);
                $price     = (float) $menuItem->price;
                $itemTotal = $qty * $price;
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'tenant_id'        => $tenantId,
                    'menu_item_id'     => $menuItemId,
                    'item_name'        => $menuItem->name,
                    'quantity'         => $qty,
                    'unit_price'       => $price,
                    'subtotal'         => $itemTotal,
                    'special_requests' => null,
                    'status'           => 'served',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }

            if (empty($orderItems)) {
                continue;
            }

            $taxAmount     = round($subtotal * 0.10, 2);
            $serviceCharge = round($subtotal * 0.05, 2);
            $totalAmount   = $subtotal + $taxAmount + $serviceCharge;

            $orderId = DB::table('fb_orders')->insertGetId([
                'tenant_id'            => $tenantId,
                'order_number'         => $orderNumber,
                'order_type'           => 'restaurant_dine_in',
                'guest_id'             => null,
                'reservation_id'       => null,
                'room_number'          => null,
                'table_number'         => null, // FK to users in schema — pass null
                'created_by'           => $userId,
                'server_id'            => null,
                'status'               => 'completed',
                'subtotal'             => $subtotal,
                'tax_amount'           => $taxAmount,
                'service_charge'       => $serviceCharge,
                'discount_amount'      => 0,
                'total_amount'         => $totalAmount,
                'special_instructions' => null,
                'ordered_at'           => $orderedAt,
                'confirmed_at'         => $orderedAt->copy()->addMinutes(2),
                'prepared_at'          => $orderedAt->copy()->addMinutes(15),
                'served_at'            => $orderedAt->copy()->addMinutes(20),
                'completed_at'         => $orderedAt->copy()->addMinutes(60),
                'payment_status'       => 'paid',
                'payment_method'       => ($i % 2 === 0) ? 'credit_card' : 'cash',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            foreach ($orderItems as &$oi) {
                $oi['order_id'] = $orderId;
            }
            unset($oi);

            $this->bulkInsert('fb_order_items', $orderItems);
            $count++;
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────────
    //  Kitchen Inventory — bahan baku dapur (fb_supplies)
    // ─────────────────────────────────────────────────────────────

    private function seedKitchenInventory(int $tenantId): int
    {
        $supplies = [
            ['name' => 'Beras Premium',    'unit' => 'kg',    'current_stock' => 50,  'minimum_stock' => 10, 'cost_per_unit' => 12000],
            ['name' => 'Minyak Goreng',    'unit' => 'liter', 'current_stock' => 20,  'minimum_stock' => 5,  'cost_per_unit' => 18000],
            ['name' => 'Daging Ayam Segar','unit' => 'kg',    'current_stock' => 15,  'minimum_stock' => 5,  'cost_per_unit' => 35000],
            ['name' => 'Daging Sapi Lokal','unit' => 'kg',    'current_stock' => 10,  'minimum_stock' => 3,  'cost_per_unit' => 120000],
            ['name' => 'Telur Ayam',       'unit' => 'butir', 'current_stock' => 200, 'minimum_stock' => 50, 'cost_per_unit' => 2000],
            ['name' => 'Tepung Terigu',    'unit' => 'kg',    'current_stock' => 25,  'minimum_stock' => 5,  'cost_per_unit' => 10000],
            ['name' => 'Gula Pasir',       'unit' => 'kg',    'current_stock' => 15,  'minimum_stock' => 3,  'cost_per_unit' => 14000],
            ['name' => 'Garam Dapur',      'unit' => 'kg',    'current_stock' => 10,  'minimum_stock' => 2,  'cost_per_unit' => 5000],
            ['name' => 'Kecap Manis',      'unit' => 'botol', 'current_stock' => 12,  'minimum_stock' => 3,  'cost_per_unit' => 15000],
            ['name' => 'Santan Kelapa',    'unit' => 'liter', 'current_stock' => 8,   'minimum_stock' => 2,  'cost_per_unit' => 20000],
            ['name' => 'Alpukat Segar',    'unit' => 'kg',    'current_stock' => 10,  'minimum_stock' => 3,  'cost_per_unit' => 25000],
            ['name' => 'Jeruk Segar',      'unit' => 'kg',    'current_stock' => 8,   'minimum_stock' => 2,  'cost_per_unit' => 18000],
            ['name' => 'Susu Segar',       'unit' => 'liter', 'current_stock' => 15,  'minimum_stock' => 5,  'cost_per_unit' => 16000],
            ['name' => 'Coklat Bubuk',     'unit' => 'kg',    'current_stock' => 5,   'minimum_stock' => 1,  'cost_per_unit' => 80000],
            ['name' => 'Pisang Kepok',     'unit' => 'sisir', 'current_stock' => 10,  'minimum_stock' => 3,  'cost_per_unit' => 15000],
        ];

        $rows = [];

        foreach ($supplies as $s) {
            $existing = DB::table('fb_supplies')
                ->where('tenant_id', $tenantId)
                ->where('name', $s['name'])
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                continue;
            }

            $rows[] = [
                'tenant_id'         => $tenantId,
                'name'              => $s['name'],
                'unit'              => $s['unit'],
                'current_stock'     => $s['current_stock'],
                'minimum_stock'     => $s['minimum_stock'],
                'cost_per_unit'     => $s['cost_per_unit'],
                'category_id'       => null,
                'supplier_name'     => null,
                'last_restocked_at' => Carbon::now()->subDays(rand(1, 14))->format('Y-m-d'),
                'is_active'         => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];
        }

        if (!empty($rows)) {
            $this->bulkInsert('fb_supplies', $rows);
        }

        return count($rows);
    }

    // ─────────────────────────────────────────────────────────────
    //  Employees — 3 restaurant staff (kasir, pelayan, koki)
    // ─────────────────────────────────────────────────────────────

    private function seedEmployees(int $tenantId): int
    {
        $employees = [
            ['employee_id' => 'RST-EMP-001', 'name' => 'Sari Wulandari', 'position' => 'Kasir',   'department' => 'Kasir',   'salary' => 4500000],
            ['employee_id' => 'RST-EMP-002', 'name' => 'Doni Prasetyo',  'position' => 'Pelayan', 'department' => 'Pelayan', 'salary' => 3800000],
            ['employee_id' => 'RST-EMP-003', 'name' => 'Hendra Kusuma',  'position' => 'Koki',    'department' => 'Dapur',   'salary' => 6000000],
        ];

        $count = 0;

        foreach ($employees as $i => $e) {
            $existing = DB::table('employees')
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $e['employee_id'])
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                continue;
            }

            DB::table('employees')->insert([
                'tenant_id'    => $tenantId,
                'employee_id'  => $e['employee_id'],
                'name'         => $e['name'],
                'email'        => strtolower(str_replace(' ', '.', $e['name'])) . '@demo-restaurant.com',
                'phone'        => '0819-' . str_pad((string) (10000000 + $i * 3333333), 8, '0', STR_PAD_LEFT),
                'position'     => $e['position'],
                'department'   => $e['department'],
                'join_date'    => Carbon::now()->subMonths(rand(3, 24))->format('Y-m-d'),
                'status'       => 'active',
                'salary'       => $e['salary'],
                'bank_name'    => 'BCA',
                'bank_account' => '2' . str_pad((string) (100000000 + $i * 111111111), 9, '0', STR_PAD_LEFT),
                'address'      => 'Jakarta',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            $count++;
        }

        return $count;
    }
}
