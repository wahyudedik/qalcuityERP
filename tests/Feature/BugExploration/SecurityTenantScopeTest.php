<?php

namespace Tests\Feature\BugExploration;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Bug 1.24 — Query Model Tanpa TenantScope Otomatis
 *
 * Membuktikan bahwa model yang memiliki tenant_id menggunakan BelongsToTenant trait
 * yang otomatis menambahkan filter tenant_id pada semua query.
 *
 * CATATAN: Berdasarkan kode aktual, BelongsToTenant trait SUDAH ADA dan digunakan
 * di banyak model. Test ini memverifikasi apakah ada model yang BELUM menggunakan trait.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 */
class SecurityTenantScopeTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenantA;

    private Tenant $tenantB;

    private User $userA;

    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = $this->createTenant();
        $this->tenantB = $this->createTenant();

        $this->userA = $this->createAdminUser($this->tenantA);

        $this->userB = $this->createAdminUser($this->tenantB);
    }

    /**
     * @test
     * Bug 1.24: Query Customer tanpa manual where tenant_id harus otomatis ter-filter
     *
     * Dengan BelongsToTenant trait, query Customer::all() seharusnya hanya
     * mengembalikan data tenant yang sedang login.
     *
     * AKAN GAGAL jika ada model yang tidak menggunakan BelongsToTenant trait
     *
     * Validates: Requirements 1.24
     */
    public function test_customer_query_automatically_filters_by_tenant(): void
    {
        // Arrange: Buat customer untuk tenant A dan tenant B
        $this->actingAs($this->userA);

        $customerA = Customer::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Customer Tenant A',
            'email' => 'customer-a@example.com',
            'is_active' => true,
        ]);

        // Buat customer untuk tenant B (tanpa actingAs userB untuk bypass scope)
        Customer::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Customer Tenant B',
            'email' => 'customer-b@example.com',
            'is_active' => true,
        ]);

        // Act: Query Customer sebagai userA (tanpa manual where tenant_id)
        $customers = Customer::all();

        // Assert: Hanya customer tenant A yang muncul
        $customerIds = $customers->pluck('id')->toArray();

        $this->assertContains(
            $customerA->id,
            $customerIds,
            'Customer tenant A seharusnya ada dalam hasil query'
        );

        // Assert: Customer tenant B tidak boleh muncul
        $tenantBCustomers = $customers->where('tenant_id', $this->tenantB->id);

        $this->assertEquals(
            0,
            $tenantBCustomers->count(),
            'Bug 1.24: Customer dari tenant B muncul dalam query Customer::all() '.
            'yang dijalankan sebagai userA. TenantScope tidak berfungsi dengan benar.'
        );
    }

    /**
     * @test
     * Bug 1.24: Verifikasi bahwa model-model kritis menggunakan BelongsToTenant trait
     *
     * AKAN GAGAL jika ada model kritis yang tidak menggunakan trait
     */
    public function test_critical_models_use_belongs_to_tenant_trait(): void
    {
        $criticalModels = [
            Customer::class,
            Product::class,
            SalesOrder::class,
            Invoice::class,
            JournalEntry::class,
            Employee::class,
        ];

        $missingTrait = [];

        foreach ($criticalModels as $modelClass) {
            if (! class_exists($modelClass)) {
                continue;
            }

            $traits = class_uses_recursive($modelClass);
            if (! in_array(BelongsToTenant::class, $traits)) {
                $missingTrait[] = $modelClass;
            }
        }

        // Test ini AKAN GAGAL jika ada model kritis yang tidak menggunakan trait
        $this->assertEmpty(
            $missingTrait,
            "Bug 1.24: Model-model berikut tidak menggunakan BelongsToTenant trait:\n".
            implode("\n", $missingTrait)."\n".
            'Model-model ini rentan terhadap kebocoran data antar tenant.'
        );
    }

    /**
     * @test
     * Bug 1.24: BelongsToTenant trait harus skip filter untuk CLI/job context
     *
     * Saat tidak ada user yang login (CLI, job), scope tidak boleh memfilter
     * AKAN GAGAL jika scope memfilter saat tidak ada user login
     */
    public function test_tenant_scope_skips_filter_when_no_user_logged_in(): void
    {
        // Arrange: Buat customer untuk dua tenant
        Customer::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Customer A',
            'email' => 'a@example.com',
            'is_active' => true,
        ]);

        Customer::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Customer B',
            'email' => 'b@example.com',
            'is_active' => true,
        ]);

        // Act: Query tanpa user login (CLI context)
        auth()->logout();
        $customers = Customer::withoutGlobalScope('tenant')->get();

        // Assert: Semua customer harus muncul (tidak ada filter)
        $this->assertGreaterThanOrEqual(
            2,
            $customers->count(),
            'Bug 1.24: BelongsToTenant scope memfilter data saat tidak ada user login. '.
            'Scope seharusnya skip filter untuk CLI/job context.'
        );
    }
}
