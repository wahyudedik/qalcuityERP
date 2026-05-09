<?php

namespace Tests\Feature\BugExploration;

use App\Models\CrmLead;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LeadConversionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Bug 1.16 — Duplikat Customer Saat Konversi Lead
 *
 * Membuktikan bahwa LeadConversionService membuat customer baru
 * meskipun customer dengan email yang sama sudah ada.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 *
 * CATATAN: Berdasarkan kode aktual, LeadConversionService sudah memiliki
 * duplicate check. Test ini memverifikasi apakah behavior yang benar sudah ada.
 */
class CrmLeadConversionDuplicateTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user = $this->createAdminUser($this->tenant);

        $this->actingAs($this->user);
    }

    /**
     * @test
     * Bug 1.16: Konversi lead dengan email duplikat seharusnya menggunakan customer existing
     *
     * AKAN GAGAL jika LeadConversionService membuat customer baru (duplikat)
     *
     * Validates: Requirements 1.16
     */
    public function test_lead_conversion_with_duplicate_email_uses_existing_customer(): void
    {
        // Arrange: Buat customer yang sudah ada
        $existingCustomer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        // Buat lead dengan email yang sama
        $lead = CrmLead::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com', // Email sama dengan customer existing
            'phone' => '08123456789',
            'stage' => 'won',
            'status' => 'active',
        ]);

        $customerCountBefore = Customer::where('tenant_id', $this->tenant->id)->count();

        // Act: Konversi lead
        $service = app(LeadConversionService::class);
        $result = $service->convertLead($lead);

        $customerCountAfter = Customer::where('tenant_id', $this->tenant->id)->count();

        // Assert: Tidak ada customer baru yang dibuat (duplikat)
        // Test ini AKAN GAGAL jika service membuat customer baru
        $this->assertEquals(
            $customerCountBefore,
            $customerCountAfter,
            'Bug 1.16: LeadConversionService membuat customer baru (duplikat) '.
            'meskipun customer dengan email yang sama sudah ada. '.
            "Jumlah customer sebelum: {$customerCountBefore}, setelah: {$customerCountAfter}."
        );

        // Assert: Result harus menunjukkan customer existing digunakan
        if (isset($result['action'])) {
            $this->assertEquals(
                'linked',
                $result['action'],
                "Bug 1.16: Action seharusnya 'linked' (menggunakan customer existing), ".
                "bukan 'created' (membuat customer baru)."
            );
        }
    }

    /**
     * @test
     * Bug 1.16: Verifikasi bahwa duplicate check dilakukan sebelum create customer
     *
     * AKAN GAGAL jika tidak ada duplicate check
     */
    public function test_lead_conversion_checks_for_duplicates_before_creating(): void
    {
        // Arrange: Buat customer existing
        $existingCustomer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Siti Rahayu',
            'email' => 'siti@example.com',
            'phone' => '08987654321',
            'is_active' => true,
        ]);

        // Buat lead dengan email yang sama
        $lead = CrmLead::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Siti Rahayu',
            'email' => 'siti@example.com',
            'phone' => '08987654321',
            'stage' => 'won',
            'status' => 'active',
        ]);

        // Act: Cek duplikat
        $service = app(LeadConversionService::class);
        $duplicateCheck = $service->checkForDuplicates($lead);

        // Assert: Harus ada duplikat yang terdeteksi
        // Test ini AKAN GAGAL jika duplicate check tidak berfungsi
        $this->assertTrue(
            $duplicateCheck['has_duplicates'],
            'Bug 1.16: checkForDuplicates() tidak mendeteksi duplikat '.
            'meskipun ada customer dengan email yang sama.'
        );

        // Assert: Duplikat yang ditemukan harus merujuk ke customer existing
        if ($duplicateCheck['has_duplicates']) {
            $foundCustomerIds = array_column($duplicateCheck['duplicates'], 'customer_id');
            $this->assertContains(
                $existingCustomer->id,
                $foundCustomerIds,
                'Bug 1.16: Duplikat yang terdeteksi tidak merujuk ke customer existing yang benar.'
            );
        }
    }
}
