<?php

namespace App\Services\DemoData\Generators;

use App\Services\DemoData\BaseIndustryGenerator;
use App\Services\DemoData\CoreDataContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ManufacturingGenerator extends BaseIndustryGenerator
{
    public function getIndustryName(): string
    {
        return 'manufacturing';
    }

    public function generate(CoreDataContext $ctx): array
    {
        $tenantId = $ctx->tenantId;
        $warehouseId = $ctx->warehouseId;
        $supplierIds = $ctx->supplierIds;
        $customerIds = $ctx->customerIds;
        $recordsCreated = 0;
        $generatedData = [];

        // ── 1. Raw Material Products (5) ───────────────────────────────────
        try {
            $rawMaterialIds = $this->seedRawMaterials($tenantId, $warehouseId);
            $recordsCreated += count($rawMaterialIds);
            $generatedData['raw_materials'] = count($rawMaterialIds);
        } catch (\Throwable $e) {
            $this->logWarning('ManufacturingGenerator: failed to seed raw materials', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
            $rawMaterialIds = [];
            $generatedData['raw_materials'] = 0;
        }

        // ── 2. Finished Good Products (3) ──────────────────────────────────
        try {
            $finishedGoodIds = $this->seedFinishedGoods($tenantId, $warehouseId);
            $recordsCreated += count($finishedGoodIds);
            $generatedData['finished_goods'] = count($finishedGoodIds);
        } catch (\Throwable $e) {
            $this->logWarning('ManufacturingGenerator: failed to seed finished goods', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
            $finishedGoodIds = [];
            $generatedData['finished_goods'] = 0;
        }

        // ── 3. BOMs (2) ────────────────────────────────────────────────────
        $bomIds = [];
        try {
            if (! empty($finishedGoodIds) && ! empty($rawMaterialIds)) {
                $bomIds = $this->seedBoms($tenantId, $finishedGoodIds, $rawMaterialIds);
                $recordsCreated += count($bomIds);
                $generatedData['boms'] = count($bomIds);
            } else {
                $this->logWarning('ManufacturingGenerator: skipping BOMs — no finished goods or raw materials', [
                    'tenant_id' => $tenantId,
                ]);
                $generatedData['boms'] = 0;
            }
        } catch (\Throwable $e) {
            $this->logWarning('ManufacturingGenerator: failed to seed BOMs', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
            $generatedData['boms'] = 0;
        }

        // ── 4. Work Orders (3 — draft, in_progress, completed) ─────────────
        $workOrderIds = [];
        try {
            if (! empty($finishedGoodIds)) {
                $workOrderIds = $this->seedWorkOrders($tenantId, $finishedGoodIds, $bomIds);
                $recordsCreated += count($workOrderIds);
                $generatedData['work_orders'] = count($workOrderIds);
            } else {
                $this->logWarning('ManufacturingGenerator: skipping work orders — no finished goods', [
                    'tenant_id' => $tenantId,
                ]);
                $generatedData['work_orders'] = 0;
            }
        } catch (\Throwable $e) {
            $this->logWarning('ManufacturingGenerator: failed to seed work orders', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
            $generatedData['work_orders'] = 0;
        }

        // ── 5. Quality Control Records (2) ─────────────────────────────────
        try {
            if (! empty($workOrderIds) && ! empty($finishedGoodIds)) {
                $qcCount = $this->seedQualityChecks($tenantId, $workOrderIds, $finishedGoodIds);
                $recordsCreated += $qcCount;
                $generatedData['quality_checks'] = $qcCount;
            } else {
                $this->logWarning('ManufacturingGenerator: skipping QC records — no work orders', [
                    'tenant_id' => $tenantId,
                ]);
                $generatedData['quality_checks'] = 0;
            }
        } catch (\Throwable $e) {
            $this->logWarning('ManufacturingGenerator: failed to seed quality checks', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
            $generatedData['quality_checks'] = 0;
        }

        // ── 6. Purchase Orders for raw materials ───────────────────────────
        try {
            if (! empty($supplierIds) && ! empty($rawMaterialIds)) {
                $poCount = $this->seedPurchaseOrders($tenantId, $supplierIds, $rawMaterialIds, $warehouseId);
                $recordsCreated += $poCount;
                $generatedData['purchase_orders'] = $poCount;
            } else {
                $this->logWarning('ManufacturingGenerator: skipping POs — no suppliers or raw materials', [
                    'tenant_id' => $tenantId,
                ]);
                $generatedData['purchase_orders'] = 0;
            }
        } catch (\Throwable $e) {
            $this->logWarning('ManufacturingGenerator: failed to seed purchase orders', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
            $generatedData['purchase_orders'] = 0;
        }

        // ── 7. Sales Orders for finished goods ─────────────────────────────
        try {
            if (! empty($customerIds) && ! empty($finishedGoodIds)) {
                $soCount = $this->seedSalesOrders($tenantId, $customerIds, $finishedGoodIds);
                $recordsCreated += $soCount;
                $generatedData['sales_orders'] = $soCount;
            } else {
                $this->logWarning('ManufacturingGenerator: skipping SOs — no customers or finished goods', [
                    'tenant_id' => $tenantId,
                ]);
                $generatedData['sales_orders'] = 0;
            }
        } catch (\Throwable $e) {
            $this->logWarning('ManufacturingGenerator: failed to seed sales orders', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
            $generatedData['sales_orders'] = 0;
        }

        return [
            'records_created' => $recordsCreated,
            'generated_data' => $generatedData,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Raw Materials — 5 products with category 'raw_material'
    // ─────────────────────────────────────────────────────────────

    private function seedRawMaterials(int $tenantId, int $warehouseId): array
    {
        $materials = [
            ['name' => 'Baja Lembaran HRC 2mm',       'sku' => 'MFG-RM-001', 'unit' => 'lembar', 'price_buy' => 185000,  'price_sell' => 210000,  'stock_min' => 50,  'qty' => 500],
            ['name' => 'Aluminium Profil 6061-T6',     'sku' => 'MFG-RM-002', 'unit' => 'batang', 'price_buy' => 95000,   'price_sell' => 115000,  'stock_min' => 30,  'qty' => 300],
            ['name' => 'Resin Epoxy 1kg',              'sku' => 'MFG-RM-003', 'unit' => 'kg',     'price_buy' => 75000,   'price_sell' => 95000,   'stock_min' => 20,  'qty' => 200],
            ['name' => 'Karet Seal EPDM 10mm',         'sku' => 'MFG-RM-004', 'unit' => 'meter',  'price_buy' => 12000,   'price_sell' => 18000,   'stock_min' => 100, 'qty' => 1000],
            ['name' => 'Cat Powder Coating Hitam 1kg', 'sku' => 'MFG-RM-005', 'unit' => 'kg',     'price_buy' => 55000,   'price_sell' => 75000,   'stock_min' => 25,  'qty' => 250],
        ];

        $productIds = [];
        $stockRows = [];

        foreach ($materials as $m) {
            $existing = DB::table('products')
                ->where('tenant_id', $tenantId)
                ->where('sku', $m['sku'])
                ->first();

            if ($existing) {
                $productIds[] = (int) $existing->id;

                continue;
            }

            $id = DB::table('products')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => $m['name'],
                'sku' => $m['sku'],
                'category' => 'raw_material',
                'unit' => $m['unit'],
                'price_buy' => $m['price_buy'],
                'price_sell' => $m['price_sell'],
                'stock_min' => $m['stock_min'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $productIds[] = (int) $id;

            $stockRows[] = [
                'product_id' => $id,
                'warehouse_id' => $warehouseId,
                'quantity' => $m['qty'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($stockRows)) {
            DB::table('product_stocks')->insertOrIgnore($stockRows);
        }

        return $productIds;
    }

    // ─────────────────────────────────────────────────────────────
    //  Finished Goods — 3 products with category 'finished_good'
    // ─────────────────────────────────────────────────────────────

    private function seedFinishedGoods(int $tenantId, int $warehouseId): array
    {
        $goods = [
            ['name' => 'Rangka Pintu Baja Galvanis',   'sku' => 'MFG-FG-001', 'unit' => 'unit', 'price_buy' => 450000,  'price_sell' => 750000,  'stock_min' => 10, 'qty' => 50],
            ['name' => 'Panel Aluminium Komposit 4mm', 'sku' => 'MFG-FG-002', 'unit' => 'lembar', 'price_buy' => 320000,  'price_sell' => 520000,  'stock_min' => 15, 'qty' => 80],
            ['name' => 'Komponen Mesin Press Hidrolik', 'sku' => 'MFG-FG-003', 'unit' => 'set',  'price_buy' => 1200000, 'price_sell' => 1950000, 'stock_min' => 5,  'qty' => 20],
        ];

        $productIds = [];
        $stockRows = [];

        foreach ($goods as $g) {
            $existing = DB::table('products')
                ->where('tenant_id', $tenantId)
                ->where('sku', $g['sku'])
                ->first();

            if ($existing) {
                $productIds[] = (int) $existing->id;

                continue;
            }

            $id = DB::table('products')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => $g['name'],
                'sku' => $g['sku'],
                'category' => 'finished_good',
                'unit' => $g['unit'],
                'price_buy' => $g['price_buy'],
                'price_sell' => $g['price_sell'],
                'stock_min' => $g['stock_min'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $productIds[] = (int) $id;

            $stockRows[] = [
                'product_id' => $id,
                'warehouse_id' => $warehouseId,
                'quantity' => $g['qty'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($stockRows)) {
            DB::table('product_stocks')->insertOrIgnore($stockRows);
        }

        return $productIds;
    }

    // ─────────────────────────────────────────────────────────────
    //  BOMs — 2 BOMs linking finished goods to raw materials
    // ─────────────────────────────────────────────────────────────

    private function seedBoms(int $tenantId, array $finishedGoodIds, array $rawMaterialIds): array
    {
        $bomDefinitions = [
            [
                'name' => 'BOM Rangka Pintu Baja',
                'product_id' => $finishedGoodIds[0],
                'batch_size' => 1,
                'batch_unit' => 'unit',
                'lines' => [
                    ['product_id' => $rawMaterialIds[0], 'qty' => 3,   'unit' => 'lembar', 'sort' => 1, 'notes' => 'Baja lembaran untuk rangka'],
                    ['product_id' => $rawMaterialIds[3], 'qty' => 2,   'unit' => 'meter',  'sort' => 2, 'notes' => 'Karet seal untuk pintu'],
                    ['product_id' => $rawMaterialIds[4], 'qty' => 0.5, 'unit' => 'kg',     'sort' => 3, 'notes' => 'Cat powder coating'],
                ],
            ],
            [
                'name' => 'BOM Panel Aluminium Komposit',
                'product_id' => $finishedGoodIds[1],
                'batch_size' => 1,
                'batch_unit' => 'lembar',
                'lines' => [
                    ['product_id' => $rawMaterialIds[1], 'qty' => 2,   'unit' => 'batang', 'sort' => 1, 'notes' => 'Profil aluminium untuk frame'],
                    ['product_id' => $rawMaterialIds[2], 'qty' => 0.3, 'unit' => 'kg',     'sort' => 2, 'notes' => 'Resin epoxy untuk bonding'],
                    ['product_id' => $rawMaterialIds[4], 'qty' => 0.2, 'unit' => 'kg',     'sort' => 3, 'notes' => 'Cat finishing'],
                ],
            ],
        ];

        $bomIds = [];

        foreach ($bomDefinitions as $def) {
            $existing = DB::table('boms')
                ->where('tenant_id', $tenantId)
                ->where('product_id', $def['product_id'])
                ->where('name', $def['name'])
                ->first();

            if ($existing) {
                $bomIds[] = (int) $existing->id;

                continue;
            }

            $bomId = DB::table('boms')->insertGetId([
                'tenant_id' => $tenantId,
                'product_id' => $def['product_id'],
                'name' => $def['name'],
                'batch_size' => $def['batch_size'],
                'batch_unit' => $def['batch_unit'],
                'is_active' => true,
                'notes' => 'Demo BOM untuk industri manufaktur',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $lineRows = [];
            foreach ($def['lines'] as $line) {
                $lineRows[] = [
                    'tenant_id' => $tenantId,
                    'bom_id' => $bomId,
                    'product_id' => $line['product_id'],
                    'quantity_per_batch' => $line['qty'],
                    'unit' => $line['unit'],
                    'sort_order' => $line['sort'],
                    'notes' => $line['notes'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $this->bulkInsert('bom_lines', $lineRows);
            $bomIds[] = (int) $bomId;
        }

        return $bomIds;
    }

    // ─────────────────────────────────────────────────────────────
    //  Work Orders — 3 with statuses: draft, in_progress, completed
    // ─────────────────────────────────────────────────────────────

    private function seedWorkOrders(int $tenantId, array $finishedGoodIds, array $bomIds): array
    {
        // work_orders requires user_id — resolve any user for this tenant
        $userId = DB::table('users')->where('tenant_id', $tenantId)->value('id');
        if (! $userId) {
            $this->logWarning('ManufacturingGenerator: no user found for tenant, skipping work orders', [
                'tenant_id' => $tenantId,
            ]);

            return [];
        }

        $workOrders = [
            [
                'number' => 'MFG-WO-' . $tenantId . '-001',
                'product_id' => $finishedGoodIds[0],
                'recipe_id' => null,
                'status' => 'pending',
                'target_quantity' => 20,
                'unit' => 'unit',
                'planned_start_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'planned_end_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'started_at' => null,
                'completed_at' => null,
                'progress_percent' => 0,
                'notes' => 'Work order baru — menunggu persiapan material',
            ],
            [
                'number' => 'MFG-WO-' . $tenantId . '-002',
                'product_id' => $finishedGoodIds[1 % count($finishedGoodIds)],
                'recipe_id' => null,
                'status' => 'in_progress',
                'target_quantity' => 15,
                'unit' => 'pcs',
                'planned_start_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'planned_end_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'started_at' => Carbon::now()->subDays(5)->toDateTimeString(),
                'completed_at' => null,
                'progress_percent' => 60,
                'notes' => 'Produksi sedang berjalan — 60% selesai',
            ],
            [
                'number' => 'MFG-WO-' . $tenantId . '-003',
                'product_id' => $finishedGoodIds[2 % count($finishedGoodIds)],
                'recipe_id' => null,
                'status' => 'completed',
                'target_quantity' => 10,
                'unit' => 'pcs',
                'planned_start_date' => Carbon::now()->subDays(20)->format('Y-m-d'),
                'planned_end_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'started_at' => Carbon::now()->subDays(20)->toDateTimeString(),
                'completed_at' => Carbon::now()->subDays(10)->toDateTimeString(),
                'progress_percent' => 100,
                'notes' => 'Produksi selesai — semua unit lolos QC',
            ],
        ];

        $workOrderIds = [];

        foreach ($workOrders as $wo) {
            $existing = DB::table('work_orders')
                ->where('tenant_id', $tenantId)
                ->where('number', $wo['number'])
                ->first();

            if ($existing) {
                $workOrderIds[] = (int) $existing->id;

                continue;
            }

            $id = DB::table('work_orders')->insertGetId([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'product_id' => $wo['product_id'],
                'recipe_id' => $wo['recipe_id'],
                'number' => $wo['number'],
                'status' => $wo['status'],
                'target_quantity' => $wo['target_quantity'],
                'unit' => $wo['unit'],
                'planned_start_date' => $wo['planned_start_date'],
                'planned_end_date' => $wo['planned_end_date'],
                'started_at' => $wo['started_at'],
                'completed_at' => $wo['completed_at'],
                'material_cost' => 0,
                'labor_cost' => 0,
                'overhead_cost' => 0,
                'total_cost' => 0,
                'progress_percent' => $wo['progress_percent'],
                'notes' => $wo['notes'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $workOrderIds[] = (int) $id;
        }

        return $workOrderIds;
    }

    // ─────────────────────────────────────────────────────────────
    //  Quality Checks — 2 records linked to work orders
    // ─────────────────────────────────────────────────────────────

    private function seedQualityChecks(int $tenantId, array $workOrderIds, array $finishedGoodIds): int
    {
        $qcRecords = [
            [
                'check_number' => 'MFG-QC-0001',
                'work_order_id' => $workOrderIds[2] ?? $workOrderIds[0], // completed WO
                'product_id' => $finishedGoodIds[2 % count($finishedGoodIds)],
                'stage' => 'final',
                'sample_size' => 10,
                'sample_passed' => 10,
                'sample_failed' => 0,
                'status' => 'passed',
                'notes' => 'Semua unit memenuhi standar dimensi dan finishing',
                'inspected_at' => Carbon::now()->subDays(10)->toDateTimeString(),
            ],
            [
                'check_number' => 'MFG-QC-0002',
                'work_order_id' => $workOrderIds[1] ?? $workOrderIds[0], // in_progress WO
                'product_id' => $finishedGoodIds[1 % count($finishedGoodIds)],
                'stage' => 'in_process',
                'sample_size' => 5,
                'sample_passed' => 4,
                'sample_failed' => 1,
                'status' => 'conditional_pass',
                'notes' => '1 unit perlu rework pada bagian coating — lanjutkan produksi dengan pengawasan',
                'inspected_at' => Carbon::now()->subDays(2)->toDateTimeString(),
            ],
        ];

        $count = 0;

        foreach ($qcRecords as $qc) {
            $exists = DB::table('quality_checks')
                ->where('tenant_id', $tenantId)
                ->where('check_number', $qc['check_number'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('quality_checks')->insert([
                'tenant_id' => $tenantId,
                'work_order_id' => $qc['work_order_id'],
                'product_id' => $qc['product_id'],
                'check_number' => $qc['check_number'],
                'stage' => $qc['stage'],
                'sample_size' => $qc['sample_size'],
                'sample_passed' => $qc['sample_passed'],
                'sample_failed' => $qc['sample_failed'],
                'status' => $qc['status'],
                'notes' => $qc['notes'],
                'inspected_at' => $qc['inspected_at'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $count++;
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────────
    //  Purchase Orders — for raw materials (2 POs)
    // ─────────────────────────────────────────────────────────────

    private function seedPurchaseOrders(
        int $tenantId,
        array $supplierIds,
        array $rawMaterialIds,
        int $warehouseId
    ): int {
        $userId = DB::table('users')->where('tenant_id', $tenantId)->value('id');
        if (! $userId || empty($supplierIds) || empty($rawMaterialIds)) {
            $this->logWarning('ManufacturingGenerator: missing user, suppliers or raw materials for POs', [
                'tenant_id' => $tenantId,
            ]);

            return 0;
        }

        $poDefinitions = [
            ['number' => 'MFG-PO-' . $tenantId . '-001', 'products' => array_slice($rawMaterialIds, 0, 3)],
            ['number' => 'MFG-PO-' . $tenantId . '-002', 'products' => array_slice($rawMaterialIds, 2, 3)],
        ];

        $count = 0;

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
            $poDate = Carbon::now()->subDays(rand(7, 30))->format('Y-m-d');
            $subtotal = 0;
            $items = [];

            foreach ($def['products'] as $productId) {
                $product = DB::table('products')->where('id', $productId)->first();
                if (! $product) {
                    continue;
                }
                $qty = rand(50, 200);
                $price = (float) $product->price_buy;
                $total = $qty * $price;
                $subtotal += $total;

                $items[] = [
                    'product_id' => $productId,
                    'quantity_ordered' => $qty,
                    'quantity_received' => 0,
                    'price' => $price,
                    'total' => $total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (empty($items)) {
                continue;
            }

            $poId = DB::table('purchase_orders')->insertGetId([
                'tenant_id' => $tenantId,
                'supplier_id' => $supplierId,
                'user_id' => $userId,
                'warehouse_id' => $warehouseId,
                'number' => $def['number'],
                'status' => 'sent',
                'date' => $poDate,
                'expected_date' => Carbon::parse($poDate)->addDays(14)->format('Y-m-d'),
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => 0,
                'total' => $subtotal,
                'created_at' => now(),
                'updated_at' => now(),
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

    // ─────────────────────────────────────────────────────────────
    //  Sales Orders — for finished goods (3 SOs)
    // ─────────────────────────────────────────────────────────────

    private function seedSalesOrders(int $tenantId, array $customerIds, array $finishedGoodIds): int
    {
        // sales_orders requires user_id
        $userId = DB::table('users')->where('tenant_id', $tenantId)->value('id');
        if (! $userId) {
            $this->logWarning('ManufacturingGenerator: no user found for tenant, skipping sales orders', [
                'tenant_id' => $tenantId,
            ]);

            return 0;
        }

        $count = 0;

        for ($i = 1; $i <= 3; $i++) {
            $soNumber = 'MFG-SO-' . $tenantId . '-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT);

            $exists = DB::table('sales_orders')
                ->where('tenant_id', $tenantId)
                ->where('number', $soNumber)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                continue;
            }

            $customerId = $customerIds[($i - 1) % count($customerIds)];
            $orderDate = Carbon::now()->subDays(rand(5, 60))->format('Y-m-d');

            $orderProducts = array_slice($finishedGoodIds, ($i - 1) % count($finishedGoodIds), 2);
            if (empty($orderProducts)) {
                $orderProducts = [$finishedGoodIds[0]];
            }

            $subtotal = 0;
            $items = [];

            foreach ($orderProducts as $productId) {
                $product = DB::table('products')->where('id', $productId)->first();
                if (! $product) {
                    continue;
                }
                $qty = rand(2, 10);
                $price = (float) $product->price_sell;
                $total = $qty * $price;
                $subtotal += $total;

                $items[] = [
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'price' => $price,
                    'discount' => 0,
                    'total' => $total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (empty($items)) {
                continue;
            }

            $soId = DB::table('sales_orders')->insertGetId([
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'user_id' => $userId,
                'number' => $soNumber,
                'status' => 'delivered',
                'date' => $orderDate,
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => 0,
                'total' => $subtotal,
                'created_at' => now(),
                'updated_at' => now(),
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
}
