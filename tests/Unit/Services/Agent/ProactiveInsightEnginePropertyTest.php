<?php

namespace Tests\Unit\Services\Agent;

use App\Models\Budget;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\ProactiveInsight;
use App\Models\SalesOrder;
use App\Models\User;
use App\Services\Agent\ProactiveInsightEngine;
use Tests\TestCase;

/**
 * Property-Based Tests for ProactiveInsightEngine.
 *
 * Feature: erp-ai-agent
 *
 * Property 8:  Proactive Insight Condition Trigger
 * Property 9:  Proactive Insight Structure Completeness
 * Property 10: Insight Suppression After Dismiss
 *
 * Validates: Requirements 4.2, 4.3, 4.5
 *
 * Note: These tests use fixed representative inputs instead of PBT generators
 * to avoid MySQL lock contention caused by many DB writes within a single
 * DatabaseTransactions wrapper. The properties are still verified across
 * multiple distinct scenarios via @dataProvider.
 */
class ProactiveInsightEnginePropertyTest extends TestCase
{
    private ProactiveInsightEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Auth::forgetGuards();
        \Illuminate\Support\Facades\Cache::flush();
        $this->engine = new ProactiveInsightEngine();
    }

    // =========================================================================
    // Property 8: Proactive Insight Condition Trigger
    // Feature: erp-ai-agent, Property 8: Proactive Insight Condition Trigger
    // Validates: Requirements 4.2
    // =========================================================================

    /** @dataProvider lowStockScenarios */
    #[\PHPUnit\Framework\Attributes\DataProvider('lowStockScenarios')]
    public function testLowStockTriggerGeneratesInsight(int $productCount, int $stockMin): void
    {
        $tenant    = $this->createTenant();
        $warehouse = $this->createWarehouse($tenant->id);

        for ($i = 0; $i < $productCount; $i++) {
            $product = $this->createProduct($tenant->id, ['stock_min' => $stockMin, 'is_active' => true]);
            $this->setStock($product->id, $warehouse->id, max(0, $stockMin - 1));
        }

        $insights = $this->engine->analyze($tenant->id);

        $lowStockInsights = array_filter($insights, fn($i) => $i->condition_type === 'low_stock');

        $this->assertNotEmpty(
            $lowStockInsights,
            "analyze() harus menghasilkan insight 'low_stock' untuk {$productCount} produk dengan stok di bawah minimum {$stockMin}"
        );
    }

    public static function lowStockScenarios(): array
    {
        return [
            'single product, low min' => [1, 5],
            'multiple products, high min' => [3, 50],
        ];
    }

    /** @dataProvider overdueArScenarios */
    #[\PHPUnit\Framework\Attributes\DataProvider('overdueArScenarios')]
    public function testOverdueArTriggerGeneratesInsight(int $invoiceCount, int $daysOverdue): void
    {
        $tenant   = $this->createTenant();
        $customer = $this->createCustomer($tenant->id);

        for ($i = 0; $i < $invoiceCount; $i++) {
            $salesOrder = $this->createSalesOrder($tenant->id, $customer->id);
            Invoice::withoutGlobalScopes()->create([
                'tenant_id'        => $tenant->id,
                'sales_order_id'   => $salesOrder->id,
                'customer_id'      => $customer->id,
                'number'           => 'INV-OD-' . uniqid(),
                'total_amount'     => 5_000_000,
                'paid_amount'      => 0,
                'remaining_amount' => 5_000_000,
                'status'           => 'unpaid',
                'due_date'         => now()->subDays($daysOverdue),
            ]);
        }

        $insights   = $this->engine->analyze($tenant->id);
        $arInsights = array_filter($insights, fn($i) => $i->condition_type === 'overdue_ar');

        $this->assertNotEmpty($arInsights,
            "analyze() harus menghasilkan insight 'overdue_ar' untuk {$invoiceCount} invoice jatuh tempo {$daysOverdue} hari");
    }

    public static function overdueArScenarios(): array
    {
        return [
            'one invoice, 8 days overdue'  => [1, 8],
            'two invoices, 30 days overdue' => [2, 30],
        ];
    }

    /** @dataProvider budgetExceededScenarios */
    #[\PHPUnit\Framework\Attributes\DataProvider('budgetExceededScenarios')]
    public function testBudgetExceededTriggerGeneratesInsight(int $budgetCount, int $usagePercent): void
    {
        $tenant = $this->createTenant();

        for ($i = 0; $i < $budgetCount; $i++) {
            $amount   = 10_000_000;
            $realized = (int) ($amount * $usagePercent / 100);
            Budget::withoutGlobalScopes()->create([
                'tenant_id'   => $tenant->id,
                'name'        => 'Anggaran Test ' . uniqid(),
                'department'  => 'Operasional',
                'period'      => now()->format('Y-m'),
                'period_type' => 'monthly',
                'amount'      => $amount,
                'realized'    => $realized,
                'status'      => 'active',
            ]);
        }

        $insights       = $this->engine->analyze($tenant->id);
        $budgetInsights = array_filter($insights, fn($i) => $i->condition_type === 'budget_exceeded');

        $this->assertNotEmpty($budgetInsights,
            "analyze() harus menghasilkan insight 'budget_exceeded' untuk {$budgetCount} anggaran dengan pemakaian {$usagePercent}%");
    }

    public static function budgetExceededScenarios(): array
    {
        return [
            'one budget at 90%'  => [1, 90],
            'two budgets at 100%' => [2, 100],
        ];
    }

    /** @dataProvider contractExpiryScenarios */
    #[\PHPUnit\Framework\Attributes\DataProvider('contractExpiryScenarios')]
    public function testContractExpiryTriggerGeneratesInsight(int $employeeCount, int $daysRemaining): void
    {
        $tenant = $this->createTenant();

        for ($i = 0; $i < $employeeCount; $i++) {
            Employee::withoutGlobalScopes()->create([
                'tenant_id'   => $tenant->id,
                'name'        => 'Karyawan Test ' . uniqid(),
                'status'      => 'active',
                'position'    => 'Staff',
                'resign_date' => now()->addDays($daysRemaining)->toDateString(),
            ]);
        }

        $insights         = $this->engine->analyze($tenant->id);
        $contractInsights = array_filter($insights, fn($i) => $i->condition_type === 'contract_expiry');

        $this->assertNotEmpty($contractInsights,
            "analyze() harus menghasilkan insight 'contract_expiry' untuk {$employeeCount} karyawan dengan kontrak berakhir dalam {$daysRemaining} hari");
    }

    public static function contractExpiryScenarios(): array
    {
        return [
            'one employee, 1 day remaining'  => [1, 1],
            'two employees, 29 days remaining' => [2, 29],
        ];
    }

    /** @dataProvider unpaidInvoiceScenarios */
    #[\PHPUnit\Framework\Attributes\DataProvider('unpaidInvoiceScenarios')]
    public function testUnpaidInvoiceTriggerGeneratesInsight(int $invoiceCount, int $invoiceAmount): void
    {
        $tenant   = $this->createTenant();
        $customer = $this->createCustomer($tenant->id);

        for ($i = 0; $i < $invoiceCount; $i++) {
            $salesOrder = $this->createSalesOrder($tenant->id, $customer->id);
            Invoice::withoutGlobalScopes()->create([
                'tenant_id'        => $tenant->id,
                'sales_order_id'   => $salesOrder->id,
                'customer_id'      => $customer->id,
                'number'           => 'INV-UP-' . uniqid(),
                'total_amount'     => $invoiceAmount,
                'paid_amount'      => 0,
                'remaining_amount' => $invoiceAmount,
                'status'           => 'unpaid',
                'due_date'         => now()->addDays(30),
            ]);
        }

        $insights       = $this->engine->analyze($tenant->id);
        $unpaidInsights = array_filter($insights, fn($i) => $i->condition_type === 'unpaid_invoice');

        $this->assertNotEmpty($unpaidInsights,
            "analyze() harus menghasilkan insight 'unpaid_invoice' untuk {$invoiceCount} invoice senilai Rp " . number_format($invoiceAmount));
    }

    public static function unpaidInvoiceScenarios(): array
    {
        return [
            'one invoice, 2M'  => [1, 2_000_000],
            'two invoices, 5M' => [2, 5_000_000],
        ];
    }

    // =========================================================================
    // Property 9: Proactive Insight Structure Completeness
    // Feature: erp-ai-agent, Property 9: Proactive Insight Structure Completeness
    // Validates: Requirements 4.3
    // =========================================================================

    /** @dataProvider allConditionTypes */
    #[\PHPUnit\Framework\Attributes\DataProvider('allConditionTypes')]
    public function testInsightStructureCompletenessForAllConditionTypes(string $conditionType): void
    {
        $tenant = $this->createTenant();
        $this->seedConditionData($tenant->id, $conditionType);

        $insights         = $this->engine->analyze($tenant->id);
        $matchingInsights = array_filter($insights, fn($i) => $i->condition_type === $conditionType);

        $this->assertNotEmpty($matchingInsights,
            "analyze() harus menghasilkan insight untuk kondisi '{$conditionType}'");

        foreach ($matchingInsights as $insight) {
            $this->assertNotEmpty($insight->title,
                "ProactiveInsight.title tidak boleh kosong untuk '{$conditionType}'");
            $this->assertNotEmpty($insight->description,
                "ProactiveInsight.description tidak boleh kosong untuk '{$conditionType}'");
            $this->assertNotEmpty($insight->business_impact,
                "ProactiveInsight.business_impact tidak boleh kosong untuk '{$conditionType}'");
            $this->assertIsArray($insight->recommendations,
                "ProactiveInsight.recommendations harus array untuk '{$conditionType}'");
            $this->assertGreaterThanOrEqual(1, count($insight->recommendations),
                "ProactiveInsight.recommendations harus minimal 1 elemen untuk '{$conditionType}'");
        }
    }

    public static function allConditionTypes(): array
    {
        return [
            'low_stock'       => ['low_stock'],
            'overdue_ar'      => ['overdue_ar'],
            'budget_exceeded' => ['budget_exceeded'],
            'contract_expiry' => ['contract_expiry'],
            'unpaid_invoice'  => ['unpaid_invoice'],
        ];
    }

    // =========================================================================
    // Property 10: Insight Suppression After Dismiss
    // Feature: erp-ai-agent, Property 10: Insight Suppression After Dismiss
    // Validates: Requirements 4.5
    // =========================================================================

    /** @dataProvider dismissReasons */
    #[\PHPUnit\Framework\Attributes\DataProvider('dismissReasons')]
    public function testInsightSuppressionAfterDismiss(string $dismissReason): void
    {
        $tenant  = $this->createTenant();
        $user    = $this->createAdminUser($tenant);

        $insight = ProactiveInsight::withoutGlobalScopes()->create([
            'tenant_id'        => $tenant->id,
            'condition_type'   => 'low_stock',
            'urgency'          => 'high',
            'title'            => 'Test Insight',
            'description'      => 'Deskripsi test',
            'business_impact'  => 'Dampak bisnis test',
            'recommendations'  => ['Rekomendasi 1'],
            'condition_data'   => ['test' => true],
            'condition_hash'   => md5('test-hash-' . uniqid()),
            'suppressed_until' => null,
        ]);

        $this->actingAs($user);
        $this->engine->dismiss($insight, $dismissReason);
        $insight->refresh();

        $this->assertNotNull($insight->suppressed_until,
            'suppressed_until harus di-set setelah dismiss');
        $this->assertGreaterThan(now()->addHours(23)->timestamp, $insight->suppressed_until->timestamp,
            'suppressed_until harus minimal 23 jam ke depan');
        $this->assertLessThanOrEqual(now()->addHours(25)->timestamp, $insight->suppressed_until->timestamp,
            'suppressed_until tidak boleh lebih dari 25 jam ke depan');

        $this->assertDatabaseHas('insight_reads', [
            'insight_id' => $insight->id,
            'user_id'    => $user->id,
        ]);

        $pending    = $this->engine->getPendingInsights($tenant->id, $user->id);
        $pendingIds = array_map(fn($i) => $i->id, $pending);

        $this->assertNotContains($insight->id, $pendingIds,
            "getPendingInsights() tidak boleh mengembalikan insight yang sudah di-dismiss dalam window 24 jam");
    }

    public static function dismissReasons(): array
    {
        return [
            'dismissed' => ['dismissed'],
            'handled'   => ['handled'],
        ];
    }

    public function testInsightWithSameHashNotDuplicatedWithinSuppressionWindow(): void
    {
        $tenant    = $this->createTenant();
        $warehouse = $this->createWarehouse($tenant->id);
        $product   = $this->createProduct($tenant->id, ['stock_min' => 10, 'is_active' => true]);
        $this->setStock($product->id, $warehouse->id, 5);

        $firstRun      = $this->engine->analyze($tenant->id);
        $firstLowStock = array_filter($firstRun, fn($i) => $i->condition_type === 'low_stock');
        $this->assertNotEmpty($firstLowStock, 'Run pertama harus menghasilkan insight low_stock');

        $secondRun      = $this->engine->analyze($tenant->id);
        $secondLowStock = array_filter($secondRun, fn($i) => $i->condition_type === 'low_stock');
        $this->assertEmpty($secondLowStock,
            'Run kedua dengan kondisi sama tidak boleh menghasilkan insight duplikat (dedup via condition_hash)');
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function seedConditionData(int $tenantId, string $conditionType): void
    {
        match ($conditionType) {
            'low_stock'       => $this->seedLowStock($tenantId),
            'overdue_ar'      => $this->seedOverdueAr($tenantId),
            'budget_exceeded' => $this->seedBudgetExceeded($tenantId),
            'contract_expiry' => $this->seedContractExpiry($tenantId),
            'unpaid_invoice'  => $this->seedUnpaidInvoice($tenantId),
        };
    }

    private function seedLowStock(int $tenantId): void
    {
        $warehouse = $this->createWarehouse($tenantId);
        $product   = $this->createProduct($tenantId, ['stock_min' => 20, 'is_active' => true]);
        $this->setStock($product->id, $warehouse->id, 5);
    }

    private function seedOverdueAr(int $tenantId): void
    {
        $customer   = $this->createCustomer($tenantId);
        $salesOrder = $this->createSalesOrder($tenantId, $customer->id);
        Invoice::withoutGlobalScopes()->create([
            'tenant_id'        => $tenantId,
            'sales_order_id'   => $salesOrder->id,
            'customer_id'      => $customer->id,
            'number'           => 'INV-OD-' . uniqid(),
            'total_amount'     => 10_000_000,
            'paid_amount'      => 0,
            'remaining_amount' => 10_000_000,
            'status'           => 'unpaid',
            'due_date'         => now()->subDays(10),
        ]);
    }

    private function seedBudgetExceeded(int $tenantId): void
    {
        Budget::withoutGlobalScopes()->create([
            'tenant_id'   => $tenantId,
            'name'        => 'Anggaran Operasional',
            'department'  => 'Operasional',
            'period'      => now()->format('Y-m'),
            'period_type' => 'monthly',
            'amount'      => 10_000_000,
            'realized'    => 9_500_000,
            'status'      => 'active',
        ]);
    }

    private function seedContractExpiry(int $tenantId): void
    {
        Employee::withoutGlobalScopes()->create([
            'tenant_id'   => $tenantId,
            'name'        => 'Karyawan Kontrak',
            'status'      => 'active',
            'position'    => 'Staff',
            'resign_date' => now()->addDays(15)->toDateString(),
        ]);
    }

    private function seedUnpaidInvoice(int $tenantId): void
    {
        $customer   = $this->createCustomer($tenantId);
        $salesOrder = $this->createSalesOrder($tenantId, $customer->id);
        Invoice::withoutGlobalScopes()->create([
            'tenant_id'        => $tenantId,
            'sales_order_id'   => $salesOrder->id,
            'customer_id'      => $customer->id,
            'number'           => 'INV-UP-' . uniqid(),
            'total_amount'     => 5_000_000,
            'paid_amount'      => 0,
            'remaining_amount' => 5_000_000,
            'status'           => 'unpaid',
            'due_date'         => now()->addDays(30),
        ]);
    }

    private function createSalesOrder(int $tenantId, int $customerId): SalesOrder
    {
        $user = User::withoutGlobalScopes()->where('tenant_id', $tenantId)->first()
            ?? User::create([
                'tenant_id'         => $tenantId,
                'name'              => 'User SO ' . uniqid(),
                'email'             => 'user-so-' . uniqid() . '@test.com',
                'password'          => bcrypt('password'),
                'role'              => 'staff',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]);

        return SalesOrder::withoutGlobalScopes()->create([
            'tenant_id'   => $tenantId,
            'customer_id' => $customerId,
            'user_id'     => $user->id,
            'number'      => 'SO-TEST-' . uniqid(),
            'status'      => 'confirmed',
            'date'        => now(),
            'total'       => 5_000_000,
            'subtotal'    => 5_000_000,
            'discount'    => 0,
            'tax'         => 0,
        ]);
    }
}
