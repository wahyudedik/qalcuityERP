<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * TenantDemoSeeder — Data demo lengkap untuk 1 tenant (semua modul).
 * Jalankan: php artisan db:seed --class=TenantDemoSeeder
 */
class TenantDemoSeeder extends Seeder
{
    private int $tenantId;
    private int $adminId;
    private int $managerId;
    private int $staffId;
    private array $productIds   = [];
    private array $customerIds  = [];
    private array $supplierIds  = [];
    private array $employeeIds  = [];
    private array $coaMap       = [];
    private int   $warehouseId;
    private int   $periodId;

    public function run(): void
    {
        $this->command->info('Memulai TenantDemoSeeder (full modules)...');

        DB::transaction(function () {
            // ── Core ──────────────────────────────────────────────
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
            $this->seedCurrencies();
            // ── HRM ───────────────────────────────────────────────
            $this->seedEmployees();
            $this->seedDepartments();
            $this->seedWorkShifts();
            $this->seedAttendances();
            $this->seedOvertimeRequests();
            $this->seedLeaveRequests();
            $this->seedPerformanceReviews();
            $this->seedRecruitment();
            $this->seedTraining();
            $this->seedDisciplinary();
            $this->seedSalaryComponents();
            $this->seedPayroll();
            $this->seedReimbursements();
            // ── Purchasing & Inventory ────────────────────────────
            $this->seedPurchaseRequisitions();
            $this->seedPurchaseOrders();
            $this->seedGoodsReceipts();
            $this->seedPurchaseReturns();
            $this->seedLandedCost();
            $this->seedWms();
            $this->seedConsignment();
            // ── Sales ─────────────────────────────────────────────
            $this->seedSalesOrders();
            $this->seedQuotations();
            $this->seedDeliveryOrders();
            $this->seedDownPayments();
            $this->seedSalesReturns();
            $this->seedInvoicesAndPayables();
            $this->seedBulkPayments();
            // ── Finance ───────────────────────────────────────────
            $this->seedBankAccount();
            $this->seedJournalEntries();
            $this->seedRecurringJournals();
            $this->seedAssets();
            $this->seedBudgets();
            $this->seedDeferredItem();
            $this->seedWriteoff();
            // ── Analytics & AI ────────────────────────────────────
            $this->seedKpiTargets();
            $this->seedSimulation();
            $this->seedAnomalyAlerts();
            $this->seedAiMemory();
            $this->seedZeroInputLogs();
            // ── CRM & Sales Tools ─────────────────────────────────
            $this->seedCrmLeads();
            $this->seedCommission();
            $this->seedHelpdesk();
            $this->seedSubscriptionBilling();
            // ── Operations ────────────────────────────────────────
            $this->seedManufacturing();
            $this->seedQualityControl();
            $this->seedFleet();
            $this->seedContracts();
            $this->seedProjects();
            $this->seedTimesheets();
            $this->seedShipping();
            $this->seedEcommerce(); 
            $this->seedPrinting();
            // ── Agriculture & Livestock ───────────────────────────
            $this->seedFarmPlots();
            $this->seedLivestockEnhancement();
            $this->seedFisheries();
            // ── Industry Verticals ────────────────────────────────
            $this->seedTourTravel();
            $this->seedCosmetic();
            $this->seedSupplierScorecard();
            // ── Settings & Config ─────────────────────────────────
            $this->seedApprovalWorkflow();
            $this->seedTransactions();
            $this->seedReminders();
            $this->seedNotifications();
            $this->seedCustomFields();
            $this->seedCompanyGroup();
            $this->seedDocumentTemplate();
            $this->seedWorkflows();
            $this->seedApiTokens();
            // ── Hotel Module ──────────────────────────────────────
            $this->seedHotelFrontOffice();
            $this->seedHotelFbModule();
            $this->seedHotelHousekeeping();
            $this->seedHotelSpa();
            $this->seedHotelNightAudit();
            $this->seedHotelRevenueManagement();
            // ── Telecom ───────────────────────────────────────────
            $this->seedTelecom();
            // ── Healthcare ────────────────────────────────────────
            $this->seedHealthcare();
            // ── IoT Devices ───────────────────────────────────────
            $this->seedIotDevices();
        });

        $this->command->info('TenantDemoSeeder selesai!');
        $this->command->table(['Item', 'Value'], [
            ['Tenant',         'PT Maju Bersama Indonesia'],
            ['Admin Email',    'admin@majubersama.com'],
            ['Manager Email',  'manager@majubersama.com'],
            ['Staff Email',    'staff@majubersama.com'], 
            ['Password',       'password'],
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  CORE
    // ══════════════════════════════════════════════════════════════

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
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        } else {
            $this->tenantId = $tenant->id;
        }
    }

    private function seedUsers(): void
    {
        $users = [
            ['email' => 'admin@majubersama.com',   'name' => 'Budi Santoso',  'role' => 'admin'],
            ['email' => 'manager@majubersama.com',  'name' => 'Siti Rahayu',   'role' => 'manager'],
            ['email' => 'staff@majubersama.com',    'name' => 'Andi Wijaya',   'role' => 'staff'],
            ['email' => 'kasir@majubersama.com',    'name' => 'Dewi Lestari',  'role' => 'kasir'],
            ['email' => 'gudang@majubersama.com',   'name' => 'Rudi Hartono',  'role' => 'gudang'],
        ];
        foreach ($users as $u) {
            $row = DB::table('users')->where('email', $u['email'])->first();
            if (!$row) {
                $id = DB::table('users')->insertGetId([
                    'tenant_id'          => $this->tenantId,
                    'name'               => $u['name'],
                    'email'              => $u['email'],
                    'email_verified_at'  => now(),
                    'password'           => Hash::make('password'),
                    'role'               => $u['role'],
                    'is_active'          => true,
                    'created_at'         => now(),
                    'updated_at'         => now(),
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
        $defaultCoa = [
            ['code'=>'1000','name'=>'ASET LANCAR','type'=>'asset','normal_balance'=>'debit','level'=>1,'is_header'=>true],
            ['code'=>'1100','name'=>'Kas dan Setara Kas','type'=>'asset','normal_balance'=>'debit','level'=>2,'is_header'=>true,'parent_code'=>'1000'],
            ['code'=>'1101','name'=>'Kas','type'=>'asset','normal_balance'=>'debit','level'=>3,'is_header'=>false,'parent_code'=>'1100'],
            ['code'=>'1102','name'=>'Bank BCA','type'=>'asset','normal_balance'=>'debit','level'=>3,'is_header'=>false,'parent_code'=>'1100'],
            ['code'=>'1103','name'=>'Piutang Usaha','type'=>'asset','normal_balance'=>'debit','level'=>2,'is_header'=>false,'parent_code'=>'1000'],
            ['code'=>'1104','name'=>'Persediaan','type'=>'asset','normal_balance'=>'debit','level'=>2,'is_header'=>false,'parent_code'=>'1000'],
            ['code'=>'1105','name'=>'Uang Muka Pembelian','type'=>'asset','normal_balance'=>'debit','level'=>2,'is_header'=>false,'parent_code'=>'1000'],
            ['code'=>'1106','name'=>'Biaya Dibayar Dimuka','type'=>'asset','normal_balance'=>'debit','level'=>2,'is_header'=>false,'parent_code'=>'1000'],
            ['code'=>'1500','name'=>'ASET TETAP','type'=>'asset','normal_balance'=>'debit','level'=>1,'is_header'=>true],
            ['code'=>'1501','name'=>'Kendaraan','type'=>'asset','normal_balance'=>'debit','level'=>2,'is_header'=>false,'parent_code'=>'1500'],
            ['code'=>'1502','name'=>'Peralatan','type'=>'asset','normal_balance'=>'debit','level'=>2,'is_header'=>false,'parent_code'=>'1500'],
            ['code'=>'1503','name'=>'Akumulasi Penyusutan','type'=>'asset','normal_balance'=>'credit','level'=>2,'is_header'=>false,'parent_code'=>'1500'],
            ['code'=>'2000','name'=>'LIABILITAS','type'=>'liability','normal_balance'=>'credit','level'=>1,'is_header'=>true],
            ['code'=>'2100','name'=>'Utang Usaha','type'=>'liability','normal_balance'=>'credit','level'=>2,'is_header'=>false,'parent_code'=>'2000'],
            ['code'=>'2101','name'=>'Utang Pajak','type'=>'liability','normal_balance'=>'credit','level'=>2,'is_header'=>false,'parent_code'=>'2000'],
            ['code'=>'2102','name'=>'Utang Gaji','type'=>'liability','normal_balance'=>'credit','level'=>2,'is_header'=>false,'parent_code'=>'2000'],
            ['code'=>'2103','name'=>'PPN Keluaran','type'=>'liability','normal_balance'=>'credit','level'=>2,'is_header'=>false,'parent_code'=>'2000'],
            ['code'=>'3000','name'=>'EKUITAS','type'=>'equity','normal_balance'=>'credit','level'=>1,'is_header'=>true],
            ['code'=>'3100','name'=>'Modal Disetor','type'=>'equity','normal_balance'=>'credit','level'=>2,'is_header'=>false,'parent_code'=>'3000'],
            ['code'=>'3200','name'=>'Laba Ditahan','type'=>'equity','normal_balance'=>'credit','level'=>2,'is_header'=>false,'parent_code'=>'3000'],
            ['code'=>'4000','name'=>'PENDAPATAN','type'=>'revenue','normal_balance'=>'credit','level'=>1,'is_header'=>true],
            ['code'=>'4100','name'=>'Penjualan','type'=>'revenue','normal_balance'=>'credit','level'=>2,'is_header'=>false,'parent_code'=>'4000'],
            ['code'=>'4101','name'=>'Penjualan Produk','type'=>'revenue','normal_balance'=>'credit','level'=>3,'is_header'=>false,'parent_code'=>'4100'],
            ['code'=>'4102','name'=>'Pendapatan Jasa','type'=>'revenue','normal_balance'=>'credit','level'=>3,'is_header'=>false,'parent_code'=>'4100'],
            ['code'=>'5000','name'=>'BEBAN','type'=>'expense','normal_balance'=>'debit','level'=>1,'is_header'=>true],
            ['code'=>'5100','name'=>'Beban Pokok Penjualan','type'=>'expense','normal_balance'=>'debit','level'=>2,'is_header'=>false,'parent_code'=>'5000'],
            ['code'=>'5200','name'=>'Beban Operasional','type'=>'expense','normal_balance'=>'debit','level'=>2,'is_header'=>false,'parent_code'=>'5000'],
            ['code'=>'5201','name'=>'Beban Gaji','type'=>'expense','normal_balance'=>'debit','level'=>3,'is_header'=>false,'parent_code'=>'5200'],
            ['code'=>'5202','name'=>'Beban Sewa','type'=>'expense','normal_balance'=>'debit','level'=>3,'is_header'=>false,'parent_code'=>'5200'],
            ['code'=>'5203','name'=>'Beban Utilitas','type'=>'expense','normal_balance'=>'debit','level'=>3,'is_header'=>false,'parent_code'=>'5200'],
            ['code'=>'5204','name'=>'Beban Penyusutan','type'=>'expense','normal_balance'=>'debit','level'=>3,'is_header'=>false,'parent_code'=>'5200'],
            ['code'=>'5205','name'=>'Beban Pemasaran','type'=>'expense','normal_balance'=>'debit','level'=>3,'is_header'=>false,'parent_code'=>'5200'],
            ['code'=>'5206','name'=>'Beban Administrasi','type'=>'expense','normal_balance'=>'debit','level'=>3,'is_header'=>false,'parent_code'=>'5200'],
            ['code'=>'5207','name'=>'Beban Transportasi','type'=>'expense','normal_balance'=>'debit','level'=>3,'is_header'=>false,'parent_code'=>'5200'],
            ['code'=>'5208','name'=>'Beban Lain-lain','type'=>'expense','normal_balance'=>'debit','level'=>3,'is_header'=>false,'parent_code'=>'5200'],
        ];
        foreach ($defaultCoa as $coa) {
            if (DB::table('chart_of_accounts')->where('tenant_id',$this->tenantId)->where('code',$coa['code'])->exists()) continue;
            $parentId = null;
            if (isset($coa['parent_code'])) {
                $parentId = DB::table('chart_of_accounts')->where('tenant_id',$this->tenantId)->where('code',$coa['parent_code'])->value('id');
            }
            DB::table('chart_of_accounts')->insert([
                'tenant_id'      => $this->tenantId,
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
        $accounts = DB::table('chart_of_accounts')->where('tenant_id',$this->tenantId)->get();
        foreach ($accounts as $acc) { $this->coaMap[$acc->code] = $acc->id; }
    }

    private function seedAccountingPeriod(): void
    {
        if (!DB::table('accounting_periods')->where('tenant_id',$this->tenantId)->where('name','Maret 2026')->exists()) {
            DB::table('accounting_periods')->insert([
                ['tenant_id'=>$this->tenantId,'name'=>'Januari 2026','start_date'=>'2026-01-01','end_date'=>'2026-01-31','status'=>'closed','created_at'=>now(),'updated_at'=>now()],
                ['tenant_id'=>$this->tenantId,'name'=>'Februari 2026','start_date'=>'2026-02-01','end_date'=>'2026-02-28','status'=>'closed','created_at'=>now(),'updated_at'=>now()],
                ['tenant_id'=>$this->tenantId,'name'=>'Maret 2026','start_date'=>'2026-03-01','end_date'=>'2026-03-31','status'=>'open','created_at'=>now(),'updated_at'=>now()],
                ['tenant_id'=>$this->tenantId,'name'=>'April 2026','start_date'=>'2026-04-01','end_date'=>'2026-04-30','status'=>'open','created_at'=>now(),'updated_at'=>now()],
            ]);
        }
        $this->periodId = DB::table('accounting_periods')->where('tenant_id',$this->tenantId)->where('name','Maret 2026')->value('id');
    }

    private function seedWarehouse(): void
    {
        $wh = DB::table('warehouses')->where('tenant_id',$this->tenantId)->first();
        if (!$wh) {
            $this->warehouseId = DB::table('warehouses')->insertGetId([
                'tenant_id'=>$this->tenantId,'name'=>'Gudang Utama Jakarta','code'=>'GDG-JKT',
                'address'=>'Jl. Raya Bekasi KM 18, Jakarta Timur','is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
            ]);
            DB::table('warehouses')->insert([
                'tenant_id'=>$this->tenantId,'name'=>'Gudang Cabang Surabaya','code'=>'GDG-SBY',
                'address'=>'Jl. Rungkut Industri No. 5, Surabaya','is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
            ]);
        } else {
            $this->warehouseId = $wh->id;
        }
    }

    private function seedTaxRates(): void
    {
        foreach ([
            ['name'=>'PPN 11%','code'=>'PPN','type'=>'ppn','rate'=>11.0],
            ['name'=>'PPh 21','code'=>'PPH21','type'=>'pph21','rate'=>5.0],
            ['name'=>'PPh 23','code'=>'PPH23','type'=>'pph23','rate'=>2.0],
            ['name'=>'PPh Final 0.5%','code'=>'PPH_FINAL','type'=>'pph_final','rate'=>0.5],
        ] as $t) {
            DB::table('tax_rates')->updateOrInsert(
                ['tenant_id'=>$this->tenantId,'code'=>$t['code']],
                array_merge($t,['tenant_id'=>$this->tenantId,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()])
            );
        }
    }

    private function seedCostCenters(): void
    {
        foreach ([
            ['code'=>'CC-OPS','name'=>'Operasional','type'=>'department'],
            ['code'=>'CC-SALES','name'=>'Penjualan','type'=>'department'],
            ['code'=>'CC-FIN','name'=>'Keuangan','type'=>'department'],
            ['code'=>'CC-HRD','name'=>'SDM & Umum','type'=>'department'],
            ['code'=>'CC-IT','name'=>'Teknologi','type'=>'department'],
            ['code'=>'CC-MFG','name'=>'Produksi','type'=>'department'],
        ] as $c) {
            DB::table('cost_centers')->updateOrInsert(
                ['tenant_id'=>$this->tenantId,'code'=>$c['code']],
                array_merge($c,['tenant_id'=>$this->tenantId,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()])
            );
        }
    }

    private function seedBusinessConstraints(): void
    {
        foreach ([
            ['key'=>'no_sell_below_cost','label'=>'Larang jual di bawah HPP','value_type'=>'boolean','value'=>'true'],
            ['key'=>'max_discount_pct','label'=>'Diskon maksimum (%)','value_type'=>'percentage','value'=>'20'],
            ['key'=>'min_cash_balance','label'=>'Saldo kas minimum (Rp)','value_type'=>'amount','value'=>'5000000'],
            ['key'=>'confirm_above_amount','label'=>'Konfirmasi order di atas (Rp)','value_type'=>'amount','value'=>'50000000'],
            ['key'=>'auto_approve_po','label'=>'Auto-approve PO di bawah (Rp)','value_type'=>'amount','value'=>'10000000'],
        ] as $r) {
            DB::table('business_constraints')->updateOrInsert(
                ['tenant_id'=>$this->tenantId,'key'=>$r['key']],
                array_merge($r,['tenant_id'=>$this->tenantId,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()])
            );
        }
    }

    private function seedExpenseCategories(): void
    {
        foreach ([
            ['name'=>'Penjualan Produk','code'=>'SALES','type'=>'income','coa_code'=>'4101'],
            ['name'=>'Pendapatan Jasa','code'=>'SERVICE','type'=>'income','coa_code'=>'4102'],
            ['name'=>'Gaji & Tunjangan','code'=>'SALARY','type'=>'expense','coa_code'=>'5201'],
            ['name'=>'Sewa Kantor','code'=>'RENT','type'=>'expense','coa_code'=>'5202'],
            ['name'=>'Listrik & Air','code'=>'UTILITY','type'=>'expense','coa_code'=>'5203'],
            ['name'=>'Pemasaran & Iklan','code'=>'MARKETING','type'=>'expense','coa_code'=>'5205'],
            ['name'=>'Administrasi','code'=>'ADMIN','type'=>'expense','coa_code'=>'5206'],
            ['name'=>'Transportasi','code'=>'TRANSPORT','type'=>'expense','coa_code'=>'5207'],
        ] as $c) {
            DB::table('expense_categories')->updateOrInsert(
                ['tenant_id'=>$this->tenantId,'code'=>$c['code']],
                ['tenant_id'=>$this->tenantId,'name'=>$c['name'],'code'=>$c['code'],'type'=>$c['type'],
                 'coa_account_code'=>$c['coa_code'],'is_active'=>true,'created_at'=>now(),'updated_at'=>now()]
            );
        }
    }

    private function seedCurrencies(): void
    {
        foreach ([
            ['code'=>'IDR','name'=>'Indonesian Rupiah','symbol'=>'Rp','rate_to_idr'=>1,'is_base'=>true],
            ['code'=>'USD','name'=>'US Dollar','symbol'=>'$','rate_to_idr'=>15800,'is_base'=>false],
            ['code'=>'SGD','name'=>'Singapore Dollar','symbol'=>'S$','rate_to_idr'=>11700,'is_base'=>false],
        ] as $c) {
            DB::table('currencies')->updateOrInsert(
                ['tenant_id'=>$this->tenantId,'code'=>$c['code']],
                array_merge($c,['tenant_id'=>$this->tenantId,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()])
            );
        }
    }

    private function seedProducts(): void
    {
        $products = [
            ['name'=>'Laptop Asus VivoBook 15','sku'=>'LPT-ASUS-001','category'=>'Elektronik','unit'=>'unit','price_buy'=>7500000,'price_sell'=>9500000,'stock_min'=>5],
            ['name'=>'Laptop Lenovo IdeaPad 3','sku'=>'LPT-LNV-001','category'=>'Elektronik','unit'=>'unit','price_buy'=>6800000,'price_sell'=>8800000,'stock_min'=>5],
            ['name'=>'Monitor LG 24 inch FHD','sku'=>'MON-LG-001','category'=>'Elektronik','unit'=>'unit','price_buy'=>1800000,'price_sell'=>2500000,'stock_min'=>10],
            ['name'=>'Keyboard Mechanical Rexus','sku'=>'KBD-REX-001','category'=>'Aksesoris','unit'=>'unit','price_buy'=>350000,'price_sell'=>550000,'stock_min'=>20],
            ['name'=>'Mouse Wireless Logitech M235','sku'=>'MSE-LOG-001','category'=>'Aksesoris','unit'=>'unit','price_buy'=>180000,'price_sell'=>280000,'stock_min'=>30],
            ['name'=>'Printer Canon PIXMA G2020','sku'=>'PRN-CAN-001','category'=>'Elektronik','unit'=>'unit','price_buy'=>1200000,'price_sell'=>1650000,'stock_min'=>8],
            ['name'=>'UPS APC 650VA','sku'=>'UPS-APC-001','category'=>'Elektronik','unit'=>'unit','price_buy'=>650000,'price_sell'=>950000,'stock_min'=>10],
            ['name'=>'Headset Gaming Rexus HX20','sku'=>'HST-REX-001','category'=>'Aksesoris','unit'=>'unit','price_buy'=>250000,'price_sell'=>420000,'stock_min'=>15],
            ['name'=>'Flashdisk Sandisk 64GB','sku'=>'FD-SND-001','category'=>'Storage','unit'=>'pcs','price_buy'=>85000,'price_sell'=>135000,'stock_min'=>50],
            ['name'=>'SSD External WD 1TB','sku'=>'SSD-WD-001','category'=>'Storage','unit'=>'unit','price_buy'=>750000,'price_sell'=>1100000,'stock_min'=>10],
        ];
        foreach ($products as $p) {
            $row = DB::table('products')->where('tenant_id',$this->tenantId)->where('sku',$p['sku'])->first();
            if (!$row) {
                $qty = rand(20,100);
                $id = DB::table('products')->insertGetId(array_merge($p,[
                    'tenant_id'=>$this->tenantId,'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
                ]));
                DB::table('product_stocks')->insertOrIgnore([
                    'product_id'=>$id,'warehouse_id'=>$this->warehouseId,'quantity'=>$qty,'created_at'=>now(),'updated_at'=>now(),
                ]);
                DB::table('stock_movements')->insert([
                    'tenant_id'=>$this->tenantId,'product_id'=>$id,'warehouse_id'=>$this->warehouseId,
                    'user_id'=>$this->adminId,'type'=>'in','quantity'=>$qty,'quantity_before'=>0,'quantity_after'=>$qty,
                    'reference'=>'OPENING-STOCK','notes'=>'Stok awal',
                    'created_at'=>Carbon::now()->subDays(30),'updated_at'=>Carbon::now()->subDays(30),
                ]);
            } else { $id = $row->id; }
            $this->productIds[] = $id;
        }
    }

    private function seedCustomers(): void
    {
        $customers = [
            ['name'=>'PT Teknologi Nusantara','email'=>'procurement@teknologi-nusantara.co.id','phone'=>'021-7654321','company'=>'PT Teknologi Nusantara','credit_limit'=>100000000],
            ['name'=>'CV Mitra Komputer','email'=>'order@mitrakomputer.com','phone'=>'022-8765432','company'=>'CV Mitra Komputer','credit_limit'=>50000000],
            ['name'=>'Toko Elektronik Jaya','email'=>'toko@elektronikjaya.com','phone'=>'031-9876543','company'=>'Toko Elektronik Jaya','credit_limit'=>30000000],
            ['name'=>'Budi Prasetyo','email'=>'budi.prasetyo@gmail.com','phone'=>'0812-3456789','company'=>null,'credit_limit'=>5000000],
            ['name'=>'PT Solusi Digital Prima','email'=>'finance@solusidigital.id','phone'=>'021-5544332','company'=>'PT Solusi Digital Prima','credit_limit'=>200000000],
        ];
        foreach ($customers as $c) {
            $row = DB::table('customers')->where('tenant_id',$this->tenantId)->where('email',$c['email'])->first();
            if (!$row) {
                $id = DB::table('customers')->insertGetId(array_merge($c,[
                    'tenant_id'=>$this->tenantId,'address'=>'Jakarta','is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
                ]));
            } else { $id = $row->id; }
            $this->customerIds[] = $id;
        }
    }

    private function seedSuppliers(): void
    {
        $suppliers = [
            ['name'=>'PT Asus Indonesia','email'=>'sales@asus.co.id','phone'=>'021-1234567','company'=>'PT Asus Indonesia','bank_name'=>'BCA','bank_account'=>'0987654321'],
            ['name'=>'PT Lenovo Indonesia','email'=>'order@lenovo.co.id','phone'=>'021-2345678','company'=>'PT Lenovo Indonesia','bank_name'=>'Mandiri','bank_account'=>'1122334455'],
            ['name'=>'PT LG Electronics','email'=>'b2b@lge.co.id','phone'=>'021-3456789','company'=>'PT LG Electronics','bank_name'=>'BNI','bank_account'=>'5566778899'],
            ['name'=>'CV Distributor Aksesoris','email'=>'sales@distaksesoris.com','phone'=>'022-4567890','company'=>'CV Distributor Aksesoris','bank_name'=>'BRI','bank_account'=>'9988776655'],
        ];
        foreach ($suppliers as $s) {
            $row = DB::table('suppliers')->where('tenant_id',$this->tenantId)->where('email',$s['email'])->first();
            if (!$row) {
                $id = DB::table('suppliers')->insertGetId(array_merge($s,[
                    'tenant_id'=>$this->tenantId,'bank_holder'=>$s['company'],'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
                ]));
            } else { $id = $row->id; }
            $this->supplierIds[] = $id;
        }
    }

    private function seedPriceList(): void
    {
        if (DB::table('price_lists')->where('tenant_id',$this->tenantId)->exists()) return;
        $plId = DB::table('price_lists')->insertGetId([
            'tenant_id'=>$this->tenantId,'name'=>'Harga Reseller','code'=>'PL-RESELLER','type'=>'tier',
            'description'=>'Harga khusus reseller terdaftar','valid_from'=>'2026-01-01','valid_until'=>'2026-12-31',
            'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
        ]);
        foreach (array_slice($this->productIds,0,3) as $pid) {
            $product = DB::table('products')->find($pid);
            DB::table('price_list_items')->insertOrIgnore([
                'price_list_id'=>$plId,'product_id'=>$pid,'price'=>$product->price_sell*0.9,
                'discount_percent'=>10,'min_qty'=>5,'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
        if (!empty($this->customerIds)) {
            DB::table('customer_price_lists')->insertOrIgnore([
                'customer_id'=>$this->customerIds[0],'price_list_id'=>$plId,'priority'=>1,'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedLoyaltyProgram(): void
    {
        if (DB::table('loyalty_programs')->where('tenant_id',$this->tenantId)->exists()) return;
        $progId = DB::table('loyalty_programs')->insertGetId([
            'tenant_id'=>$this->tenantId,'name'=>'Program Poin MBI','points_per_idr'=>0.01,
            'idr_per_point'=>100,'min_redeem_points'=>500,'expiry_days'=>365,'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
        ]);
        foreach ([
            ['name'=>'Bronze','min_points'=>0,'multiplier'=>1.0,'color'=>'#cd7f32'],
            ['name'=>'Silver','min_points'=>1000,'multiplier'=>1.5,'color'=>'#c0c0c0'],
            ['name'=>'Gold','min_points'=>5000,'multiplier'=>2.0,'color'=>'#ffd700'],
            ['name'=>'Platinum','min_points'=>15000,'multiplier'=>3.0,'color'=>'#e5e4e2'],
        ] as $t) {
            DB::table('loyalty_tiers')->insert(array_merge($t,[
                'tenant_id'=>$this->tenantId,'program_id'=>$progId,'created_at'=>now(),'updated_at'=>now(),
            ]));
        }
        foreach (array_slice($this->customerIds,0,2) as $cid) {
            DB::table('loyalty_points')->insertOrIgnore([
                'tenant_id'=>$this->tenantId,'customer_id'=>$cid,'program_id'=>$progId,
                'total_points'=>rand(500,8000),'lifetime_points'=>rand(1000,20000),'tier'=>'Silver',
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  HRM
    // ══════════════════════════════════════════════════════════════

    private function seedEmployees(): void
    {
        $employees = [
            ['name'=>'Budi Santoso','eid'=>'EMP-001','position'=>'Direktur','dept'=>'Manajemen','salary'=>25000000,'uid'=>$this->adminId],
            ['name'=>'Siti Rahayu','eid'=>'EMP-002','position'=>'Manajer Operasional','dept'=>'Operasional','salary'=>15000000,'uid'=>$this->managerId],
            ['name'=>'Andi Wijaya','eid'=>'EMP-003','position'=>'Staff Penjualan','dept'=>'Penjualan','salary'=>6000000,'uid'=>$this->staffId],
            ['name'=>'Dewi Lestari','eid'=>'EMP-004','position'=>'Kasir','dept'=>'Keuangan','salary'=>5000000,'uid'=>null],
            ['name'=>'Rudi Hartono','eid'=>'EMP-005','position'=>'Staff Gudang','dept'=>'Logistik','salary'=>5500000,'uid'=>null],
            ['name'=>'Maya Putri','eid'=>'EMP-006','position'=>'Staff Akuntansi','dept'=>'Keuangan','salary'=>7000000,'uid'=>null],
            ['name'=>'Hendra Kusuma','eid'=>'EMP-007','position'=>'Staff IT','dept'=>'IT','salary'=>8000000,'uid'=>null],
            ['name'=>'Rina Susanti','eid'=>'EMP-008','position'=>'HRD Officer','dept'=>'SDM','salary'=>7500000,'uid'=>null],
        ];
        $firstId = null;
        foreach ($employees as $i => $e) {
            $row = DB::table('employees')->where('tenant_id',$this->tenantId)->where('employee_id',$e['eid'])->first();
            if (!$row) {
                $id = DB::table('employees')->insertGetId([
                    'tenant_id'=>$this->tenantId,'user_id'=>$e['uid'],
                    'manager_id'=>($i>0&&$firstId)?$firstId:null,
                    'employee_id'=>$e['eid'],'name'=>$e['name'],
                    'email'=>strtolower(str_replace(' ','.',$e['name'])).'@majubersama.com',
                    'phone'=>'0812-'.rand(10000000,99999999),
                    'position'=>$e['position'],'department'=>$e['dept'],
                    'join_date'=>Carbon::now()->subMonths(rand(6,36))->format('Y-m-d'),
                    'status'=>'active','salary'=>$e['salary'],
                    'bank_name'=>'BCA','bank_account'=>'1'.rand(100000000,999999999),
                    'address'=>'Jakarta','created_at'=>now(),'updated_at'=>now(),
                ]);
            } else { $id = $row->id; }
            if ($i===0) $firstId = $id;
            $this->employeeIds[] = $id;
        }
    }

    private function seedDepartments(): void
    {
        foreach ([
            ['name'=>'Manajemen','code'=>'MGT'],
            ['name'=>'Operasional','code'=>'OPS'],
            ['name'=>'Penjualan','code'=>'SALES'],
            ['name'=>'Keuangan','code'=>'FIN'],
            ['name'=>'Logistik','code'=>'LOG'],
            ['name'=>'IT','code'=>'IT'],
            ['name'=>'SDM','code'=>'HRD'],
        ] as $d) {
            DB::table('departments')->updateOrInsert(
                ['tenant_id'=>$this->tenantId,'code'=>$d['code']],
                array_merge($d,['tenant_id'=>$this->tenantId,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()])
            );
        }
    }

    private function seedWorkShifts(): void
    {
        foreach ([
            ['name'=>'Shift Pagi','start_time'=>'08:00','end_time'=>'16:00','color'=>'#3b82f6','crosses_midnight'=>false],
            ['name'=>'Shift Siang','start_time'=>'12:00','end_time'=>'20:00','color'=>'#f59e0b','crosses_midnight'=>false],
            ['name'=>'Shift Malam','start_time'=>'20:00','end_time'=>'04:00','color'=>'#6366f1','crosses_midnight'=>true],
        ] as $s) {
            DB::table('work_shifts')->updateOrInsert(
                ['tenant_id'=>$this->tenantId,'name'=>$s['name']],
                array_merge($s,['tenant_id'=>$this->tenantId,'break_minutes'=>60,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()])
            );
        }
    }

    private function seedAttendances(): void
    {
        if (empty($this->employeeIds)) return;
        $statuses = ['present','present','present','present','late','absent'];
        $empId = $this->employeeIds[0];
        for ($d=20;$d>=1;$d--) {
            $date = Carbon::now()->subDays($d)->format('Y-m-d');
            DB::table('attendances')->updateOrInsert(
                ['employee_id'=>$empId,'date'=>$date],
                ['tenant_id'=>$this->tenantId,'employee_id'=>$empId,'date'=>$date,
                 'check_in'=>'08:'.str_pad(rand(0,15),2,'0',STR_PAD_LEFT).':00',
                 'check_out'=>'17:'.str_pad(rand(0,30),2,'0',STR_PAD_LEFT).':00',
                 'status'=>$statuses[array_rand($statuses)],'created_at'=>now(),'updated_at'=>now()]
            );
        }
    }

    private function seedOvertimeRequests(): void
    {
        if (empty($this->employeeIds)) return;
        if (DB::table('overtime_requests')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach (array_slice($this->employeeIds,0,3) as $i=>$empId) {
            DB::table('overtime_requests')->insert([
                'tenant_id'=>$this->tenantId,'employee_id'=>$empId,
                'date'=>Carbon::now()->subDays($i+1)->format('Y-m-d'),
                'start_time'=>'17:00','end_time'=>'20:00','duration_minutes'=>180,
                'reason'=>'Penyelesaian laporan bulanan','status'=>$i===0?'approved':'pending',
                'approved_by'=>$i===0?$this->adminId:null,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedLeaveRequests(): void
    {
        if (count($this->employeeIds)<3) return;
        foreach ([
            ['employee_id'=>$this->employeeIds[2],'type'=>'annual','start_date'=>'2026-03-10','end_date'=>'2026-03-12','days'=>3,'status'=>'approved'],
            ['employee_id'=>$this->employeeIds[3]??$this->employeeIds[0],'type'=>'sick','start_date'=>'2026-03-15','end_date'=>'2026-03-15','days'=>1,'status'=>'approved'],
            ['employee_id'=>$this->employeeIds[4]??$this->employeeIds[0],'type'=>'annual','start_date'=>'2026-03-25','end_date'=>'2026-03-26','days'=>2,'status'=>'pending'],
        ] as $l) {
            DB::table('leave_requests')->updateOrInsert(
                ['tenant_id'=>$this->tenantId,'employee_id'=>$l['employee_id'],'start_date'=>$l['start_date']],
                array_merge($l,['tenant_id'=>$this->tenantId,'reason'=>'Keperluan pribadi',
                    'approved_by'=>$l['status']==='approved'?($this->employeeIds[1]??null):null,
                    'approved_at'=>$l['status']==='approved'?now():null,'created_at'=>now(),'updated_at'=>now()])
            );
        }
    }

    private function seedPerformanceReviews(): void
    {
        if (count($this->employeeIds)<2) return;
        DB::table('performance_reviews')->updateOrInsert(
            ['employee_id'=>$this->employeeIds[2]??$this->employeeIds[0],'period'=>'Q1 2026','period_type'=>'quarterly'],
            ['tenant_id'=>$this->tenantId,'employee_id'=>$this->employeeIds[2]??$this->employeeIds[0],
             'reviewer_id'=>$this->employeeIds[1],'period'=>'Q1 2026','period_type'=>'quarterly',
             'score_work_quality'=>4,'score_productivity'=>4,'score_teamwork'=>5,'score_initiative'=>3,'score_attendance'=>4,
             'overall_score'=>4.00,'strengths'=>'Komunikasi baik, target penjualan tercapai 110%',
             'improvements'=>'Perlu meningkatkan pengetahuan produk baru',
             'goals_next_period'=>'Target penjualan Q2 naik 15%','recommendation'=>'retain',
             'status'=>'submitted','submitted_at'=>now(),'created_at'=>now(),'updated_at'=>now()]
        );
    }

    private function seedRecruitment(): void
    {
        if (DB::table('job_postings')->where('tenant_id',$this->tenantId)->exists()) return;
        $jobId = DB::table('job_postings')->insertGetId([
            'tenant_id'=>$this->tenantId,'title'=>'Sales Executive','department'=>'Penjualan',
            'location'=>'Jakarta','type'=>'full_time',
            'description'=>'Mencari Sales Executive berpengalaman untuk area Jabodetabek',
            'requirements'=>'Min. D3, pengalaman sales 2 tahun, memiliki kendaraan',
            'salary_min'=>5000000,'salary_max'=>8000000,'quota'=>2,
            'deadline'=>Carbon::now()->addDays(30)->format('Y-m-d'),
            'status'=>'open','created_by'=>$this->adminId,'created_at'=>now(),'updated_at'=>now(),
        ]);
        foreach ([
            ['name'=>'Fajar Nugroho','email'=>'fajar@gmail.com','stage'=>'interview'],
            ['name'=>'Lina Marlina','email'=>'lina@gmail.com','stage'=>'screening'],
            ['name'=>'Doni Setiawan','email'=>'doni@gmail.com','stage'=>'applied'],
        ] as $a) {
            DB::table('job_applications')->insert([
                'tenant_id'=>$this->tenantId,'job_posting_id'=>$jobId,
                'applicant_name'=>$a['name'],'applicant_email'=>$a['email'],
                'applicant_phone'=>'0812-'.rand(10000000,99999999),
                'cover_letter'=>'Saya tertarik dengan posisi ini.','stage'=>$a['stage'],
                'reviewed_by'=>$this->adminId,'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedTraining(): void
    {
        if (DB::table('training_programs')->where('tenant_id',$this->tenantId)->exists()) return;
        $progId = DB::table('training_programs')->insertGetId([
            'tenant_id'=>$this->tenantId,'name'=>'Pelatihan Teknik Penjualan','category'=>'soft-skill',
            'description'=>'Meningkatkan kemampuan negosiasi dan closing penjualan',
            'provider'=>'Sales Academy Indonesia','duration_hours'=>16,'cost'=>2500000,
            'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
        ]);
        $sessionId = DB::table('training_sessions')->insertGetId([
            'tenant_id'=>$this->tenantId,'training_program_id'=>$progId,
            'start_date'=>Carbon::now()->addDays(7)->format('Y-m-d'),
            'end_date'=>Carbon::now()->addDays(8)->format('Y-m-d'),
            'location'=>'Ruang Meeting Lt. 3','trainer'=>'Bpk. Hendra Wijaya',
            'max_participants'=>15,'status'=>'scheduled','created_at'=>now(),'updated_at'=>now(),
        ]);
        foreach (array_slice($this->employeeIds,2,2) as $empId) {
            DB::table('training_participants')->insertOrIgnore([
                'tenant_id'=>$this->tenantId,'training_session_id'=>$sessionId,
                'employee_id'=>$empId,'status'=>'registered','created_at'=>now(),'updated_at'=>now(),
            ]);
        }
        if (!empty($this->employeeIds)) {
            DB::table('employee_certifications')->insert([
                'tenant_id'=>$this->tenantId,'employee_id'=>$this->employeeIds[0],
                'name'=>'Certified Sales Professional','issuer'=>'Sales Academy Indonesia',
                'certificate_number'=>'CSP-2025-001','issued_date'=>'2025-06-01',
                'expiry_date'=>'2027-06-01','status'=>'active','created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedDisciplinary(): void
    {
        if (empty($this->employeeIds)) return;
        DB::table('disciplinary_letters')->updateOrInsert(
            ['tenant_id'=>$this->tenantId,'letter_number'=>'SP1/MBI/III/2026/001'],
            ['tenant_id'=>$this->tenantId,'employee_id'=>$this->employeeIds[4]??$this->employeeIds[0],
             'level'=>'sp1','letter_number'=>'SP1/MBI/III/2026/001','issued_date'=>'2026-03-10',
             'valid_until'=>'2026-06-10','violation_type'=>'Keterlambatan berulang',
             'violation_description'=>'Karyawan terlambat lebih dari 5 kali dalam sebulan',
             'corrective_action'=>'Hadir tepat waktu sesuai jadwal shift',
             'consequences'=>'Jika terulang akan diberikan SP2','status'=>'issued',
             'issued_by'=>$this->adminId,'created_at'=>now(),'updated_at'=>now()]
        );
    }

    private function seedSalaryComponents(): void
    {
        foreach ([
            ['name'=>'Tunjangan Transport','code'=>'T_TRANSPORT','type'=>'allowance','calc_type'=>'fixed','default_amount'=>500000,'taxable'=>false],
            ['name'=>'Tunjangan Makan','code'=>'T_MAKAN','type'=>'allowance','calc_type'=>'fixed','default_amount'=>400000,'taxable'=>false],
            ['name'=>'Tunjangan Jabatan','code'=>'T_JABATAN','type'=>'allowance','calc_type'=>'percent_base','default_amount'=>10,'taxable'=>true],
            ['name'=>'Potongan BPJS Kes.','code'=>'D_BPJS_KES','type'=>'deduction','calc_type'=>'percent_base','default_amount'=>1,'taxable'=>false],
            ['name'=>'Potongan BPJS TK','code'=>'D_BPJS_TK','type'=>'deduction','calc_type'=>'percent_base','default_amount'=>2,'taxable'=>false],
        ] as $c) {
            DB::table('salary_components')->updateOrInsert(
                ['tenant_id'=>$this->tenantId,'code'=>$c['code']],
                array_merge($c,['tenant_id'=>$this->tenantId,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()])
            );
        }
    }

    private function seedPayroll(): void
    {
        if (empty($this->employeeIds)) return;
        if (DB::table('payroll_runs')->where('tenant_id',$this->tenantId)->where('period','2026-02')->exists()) return;
        $runId = DB::table('payroll_runs')->insertGetId([
            'tenant_id'=>$this->tenantId,'period'=>'2026-02','status'=>'paid',
            'total_gross'=>0,'total_deductions'=>0,'total_net'=>0,
            'processed_by'=>$this->adminId,'processed_at'=>Carbon::parse('2026-02-28'),
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        $totalGross=0; $totalNet=0;
        foreach (array_slice($this->employeeIds,0,5) as $empId) {
            $emp = DB::table('employees')->find($empId);
            $base=$emp->salary; $allowances=900000; $gross=$base+$allowances;
            $bpjs=round($base*0.03); $pph21=round($gross*0.05); $net=$gross-$bpjs-$pph21;
            DB::table('payroll_items')->insert([
                'tenant_id'=>$this->tenantId,'payroll_run_id'=>$runId,'employee_id'=>$empId,
                'base_salary'=>$base,'working_days'=>22,'present_days'=>21,'absent_days'=>1,'late_days'=>0,
                'allowances'=>$allowances,'overtime_pay'=>0,'deduction_absent'=>round($base/22),
                'deduction_late'=>0,'deduction_other'=>0,'gross_salary'=>$gross,
                'tax_pph21'=>$pph21,'bpjs_employee'=>$bpjs,'net_salary'=>$net,
                'status'=>'paid','created_at'=>now(),'updated_at'=>now(),
            ]);
            $totalGross+=$gross; $totalNet+=$net;
        }
        DB::table('payroll_runs')->where('id',$runId)->update([
            'total_gross'=>$totalGross,'total_deductions'=>$totalGross-$totalNet,'total_net'=>$totalNet,
        ]);
    }

    private function seedReimbursements(): void
    {
        if (DB::table('reimbursements')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach (array_slice($this->employeeIds,0,3) as $i=>$empId) {
            DB::table('reimbursements')->insert([
                'tenant_id'=>$this->tenantId,'employee_id'=>$empId,'requested_by'=>$this->adminId,
                'number'=>'RMB/MBI/2026/'.str_pad($i+1,3,'0',STR_PAD_LEFT),
                'description'=>['Biaya Perjalanan Dinas','Pembelian ATK','Biaya Makan Klien'][$i],
                'amount'=>[850000,320000,450000][$i],
                'category'=>['transport','office_supply','entertainment'][$i],
                'expense_date'=>Carbon::now()->subDays($i+2)->format('Y-m-d'),
                'status'=>$i===0?'approved':($i===1?'paid':'submitted'),
                'approved_by'=>$i<2?$this->adminId:null,
                'approved_at'=>$i<2?now():null,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  PURCHASING & INVENTORY
    // ══════════════════════════════════════════════════════════════

    private function seedPurchaseRequisitions(): void
    {
        if (DB::table('purchase_requisitions')->where('tenant_id',$this->tenantId)->exists()) return;
        $prId = DB::table('purchase_requisitions')->insertGetId([
            'tenant_id'=>$this->tenantId,'number'=>'PR/MBI/2026/001',
            'requested_by'=>$this->staffId,'department'=>'Operasional',
            'required_date'=>Carbon::now()->addDays(14)->format('Y-m-d'),
            'status'=>'approved','purpose'=>'Kebutuhan stok Q2 2026',
            'approved_by'=>$this->adminId,'approved_at'=>now(),
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        foreach (array_slice($this->productIds,0,2) as $pid) {
            $product = DB::table('products')->find($pid);
            DB::table('purchase_requisition_items')->insert([
                'purchase_requisition_id'=>$prId,'product_id'=>$pid,
                'quantity'=>10,'description'=>$product->name,
                'unit'=>$product->unit,'estimated_price'=>$product->price_buy,
                'estimated_total'=>$product->price_buy*10,
                'notes'=>'Stok menipis','created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedPurchaseOrders(): void
    {
        if (empty($this->supplierIds)||empty($this->productIds)) return;
        if (DB::table('purchase_orders')->where('tenant_id',$this->tenantId)->exists()) return;
        $pos = [
            ['supplier_id'=>$this->supplierIds[0],'number'=>'PO/MBI/2026/001','status'=>'received','date'=>'2026-02-10','items'=>[0,1]],
            ['supplier_id'=>$this->supplierIds[2],'number'=>'PO/MBI/2026/002','status'=>'sent','date'=>'2026-03-05','items'=>[2,3]],
            ['supplier_id'=>$this->supplierIds[3],'number'=>'PO/MBI/2026/003','status'=>'draft','date'=>'2026-03-15','items'=>[4,7]],
        ];
        foreach ($pos as $po) {
            $subtotal=0; $itemsData=[];
            foreach ($po['items'] as $pidx) {
                $product = DB::table('products')->find($this->productIds[$pidx]);
                $qty=rand(5,20); $total=$product->price_buy*$qty; $subtotal+=$total;
                $itemsData[]=['product_id'=>$product->id,'qty'=>$qty,'price'=>$product->price_buy,'total'=>$total];
            }
            $tax=round($subtotal*0.11);
            $poId = DB::table('purchase_orders')->insertGetId([
                'tenant_id'=>$this->tenantId,'supplier_id'=>$po['supplier_id'],'user_id'=>$this->adminId,
                'warehouse_id'=>$this->warehouseId,'number'=>$po['number'],'status'=>$po['status'],
                'approval_status'=>'approved','date'=>$po['date'],
                'expected_date'=>Carbon::parse($po['date'])->addDays(7)->format('Y-m-d'),
                'subtotal'=>$subtotal,'discount'=>0,'tax'=>$tax,'total'=>$subtotal+$tax,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
            foreach ($itemsData as $item) {
                DB::table('purchase_order_items')->insert([
                    'purchase_order_id'=>$poId,'product_id'=>$item['product_id'],
                    'quantity_ordered'=>$item['qty'],'quantity_received'=>$po['status']==='received'?$item['qty']:0,
                    'price'=>$item['price'],'total'=>$item['total'],'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
            if ($po['status']==='received') {
                DB::table('payables')->insertOrIgnore([
                    'tenant_id'=>$this->tenantId,'purchase_order_id'=>$poId,'supplier_id'=>$po['supplier_id'],
                    'number'=>'AP/'.$po['number'],'total_amount'=>$subtotal+$tax,'paid_amount'=>0,
                    'remaining_amount'=>$subtotal+$tax,'status'=>'unpaid',
                    'due_date'=>Carbon::parse($po['date'])->addDays(30)->format('Y-m-d'),
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }
    }

    private function seedGoodsReceipts(): void
    {
        if (DB::table('goods_receipts')->where('tenant_id',$this->tenantId)->exists()) return;
        $po = DB::table('purchase_orders')->where('tenant_id',$this->tenantId)->where('status','received')->first();
        if (!$po) return;
        $grId = DB::table('goods_receipts')->insertGetId([
            'tenant_id'=>$this->tenantId,'purchase_order_id'=>$po->id,
            'warehouse_id'=>$this->warehouseId,
            'number'=>'GR/MBI/2026/001','receipt_date'=>Carbon::parse($po->date)->addDays(5)->format('Y-m-d'),
            'status'=>'confirmed','notes'=>'Barang diterima lengkap dan dalam kondisi baik',
            'received_by'=>$this->adminId,'created_at'=>now(),'updated_at'=>now(),
        ]);
        $poItems = DB::table('purchase_order_items')->where('purchase_order_id',$po->id)->get();
        foreach ($poItems as $item) {
            DB::table('goods_receipt_items')->insert([
                'goods_receipt_id'=>$grId,'product_id'=>$item->product_id,
                'purchase_order_item_id'=>$item->id,
                'quantity_received'=>$item->quantity_received,
                'quantity_accepted'=>$item->quantity_received,'quantity_rejected'=>0,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedPurchaseReturns(): void
    {
        if (DB::table('purchase_returns')->where('tenant_id',$this->tenantId)->exists()) return;
        $po = DB::table('purchase_orders')->where('tenant_id',$this->tenantId)->where('status','received')->first();
        if (!$po) return;
        $prId = DB::table('purchase_returns')->insertGetId([
            'tenant_id'=>$this->tenantId,'purchase_order_id'=>$po->id,'supplier_id'=>$po->supplier_id,
            'warehouse_id'=>$this->warehouseId,'created_by'=>$this->adminId,
            'number'=>'PR-RET/MBI/2026/001','return_date'=>Carbon::now()->subDays(5)->format('Y-m-d'),
            'reason'=>'Barang cacat / tidak sesuai spesifikasi','status'=>'completed',
            'subtotal'=>500000,'tax_amount'=>0,'total'=>500000,
            'refund_method'=>'debit_note','refund_amount'=>500000,
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        $poItem = DB::table('purchase_order_items')->where('purchase_order_id',$po->id)->first();
        if ($poItem) {
            DB::table('purchase_return_items')->insert([
                'purchase_return_id'=>$prId,'product_id'=>$poItem->product_id,
                'quantity'=>1,'price'=>$poItem->price,'total'=>$poItem->price,
                'return_reason'=>'Cacat produksi','created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedLandedCost(): void
    {
        if (DB::table('landed_costs')->where('tenant_id',$this->tenantId)->exists()) return;
        $po = DB::table('purchase_orders')->where('tenant_id',$this->tenantId)->where('status','received')->first();
        if (!$po) return;
        $lcId = DB::table('landed_costs')->insertGetId([
            'tenant_id'=>$this->tenantId,'number'=>'LC/MBI/2026/001',
            'purchase_order_id'=>$po->id,
            'date'=>Carbon::now()->subDays(5)->format('Y-m-d'),
            'total_additional_cost'=>2500000,'allocation_method'=>'by_value',
            'status'=>'posted','notes'=>'Biaya pengiriman dari supplier',
            'user_id'=>$this->adminId,
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        DB::table('landed_cost_components')->insert([
            'landed_cost_id'=>$lcId,'name'=>'Biaya Pengiriman','amount'=>2000000,
            'vendor'=>'JNE Cargo','type'=>'freight',
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        DB::table('landed_cost_components')->insert([
            'landed_cost_id'=>$lcId,'name'=>'Biaya Asuransi','amount'=>500000,
            'vendor'=>'Asuransi Umum','type'=>'insurance',
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function seedWms(): void
    {
        if (DB::table('warehouse_zones')->where('tenant_id',$this->tenantId)->exists()) return;
        $zoneId = DB::table('warehouse_zones')->insertGetId([
            'tenant_id'=>$this->tenantId,'warehouse_id'=>$this->warehouseId,
            'name'=>'Zona A - Elektronik','code'=>'ZN-A','type'=>'storage',
            'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
        ]);
        $binId = DB::table('warehouse_bins')->insertGetId([
            'tenant_id'=>$this->tenantId,'warehouse_id'=>$this->warehouseId,'zone_id'=>$zoneId,
            'code'=>'A-01-01','aisle'=>'A','rack'=>'01','shelf'=>'01',
            'max_capacity'=>500,'bin_type'=>'standard','is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
        ]);
        // Putaway rule
        DB::table('putaway_rules')->insert([
            'tenant_id'=>$this->tenantId,'warehouse_id'=>$this->warehouseId,'zone_id'=>$zoneId,
            'product_category'=>'Elektronik','priority'=>1,'is_active'=>true,
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        // Stock opname
        $sessionId = DB::table('stock_opname_sessions')->insertGetId([
            'tenant_id'=>$this->tenantId,'warehouse_id'=>$this->warehouseId,
            'number'=>'SO/MBI/2026/001','status'=>'completed',
            'opname_date'=>Carbon::now()->subDays(5)->format('Y-m-d'),
            'user_id'=>$this->adminId,'created_at'=>now(),'updated_at'=>now(),
        ]);
        foreach (array_slice($this->productIds,0,3) as $pid) {
            $stock = DB::table('product_stocks')->where('product_id',$pid)->where('warehouse_id',$this->warehouseId)->first();
            $sysQty = $stock?$stock->quantity:0;
            DB::table('stock_opname_items')->insert([
                'session_id'=>$sessionId,'product_id'=>$pid,
                'system_qty'=>$sysQty,'actual_qty'=>$sysQty+rand(-2,2),
                'difference'=>rand(-2,2),'notes'=>'Opname rutin','created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedConsignment(): void
    {
        if (DB::table('consignment_partners')->where('tenant_id',$this->tenantId)->exists()) return;
        $partnerId = DB::table('consignment_partners')->insertGetId([
            'tenant_id'=>$this->tenantId,'name'=>'Toko Elektronik Jaya','contact_person'=>'Pak Budi',
            'phone'=>'08123456789','address'=>'Jl. Pasar Baru No. 15, Jakarta',
            'commission_pct'=>10,'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
        ]);
        DB::table('consignment_partners')->insert([
            'tenant_id'=>$this->tenantId,'name'=>'Outlet Gadget Corner','contact_person'=>'Ibu Sari',
            'phone'=>'08198765432','address'=>'Mall Taman Anggrek Lt. 2, Jakarta',
            'commission_pct'=>15,'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
        ]);
        if (!empty($this->productIds)) {
            $product = DB::table('products')->find($this->productIds[0]);
            $shipId = DB::table('consignment_shipments')->insertGetId([
                'tenant_id'=>$this->tenantId,'partner_id'=>$partnerId,
                'warehouse_id'=>$this->warehouseId,'user_id'=>$this->adminId,
                'number'=>'CS/MBI/2026/001','ship_date'=>Carbon::now()->subDays(10)->format('Y-m-d'),
                'status'=>'shipped','notes'=>'Pengiriman konsinyasi pertama',
                'total_cost'=>$product->price_buy*5,'total_retail'=>$product->price_sell*5,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
            DB::table('consignment_shipment_items')->insert([
                'consignment_shipment_id'=>$shipId,'product_id'=>$this->productIds[0],
                'quantity_sent'=>5,'quantity_sold'=>2,'quantity_returned'=>0,
                'cost_price'=>$product->price_buy,'retail_price'=>$product->price_sell,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  SALES
    // ══════════════════════════════════════════════════════════════

    private function seedSalesOrders(): void
    {
        if (empty($this->customerIds)||empty($this->productIds)) return;
        if (DB::table('sales_orders')->where('tenant_id',$this->tenantId)->exists()) return;
        $orders = [
            ['customer_id'=>$this->customerIds[0],'number'=>'SO/MBI/2026/001','status'=>'delivered','date'=>'2026-02-15','items'=>[0,2,4]],
            ['customer_id'=>$this->customerIds[1],'number'=>'SO/MBI/2026/002','status'=>'processing','date'=>'2026-03-01','items'=>[1,3]],
            ['customer_id'=>$this->customerIds[2],'number'=>'SO/MBI/2026/003','status'=>'confirmed','date'=>'2026-03-10','items'=>[5,6]],
            ['customer_id'=>$this->customerIds[4],'number'=>'SO/MBI/2026/004','status'=>'pending','date'=>'2026-03-20','items'=>[0,1,9]],
        ];
        foreach ($orders as $order) {
            $subtotal=0; $itemsData=[];
            foreach ($order['items'] as $pidx) {
                $product = DB::table('products')->find($this->productIds[$pidx]);
                $qty=rand(2,10); $total=$product->price_sell*$qty; $subtotal+=$total;
                $itemsData[]=['product_id'=>$product->id,'qty'=>$qty,'price'=>$product->price_sell,'total'=>$total];
            }
            $tax=round($subtotal*0.11);
            $soId = DB::table('sales_orders')->insertGetId([
                'tenant_id'=>$this->tenantId,'customer_id'=>$order['customer_id'],'user_id'=>$this->staffId,
                'number'=>$order['number'],'status'=>$order['status'],'approval_status'=>'approved',
                'date'=>$order['date'],'delivery_date'=>Carbon::parse($order['date'])->addDays(7)->format('Y-m-d'),
                'subtotal'=>$subtotal,'discount'=>0,'tax'=>$tax,'total'=>$subtotal+$tax,
                'shipping_address'=>'Jakarta','created_at'=>now(),'updated_at'=>now(),
            ]);
            foreach ($itemsData as $item) {
                DB::table('sales_order_items')->insert([
                    'sales_order_id'=>$soId,'product_id'=>$item['product_id'],
                    'quantity'=>$item['qty'],'price'=>$item['price'],'discount'=>0,'total'=>$item['total'],
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }
    }

    private function seedQuotations(): void
    {
        if (DB::table('quotations')->where('tenant_id',$this->tenantId)->exists()) return;
        if (empty($this->customerIds)||empty($this->productIds)) return;
        $qtId = DB::table('quotations')->insertGetId([
            'tenant_id'=>$this->tenantId,'customer_id'=>$this->customerIds[0],'user_id'=>$this->staffId,
            'number'=>'QT/MBI/2026/001','date'=>Carbon::now()->subDays(5)->format('Y-m-d'),
            'valid_until'=>Carbon::now()->addDays(14)->format('Y-m-d'),
            'subtotal'=>19000000,'discount'=>0,'tax'=>2090000,'total'=>21090000,
            'status'=>'sent','notes'=>'Penawaran harga laptop untuk kebutuhan kantor',
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        $product = DB::table('products')->find($this->productIds[0]);
        DB::table('quotation_items')->insert([
            'quotation_id'=>$qtId,'product_id'=>$product->id,'quantity'=>2,
            'price'=>$product->price_sell,'discount'=>0,'total'=>$product->price_sell*2,
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function seedDeliveryOrders(): void
    {
        if (DB::table('delivery_orders')->where('tenant_id',$this->tenantId)->exists()) return;
        $so = DB::table('sales_orders')->where('tenant_id',$this->tenantId)->where('status','delivered')->first();
        if (!$so) return;
        $doId = DB::table('delivery_orders')->insertGetId([
            'tenant_id'=>$this->tenantId,'sales_order_id'=>$so->id,
            'warehouse_id'=>$this->warehouseId,'created_by'=>$this->adminId,
            'number'=>'DO/MBI/2026/001','delivery_date'=>Carbon::parse($so->date)->addDays(3)->format('Y-m-d'),
            'status'=>'delivered','shipping_address'=>'Jakarta','courier'=>'JNE',
            'tracking_number'=>'JNE'.rand(100000000,999999999),
            'notes'=>'Dikirim via JNE Regular','created_at'=>now(),'updated_at'=>now(),
        ]);
        $soItems = DB::table('sales_order_items')->where('sales_order_id',$so->id)->get();
        foreach ($soItems as $item) {
            DB::table('delivery_order_items')->insert([
                'delivery_order_id'=>$doId,'product_id'=>$item->product_id,
                'quantity_ordered'=>$item->quantity,'quantity_delivered'=>$item->quantity,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedDownPayments(): void
    {
        if (DB::table('down_payments')->where('tenant_id',$this->tenantId)->exists()) return;
        $so = DB::table('sales_orders')->where('tenant_id',$this->tenantId)->where('status','confirmed')->first();
        if (!$so) return;
        DB::table('down_payments')->insert([
            'tenant_id'=>$this->tenantId,
            'party_id'=>$so->customer_id,'party_type'=>'App\\Models\\Customer',
            'reference_id'=>$so->id,'reference_type'=>'App\\Models\\SalesOrder',
            'number'=>'DP/MBI/2026/001','amount'=>round($so->total*0.3),
            'applied_amount'=>round($so->total*0.3),'remaining_amount'=>0,
            'payment_date'=>Carbon::parse($so->date)->addDay()->format('Y-m-d'),
            'payment_method'=>'transfer','status'=>'applied',
            'created_by'=>$this->adminId,
            'notes'=>'DP 30% sesuai kesepakatan','created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function seedSalesReturns(): void
    {
        if (DB::table('sales_returns')->where('tenant_id',$this->tenantId)->exists()) return;
        $so = DB::table('sales_orders')->where('tenant_id',$this->tenantId)->where('status','delivered')->first();
        if (!$so) return;
        $invoice = DB::table('invoices')->where('tenant_id',$this->tenantId)->where('sales_order_id',$so->id)->first();
        if (!$invoice) return;
        $srId = DB::table('sales_returns')->insertGetId([
            'tenant_id'=>$this->tenantId,'sales_order_id'=>$so->id,'customer_id'=>$so->customer_id,
            'invoice_id'=>$invoice->id,
            'warehouse_id'=>$this->warehouseId,'created_by'=>$this->adminId,
            'number'=>'SR/MBI/2026/001','return_date'=>Carbon::now()->subDays(3)->format('Y-m-d'),
            'reason'=>'Produk tidak sesuai spesifikasi yang dipesan','status'=>'approved',
            'subtotal'=>280000,'tax_amount'=>0,'total'=>280000,
            'refund_method'=>'credit_note','refund_amount'=>280000,
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        $soItem = DB::table('sales_order_items')->where('sales_order_id',$so->id)->first();
        if ($soItem) {
            DB::table('sales_return_items')->insert([
                'sales_return_id'=>$srId,'product_id'=>$soItem->product_id,
                'quantity'=>1,'price'=>$soItem->price,'total'=>$soItem->price,
                'condition'=>'damaged','notes'=>'Tidak sesuai spesifikasi','created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedInvoicesAndPayables(): void
    {
        $deliveredSOs = DB::table('sales_orders')->where('tenant_id',$this->tenantId)->where('status','delivered')->get();
        foreach ($deliveredSOs as $so) {
            if (DB::table('invoices')->where('sales_order_id',$so->id)->exists()) continue;
            $invId = DB::table('invoices')->insertGetId([
                'tenant_id'=>$this->tenantId,'sales_order_id'=>$so->id,'customer_id'=>$so->customer_id,
                'number'=>'INV/'.str_replace('SO/','', $so->number),
                'total_amount'=>$so->total,'paid_amount'=>$so->total,'remaining_amount'=>0,
                'status'=>'paid','due_date'=>Carbon::parse($so->date)->addDays(30)->format('Y-m-d'),
                'created_at'=>now(),'updated_at'=>now(),
            ]);
            DB::table('payments')->insert([
                'tenant_id'=>$this->tenantId,'payable_type'=>'App\\Models\\Invoice','payable_id'=>$invId,
                'amount'=>$so->total,'payment_method'=>'transfer',
                'payment_date'=>Carbon::parse($so->date)->addDays(14)->format('Y-m-d'),
                'notes'=>'Pembayaran lunas via transfer bank','user_id'=>$this->adminId,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
        $processingSOs = DB::table('sales_orders')->where('tenant_id',$this->tenantId)->where('status','processing')->get();
        foreach ($processingSOs as $so) {
            if (DB::table('invoices')->where('sales_order_id',$so->id)->exists()) continue;
            DB::table('invoices')->insertGetId([
                'tenant_id'=>$this->tenantId,'sales_order_id'=>$so->id,'customer_id'=>$so->customer_id,
                'number'=>'INV/'.str_replace('SO/','',$so->number),
                'total_amount'=>$so->total,'paid_amount'=>0,'remaining_amount'=>$so->total,
                'status'=>'unpaid','due_date'=>Carbon::parse($so->date)->addDays(30)->format('Y-m-d'),
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedBulkPayments(): void
    {
        if (DB::table('bulk_payments')->where('tenant_id',$this->tenantId)->exists()) return;
        $invoices = DB::table('invoices')->where('tenant_id',$this->tenantId)->where('status','unpaid')->limit(2)->get();
        if ($invoices->isEmpty()) return;
        $total = $invoices->sum('total_amount');
        $bpId = DB::table('bulk_payments')->insertGetId([
            'tenant_id'=>$this->tenantId,'number'=>'BP/MBI/2026/001',
            'type'=>'receivable',
            'party_id'=>$invoices->first()->customer_id,'party_type'=>'App\\Models\\Customer',
            'payment_date'=>Carbon::now()->format('Y-m-d'),'payment_method'=>'transfer',
            'total_amount'=>$total,'applied_amount'=>0,'overpayment'=>0,
            'status'=>'draft','notes'=>'Pembayaran batch invoice',
            'created_by'=>$this->adminId,'created_at'=>now(),'updated_at'=>now(),
        ]);
        foreach ($invoices as $inv) {
            DB::table('bulk_payment_items')->insert([
                'bulk_payment_id'=>$bpId,
                'payable_id'=>$inv->id,'payable_type'=>'App\\Models\\Invoice',
                'amount'=>$inv->total_amount,'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  FINANCE
    // ══════════════════════════════════════════════════════════════

    private function seedBankAccount(): void
    {
        if (DB::table('bank_accounts')->where('tenant_id',$this->tenantId)->exists()) return;
        $bankId = DB::table('bank_accounts')->insertGetId([
            'tenant_id'=>$this->tenantId,'bank_name'=>'BCA','account_number'=>'1234567890',
            'account_name'=>'PT Maju Bersama Indonesia','balance'=>250000000,
            'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
        ]);
        $statements = [
            ['date'=>'2026-03-01','desc'=>'Pembayaran Invoice SO/MBI/2026/001','type'=>'credit','amount'=>45000000,'status'=>'matched'],
            ['date'=>'2026-03-05','desc'=>'Pembayaran Gaji Februari 2026','type'=>'debit','amount'=>35000000,'status'=>'matched'],
            ['date'=>'2026-03-10','desc'=>'Transfer Masuk - PT Solusi Digital','type'=>'credit','amount'=>120000000,'status'=>'unmatched'],
            ['date'=>'2026-03-15','desc'=>'Pembayaran Sewa Kantor Maret','type'=>'debit','amount'=>15000000,'status'=>'unmatched'],
            ['date'=>'2026-03-20','desc'=>'Pembayaran ke PT Asus Indonesia','type'=>'debit','amount'=>85000000,'status'=>'unmatched'],
        ];
        $balance=250000000;
        foreach ($statements as $s) {
            $balance += $s['type']==='credit'?$s['amount']:-$s['amount'];
            DB::table('bank_statements')->insert([
                'tenant_id'=>$this->tenantId,'bank_account_id'=>$bankId,
                'transaction_date'=>$s['date'],'description'=>$s['desc'],
                'type'=>$s['type'],'amount'=>$s['amount'],'balance'=>$balance,
                'status'=>$s['status'],'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedJournalEntries(): void
    {
        if (DB::table('journal_entries')->where('tenant_id',$this->tenantId)->exists()) return;
        $journals = [
            ['number'=>'JE/MBI/2026/001','date'=>'2026-03-01','description'=>'Penerimaan pembayaran piutang dari PT Teknologi Nusantara',
             'lines'=>[['code'=>'1102','debit'=>45000000,'credit'=>0],['code'=>'1103','debit'=>0,'credit'=>45000000]]],
            ['number'=>'JE/MBI/2026/002','date'=>'2026-03-05','description'=>'Pembayaran gaji karyawan Februari 2026',
             'lines'=>[['code'=>'5201','debit'=>35000000,'credit'=>0],['code'=>'1102','debit'=>0,'credit'=>35000000]]],
            ['number'=>'JE/MBI/2026/003','date'=>'2026-03-10','description'=>'Penjualan barang ke CV Mitra Komputer',
             'lines'=>[['code'=>'1103','debit'=>55000000,'credit'=>0],['code'=>'4101','debit'=>0,'credit'=>49549550],['code'=>'2103','debit'=>0,'credit'=>5450450]]],
        ];
        foreach ($journals as $j) {
            $jeId = DB::table('journal_entries')->insertGetId([
                'tenant_id'=>$this->tenantId,'period_id'=>$this->periodId,'user_id'=>$this->adminId,
                'number'=>$j['number'],'date'=>$j['date'],'description'=>$j['description'],
                'currency_code'=>'IDR','currency_rate'=>1,'status'=>'posted',
                'posted_by'=>$this->adminId,'posted_at'=>now(),'created_at'=>now(),'updated_at'=>now(),
            ]);
            foreach ($j['lines'] as $line) {
                DB::table('journal_entry_lines')->insert([
                    'journal_entry_id'=>$jeId,
                    'account_id'=>$this->coaMap[$line['code']]??array_values($this->coaMap)[0],
                    'debit'=>$line['debit'],'credit'=>$line['credit'],
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }
    }

    private function seedRecurringJournals(): void
    {
        if (DB::table('recurring_journals')->where('tenant_id',$this->tenantId)->exists()) return;
        DB::table('recurring_journals')->insert([
            'tenant_id'=>$this->tenantId,'name'=>'Amortisasi Sewa Kantor Bulanan',
            'description'=>'Pengakuan biaya sewa kantor setiap bulan',
            'frequency'=>'monthly',
            'start_date'=>Carbon::now()->startOfMonth()->format('Y-m-d'),
            'next_run_date'=>Carbon::now()->startOfMonth()->addMonth()->format('Y-m-d'),
            'is_active'=>true,'user_id'=>$this->adminId,
            'lines'=>json_encode([
                ['account_code'=>'5202','debit'=>15000000,'credit'=>0],
                ['account_code'=>'1106','debit'=>0,'credit'=>15000000],
            ]),
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function seedAssets(): void
    {
        if (DB::table('assets')->where('tenant_id',$this->tenantId)->exists()) return;
        $assets = [
            ['asset_code'=>'AST-001','name'=>'Kendaraan Toyota Avanza','category'=>'vehicle','purchase_price'=>200000000,'current_value'=>160000000,'useful_life_years'=>8],
            ['asset_code'=>'AST-002','name'=>'Laptop Dell Latitude','category'=>'equipment','purchase_price'=>15000000,'current_value'=>10000000,'useful_life_years'=>4],
            ['asset_code'=>'AST-003','name'=>'Mesin Forklift Komatsu','category'=>'machine','purchase_price'=>350000000,'current_value'=>280000000,'useful_life_years'=>10],
            ['asset_code'=>'AST-004','name'=>'Rak Gudang Heavy Duty','category'=>'furniture','purchase_price'=>25000000,'current_value'=>20000000,'useful_life_years'=>10],
        ];
        foreach ($assets as $a) {
            $assetId = DB::table('assets')->insertGetId(array_merge($a,[
                'tenant_id'=>$this->tenantId,'purchase_date'=>Carbon::now()->subYears(1)->format('Y-m-d'),
                'salvage_value'=>$a['purchase_price']*0.1,'depreciation_method'=>'straight_line',
                'status'=>'active','created_at'=>now(),'updated_at'=>now(),
            ]));
            $monthlyDep=($a['purchase_price']-$a['purchase_price']*0.1)/($a['useful_life_years']*12);
            DB::table('asset_depreciations')->insert([
                'tenant_id'=>$this->tenantId,'asset_id'=>$assetId,'period'=>'2026-03',
                'depreciation_amount'=>round($monthlyDep),'book_value_after'=>$a['current_value']-round($monthlyDep),
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
        $firstAsset = DB::table('assets')->where('tenant_id',$this->tenantId)->first();
        if ($firstAsset) {
            DB::table('asset_maintenances')->insert([
                'tenant_id'=>$this->tenantId,'asset_id'=>$firstAsset->id,'type'=>'scheduled',
                'description'=>'Service rutin 10.000 km',
                'scheduled_date'=>Carbon::now()->addDays(15)->format('Y-m-d'),
                'cost'=>1500000,'vendor'=>'Bengkel Resmi Toyota','status'=>'pending',
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedBudgets(): void
    {
        if (DB::table('budgets')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach ([
            ['name'=>'Anggaran Penjualan Maret','dept'=>'Penjualan','amount'=>500000000,'realized'=>420000000,'cat'=>'SALES'],
            ['name'=>'Anggaran Operasional Maret','dept'=>'Operasional','amount'=>80000000,'realized'=>65000000,'cat'=>'ADMIN'],
            ['name'=>'Anggaran Pemasaran Maret','dept'=>'Penjualan','amount'=>30000000,'realized'=>18000000,'cat'=>'MARKETING'],
            ['name'=>'Anggaran SDM Maret','dept'=>'SDM','amount'=>120000000,'realized'=>115000000,'cat'=>'SALARY'],
        ] as $b) {
            DB::table('budgets')->insert([
                'tenant_id'=>$this->tenantId,'name'=>$b['name'],'department'=>$b['dept'],
                'period'=>'2026-03','period_type'=>'monthly','amount'=>$b['amount'],
                'realized'=>$b['realized'],'category'=>$b['cat'],'status'=>'active',
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedDeferredItem(): void
    {
        if (DB::table('deferred_items')->where('tenant_id',$this->tenantId)->exists()) return;
        $defId = DB::table('deferred_items')->insertGetId([
            'tenant_id'=>$this->tenantId,'user_id'=>$this->adminId,'type'=>'prepaid_expense',
            'number'=>'DEF/MBI/2026/001','description'=>'Sewa kantor dibayar di muka Jan-Des 2026',
            'total_amount'=>180000000,'recognized_amount'=>45000000,'remaining_amount'=>135000000,
            'start_date'=>'2026-01-01','end_date'=>'2026-12-31','total_periods'=>12,'recognized_periods'=>3,
            'status'=>'active',
            'deferred_account_id'=>$this->coaMap['1106']??array_values($this->coaMap)[0],
            'recognition_account_id'=>$this->coaMap['5202']??array_values($this->coaMap)[0],
            'reference_number'=>'SEWA-2026','created_at'=>now(),'updated_at'=>now(),
        ]);
        for ($m=1;$m<=12;$m++) {
            DB::table('deferred_item_schedules')->insert([
                'deferred_item_id'=>$defId,'period_number'=>$m,
                'recognition_date'=>Carbon::parse('2026-01-01')->addMonths($m-1)->format('Y-m-d'),
                'amount'=>15000000,'status'=>$m<=3?'posted':'pending',
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedWriteoff(): void
    {
        if (DB::table('writeoffs')->where('tenant_id',$this->tenantId)->exists()) return;
        $invoice = DB::table('invoices')->where('tenant_id',$this->tenantId)->where('status','unpaid')->first();
        if (!$invoice) return;
        DB::table('writeoffs')->insert([
            'tenant_id'=>$this->tenantId,'requested_by'=>$this->adminId,'number'=>'WO/MBI/2026/001',
            'type'=>'receivable','reference_type'=>'App\\Models\\Invoice','reference_id'=>$invoice->id,
            'reference_number'=>$invoice->number,'original_amount'=>$invoice->total_amount,
            'writeoff_amount'=>$invoice->total_amount,
            'reason'=>'Pelanggan tidak dapat dihubungi dan tidak ada aset yang dapat disita',
            'status'=>'pending','created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  ANALYTICS & AI
    // ══════════════════════════════════════════════════════════════

    private function seedKpiTargets(): void
    {
        if (DB::table('kpi_targets')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach ([
            ['metric'=>'revenue','label'=>'Pendapatan','target'=>500000000,'actual'=>420000000,'unit'=>'currency','color'=>'#3b82f6'],
            ['metric'=>'orders','label'=>'Jumlah Order','target'=>100,'actual'=>87,'unit'=>'number','color'=>'#10b981'],
            ['metric'=>'new_customers','label'=>'Pelanggan Baru','target'=>20,'actual'=>15,'unit'=>'number','color'=>'#f59e0b'],
            ['metric'=>'profit_margin','label'=>'Margin Keuntungan','target'=>25,'actual'=>22,'unit'=>'percent','color'=>'#8b5cf6'],
        ] as $k) {
            DB::table('kpi_targets')->updateOrInsert(
                ['tenant_id'=>$this->tenantId,'metric'=>$k['metric'],'period'=>'2026-03'],
                array_merge($k,['tenant_id'=>$this->tenantId,'period'=>'2026-03','is_active'=>true,'created_at'=>now(),'updated_at'=>now()])
            );
        }
    }

    private function seedSimulation(): void
    {
        if (DB::table('simulations')->where('tenant_id',$this->tenantId)->exists()) return;
        DB::table('simulations')->insert([
            'tenant_id'=>$this->tenantId,'user_id'=>$this->adminId,
            'name'=>'Simulasi Kenaikan Harga Laptop 10%','scenario_type'=>'price_increase',
            'parameters'=>json_encode(['product_category'=>'Elektronik','increase_pct'=>10,'period'=>'2026-Q2']),
            'results'=>json_encode(['projected_revenue'=>550000000,'projected_margin'=>27.5,'demand_impact'=>-5]),
            'ai_narrative'=>'Kenaikan harga 10% pada kategori Elektronik diproyeksikan meningkatkan pendapatan 10% namun berpotensi menurunkan volume penjualan 5%.',
            'status'=>'calculated','created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function seedAnomalyAlerts(): void
    {
        if (DB::table('anomaly_alerts')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach ([
            ['type'=>'revenue_drop','severity'=>'high','title'=>'Penurunan Pendapatan Signifikan','description'=>'Pendapatan Maret turun 18% dibanding Februari','status'=>'open'],
            ['type'=>'stock_anomaly','severity'=>'medium','title'=>'Pergerakan Stok Tidak Normal','description'=>'Stok Flashdisk Sandisk berkurang 30 unit tanpa transaksi tercatat','status'=>'acknowledged'],
            ['type'=>'expense_spike','severity'=>'low','title'=>'Lonjakan Beban Operasional','description'=>'Beban transportasi naik 45% bulan ini','status'=>'resolved'],
        ] as $a) {
            DB::table('anomaly_alerts')->insert(array_merge($a,[
                'tenant_id'=>$this->tenantId,
                'created_at'=>now(),'updated_at'=>now(),
            ]));
        }
    }

    private function seedAiMemory(): void
    {
        if (DB::table('ai_memories')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach ([
            ['key'=>'business_context','value'=>json_encode('Distributor elektronik dengan 10 produk utama, 5 pelanggan korporat, 4 supplier')],
            ['key'=>'top_products','value'=>json_encode('Laptop Asus VivoBook dan Lenovo IdeaPad adalah produk terlaris')],
            ['key'=>'payment_terms','value'=>json_encode('Pelanggan korporat mendapat Net 30, pelanggan retail Net 14')],
            ['key'=>'seasonal_pattern','value'=>json_encode('Penjualan meningkat di bulan Agustus (back to school) dan Desember (akhir tahun)')],
        ] as $m) {
            DB::table('ai_memories')->insertOrIgnore([
                'tenant_id'=>$this->tenantId,'user_id'=>$this->adminId,
                'key'=>$m['key'],'value'=>$m['value'],'frequency'=>1,
                'last_seen_at'=>now(),'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedZeroInputLogs(): void
    {
        if (DB::table('zero_input_logs')->where('tenant_id',$this->tenantId)->exists()) return;
        DB::table('zero_input_logs')->insert([
            'tenant_id'=>$this->tenantId,'user_id'=>$this->adminId,
            'channel'=>'text','raw_input'=>'Beli 5 laptop asus dari PT Asus seharga 7.5 juta per unit',
            'extracted_data'=>json_encode(['type'=>'purchase','product'=>'Laptop Asus','qty'=>5,'price'=>7500000]),
            'confidence_score'=>0.92,'status'=>'confirmed',
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  CRM & SALES TOOLS
    // ══════════════════════════════════════════════════════════════

    private function seedCrmLeads(): void
    {
        if (DB::table('crm_leads')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach ([
            ['name'=>'PT Inovasi Teknologi','stage'=>'proposal','value'=>150000000,'prob'=>60,'source'=>'referral'],
            ['name'=>'CV Berkah Elektronik','stage'=>'qualified','value'=>75000000,'prob'=>40,'source'=>'cold_call'],
            ['name'=>'Toko Gadget Murah','stage'=>'negotiation','value'=>50000000,'prob'=>75,'source'=>'website'],
            ['name'=>'PT Mitra Usaha Jaya','stage'=>'won','value'=>200000000,'prob'=>100,'source'=>'exhibition'],
            ['name'=>'Bapak Agus Santoso','stage'=>'new','value'=>10000000,'prob'=>20,'source'=>'social_media'],
        ] as $l) {
            $leadId = DB::table('crm_leads')->insertGetId([
                'tenant_id'=>$this->tenantId,'assigned_to'=>$this->staffId,
                'name'=>$l['name'],'company'=>$l['name'],
                'phone'=>'0812-'.rand(10000000,99999999),
                'email'=>strtolower(str_replace([' ','.'],'', $l['name'])).'@example.com',
                'stage'=>$l['stage'],'estimated_value'=>$l['value'],'probability'=>$l['prob'],
                'source'=>$l['source'],'product_interest'=>'Laptop & Monitor',
                'expected_close_date'=>Carbon::now()->addDays(rand(7,60))->format('Y-m-d'),
                'last_contact_at'=>Carbon::now()->subDays(rand(1,10)),
                'created_at'=>now(),'updated_at'=>now(),
            ]);
            DB::table('crm_activities')->insert([
                'tenant_id'=>$this->tenantId,'lead_id'=>$leadId,'user_id'=>$this->staffId,
                'type'=>'call','description'=>'Follow up kebutuhan produk dan penawaran harga',
                'outcome'=>'interested','next_follow_up'=>Carbon::now()->addDays(3)->format('Y-m-d'),
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedCommission(): void
    {
        if (DB::table('commission_rules')->where('tenant_id',$this->tenantId)->exists()) return;
        DB::table('commission_rules')->insert([
            'tenant_id'=>$this->tenantId,'name'=>'Komisi Sales Standard','type'=>'flat_pct',
            'rate'=>2.5,'basis'=>'revenue','is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
        ]);
        DB::table('commission_rules')->insert([
            'tenant_id'=>$this->tenantId,'name'=>'Komisi Tiered','type'=>'tiered','rate'=>0,'basis'=>'revenue',
            'tiers'=>json_encode([
                ['min'=>0,'max'=>10000000,'rate'=>2],
                ['min'=>10000000,'max'=>50000000,'rate'=>3],
                ['min'=>50000000,'max'=>null,'rate'=>5],
            ]),
            'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
        ]);
        // Commission calculation
        $so = DB::table('sales_orders')->where('tenant_id',$this->tenantId)->where('status','delivered')->first();
        if ($so) {
            $rule = DB::table('commission_rules')->where('tenant_id',$this->tenantId)->first();
            DB::table('commission_calculations')->insert([
                'tenant_id'=>$this->tenantId,'commission_rule_id'=>$rule->id,
                'user_id'=>$this->staffId,
                'total_sales'=>$so->total,'total_orders'=>1,
                'commission_amount'=>round($so->total*0.025),
                'bonus_amount'=>0,'total_payout'=>round($so->total*0.025),
                'status'=>'draft','period'=>'2026-03','created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedHelpdesk(): void
    {
        if (DB::table('kb_articles')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach ([
            ['title'=>'Cara Melakukan Retur Barang','category'=>'product','body'=>'Untuk melakukan retur barang, silakan hubungi customer service kami dengan menyertakan nomor invoice dan foto barang yang ingin diretur.'],
            ['title'=>'Metode Pembayaran yang Tersedia','category'=>'billing','body'=>'Kami menerima pembayaran via transfer bank (BCA, Mandiri, BNI), e-wallet (GoPay, OVO), dan kartu kredit.'],
            ['title'=>'Estimasi Waktu Pengiriman','category'=>'delivery','body'=>'Pengiriman dalam kota: 1-2 hari kerja. Luar kota Jawa: 2-4 hari kerja. Luar Jawa: 4-7 hari kerja.'],
        ] as $kb) {
            DB::table('kb_articles')->insert(array_merge($kb,[
                'tenant_id'=>$this->tenantId,'slug'=>Str::slug($kb['title']),
                'is_published'=>true,'views'=>rand(10,100),'user_id'=>$this->adminId,
                'created_at'=>now(),'updated_at'=>now(),
            ]));
        }
        // Helpdesk tickets
        if (!empty($this->customerIds)) {
            foreach ([
                ['subject'=>'Laptop tidak menyala setelah diterima','priority'=>'high','status'=>'open'],
                ['subject'=>'Pertanyaan garansi produk','priority'=>'medium','status'=>'resolved'],
                ['subject'=>'Request invoice ulang','priority'=>'low','status'=>'closed'],
            ] as $i=>$t) {
                $ticketId = DB::table('helpdesk_tickets')->insertGetId([
                    'tenant_id'=>$this->tenantId,'customer_id'=>$this->customerIds[$i]??$this->customerIds[0],
                    'assigned_to'=>$this->staffId,'created_by'=>$this->adminId,
                    'ticket_number'=>'TKT/MBI/2026/'.str_pad($i+1,3,'0',STR_PAD_LEFT),
                    'subject'=>$t['subject'],
                    'description'=>'Detail masalah: '.$t['subject'],
                    'priority'=>$t['priority'],'status'=>$t['status'],
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
                DB::table('helpdesk_replies')->insert([
                    'ticket_id'=>$ticketId,'user_id'=>$this->staffId,
                    'body'=>'Terima kasih atas laporan Anda. Tim kami sedang menangani masalah ini.',
                    'is_internal'=>false,'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }
    }

    private function seedSubscriptionBilling(): void
    {
        if (DB::table('customer_subscription_plans')->where('tenant_id',$this->tenantId)->exists()) return;
        $planIds = [];
        foreach ([
            ['name'=>'Basic Support','code'=>'BASIC','price'=>500000,'billing_cycle'=>'monthly','trial_days'=>14,'features'=>json_encode(['Email support','Knowledge base','Response 24 jam'])],
            ['name'=>'Premium Support','code'=>'PREM','price'=>1500000,'billing_cycle'=>'monthly','trial_days'=>7,'features'=>json_encode(['Priority support','Phone & WhatsApp','Response 4 jam','Dedicated account manager'])],
            ['name'=>'Enterprise Annual','code'=>'ENT','price'=>15000000,'billing_cycle'=>'annual','trial_days'=>30,'features'=>json_encode(['24/7 support','SLA 99.9%','Custom integration','On-site visit'])],
        ] as $plan) {
            $planIds[] = DB::table('customer_subscription_plans')->insertGetId(array_merge($plan,[
                'tenant_id'=>$this->tenantId,'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
            ]));
        }
        // Active subscription
        if (!empty($this->customerIds) && !empty($planIds)) {
            DB::table('customer_subscriptions')->insertOrIgnore([
                'tenant_id'=>$this->tenantId,'customer_id'=>$this->customerIds[0],'plan_id'=>$planIds[1],
                'subscription_number'=>'SUB-MBI-001',
                'status'=>'active','start_date'=>Carbon::now()->subMonths(2)->format('Y-m-d'),
                'next_billing_date'=>Carbon::now()->addDays(15)->format('Y-m-d'),
                'user_id'=>$this->adminId,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  OPERATIONS
    // ══════════════════════════════════════════════════════════════

    private function seedManufacturing(): void
    {
        if (DB::table('work_centers')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach ([
            ['code'=>'WC-01','name'=>'Mesin CNC Utama','cost_per_hour'=>150000,'capacity_per_day'=>8],
            ['code'=>'WC-02','name'=>'Stasiun Assembly','cost_per_hour'=>75000,'capacity_per_day'=>8],
            ['code'=>'WC-03','name'=>'Quality Control','cost_per_hour'=>50000,'capacity_per_day'=>8],
        ] as $wc) {
            DB::table('work_centers')->insert(array_merge($wc,[
                'tenant_id'=>$this->tenantId,'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
            ]));
        }
        $product = DB::table('products')->where('tenant_id',$this->tenantId)->first();
        if ($product) {
            $bomId = DB::table('boms')->insertGetId([
                'tenant_id'=>$this->tenantId,'product_id'=>$product->id,'name'=>'BOM '.$product->name,
                'batch_size'=>10,'batch_unit'=>'pcs','is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
            ]);
            $materials = DB::table('products')->where('tenant_id',$this->tenantId)->where('id','!=',$product->id)->limit(2)->get();
            foreach ($materials as $i=>$mat) {
                DB::table('bom_lines')->insert([
                    'bom_id'=>$bomId,'product_id'=>$mat->id,'quantity_per_batch'=>($i+1)*5,
                    'unit'=>'pcs','sort_order'=>$i,'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
            // Work Order
            $woId = DB::table('work_orders')->insertGetId([
                'tenant_id'=>$this->tenantId,'bom_id'=>$bomId,'product_id'=>$product->id,
                'user_id'=>$this->adminId,
                'number'=>'WO/MBI/2026/001','target_quantity'=>20,'unit'=>'pcs',
                'status'=>'in_progress',
                'planned_start_date'=>Carbon::now()->subDays(3)->format('Y-m-d'),
                'planned_end_date'=>Carbon::now()->addDays(2)->format('Y-m-d'),
                'created_at'=>now(),'updated_at'=>now(),
            ]);
            DB::table('production_outputs')->insert([
                'tenant_id'=>$this->tenantId,'work_order_id'=>$woId,
                'user_id'=>$this->adminId,
                'good_qty'=>15,'reject_qty'=>0,
                'notes'=>'Produksi berjalan normal',
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedQualityControl(): void
    {
        if (DB::table('qc_test_templates')->where('tenant_id',$this->tenantId)->exists()) return;
        $tplId = DB::table('qc_test_templates')->insertGetId([
            'tenant_id'=>$this->tenantId,
            'template_name'=>'Template QC Elektronik','template_code'=>'QC-ELEC-001',
            'test_category'=>'incoming',
            'test_parameters'=>json_encode(['visual'=>'no_defect','functional'=>'100%']),
            'acceptance_criteria'=>json_encode(['pass_threshold'=>80]),
            'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
        ]);
        $wo = DB::table('work_orders')->where('tenant_id',$this->tenantId)->first();
        if ($wo) {
            DB::table('qc_inspections')->insertGetId([
                'tenant_id'=>$this->tenantId,'template_id'=>$tplId,'work_order_id'=>$wo->id,
                'inspection_number'=>'QC/MBI/2026/001',
                'stage'=>'incoming',
                'inspector_id'=>$this->adminId,'sample_size'=>10,'sample_passed'=>9,'sample_failed'=>1,
                'pass_rate'=>90,'grade'=>'A','status'=>'completed',
                'inspector_notes'=>'1 unit cacat minor',
                'inspected_at'=>Carbon::now()->subDays(2),
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
        // Quality standards
        DB::table('quality_check_standards')->insertOrIgnore([
            'tenant_id'=>$this->tenantId,'name'=>'Standar Elektronik Konsumen',
            'code'=>'STD-ELEC-001',
            'description'=>'Standar kualitas untuk produk elektronik konsumen',
            'stage'=>'incoming',
            'parameters'=>json_encode(['visual'=>'no_defect','functional'=>'100%','packaging'=>'intact']),
            'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function seedFleet(): void
    {
        if (DB::table('fleet_vehicles')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach ([
            ['plate_number'=>'B 1234 XYZ','name'=>'Toyota Avanza 2024','type'=>'car','brand'=>'Toyota','model'=>'Avanza','year'=>2024,'odometer'=>15000],
            ['plate_number'=>'B 5678 ABC','name'=>'Mitsubishi Colt Diesel','type'=>'truck','brand'=>'Mitsubishi','model'=>'Colt Diesel','year'=>2023,'odometer'=>45000],
            ['plate_number'=>'B 9012 DEF','name'=>'Honda Beat 2025','type'=>'motorcycle','brand'=>'Honda','model'=>'Beat','year'=>2025,'odometer'=>3000],
        ] as $v) {
            DB::table('fleet_vehicles')->insert(array_merge($v,[
                'tenant_id'=>$this->tenantId,'status'=>'available','is_active'=>true,
                'registration_expiry'=>now()->addYear(),'insurance_expiry'=>now()->addMonths(8),
                'created_at'=>now(),'updated_at'=>now(),
            ]));
        }
        $employees = DB::table('employees')->where('tenant_id',$this->tenantId)->limit(2)->get();
        foreach ($employees as $i=>$emp) {
            DB::table('fleet_drivers')->insert([
                'tenant_id'=>$this->tenantId,'employee_id'=>$emp->id,'name'=>$emp->name,
                'license_number'=>'SIM-'.str_pad($i+1,6,'0',STR_PAD_LEFT),
                'license_type'=>$i===0?'A':'C','license_expiry'=>now()->addYears(2),
                'phone'=>$emp->phone,'status'=>'active','is_active'=>true,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
        // Fleet trip
        $vehicle = DB::table('fleet_vehicles')->where('tenant_id',$this->tenantId)->first();
        $driver = DB::table('fleet_drivers')->where('tenant_id',$this->tenantId)->first();
        if ($vehicle && $driver) {
            DB::table('fleet_trips')->insert([
                'tenant_id'=>$this->tenantId,'vehicle_id'=>$vehicle->id,'driver_id'=>$driver->id,
                'user_id'=>$this->adminId,
                'trip_number'=>'TRIP/MBI/2026/001',
                'purpose'=>'Pengiriman barang ke PT Teknologi Nusantara',
                'origin'=>'Gudang Utama Jakarta','destination'=>'Gedung Sudirman, Jakarta',
                'odometer_start'=>$vehicle->odometer,'odometer_end'=>$vehicle->odometer+45,
                'departed_at'=>Carbon::now()->subDays(2)->setTime(8,0),
                'returned_at'=>Carbon::now()->subDay()->setTime(17,0),
                'status'=>'completed',
                'created_at'=>now(),'updated_at'=>now(),
            ]);
            DB::table('fleet_fuel_logs')->insert([
                'tenant_id'=>$this->tenantId,'vehicle_id'=>$vehicle->id,'driver_id'=>$driver->id,
                'user_id'=>$this->adminId,
                'date'=>Carbon::now()->subDays(2)->format('Y-m-d'),
                'liters'=>40,'price_per_liter'=>10000,'total_cost'=>400000,
                'odometer'=>$vehicle->odometer+100,'fuel_type'=>'pertalite',
                'station'=>'SPBU Pertamina Sudirman','created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedContracts(): void
    {
        if (DB::table('contracts')->where('tenant_id',$this->tenantId)->exists()) return;
        $tplId = DB::table('contract_templates')->insertGetId([
            'tenant_id'=>$this->tenantId,'name'=>'Kontrak Jasa Standar','category'=>'service',
            'default_terms'=>'Pembayaran 30 hari setelah invoice.','is_active'=>true,
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        $customer = DB::table('customers')->where('tenant_id',$this->tenantId)->first();
        if (!$customer) return;
        DB::table('contracts')->insert([
            'tenant_id'=>$this->tenantId,'contract_number'=>'CTR-202603-0001',
            'title'=>'Kontrak Maintenance IT Tahunan','template_id'=>$tplId,
            'customer_id'=>$customer->id,'party_type'=>'customer','category'=>'maintenance',
            'start_date'=>now()->startOfYear(),'end_date'=>now()->endOfYear(),
            'value'=>60000000,'billing_cycle'=>'monthly','billing_amount'=>5000000,
            'next_billing_date'=>now()->startOfMonth()->addMonth(),
            'auto_renew'=>true,'renewal_days_before'=>30,'status'=>'active',
            'sla_response_hours'=>4,'sla_resolution_hours'=>24,'sla_uptime_pct'=>99.50,
            'user_id'=>$this->adminId,'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function seedProjects(): void
    {
        if (DB::table('projects')->where('tenant_id',$this->tenantId)->exists()) return;
        $customer = DB::table('customers')->where('tenant_id',$this->tenantId)->first();
        $projectId = DB::table('projects')->insertGetId([
            'tenant_id'=>$this->tenantId,'name'=>'Implementasi ERP PT Teknologi Nusantara',
            'number'=>'PRJ-2026-001','customer_id'=>$customer?->id,
            'description'=>'Implementasi sistem ERP terintegrasi untuk klien',
            'start_date'=>Carbon::now()->subMonths(1)->format('Y-m-d'),
            'end_date'=>Carbon::now()->addMonths(3)->format('Y-m-d'),
            'budget'=>150000000,'actual_cost'=>45000000,'progress'=>30,'status'=>'active',
            'user_id'=>$this->managerId,'created_at'=>now(),'updated_at'=>now(),
        ]);
        foreach ([
            ['name'=>'Analisis Kebutuhan','status'=>'done'],
            ['name'=>'Desain Sistem','status'=>'done'],
            ['name'=>'Pengembangan Modul','status'=>'in_progress'],
            ['name'=>'Testing & UAT','status'=>'todo'],
            ['name'=>'Go Live & Training','status'=>'todo'],
        ] as $i=>$task) {
            DB::table('project_tasks')->insert([
                'tenant_id'=>$this->tenantId,'project_id'=>$projectId,
                'name'=>$task['name'],'status'=>$task['status'],
                'assigned_to'=>$this->staffId,
                'due_date'=>Carbon::now()->addDays(($i+1)*14)->format('Y-m-d'),
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
        // Project milestones
        DB::table('project_milestones')->insert([
            'tenant_id'=>$this->tenantId,'project_id'=>$projectId,
            'name'=>'Selesai Fase 1 - Analisis','due_date'=>Carbon::now()->subDays(15)->format('Y-m-d'),
            'amount'=>30000000,'status'=>'completed','completed_at'=>Carbon::now()->subDays(15),
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function seedTimesheets(): void
    {
        if (DB::table('timesheets')->where('tenant_id',$this->tenantId)->exists()) return;
        $project = DB::table('projects')->where('tenant_id',$this->tenantId)->first();
        $userIds = [$this->adminId, $this->managerId, $this->staffId];
        foreach ($userIds as $userId) {
            for ($d=5;$d>=1;$d--) {
                DB::table('timesheets')->insert([
                    'tenant_id'=>$this->tenantId,'user_id'=>$userId,
                    'project_id'=>$project?->id,'date'=>Carbon::now()->subDays($d)->format('Y-m-d'),
                    'hours'=>rand(6,8),'description'=>'Pengerjaan modul sistem',
                    'billing_status'=>'unbilled',
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }
    }

    private function seedShipping(): void
    {
        if (DB::table('shipments')->where('tenant_id',$this->tenantId)->exists()) return;
        $so = DB::table('sales_orders')->where('tenant_id',$this->tenantId)->where('status','delivered')->first();
        if (!$so) return;
        DB::table('shipments')->insert([
            'tenant_id'=>$this->tenantId,'sales_order_id'=>$so->id,
            'courier'=>'JNE','service'=>'REG',
            'tracking_number'=>'JNE'.rand(100000000,999999999),
            'origin_city'=>'Jakarta','destination_city'=>'Surabaya',
            'weight_kg'=>5.5,'shipping_cost'=>85000,'status'=>'delivered',
            'delivered_at'=>Carbon::parse($so->date)->addDays(3),
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function seedEcommerce(): void
    {
        if (DB::table('ecommerce_channels')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach ([
            ['shop_name'=>'Tokopedia MBI','platform'=>'tokopedia','is_active'=>true],
            ['shop_name'=>'Shopee MBI','platform'=>'shopee','is_active'=>true],
            ['shop_name'=>'Lazada MBI','platform'=>'lazada','is_active'=>false],
        ] as $ch) {
            $chId = DB::table('ecommerce_channels')->insertGetId(array_merge($ch,[
                'tenant_id'=>$this->tenantId,'api_key'=>'demo_key_'.Str::random(16),
                'created_at'=>now(),'updated_at'=>now(),
            ]));
            if ($ch['is_active'] && !empty($this->productIds)) {
                DB::table('ecommerce_product_mappings')->insertOrIgnore([
                    'tenant_id'=>$this->tenantId,'channel_id'=>$chId,'product_id'=>$this->productIds[0],
                    'external_id'=>'EXT-'.rand(100000,999999),
                    'external_sku'=>'EXT-SKU-001',
                    'is_active'=>true,
                    'last_synced_at'=>now(),'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }
    }

    private function seedPrinting(): void
    {
        if (DB::table('print_jobs')->where('tenant_id',$this->tenantId)->exists()) return;
        $customer = DB::table('customers')->where('tenant_id',$this->tenantId)->first();
        
        // Check which print_jobs table structure exists
        $columns = Schema::getColumnListing('print_jobs');
        
        if (in_array('printer_destination', $columns)) {
            // This is the print queue table (for POS receipts, etc.)
            DB::table('print_jobs')->insert([
                'tenant_id'=>$this->tenantId,
                'job_type'=>'receipt',
                'reference_id'=>null,
                'reference_number'=>'DEMO-001',
                'printer_type'=>'usb',
                'printer_destination'=>'/dev/usb/lp0',
                'print_data'=>json_encode(['content'=>'Demo print job']),
                'status'=>'completed',
                'processed_at'=>now(),
                'created_at'=>now(),
                'updated_at'=>now(),
            ]);
        } else {
            // This is the printing module table (for print shop jobs)
            DB::table('print_jobs')->insert([
                'tenant_id'=>$this->tenantId,'customer_id'=>$customer?->id,
                'job_number'=>'PJ/MBI/2026/001','job_name'=>'Brosur Produk Q2 2026',
                'description'=>'Cetak brosur promosi produk elektronik','product_type'=>'brochure',
                'quantity'=>1000,'paper_type'=>'art_paper',
                'specifications'=>json_encode(['paper_weight'=>150,'colors'=>'4/4','finishing'=>['laminating','cutting']]),
                'estimated_cost'=>2500000,'quoted_price'=>2500000,'status'=>'completed',
                'due_date'=>Carbon::now()->addDays(7)->format('Y-m-d'),
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  AGRICULTURE & LIVESTOCK
    // ══════════════════════════════════════════════════════════════

    private function seedFarmPlots(): void
    {
        if (DB::table('farm_plots')->where('tenant_id',$this->tenantId)->exists()) return;
        $plotId = DB::table('farm_plots')->insertGetId([
            'tenant_id'=>$this->tenantId,'name'=>'Lahan A - Padi','code'=>'LP-A-001',
            'area_size'=>5.5,'area_unit'=>'hectare','soil_type'=>'clay_loam','irrigation_type'=>'irigasi_teknis',
            'location'=>'Karawang, Jawa Barat','status'=>'growing',
            'is_active'=>true,
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        $cycleId = DB::table('crop_cycles')->insertGetId([
            'tenant_id'=>$this->tenantId,'farm_plot_id'=>$plotId,'crop_name'=>'Padi IR64',
            'variety'=>'IR64','planting_date'=>Carbon::now()->subMonths(2)->format('Y-m-d'),
            'expected_harvest_date'=>Carbon::now()->addMonths(1)->format('Y-m-d'),
            'area_hectares'=>5.5,'seed_quantity'=>55,'seed_unit'=>'kg',
            'phase'=>'vegetative','status'=>'active',
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        DB::table('harvest_logs')->insert([
            'tenant_id'=>$this->tenantId,'farm_plot_id'=>$plotId,'crop_cycle_id'=>$cycleId,
            'user_id'=>$this->adminId,'number'=>'HRV-A1-'.Carbon::now()->subMonths(5)->format('Ymd').'-01',
            'harvest_date'=>Carbon::now()->subMonths(5)->format('Y-m-d'),
            'crop_name'=>'Padi IR64',
            'total_qty'=>22000,'unit'=>'kg','reject_qty'=>500,
            'moisture_pct'=>14.5,'storage_location'=>'Gudang Utama',
            'labor_cost'=>2000000,'transport_cost'=>500000,
            'weather'=>'Cerah','notes'=>'Panen musim sebelumnya',
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function seedLivestockEnhancement(): void
    {
        if (DB::table('livestock_herds')->where('tenant_id',$this->tenantId)->exists()) return;
        $herdId = DB::table('livestock_herds')->insertGetId([
            'tenant_id'=>$this->tenantId,'code'=>'HRD-A1','name'=>'Sapi Perah Kelompok A',
            'animal_type'=>'sapi','breed'=>'Friesian Holstein',
            'initial_count'=>25,'current_count'=>25,
            'entry_date'=>Carbon::now()->subMonths(6)->format('Y-m-d'),
            'entry_age_days'=>90,'entry_weight_kg'=>150,'purchase_price'=>75000000,
            'status'=>'active','target_harvest_date'=>Carbon::now()->addMonths(6)->format('Y-m-d'),
            'target_weight_kg'=>400,'notes'=>'Sapi perah produktif',
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        // Dairy milk records
        for ($d=7;$d>=1;$d--) {
            DB::table('dairy_milk_records')->insert([
                'tenant_id'=>$this->tenantId,'livestock_herd_id'=>$herdId,
                'animal_id'=>'COW-'.str_pad(rand(1,25),3,'0',STR_PAD_LEFT),
                'record_date'=>Carbon::now()->subDays($d)->format('Y-m-d'),
                'milking_session'=>['morning','afternoon','evening'][rand(0,2)],
                'milk_volume_liters'=>rand(8,15),
                'fat_percentage'=>rand(35,45)/10,
                'protein_percentage'=>rand(30,35)/10,
                'lactose_percentage'=>rand(45,50)/10,
                'somatic_cell_count'=>rand(100000,300000),
                'quality_grade'=>'A',
                'recorded_by'=>$this->adminId,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
        // Poultry (skip if table doesn't exist)
        if (Schema::hasTable('poultry_flocks')) {
            DB::table('poultry_flocks')->insertOrIgnore([
                'tenant_id'=>$this->tenantId,'name'=>'Ayam Broiler Batch 1','breed'=>'Ross 308',
                'initial_count'=>500,'current_count'=>485,'age_days'=>28,
                'house_number'=>'Kandang B1','status'=>'active',
                'placement_date'=>Carbon::now()->subDays(28)->format('Y-m-d'),
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
        // Breeding record
        if (Schema::hasTable('breeding_records')) {
            DB::table('breeding_records')->insert([
                'tenant_id'=>$this->tenantId,'livestock_herd_id'=>$herdId,
                'dam_id'=>'COW-001','sire_id'=>null,
                'mating_date'=>Carbon::now()->subDays(60)->format('Y-m-d'),
                'mating_type'=>'artificial_insemination','genetics_line'=>'Friesian Holstein',
                'expected_due_date'=>Carbon::now()->addMonths(7)->format('Y-m-d'),
                'status'=>'pregnant','notes'=>'Inseminasi buatan berhasil',
                'recorded_by'=>$this->adminId,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
        // Health treatment
        if (Schema::hasTable('livestock_health_records') && Schema::hasColumn('livestock_health_records', 'date')) {
            DB::table('livestock_health_records')->insert([
                'tenant_id'=>$this->tenantId,'livestock_herd_id'=>$herdId,
                'user_id'=>$this->adminId,
                'date'=>Carbon::now()->subDays(14)->format('Y-m-d'),
                'type'=>'treatment','condition'=>'Foot and Mouth Disease',
                'affected_count'=>25,'death_count'=>0,
                'symptoms'=>'Demam, lemas','medication'=>'Vaksin PMK',
                'medication_cost'=>500000,'administered_by'=>'drh. Bambang Susilo',
                'severity'=>'medium','status'=>'resolved',
                'notes'=>'Vaksinasi rutin tahunan',
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
        // Waste management
        if (Schema::hasTable('waste_management_logs') && Schema::hasColumn('waste_management_logs', 'collection_date')) {
            DB::table('waste_management_logs')->insert([
                'tenant_id'=>$this->tenantId,'livestock_herd_id'=>$herdId,
                'collection_date'=>Carbon::now()->subDays(3)->format('Y-m-d'),
                'waste_type'=>'manure_solid','quantity_kg'=>500,
                'disposal_method'=>'composting','storage_location'=>'Area Kompos',
                'end_product'=>'Pupuk Organik','notes'=>'Diolah menjadi pupuk organik',
                'recorded_by'=>$this->adminId,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedFisheries(): void
    {
        if (DB::table('fishing_vessels')->where('tenant_id',$this->tenantId)->exists()) return;
        $vesselId = DB::table('fishing_vessels')->insertGetId([
            'tenant_id'=>$this->tenantId,'name'=>'KM Maju Jaya 01','registration_number'=>'GT-2026-001',
            'vessel_type'=>'purse_seiner','length_meters'=>25,'capacity_tons'=>50,
            'engine_power_hp'=>300,'home_port'=>'Pelabuhan Muara Baru, Jakarta',
            'status'=>'active','created_at'=>now(),'updated_at'=>now(),
        ]);
        // Fishing trip
        $tripId = DB::table('fishing_trips')->insertGetId([
            'tenant_id'=>$this->tenantId,'vessel_id'=>$vesselId,
            'trip_number'=>'TRIP/MBI/2026/001',
            'departure_date'=>Carbon::now()->subDays(10)->format('Y-m-d'),
            'return_date'=>Carbon::now()->subDays(3)->format('Y-m-d'),
            'fishing_zone'=>'Laut Jawa - WPP 712','crew_count'=>12,
            'status'=>'completed','total_catch_kg'=>8500,'total_revenue'=>127500000,
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        // Aquaculture pond
        $pondId = DB::table('aquaculture_ponds')->insertGetId([
            'tenant_id'=>$this->tenantId,'name'=>'Kolam Udang A1','pond_code'=>'KU-A1',
            'type'=>'shrimp','area_m2'=>5000,'depth_meters'=>1.5,
            'water_source'=>'air_laut','status'=>'active',
            'stocking_date'=>Carbon::now()->subDays(45)->format('Y-m-d'),
            'stocking_count'=>100000,'species'=>'Litopenaeus vannamei',
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        // Water quality log
        DB::table('water_quality_logs')->insert([
            'tenant_id'=>$this->tenantId,'pond_id'=>$pondId,
            'recorded_at'=>Carbon::now()->subHours(6),
            'temperature'=>28.5,'ph'=>7.8,'dissolved_oxygen'=>6.2,
            'salinity'=>15,'ammonia'=>0.02,'turbidity'=>25,
            'status'=>'normal','created_at'=>now(),'updated_at'=>now(),
        ]);
        // Cold storage
        $storageId = DB::table('cold_storage_units')->insertGetId([
            'tenant_id'=>$this->tenantId,'name'=>'Cold Storage 1','code'=>'CS-001',
            'capacity_tons'=>100,'current_stock_tons'=>45,
            'temperature_min'=>-18,'temperature_max'=>-15,
            'location'=>'Pelabuhan Muara Baru','status'=>'operational',
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        DB::table('temperature_logs')->insert([
            'tenant_id'=>$this->tenantId,'storage_id'=>$storageId,
            'recorded_at'=>Carbon::now()->subHours(2),
            'temperature'=>-16.5,'status'=>'normal',
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  INDUSTRY VERTICALS
    // ══════════════════════════════════════════════════════════════

    private function seedTourTravel(): void
    {
        if (DB::table('tour_packages')->where('tenant_id',$this->tenantId)->exists()) return;
        $pkgId = DB::table('tour_packages')->insertGetId([
            'tenant_id'=>$this->tenantId,'name'=>'Bali Honeymoon 4D3N','code'=>'PKG-BALI-001',
            'destination'=>'Bali, Indonesia','duration_days'=>4,'duration_nights'=>3,
            'price_per_person'=>3500000,'min_pax'=>2,'max_pax'=>20,
            'description'=>'Paket wisata romantis ke Bali dengan akomodasi bintang 4',
            'includes'=>json_encode(['Hotel bintang 4','Sarapan','Airport transfer','Tour guide']),
            'excludes'=>json_encode(['Tiket pesawat','Makan siang & malam','Pengeluaran pribadi']),
            'status'=>'active','created_at'=>now(),'updated_at'=>now(),
        ]);
        // Itinerary
        foreach ([
            ['day'=>1,'title'=>'Arrival & Kuta Beach','activities'=>'Tiba di Bali, check-in hotel, sore hari ke Pantai Kuta'],
            ['day'=>2,'title'=>'Ubud Cultural Tour','activities'=>'Pagi ke Tegalalang Rice Terrace, siang ke Ubud Art Market, sore ke Monkey Forest'],
            ['day'=>3,'title'=>'Nusa Dua & Water Sport','activities'=>'Pagi water sport di Nusa Dua, siang relaksasi di pantai, malam Kecak Dance'],
            ['day'=>4,'title'=>'Departure','activities'=>'Sarapan, check-out, airport transfer'],
        ] as $it) {
            DB::table('itinerary_days')->insert(array_merge($it,[
                'tenant_id'=>$this->tenantId,'package_id'=>$pkgId,
                'created_at'=>now(),'updated_at'=>now(),
            ]));
        }
        // Booking
        if (!empty($this->customerIds)) {
            $bookingId = DB::table('tour_bookings')->insertGetId([
                'tenant_id'=>$this->tenantId,'package_id'=>$pkgId,'customer_id'=>$this->customerIds[0],
                'booking_number'=>'BK/MBI/2026/001',
                'travel_date'=>Carbon::now()->addDays(30)->format('Y-m-d'),
                'return_date'=>Carbon::now()->addDays(33)->format('Y-m-d'),
                'pax_count'=>2,'total_price'=>7000000,'paid_amount'=>3500000,
                'status'=>'confirmed','notes'=>'Honeymoon package',
                'created_at'=>now(),'updated_at'=>now(),
            ]);
            DB::table('booking_passengers')->insert([
                'tenant_id'=>$this->tenantId,'booking_id'=>$bookingId,
                'name'=>'Ahmad Fauzi','id_number'=>'3174012345678901',
                'id_type'=>'ktp','phone'=>'0812-1111-2222',
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedCosmetic(): void
    {
        if (DB::table('cosmetic_formulas')->where('tenant_id',$this->tenantId)->exists()) return;
        $formulaId = DB::table('cosmetic_formulas')->insertGetId([
            'tenant_id'=>$this->tenantId,'name'=>'Moisturizer Aloe Vera SPF30','code'=>'FORM-001',
            'category'=>'skincare','product_type'=>'moisturizer',
            'description'=>'Formula pelembab wajah dengan kandungan aloe vera dan SPF30',
            'batch_size'=>100,'batch_unit'=>'kg','status'=>'approved',
            'version'=>'1.0','created_by'=>$this->adminId,
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        foreach ([
            ['name'=>'Aloe Vera Extract','percentage'=>15.0,'function'=>'moisturizer','inci_name'=>'Aloe Barbadensis Leaf Juice'],
            ['name'=>'Titanium Dioxide','percentage'=>5.0,'function'=>'sunscreen','inci_name'=>'Titanium Dioxide'],
            ['name'=>'Glycerin','percentage'=>3.0,'function'=>'humectant','inci_name'=>'Glycerin'],
            ['name'=>'Aqua','percentage'=>70.0,'function'=>'solvent','inci_name'=>'Aqua'],
        ] as $ing) {
            DB::table('formula_ingredients')->insert(array_merge($ing,[
                'tenant_id'=>$this->tenantId,'formula_id'=>$formulaId,
                'created_at'=>now(),'updated_at'=>now(),
            ]));
        }
        // Batch record
        $batchId = DB::table('cosmetic_batch_records')->insertGetId([
            'tenant_id'=>$this->tenantId,'formula_id'=>$formulaId,
            'batch_number'=>'BATCH-2026-001','production_date'=>Carbon::now()->subDays(7)->format('Y-m-d'),
            'expiry_date'=>Carbon::now()->addYears(2)->format('Y-m-d'),
            'batch_size'=>100,'batch_unit'=>'kg','yield_actual'=>98.5,
            'status'=>'released','qc_status'=>'passed',
            'created_by'=>$this->adminId,'created_at'=>now(),'updated_at'=>now(),
        ]);
        // BPOM registration
        DB::table('product_registrations')->insert([
            'tenant_id'=>$this->tenantId,'formula_id'=>$formulaId,
            'registration_number'=>'NA18230100001','product_name'=>'Moisturizer Aloe Vera SPF30',
            'brand_name'=>'MBI Skincare','registration_type'=>'notifikasi',
            'submission_date'=>Carbon::now()->subMonths(3)->format('Y-m-d'),
            'approval_date'=>Carbon::now()->subMonths(1)->format('Y-m-d'),
            'expiry_date'=>Carbon::now()->addYears(3)->format('Y-m-d'),
            'status'=>'approved','created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function seedSupplierScorecard(): void
    {
        if (DB::table('supplier_scorecards')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach (array_slice($this->supplierIds,0,2) as $suppId) {
            DB::table('supplier_scorecards')->insert([
                'tenant_id'=>$this->tenantId,'supplier_id'=>$suppId,
                'period'=>'2026-Q1','evaluation_date'=>Carbon::now()->subDays(5)->format('Y-m-d'),
                'quality_score'=>85,'delivery_score'=>90,'price_score'=>80,'service_score'=>88,
                'overall_score'=>85.75,'grade'=>'B+',
                'on_time_delivery_pct'=>92,'defect_rate_pct'=>1.5,'price_competitiveness'=>80,
                'notes'=>'Supplier performa baik, perlu peningkatan ketepatan waktu pengiriman',
                'evaluated_by'=>$this->adminId,'created_at'=>now(),'updated_at'=>now(),
            ]);
            DB::table('supplier_performances')->insert([
                'tenant_id'=>$this->tenantId,'supplier_id'=>$suppId,
                'period'=>'2026-03','total_orders'=>5,'on_time_orders'=>4,'late_orders'=>1,
                'total_items'=>50,'defective_items'=>1,'return_items'=>1,
                'total_value'=>85000000,'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  SETTINGS & CONFIG
    // ══════════════════════════════════════════════════════════════

    private function seedApprovalWorkflow(): void
    {
        if (DB::table('approval_workflows')->where('tenant_id',$this->tenantId)->exists()) return;
        $wfId = DB::table('approval_workflows')->insertGetId([
            'tenant_id'=>$this->tenantId,'name'=>'Persetujuan PO > 50 Juta',
            'model_type'=>'App\\Models\\PurchaseOrder','min_amount'=>50000000,
            'approver_roles'=>json_encode(['manager','admin']),'is_active'=>true,
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        $po = DB::table('purchase_orders')->where('tenant_id',$this->tenantId)->where('status','draft')->first();
        if ($po) {
            DB::table('approval_requests')->insertOrIgnore([
                'tenant_id'=>$this->tenantId,'workflow_id'=>$wfId,'requested_by'=>$this->staffId,
                'model_type'=>'App\\Models\\PurchaseOrder','model_id'=>$po->id,
                'status'=>'pending','amount'=>$po->total,
                'notes'=>'Mohon persetujuan PO untuk pembelian stok bulan Maret',
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedTransactions(): void
    {
        if (DB::table('transactions')->where('tenant_id',$this->tenantId)->exists()) return;
        $rentCatId = DB::table('expense_categories')->where('tenant_id',$this->tenantId)->where('code','RENT')->value('id');
        $utilCatId = DB::table('expense_categories')->where('tenant_id',$this->tenantId)->where('code','UTILITY')->value('id');
        $salesCatId = DB::table('expense_categories')->where('tenant_id',$this->tenantId)->where('code','SALES')->value('id');
        foreach ([
            ['type'=>'income','amount'=>45000000,'desc'=>'Penerimaan SO/MBI/2026/001','cat'=>$salesCatId,'date'=>'2026-03-01'],
            ['type'=>'expense','amount'=>15000000,'desc'=>'Sewa kantor Maret 2026','cat'=>$rentCatId,'date'=>'2026-03-05'],
            ['type'=>'expense','amount'=>3500000,'desc'=>'Listrik & air Maret 2026','cat'=>$utilCatId,'date'=>'2026-03-10'],
            ['type'=>'income','amount'=>120000000,'desc'=>'Penerimaan SO/MBI/2026/004','cat'=>$salesCatId,'date'=>'2026-03-15'],
        ] as $i=>$t) {
            DB::table('transactions')->insert([
                'tenant_id'=>$this->tenantId,'user_id'=>$this->adminId,'expense_category_id'=>$t['cat'],
                'number'=>'TRX/MBI/2026/'.str_pad($i+1,3,'0',STR_PAD_LEFT),
                'type'=>$t['type'],'date'=>$t['date'],'amount'=>$t['amount'],
                'payment_method'=>'transfer','account'=>'Bank BCA','description'=>$t['desc'],
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedReminders(): void
    {
        if (DB::table('reminders')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach ([
            ['title'=>'Tutup Buku Maret 2026','remind_at'=>'2026-03-31 08:00:00','notes'=>'Pastikan semua jurnal sudah diposting'],
            ['title'=>'Bayar PPN Maret 2026','remind_at'=>'2026-04-15 08:00:00','notes'=>'Batas akhir pembayaran PPN masa Maret'],
            ['title'=>'Renewal Kontrak Sewa Kantor','remind_at'=>'2026-12-01 08:00:00','notes'=>'Kontrak sewa berakhir 31 Desember 2026'],
            ['title'=>'Review Anggaran Q2 2026','remind_at'=>'2026-04-01 08:00:00','notes'=>'Rapat review anggaran kuartal 2'],
        ] as $r) {
            DB::table('reminders')->insert([
                'tenant_id'=>$this->tenantId,'user_id'=>$this->adminId,
                'title'=>$r['title'],'notes'=>$r['notes'],'remind_at'=>$r['remind_at'],
                'status'=>'pending','channel'=>'both',
                'related_type'=>null,'related_id'=>null,'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedNotifications(): void
    {
        if (DB::table('erp_notifications')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach ([
            ['title'=>'Stok Laptop Asus hampir habis','body'=>'Stok Laptop Asus VivoBook 15 tersisa 3 unit, di bawah minimum stok (5 unit)','type'=>'low_stock'],
            ['title'=>'Invoice jatuh tempo dalam 3 hari','body'=>'Invoice INV/MBI/2026/002 senilai Rp 55.000.000 dari CV Mitra Komputer akan jatuh tempo','type'=>'invoice_due'],
            ['title'=>'Approval PO menunggu persetujuan','body'=>'Purchase Order PO/MBI/2026/003 senilai Rp 12.000.000 menunggu persetujuan Anda','type'=>'approval'],
            ['title'=>'Payroll Februari 2026 telah diproses','body'=>'Penggajian bulan Februari 2026 telah berhasil diproses untuk 5 karyawan','type'=>'payroll'],
        ] as $n) {
            DB::table('erp_notifications')->insert([
                'tenant_id'=>$this->tenantId,'user_id'=>$this->adminId,
                'title'=>$n['title'],'body'=>$n['body'],'type'=>$n['type'],
                'read_at'=>null,'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedCustomFields(): void
    {
        if (DB::table('custom_fields')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach ([
            ['module'=>'customer','key'=>'market_segment','label'=>'Segmen Pasar','type'=>'select','options'=>json_encode(['Korporat','UMKM','Retail','Pemerintah'])],
            ['module'=>'product','key'=>'warranty_months','label'=>'Garansi (bulan)','type'=>'number','options'=>null],
            ['module'=>'employee','key'=>'bpjs_number','label'=>'Nomor BPJS','type'=>'text','options'=>null],
        ] as $f) {
            DB::table('custom_fields')->insert([
                'tenant_id'=>$this->tenantId,'module'=>$f['module'],'key'=>$f['key'],
                'label'=>$f['label'],'type'=>$f['type'],'options'=>$f['options'],
                'required'=>false,'is_active'=>true,'sort_order'=>0,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedCompanyGroup(): void
    {
        if (DB::table('company_groups')->where('owner_user_id',$this->adminId)->exists()) return;
        $groupId = DB::table('company_groups')->insertGetId([
            'owner_user_id'=>$this->adminId,'name'=>'Grup MBI Holding',
            'currency_code'=>'IDR','created_at'=>now(),'updated_at'=>now(),
        ]);
        DB::table('company_group_members')->insertOrIgnore([
            'company_group_id'=>$groupId,'tenant_id'=>$this->tenantId,
            'role'=>'owner','created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function seedDocumentTemplate(): void
    {
        if (DB::table('document_templates')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach ([
            ['name'=>'Template Invoice Standar','doc_type'=>'invoice'],
            ['name'=>'Template Quotation Standar','doc_type'=>'quotation'],
            ['name'=>'Template Purchase Order','doc_type'=>'po'],
            ['name'=>'Template Surat Jalan','doc_type'=>'delivery_order'],
            ['name'=>'Template Kontrak Jasa','doc_type'=>'contract'],
        ] as $t) {
            DB::table('document_templates')->insert([
                'tenant_id'=>$this->tenantId,'name'=>$t['name'],'doc_type'=>$t['doc_type'],
                'html_content'=>'<h1>'.strtoupper($t['doc_type']).'</h1><p>{{company_name}}</p>',
                'is_default'=>true,'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedWorkflows(): void
    {
        if (DB::table('workflows')->where('tenant_id',$this->tenantId)->exists()) return;
        $wfId = DB::table('workflows')->insertGetId([
            'tenant_id'=>$this->tenantId,'name'=>'Auto Notifikasi Invoice Jatuh Tempo',
            'trigger_event'=>'invoice.due_soon','description'=>'Kirim notifikasi otomatis 3 hari sebelum invoice jatuh tempo',
            'is_active'=>true,'created_by'=>$this->adminId,'created_at'=>now(),'updated_at'=>now(),
        ]);
        DB::table('workflow_actions')->insert([
            'workflow_id'=>$wfId,'type'=>'send_notification','sort_order'=>1,
            'config'=>json_encode(['channel'=>'email','template'=>'invoice_due_reminder']),
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function seedApiTokens(): void
    {
        if (DB::table('api_tokens')->where('tenant_id',$this->tenantId)->exists()) return;
        DB::table('api_tokens')->insert([
            'tenant_id'=>$this->tenantId,'user_id'=>$this->adminId,
            'name'=>'Token Integrasi Demo','token'=>hash('sha256',Str::random(40)),
            'abilities'=>json_encode(['read','write']),'last_used_at'=>null,
            'expires_at'=>Carbon::now()->addYear(),'is_active'=>true,
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  HOTEL MODULE
    // ══════════════════════════════════════════════════════════════

    private function seedHotelFrontOffice(): void
    {
        if (DB::table('room_types')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach ([
            ['code'=>'STD','name'=>'Standard Room','base_rate'=>350000,'max_occupancy'=>2,'base_occupancy'=>2],
            ['code'=>'DLX','name'=>'Deluxe Room','base_rate'=>550000,'max_occupancy'=>2,'base_occupancy'=>2],
            ['code'=>'STE','name'=>'Suite Room','base_rate'=>950000,'max_occupancy'=>4,'base_occupancy'=>2],
            ['code'=>'PRM','name'=>'Presidential Suite','base_rate'=>2500000,'max_occupancy'=>6,'base_occupancy'=>2],
        ] as $rt) {
            DB::table('room_types')->insert(array_merge($rt,[
                'tenant_id'=>$this->tenantId,'description'=>'Kamar tipe '.$rt['name'],
                'amenities'=>json_encode(['wifi','ac','tv']),'is_active'=>true,
                'created_at'=>now(),'updated_at'=>now(),
            ]));
        }
        $roomTypeIds = DB::table('room_types')->where('tenant_id',$this->tenantId)->pluck('id')->toArray();
        foreach ([1,2,3] as $floor) {
            for ($i=1;$i<=5;$i++) {
                $roomNumber = $floor*100+$i;
                DB::table('rooms')->insert([
                    'tenant_id'=>$this->tenantId,'room_type_id'=>$roomTypeIds[($roomNumber-101)%count($roomTypeIds)],
                    'number'=>(string)$roomNumber,'floor'=>(string)$floor,
                    'status'=>'available','is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }
        foreach ([
            ['guest_code'=>'GST-001','name'=>'Ahmad Fauzi','email'=>'ahmad.fauzi@gmail.com','phone'=>'0812-1111-2222','vip_level'=>'gold','total_stays'=>15],
            ['guest_code'=>'GST-002','name'=>'Siti Nurhaliza','email'=>'siti.nur@gmail.com','phone'=>'0813-3333-4444','vip_level'=>'platinum','total_stays'=>30],
            ['guest_code'=>'GST-003','name'=>'Budi Setiawan','email'=>'budi.setia@yahoo.com','phone'=>'0815-5555-6666','vip_level'=>'silver','total_stays'=>5],
        ] as $g) {
            DB::table('guests')->insert(array_merge($g,[
                'tenant_id'=>$this->tenantId,'notes'=>'Tamu loyal',
                'preferences'=>json_encode(['non_smoking'=>true]),
                'loyalty_points'=>rand(1000,10000),'last_stay_at'=>now()->subDays(rand(1,30)),
                'created_at'=>now(),'updated_at'=>now(),
            ]));
        }
        $guestIds = DB::table('guests')->where('tenant_id',$this->tenantId)->pluck('id')->toArray();
        $roomIds = DB::table('rooms')->where('tenant_id',$this->tenantId)->pluck('id')->toArray();
        if (!empty($guestIds)&&!empty($roomIds)) {
            foreach ([
                ['check_in'=>now()->subDays(2),'check_out'=>now()->addDay(),'status'=>'checked_in','adults'=>2,'children'=>0],
                ['check_in'=>now()->addDays(3),'check_out'=>now()->addDays(7),'status'=>'confirmed','adults'=>1,'children'=>1],
                ['check_in'=>now()->addDays(10),'check_out'=>now()->addDays(14),'status'=>'pending','adults'=>2,'children'=>2],
            ] as $i=>$res) {
                $roomId=$roomIds[$i%count($roomIds)];
                $guestId=$guestIds[$i%count($guestIds)];
                $roomTypeId=DB::table('rooms')->find($roomId)->room_type_id;
                $roomType=DB::table('room_types')->find($roomTypeId);
                $nights=$res['check_in']->diffInDays($res['check_out']);
                $totalAmount=$roomType->base_rate*$nights;
                $tax=round($totalAmount*0.11);
                DB::table('reservations')->insert([
                    'tenant_id'=>$this->tenantId,'guest_id'=>$guestId,'room_type_id'=>$roomTypeId,'room_id'=>$roomId,
                    'reservation_number'=>'RES-'.now()->format('Ymd').'-'.str_pad($i+1,4,'0',STR_PAD_LEFT),
                    'check_in_date'=>$res['check_in'],'check_out_date'=>$res['check_out'],
                    'nights'=>$nights,'adults'=>$res['adults'],'children'=>$res['children'],
                    'status'=>$res['status'],'rate_per_night'=>$roomType->base_rate,
                    'total_amount'=>$totalAmount,'tax'=>$tax,'grand_total'=>$totalAmount+$tax,
                    'source'=>'direct','special_requests'=>'Reservasi demo',
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }
    }

    private function seedHotelFbModule(): void
    {
        if (DB::table('restaurant_menus')->where('tenant_id',$this->tenantId)->exists()) return;
        $menuIds = [];
        foreach ([
            ['name'=>'Menu Sarapan','type'=>'breakfast','available_from'=>'06:00','available_until'=>'10:00'],
            ['name'=>'Menu Makan Siang','type'=>'lunch','available_from'=>'11:00','available_until'=>'14:00'],
            ['name'=>'Menu Makan Malam','type'=>'dinner','available_from'=>'17:00','available_until'=>'22:00'],
            ['name'=>'Room Service 24 Jam','type'=>'room_service','available_from'=>'00:00','available_until'=>'23:59'],
        ] as $menu) {
            $menuIds[] = DB::table('restaurant_menus')->insertGetId(array_merge($menu,[
                'tenant_id'=>$this->tenantId,'description'=>'Menu '.$menu['name'],
                'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
            ]));
        }
        foreach ([
            ['name'=>'Nasi Goreng Spesial','price'=>45000,'cost'=>18000,'preparation_time'=>15,'category'=>'main_course'],
            ['name'=>'Mie Goreng Seafood','price'=>50000,'cost'=>22000,'preparation_time'=>15,'category'=>'main_course'],
            ['name'=>'Ayam Bakar Madu','price'=>65000,'cost'=>28000,'preparation_time'=>25,'category'=>'main_course'],
            ['name'=>'Es Teh Manis','price'=>8000,'cost'=>2000,'preparation_time'=>5,'category'=>'beverage'],
            ['name'=>'Jus Jeruk Segar','price'=>15000,'cost'=>6000,'preparation_time'=>10,'category'=>'beverage'],
            ['name'=>'Kopi Latte','price'=>25000,'cost'=>10000,'preparation_time'=>10,'category'=>'beverage'],
            ['name'=>'Pancake Maple Syrup','price'=>35000,'cost'=>12000,'preparation_time'=>20,'category'=>'breakfast'],
            ['name'=>'Ice Cream Sundae','price'=>30000,'cost'=>10000,'preparation_time'=>10,'category'=>'dessert'],
        ] as $i=>$item) {
            DB::table('menu_items')->insert(array_merge($item,[
                'tenant_id'=>$this->tenantId,'menu_id'=>$menuIds[$i%count($menuIds)],
                'description'=>'Menu item '.$item['name'],'allergens'=>json_encode([]),
                'dietary_info'=>json_encode([]),'is_available'=>true,
                'daily_limit'=>null,'sold_today'=>0,'display_order'=>$i,
                'created_at'=>now(),'updated_at'=>now(),
            ]));
        }
        foreach ([
            ['name'=>'Beras Premium','unit'=>'kg','current_stock'=>100,'minimum_stock'=>20,'cost_per_unit'=>12000],
            ['name'=>'Minyak Goreng','unit'=>'liter','current_stock'=>50,'minimum_stock'=>10,'cost_per_unit'=>18000],
            ['name'=>'Gula Pasir','unit'=>'kg','current_stock'=>30,'minimum_stock'=>5,'cost_per_unit'=>15000],
            ['name'=>'Telur Ayam','unit'=>'kg','current_stock'=>40,'minimum_stock'=>10,'cost_per_unit'=>28000],
            ['name'=>'Daging Sapi','unit'=>'kg','current_stock'=>25,'minimum_stock'=>5,'cost_per_unit'=>120000],
        ] as $supply) {
            DB::table('fb_supplies')->insert(array_merge($supply,[
                'tenant_id'=>$this->tenantId,'last_restocked_at'=>now()->subDays(rand(1,7)),
                'supplier_name'=>'Supplier Demo','is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
            ]));
        }
        // Restaurant tables
        foreach ([1,2,3,4,5] as $t) {
            DB::table('restaurant_tables')->insertOrIgnore([
                'tenant_id'=>$this->tenantId,'table_number'=>'T-'.str_pad($t,2,'0',STR_PAD_LEFT),
                'capacity'=>4,'location'=>'indoor','status'=>'available',
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedHotelHousekeeping(): void
    {
        $rooms = DB::table('rooms')->where('tenant_id',$this->tenantId)->limit(5)->get();
        $userIds = DB::table('users')->where('tenant_id',$this->tenantId)->pluck('id')->toArray();
        if ($rooms->isEmpty()||empty($userIds)) return;
        if (DB::table('housekeeping_tasks')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach ($rooms as $i=>$room) {
            DB::table('housekeeping_tasks')->insert([
                'tenant_id'=>$this->tenantId,'room_id'=>$room->id,
                'assigned_to'=>$userIds[$i%count($userIds)],
                'type'=>['checkout_clean','stay_clean','deep_clean'][array_rand(['checkout_clean','stay_clean','deep_clean'])],
                'status'=>['pending','in_progress','completed'][array_rand(['pending','in_progress','completed'])],
                'priority'=>['low','normal','high'][array_rand(['low','normal','high'])],
                'scheduled_at'=>now()->addHours(rand(1,8)),'notes'=>'Tugas housekeeping demo',
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
        foreach ($rooms->take(2) as $room) {
            DB::table('room_maintenance')->insert([
                'tenant_id'=>$this->tenantId,'room_id'=>$room->id,'reported_by'=>$userIds[0]??$this->adminId,
                'title'=>'AC tidak dingin','description'=>'Perlu perbaikan sistem pendingin ruangan',
                'status'=>'reported','priority'=>'high','cost'=>0,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
        // Housekeeping supplies
        foreach ([
            ['name'=>'Sabun Mandi','unit'=>'pcs','current_stock'=>200,'minimum_stock'=>50,'cost_per_unit'=>5000],
            ['name'=>'Shampo','unit'=>'pcs','current_stock'=>150,'minimum_stock'=>30,'cost_per_unit'=>8000],
            ['name'=>'Handuk Mandi','unit'=>'pcs','current_stock'=>100,'minimum_stock'=>20,'cost_per_unit'=>35000],
            ['name'=>'Sprei','unit'=>'set','current_stock'=>80,'minimum_stock'=>15,'cost_per_unit'=>75000],
        ] as $supply) {
            DB::table('housekeeping_supplies')->insertOrIgnore([
                'tenant_id'=>$this->tenantId,'name'=>$supply['name'],'unit'=>$supply['unit'],
                'current_stock'=>$supply['current_stock'],'minimum_stock'=>$supply['minimum_stock'],
                'cost_per_unit'=>$supply['cost_per_unit'],'is_active'=>true,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedHotelSpa(): void
    {
        if (DB::table('spa_therapists')->where('tenant_id',$this->tenantId)->exists()) return;
        foreach ([
            ['employee_number'=>'SPA-001','name'=>'Maya Sari','specializations'=>json_encode(['Traditional Massage']),'hourly_rate'=>50000,'rating'=>4.8,'total_treatments'=>120],
            ['employee_number'=>'SPA-002','name'=>'Dewi Anggraini','specializations'=>json_encode(['Aromatherapy']),'hourly_rate'=>45000,'rating'=>4.6,'total_treatments'=>95],
            ['employee_number'=>'SPA-003','name'=>'Rina Kusuma','specializations'=>json_encode(['Hot Stone Therapy']),'hourly_rate'=>55000,'rating'=>4.9,'total_treatments'=>150],
        ] as $therapist) {
            DB::table('spa_therapists')->insert(array_merge($therapist,[
                'tenant_id'=>$this->tenantId,'phone'=>'0812-'.rand(10000000,99999999),
                'email'=>strtolower(str_replace(' ','.',$therapist['name'])).'@spa.com',
                'status'=>'available','is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
            ]));
        }
        $treatmentIds = [];
        foreach ([
            ['name'=>'Balinese Massage 60 min','duration_minutes'=>60,'price'=>250000,'category'=>'massage','cost'=>50000,'preparation_time'=>10,'cleanup_time'=>10],
            ['name'=>'Aromatherapy Treatment 90 min','duration_minutes'=>90,'price'=>350000,'category'=>'aromatherapy','cost'=>70000,'preparation_time'=>15,'cleanup_time'=>15],
            ['name'=>'Hot Stone Therapy 75 min','duration_minutes'=>75,'price'=>300000,'category'=>'therapy','cost'=>60000,'preparation_time'=>20,'cleanup_time'=>10],
            ['name'=>'Facial Treatment 45 min','duration_minutes'=>45,'price'=>200000,'category'=>'facial','cost'=>40000,'preparation_time'=>10,'cleanup_time'=>10],
        ] as $treatment) {
            $treatmentIds[] = DB::table('spa_treatments')->insertGetId(array_merge($treatment,[
                'tenant_id'=>$this->tenantId,'description'=>'Perawatan spa '.$treatment['name'],
                'benefits'=>json_encode(['Relaxation','Stress relief']),'requires_consultation'=>false,
                'max_daily_bookings'=>10,'booked_today'=>rand(0,3),'is_active'=>true,'display_order'=>0,
                'created_at'=>now(),'updated_at'=>now(),
            ]));
        }
        foreach ([
            ['name'=>'Relaxation Package','package_price'=>500000,'regular_price'=>600000,'savings'=>100000,'total_duration_minutes'=>120],
            ['name'=>'Luxury Spa Day','package_price'=>850000,'regular_price'=>1050000,'savings'=>200000,'total_duration_minutes'=>180],
        ] as $pkg) {
            $pkgId = DB::table('spa_packages')->insertGetId(array_merge($pkg,[
                'tenant_id'=>$this->tenantId,'description'=>'Paket spa '.$pkg['name'],
                'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
            ]));
            foreach (array_slice($treatmentIds,0,2) as $i=>$tid) {
                DB::table('spa_package_items')->insert([
                    'tenant_id'=>$this->tenantId,'package_id'=>$pkgId,'treatment_id'=>$tid,
                    'sequence_order'=>$i+1,'duration_override'=>null,'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }
    }

    private function seedHotelNightAudit(): void
    {
        if (DB::table('night_audit_batches')->where('tenant_id',$this->tenantId)->exists()) return;
        $totalRooms = DB::table('rooms')->where('tenant_id',$this->tenantId)->count();
        for ($i=2;$i>=0;$i--) {
            $auditDate=now()->subDays($i);
            $occupiedRooms=rand(5,max(6,$totalRooms-2));
            $occupancyRate=$totalRooms>0?round(($occupiedRooms/$totalRooms)*100,2):0;
            $totalRevenue=$occupiedRooms*450000;
            $adr=$occupiedRooms>0?round($totalRevenue/$occupiedRooms,2):0;
            DB::table('night_audit_batches')->insert([
                'tenant_id'=>$this->tenantId,'batch_number'=>'NA-'.$auditDate->format('Ymd'),
                'audit_date'=>$auditDate,'status'=>'completed',
                'started_at'=>$auditDate->copy()->setTime(23,0),'completed_at'=>$auditDate->copy()->setTime(23,45),
                'auditor_id'=>$this->adminId,'notes'=>'Audit malam otomatis',
                'summary_data'=>json_encode(['details'=>'Audit summary']),
                'total_rooms'=>$totalRooms,'occupied_rooms'=>$occupiedRooms,'occupancy_rate'=>$occupancyRate,
                'total_room_revenue'=>$totalRevenue,'total_fb_revenue'=>rand(1000000,3000000),
                'total_other_revenue'=>rand(200000,800000),'total_revenue'=>$totalRevenue+rand(1200000,3800000),
                'adr'=>$adr,'revpar'=>round($totalRevenue/$totalRooms,2),
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function seedHotelRevenueManagement(): void
    {
        if (DB::table('rate_plans')->where('tenant_id',$this->tenantId)->exists()) return;
        $roomTypeIds = DB::table('room_types')->where('tenant_id',$this->tenantId)->pluck('id')->toArray();
        if (empty($roomTypeIds)) return;
        foreach ($roomTypeIds as $rtId) {
            $roomType = DB::table('room_types')->find($rtId);
            foreach ([
                ['name'=>'Best Available Rate','code'=>'BAR','type'=>'standard','is_refundable'=>true],
                ['name'=>'Non-Refundable Discount','code'=>'NR','type'=>'non_refundable','is_refundable'=>false],
                ['name'=>'Corporate Rate','code'=>'CORP','type'=>'corporate','is_refundable'=>true],
                ['name'=>'Weekend Special','code'=>'WKND','type'=>'promotional','is_refundable'=>true],
            ] as $rp) {
                DB::table('rate_plans')->insert(array_merge($rp,[
                    'tenant_id'=>$this->tenantId,'room_type_id'=>$rtId,
                    'code'=>$rp['code'].'-'.$roomType->code,
                    'description'=>'Plan tarif '.$rp['name'].' - '.$roomType->name,
                    'base_rate'=>$roomType->base_rate*($rp['type']==='non_refundable'?0.85:1),
                    'min_stay'=>1,'max_stay'=>null,'advance_booking_days'=>null,
                    'cancellation_hours'=>$rp['is_refundable']?24:0,'includes_breakfast'=>false,
                    'inclusions'=>json_encode([]),'is_active'=>true,'valid_from'=>null,'valid_to'=>null,
                    'created_at'=>now(),'updated_at'=>now(),
                ]));
            }
        }
        foreach ([
            ['name'=>'Hotel Grand Palace','source'=>'Booking.com'],
            ['name'=>'Sunrise Resort','source'=>'Agoda'],
            ['name'=>'City Center Hotel','source'=>'Manual'],
        ] as $comp) {
            for ($d=0;$d<7;$d++) {
                DB::table('competitor_rates')->insert([
                    'tenant_id'=>$this->tenantId,'competitor_name'=>$comp['name'],'source'=>$comp['source'],
                    'rate_date'=>now()->addDays($d),'rate'=>400000+rand(0,200)*1000,
                    'room_type'=>'Standard Room','amenities'=>json_encode(['wifi','breakfast']),
                    'notes'=>null,'recorded_by'=>$this->adminId,'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  TELECOM
    // ══════════════════════════════════════════════════════════════

    private function seedTelecom(): void
    {
        if (DB::table('network_devices')->where('tenant_id', $this->tenantId)->exists()) return;

        // Network devices
        $deviceIds = [];
        foreach ([
            ['name' => 'Router Utama MikroTik RB4011', 'type' => 'router', 'brand' => 'MikroTik', 'model' => 'RB4011', 'ip_address' => '192.168.1.1', 'mac_address' => 'AA:BB:CC:DD:EE:01', 'status' => 'online'],
            ['name' => 'Switch Core Cisco SG350', 'type' => 'switch', 'brand' => 'Cisco', 'model' => 'SG350-28', 'ip_address' => '192.168.1.2', 'mac_address' => 'AA:BB:CC:DD:EE:02', 'status' => 'online'],
            ['name' => 'Access Point Ubiquiti UAP-AC-Pro', 'type' => 'access_point', 'brand' => 'Ubiquiti', 'model' => 'UAP-AC-Pro', 'ip_address' => '192.168.1.10', 'mac_address' => 'AA:BB:CC:DD:EE:03', 'status' => 'online'],
        ] as $dev) {
            $deviceIds[] = DB::table('network_devices')->insertGetId(array_merge($dev, [
                'tenant_id' => $this->tenantId,
                'location'  => 'Server Room Lt. 1',
                'uptime'    => rand(86400, 2592000),
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }

        // Internet packages
        $pkgIds = [];
        foreach ([
            ['name' => 'Paket Basic 10 Mbps',    'code' => 'PKG-10M',  'download_speed' => 10,  'upload_speed' => 5,  'price' => 150000,  'data_limit_gb' => null, 'validity_days' => 30],
            ['name' => 'Paket Standard 25 Mbps',  'code' => 'PKG-25M',  'download_speed' => 25,  'upload_speed' => 10, 'price' => 250000,  'data_limit_gb' => null, 'validity_days' => 30],
            ['name' => 'Paket Premium 50 Mbps',   'code' => 'PKG-50M',  'download_speed' => 50,  'upload_speed' => 20, 'price' => 450000,  'data_limit_gb' => null, 'validity_days' => 30],
            ['name' => 'Paket Unlimited 100 Mbps','code' => 'PKG-100M', 'download_speed' => 100, 'upload_speed' => 50, 'price' => 750000,  'data_limit_gb' => null, 'validity_days' => 30],
            ['name' => 'Voucher Harian 5 Mbps',   'code' => 'VCH-5M',   'download_speed' => 5,   'upload_speed' => 2,  'price' => 10000,   'data_limit_gb' => 1,    'validity_days' => 1],
        ] as $pkg) {
            $pkgIds[] = DB::table('internet_packages')->insertGetId(array_merge($pkg, [
                'tenant_id'  => $this->tenantId,
                'is_active'  => true,
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }

        // Telecom subscriptions (customers)
        foreach (array_slice($this->customerIds, 0, 3) as $i => $custId) {
            DB::table('telecom_subscriptions')->insertOrIgnore([
                'tenant_id'          => $this->tenantId,
                'customer_id'        => $custId,
                'package_id'         => $pkgIds[$i % count($pkgIds)],
                'subscription_number'=> 'SUB-'.str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'ip_address'         => '192.168.10.'.($i + 10),
                'mac_address'        => 'BB:CC:DD:EE:FF:'.str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                'status'             => 'active',
                'started_at'         => Carbon::now()->subMonths(rand(1, 6)),
                'expires_at'         => Carbon::now()->addDays(rand(5, 25)),
                'created_at'         => now(), 'updated_at' => now(),
            ]);
        }

        // Hotspot users
        foreach ([
            ['username' => 'hotspot_user1', 'password' => 'pass123', 'profile' => 'basic'],
            ['username' => 'hotspot_user2', 'password' => 'pass456', 'profile' => 'premium'],
            ['username' => 'hotspot_guest1', 'password' => 'guest001', 'profile' => 'guest'],
        ] as $hu) {
            DB::table('hotspot_users')->insertOrIgnore([
                'tenant_id'       => $this->tenantId,
                'username'        => $hu['username'],
                'password'        => bcrypt($hu['password']),
                'profile'         => $hu['profile'],
                'status'          => 'active',
                'data_used_mb'    => rand(100, 5000),
                'data_limit_mb'   => 10240,
                'session_timeout' => 3600,
                'created_at'      => now(), 'updated_at' => now(),
            ]);
        }

        // Voucher codes
        foreach (range(1, 5) as $i) {
            DB::table('voucher_codes')->insertOrIgnore([
                'tenant_id'   => $this->tenantId,
                'package_id'  => $pkgIds[4], // daily voucher
                'code'        => 'VCH-'.strtoupper(Str::random(8)),
                'status'      => $i <= 2 ? 'used' : 'available',
                'used_at'     => $i <= 2 ? Carbon::now()->subDays($i) : null,
                'expires_at'  => Carbon::now()->addDays(30),
                'created_at'  => now(), 'updated_at' => now(),
            ]);
        }

        // Bandwidth allocations
        foreach (array_slice($this->customerIds, 0, 2) as $i => $custId) {
            DB::table('bandwidth_allocations')->insertOrIgnore([
                'tenant_id'        => $this->tenantId,
                'customer_id'      => $custId,
                'package_id'       => $pkgIds[$i],
                'download_limit'   => ($i + 1) * 10 * 1024, // kbps
                'upload_limit'     => ($i + 1) * 5 * 1024,
                'burst_download'   => ($i + 1) * 15 * 1024,
                'burst_upload'     => ($i + 1) * 8 * 1024,
                'is_active'        => true,
                'created_at'       => now(), 'updated_at' => now(),
            ]);
        }

        // Network alerts
        foreach ([
            ['device_id' => $deviceIds[0], 'type' => 'high_cpu',       'severity' => 'warning', 'message' => 'CPU usage mencapai 85%',              'status' => 'resolved'],
            ['device_id' => $deviceIds[1], 'type' => 'port_down',       'severity' => 'critical','message' => 'Port GE0/1 down selama 5 menit',      'status' => 'open'],
            ['device_id' => $deviceIds[2], 'type' => 'high_traffic',    'severity' => 'info',    'message' => 'Traffic melebihi 80% kapasitas',       'status' => 'acknowledged'],
        ] as $alert) {
            DB::table('network_alerts')->insert([
                'tenant_id'    => $this->tenantId,
                'device_id'    => $alert['device_id'],
                'type'         => $alert['type'],
                'severity'     => $alert['severity'],
                'message'      => $alert['message'],
                'status'       => $alert['status'],
                'triggered_at' => Carbon::now()->subHours(rand(1, 24)),
                'created_at'   => now(), 'updated_at' => now(),
            ]);
        }

        // Usage tracking
        foreach (array_slice($this->customerIds, 0, 2) as $custId) {
            for ($d = 6; $d >= 0; $d--) {
                DB::table('usage_tracking')->insert([
                    'tenant_id'       => $this->tenantId,
                    'customer_id'     => $custId,
                    'date'            => Carbon::now()->subDays($d)->format('Y-m-d'),
                    'download_mb'     => rand(500, 5000),
                    'upload_mb'       => rand(100, 1000),
                    'session_count'   => rand(1, 10),
                    'online_duration' => rand(3600, 86400),
                    'created_at'      => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  HEALTHCARE
    // ══════════════════════════════════════════════════════════════

    private function seedHealthcare(): void
    {
        if (DB::table('patients')->where('tenant_id', $this->tenantId)->exists()) return;

        // Doctors
        $doctorIds = [];
        foreach ([
            ['name' => 'dr. Andi Kurniawan, Sp.PD',  'specialization' => 'Penyakit Dalam',  'license_number' => 'STR-001-2026', 'consultation_fee' => 150000],
            ['name' => 'dr. Sari Dewi, Sp.A',         'specialization' => 'Anak',             'license_number' => 'STR-002-2026', 'consultation_fee' => 175000],
            ['name' => 'dr. Budi Santoso, Sp.OG',     'specialization' => 'Obstetri Ginekologi','license_number' => 'STR-003-2026','consultation_fee' => 200000],
            ['name' => 'dr. Maya Indah, Sp.JP',       'specialization' => 'Jantung & Pembuluh Darah','license_number' => 'STR-004-2026','consultation_fee' => 250000],
        ] as $doc) {
            $doctorIds[] = DB::table('doctors')->insertGetId(array_merge($doc, [
                'tenant_id'   => $this->tenantId,
                'phone'       => '0812-'.rand(10000000, 99999999),
                'email'       => strtolower(str_replace([' ', '.', ','], '', $doc['name'])).'@rsmbi.co.id',
                'schedule'    => json_encode(['monday' => '08:00-14:00', 'wednesday' => '08:00-14:00', 'friday' => '08:00-12:00']),
                'is_active'   => true,
                'created_at'  => now(), 'updated_at' => now(),
            ]));
        }

        // Patients
        $patientIds = [];
        foreach ([
            ['name' => 'Hendra Gunawan',   'dob' => '1985-03-15', 'gender' => 'male',   'blood_type' => 'A',  'phone' => '0812-2222-3333'],
            ['name' => 'Rina Wulandari',   'dob' => '1992-07-22', 'gender' => 'female', 'blood_type' => 'B',  'phone' => '0813-4444-5555'],
            ['name' => 'Agus Setiawan',    'dob' => '1978-11-08', 'gender' => 'male',   'blood_type' => 'O',  'phone' => '0815-6666-7777'],
            ['name' => 'Dewi Rahayu',      'dob' => '2001-05-30', 'gender' => 'female', 'blood_type' => 'AB', 'phone' => '0816-8888-9999'],
            ['name' => 'Bambang Susilo',   'dob' => '1965-09-12', 'gender' => 'male',   'blood_type' => 'A',  'phone' => '0817-1111-2222'],
        ] as $i => $p) {
            $patientIds[] = DB::table('patients')->insertGetId(array_merge($p, [
                'tenant_id'      => $this->tenantId,
                'patient_number' => 'PAT-'.str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'address'        => 'Jakarta',
                'email'          => strtolower(str_replace(' ', '.', $p['name'])).'@gmail.com',
                'marital_status' => $i % 2 === 0 ? 'married' : 'single',
                'religion'       => 'Islam',
                'nationality'    => 'WNI',
                'created_at'     => now(), 'updated_at' => now(),
            ]));
        }

        // Patient allergies
        DB::table('patient_allergies')->insert([
            'tenant_id'  => $this->tenantId,
            'patient_id' => $patientIds[0],
            'allergen'   => 'Penisilin',
            'reaction'   => 'Ruam kulit dan gatal-gatal',
            'severity'   => 'moderate',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Patient insurances
        DB::table('patient_insurances')->insert([
            'tenant_id'       => $this->tenantId,
            'patient_id'      => $patientIds[0],
            'insurance_type'  => 'bpjs',
            'insurance_number'=> 'BPJS-'.rand(1000000000, 9999999999),
            'provider'        => 'BPJS Kesehatan',
            'class'           => '2',
            'valid_from'      => '2026-01-01',
            'valid_until'     => '2026-12-31',
            'is_active'       => true,
            'created_at'      => now(), 'updated_at' => now(),
        ]);

        // Appointments
        $appointmentIds = [];
        foreach (array_slice($patientIds, 0, 3) as $i => $patId) {
            $appointmentIds[] = DB::table('appointments')->insertGetId([
                'tenant_id'          => $this->tenantId,
                'patient_id'         => $patId,
                'doctor_id'          => $doctorIds[$i % count($doctorIds)],
                'appointment_number' => 'APT-DEMO-'.str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'appointment_date'   => Carbon::now()->addDays($i + 1)->format('Y-m-d'),
                'appointment_time'   => '09:'.str_pad($i * 15, 2, '0', STR_PAD_LEFT).':00',
                'appointment_type'   => 'consultation', // valid: consultation, follow_up, check_up, procedure, telemedicine, emergency
                'visit_type'         => 'outpatient',   // valid after migration 2026_04_15_000010
                'status'             => 'scheduled',
                'reason_for_visit'   => ['Demam dan batuk', 'Kontrol rutin', 'Nyeri dada'][$i],
                'notes'              => 'Janji temu demo',
                'created_at'         => now(), 'updated_at' => now(),
            ]);
        }

        // Patient visits (outpatient)
        foreach (array_slice($patientIds, 0, 3) as $i => $patId) {
            $visitId = DB::table('patient_visits')->insertGetId([
                'tenant_id'          => $this->tenantId,
                'patient_id'         => $patId,
                'doctor_id'          => $doctorIds[$i % count($doctorIds)],
                'visit_number'       => 'VIS-DEMO-'.str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'visit_date'         => Carbon::now()->subDays($i + 1)->format('Y-m-d'),
                'visit_time'         => '10:00:00',
                'visit_type'         => 'outpatient', // valid: outpatient, inpatient, emergency, telemedicine, home_care
                'visit_status'       => 'completed',  // valid: registered, waiting, in_consultation, completed, referred, cancelled
                'chief_complaint'    => ['Demam 3 hari', 'Batuk pilek', 'Kontrol tekanan darah'][$i],
                'primary_diagnosis'  => ['Infeksi saluran pernapasan atas', 'Common cold', 'Hipertensi grade 1'][$i],
                'icd10_code'         => ['J06.9', 'J00', 'I10'][$i],
                'treatment_summary'  => 'Pemberian obat dan istirahat',
                'created_at'         => now(), 'updated_at' => now(),
            ]);

            // Medical records (EMR)
            DB::table('patient_medical_records')->insertOrIgnore([
                'tenant_id'        => $this->tenantId,
                'patient_id'       => $patId,
                'visit_id'         => $visitId,
                'doctor_id'        => $doctorIds[$i % count($doctorIds)],
                'record_date'      => Carbon::now()->subDays($i + 1)->format('Y-m-d'),
                'subjective'       => 'Pasien mengeluh '.['demam 3 hari', 'batuk pilek', 'pusing dan nyeri kepala'][$i],
                'objective'        => json_encode(['td' => '120/80', 'nadi' => 80, 'suhu' => 37.5, 'rr' => 18]),
                'assessment'       => ['ISPA', 'Common cold', 'Hipertensi'][$i],
                'plan'             => 'Terapi simtomatik, kontrol ulang 1 minggu',
                'created_at'       => now(), 'updated_at' => now(),
            ]);
        }

        // Prescriptions
        $medIds = [];
        foreach ([
            ['name' => 'Paracetamol 500mg', 'generic_name' => 'Paracetamol', 'category' => 'analgesic',    'unit' => 'tablet', 'stock' => 1000, 'price' => 500],
            ['name' => 'Amoxicillin 500mg',  'generic_name' => 'Amoxicillin',  'category' => 'antibiotic',   'unit' => 'kapsul', 'stock' => 500,  'price' => 2000],
            ['name' => 'Antasida Doen',       'generic_name' => 'Antasida',     'category' => 'antacid',      'unit' => 'tablet', 'stock' => 800,  'price' => 300],
            ['name' => 'Amlodipine 5mg',      'generic_name' => 'Amlodipine',   'category' => 'antihypertensive','unit' => 'tablet','stock' => 600, 'price' => 1500],
            ['name' => 'Vitamin C 500mg',     'generic_name' => 'Ascorbic Acid','category' => 'vitamin',      'unit' => 'tablet', 'stock' => 2000, 'price' => 800],
        ] as $med) {
            $medIds[] = DB::table('medicines')->insertGetId(array_merge($med, [
                'tenant_id'   => $this->tenantId,
                'is_active'   => true,
                'created_at'  => now(), 'updated_at' => now(),
            ]));
        }

        foreach (array_slice($patientIds, 0, 2) as $i => $patId) {
            $prescId = DB::table('prescriptions')->insertGetId([
                'tenant_id'         => $this->tenantId,
                'patient_id'        => $patId,
                'doctor_id'         => $doctorIds[$i % count($doctorIds)],
                'prescription_date' => Carbon::now()->subDays($i + 1)->format('Y-m-d'),
                'status'            => $i === 0 ? 'dispensed' : 'pending',
                'notes'             => 'Minum obat sesuai anjuran dokter',
                'created_at'        => now(), 'updated_at' => now(),
            ]);
            foreach (array_slice($medIds, 0, 2) as $j => $medId) {
                DB::table('prescription_items')->insert([
                    'prescription_id' => $prescId,
                    'medicine_id'     => $medId,
                    'quantity'        => ($j + 1) * 10,
                    'dosage'          => '3x1',
                    'instructions'    => 'Sesudah makan',
                    'created_at'      => now(), 'updated_at' => now(),
                ]);
            }
        }

        // Lab results — skipped in demo seeder due to complex required FK dependencies
        // (lab_orders, lab_test_catalogs must exist first; use HealthcareGenerator for full data)
        $labId = null;

        // Medical billing
        foreach (array_slice($patientIds, 0, 2) as $i => $patId) {
            $totalAmount = 150000 + rand(50000, 350000);
            $isPaid = $i === 0;
            DB::table('medical_bills')->insertOrIgnore([
                'tenant_id'      => $this->tenantId,
                'patient_id'     => $patId,
                'bill_number'    => 'BILL-'.str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'bill_date'      => Carbon::now()->subDays($i + 1)->format('Y-m-d'),
                'subtotal'       => $totalAmount,
                'total_amount'   => $totalAmount,
                'amount_paid'    => $isPaid ? $totalAmount : 0,
                'balance_due'    => $isPaid ? 0 : $totalAmount,
                'payment_status' => $isPaid ? 'paid' : 'unpaid', // valid: unpaid, partial, paid, overdue, written_off, refunded
                'billing_status' => 'finalized',                  // valid: draft, finalized, submitted, approved, rejected, cancelled
                'financial_class'=> $isPaid ? 'insurance' : 'self_pay',
                'created_at'     => now(), 'updated_at' => now(),
            ]);
        }

        // Wards & beds
        $wardId = DB::table('wards')->insertGetId([
            'tenant_id'   => $this->tenantId,
            'name'        => 'Ruang Rawat Inap Kelas 2',
            'code'        => 'WARD-K2',
            'type'        => 'general',
            'floor'       => '2',
            'capacity'    => 20,
            'is_active'   => true,
            'created_at'  => now(), 'updated_at' => now(),
        ]);
        foreach (range(1, 5) as $b) {
            DB::table('beds')->insertOrIgnore([
                'tenant_id'  => $this->tenantId,
                'ward_id'    => $wardId,
                'bed_number' => 'K2-'.str_pad($b, 2, '0', STR_PAD_LEFT),
                'status'     => $b <= 3 ? 'occupied' : 'available',
                'is_active'  => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Telemedicine settings
        DB::table('telemedicine_settings')->updateOrInsert(
            ['tenant_id' => $this->tenantId],
            [
                'tenant_id'              => $this->tenantId,
                'is_enabled'             => true,
                'consultation_fee'       => 75000,
                'platform'               => 'zoom',
                'max_duration_minutes'   => 30,
                'booking_advance_hours'  => 2,
                'created_at'             => now(), 'updated_at' => now(),
            ]
        );

        // Teleconsultations
        foreach (array_slice($patientIds, 0, 2) as $i => $patId) {
            DB::table('teleconsultations')->insertOrIgnore([
                'tenant_id'          => $this->tenantId,
                'patient_id'         => $patId,
                'doctor_id'          => $doctorIds[$i % count($doctorIds)],
                'consultation_number'=> 'TC-'.str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'scheduled_at'       => Carbon::now()->addDays($i + 2)->setTime(10, 0),
                'duration_minutes'   => 30,
                'platform'           => 'zoom',
                'meeting_url'        => 'https://zoom.us/j/'.rand(100000000, 999999999),
                'status'             => 'scheduled',
                'fee'                => 75000,
                'chief_complaint'    => 'Konsultasi online demo',
                'created_at'         => now(), 'updated_at' => now(),
            ]);
        }

        // Medical staff schedules
        foreach (array_slice($doctorIds, 0, 2) as $i => $docId) {
            foreach (['monday', 'wednesday', 'friday'] as $day) {
                DB::table('medical_staff_schedules')->insertOrIgnore([
                    'tenant_id'  => $this->tenantId,
                    'doctor_id'  => $docId,
                    'day_of_week'=> $day,
                    'start_time' => '08:00',
                    'end_time'   => '14:00',
                    'quota'      => 20,
                    'is_active'  => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        // Pharmacy inventory
        foreach (array_slice($medIds, 0, 3) as $i => $medId) {
            DB::table('pharmacy_inventories')->insertOrIgnore([
                'tenant_id'      => $this->tenantId,
                'medicine_id'    => $medId,
                'batch_number'   => 'BATCH-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'quantity'       => rand(100, 1000),
                'unit_price'     => rand(500, 5000),
                'expiry_date'    => Carbon::now()->addYears(2)->format('Y-m-d'),
                'supplier_name'  => 'PT Kimia Farma',
                'created_at'     => now(), 'updated_at' => now(),
            ]);
        }

        // Operating rooms
        foreach ([
            ['name' => 'OK 1 - Bedah Umum',    'code' => 'OK-01', 'type' => 'general'],
            ['name' => 'OK 2 - Bedah Ortopedi', 'code' => 'OK-02', 'type' => 'orthopedic'],
        ] as $or) {
            DB::table('operating_rooms')->insertOrIgnore([
                'tenant_id'  => $this->tenantId,
                'name'       => $or['name'],
                'code'       => $or['code'],
                'type'       => $or['type'],
                'status'     => 'available',
                'is_active'  => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Patient satisfaction surveys
        foreach (array_slice($patientIds, 0, 2) as $i => $patId) {
            DB::table('patient_satisfaction_surveys')->insertOrIgnore([
                'tenant_id'          => $this->tenantId,
                'patient_id'         => $patId,
                'doctor_id'          => $doctorIds[$i % count($doctorIds)],
                'survey_date'        => Carbon::now()->subDays($i + 1)->format('Y-m-d'),
                'doctor_score'       => rand(4, 5),
                'service_score'      => rand(3, 5),
                'facility_score'     => rand(3, 5),
                'overall_score'      => rand(4, 5),
                'comments'           => 'Pelayanan baik dan dokter ramah',
                'would_recommend'    => true,
                'created_at'         => now(), 'updated_at' => now(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  IoT DEVICES
    // ══════════════════════════════════════════════════════════════

    private function seedIotDevices(): void
    {
        $devices = [
            [
                'name'          => 'ESP32 Sensor Gudang A',
                'device_type'   => 'esp32',
                'location'      => 'Gudang A Lantai 1',
                'target_module' => 'inventory',
                'sensor_types'  => ['temperature', 'humidity'],
                'firmware_version' => 'v1.0.0',
            ],
            [
                'name'          => 'Raspberry Pi Gateway Produksi',
                'device_type'   => 'raspberry_pi',
                'location'      => 'Lantai Produksi',
                'target_module' => 'manufacturing',
                'sensor_types'  => ['counter', 'temperature'],
                'firmware_version' => 'rpi-agent-v1.0.0',
            ],
            [
                'name'          => 'ESP32 Sensor Kolam Ikan',
                'device_type'   => 'esp32',
                'location'      => 'Kolam Budidaya No.1',
                'target_module' => 'fisheries',
                'sensor_types'  => ['temperature', 'ph', 'turbidity'],
                'firmware_version' => 'v1.2.0',
            ],
            [
                'name'          => 'Arduino Counter Mesin Press',
                'device_type'   => 'arduino',
                'location'      => 'Area Mesin Press',
                'target_module' => 'manufacturing',
                'sensor_types'  => ['counter'],
                'firmware_version' => 'v0.9.0',
            ],
        ];

        foreach ($devices as $i => $d) {
            $deviceId = DB::table('iot_devices')->insertGetId([
                'tenant_id'       => $this->tenantId,
                'name'            => $d['name'],
                'device_id'       => 'DEV-' . strtoupper(substr(md5($d['name']), 0, 8)),
                'device_token'    => bin2hex(random_bytes(32)),
                'device_type'     => $d['device_type'],
                'location'        => $d['location'],
                'target_module'   => $d['target_module'],
                'sensor_types'    => json_encode($d['sensor_types']),
                'firmware_version'=> $d['firmware_version'],
                'is_active'       => true,
                'is_connected'    => $i < 2, // 2 pertama online
                'last_seen_at'    => $i < 2 ? now()->subMinutes(rand(1, 10)) : now()->subHours(rand(2, 24)),
                'created_at'      => now(), 'updated_at' => now(),
            ]);

            // Seed sample telemetry logs
            foreach ($d['sensor_types'] as $sensorType) {
                $sampleValues = [
                    'temperature' => [25.0, 26.5, 28.0, 27.3, 29.1],
                    'humidity'    => [65.0, 68.5, 72.0, 70.2, 66.8],
                    'ph'          => [6.8, 7.0, 7.2, 6.9, 7.1],
                    'turbidity'   => [12.5, 15.0, 11.8, 13.2, 14.0],
                    'counter'     => [100, 150, 200, 175, 220],
                ];
                $units = [
                    'temperature' => '°C', 'humidity' => '%', 'ph' => 'pH',
                    'turbidity' => 'NTU', 'counter' => 'pcs',
                ];
                $values = $sampleValues[$sensorType] ?? [1, 2, 3, 4, 5];

                foreach ($values as $j => $val) {
                    DB::table('iot_telemetry_logs')->insert([
                        'tenant_id'     => $this->tenantId,
                        'iot_device_id' => $deviceId,
                        'sensor_type'   => $sensorType,
                        'value'         => $val,
                        'unit'          => $units[$sensorType] ?? '',
                        'payload'       => json_encode(['type' => $sensorType, 'value' => $val]),
                        'status'        => 'received',
                        'recorded_at'   => now()->subMinutes(($j + 1) * 30),
                        'created_at'    => now(), 'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
