<?php

namespace App\Services\DemoData\Generators;

use App\Services\DemoData\BaseIndustryGenerator;
use App\Services\DemoData\CoreDataContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AgricultureGenerator extends BaseIndustryGenerator
{
    public function getIndustryName(): string
    {
        return 'agriculture';
    }

    public function generate(CoreDataContext $ctx): array
    {
        $tenantId      = $ctx->tenantId;
        $warehouseId   = $ctx->warehouseId;
        $recordsCreated = 0;
        $generatedData  = [];

        // ── 1. Farm Plots (3 — padi, jagung, sayuran) ─────────────────────
        $farmPlotIds = [];
        try {
            $farmPlotIds = $this->seedFarmPlots($tenantId);
            $recordsCreated += count($farmPlotIds);
            $generatedData['farm_plots'] = count($farmPlotIds);
        } catch (\Throwable $e) {
            $this->logWarning('AgricultureGenerator: failed to seed farm plots', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['farm_plots'] = 0;
        }

        // ── 2. Crop Cycles (3 — seedling, vegetative, flowering) ──────────
        $cropCycleIds = [];
        try {
            if (!empty($farmPlotIds)) {
                $cropCycleIds = $this->seedCropCycles($tenantId, $farmPlotIds);
                $recordsCreated += count($cropCycleIds);
                $generatedData['crop_cycles'] = count($cropCycleIds);
            } else {
                $this->logWarning('AgricultureGenerator: skipping crop cycles — no farm plots', [
                    'tenant_id' => $tenantId,
                ]);
                $generatedData['crop_cycles'] = 0;
            }
        } catch (\Throwable $e) {
            $this->logWarning('AgricultureGenerator: failed to seed crop cycles', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['crop_cycles'] = 0;
        }

        // ── 3. Harvest Logs (2 — linked to crop cycles) ───────────────────
        try {
            if (!empty($cropCycleIds) && !empty($farmPlotIds)) {
                $harvestCount = $this->seedHarvestLogs($tenantId, $farmPlotIds, $cropCycleIds);
                $recordsCreated += $harvestCount;
                $generatedData['harvest_logs'] = $harvestCount;
            } else {
                $this->logWarning('AgricultureGenerator: skipping harvest logs — no crop cycles', [
                    'tenant_id' => $tenantId,
                ]);
                $generatedData['harvest_logs'] = 0;
            }
        } catch (\Throwable $e) {
            $this->logWarning('AgricultureGenerator: failed to seed harvest logs', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['harvest_logs'] = 0;
        }

        // ── 4. Inventory — pupuk, pestisida, alat pertanian ───────────────
        try {
            $inventoryCount = $this->seedInventory($tenantId, $warehouseId);
            $recordsCreated += $inventoryCount;
            $generatedData['inventory'] = $inventoryCount;
        } catch (\Throwable $e) {
            $this->logWarning('AgricultureGenerator: failed to seed inventory', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['inventory'] = 0;
        }

        // ── 5. Field Employees (3) ─────────────────────────────────────────
        try {
            $employeeCount = $this->seedFieldEmployees($tenantId);
            $recordsCreated += $employeeCount;
            $generatedData['field_employees'] = $employeeCount;
        } catch (\Throwable $e) {
            $this->logWarning('AgricultureGenerator: failed to seed field employees', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            $generatedData['field_employees'] = 0;
        }

        return [
            'records_created' => $recordsCreated,
            'generated_data'  => $generatedData,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Farm Plots — 3 plots: padi, jagung, sayuran
    // ─────────────────────────────────────────────────────────────

    private function seedFarmPlots(int $tenantId): array
    {
        $plots = [
            [
                'code'             => 'AGR-PLOT-001',
                'name'             => 'Lahan Padi Blok A',
                'area_size'        => 2.5,
                'area_unit'        => 'hectare',
                'location'         => 'Blok A, Desa Sukamaju, Kec. Cianjur',
                'soil_type'        => 'clay',
                'irrigation_type'  => 'irigasi_teknis',
                'ownership'        => 'owned',
                'current_crop'     => 'Padi IR64',
                'status'           => 'growing',
                'planted_at'       => Carbon::now()->subDays(45)->format('Y-m-d'),
                'expected_harvest' => Carbon::now()->addDays(60)->format('Y-m-d'),
                'notes'            => 'Lahan utama untuk budidaya padi sawah varietas IR64.',
            ],
            [
                'code'             => 'AGR-PLOT-002',
                'name'             => 'Lahan Jagung Blok B',
                'area_size'        => 1.8,
                'area_unit'        => 'hectare',
                'location'         => 'Blok B, Desa Sukamaju, Kec. Cianjur',
                'soil_type'        => 'loam',
                'irrigation_type'  => 'tadah_hujan',
                'ownership'        => 'owned',
                'current_crop'     => 'Jagung Hibrida NK212',
                'status'           => 'planted',
                'planted_at'       => Carbon::now()->subDays(10)->format('Y-m-d'),
                'expected_harvest' => Carbon::now()->addDays(80)->format('Y-m-d'),
                'notes'            => 'Lahan jagung hibrida untuk kebutuhan pakan ternak.',
            ],
            [
                'code'             => 'AGR-PLOT-003',
                'name'             => 'Lahan Sayuran Blok C',
                'area_size'        => 0.75,
                'area_unit'        => 'hectare',
                'location'         => 'Blok C, Desa Sukamaju, Kec. Cianjur',
                'soil_type'        => 'sandy_loam',
                'irrigation_type'  => 'drip',
                'ownership'        => 'rent',
                'rent_cost'        => 3500000,
                'current_crop'     => 'Sayuran Campuran (Bayam, Kangkung)',
                'status'           => 'ready_harvest',
                'planted_at'       => Carbon::now()->subDays(30)->format('Y-m-d'),
                'expected_harvest' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'notes'            => 'Lahan sayuran organik dengan sistem irigasi tetes.',
            ],
        ];

        $plotIds = [];

        foreach ($plots as $p) {
            $existing = DB::table('farm_plots')
                ->where('tenant_id', $tenantId)
                ->where('code', $p['code'])
                ->first();

            if ($existing) {
                $plotIds[] = (int) $existing->id;
                continue;
            }

            $id = DB::table('farm_plots')->insertGetId([
                'tenant_id'        => $tenantId,
                'code'             => $p['code'],
                'name'             => $p['name'],
                'area_size'        => $p['area_size'],
                'area_unit'        => $p['area_unit'],
                'location'         => $p['location'],
                'soil_type'        => $p['soil_type'],
                'irrigation_type'  => $p['irrigation_type'],
                'ownership'        => $p['ownership'],
                'rent_cost'        => $p['rent_cost'] ?? null,
                'current_crop'     => $p['current_crop'],
                'status'           => $p['status'],
                'planted_at'       => $p['planted_at'],
                'expected_harvest' => $p['expected_harvest'],
                'is_active'        => true,
                'notes'            => $p['notes'],
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $plotIds[] = (int) $id;
        }

        return $plotIds;
    }

    // ─────────────────────────────────────────────────────────────
    //  Crop Cycles — 3 active cycles with different growth stages
    // ─────────────────────────────────────────────────────────────

    private function seedCropCycles(int $tenantId, array $farmPlotIds): array
    {
        $cycles = [
            [
                'crop_name'             => 'Padi IR64',
                'variety'               => 'IR64',
                'area_hectares'         => 2.5,
                'field_location'        => 'Blok A, Desa Sukamaju',
                'planting_date'         => Carbon::now()->subDays(45)->format('Y-m-d'),
                'expected_harvest_date' => Carbon::now()->addDays(60)->format('Y-m-d'),
                'actual_harvest_date'   => null,
                'growth_stage'          => 'vegetative',
                'estimated_yield_tons'  => 15.0,
                'actual_yield_tons'     => null,
                'status'                => 'active',
                'notes'                 => 'Siklus tanam padi musim hujan. Fase vegetatif berjalan baik.',
                'farm_plot_idx'         => 0,
            ],
            [
                'crop_name'             => 'Jagung Hibrida NK212',
                'variety'               => 'NK212',
                'area_hectares'         => 1.8,
                'field_location'        => 'Blok B, Desa Sukamaju',
                'planting_date'         => Carbon::now()->subDays(10)->format('Y-m-d'),
                'expected_harvest_date' => Carbon::now()->addDays(80)->format('Y-m-d'),
                'actual_harvest_date'   => null,
                'growth_stage'          => 'seedling',
                'estimated_yield_tons'  => 10.8,
                'actual_yield_tons'     => null,
                'status'                => 'active',
                'notes'                 => 'Bibit jagung baru ditanam. Fase perkecambahan berlangsung normal.',
                'farm_plot_idx'         => 1,
            ],
            [
                'crop_name'             => 'Bayam & Kangkung',
                'variety'               => 'Lokal',
                'area_hectares'         => 0.75,
                'field_location'        => 'Blok C, Desa Sukamaju',
                'planting_date'         => Carbon::now()->subDays(30)->format('Y-m-d'),
                'expected_harvest_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'actual_harvest_date'   => null,
                'growth_stage'          => 'flowering',
                'estimated_yield_tons'  => 2.25,
                'actual_yield_tons'     => null,
                'status'                => 'active',
                'notes'                 => 'Sayuran hampir siap panen. Fase berbunga menandakan kematangan.',
                'farm_plot_idx'         => 2,
            ],
        ];

        $cycleIds = [];

        foreach ($cycles as $c) {
            $existing = DB::table('crop_cycles')
                ->where('tenant_id', $tenantId)
                ->where('crop_name', $c['crop_name'])
                ->where('planting_date', $c['planting_date'])
                ->first();

            if ($existing) {
                $cycleIds[] = (int) $existing->id;
                continue;
            }

            $id = DB::table('crop_cycles')->insertGetId([
                'tenant_id'             => $tenantId,
                'crop_name'             => $c['crop_name'],
                'variety'               => $c['variety'],
                'area_hectares'         => $c['area_hectares'],
                'field_location'        => $c['field_location'],
                'planting_date'         => $c['planting_date'],
                'expected_harvest_date' => $c['expected_harvest_date'],
                'actual_harvest_date'   => $c['actual_harvest_date'],
                'growth_stage'          => $c['growth_stage'],
                'estimated_yield_tons'  => $c['estimated_yield_tons'],
                'actual_yield_tons'     => $c['actual_yield_tons'],
                'status'                => $c['status'],
                'notes'                 => $c['notes'],
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            $cycleIds[] = (int) $id;
        }

        return $cycleIds;
    }

    // ─────────────────────────────────────────────────────────────
    //  Harvest Logs — 2 logs linked to crop cycles & farm plots
    // ─────────────────────────────────────────────────────────────

    private function seedHarvestLogs(int $tenantId, array $farmPlotIds, array $cropCycleIds): int
    {
        $logs = [
            [
                'number'          => 'HRV-AGR-PLOT001-' . date('Ymd') . '-01',
                'farm_plot_id'    => $farmPlotIds[0],
                'crop_cycle_id'   => $cropCycleIds[0],
                'harvest_date'    => Carbon::now()->subDays(90)->format('Y-m-d'),
                'crop_name'       => 'Padi IR64',
                'total_qty'       => 14.5,
                'unit'            => 'ton',
                'reject_qty'      => 0.5,
                'moisture_pct'    => 14.0,
                'storage_location'=> 'Gudang Utama',
                'labor_cost'      => 2500000,
                'transport_cost'  => 750000,
                'weather'         => 'cerah',
                'notes'           => 'Panen musim sebelumnya. Hasil baik, kadar air sesuai standar.',
            ],
            [
                'number'          => 'HRV-AGR-PLOT003-' . date('Ymd') . '-02',
                'farm_plot_id'    => $farmPlotIds[2 % count($farmPlotIds)],
                'crop_cycle_id'   => $cropCycleIds[2 % count($cropCycleIds)],
                'harvest_date'    => Carbon::now()->subDays(5)->format('Y-m-d'),
                'crop_name'       => 'Bayam & Kangkung',
                'total_qty'       => 2.1,
                'unit'            => 'ton',
                'reject_qty'      => 0.1,
                'moisture_pct'    => 85.0,
                'storage_location'=> 'Cold Storage',
                'labor_cost'      => 800000,
                'transport_cost'  => 300000,
                'weather'         => 'berawan',
                'notes'           => 'Panen sayuran segar. Kualitas grade A 95%.',
            ],
        ];

        $count = 0;

        foreach ($logs as $log) {
            $exists = DB::table('harvest_logs')
                ->where('tenant_id', $tenantId)
                ->where('number', $log['number'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('harvest_logs')->insert([
                'tenant_id'        => $tenantId,
                'farm_plot_id'     => $log['farm_plot_id'],
                'crop_cycle_id'    => $log['crop_cycle_id'],
                'number'           => $log['number'],
                'harvest_date'     => $log['harvest_date'],
                'crop_name'        => $log['crop_name'],
                'total_qty'        => $log['total_qty'],
                'unit'             => $log['unit'],
                'reject_qty'       => $log['reject_qty'],
                'moisture_pct'     => $log['moisture_pct'],
                'storage_location' => $log['storage_location'],
                'labor_cost'       => $log['labor_cost'],
                'transport_cost'   => $log['transport_cost'],
                'weather'          => $log['weather'],
                'notes'            => $log['notes'],
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $count++;
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────────
    //  Inventory — pupuk, pestisida, alat pertanian
    // ─────────────────────────────────────────────────────────────

    private function seedInventory(int $tenantId, int $warehouseId): int
    {
        $supplies = [
            // Pupuk
            ['name' => 'Pupuk Urea 50kg',           'sku' => 'AGR-SUP-001', 'unit' => 'sak',   'price_buy' => 115000,  'price_sell' => 135000,  'stock_min' => 20, 'qty' => 100],
            ['name' => 'Pupuk NPK Phonska 50kg',     'sku' => 'AGR-SUP-002', 'unit' => 'sak',   'price_buy' => 145000,  'price_sell' => 165000,  'stock_min' => 15, 'qty' => 80],
            ['name' => 'Pupuk Organik Kompos 25kg',  'sku' => 'AGR-SUP-003', 'unit' => 'sak',   'price_buy' => 35000,   'price_sell' => 50000,   'stock_min' => 30, 'qty' => 150],
            // Pestisida
            ['name' => 'Pestisida Decis 25EC 1L',    'sku' => 'AGR-SUP-004', 'unit' => 'botol', 'price_buy' => 85000,   'price_sell' => 110000,  'stock_min' => 10, 'qty' => 40],
            ['name' => 'Herbisida Roundup 1L',       'sku' => 'AGR-SUP-005', 'unit' => 'botol', 'price_buy' => 75000,   'price_sell' => 95000,   'stock_min' => 10, 'qty' => 30],
            ['name' => 'Fungisida Dithane M-45 1kg', 'sku' => 'AGR-SUP-006', 'unit' => 'kg',    'price_buy' => 65000,   'price_sell' => 85000,   'stock_min' => 8,  'qty' => 25],
            // Alat pertanian
            ['name' => 'Cangkul Baja',               'sku' => 'AGR-SUP-007', 'unit' => 'buah',  'price_buy' => 75000,   'price_sell' => 95000,   'stock_min' => 5,  'qty' => 20],
            ['name' => 'Sprayer Elektrik 16L',        'sku' => 'AGR-SUP-008', 'unit' => 'unit',  'price_buy' => 350000,  'price_sell' => 450000,  'stock_min' => 3,  'qty' => 8],
            ['name' => 'Selang Irigasi 50m',          'sku' => 'AGR-SUP-009', 'unit' => 'roll',  'price_buy' => 185000,  'price_sell' => 230000,  'stock_min' => 5,  'qty' => 15],
        ];

        $productIds = [];
        $stockRows  = [];

        foreach ($supplies as $s) {
            $existing = DB::table('products')
                ->where('tenant_id', $tenantId)
                ->where('sku', $s['sku'])
                ->first();

            if ($existing) {
                $productIds[] = (int) $existing->id;
                continue;
            }

            $id = DB::table('products')->insertGetId([
                'tenant_id'  => $tenantId,
                'name'       => $s['name'],
                'sku'        => $s['sku'],
                'category'   => 'agriculture_supply',
                'unit'       => $s['unit'],
                'price_buy'  => $s['price_buy'],
                'price_sell' => $s['price_sell'],
                'stock_min'  => $s['stock_min'],
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $productIds[] = (int) $id;

            $stockRows[] = [
                'product_id'   => $id,
                'warehouse_id' => $warehouseId,
                'quantity'     => $s['qty'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        if (!empty($stockRows)) {
            DB::table('product_stocks')->insertOrIgnore($stockRows);
        }

        return count($productIds);
    }

    // ─────────────────────────────────────────────────────────────
    //  Field Employees — 3 karyawan lapangan
    // ─────────────────────────────────────────────────────────────

    private function seedFieldEmployees(int $tenantId): int
    {
        $employees = [
            [
                'employee_id' => 'AGR-EMP-001',
                'name'        => 'Slamet Riyadi',
                'email'       => 'slamet.riyadi@demo-agr.local',
                'phone'       => '0812-3001-4001',
                'position'    => 'Mandor Lapangan',
                'department'  => 'Produksi Pertanian',
                'join_date'   => Carbon::now()->subYears(3)->format('Y-m-d'),
                'salary'      => 3500000,
                'status'      => 'active',
            ],
            [
                'employee_id' => 'AGR-EMP-002',
                'name'        => 'Warsito Hadi',
                'email'       => 'warsito.hadi@demo-agr.local',
                'phone'       => '0813-3002-5002',
                'position'    => 'Pekerja Lapangan',
                'department'  => 'Produksi Pertanian',
                'join_date'   => Carbon::now()->subYears(2)->format('Y-m-d'),
                'salary'      => 2500000,
                'status'      => 'active',
            ],
            [
                'employee_id' => 'AGR-EMP-003',
                'name'        => 'Sumiati Dewi',
                'email'       => 'sumiati.dewi@demo-agr.local',
                'phone'       => '0814-3003-6003',
                'position'    => 'Pekerja Lapangan',
                'department'  => 'Produksi Pertanian',
                'join_date'   => Carbon::now()->subMonths(8)->format('Y-m-d'),
                'salary'      => 2500000,
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
