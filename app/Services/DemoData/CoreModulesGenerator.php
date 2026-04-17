<?php

namespace App\Services\DemoData;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CoreModulesGenerator
{
    /**
     * Generate all core module data for a tenant.
     * Execution order respects foreign key dependencies:
     * CoA → AccountingPeriod → Warehouse → TaxRates → CostCenters
     *     → Products → Customers → Suppliers → Employees
     */
    public function generate(int $tenantId): CoreDataContext
    {
        $ctx = new CoreDataContext(tenantId: $tenantId);

        $ctx->coaMap      = $this->seedCoA($tenantId);
        $ctx->periodId    = $this->seedAccountingPeriod($tenantId);
        $ctx->warehouseId = $this->seedWarehouse($tenantId);

        $this->seedTaxRates($tenantId);
        $this->seedCostCenters($tenantId);

        $ctx->productIds  = $this->seedProducts($tenantId, $ctx->warehouseId);
        $ctx->customerIds = $this->seedCustomers($tenantId);
        $ctx->supplierIds = $this->seedSuppliers($tenantId);
        $ctx->employeeIds = $this->seedEmployees($tenantId);

        // Count all records created for this tenant across core tables
        $ctx->recordsCreated =
            count($ctx->coaMap) +
            4 + // accounting periods
            1 + // warehouse
            4 + // tax rates
            6 + // cost centers
            count($ctx->productIds) +
            count($ctx->customerIds) +
            count($ctx->supplierIds) +
            count($ctx->employeeIds);

        return $ctx;
    }

    // ─────────────────────────────────────────────────────────────
    //  Chart of Accounts  (~35 accounts covering all 5 types)
    // ─────────────────────────────────────────────────────────────

    private function seedCoA(int $tenantId): array
    {
        $accounts = [
            // Assets
            ['code' => '1000', 'name' => 'ASET LANCAR',           'type' => 'asset',     'normal_balance' => 'debit',  'level' => 1, 'is_header' => true,  'parent_code' => null],
            ['code' => '1100', 'name' => 'Kas dan Setara Kas',    'type' => 'asset',     'normal_balance' => 'debit',  'level' => 2, 'is_header' => true,  'parent_code' => '1000'],
            ['code' => '1101', 'name' => 'Kas',                   'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1100'],
            ['code' => '1102', 'name' => 'Bank BCA',              'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1100'],
            ['code' => '1103', 'name' => 'Piutang Usaha',         'type' => 'asset',     'normal_balance' => 'debit',  'level' => 2, 'is_header' => false, 'parent_code' => '1000'],
            ['code' => '1104', 'name' => 'Persediaan',            'type' => 'asset',     'normal_balance' => 'debit',  'level' => 2, 'is_header' => false, 'parent_code' => '1000'],
            ['code' => '1105', 'name' => 'Uang Muka Pembelian',   'type' => 'asset',     'normal_balance' => 'debit',  'level' => 2, 'is_header' => false, 'parent_code' => '1000'],
            ['code' => '1106', 'name' => 'Biaya Dibayar Dimuka',  'type' => 'asset',     'normal_balance' => 'debit',  'level' => 2, 'is_header' => false, 'parent_code' => '1000'],
            ['code' => '1107', 'name' => 'PPN Masukan',           'type' => 'asset',     'normal_balance' => 'debit',  'level' => 2, 'is_header' => false, 'parent_code' => '1000'],
            ['code' => '1500', 'name' => 'ASET TETAP',            'type' => 'asset',     'normal_balance' => 'debit',  'level' => 1, 'is_header' => true,  'parent_code' => null],
            ['code' => '1501', 'name' => 'Kendaraan',             'type' => 'asset',     'normal_balance' => 'debit',  'level' => 2, 'is_header' => false, 'parent_code' => '1500'],
            ['code' => '1502', 'name' => 'Peralatan',             'type' => 'asset',     'normal_balance' => 'debit',  'level' => 2, 'is_header' => false, 'parent_code' => '1500'],
            ['code' => '1503', 'name' => 'Akumulasi Penyusutan',  'type' => 'asset',     'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'parent_code' => '1500'],
            // Liabilities
            ['code' => '2000', 'name' => 'LIABILITAS',            'type' => 'liability', 'normal_balance' => 'credit', 'level' => 1, 'is_header' => true,  'parent_code' => null],
            ['code' => '2100', 'name' => 'Utang Usaha',           'type' => 'liability', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'parent_code' => '2000'],
            ['code' => '2101', 'name' => 'Utang Pajak',           'type' => 'liability', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'parent_code' => '2000'],
            ['code' => '2102', 'name' => 'Utang Gaji',            'type' => 'liability', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'parent_code' => '2000'],
            ['code' => '2103', 'name' => 'PPN Keluaran',          'type' => 'liability', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'parent_code' => '2000'],
            ['code' => '2104', 'name' => 'Uang Muka Pelanggan',   'type' => 'liability', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'parent_code' => '2000'],
            // Equity
            ['code' => '3000', 'name' => 'EKUITAS',               'type' => 'equity',    'normal_balance' => 'credit', 'level' => 1, 'is_header' => true,  'parent_code' => null],
            ['code' => '3100', 'name' => 'Modal Disetor',         'type' => 'equity',    'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'parent_code' => '3000'],
            ['code' => '3200', 'name' => 'Laba Ditahan',          'type' => 'equity',    'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'parent_code' => '3000'],
            ['code' => '3300', 'name' => 'Laba Tahun Berjalan',   'type' => 'equity',    'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'parent_code' => '3000'],
            // Revenue
            ['code' => '4000', 'name' => 'PENDAPATAN',            'type' => 'revenue',   'normal_balance' => 'credit', 'level' => 1, 'is_header' => true,  'parent_code' => null],
            ['code' => '4100', 'name' => 'Penjualan',             'type' => 'revenue',   'normal_balance' => 'credit', 'level' => 2, 'is_header' => true,  'parent_code' => '4000'],
            ['code' => '4101', 'name' => 'Penjualan Produk',      'type' => 'revenue',   'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '4100'],
            ['code' => '4102', 'name' => 'Pendapatan Jasa',       'type' => 'revenue',   'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '4100'],
            ['code' => '4103', 'name' => 'Pendapatan Lain-lain',  'type' => 'revenue',   'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'parent_code' => '4000'],
            // Expenses
            ['code' => '5000', 'name' => 'BEBAN',                 'type' => 'expense',   'normal_balance' => 'debit',  'level' => 1, 'is_header' => true,  'parent_code' => null],
            ['code' => '5100', 'name' => 'Beban Pokok Penjualan', 'type' => 'expense',   'normal_balance' => 'debit',  'level' => 2, 'is_header' => false, 'parent_code' => '5000'],
            ['code' => '5200', 'name' => 'Beban Operasional',     'type' => 'expense',   'normal_balance' => 'debit',  'level' => 2, 'is_header' => true,  'parent_code' => '5000'],
            ['code' => '5201', 'name' => 'Beban Gaji',            'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5202', 'name' => 'Beban Sewa',            'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5203', 'name' => 'Beban Utilitas',        'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5204', 'name' => 'Beban Penyusutan',      'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5205', 'name' => 'Beban Pemasaran',       'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5206', 'name' => 'Beban Administrasi',    'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5207', 'name' => 'Beban Transportasi',    'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5208', 'name' => 'Beban Lain-lain',       'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
        ];

        foreach ($accounts as $coa) {
            if (DB::table('chart_of_accounts')
                ->where('tenant_id', $tenantId)
                ->where('code', $coa['code'])
                ->exists()
            ) {
                continue;
            }

            $parentId = null;
            if ($coa['parent_code'] !== null) {
                $parentId = DB::table('chart_of_accounts')
                    ->where('tenant_id', $tenantId)
                    ->where('code', $coa['parent_code'])
                    ->value('id');
            }

            DB::table('chart_of_accounts')->insert([
                'tenant_id'      => $tenantId,
                'code'           => $coa['code'],
                'name'           => $coa['name'],
                'type'           => $coa['type'],
                'normal_balance' => $coa['normal_balance'],
                'level'          => $coa['level'],
                'is_header'      => $coa['is_header'],
                'parent_id'      => $parentId,
                'is_active'      => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        // Build and return coaMap: code => id
        $coaMap = [];
        $rows = DB::table('chart_of_accounts')
            ->where('tenant_id', $tenantId)
            ->get(['id', 'code']);
        foreach ($rows as $row) {
            $coaMap[$row->code] = $row->id;
        }

        return $coaMap;
    }

    // ─────────────────────────────────────────────────────────────
    //  Accounting Periods  (2 closed + 2 open)
    // ─────────────────────────────────────────────────────────────

    private function seedAccountingPeriod(int $tenantId): int
    {
        $year  = now()->year;
        $month = now()->month;

        // Build 4 consecutive months: 2 before current (closed), current + next (open)
        $periods = [];
        for ($offset = -2; $offset <= 1; $offset++) {
            $date   = Carbon::now()->startOfMonth()->addMonths($offset);
            $status = $offset < 0 ? 'closed' : 'open';
            $periods[] = [
                'name'       => $date->translatedFormat('F Y') ?: $date->format('F Y'),
                'start_date' => $date->copy()->startOfMonth()->format('Y-m-d'),
                'end_date'   => $date->copy()->endOfMonth()->format('Y-m-d'),
                'status'     => $status,
            ];
        }

        foreach ($periods as $period) {
            DB::table('accounting_periods')->updateOrInsert(
                ['tenant_id' => $tenantId, 'name' => $period['name']],
                array_merge($period, [
                    'tenant_id'  => $tenantId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // Return the ID of the current (first open) period
        $currentPeriodName = Carbon::now()->startOfMonth()->format('F Y');
        $periodId = DB::table('accounting_periods')
            ->where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->value('id');

        return (int) $periodId;
    }

    // ─────────────────────────────────────────────────────────────
    //  Warehouse  (1 active main warehouse)
    // ─────────────────────────────────────────────────────────────

    private function seedWarehouse(int $tenantId): int
    {
        $existing = DB::table('warehouses')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            return (int) $existing->id;
        }

        $warehouseId = DB::table('warehouses')->insertGetId([
            'tenant_id'  => $tenantId,
            'name'       => 'Gudang Utama',
            'code'       => 'GDG-UTAMA',
            'address'    => 'Jl. Industri No. 1',
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) $warehouseId;
    }

    // ─────────────────────────────────────────────────────────────
    //  Tax Rates  (PPN, PPh 21, PPh 23, PPh Final)
    // ─────────────────────────────────────────────────────────────

    private function seedTaxRates(int $tenantId): void
    {
        $taxes = [
            ['name' => 'PPN 11%',        'code' => 'PPN',       'type' => 'ppn',       'tax_type' => 'ppn',       'rate' => 11.0,  'is_withholding' => false],
            ['name' => 'PPh 21',         'code' => 'PPH21',     'type' => 'pph21',     'tax_type' => 'pph21',     'rate' => 5.0,   'is_withholding' => true],
            ['name' => 'PPh 23',         'code' => 'PPH23',     'type' => 'pph23',     'tax_type' => 'pph23',     'rate' => 2.0,   'is_withholding' => true],
            ['name' => 'PPh Final 0.5%', 'code' => 'PPH_FINAL', 'type' => 'pph_final', 'tax_type' => 'pph4ayat2', 'rate' => 0.5,   'is_withholding' => false],
        ];

        foreach ($taxes as $tax) {
            DB::table('tax_rates')->updateOrInsert(
                ['tenant_id' => $tenantId, 'code' => $tax['code']],
                array_merge($tax, [
                    'tenant_id'  => $tenantId,
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  Cost Centers  (6 departments)
    // ─────────────────────────────────────────────────────────────

    private function seedCostCenters(int $tenantId): void
    {
        $centers = [
            ['code' => 'CC-OPS',   'name' => 'Operasional',   'type' => 'department'],
            ['code' => 'CC-SALES', 'name' => 'Penjualan',     'type' => 'department'],
            ['code' => 'CC-FIN',   'name' => 'Keuangan',      'type' => 'department'],
            ['code' => 'CC-HRD',   'name' => 'SDM & Umum',    'type' => 'department'],
            ['code' => 'CC-IT',    'name' => 'Teknologi',     'type' => 'department'],
            ['code' => 'CC-MFG',   'name' => 'Produksi',      'type' => 'department'],
        ];

        foreach ($centers as $center) {
            DB::table('cost_centers')->updateOrInsert(
                ['tenant_id' => $tenantId, 'code' => $center['code']],
                array_merge($center, [
                    'tenant_id'  => $tenantId,
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  Products  (10 products with initial stock > 0)
    // ─────────────────────────────────────────────────────────────

    private function seedProducts(int $tenantId, int $warehouseId): array
    {
        $products = [
            ['name' => 'Laptop Asus VivoBook 15',      'sku' => 'DEMO-LPT-001', 'category' => 'Elektronik',  'unit' => 'unit', 'price_buy' => 7500000,  'price_sell' => 9500000,  'stock_min' => 5,  'qty' => 25],
            ['name' => 'Laptop Lenovo IdeaPad 3',      'sku' => 'DEMO-LPT-002', 'category' => 'Elektronik',  'unit' => 'unit', 'price_buy' => 6800000,  'price_sell' => 8800000,  'stock_min' => 5,  'qty' => 20],
            ['name' => 'Monitor LG 24 inch FHD',       'sku' => 'DEMO-MON-001', 'category' => 'Elektronik',  'unit' => 'unit', 'price_buy' => 1800000,  'price_sell' => 2500000,  'stock_min' => 10, 'qty' => 40],
            ['name' => 'Keyboard Mechanical Rexus',    'sku' => 'DEMO-KBD-001', 'category' => 'Aksesoris',   'unit' => 'unit', 'price_buy' => 350000,   'price_sell' => 550000,   'stock_min' => 20, 'qty' => 60],
            ['name' => 'Mouse Wireless Logitech M235', 'sku' => 'DEMO-MSE-001', 'category' => 'Aksesoris',   'unit' => 'unit', 'price_buy' => 180000,   'price_sell' => 280000,   'stock_min' => 30, 'qty' => 80],
            ['name' => 'Printer Canon PIXMA G2020',    'sku' => 'DEMO-PRN-001', 'category' => 'Elektronik',  'unit' => 'unit', 'price_buy' => 1200000,  'price_sell' => 1650000,  'stock_min' => 8,  'qty' => 15],
            ['name' => 'UPS APC 650VA',                'sku' => 'DEMO-UPS-001', 'category' => 'Elektronik',  'unit' => 'unit', 'price_buy' => 650000,   'price_sell' => 950000,   'stock_min' => 10, 'qty' => 30],
            ['name' => 'Headset Gaming Rexus HX20',    'sku' => 'DEMO-HST-001', 'category' => 'Aksesoris',   'unit' => 'unit', 'price_buy' => 250000,   'price_sell' => 420000,   'stock_min' => 15, 'qty' => 50],
            ['name' => 'Flashdisk Sandisk 64GB',       'sku' => 'DEMO-FD-001',  'category' => 'Storage',     'unit' => 'pcs',  'price_buy' => 85000,    'price_sell' => 135000,   'stock_min' => 50, 'qty' => 100],
            ['name' => 'SSD External WD 1TB',          'sku' => 'DEMO-SSD-001', 'category' => 'Storage',     'unit' => 'unit', 'price_buy' => 750000,   'price_sell' => 1100000,  'stock_min' => 10, 'qty' => 35],
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

        // Bulk insert stock records (idempotent via insertOrIgnore)
        if (!empty($stockRows)) {
            DB::table('product_stocks')->insertOrIgnore($stockRows);
        }

        return $productIds;
    }

    // ─────────────────────────────────────────────────────────────
    //  Customers  (5 active customers)
    // ─────────────────────────────────────────────────────────────

    private function seedCustomers(int $tenantId): array
    {
        $customers = [
            ['name' => 'PT Teknologi Nusantara',  'email' => 'procurement@demo-teknologi.co.id', 'phone' => '021-7654321', 'company' => 'PT Teknologi Nusantara',  'credit_limit' => 100000000],
            ['name' => 'CV Mitra Komputer',        'email' => 'order@demo-mitrakomputer.com',     'phone' => '022-8765432', 'company' => 'CV Mitra Komputer',        'credit_limit' => 50000000],
            ['name' => 'Toko Elektronik Jaya',     'email' => 'toko@demo-elektronikjaya.com',     'phone' => '031-9876543', 'company' => 'Toko Elektronik Jaya',     'credit_limit' => 30000000],
            ['name' => 'Budi Prasetyo',            'email' => 'budi.prasetyo@demo-example.com',   'phone' => '0812-3456789', 'company' => null,                      'credit_limit' => 5000000],
            ['name' => 'PT Solusi Digital Prima',  'email' => 'finance@demo-solusidigital.id',    'phone' => '021-5544332', 'company' => 'PT Solusi Digital Prima',  'credit_limit' => 200000000],
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
                'address'      => 'Jakarta',
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
    //  Suppliers  (4 active suppliers)
    // ─────────────────────────────────────────────────────────────

    private function seedSuppliers(int $tenantId): array
    {
        $suppliers = [
            ['name' => 'PT Asus Indonesia',          'email' => 'sales@demo-asus.co.id',          'phone' => '021-1234567', 'company' => 'PT Asus Indonesia',          'bank_name' => 'BCA',     'bank_account' => '0987654321'],
            ['name' => 'PT Lenovo Indonesia',         'email' => 'order@demo-lenovo.co.id',         'phone' => '021-2345678', 'company' => 'PT Lenovo Indonesia',         'bank_name' => 'Mandiri', 'bank_account' => '1122334455'],
            ['name' => 'PT LG Electronics',           'email' => 'b2b@demo-lge.co.id',              'phone' => '021-3456789', 'company' => 'PT LG Electronics',           'bank_name' => 'BNI',     'bank_account' => '5566778899'],
            ['name' => 'CV Distributor Aksesoris',    'email' => 'sales@demo-distaksesoris.com',    'phone' => '022-4567890', 'company' => 'CV Distributor Aksesoris',    'bank_name' => 'BRI',     'bank_account' => '9988776655'],
        ];

        $supplierIds = [];

        foreach ($suppliers as $s) {
            $existing = DB::table('suppliers')
                ->where('tenant_id', $tenantId)
                ->where('email', $s['email'])
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                $supplierIds[] = (int) $existing->id;
                continue;
            }

            $id = DB::table('suppliers')->insertGetId([
                'tenant_id'    => $tenantId,
                'name'         => $s['name'],
                'email'        => $s['email'],
                'phone'        => $s['phone'],
                'company'      => $s['company'],
                'bank_name'    => $s['bank_name'],
                'bank_account' => $s['bank_account'],
                'bank_holder'  => $s['company'],
                'is_active'    => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            $supplierIds[] = (int) $id;
        }

        return $supplierIds;
    }

    // ─────────────────────────────────────────────────────────────
    //  Employees  (5 employees with different roles/positions)
    // ─────────────────────────────────────────────────────────────

    private function seedEmployees(int $tenantId): array
    {
        $employees = [
            ['employee_id' => 'DEMO-EMP-001', 'name' => 'Budi Santoso',   'position' => 'Direktur',            'department' => 'Manajemen',  'salary' => 25000000, 'status' => 'active'],
            ['employee_id' => 'DEMO-EMP-002', 'name' => 'Siti Rahayu',    'position' => 'Manajer Operasional', 'department' => 'Operasional', 'salary' => 15000000, 'status' => 'active'],
            ['employee_id' => 'DEMO-EMP-003', 'name' => 'Andi Wijaya',    'position' => 'Staff Penjualan',     'department' => 'Penjualan',   'salary' => 6000000,  'status' => 'active'],
            ['employee_id' => 'DEMO-EMP-004', 'name' => 'Dewi Lestari',   'position' => 'Kasir',               'department' => 'Keuangan',    'salary' => 5000000,  'status' => 'active'],
            ['employee_id' => 'DEMO-EMP-005', 'name' => 'Rudi Hartono',   'position' => 'Staff Gudang',        'department' => 'Logistik',    'salary' => 5500000,  'status' => 'active'],
        ];

        $employeeIds = [];
        $firstId     = null;

        foreach ($employees as $i => $e) {
            $existing = DB::table('employees')
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $e['employee_id'])
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                $employeeIds[] = (int) $existing->id;
                if ($i === 0) {
                    $firstId = (int) $existing->id;
                }
                continue;
            }

            $id = DB::table('employees')->insertGetId([
                'tenant_id'   => $tenantId,
                'employee_id' => $e['employee_id'],
                'name'        => $e['name'],
                'email'       => strtolower(str_replace(' ', '.', $e['name'])) . '@demo-company.com',
                'phone'       => '0812-' . str_pad((string) (10000000 + $i * 1111111), 8, '0', STR_PAD_LEFT),
                'position'    => $e['position'],
                'department'  => $e['department'],
                'join_date'   => Carbon::now()->subMonths(12 + $i * 3)->format('Y-m-d'),
                'status'      => $e['status'],
                'salary'      => $e['salary'],
                'manager_id'  => ($i > 0 && $firstId) ? $firstId : null,
                'bank_name'   => 'BCA',
                'bank_account'=> '1' . str_pad((string) (100000000 + $i * 111111111), 9, '0', STR_PAD_LEFT),
                'address'     => 'Jakarta',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            if ($i === 0) {
                $firstId = (int) $id;
            }

            $employeeIds[] = (int) $id;
        }

        return $employeeIds;
    }
}
