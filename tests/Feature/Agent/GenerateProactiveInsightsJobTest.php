<?php

namespace Tests\Feature\Agent;

use App\Models\Employee;
use App\Models\Invoice;
use App\Models\ProactiveInsight;
use App\Models\SalesOrder;
use App\Services\Agent\ProactiveInsightEngine;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Integration Test: Scheduled Insight Generation
 *
 * Verifikasi bahwa ProactiveInsightEngine::analyze() menghasilkan
 * ProactiveInsight records di database ketika kondisi bisnis terpenuhi.
 *
 * Validates: Requirements 4.1
 */
class GenerateProactiveInsightsJobTest extends TestCase
{
    private ProactiveInsightEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new ProactiveInsightEngine();
    }

    /**
     * Buat invoice overdue dengan SalesOrder yang valid untuk memenuhi FK constraint.
     */
    private function createOverdueInvoice(int $tenantId, int $customerId, array $attrs = []): Invoice
    {
        $user = \App\Models\User::where('tenant_id', $tenantId)->first()
            ?? $this->createAdminUser(\App\Models\Tenant::find($tenantId));

        $so = SalesOrder::create([
            'tenant_id'   => $tenantId,
            'customer_id' => $customerId,
            'user_id'     => $user->id,
            'number'      => 'SO-TEST-' . uniqid(),
            'status'      => 'confirmed',
            'date'        => now()->subDays(30)->toDateString(),
            'subtotal'    => $attrs['total_amount'] ?? 5_000_000,
            'total'       => $attrs['total_amount'] ?? 5_000_000,
        ]);

        return Invoice::create(array_merge([
            'tenant_id'        => $tenantId,
            'customer_id'      => $customerId,
            'sales_order_id'   => $so->id,
            'number'           => 'INV-TEST-' . uniqid(),
            'due_date'         => now()->subDays(10)->toDateString(),
            'status'           => 'unpaid',
            'subtotal_amount'  => 5_000_000,
            'total_amount'     => 5_000_000,
            'remaining_amount' => 5_000_000,
        ], $attrs));
    }

    // =========================================================================
    // Test: analyze() menghasilkan insight untuk produk dengan stok rendah
    //
    // Validates: Requirements 4.1, 4.2
    // =========================================================================

    public function testAnalyzeGeneratesLowStockInsightWhenConditionIsMet(): void
    {
        $tenant    = $this->createTenant();
        $warehouse = $this->createWarehouse($tenant->id);

        // Buat produk dengan stock_min = 10, stok aktual = 2 (di bawah minimum)
        $product = $this->createProduct($tenant->id, [
            'stock_min' => 10,
            'is_active' => true,
        ]);
        $this->setStock($product->id, $warehouse->id, 2);

        // Jalankan analisis langsung (simulasi scheduled job)
        $insights = $this->engine->analyze($tenant->id);

        // Harus ada minimal satu insight yang dihasilkan
        $this->assertNotEmpty($insights, 'analyze() harus menghasilkan insight untuk kondisi stok rendah');

        // Verifikasi record tersimpan di database
        $this->assertDatabaseHas('proactive_insights', [
            'tenant_id'      => $tenant->id,
            'condition_type' => 'low_stock',
        ]);

        // Verifikasi struktur insight yang dihasilkan
        $lowStockInsight = collect($insights)->firstWhere('condition_type', 'low_stock');
        $this->assertNotNull($lowStockInsight, 'Harus ada insight dengan condition_type low_stock');
        $this->assertNotEmpty($lowStockInsight->title);
        $this->assertNotEmpty($lowStockInsight->description);
        $this->assertNotEmpty($lowStockInsight->business_impact);
        $this->assertNotEmpty($lowStockInsight->recommendations);
    }

    // =========================================================================
    // Test: analyze() menghasilkan insight untuk piutang jatuh tempo
    //
    // Validates: Requirements 4.1, 4.2
    // =========================================================================

    public function testAnalyzeGeneratesOverdueArInsightWhenConditionIsMet(): void
    {
        $tenant   = $this->createTenant();
        $customer = $this->createCustomer($tenant->id);

        // Buat invoice yang sudah melewati jatuh tempo > 7 hari
        $this->createOverdueInvoice($tenant->id, $customer->id);

        $insights = $this->engine->analyze($tenant->id);

        $this->assertNotEmpty($insights, 'analyze() harus menghasilkan insight untuk piutang jatuh tempo');

        $this->assertDatabaseHas('proactive_insights', [
            'tenant_id'      => $tenant->id,
            'condition_type' => 'overdue_ar',
        ]);
    }

    // =========================================================================
    // Test: analyze() tidak menghasilkan insight jika tidak ada kondisi yang terpenuhi
    //
    // Validates: Requirements 4.1
    // =========================================================================

    public function testAnalyzeReturnsEmptyWhenNoConditionsAreMet(): void
    {
        $tenant = $this->createTenant();

        // Tenant baru tanpa data apapun — tidak ada kondisi yang terpenuhi
        $insights = $this->engine->analyze($tenant->id);

        $this->assertEmpty($insights, 'analyze() tidak boleh menghasilkan insight jika tidak ada kondisi yang terpenuhi');

        $this->assertDatabaseMissing('proactive_insights', [
            'tenant_id' => $tenant->id,
        ]);
    }

    // =========================================================================
    // Test: analyze() menghasilkan multiple insights untuk multiple kondisi
    //
    // Validates: Requirements 4.1
    // =========================================================================

    public function testAnalyzeGeneratesMultipleInsightsForMultipleConditions(): void
    {
        $tenant    = $this->createTenant();
        $warehouse = $this->createWarehouse($tenant->id);
        $customer  = $this->createCustomer($tenant->id);

        // Kondisi 1: stok rendah
        $product = $this->createProduct($tenant->id, [
            'stock_min' => 20,
            'is_active' => true,
        ]);
        $this->setStock($product->id, $warehouse->id, 1);

        // Kondisi 2: piutang jatuh tempo
        $this->createOverdueInvoice($tenant->id, $customer->id, [
            'total_amount'     => 15_000_000,
            'subtotal_amount'  => 15_000_000,
            'remaining_amount' => 15_000_000,
            'due_date'         => now()->subDays(8)->toDateString(),
        ]);

        $insights = $this->engine->analyze($tenant->id);

        $this->assertGreaterThanOrEqual(2, count($insights),
            'analyze() harus menghasilkan minimal 2 insights untuk 2 kondisi yang terpenuhi');

        $conditionTypes = collect($insights)->pluck('condition_type')->toArray();
        $this->assertContains('low_stock', $conditionTypes);
        $this->assertContains('overdue_ar', $conditionTypes);
    }

    // =========================================================================
    // Test: analyze() di-scope ke tenant yang benar (isolasi tenant)
    //
    // Validates: Requirements 4.1, 9.1
    // =========================================================================

    public function testAnalyzeOnlyGeneratesInsightsForSpecifiedTenant(): void
    {
        $tenantA   = $this->createTenant(['name' => 'Tenant A ' . uniqid()]);
        $tenantB   = $this->createTenant(['name' => 'Tenant B ' . uniqid()]);
        $warehouse = $this->createWarehouse($tenantA->id);

        // Hanya tenant A yang memiliki kondisi stok rendah
        $product = $this->createProduct($tenantA->id, [
            'stock_min' => 10,
            'is_active' => true,
        ]);
        $this->setStock($product->id, $warehouse->id, 0);

        // Jalankan analisis untuk tenant B (tidak ada kondisi)
        $insightsB = $this->engine->analyze($tenantB->id);

        $this->assertEmpty($insightsB,
            'analyze() untuk tenant B tidak boleh menghasilkan insight dari kondisi tenant A');

        $this->assertDatabaseMissing('proactive_insights', [
            'tenant_id' => $tenantB->id,
        ]);

        // Jalankan analisis untuk tenant A (ada kondisi)
        $insightsA = $this->engine->analyze($tenantA->id);

        $this->assertNotEmpty($insightsA,
            'analyze() untuk tenant A harus menghasilkan insight');

        $this->assertDatabaseHas('proactive_insights', [
            'tenant_id' => $tenantA->id,
        ]);
    }

    // =========================================================================
    // Test: analyze() tidak menghasilkan insight duplikat dalam window suppression
    //
    // Validates: Requirements 4.1, 4.5
    // =========================================================================

    public function testAnalyzeDoesNotCreateDuplicateInsightsWithinSuppressionWindow(): void
    {
        $tenant    = $this->createTenant();
        $warehouse = $this->createWarehouse($tenant->id);

        $product = $this->createProduct($tenant->id, [
            'stock_min' => 10,
            'is_active' => true,
        ]);
        $this->setStock($product->id, $warehouse->id, 2);

        // Jalankan analisis pertama kali
        $firstRun = $this->engine->analyze($tenant->id);
        $this->assertNotEmpty($firstRun);

        $countAfterFirst = ProactiveInsight::where('tenant_id', $tenant->id)
            ->where('condition_type', 'low_stock')
            ->count();

        // Jalankan analisis kedua kali (kondisi sama, hash sama)
        $secondRun = $this->engine->analyze($tenant->id);

        $countAfterSecond = ProactiveInsight::where('tenant_id', $tenant->id)
            ->where('condition_type', 'low_stock')
            ->count();

        $this->assertEquals($countAfterFirst, $countAfterSecond,
            'analyze() tidak boleh membuat insight duplikat untuk kondisi yang sama dalam window suppression');
    }

    // =========================================================================
    // Test: analyze() menghasilkan insight untuk kontrak karyawan yang akan berakhir
    //
    // Validates: Requirements 4.1, 4.2
    // =========================================================================

    public function testAnalyzeGeneratesContractExpiryInsightWhenConditionIsMet(): void
    {
        $tenant = $this->createTenant();

        // Buat karyawan dengan kontrak berakhir dalam 15 hari
        Employee::create([
            'tenant_id'   => $tenant->id,
            'name'        => 'Karyawan Test',
            'employee_id' => 'EMP-' . uniqid(),
            'status'      => 'active',
            'resign_date' => now()->addDays(15)->toDateString(),
            'join_date'   => now()->subYear()->toDateString(),
        ]);

        $insights = $this->engine->analyze($tenant->id);

        $contractInsight = collect($insights)->firstWhere('condition_type', 'contract_expiry');
        $this->assertNotNull($contractInsight,
            'analyze() harus menghasilkan insight contract_expiry untuk karyawan yang kontraknya akan berakhir');

        $this->assertDatabaseHas('proactive_insights', [
            'tenant_id'      => $tenant->id,
            'condition_type' => 'contract_expiry',
        ]);
    }
}
