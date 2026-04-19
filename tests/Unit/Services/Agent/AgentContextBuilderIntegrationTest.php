<?php

namespace Tests\Unit\Services\Agent;

use App\DTOs\Agent\ErpContext;
use App\Models\AccountingPeriod;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use App\Models\Warehouse;
use App\Services\Agent\AgentContextBuilder;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Integration Tests for AgentContextBuilder.
 *
 * Feature: erp-ai-agent
 *
 * Verifikasi:
 * - build() selesai dalam < 3 detik dengan data tenant nyata
 * - KPI values akurat sesuai data yang di-seed
 * - refresh() memperbarui data modul yang relevan
 * - Partial context dikembalikan jika ada field yang tidak tersedia
 *
 * Validates: Requirements 2.1, 2.2, 2.4
 */
class AgentContextBuilderIntegrationTest extends TestCase
{
    private AgentContextBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new AgentContextBuilder();
    }

    // =========================================================================
    // Test: build() selesai dalam < 3 detik
    //
    // Validates: Requirements 2.2
    // =========================================================================

    public function testBuildCompletesWithinThreeSeconds(): void
    {
        $tenant    = $this->createTenant();
        $warehouse = $this->createWarehouse($tenant->id);

        // Seed data yang representatif
        $this->seedRealisticData($tenant->id, $warehouse->id);

        $start   = microtime(true);
        $context = $this->builder->build($tenant->id, ['accounting', 'inventory', 'hrm', 'sales']);
        $elapsed = microtime(true) - $start;

        $this->assertLessThan(
            3.0,
            $elapsed,
            "build() harus selesai dalam < 3 detik, tapi membutuhkan {$elapsed} detik"
        );

        $this->assertInstanceOf(ErpContext::class, $context);
    }

    // =========================================================================
    // Test: KPI revenue akurat
    //
    // Validates: Requirements 2.1
    // =========================================================================

    public function testRevenueKpiIsAccurate(): void
    {
        $tenant   = $this->createTenant();
        $user     = $this->createAdminUser($tenant);
        $customer = $this->createCustomer($tenant->id);

        // Buat 3 sales orders bulan ini dengan total yang diketahui
        $expectedRevenue = 0.0;
        $statuses        = ['confirmed', 'processing', 'completed'];

        foreach ($statuses as $status) {
            $total = 500000.0;
            SalesOrder::withoutGlobalScopes()->create([
                'tenant_id'   => $tenant->id,
                'customer_id' => $customer->id,
                'user_id'     => $user->id,
                'number'      => 'SO-' . uniqid(),
                'status'      => $status,
                'date'        => now()->startOfMonth()->addDays(2),
                'total'       => $total,
                'subtotal'    => $total,
                'discount'    => 0,
                'tax'         => 0,
            ]);
            $expectedRevenue += $total;
        }

        // Buat 1 sales order bulan lalu (tidak boleh masuk hitungan)
        SalesOrder::withoutGlobalScopes()->create([
            'tenant_id'   => $tenant->id,
            'customer_id' => $customer->id,
            'user_id'     => $user->id,
            'number'      => 'SO-LAST-' . uniqid(),
            'status'      => 'completed',
            'date'        => now()->subMonth()->startOfMonth(),
            'total'       => 999999.0,
            'subtotal'    => 999999.0,
            'discount'    => 0,
            'tax'         => 0,
        ]);

        $context = $this->builder->build($tenant->id, ['sales']);

        $this->assertEqualsWithDelta(
            $expectedRevenue,
            $context->kpiSummary['revenue'],
            0.01,
            'Revenue KPI harus sesuai dengan total sales orders bulan ini'
        );
    }

    // =========================================================================
    // Test: KPI active_employees akurat
    //
    // Validates: Requirements 2.1
    // =========================================================================

    public function testActiveEmployeesKpiIsAccurate(): void
    {
        $tenant = $this->createTenant();

        // Buat 4 karyawan aktif
        for ($i = 0; $i < 4; $i++) {
            Employee::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name'      => 'Karyawan Aktif ' . ($i + 1),
                'status'    => 'active',
                'position'  => 'Staff',
            ]);
        }

        // Buat 2 karyawan tidak aktif (tidak boleh masuk hitungan)
        for ($i = 0; $i < 2; $i++) {
            Employee::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name'      => 'Karyawan Resign ' . ($i + 1),
                'status'    => 'resigned',
                'position'  => 'Staff',
            ]);
        }

        $context = $this->builder->build($tenant->id, ['hrm']);

        $this->assertSame(
            4,
            $context->kpiSummary['active_employees'],
            'active_employees KPI harus menghitung hanya karyawan dengan status active'
        );
    }

    // =========================================================================
    // Test: KPI overdue_ar akurat
    //
    // Validates: Requirements 2.1
    // =========================================================================

    public function testOverdueArKpiIsAccurate(): void
    {
        $tenant   = $this->createTenant();
        $user     = $this->createAdminUser($tenant);
        $customer = $this->createCustomer($tenant->id);

        // Buat sales order dummy untuk foreign key
        $so = SalesOrder::withoutGlobalScopes()->create([
            'tenant_id'   => $tenant->id,
            'customer_id' => $customer->id,
            'user_id'     => $user->id,
            'number'      => 'SO-AR-' . uniqid(),
            'status'      => 'completed',
            'date'        => now()->subMonth(),
            'total'       => 2000000,
            'subtotal'    => 2000000,
            'discount'    => 0,
            'tax'         => 0,
        ]);

        // Invoice jatuh tempo (overdue)
        $overdueAmount = 750000.0;
        Invoice::withoutGlobalScopes()->create([
            'tenant_id'        => $tenant->id,
            'sales_order_id'   => $so->id,
            'customer_id'      => $customer->id,
            'number'           => 'INV-OVERDUE-' . uniqid(),
            'status'           => 'unpaid',
            'due_date'         => now()->subDays(10),
            'total_amount'     => $overdueAmount,
            'paid_amount'      => 0,
            'remaining_amount' => $overdueAmount,
        ]);

        // Invoice partial overdue
        $partialRemaining = 250000.0;
        Invoice::withoutGlobalScopes()->create([
            'tenant_id'        => $tenant->id,
            'sales_order_id'   => $so->id,
            'customer_id'      => $customer->id,
            'number'           => 'INV-PARTIAL-' . uniqid(),
            'status'           => 'partial',
            'due_date'         => now()->subDays(5),
            'total_amount'     => 500000.0,
            'paid_amount'      => 250000.0,
            'remaining_amount' => $partialRemaining,
        ]);

        // Invoice belum jatuh tempo (tidak boleh masuk hitungan)
        Invoice::withoutGlobalScopes()->create([
            'tenant_id'        => $tenant->id,
            'sales_order_id'   => $so->id,
            'customer_id'      => $customer->id,
            'number'           => 'INV-CURRENT-' . uniqid(),
            'status'           => 'unpaid',
            'due_date'         => now()->addDays(10),
            'total_amount'     => 300000.0,
            'paid_amount'      => 0,
            'remaining_amount' => 300000.0,
        ]);

        // Invoice sudah lunas (tidak boleh masuk hitungan)
        Invoice::withoutGlobalScopes()->create([
            'tenant_id'        => $tenant->id,
            'sales_order_id'   => $so->id,
            'customer_id'      => $customer->id,
            'number'           => 'INV-PAID-' . uniqid(),
            'status'           => 'paid',
            'due_date'         => now()->subDays(3),
            'total_amount'     => 200000.0,
            'paid_amount'      => 200000.0,
            'remaining_amount' => 0,
        ]);

        $context = $this->builder->build($tenant->id, ['accounting']);

        $expectedOverdue = $overdueAmount + $partialRemaining;
        $this->assertEqualsWithDelta(
            $expectedOverdue,
            $context->kpiSummary['overdue_ar'],
            0.01,
            'overdue_ar KPI harus menjumlahkan remaining_amount invoice yang sudah jatuh tempo'
        );
    }

    // =========================================================================
    // Test: KPI critical_stock akurat
    //
    // Validates: Requirements 2.1
    // =========================================================================

    public function testCriticalStockKpiIsAccurate(): void
    {
        $tenant    = $this->createTenant();
        $warehouse = $this->createWarehouse($tenant->id);

        // Produk dengan stok di bawah minimum (kritis)
        $productCritical1 = $this->createProduct($tenant->id, ['stock_min' => 10]);
        $this->setStock($productCritical1->id, $warehouse->id, 3); // di bawah min

        $productCritical2 = $this->createProduct($tenant->id, ['stock_min' => 20]);
        $this->setStock($productCritical2->id, $warehouse->id, 5); // di bawah min

        // Produk dengan stok cukup (tidak kritis)
        $productOk = $this->createProduct($tenant->id, ['stock_min' => 10]);
        $this->setStock($productOk->id, $warehouse->id, 50); // di atas min

        // Produk tanpa stock_min (tidak dihitung — stock_min = 0)
        $productNoMin = $this->createProduct($tenant->id, ['stock_min' => 0]);
        $this->setStock($productNoMin->id, $warehouse->id, 0);

        $context = $this->builder->build($tenant->id, ['inventory']);

        $this->assertSame(
            2,
            $context->kpiSummary['critical_stock'],
            'critical_stock KPI harus menghitung produk dengan stok di bawah stock_min'
        );
    }

    // =========================================================================
    // Test: accounting period di-resolve dengan benar
    //
    // Validates: Requirements 2.1
    // =========================================================================

    public function testAccountingPeriodIsResolved(): void
    {
        $tenant = $this->createTenant();

        AccountingPeriod::withoutGlobalScopes()->create([
            'tenant_id'  => $tenant->id,
            'name'       => 'Periode Jan 2026',
            'start_date' => now()->startOfMonth(),
            'end_date'   => now()->endOfMonth(),
            'status'     => 'open',
        ]);

        $context = $this->builder->build($tenant->id, ['accounting']);

        $this->assertNotNull(
            $context->accountingPeriod,
            'accountingPeriod harus di-resolve jika ada periode yang aktif'
        );
        $this->assertStringContainsString(
            'Periode Jan 2026',
            $context->accountingPeriod
        );
    }

    // =========================================================================
    // Test: accounting period null jika tidak ada periode aktif
    //
    // Validates: Requirements 2.1
    // =========================================================================

    public function testAccountingPeriodIsNullWhenNoActivePeriod(): void
    {
        $tenant  = $this->createTenant();
        $context = $this->builder->build($tenant->id, ['accounting']);

        $this->assertNull(
            $context->accountingPeriod,
            'accountingPeriod harus null jika tidak ada periode akuntansi yang aktif'
        );
    }

    // =========================================================================
    // Test: refresh() memperbarui data modul yang relevan
    //
    // Validates: Requirements 2.4
    // =========================================================================

    public function testRefreshUpdatesRelevantModuleData(): void
    {
        $tenant = $this->createTenant();

        // Build context awal tanpa karyawan
        $initialContext = $this->builder->build($tenant->id, ['hrm']);
        $this->assertSame(0, $initialContext->kpiSummary['active_employees']);

        // Tambah karyawan baru
        Employee::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name'      => 'Karyawan Baru',
            'status'    => 'active',
            'position'  => 'Manager',
        ]);

        // Refresh modul hrm
        $refreshedContext = $this->builder->refresh($initialContext, 'hrm');

        $this->assertSame(
            1,
            $refreshedContext->kpiSummary['active_employees'],
            'refresh() harus memperbarui active_employees setelah karyawan baru ditambahkan'
        );

        // builtAt harus lebih baru dari context awal
        $this->assertTrue(
            $refreshedContext->builtAt->greaterThanOrEqualTo($initialContext->builtAt),
            'builtAt context yang di-refresh harus lebih baru atau sama dengan context awal'
        );
    }

    // =========================================================================
    // Test: refresh() mempertahankan data modul yang tidak di-refresh
    //
    // Validates: Requirements 2.4
    // =========================================================================

    public function testRefreshPreservesUnrelatedModuleData(): void
    {
        $tenant = $this->createTenant();

        // Buat karyawan aktif
        Employee::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name'      => 'Karyawan',
            'status'    => 'active',
            'position'  => 'Staff',
        ]);

        $initialContext = $this->builder->build($tenant->id, ['hrm', 'inventory']);

        // Refresh hanya modul inventory (bukan hrm)
        $refreshedContext = $this->builder->refresh($initialContext, 'inventory');

        // active_employees harus tetap sama (tidak di-refresh)
        $this->assertSame(
            $initialContext->kpiSummary['active_employees'],
            $refreshedContext->kpiSummary['active_employees'],
            'refresh() tidak boleh mengubah data modul yang tidak di-refresh'
        );

        // activeModules harus tetap sama
        $this->assertSame(
            $initialContext->activeModules,
            $refreshedContext->activeModules,
            'refresh() tidak boleh mengubah activeModules'
        );
    }

    // =========================================================================
    // Test: toSystemPrompt() menghasilkan string yang valid
    //
    // Validates: Requirements 2.3
    // =========================================================================

    public function testToSystemPromptGeneratesValidString(): void
    {
        $tenant  = $this->createTenant(['name' => 'PT Test Maju']);
        $context = $this->builder->build($tenant->id, ['accounting', 'inventory']);

        $prompt = $context->toSystemPrompt();

        $this->assertIsString($prompt);
        $this->assertNotEmpty($prompt);
        $this->assertStringContainsString((string) $tenant->id, $prompt);
    }

    // =========================================================================
    // Test: isStale() mendeteksi context yang sudah kadaluarsa
    //
    // Validates: Requirements 2.4
    // =========================================================================

    public function testIsStaleDetectsExpiredContext(): void
    {
        $tenant  = $this->createTenant();
        $context = $this->builder->build($tenant->id, []);

        // Context baru tidak boleh stale
        $this->assertFalse(
            $context->isStale(300),
            'Context yang baru dibuat tidak boleh stale'
        );

        // Simulasi context yang sudah lama (builtAt 10 menit lalu)
        $staleContext = new ErpContext(
            tenantId: $tenant->id,
            kpiSummary: $context->kpiSummary,
            activeModules: [],
            accountingPeriod: null,
            industrySkills: [],
            builtAt: Carbon::now()->subMinutes(10),
        );

        $this->assertTrue(
            $staleContext->isStale(300),
            'Context yang dibuat 10 menit lalu harus stale dengan maxAge 300 detik'
        );
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Seed data yang representatif untuk integration test.
     */
    private function seedRealisticData(int $tenantId, int $warehouseId): void
    {
        $user     = \App\Models\User::create([
            'tenant_id'         => $tenantId,
            'name'              => 'User Test',
            'email'             => 'user-' . uniqid() . '@test.com',
            'password'          => bcrypt('password'),
            'role'              => 'staff',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);
        $customer = $this->createCustomer($tenantId);

        // 5 sales orders bulan ini
        for ($i = 0; $i < 5; $i++) {
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

        // 10 karyawan aktif
        for ($i = 0; $i < 10; $i++) {
            Employee::withoutGlobalScopes()->create([
                'tenant_id' => $tenantId,
                'name'      => 'Karyawan ' . ($i + 1),
                'status'    => 'active',
                'position'  => 'Staff',
            ]);
        }

        // 3 produk dengan stok kritis
        for ($i = 0; $i < 3; $i++) {
            $product = $this->createProduct($tenantId, ['stock_min' => 20]);
            $this->setStock($product->id, $warehouseId, 5);
        }
    }
}
