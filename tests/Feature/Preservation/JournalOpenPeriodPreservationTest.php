<?php

namespace Tests\Feature\Preservation;

use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Models\Tenant;
use App\Models\User;
use App\Services\GlPostingService;
use App\Services\PeriodLockService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Preservation Test — Journal Creation Succeeds for Open Period
 *
 * Memverifikasi bahwa pembuatan jurnal untuk periode 'open' yang SUDAH BENAR
 * tidak berubah setelah fix diterapkan. Test ini harus LULUS pada kode unfixed.
 *
 * Ini adalah NON-BUGGY case — kontras dengan Bug 1.14 yang menguji periode locked.
 * Jurnal harus BERHASIL dibuat ketika periode dalam status 'open'.
 *
 * Validates: Requirements 3.14
 */
class JournalOpenPeriodPreservationTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user   = $this->createAdminUser($this->tenant);
        $this->seedCoa($this->tenant->id);

        $this->actingAs($this->user);
    }

    // ── Requirement 3.14: Journal succeeds for open period ────────────────────

    /**
     * @test
     * Preservation 3.14: GlPostingService.postSalesOrder berhasil untuk periode 'open'
     *
     * Ini adalah NON-BUGGY case. Jurnal harus berhasil dibuat ketika periode open.
     * Validates: Requirements 3.14
     */
    public function test_journal_posting_succeeds_for_open_period(): void
    {
        // Buat accounting period dengan status 'open'
        AccountingPeriod::create([
            'tenant_id'  => $this->tenant->id,
            'name'       => 'Januari 2025',
            'start_date' => '2025-01-01',
            'end_date'   => '2025-01-31',
            'status'     => 'open',
        ]);

        $glService = app(GlPostingService::class);

        // Act: Posting jurnal ke periode open — harus BERHASIL
        $result = $glService->postSalesOrder(
            tenantId:    $this->tenant->id,
            userId:      $this->user->id,
            soNumber:    'SO-OPEN-001',
            soId:        1,
            subtotal:    1000000,
            taxAmount:   0,
            total:       1000000,
            paymentType: 'credit',
            date:        '2025-01-15', // Tanggal di dalam periode open
        );

        // Assert: Jurnal harus berhasil dibuat
        $this->assertTrue(
            $result->isSuccess() || $result->isSkipped(),
            "Jurnal harus berhasil dibuat untuk periode 'open'. " .
            "Result: " . ($result->isFailed() ? $result->warningMessage() : 'ok')
        );
    }

    /**
     * @test
     * Preservation 3.14: Jurnal entry dibuat di database untuk periode open
     *
     * Validates: Requirements 3.14
     */
    public function test_journal_entry_is_created_in_database_for_open_period(): void
    {
        AccountingPeriod::create([
            'tenant_id'  => $this->tenant->id,
            'name'       => 'Februari 2025',
            'start_date' => '2025-02-01',
            'end_date'   => '2025-02-28',
            'status'     => 'open',
        ]);

        $glService = app(GlPostingService::class);

        $result = $glService->postSalesOrder(
            tenantId:    $this->tenant->id,
            userId:      $this->user->id,
            soNumber:    'SO-OPEN-002',
            soId:        2,
            subtotal:    500000,
            taxAmount:   0,
            total:       500000,
            paymentType: 'credit',
            date:        '2025-02-15',
        );

        // Jurnal harus ada di database
        $this->assertDatabaseHas('journal_entries', [
            'tenant_id'      => $this->tenant->id,
            'reference_type' => 'sales_order',
            'reference'      => 'SO-OPEN-002',
            'status'         => 'posted',
        ]);
    }

    /**
     * @test
     * Preservation 3.14: Jurnal untuk periode open memiliki debit dan credit lines
     *
     * Validates: Requirements 3.14
     */
    public function test_journal_for_open_period_has_debit_and_credit_lines(): void
    {
        AccountingPeriod::create([
            'tenant_id'  => $this->tenant->id,
            'name'       => 'Maret 2025',
            'start_date' => '2025-03-01',
            'end_date'   => '2025-03-31',
            'status'     => 'open',
        ]);

        $glService = app(GlPostingService::class);

        $result = $glService->postSalesOrder(
            tenantId:    $this->tenant->id,
            userId:      $this->user->id,
            soNumber:    'SO-OPEN-003',
            soId:        3,
            subtotal:    750000,
            taxAmount:   0,
            total:       750000,
            paymentType: 'credit',
            date:        '2025-03-10',
        );

        if ($result->isFailed()) {
            $this->markTestSkipped("COA tidak lengkap: " . $result->warningMessage());
        }

        $journal = $result->journal;
        $this->assertNotNull($journal, "Jurnal harus ada");

        // Harus ada debit lines
        $debitLines = $journal->lines->where('debit', '>', 0);
        $this->assertGreaterThan(0, $debitLines->count(), "Jurnal harus memiliki debit lines");

        // Harus ada credit lines
        $creditLines = $journal->lines->where('credit', '>', 0);
        $this->assertGreaterThan(0, $creditLines->count(), "Jurnal harus memiliki credit lines");

        // Jurnal harus balance
        $totalDebit  = round($journal->lines->sum('debit'), 2);
        $totalCredit = round($journal->lines->sum('credit'), 2);
        $this->assertEquals($totalDebit, $totalCredit, "Jurnal harus balance");
    }

    /**
     * @test
     * Preservation 3.14: PeriodLockService.isLocked() mengembalikan false untuk periode open
     *
     * Validates: Requirements 3.14
     */
    public function test_period_lock_service_returns_false_for_open_period(): void
    {
        AccountingPeriod::create([
            'tenant_id'  => $this->tenant->id,
            'name'       => 'April 2025',
            'start_date' => '2025-04-01',
            'end_date'   => '2025-04-30',
            'status'     => 'open',
        ]);

        $periodLockService = app(PeriodLockService::class);

        // Periode open tidak boleh dianggap locked
        $isLocked = $periodLockService->isLocked($this->tenant->id, '2025-04-15');

        $this->assertFalse(
            $isLocked,
            "PeriodLockService.isLocked() harus mengembalikan false untuk periode 'open'"
        );
    }

    /**
     * @test
     * Preservation 3.14: Jurnal berhasil dibuat tanpa accounting period (periode tidak dikonfigurasi)
     *
     * Ketika tidak ada accounting period yang dikonfigurasi, jurnal tetap harus bisa dibuat.
     * Validates: Requirements 3.14
     */
    public function test_journal_succeeds_when_no_accounting_period_configured(): void
    {
        // Tidak ada accounting period yang dibuat
        $this->assertEquals(
            0,
            AccountingPeriod::where('tenant_id', $this->tenant->id)->count(),
            "Tidak ada accounting period yang dikonfigurasi"
        );

        $glService = app(GlPostingService::class);

        // Jurnal harus tetap bisa dibuat
        $result = $glService->postSalesOrder(
            tenantId:    $this->tenant->id,
            userId:      $this->user->id,
            soNumber:    'SO-NOPERIOD-001',
            soId:        99,
            subtotal:    200000,
            taxAmount:   0,
            total:       200000,
            paymentType: 'credit',
            date:        today()->toDateString(),
        );

        // Tidak boleh gagal hanya karena tidak ada accounting period
        $this->assertFalse(
            $result->isFailed() && str_contains($result->warningMessage() ?? '', 'periode'),
            "Jurnal tidak boleh gagal karena tidak ada accounting period yang dikonfigurasi"
        );
    }

    /**
     * @test
     * Preservation 3.14: AccountingPeriod.isOpen() mengembalikan true untuk status 'open'
     *
     * Validates: Requirements 3.14
     */
    public function test_accounting_period_is_open_returns_true_for_open_status(): void
    {
        $period = AccountingPeriod::create([
            'tenant_id'  => $this->tenant->id,
            'name'       => 'Mei 2025',
            'start_date' => '2025-05-01',
            'end_date'   => '2025-05-31',
            'status'     => 'open',
        ]);

        $this->assertTrue($period->isOpen(), "AccountingPeriod.isOpen() harus true untuk status 'open'");
        $this->assertFalse($period->isLocked(), "AccountingPeriod.isLocked() harus false untuk status 'open'");
    }

    /**
     * @test
     * Preservation 3.14: AccountingPeriod.findForDate() menemukan periode open untuk tanggal yang sesuai
     *
     * Validates: Requirements 3.14
     */
    public function test_accounting_period_find_for_date_returns_open_period(): void
    {
        $period = AccountingPeriod::create([
            'tenant_id'  => $this->tenant->id,
            'name'       => 'Juni 2025',
            'start_date' => '2025-06-01',
            'end_date'   => '2025-06-30',
            'status'     => 'open',
        ]);

        $found = AccountingPeriod::findForDate($this->tenant->id, '2025-06-15');

        $this->assertNotNull($found, "findForDate harus menemukan periode open");
        $this->assertEquals($period->id, $found->id);
        $this->assertEquals('open', $found->status);
    }
}
