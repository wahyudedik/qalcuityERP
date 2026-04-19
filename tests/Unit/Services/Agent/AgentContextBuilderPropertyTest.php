<?php

namespace Tests\Unit\Services\Agent;

use App\DTOs\Agent\ErpContext;
use App\Models\AccountingPeriod;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Models\Warehouse;
use App\Services\Agent\AgentContextBuilder;
use Carbon\Carbon;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Tests\TestCase;

/**
 * Property-Based Tests for AgentContextBuilder.
 *
 * Feature: erp-ai-agent
 *
 * Property 5: ERP Context Completeness
 * Property 6: Tenant Context Isolation
 *
 * Validates: Requirements 2.1, 2.5, 9.1
 */
class AgentContextBuilderPropertyTest extends TestCase
{
    use TestTrait;

    private AgentContextBuilder $builder;

    /** Semua kombinasi modul yang mungkin */
    private array $allModules = [
        'accounting', 'inventory', 'hrm', 'sales', 'project',
        'crm', 'payroll', 'purchase',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new AgentContextBuilder();
    }

    // =========================================================================
    // Property 5: ERP Context Completeness
    //
    // Untuk kombinasi modul aktif apapun, build() selalu menghasilkan ErpContext
    // dengan field tenantId, kpiSummary, activeModules, builtAt yang non-null.
    //
    // Feature: erp-ai-agent, Property 5: ERP Context Completeness
    // Validates: Requirements 2.1
    // =========================================================================

    #[ErisRepeat(repeat: 20)]
    public function testErpContextCompletenessForAnyModuleCombination(): void
    {
        $this->forAll(
            // Generate subset acak dari modul yang tersedia (0 hingga semua modul)
            Generators::map(
                function (int $bitmask) {
                    $selected = [];
                    foreach ($this->allModules as $i => $module) {
                        if ($bitmask & (1 << $i)) {
                            $selected[] = $module;
                        }
                    }
                    return $selected;
                },
                Generators::choose(0, (1 << count($this->allModules)) - 1)
            )
        )->then(function (array $activeModules) {
            $tenant = $this->createTenant();

            $context = $this->builder->build($tenant->id, $activeModules);

            // ── Assert: context adalah instance ErpContext ──
            $this->assertInstanceOf(
                ErpContext::class,
                $context,
                'build() harus mengembalikan instance ErpContext'
            );

            // ── Assert: tenantId non-null dan sesuai ──
            $this->assertNotNull(
                $context->tenantId,
                'ErpContext::tenantId tidak boleh null'
            );
            $this->assertSame(
                $tenant->id,
                $context->tenantId,
                'ErpContext::tenantId harus sesuai dengan tenant yang diberikan'
            );

            // ── Assert: kpiSummary non-null dan berupa array ──
            $this->assertNotNull(
                $context->kpiSummary,
                'ErpContext::kpiSummary tidak boleh null'
            );
            $this->assertIsArray(
                $context->kpiSummary,
                'ErpContext::kpiSummary harus berupa array'
            );

            // ── Assert: kpiSummary mengandung semua key yang diperlukan ──
            $requiredKpiKeys = ['revenue', 'critical_stock', 'overdue_ar', 'active_employees'];
            foreach ($requiredKpiKeys as $key) {
                $this->assertArrayHasKey(
                    $key,
                    $context->kpiSummary,
                    "ErpContext::kpiSummary harus mengandung key '{$key}'"
                );
            }

            // ── Assert: activeModules non-null dan sesuai ──
            $this->assertNotNull(
                $context->activeModules,
                'ErpContext::activeModules tidak boleh null'
            );
            $this->assertIsArray(
                $context->activeModules,
                'ErpContext::activeModules harus berupa array'
            );
            $this->assertSame(
                $activeModules,
                $context->activeModules,
                'ErpContext::activeModules harus sesuai dengan yang diberikan'
            );

            // ── Assert: builtAt non-null dan berupa Carbon ──
            $this->assertNotNull(
                $context->builtAt,
                'ErpContext::builtAt tidak boleh null'
            );
            $this->assertInstanceOf(
                Carbon::class,
                $context->builtAt,
                'ErpContext::builtAt harus berupa Carbon instance'
            );

            // ── Assert: builtAt adalah waktu yang baru saja dibuat (dalam 10 detik terakhir) ──
            $this->assertLessThanOrEqual(
                10,
                Carbon::now()->diffInSeconds($context->builtAt),
                'ErpContext::builtAt harus merupakan waktu yang baru saja dibuat'
            );
        });
    }

    // =========================================================================
    // Property 5 (edge case): Context completeness dengan modul kosong
    //
    // Bahkan dengan activeModules = [], build() harus tetap menghasilkan
    // ErpContext yang valid dengan semua field non-null.
    //
    // Feature: erp-ai-agent, Property 5: ERP Context Completeness
    // Validates: Requirements 2.1
    // =========================================================================

    public function testErpContextCompletenessWithEmptyModules(): void
    {
        $tenant  = $this->createTenant();
        $context = $this->builder->build($tenant->id, []);

        $this->assertInstanceOf(ErpContext::class, $context);
        $this->assertNotNull($context->tenantId);
        $this->assertNotNull($context->kpiSummary);
        $this->assertNotNull($context->activeModules);
        $this->assertNotNull($context->builtAt);
        $this->assertSame($tenant->id, $context->tenantId);
        $this->assertSame([], $context->activeModules);
    }

    // =========================================================================
    // Property 6: Tenant Context Isolation
    //
    // Untuk dua tenant berbeda, ErpContext masing-masing tidak mengandung
    // data dari tenant lain.
    //
    // Feature: erp-ai-agent, Property 6: Tenant Context Isolation
    // Validates: Requirements 2.5, 9.1
    // =========================================================================

    #[ErisRepeat(repeat: 10)]
    public function testTenantContextIsolation(): void
    {
        $this->forAll(
            // Jumlah sales orders untuk tenant A (0-5)
            Generators::choose(0, 5),
            // Jumlah employees untuk tenant A (0-5)
            Generators::choose(0, 5),
            // Jumlah sales orders untuk tenant B (0-5)
            Generators::choose(0, 5),
            // Jumlah employees untuk tenant B (0-5)
            Generators::choose(0, 5)
        )->then(function (
            int $salesA,
            int $employeesA,
            int $salesB,
            int $employeesB
        ) {
            $tenantA = $this->createTenant(['name' => 'Tenant A ' . uniqid()]);
            $tenantB = $this->createTenant(['name' => 'Tenant B ' . uniqid()]);

            // Seed data untuk tenant A
            $this->seedTenantData($tenantA->id, $salesA, $employeesA);

            // Seed data untuk tenant B
            $this->seedTenantData($tenantB->id, $salesB, $employeesB);

            $contextA = $this->builder->build($tenantA->id, ['accounting', 'hrm']);
            $contextB = $this->builder->build($tenantB->id, ['accounting', 'hrm']);

            // ── Assert: tenantId tidak tercampur ──
            $this->assertSame(
                $tenantA->id,
                $contextA->tenantId,
                'Context tenant A harus memiliki tenantId tenant A'
            );
            $this->assertSame(
                $tenantB->id,
                $contextB->tenantId,
                'Context tenant B harus memiliki tenantId tenant B'
            );
            $this->assertNotSame(
                $contextA->tenantId,
                $contextB->tenantId,
                'tenantId context A dan B tidak boleh sama'
            );

            // ── Assert: data KPI tidak tercampur antar tenant ──
            // Verifikasi active_employees sesuai dengan data masing-masing tenant
            // (ini adalah cara yang tepat untuk memverifikasi isolasi data)
            $this->assertSame(
                $employeesA,
                $contextA->kpiSummary['active_employees'],
                "active_employees tenant A harus {$employeesA}, bukan data tenant B"
            );
            $this->assertSame(
                $employeesB,
                $contextB->kpiSummary['active_employees'],
                "active_employees tenant B harus {$employeesB}, bukan data tenant A"
            );

            // Verifikasi revenue tidak tercampur: jika tenant A punya sales dan tenant B tidak,
            // revenue tenant B harus 0 (bukan revenue tenant A)
            if ($salesA > 0 && $salesB === 0) {
                $this->assertSame(
                    0.0,
                    $contextB->kpiSummary['revenue'],
                    'Revenue tenant B harus 0 karena tidak ada sales orders, bukan data dari tenant A'
                );
            }
            if ($salesB > 0 && $salesA === 0) {
                $this->assertSame(
                    0.0,
                    $contextA->kpiSummary['revenue'],
                    'Revenue tenant A harus 0 karena tidak ada sales orders, bukan data dari tenant B'
                );
            }
        });
    }

    // =========================================================================
    // Property 6 (edge case): Isolation dengan tenant yang tidak ada datanya
    //
    // Tenant baru tanpa data apapun harus mendapat context dengan KPI = 0/null,
    // bukan data dari tenant lain.
    //
    // Feature: erp-ai-agent, Property 6: Tenant Context Isolation
    // Validates: Requirements 2.5, 9.1
    // =========================================================================

    public function testTenantContextIsolationWithEmptyTenant(): void
    {
        $tenantWithData = $this->createTenant();
        $emptyTenant    = $this->createTenant();

        // Seed data hanya untuk tenantWithData
        $this->seedTenantData($tenantWithData->id, salesCount: 3, employeeCount: 5);

        $contextEmpty = $this->builder->build($emptyTenant->id, ['accounting', 'hrm', 'inventory']);

        // Tenant kosong harus mendapat KPI = 0, bukan data dari tenant lain
        $this->assertSame(0.0, $contextEmpty->kpiSummary['revenue']);
        $this->assertSame(0, $contextEmpty->kpiSummary['active_employees']);
        $this->assertSame(0.0, $contextEmpty->kpiSummary['overdue_ar']);
        $this->assertSame($emptyTenant->id, $contextEmpty->tenantId);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Seed data ERP untuk tenant tertentu.
     */
    private function seedTenantData(int $tenantId, int $salesCount = 0, int $employeeCount = 0): void
    {
        // Buat user dummy untuk foreign key sales_orders.user_id
        $user     = null;
        $customer = null;

        if ($salesCount > 0) {
            $tenant   = \App\Models\Tenant::withoutGlobalScopes()->find($tenantId);
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
        }

        // Buat sales orders bulan ini
        for ($i = 0; $i < $salesCount; $i++) {
            $total = rand(100000, 1000000);
            SalesOrder::withoutGlobalScopes()->create([
                'tenant_id'   => $tenantId,
                'customer_id' => $customer->id,
                'user_id'     => $user->id,
                'number'      => 'SO-TEST-' . uniqid(),
                'status'      => 'confirmed',
                'date'        => now()->startOfMonth()->addDays(rand(0, 10)),
                'total'       => $total,
                'subtotal'    => $total,
                'discount'    => 0,
                'tax'         => 0,
            ]);
        }

        // Buat employees aktif
        for ($i = 0; $i < $employeeCount; $i++) {
            Employee::withoutGlobalScopes()->create([
                'tenant_id' => $tenantId,
                'name'      => 'Employee ' . uniqid(),
                'status'    => 'active',
                'position'  => 'Staff',
            ]);
        }
    }
}
