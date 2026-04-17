<?php

namespace App\Services\DemoData\Generators;

use App\Services\DemoData\BaseIndustryGenerator;
use App\Services\DemoData\CoreDataContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConstructionGenerator extends BaseIndustryGenerator
{
    public function getIndustryName(): string
    {
        return 'construction';
    }

    public function generate(CoreDataContext $ctx): array
    {
        $tenantId       = $ctx->tenantId;
        $warehouseId    = $ctx->warehouseId;
        $supplierIds    = $ctx->supplierIds;
        $recordsCreated = 0;
        $generatedData  = [];

        // ── 1. Construction Projects (3 — planning, in_progress, completed) ──
        $projectIds = [];
        try {
            $projectIds = $this->seedProjects($tenantId);
            $recordsCreated += count($projectIds);
            $generatedData['projects'] = count($projectIds);
        } catch (\Throwable $e) {
            $this->logWarning('ConstructionGenerator: failed to seed projects', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['projects'] = 0;
        }

        // ── 2. RAB — 1 per project ─────────────────────────────────────────
        try {
            if (!empty($projectIds)) {
                $rabCount = $this->seedRab($tenantId, $projectIds);
                $recordsCreated += $rabCount;
                $generatedData['rab'] = $rabCount;
            } else {
                $this->logWarning('ConstructionGenerator: skipping RAB — no projects', [
                    'tenant_id' => $tenantId,
                ]);
                $generatedData['rab'] = 0;
            }
        } catch (\Throwable $e) {
            $this->logWarning('ConstructionGenerator: failed to seed RAB', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['rab'] = 0;
        }

        // ── 3. Construction Materials (20 products) ────────────────────────
        $materialIds = [];
        try {
            $materialIds = $this->seedMaterials($tenantId, $warehouseId);
            $recordsCreated += count($materialIds);
            $generatedData['materials'] = count($materialIds);
        } catch (\Throwable $e) {
            $this->logWarning('ConstructionGenerator: failed to seed materials', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['materials'] = 0;
        }

        // ── 4. Purchase Orders (5 — for construction materials) ────────────
        try {
            if (!empty($materialIds)) {
                $poCount = $this->seedPurchaseOrders($tenantId, $materialIds, $supplierIds);
                $recordsCreated += $poCount;
                $generatedData['purchase_orders'] = $poCount;
            } else {
                $this->logWarning('ConstructionGenerator: skipping POs — no materials', [
                    'tenant_id' => $tenantId,
                ]);
                $generatedData['purchase_orders'] = 0;
            }
        } catch (\Throwable $e) {
            $this->logWarning('ConstructionGenerator: failed to seed purchase orders', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['purchase_orders'] = 0;
        }

        // ── 5. Employees (5 — mandor x1, tukang x2, pengawas x2) ──────────
        try {
            $employeeCount = $this->seedEmployees($tenantId);
            $recordsCreated += $employeeCount;
            $generatedData['employees'] = $employeeCount;
        } catch (\Throwable $e) {
            $this->logWarning('ConstructionGenerator: failed to seed employees', [
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
    //  Projects — 3 with statuses: planning, in_progress, completed
    // ─────────────────────────────────────────────────────────────

    private function seedProjects(int $tenantId): array
    {
        // projects requires user_id — resolve any user for this tenant
        $userId = DB::table('users')->where('tenant_id', $tenantId)->value('id');
        if (!$userId) {
            $this->logWarning('ConstructionGenerator: no user found for tenant, skipping projects', [
                'tenant_id' => $tenantId,
            ]);
            return [];
        }

        $projects = [
            [
                'number'      => 'CON-PRJ-' . $tenantId . '-001',
                'name'        => 'Pembangunan Gedung Kantor PT Maju Sejahtera',
                'description' => 'Pembangunan gedung kantor 5 lantai dengan fasilitas lengkap di kawasan bisnis.',
                'type'        => 'construction',
                'status'      => 'planning',
                'start_date'  => Carbon::now()->addDays(14)->format('Y-m-d'),
                'end_date'    => Carbon::now()->addMonths(12)->format('Y-m-d'),
                'budget'      => 4500000000,
                'progress'    => 0,
            ],
            [
                'number'      => 'CON-PRJ-' . $tenantId . '-002',
                'name'        => 'Renovasi Jembatan Desa Sukamaju',
                'description' => 'Renovasi dan perkuatan struktur jembatan beton sepanjang 40 meter.',
                'type'        => 'construction',
                'status'      => 'active',
                'start_date'  => Carbon::now()->subDays(60)->format('Y-m-d'),
                'end_date'    => Carbon::now()->addMonths(4)->format('Y-m-d'),
                'budget'      => 850000000,
                'progress'    => 45,
            ],
            [
                'number'      => 'CON-PRJ-' . $tenantId . '-003',
                'name'        => 'Pembangunan Perumahan Griya Asri Blok C',
                'description' => 'Pembangunan 20 unit rumah tipe 36/72 di kawasan perumahan Griya Asri.',
                'type'        => 'construction',
                'status'      => 'completed',
                'start_date'  => Carbon::now()->subMonths(8)->format('Y-m-d'),
                'end_date'    => Carbon::now()->subDays(15)->format('Y-m-d'),
                'budget'      => 2200000000,
                'progress'    => 100,
            ],
        ];

        $projectIds = [];

        foreach ($projects as $p) {
            $existing = DB::table('projects')
                ->where('tenant_id', $tenantId)
                ->where('number', $p['number'])
                ->first();

            if ($existing) {
                $projectIds[] = (int) $existing->id;
                continue;
            }

            $id = DB::table('projects')->insertGetId([
                'tenant_id'   => $tenantId,
                'user_id'     => $userId,
                'number'      => $p['number'],
                'name'        => $p['name'],
                'description' => $p['description'],
                'type'        => $p['type'],
                'status'      => $p['status'],
                'start_date'  => $p['start_date'],
                'end_date'    => $p['end_date'],
                'budget'      => $p['budget'],
                'actual_cost' => 0,
                'progress'    => $p['progress'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $projectIds[] = (int) $id;
        }

        return $projectIds;
    }

    // ─────────────────────────────────────────────────────────────
    //  RAB (Rencana Anggaran Biaya) — 1 per project
    // ─────────────────────────────────────────────────────────────

    private function seedRab(int $tenantId, array $projectIds): int
    {
        $rabData = [
            [
                'number'      => 'RAB-CON-0001',
                'title'       => 'RAB Pembangunan Gedung Kantor PT Maju Sejahtera',
                'description' => 'Rencana anggaran biaya lengkap untuk pembangunan gedung kantor 5 lantai.',
                'status'      => 'approved',
                'total_cost'  => 4500000000,
                'project_idx' => 0,
            ],
            [
                'number'      => 'RAB-CON-0002',
                'title'       => 'RAB Renovasi Jembatan Desa Sukamaju',
                'description' => 'Rencana anggaran biaya renovasi dan perkuatan struktur jembatan.',
                'status'      => 'approved',
                'total_cost'  => 850000000,
                'project_idx' => 1,
            ],
            [
                'number'      => 'RAB-CON-0003',
                'title'       => 'RAB Pembangunan Perumahan Griya Asri Blok C',
                'description' => 'Rencana anggaran biaya pembangunan 20 unit rumah tipe 36/72.',
                'status'      => 'approved',
                'total_cost'  => 2200000000,
                'project_idx' => 2,
            ],
        ];

        $count = 0;

        foreach ($rabData as $rab) {
            $projectId = $projectIds[$rab['project_idx'] % count($projectIds)];

            $exists = DB::table('project_budgets')
                ->where('tenant_id', $tenantId)
                ->where('number', $rab['number'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('project_budgets')->insert([
                'tenant_id'   => $tenantId,
                'project_id'  => $projectId,
                'number'      => $rab['number'],
                'title'       => $rab['title'],
                'description' => $rab['description'],
                'status'      => $rab['status'],
                'total_cost'  => $rab['total_cost'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $count++;
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────────
    //  Materials — 20 construction products
    // ─────────────────────────────────────────────────────────────

    private function seedMaterials(int $tenantId, int $warehouseId): array
    {
        $materials = [
            ['name' => 'Semen Portland 50kg',          'sku' => 'CON-MAT-001', 'unit' => 'sak',    'price_buy' => 58000,    'price_sell' => 68000,    'qty' => 500],
            ['name' => 'Besi Beton Ulir D10 12m',      'sku' => 'CON-MAT-002', 'unit' => 'batang', 'price_buy' => 85000,    'price_sell' => 98000,    'qty' => 300],
            ['name' => 'Besi Beton Ulir D13 12m',      'sku' => 'CON-MAT-003', 'unit' => 'batang', 'price_buy' => 145000,   'price_sell' => 165000,   'qty' => 200],
            ['name' => 'Pasir Beton (m³)',              'sku' => 'CON-MAT-004', 'unit' => 'm3',     'price_buy' => 185000,   'price_sell' => 220000,   'qty' => 100],
            ['name' => 'Batu Split 2/3 (m³)',           'sku' => 'CON-MAT-005', 'unit' => 'm3',     'price_buy' => 210000,   'price_sell' => 250000,   'qty' => 80],
            ['name' => 'Bata Merah 5x11x22cm',         'sku' => 'CON-MAT-006', 'unit' => 'buah',   'price_buy' => 800,      'price_sell' => 1000,     'qty' => 10000],
            ['name' => 'Kayu Meranti 5/10 4m',         'sku' => 'CON-MAT-007', 'unit' => 'batang', 'price_buy' => 65000,    'price_sell' => 80000,    'qty' => 150],
            ['name' => 'Cat Tembok Eksterior 25kg',     'sku' => 'CON-MAT-008', 'unit' => 'ember',  'price_buy' => 285000,   'price_sell' => 340000,   'qty' => 60],
            ['name' => 'Keramik Lantai 60x60 Polished', 'sku' => 'CON-MAT-009', 'unit' => 'dus',    'price_buy' => 145000,   'price_sell' => 175000,   'qty' => 200],
            ['name' => 'Pipa PVC AW 4 inch 4m',        'sku' => 'CON-MAT-010', 'unit' => 'batang', 'price_buy' => 95000,    'price_sell' => 115000,   'qty' => 80],
            ['name' => 'Kabel NYM 3x2.5mm 50m',        'sku' => 'CON-MAT-011', 'unit' => 'roll',   'price_buy' => 385000,   'price_sell' => 450000,   'qty' => 40],
            ['name' => 'Besi Hollow 40x40x2mm 6m',     'sku' => 'CON-MAT-012', 'unit' => 'batang', 'price_buy' => 115000,   'price_sell' => 138000,   'qty' => 120],
            ['name' => 'Triplek 12mm 122x244cm',        'sku' => 'CON-MAT-013', 'unit' => 'lembar', 'price_buy' => 185000,   'price_sell' => 220000,   'qty' => 100],
            ['name' => 'Genteng Beton Flat',            'sku' => 'CON-MAT-014', 'unit' => 'buah',   'price_buy' => 8500,     'price_sell' => 10500,    'qty' => 2000],
            ['name' => 'Besi WF 200x100x5.5x8mm 12m',  'sku' => 'CON-MAT-015', 'unit' => 'batang', 'price_buy' => 1850000,  'price_sell' => 2200000,  'qty' => 30],
            ['name' => 'Kawat Bendrat 1kg',             'sku' => 'CON-MAT-016', 'unit' => 'kg',     'price_buy' => 18000,    'price_sell' => 22000,    'qty' => 200],
            ['name' => 'Paku Beton 3 inch 1kg',         'sku' => 'CON-MAT-017', 'unit' => 'kg',     'price_buy' => 22000,    'price_sell' => 28000,    'qty' => 150],
            ['name' => 'Waterproofing Coating 20kg',    'sku' => 'CON-MAT-018', 'unit' => 'ember',  'price_buy' => 385000,   'price_sell' => 460000,   'qty' => 25],
            ['name' => 'Batu Bata Ringan AAC 60x20x10', 'sku' => 'CON-MAT-019', 'unit' => 'buah',   'price_buy' => 9500,     'price_sell' => 12000,    'qty' => 3000],
            ['name' => 'Mortar Instan 40kg',            'sku' => 'CON-MAT-020', 'unit' => 'sak',    'price_buy' => 75000,    'price_sell' => 90000,    'qty' => 300],
        ];

        $materialIds = [];
        $stockRows   = [];

        foreach ($materials as $m) {
            $existing = DB::table('products')
                ->where('tenant_id', $tenantId)
                ->where('sku', $m['sku'])
                ->first();

            if ($existing) {
                $materialIds[] = (int) $existing->id;
                continue;
            }

            $id = DB::table('products')->insertGetId([
                'tenant_id'  => $tenantId,
                'name'       => $m['name'],
                'sku'        => $m['sku'],
                'category'   => 'construction_material',
                'unit'       => $m['unit'],
                'price_buy'  => $m['price_buy'],
                'price_sell' => $m['price_sell'],
                'stock_min'  => 10,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $materialIds[] = (int) $id;

            $stockRows[] = [
                'product_id'   => $id,
                'warehouse_id' => $warehouseId,
                'quantity'     => $m['qty'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        if (!empty($stockRows)) {
            DB::table('product_stocks')->insertOrIgnore($stockRows);
        }

        return $materialIds;
    }

    // ─────────────────────────────────────────────────────────────
    //  Purchase Orders — 5 POs for construction materials
    // ─────────────────────────────────────────────────────────────

    private function seedPurchaseOrders(int $tenantId, array $materialIds, array $supplierIds): int
    {
        // purchase_orders requires user_id and warehouse_id
        $userId = DB::table('users')->where('tenant_id', $tenantId)->value('id');
        $warehouseId = DB::table('warehouses')->where('tenant_id', $tenantId)->where('is_active', true)->value('id');

        if (!$userId || !$warehouseId) {
            $this->logWarning('ConstructionGenerator: missing user or warehouse for POs', [
                'tenant_id' => $tenantId,
            ]);
            return 0;
        }

        $supplierId = !empty($supplierIds) ? $supplierIds[0] : null;
        if (!$supplierId) {
            $this->logWarning('ConstructionGenerator: no suppliers available for POs', [
                'tenant_id' => $tenantId,
            ]);
            return 0;
        }

        $poList = [
            [
                'number'  => 'CON-PO-' . $tenantId . '-001',
                'status'  => 'received',
                'notes'   => 'Pembelian semen dan pasir untuk pondasi gedung kantor.',
                'items'   => [
                    ['mat_idx' => 0, 'qty' => 200],
                    ['mat_idx' => 3, 'qty' => 40],
                ],
            ],
            [
                'number'  => 'CON-PO-' . $tenantId . '-002',
                'status'  => 'received',
                'notes'   => 'Pembelian besi beton untuk struktur kolom dan balok.',
                'items'   => [
                    ['mat_idx' => 1, 'qty' => 150],
                    ['mat_idx' => 2, 'qty' => 100],
                ],
            ],
            [
                'number'  => 'CON-PO-' . $tenantId . '-003',
                'status'  => 'partial',
                'notes'   => 'Pembelian bata merah dan material dinding.',
                'items'   => [
                    ['mat_idx' => 5, 'qty' => 5000],
                    ['mat_idx' => 19 % count($materialIds), 'qty' => 100],
                ],
            ],
            [
                'number'  => 'CON-PO-' . $tenantId . '-004',
                'status'  => 'sent',
                'notes'   => 'Pembelian material finishing: keramik dan cat.',
                'items'   => [
                    ['mat_idx' => 8 % count($materialIds), 'qty' => 100],
                    ['mat_idx' => 7 % count($materialIds), 'qty' => 30],
                ],
            ],
            [
                'number'  => 'CON-PO-' . $tenantId . '-005',
                'status'  => 'draft',
                'notes'   => 'Pembelian material MEP: pipa dan kabel listrik.',
                'items'   => [
                    ['mat_idx' => 9 % count($materialIds), 'qty' => 40],
                    ['mat_idx' => 10 % count($materialIds), 'qty' => 20],
                ],
            ],
        ];

        $count = 0;

        foreach ($poList as $idx => $po) {
            $exists = DB::table('purchase_orders')
                ->where('tenant_id', $tenantId)
                ->where('number', $po['number'])
                ->exists();

            if ($exists) {
                continue;
            }

            $subtotal = 0;
            $items    = [];

            foreach ($po['items'] as $item) {
                $matIdx    = $item['mat_idx'] % count($materialIds);
                $productId = $materialIds[$matIdx];
                $product   = DB::table('products')->where('id', $productId)->first();
                if (!$product) {
                    continue;
                }
                $price    = (float) $product->price_buy;
                $total    = $item['qty'] * $price;
                $subtotal += $total;

                $items[] = [
                    'product_id'        => $productId,
                    'quantity_ordered'  => $item['qty'],
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
                'supplier_id'   => $supplierIds[$idx % count($supplierIds)],
                'user_id'       => $userId,
                'warehouse_id'  => $warehouseId,
                'number'        => $po['number'],
                'status'        => $po['status'],
                'date'          => Carbon::now()->subDays(rand(3, 45))->format('Y-m-d'),
                'expected_date' => Carbon::now()->addDays(14)->format('Y-m-d'),
                'subtotal'      => $subtotal,
                'discount'      => 0,
                'tax'           => 0,
                'total'         => $subtotal,
                'notes'         => $po['notes'],
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

    // ─────────────────────────────────────────────────────────────
    //  Employees — 5: mandor(1), tukang(2), pengawas(2)
    // ─────────────────────────────────────────────────────────────

    private function seedEmployees(int $tenantId): int
    {
        $employees = [
            [
                'employee_id' => 'CON-EMP-001',
                'name'        => 'Bambang Supriyadi',
                'email'       => 'bambang.supriyadi@demo-con.local',
                'phone'       => '0812-4001-5001',
                'position'    => 'Mandor',
                'department'  => 'Konstruksi',
                'join_date'   => Carbon::now()->subYears(5)->format('Y-m-d'),
                'salary'      => 6500000,
                'status'      => 'active',
            ],
            [
                'employee_id' => 'CON-EMP-002',
                'name'        => 'Agus Setiawan',
                'email'       => 'agus.setiawan@demo-con.local',
                'phone'       => '0813-4002-6002',
                'position'    => 'Tukang Batu',
                'department'  => 'Konstruksi',
                'join_date'   => Carbon::now()->subYears(3)->format('Y-m-d'),
                'salary'      => 4500000,
                'status'      => 'active',
            ],
            [
                'employee_id' => 'CON-EMP-003',
                'name'        => 'Heri Santoso',
                'email'       => 'heri.santoso@demo-con.local',
                'phone'       => '0814-4003-7003',
                'position'    => 'Tukang Kayu',
                'department'  => 'Konstruksi',
                'join_date'   => Carbon::now()->subYears(2)->format('Y-m-d'),
                'salary'      => 4500000,
                'status'      => 'active',
            ],
            [
                'employee_id' => 'CON-EMP-004',
                'name'        => 'Ir. Dedi Kurniawan',
                'email'       => 'dedi.kurniawan@demo-con.local',
                'phone'       => '0815-4004-8004',
                'position'    => 'Pengawas Lapangan',
                'department'  => 'Konstruksi',
                'join_date'   => Carbon::now()->subYears(4)->format('Y-m-d'),
                'salary'      => 8000000,
                'status'      => 'active',
            ],
            [
                'employee_id' => 'CON-EMP-005',
                'name'        => 'Siti Nurhaliza',
                'email'       => 'siti.nurhaliza@demo-con.local',
                'phone'       => '0816-4005-9005',
                'position'    => 'Pengawas Mutu',
                'department'  => 'Quality Control',
                'join_date'   => Carbon::now()->subYears(2)->format('Y-m-d'),
                'salary'      => 7500000,
                'status'      => 'active',
            ],
        ];

        $count = 0;

        foreach ($employees as $emp) {
            $exists = DB::table('employees')
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $emp['employee_id'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('employees')->insert([
                'tenant_id'   => $tenantId,
                'employee_id' => $emp['employee_id'],
                'name'        => $emp['name'],
                'email'       => $emp['email'],
                'phone'       => $emp['phone'],
                'position'    => $emp['position'],
                'department'  => $emp['department'],
                'join_date'   => $emp['join_date'],
                'salary'      => $emp['salary'],
                'status'      => $emp['status'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $count++;
        }

        return $count;
    }
}
