<?php

namespace Tests\Unit\Services\Agent;

use App\Models\Attendance;
use App\Models\CrmLead;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\PayrollRun;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Project;
use App\Models\SalesOrder;
use App\Models\Transaction;
use App\Services\Agent\CrossModuleQueryService;
use Tests\TestCase;

/**
 * Integration Tests for CrossModuleQueryService.
 *
 * Feature: erp-ai-agent
 *
 * Verifikasi:
 * - Query 3 modul selesai dalam < 5 detik
 * - Data yang dikembalikan akurat sesuai data yang di-seed
 * - Partial results bekerja dengan benar
 *
 * Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5
 */
class CrossModuleQueryIntegrationTest extends TestCase
{
    // =========================================================================
    // Test: query 3 modul selesai dalam < 5 detik
    //
    // Validates: Requirements 3.4
    // =========================================================================

    public function testThreeModuleQueryCompletesWithinFiveSeconds(): void
    {
        $tenant    = $this->createTenant();
        $warehouse = $this->createWarehouse($tenant->id);

        // Seed data representatif untuk 3 modul
        $this->seedSalesData($tenant->id);
        $this->seedCrmData($tenant->id);
        $this->seedInventoryData($tenant->id, $warehouse->id);

        $service = new CrossModuleQueryService($tenant->id, ['sales', 'crm', 'inventory']);

        $start   = microtime(true);
        $result  = $service->queryPenjualanCrmInventory([]);
        $elapsed = microtime(true) - $start;

        $this->assertLessThan(
            5.0,
            $elapsed,
            "queryPenjualanCrmInventory() harus selesai dalam < 5 detik, tapi membutuhkan {$elapsed} detik"
        );

        $this->assertSame('success', $result['status']);
    }

    public function testHrmPayrollAbsensiQueryCompletesWithinFiveSeconds(): void
    {
        $tenant = $this->createTenant();

        $this->seedHrmData($tenant->id);
        $this->seedPayrollData($tenant->id);
        $this->seedAttendanceData($tenant->id);

        $service = new CrossModuleQueryService($tenant->id, ['hrm', 'payroll']);

        $start   = microtime(true);
        $result  = $service->queryHrmPayrollAbsensi([]);
        $elapsed = microtime(true) - $start;

        $this->assertLessThan(
            5.0,
            $elapsed,
            "queryHrmPayrollAbsensi() harus selesai dalam < 5 detik, tapi membutuhkan {$elapsed} detik"
        );

        $this->assertSame('success', $result['status']);
    }

    public function testProjectKeuanganQueryCompletesWithinFiveSeconds(): void
    {
        $tenant   = $this->createTenant();
        $user     = $this->createAdminUser($tenant);
        $customer = $this->createCustomer($tenant->id);

        $this->seedProjectData($tenant->id, $user->id, $customer->id);
        $this->seedTransactionData($tenant->id, $user->id);

        $service = new CrossModuleQueryService($tenant->id, ['project', 'accounting']);

        $start   = microtime(true);
        $result  = $service->queryProjectKeuangan([]);
        $elapsed = microtime(true) - $start;

        $this->assertLessThan(
            5.0,
            $elapsed,
            "queryProjectKeuangan() harus selesai dalam < 5 detik, tapi membutuhkan {$elapsed} detik"
        );

        $this->assertSame('success', $result['status']);
    }

    // =========================================================================
    // Test: queryAkuntansiInventory mengembalikan data yang akurat
    //
    // Validates: Requirements 3.1, 3.2
    // =========================================================================

    public function testAkuntansiInventoryReturnsAccurateData(): void
    {
        $tenant    = $this->createTenant();
        $user      = $this->createAdminUser($tenant);
        $warehouse = $this->createWarehouse($tenant->id);

        // Seed transaksi keuangan
        Transaction::withoutGlobalScopes()->create([
            'tenant_id'   => $tenant->id,
            'user_id'     => $user->id,
            'number'      => 'TRX-' . uniqid(),
            'type'        => 'income',
            'date'        => now()->startOfMonth()->addDays(2),
            'amount'      => 5000000,
            'description' => 'Pendapatan test',
        ]);
        Transaction::withoutGlobalScopes()->create([
            'tenant_id'   => $tenant->id,
            'user_id'     => $user->id,
            'number'      => 'TRX-' . uniqid(),
            'type'        => 'expense',
            'date'        => now()->startOfMonth()->addDays(3),
            'amount'      => 2000000,
            'description' => 'Pengeluaran test',
        ]);

        // Seed produk dengan stok kritis
        $product = $this->createProduct($tenant->id, ['stock_min' => 10, 'price_buy' => 50000]);
        $this->setStock($product->id, $warehouse->id, 3);

        $service = new CrossModuleQueryService($tenant->id, ['accounting', 'inventory']);
        $result  = $service->queryAkuntansiInventory(['period' => 'this_month']);

        $this->assertSame('success', $result['status']);
        $this->assertArrayHasKey('akuntansi', $result['data']);
        $this->assertArrayHasKey('inventory', $result['data']);
        $this->assertEmpty($result['unavailable_modules']);

        // Verifikasi data akuntansi
        $this->assertEqualsWithDelta(5000000, $result['data']['akuntansi']['pendapatan'], 0.01);
        $this->assertEqualsWithDelta(2000000, $result['data']['akuntansi']['pengeluaran'], 0.01);
        $this->assertEqualsWithDelta(3000000, $result['data']['akuntansi']['profit'], 0.01);
        $this->assertSame('SURPLUS', $result['data']['akuntansi']['profit_status']);

        // Verifikasi data inventory
        $this->assertSame(1, $result['data']['inventory']['stok_kritis']);
        $this->assertGreaterThan(0, $result['data']['inventory']['nilai_persediaan']);
    }

    // =========================================================================
    // Test: queryAkuntansiHrm mengembalikan data yang akurat
    //
    // Validates: Requirements 3.1, 3.2
    // =========================================================================

    public function testAkuntansiHrmReturnsAccurateData(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        // Seed transaksi
        Transaction::withoutGlobalScopes()->create([
            'tenant_id'   => $tenant->id,
            'user_id'     => $user->id,
            'number'      => 'TRX-' . uniqid(),
            'type'        => 'income',
            'date'        => now()->startOfMonth()->addDays(1),
            'amount'      => 10000000,
            'description' => 'Pendapatan',
        ]);

        // Seed 3 karyawan aktif
        for ($i = 0; $i < 3; $i++) {
            Employee::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name'      => 'Karyawan ' . ($i + 1),
                'status'    => 'active',
                'position'  => 'Staff',
            ]);
        }

        $service = new CrossModuleQueryService($tenant->id, ['accounting', 'hrm']);
        $result  = $service->queryAkuntansiHrm(['period' => 'this_month']);

        $this->assertSame('success', $result['status']);
        $this->assertArrayHasKey('akuntansi', $result['data']);
        $this->assertArrayHasKey('hrm', $result['data']);
        $this->assertEmpty($result['unavailable_modules']);

        $this->assertSame(3, $result['data']['hrm']['karyawan_aktif']);

        // Verifikasi korelasi pendapatan per karyawan
        $this->assertArrayHasKey('pendapatan_per_karyawan', $result['correlation']);
    }

    // =========================================================================
    // Test: queryHrmPayrollAbsensi mengembalikan data yang akurat
    //
    // Validates: Requirements 3.1, 3.2
    // =========================================================================

    public function testHrmPayrollAbsensiReturnsAccurateData(): void
    {
        $tenant = $this->createTenant();

        // Seed 2 karyawan aktif
        $employees = [];
        for ($i = 0; $i < 2; $i++) {
            $employees[] = Employee::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name'      => 'Karyawan ' . ($i + 1),
                'status'    => 'active',
                'position'  => 'Staff',
            ]);
        }

        // Seed payroll run bulan ini
        $period = now()->format('Y-m');
        PayrollRun::withoutGlobalScopes()->create([
            'tenant_id'        => $tenant->id,
            'period'           => $period,
            'status'           => 'draft',
            'total_gross'      => 10000000,
            'total_deductions' => 1000000,
            'total_net'        => 9000000,
        ]);

        // Seed absensi bulan ini
        foreach ($employees as $emp) {
            Attendance::withoutGlobalScopes()->create([
                'tenant_id'   => $tenant->id,
                'employee_id' => $emp->id,
                'date'        => now()->startOfMonth()->addDays(1),
                'status'      => 'present',
            ]);
        }
        Attendance::withoutGlobalScopes()->create([
            'tenant_id'   => $tenant->id,
            'employee_id' => $employees[0]->id,
            'date'        => now()->startOfMonth()->addDays(2),
            'status'      => 'absent',
        ]);

        $service = new CrossModuleQueryService($tenant->id, ['hrm', 'payroll']);
        $result  = $service->queryHrmPayrollAbsensi(['period' => $period]);

        $this->assertSame('success', $result['status']);
        $this->assertArrayHasKey('hrm', $result['data']);
        $this->assertArrayHasKey('payroll', $result['data']);
        $this->assertArrayHasKey('absensi', $result['data']);

        $this->assertSame(2, $result['data']['hrm']['karyawan_aktif']);
        $this->assertEqualsWithDelta(9000000, $result['data']['payroll']['total_gaji_bersih'], 0.01);
        $this->assertSame(2, $result['data']['absensi']['total_hadir']);
        $this->assertSame(1, $result['data']['absensi']['total_absen']);

        // Verifikasi korelasi
        $this->assertArrayHasKey('rata_gaji_per_karyawan', $result['correlation']);
        $this->assertArrayHasKey('tingkat_kehadiran', $result['correlation']);
    }

    // =========================================================================
    // Test: queryProjectKeuangan mengembalikan data yang akurat
    //
    // Validates: Requirements 3.1, 3.2
    // =========================================================================

    public function testProjectKeuanganReturnsAccurateData(): void
    {
        $tenant   = $this->createTenant();
        $user     = $this->createAdminUser($tenant);
        $customer = $this->createCustomer($tenant->id);

        // Seed 2 proyek
        Project::withoutGlobalScopes()->create([
            'tenant_id'   => $tenant->id,
            'user_id'     => $user->id,
            'customer_id' => $customer->id,
            'number'      => 'PRJ-001',
            'name'        => 'Proyek A',
            'status'      => 'active',
            'budget'      => 50000000,
            'actual_cost' => 30000000,
            'progress'    => 60,
        ]);
        Project::withoutGlobalScopes()->create([
            'tenant_id'   => $tenant->id,
            'user_id'     => $user->id,
            'customer_id' => $customer->id,
            'number'      => 'PRJ-002',
            'name'        => 'Proyek B',
            'status'      => 'planning',
            'budget'      => 20000000,
            'actual_cost' => 0,
            'progress'    => 0,
        ]);

        // Seed transaksi keuangan
        Transaction::withoutGlobalScopes()->create([
            'tenant_id'   => $tenant->id,
            'user_id'     => $user->id,
            'number'      => 'TRX-' . uniqid(),
            'type'        => 'income',
            'date'        => now()->startOfMonth()->addDays(1),
            'amount'      => 15000000,
            'description' => 'Pendapatan proyek',
        ]);

        $service = new CrossModuleQueryService($tenant->id, ['project', 'accounting']);
        $result  = $service->queryProjectKeuangan([]);

        $this->assertSame('success', $result['status']);
        $this->assertArrayHasKey('project', $result['data']);
        $this->assertArrayHasKey('keuangan', $result['data']);
        $this->assertEmpty($result['unavailable_modules']);

        $this->assertSame(2, $result['data']['project']['total_proyek']);
        $this->assertEqualsWithDelta(70000000, $result['data']['project']['total_budget'], 0.01);
        $this->assertEqualsWithDelta(30000000, $result['data']['project']['total_realisasi'], 0.01);
        $this->assertEqualsWithDelta(40000000, $result['data']['project']['variance'], 0.01);

        // Verifikasi korelasi sisa budget
        $this->assertArrayHasKey('sisa_budget', $result['correlation']);
    }

    // =========================================================================
    // Test: partial results ketika modul tidak aktif
    //
    // Validates: Requirements 3.5
    // =========================================================================

    public function testPartialResultsWhenModuleInactive(): void
    {
        $tenant = $this->createTenant();

        // Hanya HRM aktif, payroll tidak aktif
        $service = new CrossModuleQueryService($tenant->id, ['hrm']);
        $result  = $service->queryHrmPayrollAbsensi([]);

        $this->assertSame('success', $result['status']);
        $this->assertArrayHasKey('hrm', $result['data']);
        $this->assertArrayNotHasKey('payroll', $result['data']);
        $this->assertContains('payroll', $result['unavailable_modules']);
        $this->assertTrue($result['partial'] ?? false);
        $this->assertStringContainsString('payroll', $result['message']);
    }

    // =========================================================================
    // Test: queryPenjualanCrmInventory dengan data lengkap
    //
    // Validates: Requirements 3.1, 3.2, 3.3
    // =========================================================================

    public function testPenjualanCrmInventoryReturnsAccurateData(): void
    {
        $tenant    = $this->createTenant();
        $user      = $this->createAdminUser($tenant);
        $customer  = $this->createCustomer($tenant->id);
        $warehouse = $this->createWarehouse($tenant->id);

        // Seed sales orders
        SalesOrder::withoutGlobalScopes()->create([
            'tenant_id'   => $tenant->id,
            'customer_id' => $customer->id,
            'user_id'     => $user->id,
            'number'      => 'SO-' . uniqid(),
            'status'      => 'confirmed',
            'date'        => now()->startOfMonth()->addDays(1),
            'total'       => 3000000,
            'subtotal'    => 3000000,
            'discount'    => 0,
            'tax'         => 0,
        ]);

        // Seed CRM leads
        CrmLead::withoutGlobalScopes()->create([
            'tenant_id'       => $tenant->id,
            'name'            => 'Lead Test',
            'stage'           => 'qualified',
            'estimated_value' => 5000000,
            'probability'     => 60,
        ]);

        // Seed produk dengan stok kritis
        $product = $this->createProduct($tenant->id, ['stock_min' => 20]);
        $this->setStock($product->id, $warehouse->id, 5);

        $service = new CrossModuleQueryService($tenant->id, ['sales', 'crm', 'inventory']);
        $result  = $service->queryPenjualanCrmInventory(['period' => 'this_month']);

        $this->assertSame('success', $result['status']);
        $this->assertArrayHasKey('penjualan', $result['data']);
        $this->assertArrayHasKey('crm', $result['data']);
        $this->assertArrayHasKey('inventory', $result['data']);
        $this->assertEmpty($result['unavailable_modules']);

        $this->assertSame(1, $result['data']['crm']['total_leads']);
        $this->assertSame(1, $result['data']['inventory']['stok_kritis']);

        // Verifikasi peringatan stok di korelasi
        $this->assertArrayHasKey('peringatan_stok', $result['correlation']);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function seedSalesData(int $tenantId): void
    {
        $user     = \App\Models\User::create([
            'tenant_id'         => $tenantId,
            'name'              => 'User ' . uniqid(),
            'email'             => 'user-' . uniqid() . '@test.com',
            'password'          => bcrypt('password'),
            'role'              => 'staff',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);
        $customer = $this->createCustomer($tenantId);

        for ($i = 0; $i < 3; $i++) {
            SalesOrder::withoutGlobalScopes()->create([
                'tenant_id'   => $tenantId,
                'customer_id' => $customer->id,
                'user_id'     => $user->id,
                'number'      => 'SO-INT-' . uniqid(),
                'status'      => 'confirmed',
                'date'        => now()->startOfMonth()->addDays($i),
                'total'       => 1000000,
                'subtotal'    => 1000000,
                'discount'    => 0,
                'tax'         => 0,
            ]);
        }
    }

    private function seedCrmData(int $tenantId): void
    {
        for ($i = 0; $i < 3; $i++) {
            CrmLead::withoutGlobalScopes()->create([
                'tenant_id'       => $tenantId,
                'name'            => 'Lead ' . ($i + 1),
                'stage'           => 'qualified',
                'estimated_value' => 2000000,
                'probability'     => 50,
            ]);
        }
    }

    private function seedInventoryData(int $tenantId, int $warehouseId): void
    {
        for ($i = 0; $i < 3; $i++) {
            $product = $this->createProduct($tenantId, ['stock_min' => 10, 'price_buy' => 50000]);
            $this->setStock($product->id, $warehouseId, 3);
        }
    }

    private function seedHrmData(int $tenantId): void
    {
        for ($i = 0; $i < 5; $i++) {
            Employee::withoutGlobalScopes()->create([
                'tenant_id' => $tenantId,
                'name'      => 'Karyawan ' . ($i + 1),
                'status'    => 'active',
                'position'  => 'Staff',
            ]);
        }
    }

    private function seedPayrollData(int $tenantId): void
    {
        PayrollRun::withoutGlobalScopes()->create([
            'tenant_id'        => $tenantId,
            'period'           => now()->format('Y-m'),
            'status'           => 'draft',
            'total_gross'      => 25000000,
            'total_deductions' => 2500000,
            'total_net'        => 22500000,
        ]);
    }

    private function seedAttendanceData(int $tenantId): void
    {
        $employees = Employee::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->get();

        foreach ($employees as $emp) {
            Attendance::withoutGlobalScopes()->create([
                'tenant_id'   => $tenantId,
                'employee_id' => $emp->id,
                'date'        => now()->startOfMonth()->addDays(1),
                'status'      => 'present',
            ]);
        }
    }

    private function seedProjectData(int $tenantId, int $userId, int $customerId): void
    {
        for ($i = 0; $i < 3; $i++) {
            Project::withoutGlobalScopes()->create([
                'tenant_id'   => $tenantId,
                'user_id'     => $userId,
                'customer_id' => $customerId,
                'number'      => 'PRJ-INT-' . uniqid(),
                'name'        => 'Proyek ' . ($i + 1),
                'status'      => 'active',
                'budget'      => 10000000,
                'actual_cost' => 5000000,
                'progress'    => 50,
            ]);
        }
    }

    private function seedTransactionData(int $tenantId, int $userId): void
    {
        Transaction::withoutGlobalScopes()->create([
            'tenant_id'   => $tenantId,
            'user_id'     => $userId,
            'number'      => 'TRX-INT-' . uniqid(),
            'type'        => 'income',
            'date'        => now()->startOfMonth()->addDays(1),
            'amount'      => 8000000,
            'description' => 'Pendapatan proyek',
        ]);
    }
}
