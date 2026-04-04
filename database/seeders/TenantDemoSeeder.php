<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * TenantDemoSeeder — Data demo lengkap untuk 1 tenant.
 * Jalankan: php artisan db:seed --class=TenantDemoSeeder
 */
class TenantDemoSeeder extends Seeder
{
    private int $tenantId;
    private int $adminId;
    private int $managerId;
    private int $staffId;
    private array $productIds  = [];
    private array $customerIds = [];
    private array $supplierIds = [];
    private array $employeeIds = [];
    private array $coaMap      = [];
    private int $warehouseId;
    private int $periodId;

    public function run(): void
    {
        $this->command->info('Memulai TenantDemoSeeder...');

        DB::transaction(function () {
            $this->seedTenant();
            $this->seedUsers();
            $this->seedCoa();
            $this->seedAccountingPeriod();
            $this->seedWarehouse();
            $this->seedTaxRates();
            $this->seedCostCenters();
            $this->seedBusinessConstraints();
            $this->seedExpenseCategories();
            $this->seedProducts();
            $this->seedCustomers();
            $this->seedSuppliers();
            $this->seedPriceList();
            $this->seedLoyaltyProgram();
            $this->seedEmployees();
            $this->seedWorkShifts();
            $this->seedAttendances();
            $this->seedLeaveRequests();
            $this->seedPerformanceReviews();
            $this->seedRecruitment();
            $this->seedTraining();
            $this->seedDisciplinary();
            $this->seedSalaryComponents();
            $this->seedPayroll();
            $this->seedPurchaseOrders();
            $this->seedSalesOrders();
            $this->seedInvoicesAndPayables();
            $this->seedBankAccount();
            $this->seedJournalEntries();
            $this->seedAssets();
            $this->seedBudgets();
            $this->seedKpiTargets();
            $this->seedCrmLeads();
            $this->seedApprovalWorkflow();
            $this->seedTransactions();
            $this->seedSimulation();
            $this->seedDeferredItem();
            $this->seedWriteoff();
            $this->seedAiMemory();
            $this->seedReminders();
            $this->seedNotifications();
            $this->seedCustomFields();
            $this->seedCompanyGroup();
            $this->seedDocumentTemplate();

            // New modules (Task 44+)
            $this->seedManufacturing();
            $this->seedFleet();
            $this->seedContracts();
            $this->seedConsignment();
            $this->seedCommission();
            $this->seedHelpdesk();
            $this->seedSubscriptionBilling();
        });

        $this->command->info('TenantDemoSeeder selesai!');
        $this->command->table(['Item', 'Value'], [
            ['Tenant',        'PT Maju Bersama Indonesia'],
            ['Admin Email',   'admin@majubersama.com'],
            ['Manager Email', 'manager@majubersama.com'],
            ['Staff Email',   'staff@majubersama.com'],
            ['Password',      'password'],
        ]);
    }

    private function seedTenant(): void
    {
        $tenant = DB::table('tenants')->where('slug', 'majubersama')->first();
        if (!$tenant) {
            $this->tenantId = DB::table('tenants')->insertGetId([
                'name'                  => 'PT Maju Bersama Indonesia',
                'slug'                  => 'majubersama',
                'email'                 => 'info@majubersama.com',
                'phone'                 => '021-55512345',
                'address'               => 'Jl. Sudirman No. 88, Jakarta Selatan',
                'plan'                  => 'pro',
                'is_active'             => true,
                'trial_ends_at'         => null,
                'plan_expires_at'       => Carbon::now()->addYear(),
                'business_type'         => 'distributor',
                'business_description'  => 'Distributor elektronik dan peralatan rumah tangga skala nasional',
                'onboarding_completed'  => true,
                'costing_method'        => 'avco',
                'npwp'                  => '01.234.567.8-901.000',
                'website'               => 'https://majubersama.com',
                'city'                  => 'Jakarta Selatan',
                'province'              => 'DKI Jakarta',
                'postal_code'           => '12190',
                'bank_name'             => 'BCA',
                'bank_account'          => '1234567890',
                'bank_account_name'     => 'PT Maju Bersama Indonesia',
                'tagline'               => 'Maju Bersama, Sukses Bersama',
                'invoice_footer_notes'  => 'Terima kasih. Transfer ke BCA 1234567890 a/n PT Maju Bersama Indonesia.',
                'invoice_payment_terms' => 'Net 30',
                'letter_head_color'     => '#1e40af',
                'doc_number_prefix'     => 'MBI',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $this->tenantId = $tenant->id;
        }
    }

    private function seedUsers(): void
    {
        $users = [
            ['email' => 'admin@majubersama.com',   'name' => 'Budi Santoso',  'role' => 'admin'],
            ['email' => 'manager@majubersama.com', 'name' => 'Siti Rahayu',   'role' => 'manager'],
            ['email' => 'staff@majubersama.com',   'name' => 'Andi Wijaya',   'role' => 'staff'],
            ['email' => 'kasir@majubersama.com',   'name' => 'Dewi Lestari',  'role' => 'kasir'],
            ['email' => 'gudang@majubersama.com',  'name' => 'Rudi Hartono',  'role' => 'gudang'],
        ];
        foreach ($users as $u) {
            $row = DB::table('users')->where('email', $u['email'])->first();
            if (!$row) {
                $id = DB::table('users')->insertGetId([
                    'tenant_id'         => $this->tenantId,
                    'name'              => $u['name'],
                    'email'             => $u['email'],
                    'email_verified_at' => now(),
                    'password'          => Hash::make('password'),
                    'role'              => $u['role'],
                    'is_active'         => true,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            } else {
                $id = $row->id;
            }
            match ($u['role']) {
                'admin'   => $this->adminId   = $id,
                'manager' => $this->managerId = $id,
                'staff'   => $this->staffId   = $id,
                default   => null,
            };
        }
    }

    private function seedCoa(): void
    {
        DefaultCoaSeeder::seedForTenant($this->tenantId);
        $accounts = DB::table('chart_of_accounts')->where('tenant_id', $this->tenantId)->get();
        foreach ($accounts as $acc) {
            $this->coaMap[$acc->code] = $acc->id;
        }
    }

    private function seedAccountingPeriod(): void
    {
        $row = DB::table('accounting_periods')
            ->where('tenant_id', $this->tenantId)->where('name', 'Maret 2026')->first();
        if (!$row) {
            DB::table('accounting_periods')->insert([
                ['tenant_id' => $this->tenantId, 'name' => 'Januari 2026',  'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'status' => 'closed', 'created_at' => now(), 'updated_at' => now()],
                ['tenant_id' => $this->tenantId, 'name' => 'Februari 2026', 'start_date' => '2026-02-01', 'end_date' => '2026-02-28', 'status' => 'closed', 'created_at' => now(), 'updated_at' => now()],
                ['tenant_id' => $this->tenantId, 'name' => 'Maret 2026',    'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'status' => 'open',   'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        $this->periodId = DB::table('accounting_periods')
            ->where('tenant_id', $this->tenantId)->where('name', 'Maret 2026')->value('id');
    }

    private function seedWarehouse(): void
    {
        $wh = DB::table('warehouses')->where('tenant_id', $this->tenantId)->first();
        if (!$wh) {
            $this->warehouseId = DB::table('warehouses')->insertGetId([
                'tenant_id' => $this->tenantId,
                'name' => 'Gudang Utama Jakarta',
                'code' => 'GDG-JKT',
                'address' => 'Jl. Raya Bekasi KM 18, Jakarta Timur',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('warehouses')->insert([
                'tenant_id' => $this->tenantId,
                'name' => 'Gudang Cabang Surabaya',
                'code' => 'GDG-SBY',
                'address' => 'Jl. Rungkut Industri No. 5, Surabaya',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $this->warehouseId = $wh->id;
        }
    }

    private function seedTaxRates(): void
    {
        $taxes = [
            ['name' => 'PPN 11%',        'code' => 'PPN',       'type' => 'ppn',       'rate' => 11.0000],
            ['name' => 'PPh 21',         'code' => 'PPH21',     'type' => 'pph21',     'rate' => 5.0000],
            ['name' => 'PPh 23',         'code' => 'PPH23',     'type' => 'pph23',     'rate' => 2.0000],
            ['name' => 'PPh Final 0.5%', 'code' => 'PPH_FINAL', 'type' => 'pph_final', 'rate' => 0.5000],
        ];
        foreach ($taxes as $t) {
            DB::table('tax_rates')->updateOrInsert(
                ['tenant_id' => $this->tenantId, 'code' => $t['code']],
                array_merge($t, ['tenant_id' => $this->tenantId, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedCostCenters(): void
    {
        $centers = [
            ['code' => 'CC-OPS',   'name' => 'Operasional', 'type' => 'department'],
            ['code' => 'CC-SALES', 'name' => 'Penjualan',   'type' => 'department'],
            ['code' => 'CC-FIN',   'name' => 'Keuangan',    'type' => 'department'],
            ['code' => 'CC-HRD',   'name' => 'SDM & Umum',  'type' => 'department'],
            ['code' => 'CC-IT',    'name' => 'Teknologi',   'type' => 'department'],
        ];
        foreach ($centers as $c) {
            DB::table('cost_centers')->updateOrInsert(
                ['tenant_id' => $this->tenantId, 'code' => $c['code']],
                array_merge($c, ['tenant_id' => $this->tenantId, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedBusinessConstraints(): void
    {
        $rows = [
            ['key' => 'no_sell_below_cost',  'label' => 'Larang jual di bawah HPP',      'value_type' => 'boolean',    'value' => 'true'],
            ['key' => 'max_discount_pct',     'label' => 'Diskon maksimum (%)',            'value_type' => 'percentage', 'value' => '20'],
            ['key' => 'min_cash_balance',     'label' => 'Saldo kas minimum (Rp)',         'value_type' => 'amount',     'value' => '5000000'],
            ['key' => 'confirm_above_amount', 'label' => 'Konfirmasi order di atas (Rp)', 'value_type' => 'amount',     'value' => '50000000'],
            ['key' => 'auto_approve_po',      'label' => 'Auto-approve PO di bawah (Rp)', 'value_type' => 'amount',     'value' => '10000000'],
        ];
        foreach ($rows as $r) {
            DB::table('business_constraints')->updateOrInsert(
                ['tenant_id' => $this->tenantId, 'key' => $r['key']],
                array_merge($r, ['tenant_id' => $this->tenantId, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedExpenseCategories(): void
    {
        $cats = [
            ['name' => 'Penjualan Produk',  'code' => 'SALES',    'type' => 'income',  'coa_code' => '4101'],
            ['name' => 'Pendapatan Jasa',   'code' => 'SERVICE',  'type' => 'income',  'coa_code' => '4102'],
            ['name' => 'Gaji & Tunjangan',  'code' => 'SALARY',   'type' => 'expense', 'coa_code' => '5201'],
            ['name' => 'Sewa Kantor',       'code' => 'RENT',     'type' => 'expense', 'coa_code' => '5202'],
            ['name' => 'Listrik & Air',     'code' => 'UTILITY',  'type' => 'expense', 'coa_code' => '5203'],
            ['name' => 'Pemasaran & Iklan', 'code' => 'MARKETING', 'type' => 'expense', 'coa_code' => '5205'],
            ['name' => 'Administrasi',      'code' => 'ADMIN',    'type' => 'expense', 'coa_code' => '5206'],
            ['name' => 'Transportasi',      'code' => 'TRANSPORT', 'type' => 'expense', 'coa_code' => '5208'],
        ];
        foreach ($cats as $c) {
            DB::table('expense_categories')->updateOrInsert(
                ['tenant_id' => $this->tenantId, 'code' => $c['code']],
                [
                    'tenant_id'        => $this->tenantId,
                    'name'             => $c['name'],
                    'code'             => $c['code'],
                    'type'             => $c['type'],
                    'coa_account_code' => $c['coa_code'],
                    'is_active'        => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]
            );
        }
    }

    private function seedProducts(): void
    {
        $products = [
            ['name' => 'Laptop Asus VivoBook 15',     'sku' => 'LPT-ASUS-001', 'category' => 'Elektronik', 'unit' => 'unit', 'price_buy' => 7500000,  'price_sell' => 9500000,  'stock_min' => 5],
            ['name' => 'Laptop Lenovo IdeaPad 3',     'sku' => 'LPT-LNV-001',  'category' => 'Elektronik', 'unit' => 'unit', 'price_buy' => 6800000,  'price_sell' => 8800000,  'stock_min' => 5],
            ['name' => 'Monitor LG 24 inch FHD',      'sku' => 'MON-LG-001',   'category' => 'Elektronik', 'unit' => 'unit', 'price_buy' => 1800000,  'price_sell' => 2500000,  'stock_min' => 10],
            ['name' => 'Keyboard Mechanical Rexus',   'sku' => 'KBD-REX-001',  'category' => 'Aksesoris',  'unit' => 'unit', 'price_buy' => 350000,   'price_sell' => 550000,   'stock_min' => 20],
            ['name' => 'Mouse Wireless Logitech M235', 'sku' => 'MSE-LOG-001',   'category' => 'Aksesoris',  'unit' => 'unit', 'price_buy' => 180000,   'price_sell' => 280000,   'stock_min' => 30],
            ['name' => 'Printer Canon PIXMA G2020',   'sku' => 'PRN-CAN-001',  'category' => 'Elektronik', 'unit' => 'unit', 'price_buy' => 1200000,  'price_sell' => 1650000,  'stock_min' => 8],
            ['name' => 'UPS APC 650VA',               'sku' => 'UPS-APC-001',  'category' => 'Elektronik', 'unit' => 'unit', 'price_buy' => 650000,   'price_sell' => 950000,   'stock_min' => 10],
            ['name' => 'Headset Gaming Rexus HX20',   'sku' => 'HST-REX-001',  'category' => 'Aksesoris',  'unit' => 'unit', 'price_buy' => 250000,   'price_sell' => 420000,   'stock_min' => 15],
            ['name' => 'Flashdisk Sandisk 64GB',      'sku' => 'FD-SND-001',   'category' => 'Storage',    'unit' => 'pcs',  'price_buy' => 85000,    'price_sell' => 135000,   'stock_min' => 50],
            ['name' => 'SSD External WD 1TB',         'sku' => 'SSD-WD-001',   'category' => 'Storage',    'unit' => 'unit', 'price_buy' => 750000,   'price_sell' => 1100000,  'stock_min' => 10],
        ];
        foreach ($products as $p) {
            $row = DB::table('products')->where('tenant_id', $this->tenantId)->where('sku', $p['sku'])->first();
            if (!$row) {
                $qty = rand(20, 100);
                $id = DB::table('products')->insertGetId(array_merge($p, [
                    'tenant_id' => $this->tenantId,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                DB::table('product_stocks')->insertOrIgnore([
                    'product_id' => $id,
                    'warehouse_id' => $this->warehouseId,
                    'quantity' => $qty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('stock_movements')->insert([
                    'tenant_id' => $this->tenantId,
                    'product_id' => $id,
                    'warehouse_id' => $this->warehouseId,
                    'user_id' => $this->adminId,
                    'type' => 'in',
                    'quantity' => $qty,
                    'quantity_before' => 0,
                    'quantity_after' => $qty,
                    'reference' => 'OPENING-STOCK',
                    'notes' => 'Stok awal',
                    'created_at' => Carbon::now()->subDays(30),
                    'updated_at' => Carbon::now()->subDays(30),
                ]);
            } else {
                $id = $row->id;
            }
            $this->productIds[] = $id;
        }
    }

    private function seedCustomers(): void
    {
        $customers = [
            ['name' => 'PT Teknologi Nusantara',  'email' => 'procurement@teknologi-nusantara.co.id', 'phone' => '021-7654321', 'company' => 'PT Teknologi Nusantara',  'credit_limit' => 100000000],
            ['name' => 'CV Mitra Komputer',        'email' => 'order@mitrakomputer.com',               'phone' => '022-8765432', 'company' => 'CV Mitra Komputer',        'credit_limit' => 50000000],
            ['name' => 'Toko Elektronik Jaya',     'email' => 'toko@elektronikjaya.com',               'phone' => '031-9876543', 'company' => 'Toko Elektronik Jaya',     'credit_limit' => 30000000],
            ['name' => 'Budi Prasetyo',            'email' => 'budi.prasetyo@gmail.com',               'phone' => '0812-3456789', 'company' => null,                        'credit_limit' => 5000000],
            ['name' => 'PT Solusi Digital Prima',  'email' => 'finance@solusidigital.id',              'phone' => '021-5544332', 'company' => 'PT Solusi Digital Prima',  'credit_limit' => 200000000],
        ];
        foreach ($customers as $c) {
            $row = DB::table('customers')->where('tenant_id', $this->tenantId)->where('email', $c['email'])->first();
            if (!$row) {
                $id = DB::table('customers')->insertGetId(array_merge($c, [
                    'tenant_id' => $this->tenantId,
                    'address' => 'Jakarta',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            } else {
                $id = $row->id;
            }
            $this->customerIds[] = $id;
        }
    }

    private function seedSuppliers(): void
    {
        $suppliers = [
            ['name' => 'PT Asus Indonesia',        'email' => 'sales@asus.co.id',        'phone' => '021-1234567', 'company' => 'PT Asus Indonesia',        'bank_name' => 'BCA',     'bank_account' => '0987654321'],
            ['name' => 'PT Lenovo Indonesia',      'email' => 'order@lenovo.co.id',      'phone' => '021-2345678', 'company' => 'PT Lenovo Indonesia',      'bank_name' => 'Mandiri', 'bank_account' => '1122334455'],
            ['name' => 'PT LG Electronics',        'email' => 'b2b@lge.co.id',           'phone' => '021-3456789', 'company' => 'PT LG Electronics',        'bank_name' => 'BNI',     'bank_account' => '5566778899'],
            ['name' => 'CV Distributor Aksesoris', 'email' => 'sales@distaksesoris.com', 'phone' => '022-4567890', 'company' => 'CV Distributor Aksesoris', 'bank_name' => 'BRI',     'bank_account' => '9988776655'],
        ];
        foreach ($suppliers as $s) {
            $row = DB::table('suppliers')->where('tenant_id', $this->tenantId)->where('email', $s['email'])->first();
            if (!$row) {
                $id = DB::table('suppliers')->insertGetId(array_merge($s, [
                    'tenant_id' => $this->tenantId,
                    'bank_holder' => $s['company'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            } else {
                $id = $row->id;
            }
            $this->supplierIds[] = $id;
        }
    }

    private function seedPriceList(): void
    {
        if (DB::table('price_lists')->where('tenant_id', $this->tenantId)->exists()) return;
        $plId = DB::table('price_lists')->insertGetId([
            'tenant_id' => $this->tenantId,
            'name' => 'Harga Reseller',
            'code' => 'PL-RESELLER',
            'type' => 'tier',
            'description' => 'Harga khusus reseller terdaftar',
            'valid_from' => '2026-01-01',
            'valid_until' => '2026-12-31',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        foreach (array_slice($this->productIds, 0, 3) as $pid) {
            $product = DB::table('products')->find($pid);
            DB::table('price_list_items')->insertOrIgnore([
                'price_list_id' => $plId,
                'product_id' => $pid,
                'price' => $product->price_sell * 0.9,
                'discount_percent' => 10,
                'min_qty' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        if (!empty($this->customerIds)) {
            DB::table('customer_price_lists')->insertOrIgnore([
                'customer_id' => $this->customerIds[0],
                'price_list_id' => $plId,
                'priority' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedLoyaltyProgram(): void
    {
        if (DB::table('loyalty_programs')->where('tenant_id', $this->tenantId)->exists()) return;
        $progId = DB::table('loyalty_programs')->insertGetId([
            'tenant_id' => $this->tenantId,
            'name' => 'Program Poin MBI',
            'points_per_idr' => 0.01,
            'idr_per_point' => 100,
            'min_redeem_points' => 500,
            'expiry_days' => 365,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        foreach (
            [
                ['name' => 'Bronze',   'min_points' => 0,     'multiplier' => 1.0, 'color' => '#cd7f32'],
                ['name' => 'Silver',   'min_points' => 1000,  'multiplier' => 1.5, 'color' => '#c0c0c0'],
                ['name' => 'Gold',     'min_points' => 5000,  'multiplier' => 2.0, 'color' => '#ffd700'],
                ['name' => 'Platinum', 'min_points' => 15000, 'multiplier' => 3.0, 'color' => '#e5e4e2'],
            ] as $t
        ) {
            DB::table('loyalty_tiers')->insert(array_merge($t, [
                'tenant_id' => $this->tenantId,
                'program_id' => $progId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
        foreach (array_slice($this->customerIds, 0, 2) as $cid) {
            DB::table('loyalty_points')->insertOrIgnore([
                'tenant_id' => $this->tenantId,
                'customer_id' => $cid,
                'program_id' => $progId,
                'total_points' => rand(500, 8000),
                'lifetime_points' => rand(1000, 20000),
                'tier' => 'Silver',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedEmployees(): void
    {
        $employees = [
            ['name' => 'Budi Santoso',  'eid' => 'EMP-001', 'position' => 'Direktur',            'dept' => 'Manajemen',  'salary' => 25000000, 'uid' => $this->adminId],
            ['name' => 'Siti Rahayu',   'eid' => 'EMP-002', 'position' => 'Manajer Operasional',  'dept' => 'Operasional', 'salary' => 15000000, 'uid' => $this->managerId],
            ['name' => 'Andi Wijaya',   'eid' => 'EMP-003', 'position' => 'Staff Penjualan',      'dept' => 'Penjualan',  'salary' => 6000000,  'uid' => $this->staffId],
            ['name' => 'Dewi Lestari',  'eid' => 'EMP-004', 'position' => 'Kasir',                'dept' => 'Keuangan',   'salary' => 5000000,  'uid' => null],
            ['name' => 'Rudi Hartono',  'eid' => 'EMP-005', 'position' => 'Staff Gudang',         'dept' => 'Logistik',   'salary' => 5500000,  'uid' => null],
            ['name' => 'Maya Putri',    'eid' => 'EMP-006', 'position' => 'Staff Akuntansi',      'dept' => 'Keuangan',   'salary' => 7000000,  'uid' => null],
            ['name' => 'Hendra Kusuma', 'eid' => 'EMP-007', 'position' => 'Staff IT',             'dept' => 'IT',         'salary' => 8000000,  'uid' => null],
            ['name' => 'Rina Susanti',  'eid' => 'EMP-008', 'position' => 'HRD Officer',          'dept' => 'SDM',        'salary' => 7500000,  'uid' => null],
        ];
        $firstId = null;
        foreach ($employees as $i => $e) {
            $row = DB::table('employees')->where('tenant_id', $this->tenantId)->where('employee_id', $e['eid'])->first();
            if (!$row) {
                $id = DB::table('employees')->insertGetId([
                    'tenant_id'   => $this->tenantId,
                    'user_id'     => $e['uid'],
                    'manager_id'  => ($i > 0 && $firstId) ? $firstId : null,
                    'employee_id' => $e['eid'],
                    'name'        => $e['name'],
                    'email'       => strtolower(str_replace(' ', '.', $e['name'])) . '@majubersama.com',
                    'phone'       => '0812-' . rand(10000000, 99999999),
                    'position'    => $e['position'],
                    'department'  => $e['dept'],
                    'join_date'   => Carbon::now()->subMonths(rand(6, 36))->format('Y-m-d'),
                    'status'      => 'active',
                    'salary'      => $e['salary'],
                    'bank_name'   => 'BCA',
                    'bank_account' => '1' . rand(100000000, 999999999),
                    'address'     => 'Jakarta',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            } else {
                $id = $row->id;
            }
            if ($i === 0) $firstId = $id;
            $this->employeeIds[] = $id;
        }
    }

    private function seedWorkShifts(): void
    {
        $shifts = [
            ['name' => 'Shift Pagi',  'start_time' => '08:00', 'end_time' => '16:00', 'color' => '#3b82f6', 'crosses_midnight' => false],
            ['name' => 'Shift Siang', 'start_time' => '12:00', 'end_time' => '20:00', 'color' => '#f59e0b', 'crosses_midnight' => false],
            ['name' => 'Shift Malam', 'start_time' => '20:00', 'end_time' => '04:00', 'color' => '#6366f1', 'crosses_midnight' => true],
        ];
        foreach ($shifts as $s) {
            DB::table('work_shifts')->updateOrInsert(
                ['tenant_id' => $this->tenantId, 'name' => $s['name']],
                array_merge($s, ['tenant_id' => $this->tenantId, 'break_minutes' => 60, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedAttendances(): void
    {
        if (empty($this->employeeIds)) return;
        $statuses = ['present', 'present', 'present', 'present', 'late', 'absent'];
        $empId = $this->employeeIds[0];
        for ($d = 20; $d >= 1; $d--) {
            $date = Carbon::now()->subDays($d)->format('Y-m-d');
            DB::table('attendances')->updateOrInsert(
                ['employee_id' => $empId, 'date' => $date],
                [
                    'tenant_id'   => $this->tenantId,
                    'employee_id' => $empId,
                    'date'        => $date,
                    'check_in'    => '08:' . str_pad(rand(0, 15), 2, '0', STR_PAD_LEFT) . ':00',
                    'check_out'   => '17:' . str_pad(rand(0, 30), 2, '0', STR_PAD_LEFT) . ':00',
                    'status'      => $statuses[array_rand($statuses)],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }
    }

    private function seedLeaveRequests(): void
    {
        if (count($this->employeeIds) < 3) return;
        $leaves = [
            ['employee_id' => $this->employeeIds[2], 'type' => 'annual', 'start_date' => '2026-03-10', 'end_date' => '2026-03-12', 'days' => 3, 'status' => 'approved'],
            ['employee_id' => $this->employeeIds[3] ?? $this->employeeIds[0], 'type' => 'sick',   'start_date' => '2026-03-15', 'end_date' => '2026-03-15', 'days' => 1, 'status' => 'approved'],
            ['employee_id' => $this->employeeIds[4] ?? $this->employeeIds[0], 'type' => 'annual', 'start_date' => '2026-03-25', 'end_date' => '2026-03-26', 'days' => 2, 'status' => 'pending'],
        ];
        foreach ($leaves as $l) {
            DB::table('leave_requests')->updateOrInsert(
                ['tenant_id' => $this->tenantId, 'employee_id' => $l['employee_id'], 'start_date' => $l['start_date']],
                array_merge($l, [
                    'tenant_id'   => $this->tenantId,
                    'reason'      => 'Keperluan pribadi',
                    'approved_by' => $l['status'] === 'approved' ? ($this->employeeIds[1] ?? null) : null,
                    'approved_at' => $l['status'] === 'approved' ? now() : null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ])
            );
        }
    }

    private function seedPerformanceReviews(): void
    {
        if (count($this->employeeIds) < 2) return;
        DB::table('performance_reviews')->updateOrInsert(
            ['employee_id' => $this->employeeIds[2] ?? $this->employeeIds[0], 'period' => 'Q1 2026', 'period_type' => 'quarterly'],
            [
                'tenant_id'           => $this->tenantId,
                'employee_id'         => $this->employeeIds[2] ?? $this->employeeIds[0],
                'reviewer_id'         => $this->employeeIds[1],
                'period'              => 'Q1 2026',
                'period_type'         => 'quarterly',
                'score_work_quality'  => 4,
                'score_productivity'  => 4,
                'score_teamwork'      => 5,
                'score_initiative'    => 3,
                'score_attendance'    => 4,
                'overall_score'       => 4.00,
                'strengths'           => 'Komunikasi baik, target penjualan tercapai 110%',
                'improvements'        => 'Perlu meningkatkan pengetahuan produk baru',
                'goals_next_period'   => 'Target penjualan Q2 naik 15%',
                'recommendation'      => 'retain',
                'status'              => 'submitted',
                'submitted_at'        => now(),
                'created_at'          => now(),
                'updated_at'          => now(),
            ]
        );
    }

    private function seedRecruitment(): void
    {
        if (DB::table('job_postings')->where('tenant_id', $this->tenantId)->exists()) return;
        $jobId = DB::table('job_postings')->insertGetId([
            'tenant_id'    => $this->tenantId,
            'title'        => 'Sales Executive',
            'department'   => 'Penjualan',
            'location'     => 'Jakarta',
            'type'         => 'full_time',
            'description'  => 'Mencari Sales Executive berpengalaman untuk area Jabodetabek',
            'requirements' => 'Min. D3, pengalaman sales 2 tahun, memiliki kendaraan',
            'salary_min'   => 5000000,
            'salary_max'   => 8000000,
            'quota'        => 2,
            'deadline'     => Carbon::now()->addDays(30)->format('Y-m-d'),
            'status'       => 'open',
            'created_by'   => $this->adminId,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
        foreach (
            [
                ['name' => 'Fajar Nugroho', 'email' => 'fajar@gmail.com', 'stage' => 'interview'],
                ['name' => 'Lina Marlina',  'email' => 'lina@gmail.com',  'stage' => 'screening'],
                ['name' => 'Doni Setiawan', 'email' => 'doni@gmail.com',  'stage' => 'applied'],
            ] as $a
        ) {
            DB::table('job_applications')->insert([
                'tenant_id'       => $this->tenantId,
                'job_posting_id'  => $jobId,
                'applicant_name'  => $a['name'],
                'applicant_email' => $a['email'],
                'applicant_phone' => '0812-' . rand(10000000, 99999999),
                'cover_letter'    => 'Saya tertarik dengan posisi ini karena pengalaman saya di bidang penjualan.',
                'stage'           => $a['stage'],
                'reviewed_by'     => $this->adminId,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    private function seedTraining(): void
    {
        if (DB::table('training_programs')->where('tenant_id', $this->tenantId)->exists()) return;
        $progId = DB::table('training_programs')->insertGetId([
            'tenant_id'      => $this->tenantId,
            'name'           => 'Pelatihan Teknik Penjualan',
            'category'       => 'soft-skill',
            'description'    => 'Meningkatkan kemampuan negosiasi dan closing penjualan',
            'provider'       => 'Sales Academy Indonesia',
            'duration_hours' => 16,
            'cost'           => 2500000,
            'is_active'      => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
        $sessionId = DB::table('training_sessions')->insertGetId([
            'tenant_id'           => $this->tenantId,
            'training_program_id' => $progId,
            'start_date'          => Carbon::now()->addDays(7)->format('Y-m-d'),
            'end_date'            => Carbon::now()->addDays(8)->format('Y-m-d'),
            'location'            => 'Ruang Meeting Lt. 3',
            'trainer'             => 'Bpk. Hendra Wijaya',
            'max_participants'    => 15,
            'status'              => 'scheduled',
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);
        foreach (array_slice($this->employeeIds, 2, 2) as $empId) {
            DB::table('training_participants')->insertOrIgnore([
                'tenant_id' => $this->tenantId,
                'training_session_id' => $sessionId,
                'employee_id' => $empId,
                'status' => 'registered',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        if (!empty($this->employeeIds)) {
            DB::table('employee_certifications')->insert([
                'tenant_id'          => $this->tenantId,
                'employee_id'        => $this->employeeIds[0],
                'name'               => 'Certified Sales Professional',
                'issuer'             => 'Sales Academy Indonesia',
                'certificate_number' => 'CSP-2025-001',
                'issued_date'        => '2025-06-01',
                'expiry_date'        => '2027-06-01',
                'status'             => 'active',
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }

    private function seedDisciplinary(): void
    {
        if (empty($this->employeeIds)) return;
        DB::table('disciplinary_letters')->updateOrInsert(
            ['tenant_id' => $this->tenantId, 'letter_number' => 'SP1/MBI/III/2026/001'],
            [
                'tenant_id'             => $this->tenantId,
                'employee_id'           => $this->employeeIds[4] ?? $this->employeeIds[0],
                'level'                 => 'sp1',
                'letter_number'         => 'SP1/MBI/III/2026/001',
                'issued_date'           => '2026-03-10',
                'valid_until'           => '2026-06-10',
                'violation_type'        => 'Keterlambatan berulang',
                'violation_description' => 'Karyawan terlambat lebih dari 5 kali dalam sebulan',
                'corrective_action'     => 'Hadir tepat waktu sesuai jadwal shift',
                'consequences'          => 'Jika terulang akan diberikan SP2',
                'status'                => 'issued',
                'issued_by'             => $this->adminId,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]
        );
    }

    private function seedSalaryComponents(): void
    {
        $components = [
            ['name' => 'Tunjangan Transport', 'code' => 'T_TRANSPORT', 'type' => 'allowance',  'calc_type' => 'fixed',        'default_amount' => 500000, 'taxable' => false],
            ['name' => 'Tunjangan Makan',     'code' => 'T_MAKAN',     'type' => 'allowance',  'calc_type' => 'fixed',        'default_amount' => 400000, 'taxable' => false],
            ['name' => 'Tunjangan Jabatan',   'code' => 'T_JABATAN',   'type' => 'allowance',  'calc_type' => 'percent_base', 'default_amount' => 10,     'taxable' => true],
            ['name' => 'Potongan BPJS Kes.',  'code' => 'D_BPJS_KES',  'type' => 'deduction',  'calc_type' => 'percent_base', 'default_amount' => 1,      'taxable' => false],
            ['name' => 'Potongan BPJS TK',    'code' => 'D_BPJS_TK',   'type' => 'deduction',  'calc_type' => 'percent_base', 'default_amount' => 2,      'taxable' => false],
        ];
        foreach ($components as $c) {
            DB::table('salary_components')->updateOrInsert(
                ['tenant_id' => $this->tenantId, 'code' => $c['code']],
                array_merge($c, ['tenant_id' => $this->tenantId, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedPayroll(): void
    {
        if (empty($this->employeeIds)) return;
        if (DB::table('payroll_runs')->where('tenant_id', $this->tenantId)->where('period', '2026-02')->exists()) return;
        $runId = DB::table('payroll_runs')->insertGetId([
            'tenant_id' => $this->tenantId,
            'period' => '2026-02',
            'status' => 'paid',
            'total_gross' => 0,
            'total_deductions' => 0,
            'total_net' => 0,
            'processed_by' => $this->adminId,
            'processed_at' => Carbon::parse('2026-02-28'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $totalGross = 0;
        $totalNet = 0;
        foreach (array_slice($this->employeeIds, 0, 5) as $empId) {
            $emp = DB::table('employees')->find($empId);
            $base = $emp->salary;
            $allowances = 900000;
            $gross = $base + $allowances;
            $bpjs = round($base * 0.03);
            $pph21 = round($gross * 0.05);
            $net = $gross - $bpjs - $pph21;
            DB::table('payroll_items')->insert([
                'tenant_id' => $this->tenantId,
                'payroll_run_id' => $runId,
                'employee_id' => $empId,
                'base_salary' => $base,
                'working_days' => 22,
                'present_days' => 21,
                'absent_days' => 1,
                'late_days' => 0,
                'allowances' => $allowances,
                'overtime_pay' => 0,
                'deduction_absent' => round($base / 22),
                'deduction_late' => 0,
                'deduction_other' => 0,
                'gross_salary' => $gross,
                'tax_pph21' => $pph21,
                'bpjs_employee' => $bpjs,
                'net_salary' => $net,
                'status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $totalGross += $gross;
            $totalNet += $net;
        }
        DB::table('payroll_runs')->where('id', $runId)->update([
            'total_gross' => $totalGross,
            'total_deductions' => $totalGross - $totalNet,
            'total_net' => $totalNet,
        ]);
    }

    private function seedPurchaseOrders(): void
    {
        if (empty($this->supplierIds) || empty($this->productIds)) return;
        if (DB::table('purchase_orders')->where('tenant_id', $this->tenantId)->exists()) return;
        $pos = [
            ['supplier_id' => $this->supplierIds[0], 'number' => 'PO/MBI/2026/001', 'status' => 'received', 'date' => '2026-02-10', 'items' => [0, 1]],
            ['supplier_id' => $this->supplierIds[2], 'number' => 'PO/MBI/2026/002', 'status' => 'sent',     'date' => '2026-03-05', 'items' => [2, 3]],
            ['supplier_id' => $this->supplierIds[3], 'number' => 'PO/MBI/2026/003', 'status' => 'draft',    'date' => '2026-03-15', 'items' => [4, 7]],
        ];
        foreach ($pos as $po) {
            $subtotal = 0;
            $itemsData = [];
            foreach ($po['items'] as $pidx) {
                $product = DB::table('products')->find($this->productIds[$pidx]);
                $qty = rand(5, 20);
                $total = $product->price_buy * $qty;
                $subtotal += $total;
                $itemsData[] = ['product_id' => $product->id, 'qty' => $qty, 'price' => $product->price_buy, 'total' => $total];
            }
            $tax = round($subtotal * 0.11);
            $poId = DB::table('purchase_orders')->insertGetId([
                'tenant_id'       => $this->tenantId,
                'supplier_id'     => $po['supplier_id'],
                'user_id'         => $this->adminId,
                'warehouse_id'    => $this->warehouseId,
                'number'          => $po['number'],
                'status'          => $po['status'],
                'approval_status' => 'approved',
                'date'            => $po['date'],
                'expected_date'   => Carbon::parse($po['date'])->addDays(7)->format('Y-m-d'),
                'subtotal'        => $subtotal,
                'discount'        => 0,
                'tax'             => $tax,
                'total'           => $subtotal + $tax,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
            foreach ($itemsData as $item) {
                DB::table('purchase_order_items')->insert([
                    'purchase_order_id' => $poId,
                    'product_id'        => $item['product_id'],
                    'quantity_ordered'  => $item['qty'],
                    'quantity_received' => $po['status'] === 'received' ? $item['qty'] : 0,
                    'price'             => $item['price'],
                    'total'             => $item['total'],
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
            // Create payable for received POs
            if ($po['status'] === 'received') {
                DB::table('payables')->insertOrIgnore([
                    'tenant_id'        => $this->tenantId,
                    'purchase_order_id' => $poId,
                    'supplier_id'      => $po['supplier_id'],
                    'number'           => 'AP/' . $po['number'],
                    'total_amount'     => $subtotal + $tax,
                    'paid_amount'      => 0,
                    'remaining_amount' => $subtotal + $tax,
                    'status'           => 'unpaid',
                    'due_date'         => Carbon::parse($po['date'])->addDays(30)->format('Y-m-d'),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }
    }

    private function seedSalesOrders(): void
    {
        if (empty($this->customerIds) || empty($this->productIds)) return;
        if (DB::table('sales_orders')->where('tenant_id', $this->tenantId)->exists()) return;
        $orders = [
            ['customer_id' => $this->customerIds[0], 'number' => 'SO/MBI/2026/001', 'status' => 'delivered', 'date' => '2026-02-15', 'items' => [0, 2, 4]],
            ['customer_id' => $this->customerIds[1], 'number' => 'SO/MBI/2026/002', 'status' => 'processing', 'date' => '2026-03-01', 'items' => [1, 3]],
            ['customer_id' => $this->customerIds[2], 'number' => 'SO/MBI/2026/003', 'status' => 'confirmed', 'date' => '2026-03-10', 'items' => [5, 6]],
            ['customer_id' => $this->customerIds[4], 'number' => 'SO/MBI/2026/004', 'status' => 'pending',   'date' => '2026-03-20', 'items' => [0, 1, 9]],
        ];
        foreach ($orders as $order) {
            $subtotal = 0;
            $itemsData = [];
            foreach ($order['items'] as $pidx) {
                $product = DB::table('products')->find($this->productIds[$pidx]);
                $qty = rand(2, 10);
                $total = $product->price_sell * $qty;
                $subtotal += $total;
                $itemsData[] = ['product_id' => $product->id, 'qty' => $qty, 'price' => $product->price_sell, 'total' => $total];
            }
            $tax = round($subtotal * 0.11);
            $soId = DB::table('sales_orders')->insertGetId([
                'tenant_id'       => $this->tenantId,
                'customer_id'     => $order['customer_id'],
                'user_id'         => $this->staffId,
                'number'          => $order['number'],
                'status'          => $order['status'],
                'approval_status' => 'approved',
                'date'            => $order['date'],
                'delivery_date'   => Carbon::parse($order['date'])->addDays(7)->format('Y-m-d'),
                'subtotal'        => $subtotal,
                'discount'        => 0,
                'tax'             => $tax,
                'total'           => $subtotal + $tax,
                'shipping_address' => 'Jakarta',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
            foreach ($itemsData as $item) {
                DB::table('sales_order_items')->insert([
                    'sales_order_id' => $soId,
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['qty'],
                    'price'          => $item['price'],
                    'discount'       => 0,
                    'total'          => $item['total'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }
    }

    private function seedInvoicesAndPayables(): void
    {
        // Create invoices for delivered sales orders
        $deliveredSOs = DB::table('sales_orders')
            ->where('tenant_id', $this->tenantId)
            ->where('status', 'delivered')
            ->get();
        foreach ($deliveredSOs as $so) {
            if (DB::table('invoices')->where('sales_order_id', $so->id)->exists()) continue;
            $invId = DB::table('invoices')->insertGetId([
                'tenant_id'       => $this->tenantId,
                'sales_order_id'  => $so->id,
                'customer_id'     => $so->customer_id,
                'number'          => 'INV/' . str_replace('SO/', '', $so->number),
                'total_amount'    => $so->total,
                'paid_amount'     => $so->total,
                'remaining_amount' => 0,
                'status'          => 'paid',
                'due_date'        => Carbon::parse($so->date)->addDays(30)->format('Y-m-d'),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
            // Payment record
            DB::table('payments')->insert([
                'tenant_id'      => $this->tenantId,
                'payable_type'   => 'App\\Models\\Invoice',
                'payable_id'     => $invId,
                'amount'         => $so->total,
                'payment_method' => 'transfer',
                'payment_date'   => Carbon::parse($so->date)->addDays(14)->format('Y-m-d'),
                'notes'          => 'Pembayaran lunas via transfer bank',
                'user_id'        => $this->adminId,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
        // Create unpaid invoice for processing SO
        $processingSOs = DB::table('sales_orders')
            ->where('tenant_id', $this->tenantId)
            ->where('status', 'processing')
            ->get();
        foreach ($processingSOs as $so) {
            if (DB::table('invoices')->where('sales_order_id', $so->id)->exists()) continue;
            DB::table('invoices')->insertGetId([
                'tenant_id'       => $this->tenantId,
                'sales_order_id'  => $so->id,
                'customer_id'     => $so->customer_id,
                'number'          => 'INV/' . str_replace('SO/', '', $so->number),
                'total_amount'    => $so->total,
                'paid_amount'     => 0,
                'remaining_amount' => $so->total,
                'status'          => 'unpaid',
                'due_date'        => Carbon::parse($so->date)->addDays(30)->format('Y-m-d'),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    private function seedBankAccount(): void
    {
        if (DB::table('bank_accounts')->where('tenant_id', $this->tenantId)->exists()) return;
        $bankId = DB::table('bank_accounts')->insertGetId([
            'tenant_id'      => $this->tenantId,
            'bank_name'      => 'BCA',
            'account_number' => '1234567890',
            'account_name'   => 'PT Maju Bersama Indonesia',
            'balance'        => 250000000,
            'is_active'      => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
        // Bank statements
        $statements = [
            ['date' => '2026-03-01', 'desc' => 'Pembayaran Invoice SO/MBI/2026/001', 'type' => 'credit', 'amount' => 45000000, 'status' => 'matched'],
            ['date' => '2026-03-05', 'desc' => 'Pembayaran Gaji Februari 2026',       'type' => 'debit',  'amount' => 35000000, 'status' => 'matched'],
            ['date' => '2026-03-10', 'desc' => 'Transfer Masuk - PT Solusi Digital',  'type' => 'credit', 'amount' => 120000000, 'status' => 'unmatched'],
            ['date' => '2026-03-15', 'desc' => 'Pembayaran Sewa Kantor Maret',        'type' => 'debit',  'amount' => 15000000, 'status' => 'unmatched'],
            ['date' => '2026-03-20', 'desc' => 'Pembayaran ke PT Asus Indonesia',     'type' => 'debit',  'amount' => 85000000, 'status' => 'unmatched'],
        ];
        $balance = 250000000;
        foreach ($statements as $s) {
            $balance += $s['type'] === 'credit' ? $s['amount'] : -$s['amount'];
            DB::table('bank_statements')->insert([
                'tenant_id'        => $this->tenantId,
                'bank_account_id'  => $bankId,
                'transaction_date' => $s['date'],
                'description'      => $s['desc'],
                'type'             => $s['type'],
                'amount'           => $s['amount'],
                'balance'          => $balance,
                'status'           => $s['status'],
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }

    private function seedJournalEntries(): void
    {
        if (DB::table('journal_entries')->where('tenant_id', $this->tenantId)->exists()) return;
        $journals = [
            [
                'number' => 'JE/MBI/2026/001',
                'date' => '2026-03-01',
                'description' => 'Penerimaan pembayaran piutang dari PT Teknologi Nusantara',
                'lines' => [['code' => '1102', 'debit' => 45000000, 'credit' => 0], ['code' => '1103', 'debit' => 0, 'credit' => 45000000]]
            ],
            [
                'number' => 'JE/MBI/2026/002',
                'date' => '2026-03-05',
                'description' => 'Pembayaran gaji karyawan Februari 2026',
                'lines' => [['code' => '5201', 'debit' => 35000000, 'credit' => 0], ['code' => '1102', 'debit' => 0, 'credit' => 35000000]]
            ],
            [
                'number' => 'JE/MBI/2026/003',
                'date' => '2026-03-10',
                'description' => 'Penjualan barang ke CV Mitra Komputer',
                'lines' => [['code' => '1103', 'debit' => 55000000, 'credit' => 0], ['code' => '4101', 'debit' => 0, 'credit' => 49549550], ['code' => '2103', 'debit' => 0, 'credit' => 5450450]]
            ],
        ];
        foreach ($journals as $j) {
            $jeId = DB::table('journal_entries')->insertGetId([
                'tenant_id' => $this->tenantId,
                'period_id' => $this->periodId,
                'user_id' => $this->adminId,
                'number' => $j['number'],
                'date' => $j['date'],
                'description' => $j['description'],
                'currency_code' => 'IDR',
                'currency_rate' => 1,
                'status' => 'posted',
                'posted_by' => $this->adminId,
                'posted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach ($j['lines'] as $line) {
                DB::table('journal_entry_lines')->insert([
                    'journal_entry_id' => $jeId,
                    'account_id'       => $this->coaMap[$line['code']] ?? array_values($this->coaMap)[0],
                    'debit'            => $line['debit'],
                    'credit'           => $line['credit'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }
    }

    private function seedAssets(): void
    {
        if (DB::table('assets')->where('tenant_id', $this->tenantId)->exists()) return;
        $assets = [
            ['asset_code' => 'AST-001', 'name' => 'Kendaraan Toyota Avanza', 'category' => 'vehicle',   'purchase_price' => 200000000, 'current_value' => 160000000, 'useful_life_years' => 8],
            ['asset_code' => 'AST-002', 'name' => 'Laptop Dell Latitude',    'category' => 'equipment', 'purchase_price' => 15000000,  'current_value' => 10000000,  'useful_life_years' => 4],
            ['asset_code' => 'AST-003', 'name' => 'Mesin Forklift Komatsu',  'category' => 'machine',   'purchase_price' => 350000000, 'current_value' => 280000000, 'useful_life_years' => 10],
            ['asset_code' => 'AST-004', 'name' => 'Rak Gudang Heavy Duty',   'category' => 'furniture', 'purchase_price' => 25000000,  'current_value' => 20000000,  'useful_life_years' => 10],
        ];
        foreach ($assets as $a) {
            $assetId = DB::table('assets')->insertGetId(array_merge($a, [
                'tenant_id'            => $this->tenantId,
                'purchase_date'        => Carbon::now()->subYears(1)->format('Y-m-d'),
                'salvage_value'        => $a['purchase_price'] * 0.1,
                'depreciation_method'  => 'straight_line',
                'status'               => 'active',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]));
            $monthlyDep = ($a['purchase_price'] - $a['purchase_price'] * 0.1) / ($a['useful_life_years'] * 12);
            DB::table('asset_depreciations')->insert([
                'tenant_id' => $this->tenantId,
                'asset_id' => $assetId,
                'period' => '2026-03',
                'depreciation_amount' => round($monthlyDep),
                'book_value_after' => $a['current_value'] - round($monthlyDep),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $firstAsset = DB::table('assets')->where('tenant_id', $this->tenantId)->first();
        if ($firstAsset) {
            DB::table('asset_maintenances')->insert([
                'tenant_id' => $this->tenantId,
                'asset_id' => $firstAsset->id,
                'type' => 'scheduled',
                'description' => 'Service rutin 10.000 km',
                'scheduled_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
                'cost' => 1500000,
                'vendor' => 'Bengkel Resmi Toyota',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedBudgets(): void
    {
        if (DB::table('budgets')->where('tenant_id', $this->tenantId)->exists()) return;
        $budgets = [
            ['name' => 'Anggaran Penjualan Maret',  'dept' => 'Penjualan',  'amount' => 500000000, 'realized' => 420000000, 'cat' => 'SALES'],
            ['name' => 'Anggaran Operasional Maret', 'dept' => 'Operasional', 'amount' => 80000000,  'realized' => 65000000,  'cat' => 'ADMIN'],
            ['name' => 'Anggaran Pemasaran Maret',   'dept' => 'Penjualan',  'amount' => 30000000,  'realized' => 18000000,  'cat' => 'MARKETING'],
            ['name' => 'Anggaran SDM Maret',         'dept' => 'SDM',        'amount' => 120000000, 'realized' => 115000000, 'cat' => 'SALARY'],
        ];
        foreach ($budgets as $b) {
            DB::table('budgets')->insert([
                'tenant_id' => $this->tenantId,
                'name' => $b['name'],
                'department' => $b['dept'],
                'period' => '2026-03',
                'period_type' => 'monthly',
                'amount' => $b['amount'],
                'realized' => $b['realized'],
                'category' => $b['cat'],
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedKpiTargets(): void
    {
        if (DB::table('kpi_targets')->where('tenant_id', $this->tenantId)->exists()) return;
        $kpis = [
            ['metric' => 'revenue',       'label' => 'Pendapatan',        'target' => 500000000, 'actual' => 420000000, 'unit' => 'currency', 'color' => '#3b82f6'],
            ['metric' => 'orders',        'label' => 'Jumlah Order',      'target' => 100,       'actual' => 87,        'unit' => 'number',   'color' => '#10b981'],
            ['metric' => 'new_customers', 'label' => 'Pelanggan Baru',    'target' => 20,        'actual' => 15,        'unit' => 'number',   'color' => '#f59e0b'],
            ['metric' => 'profit_margin', 'label' => 'Margin Keuntungan', 'target' => 25,        'actual' => 22,        'unit' => 'percent',  'color' => '#8b5cf6'],
        ];
        foreach ($kpis as $k) {
            DB::table('kpi_targets')->updateOrInsert(
                ['tenant_id' => $this->tenantId, 'metric' => $k['metric'], 'period' => '2026-03'],
                array_merge($k, ['tenant_id' => $this->tenantId, 'period' => '2026-03', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedCrmLeads(): void
    {
        if (DB::table('crm_leads')->where('tenant_id', $this->tenantId)->exists()) return;
        $leads = [
            ['name' => 'PT Inovasi Teknologi', 'stage' => 'proposal',    'value' => 150000000, 'prob' => 60,  'source' => 'referral'],
            ['name' => 'CV Berkah Elektronik', 'stage' => 'qualified',   'value' => 75000000,  'prob' => 40,  'source' => 'cold_call'],
            ['name' => 'Toko Gadget Murah',    'stage' => 'negotiation', 'value' => 50000000,  'prob' => 75,  'source' => 'website'],
            ['name' => 'PT Mitra Usaha Jaya',  'stage' => 'won',         'value' => 200000000, 'prob' => 100, 'source' => 'exhibition'],
            ['name' => 'Bapak Agus Santoso',   'stage' => 'new',         'value' => 10000000,  'prob' => 20,  'source' => 'social_media'],
        ];
        foreach ($leads as $l) {
            $leadId = DB::table('crm_leads')->insertGetId([
                'tenant_id' => $this->tenantId,
                'assigned_to' => $this->staffId,
                'name' => $l['name'],
                'company' => $l['name'],
                'phone' => '0812-' . rand(10000000, 99999999),
                'email' => strtolower(str_replace([' ', '.'], '', $l['name'])) . '@example.com',
                'stage' => $l['stage'],
                'estimated_value' => $l['value'],
                'probability' => $l['prob'],
                'source' => $l['source'],
                'product_interest' => 'Laptop & Monitor',
                'expected_close_date' => Carbon::now()->addDays(rand(7, 60))->format('Y-m-d'),
                'last_contact_at' => Carbon::now()->subDays(rand(1, 10)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('crm_activities')->insert([
                'tenant_id' => $this->tenantId,
                'lead_id' => $leadId,
                'user_id' => $this->staffId,
                'type' => 'call',
                'description' => 'Follow up kebutuhan produk dan penawaran harga',
                'outcome' => 'interested',
                'next_follow_up' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedApprovalWorkflow(): void
    {
        if (DB::table('approval_workflows')->where('tenant_id', $this->tenantId)->exists()) return;
        $wfId = DB::table('approval_workflows')->insertGetId([
            'tenant_id' => $this->tenantId,
            'name' => 'Persetujuan PO > 50 Juta',
            'model_type' => 'App\\Models\\PurchaseOrder',
            'min_amount' => 50000000,
            'approver_roles' => json_encode(['manager', 'admin']),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $po = DB::table('purchase_orders')->where('tenant_id', $this->tenantId)->where('status', 'draft')->first();
        if ($po) {
            DB::table('approval_requests')->insertOrIgnore([
                'tenant_id' => $this->tenantId,
                'workflow_id' => $wfId,
                'requested_by' => $this->staffId,
                'model_type' => 'App\\Models\\PurchaseOrder',
                'model_id' => $po->id,
                'status' => 'pending',
                'amount' => $po->total,
                'notes' => 'Mohon persetujuan PO untuk pembelian stok bulan Maret',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedTransactions(): void
    {
        if (DB::table('transactions')->where('tenant_id', $this->tenantId)->exists()) return;
        $rentCatId  = DB::table('expense_categories')->where('tenant_id', $this->tenantId)->where('code', 'RENT')->value('id');
        $utilCatId  = DB::table('expense_categories')->where('tenant_id', $this->tenantId)->where('code', 'UTILITY')->value('id');
        $salesCatId = DB::table('expense_categories')->where('tenant_id', $this->tenantId)->where('code', 'SALES')->value('id');
        $txns = [
            ['type' => 'income',  'amount' => 45000000,  'desc' => 'Penerimaan SO/MBI/2026/001', 'cat' => $salesCatId, 'date' => '2026-03-01'],
            ['type' => 'expense', 'amount' => 15000000,  'desc' => 'Sewa kantor Maret 2026',      'cat' => $rentCatId,  'date' => '2026-03-05'],
            ['type' => 'expense', 'amount' => 3500000,   'desc' => 'Listrik & air Maret 2026',    'cat' => $utilCatId,  'date' => '2026-03-10'],
            ['type' => 'income',  'amount' => 120000000, 'desc' => 'Penerimaan SO/MBI/2026/004',  'cat' => $salesCatId, 'date' => '2026-03-15'],
        ];
        foreach ($txns as $i => $t) {
            DB::table('transactions')->insert([
                'tenant_id' => $this->tenantId,
                'user_id' => $this->adminId,
                'expense_category_id' => $t['cat'],
                'number' => 'TRX/MBI/2026/' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'type' => $t['type'],
                'date' => $t['date'],
                'amount' => $t['amount'],
                'payment_method' => 'transfer',
                'account' => 'Bank BCA',
                'description' => $t['desc'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedSimulation(): void
    {
        if (DB::table('simulations')->where('tenant_id', $this->tenantId)->exists()) return;
        DB::table('simulations')->insert([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->adminId,
            'name' => 'Simulasi Kenaikan Harga Laptop 10%',
            'scenario_type' => 'price_increase',
            'parameters' => json_encode(['product_category' => 'Elektronik', 'increase_pct' => 10, 'period' => '2026-Q2']),
            'results' => json_encode(['projected_revenue' => 550000000, 'projected_margin' => 27.5, 'demand_impact' => -5]),
            'ai_narrative' => 'Kenaikan harga 10% pada kategori Elektronik diproyeksikan meningkatkan pendapatan 10% namun berpotensi menurunkan volume penjualan 5%. Margin bersih meningkat dari 22% ke 27.5%.',
            'status' => 'calculated',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedDeferredItem(): void
    {
        if (DB::table('deferred_items')->where('tenant_id', $this->tenantId)->exists()) return;
        $deferredAccId    = $this->coaMap['1106'] ?? array_values($this->coaMap)[0];
        $recognitionAccId = $this->coaMap['5202'] ?? array_values($this->coaMap)[0];
        $defId = DB::table('deferred_items')->insertGetId([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->adminId,
            'type' => 'prepaid_expense',
            'number' => 'DEF/MBI/2026/001',
            'description' => 'Sewa kantor dibayar di muka Jan-Des 2026',
            'total_amount' => 180000000,
            'recognized_amount' => 45000000,
            'remaining_amount' => 135000000,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'total_periods' => 12,
            'recognized_periods' => 3,
            'status' => 'active',
            'deferred_account_id' => $deferredAccId,
            'recognition_account_id' => $recognitionAccId,
            'reference_number' => 'SEWA-2026',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        for ($m = 1; $m <= 12; $m++) {
            DB::table('deferred_item_schedules')->insert([
                'deferred_item_id' => $defId,
                'period_number' => $m,
                'recognition_date' => Carbon::parse('2026-01-01')->addMonths($m - 1)->format('Y-m-d'),
                'amount' => 15000000,
                'status' => $m <= 3 ? 'posted' : 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedWriteoff(): void
    {
        if (DB::table('writeoffs')->where('tenant_id', $this->tenantId)->exists()) return;
        $invoice = DB::table('invoices')->where('tenant_id', $this->tenantId)->where('status', 'unpaid')->first();
        if (!$invoice) return;
        DB::table('writeoffs')->insert([
            'tenant_id' => $this->tenantId,
            'requested_by' => $this->adminId,
            'number' => 'WO/MBI/2026/001',
            'type' => 'receivable',
            'reference_type' => 'App\\Models\\Invoice',
            'reference_id' => $invoice->id,
            'reference_number' => $invoice->number,
            'original_amount' => $invoice->total_amount,
            'writeoff_amount' => $invoice->total_amount,
            'reason' => 'Pelanggan tidak dapat dihubungi dan tidak ada aset yang dapat disita',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedAiMemory(): void
    {
        if (DB::table('ai_memories')->where('tenant_id', $this->tenantId)->exists()) return;
        $memories = [
            ['key' => 'business_context', 'value' => json_encode('Distributor elektronik dengan 10 produk utama, 5 pelanggan korporat, 4 supplier')],
            ['key' => 'top_products',     'value' => json_encode('Laptop Asus VivoBook dan Lenovo IdeaPad adalah produk terlaris')],
            ['key' => 'payment_terms',    'value' => json_encode('Pelanggan korporat mendapat Net 30, pelanggan retail Net 14')],
            ['key' => 'seasonal_pattern', 'value' => json_encode('Penjualan meningkat di bulan Agustus (back to school) dan Desember (akhir tahun)')],
        ];
        foreach ($memories as $m) {
            DB::table('ai_memories')->insertOrIgnore([
                'tenant_id'    => $this->tenantId,
                'user_id'      => $this->adminId,
                'key'          => $m['key'],
                'value'        => $m['value'],
                'frequency'    => 1,
                'last_seen_at' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }

    private function seedReminders(): void
    {
        if (DB::table('reminders')->where('tenant_id', $this->tenantId)->exists()) return;
        $reminders = [
            ['title' => 'Tutup Buku Maret 2026',       'remind_at' => '2026-03-31 08:00:00', 'notes' => 'Pastikan semua jurnal sudah diposting'],
            ['title' => 'Bayar PPN Maret 2026',        'remind_at' => '2026-04-15 08:00:00', 'notes' => 'Batas akhir pembayaran PPN masa Maret'],
            ['title' => 'Renewal Kontrak Sewa Kantor', 'remind_at' => '2026-12-01 08:00:00', 'notes' => 'Kontrak sewa berakhir 31 Desember 2026'],
            ['title' => 'Review Anggaran Q2 2026',     'remind_at' => '2026-04-01 08:00:00', 'notes' => 'Rapat review anggaran kuartal 2'],
        ];
        foreach ($reminders as $r) {
            DB::table('reminders')->insert([
                'tenant_id'    => $this->tenantId,
                'user_id'      => $this->adminId,
                'title'        => $r['title'],
                'notes'        => $r['notes'],
                'remind_at'    => $r['remind_at'],
                'status'       => 'pending',
                'channel'      => 'both',
                'related_type' => null,
                'related_id'   => null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }

    private function seedNotifications(): void
    {
        if (DB::table('erp_notifications')->where('tenant_id', $this->tenantId)->exists()) return;
        $notifs = [
            ['title' => 'Stok Laptop Asus hampir habis',        'body' => 'Stok Laptop Asus VivoBook 15 tersisa 3 unit, di bawah minimum stok (5 unit)', 'type' => 'low_stock'],
            ['title' => 'Invoice jatuh tempo dalam 3 hari',     'body' => 'Invoice INV/MBI/2026/002 senilai Rp 55.000.000 dari CV Mitra Komputer akan jatuh tempo', 'type' => 'invoice_due'],
            ['title' => 'Approval PO menunggu persetujuan',     'body' => 'Purchase Order PO/MBI/2026/003 senilai Rp 12.000.000 menunggu persetujuan Anda', 'type' => 'approval'],
            ['title' => 'Payroll Februari 2026 telah diproses', 'body' => 'Penggajian bulan Februari 2026 telah berhasil diproses untuk 5 karyawan', 'type' => 'payroll'],
        ];
        foreach ($notifs as $n) {
            DB::table('erp_notifications')->insert([
                'tenant_id' => $this->tenantId,
                'user_id' => $this->adminId,
                'title' => $n['title'],
                'body' => $n['body'],
                'type' => $n['type'],
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedCustomFields(): void
    {
        if (DB::table('custom_fields')->where('tenant_id', $this->tenantId)->exists()) return;
        $fields = [
            ['module' => 'customer', 'key' => 'market_segment',  'label' => 'Segmen Pasar',    'type' => 'select', 'options' => json_encode(['Korporat', 'UMKM', 'Retail', 'Pemerintah'])],
            ['module' => 'product',  'key' => 'warranty_months', 'label' => 'Garansi (bulan)', 'type' => 'number', 'options' => null],
            ['module' => 'employee', 'key' => 'bpjs_number',     'label' => 'Nomor BPJS',      'type' => 'text',   'options' => null],
        ];
        foreach ($fields as $f) {
            DB::table('custom_fields')->insert([
                'tenant_id'  => $this->tenantId,
                'module'     => $f['module'],
                'key'        => $f['key'],
                'label'      => $f['label'],
                'type'       => $f['type'],
                'options'    => $f['options'],
                'required'   => false,
                'is_active'  => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedCompanyGroup(): void
    {
        if (DB::table('company_groups')->where('owner_user_id', $this->adminId)->exists()) return;
        $groupId = DB::table('company_groups')->insertGetId([
            'owner_user_id' => $this->adminId,
            'name'          => 'Grup MBI Holding',
            'currency_code' => 'IDR',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
        DB::table('company_group_members')->insertOrIgnore([
            'company_group_id' => $groupId,
            'tenant_id'        => $this->tenantId,
            'role'             => 'owner',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    private function seedDocumentTemplate(): void
    {
        if (DB::table('document_templates')->where('tenant_id', $this->tenantId)->exists()) return;
        foreach (
            [
                ['name' => 'Template Invoice Standar',   'doc_type' => 'invoice'],
                ['name' => 'Template Quotation Standar', 'doc_type' => 'quotation'],
                ['name' => 'Template Purchase Order',    'doc_type' => 'po'],
            ] as $t
        ) {
            DB::table('document_templates')->insert([
                'tenant_id'    => $this->tenantId,
                'name'         => $t['name'],
                'doc_type'     => $t['doc_type'],
                'html_content' => '<h1>' . strtoupper($t['doc_type']) . '</h1><p>{{company_name}}</p>',
                'is_default'   => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }

    // ── New Module Seeds ──────────────────────────────────────────

    private function seedManufacturing(): void
    {
        if (DB::table('work_centers')->where('tenant_id', $this->tenantId)->exists()) return;

        // Work Centers
        foreach (
            [
                ['code' => 'WC-01', 'name' => 'Mesin CNC Utama',     'cost_per_hour' => 150000, 'capacity_per_day' => 8],
                ['code' => 'WC-02', 'name' => 'Stasiun Assembly',     'cost_per_hour' => 75000,  'capacity_per_day' => 8],
                ['code' => 'WC-03', 'name' => 'Quality Control',      'cost_per_hour' => 50000,  'capacity_per_day' => 8],
            ] as $wc
        ) {
            DB::table('work_centers')->insert(array_merge($wc, [
                'tenant_id' => $this->tenantId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // BOM (if products exist)
        $product = DB::table('products')->where('tenant_id', $this->tenantId)->first();
        if ($product) {
            $bomId = DB::table('boms')->insertGetId([
                'tenant_id' => $this->tenantId,
                'product_id' => $product->id,
                'name' => 'BOM ' . $product->name,
                'batch_size' => 10,
                'batch_unit' => 'pcs',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $materials = DB::table('products')->where('tenant_id', $this->tenantId)
                ->where('id', '!=', $product->id)->limit(2)->get();
            foreach ($materials as $i => $mat) {
                DB::table('bom_lines')->insert([
                    'bom_id' => $bomId,
                    'product_id' => $mat->id,
                    'quantity_per_batch' => ($i + 1) * 5,
                    'unit' => 'pcs',
                    'sort_order' => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedFleet(): void
    {
        if (DB::table('fleet_vehicles')->where('tenant_id', $this->tenantId)->exists()) return;

        $vehicles = [
            ['plate_number' => 'B 1234 XYZ', 'name' => 'Toyota Avanza 2024', 'type' => 'car',   'brand' => 'Toyota', 'model' => 'Avanza', 'year' => 2024, 'odometer' => 15000],
            ['plate_number' => 'B 5678 ABC', 'name' => 'Mitsubishi Colt Diesel', 'type' => 'truck', 'brand' => 'Mitsubishi', 'model' => 'Colt Diesel', 'year' => 2023, 'odometer' => 45000],
            ['plate_number' => 'B 9012 DEF', 'name' => 'Honda Beat 2025',   'type' => 'motorcycle', 'brand' => 'Honda', 'model' => 'Beat', 'year' => 2025, 'odometer' => 3000],
        ];
        foreach ($vehicles as $v) {
            DB::table('fleet_vehicles')->insert(array_merge($v, [
                'tenant_id' => $this->tenantId,
                'status' => 'available',
                'is_active' => true,
                'registration_expiry' => now()->addYear(),
                'insurance_expiry' => now()->addMonths(8),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Drivers
        $employees = DB::table('employees')->where('tenant_id', $this->tenantId)->limit(2)->get();
        foreach ($employees as $i => $emp) {
            DB::table('fleet_drivers')->insert([
                'tenant_id' => $this->tenantId,
                'employee_id' => $emp->id,
                'name' => $emp->name,
                'license_number' => 'SIM-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'license_type' => $i === 0 ? 'A' : 'C',
                'license_expiry' => now()->addYears(2),
                'phone' => $emp->phone,
                'status' => 'active',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedContracts(): void
    {
        if (DB::table('contracts')->where('tenant_id', $this->tenantId)->exists()) return;

        // Template
        $tplId = DB::table('contract_templates')->insertGetId([
            'tenant_id' => $this->tenantId,
            'name' => 'Kontrak Jasa Standar',
            'category' => 'service',
            'default_terms' => 'Pembayaran 30 hari setelah invoice.',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $customer = DB::table('customers')->where('tenant_id', $this->tenantId)->first();
        $userId = DB::table('users')->where('tenant_id', $this->tenantId)->where('role', 'admin')->value('id');
        if (!$customer || !$userId) return;

        DB::table('contracts')->insert([
            'tenant_id' => $this->tenantId,
            'contract_number' => 'CTR-202603-0001',
            'title' => 'Kontrak Maintenance IT Tahunan',
            'template_id' => $tplId,
            'customer_id' => $customer->id,
            'party_type' => 'customer',
            'category' => 'maintenance',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'value' => 60000000,
            'billing_cycle' => 'monthly',
            'billing_amount' => 5000000,
            'next_billing_date' => now()->startOfMonth()->addMonth(),
            'auto_renew' => true,
            'renewal_days_before' => 30,
            'status' => 'active',
            'sla_response_hours' => 4,
            'sla_resolution_hours' => 24,
            'sla_uptime_pct' => 99.50,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedConsignment(): void
    {
        if (DB::table('consignment_partners')->where('tenant_id', $this->tenantId)->exists()) return;

        DB::table('consignment_partners')->insert([
            'tenant_id' => $this->tenantId,
            'name' => 'Toko Elektronik Jaya',
            'contact_person' => 'Pak Budi',
            'phone' => '08123456789',
            'address' => 'Jl. Pasar Baru No. 15, Jakarta',
            'commission_pct' => 10,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('consignment_partners')->insert([
            'tenant_id' => $this->tenantId,
            'name' => 'Outlet Gadget Corner',
            'contact_person' => 'Ibu Sari',
            'phone' => '08198765432',
            'address' => 'Mall Taman Anggrek Lt. 2, Jakarta',
            'commission_pct' => 15,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedCommission(): void
    {
        if (DB::table('commission_rules')->where('tenant_id', $this->tenantId)->exists()) return;

        DB::table('commission_rules')->insert([
            'tenant_id' => $this->tenantId,
            'name' => 'Komisi Sales Standard',
            'type' => 'flat_pct',
            'rate' => 2.5,
            'basis' => 'revenue',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('commission_rules')->insert([
            'tenant_id' => $this->tenantId,
            'name' => 'Komisi Tiered',
            'type' => 'tiered',
            'rate' => 0,
            'basis' => 'revenue',
            'tiers' => json_encode([
                ['min' => 0, 'max' => 10000000, 'rate' => 2],
                ['min' => 10000000, 'max' => 50000000, 'rate' => 3],
                ['min' => 50000000, 'max' => null, 'rate' => 5],
            ]),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedHelpdesk(): void
    {
        if (DB::table('kb_articles')->where('tenant_id', $this->tenantId)->exists()) return;

        $userId = DB::table('users')->where('tenant_id', $this->tenantId)->where('role', 'admin')->value('id');
        if (!$userId) return;

        // Knowledge Base
        foreach (
            [
                ['title' => 'Cara Melakukan Retur Barang', 'category' => 'product', 'body' => 'Untuk melakukan retur barang, silakan hubungi customer service kami dengan menyertakan nomor invoice dan foto barang yang ingin diretur.'],
                ['title' => 'Metode Pembayaran yang Tersedia', 'category' => 'billing', 'body' => 'Kami menerima pembayaran via transfer bank (BCA, Mandiri, BNI), e-wallet (GoPay, OVO), dan kartu kredit.'],
                ['title' => 'Estimasi Waktu Pengiriman', 'category' => 'delivery', 'body' => 'Pengiriman dalam kota: 1-2 hari kerja. Luar kota Jawa: 2-4 hari kerja. Luar Jawa: 4-7 hari kerja.'],
            ] as $kb
        ) {
            DB::table('kb_articles')->insert(array_merge($kb, [
                'tenant_id' => $this->tenantId,
                'slug' => \Illuminate\Support\Str::slug($kb['title']),
                'is_published' => true,
                'views' => rand(10, 100),
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedSubscriptionBilling(): void
    {
        if (DB::table('customer_subscription_plans')->where('tenant_id', $this->tenantId)->exists()) return;

        foreach (
            [
                ['name' => 'Basic Support', 'code' => 'BASIC', 'price' => 500000, 'billing_cycle' => 'monthly', 'trial_days' => 14, 'features' => json_encode(['Email support', 'Knowledge base', 'Response 24 jam'])],
                ['name' => 'Premium Support', 'code' => 'PREM', 'price' => 1500000, 'billing_cycle' => 'monthly', 'trial_days' => 7, 'features' => json_encode(['Priority support', 'Phone & WhatsApp', 'Response 4 jam', 'Dedicated account manager'])],
                ['name' => 'Enterprise Annual', 'code' => 'ENT', 'price' => 15000000, 'billing_cycle' => 'annual', 'trial_days' => 30, 'features' => json_encode(['24/7 support', 'SLA 99.9%', 'Custom integration', 'On-site visit'])],
            ] as $plan
        ) {
            DB::table('customer_subscription_plans')->insert(array_merge($plan, [
                'tenant_id' => $this->tenantId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
