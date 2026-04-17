<?php

namespace Tests\Feature\BugExploration;

use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Models\Tenant;
use App\Models\User;
use App\Services\GlPostingService;
use App\Services\PeriodLockService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Bug 1.14 — Jurnal Bisa Masuk ke Periode Locked
 *
 * Membuktikan bahwa GlPostingService tidak memvalidasi status accounting_period
 * sebelum membuat jurnal entry, sehingga jurnal bisa masuk ke periode locked.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 * Kegagalan membuktikan bug ada.
 */
class KeuanganJournalLockedPeriodTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant(['plan' => 'business']);

        $this->user = $this->createAdminUser($this->tenant);

        $this->actingAs($this->user);
    }

    /**
     * @test
     * Bug 1.14: GlPostingService harus melempar exception saat posting ke periode locked
     *
     * AKAN GAGAL karena GlPostingService tidak memvalidasi status periode
     * sebelum membuat jurnal entry.
     *
     * Validates: Requirements 1.14
     */
    public function test_journal_posting_to_locked_period_throws_exception(): void
    {
        // Arrange: Buat accounting period dengan status 'locked'
        $lockedPeriod = AccountingPeriod::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Januari 2025',
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'status' => 'locked',
        ]);

        $glService = app(GlPostingService::class);

        // Act & Assert: Posting jurnal ke periode locked seharusnya melempar exception
        // Test ini AKAN GAGAL karena GlPostingService tidak memvalidasi status periode
        $this->expectException(\Exception::class);

        $result = $glService->postSalesOrder(
            tenantId: $this->tenant->id,
            userId: $this->user->id,
            soNumber: 'SO-TEST-001',
            soId: 1,
            subtotal: 1000000,
            taxAmount: 110000,
            total: 1110000,
            date: '2025-01-15' // Tanggal di dalam periode locked
        );

        // Jika tidak ada exception, verifikasi bahwa result menunjukkan failure
        // Test ini AKAN GAGAL karena GlPostingService berhasil membuat jurnal
        $this->assertTrue(
            $result->isFailed(),
            "Bug 1.14: GlPostingService berhasil membuat jurnal ke periode locked " .
            "tanpa melempar exception. Jurnal seharusnya ditolak."
        );
    }

    /**
     * @test
     * Bug 1.14: PeriodLockService.assertNotLocked harus mencegah pembuatan jurnal
     *
     * AKAN GAGAL jika PeriodLockService tidak digunakan di GlPostingService
     */
    public function test_period_lock_service_is_used_in_gl_posting(): void
    {
        // Arrange: Buat accounting period locked
        AccountingPeriod::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Februari 2025',
            'start_date' => '2025-02-01',
            'end_date' => '2025-02-28',
            'status' => 'locked',
        ]);

        $periodLockService = app(PeriodLockService::class);

        // Verifikasi bahwa periode memang terkunci
        $isLocked = $periodLockService->isLocked($this->tenant->id, '2025-02-15');
        $this->assertTrue($isLocked, "Periode seharusnya terkunci");

        // Hitung jumlah jurnal sebelum
        $journalCountBefore = JournalEntry::where('tenant_id', $this->tenant->id)->count();

        // Act: Coba buat jurnal ke periode locked
        $glService = app(GlPostingService::class);

        try {
            $result = $glService->postSalesOrder(
                tenantId: $this->tenant->id,
                userId: $this->user->id,
                soNumber: 'SO-TEST-002',
                soId: 2,
                subtotal: 500000,
                taxAmount: 55000,
                total: 555000,
                date: '2025-02-15'
            );
        } catch (\Exception $e) {
            // Exception dilempar - ini adalah behavior yang benar
            $journalCountAfter = JournalEntry::where('tenant_id', $this->tenant->id)->count();
            $this->assertEquals(
                $journalCountBefore,
                $journalCountAfter,
                "Bug 1.14: Jurnal dibuat meskipun exception dilempar"
            );
            return;
        }

        // Jika tidak ada exception, verifikasi bahwa tidak ada jurnal baru yang dibuat
        $journalCountAfter = JournalEntry::where('tenant_id', $this->tenant->id)->count();

        // Test ini AKAN GAGAL karena jurnal berhasil dibuat ke periode locked
        $this->assertEquals(
            $journalCountBefore,
            $journalCountAfter,
            "Bug 1.14: Jurnal berhasil dibuat ke periode locked tanpa exception. " .
            "Jumlah jurnal sebelum: {$journalCountBefore}, setelah: {$journalCountAfter}. " .
            "GlPostingService tidak memvalidasi status periode sebelum membuat jurnal."
        );
    }
}
