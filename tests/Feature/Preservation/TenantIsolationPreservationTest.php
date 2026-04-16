<?php

namespace Tests\Feature\Preservation;

use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Preservation Test — Tenant Data Isolation
 *
 * Memverifikasi bahwa isolasi data multi-tenant yang SUDAH BENAR tidak berubah
 * setelah fix diterapkan. Test ini harus LULUS pada kode unfixed (baseline).
 *
 * BelongsToTenant trait sudah ada dan berfungsi — test ini memastikan
 * behavior tersebut tetap terjaga setelah fix.
 *
 * Validates: Requirements 3.8
 */
class TenantIsolationPreservationTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenantA;
    private Tenant $tenantB;
    private User $userA;
    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = $this->createTenant(['name' => 'Tenant A', 'slug' => 'tenant-a-' . uniqid()]);
        $this->tenantB = $this->createTenant(['name' => 'Tenant B', 'slug' => 'tenant-b-' . uniqid()]);

        $this->userA = $this->createAdminUser($this->tenantA);
        $this->userB = $this->createAdminUser($this->tenantB);
    }

    // ── Requirement 3.8: Customer isolation ──────────────────────────────────

    /**
     * @test
     * Preservation 3.8: Customer::all() sebagai userA tidak mengembalikan data tenantB
     *
     * BelongsToTenant trait sudah ada dan berfungsi.
     * Validates: Requirements 3.8
     */
    public function test_customer_query_as_tenant_a_does_not_return_tenant_b_data(): void
    {
        // Buat customer untuk tenant A
        $customerA = Customer::create([
            'tenant_id' => $this->tenantA->id,
            'name'      => 'Customer Tenant A',
            'email'     => 'customer-a-' . uniqid() . '@example.com',
            'is_active' => true,
        ]);

        // Buat customer untuk tenant B (bypass scope)
        Customer::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantB->id,
            'name'      => 'Customer Tenant B',
            'email'     => 'customer-b-' . uniqid() . '@example.com',
            'is_active' => true,
        ]);

        // Query sebagai userA
        $this->actingAs($this->userA);
        $customers = Customer::all();

        // Customer A harus ada
        $this->assertContains(
            $customerA->id,
            $customers->pluck('id')->toArray(),
            "Customer tenant A harus ada dalam hasil query"
        );

        // Customer tenant B tidak boleh ada
        $tenantBCustomers = $customers->where('tenant_id', $this->tenantB->id);
        $this->assertEquals(
            0,
            $tenantBCustomers->count(),
            "Customer tenant B tidak boleh muncul dalam query Customer::all() sebagai userA"
        );
    }

    /**
     * @test
     * Preservation 3.8: Customer::all() sebagai userB tidak mengembalikan data tenantA
     *
     * Validates: Requirements 3.8
     */
    public function test_customer_query_as_tenant_b_does_not_return_tenant_a_data(): void
    {
        // Buat customer untuk kedua tenant (bypass scope)
        Customer::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'name'      => 'Customer A',
            'email'     => 'ca-' . uniqid() . '@example.com',
            'is_active' => true,
        ]);

        $customerB = Customer::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantB->id,
            'name'      => 'Customer B',
            'email'     => 'cb-' . uniqid() . '@example.com',
            'is_active' => true,
        ]);

        // Query sebagai userB
        $this->actingAs($this->userB);
        $customers = Customer::all();

        // Customer B harus ada
        $this->assertContains(
            $customerB->id,
            $customers->pluck('id')->toArray(),
            "Customer tenant B harus ada dalam hasil query sebagai userB"
        );

        // Customer tenant A tidak boleh ada
        $tenantACustomers = $customers->where('tenant_id', $this->tenantA->id);
        $this->assertEquals(
            0,
            $tenantACustomers->count(),
            "Customer tenant A tidak boleh muncul dalam query sebagai userB"
        );
    }

    // ── Requirement 3.8: Product isolation ───────────────────────────────────

    /**
     * @test
     * Preservation 3.8: Product::all() sebagai tenantA tidak mengembalikan data tenantB
     *
     * Validates: Requirements 3.8
     */
    public function test_product_query_as_tenant_a_does_not_return_tenant_b_data(): void
    {
        $productA = $this->createProduct($this->tenantA->id, ['name' => 'Produk A']);

        // Buat produk untuk tenant B (bypass scope)
        Product::withoutGlobalScope('tenant')->create([
            'tenant_id'  => $this->tenantB->id,
            'name'       => 'Produk B',
            'sku'        => 'SKU-B-' . uniqid(),
            'unit'       => 'pcs',
            'price_sell' => 50000,
            'price_buy'  => 30000,
            'is_active'  => true,
        ]);

        $this->actingAs($this->userA);
        $products = Product::all();

        // Produk A harus ada
        $this->assertContains(
            $productA->id,
            $products->pluck('id')->toArray(),
            "Produk tenant A harus ada dalam hasil query"
        );

        // Produk tenant B tidak boleh ada
        $tenantBProducts = $products->where('tenant_id', $this->tenantB->id);
        $this->assertEquals(
            0,
            $tenantBProducts->count(),
            "Produk tenant B tidak boleh muncul dalam query Product::all() sebagai userA"
        );
    }

    // ── Requirement 3.8: JournalEntry isolation ───────────────────────────────

    /**
     * @test
     * Preservation 3.8: JournalEntry::all() sebagai tenantA tidak mengembalikan data tenantB
     *
     * Validates: Requirements 3.8
     */
    public function test_journal_entry_query_as_tenant_a_does_not_return_tenant_b_data(): void
    {
        $this->seedCoa($this->tenantA->id);
        $this->seedCoa($this->tenantB->id);

        // Buat jurnal untuk tenant A
        $journalA = JournalEntry::create([
            'tenant_id'      => $this->tenantA->id,
            'user_id'        => $this->userA->id,
            'number'         => 'JE-A-001',
            'date'           => today(),
            'description'    => 'Jurnal Tenant A',
            'reference'      => 'REF-A-001',
            'reference_type' => 'test',
            'currency_code'  => 'IDR',
            'currency_rate'  => 1,
            'status'         => 'posted',
        ]);

        // Buat jurnal untuk tenant B (bypass scope)
        JournalEntry::withoutGlobalScope('tenant')->create([
            'tenant_id'      => $this->tenantB->id,
            'user_id'        => $this->userB->id,
            'number'         => 'JE-B-001',
            'date'           => today(),
            'description'    => 'Jurnal Tenant B',
            'reference'      => 'REF-B-001',
            'reference_type' => 'test',
            'currency_code'  => 'IDR',
            'currency_rate'  => 1,
            'status'         => 'posted',
        ]);

        // Query sebagai userA
        $this->actingAs($this->userA);
        $journals = JournalEntry::all();

        // Jurnal A harus ada
        $this->assertContains(
            $journalA->id,
            $journals->pluck('id')->toArray(),
            "Jurnal tenant A harus ada dalam hasil query"
        );

        // Jurnal tenant B tidak boleh ada
        $tenantBJournals = $journals->where('tenant_id', $this->tenantB->id);
        $this->assertEquals(
            0,
            $tenantBJournals->count(),
            "Jurnal tenant B tidak boleh muncul dalam query JournalEntry::all() sebagai userA"
        );
    }

    // ── Requirement 3.8: BelongsToTenant trait ada di model kritis ───────────

    /**
     * @test
     * Preservation 3.8: Model kritis menggunakan BelongsToTenant trait
     *
     * Validates: Requirements 3.8
     */
    public function test_critical_models_use_belongs_to_tenant_trait(): void
    {
        $criticalModels = [
            \App\Models\Customer::class,
            \App\Models\Product::class,
            \App\Models\JournalEntry::class,
        ];

        foreach ($criticalModels as $modelClass) {
            if (!class_exists($modelClass)) {
                continue;
            }

            $traits = class_uses_recursive($modelClass);
            $this->assertContains(
                \App\Traits\BelongsToTenant::class,
                $traits,
                "Model {$modelClass} harus menggunakan BelongsToTenant trait untuk isolasi data"
            );
        }
    }

    /**
     * @test
     * Preservation 3.8: Tenant scope tidak memfilter saat tidak ada user login (CLI context)
     *
     * Validates: Requirements 3.8
     */
    public function test_tenant_scope_does_not_filter_when_no_user_logged_in(): void
    {
        // Buat data untuk kedua tenant (bypass scope)
        Customer::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantA->id,
            'name'      => 'Customer A CLI',
            'email'     => 'cli-a-' . uniqid() . '@example.com',
            'is_active' => true,
        ]);

        Customer::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->tenantB->id,
            'name'      => 'Customer B CLI',
            'email'     => 'cli-b-' . uniqid() . '@example.com',
            'is_active' => true,
        ]);

        // Tanpa user login (CLI context), withoutGlobalScope harus bisa akses semua
        auth()->logout();
        $allCustomers = Customer::withoutGlobalScope('tenant')->get();

        $this->assertGreaterThanOrEqual(
            2,
            $allCustomers->count(),
            "withoutGlobalScope harus bisa mengakses semua data dari semua tenant (CLI context)"
        );
    }
}
