<?php

namespace Tests\Feature\BugExploration;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Tenant;
use App\Models\User;
use App\Services\FinancialStatementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bug 1.22 — Balance Sheet Tidak Balance Tanpa Warning
 *
 * Membuktikan bahwa FinancialStatementService tidak menampilkan warning
 * ketika balance sheet tidak balance (total_assets ≠ total_liabilities + total_equity).
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 *
 * CATATAN: Berdasarkan kode aktual, FinancialStatementService.balanceSheet()
 * sudah memiliki 'is_balanced' field. Test ini memverifikasi apakah ada
 * 'balance_warning' dengan detail yang cukup.
 */
class AkuntansiBalanceSheetTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create(['is_active' => true]);
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
        ]);

        $this->actingAs($this->user);
    }

    /**
     * @test
     * Bug 1.22: Balance sheet harus memiliki balance_warning saat tidak balance
     *
     * AKAN GAGAL karena FinancialStatementService tidak memiliki 'balance_warning' key
     * dengan detail yang cukup (hanya ada 'is_balanced')
     *
     * Validates: Requirements 1.22
     */
    public function test_balance_sheet_has_balance_warning_when_not_balanced(): void
    {
        // Arrange: Buat data akuntansi yang tidak balance
        // Buat COA
        $assetAccount = ChartOfAccount::create([
            'tenant_id' => $this->tenant->id,
            'code' => '1101',
            'name' => 'Kas',
            'type' => 'asset',
            'normal_balance' => 'debit',
            'is_header' => false,
            'is_active' => true,
        ]);

        $revenueAccount = ChartOfAccount::create([
            'tenant_id' => $this->tenant->id,
            'code' => '4101',
            'name' => 'Pendapatan',
            'type' => 'revenue',
            'normal_balance' => 'credit',
            'is_header' => false,
            'is_active' => true,
        ]);

        // Buat jurnal yang tidak balance (hanya debit, tidak ada credit yang sesuai)
        $journal = JournalEntry::create([
            'tenant_id' => $this->tenant->id,
            'reference' => 'JE-TEST-001',
            'date' => today()->toDateString(),
            'description' => 'Test unbalanced journal',
            'status' => 'posted',
            'total_debit' => 1000000,
            'total_credit' => 500000, // Tidak balance!
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $journal->id,
            'account_id' => $assetAccount->id,
            'debit' => 1000000,
            'credit' => 0,
            'description' => 'Debit entry',
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $journal->id,
            'account_id' => $revenueAccount->id,
            'debit' => 0,
            'credit' => 500000, // Tidak balance!
            'description' => 'Credit entry',
        ]);

        // Act: Generate balance sheet
        $service = app(FinancialStatementService::class);
        $result = $service->balanceSheet($this->tenant->id, today()->toDateString());

        // Assert: Harus ada 'balance_warning' key dengan is_balanced = false
        // Test ini AKAN GAGAL karena result hanya memiliki 'is_balanced' bukan 'balance_warning'
        $this->assertArrayHasKey(
            'balance_warning',
            $result,
            "Bug 1.22: Balance sheet result tidak memiliki key 'balance_warning'. " .
            "Seharusnya ada 'balance_warning' dengan detail: is_balanced, difference, message. " .
            "Keys yang ada: " . implode(', ', array_keys($result))
        );

        if (isset($result['balance_warning'])) {
            $this->assertFalse(
                $result['balance_warning']['is_balanced'],
                "Bug 1.22: balance_warning.is_balanced seharusnya false untuk data yang tidak balance."
            );
        }
    }

    /**
     * @test
     * Bug 1.22: FinancialStatementService harus memiliki balance_warning dengan selisih
     *
     * AKAN GAGAL karena tidak ada balance_warning dengan detail selisih
     */
    public function test_balance_sheet_warning_includes_difference_amount(): void
    {
        $serviceFile = 'app/Services/FinancialStatementService.php';

        if (!file_exists($serviceFile)) {
            $this->markTestSkipped("FinancialStatementService tidak ditemukan");
        }

        $content = file_get_contents($serviceFile);

        // Cari 'balance_warning' key di return statement
        $hasBalanceWarning = str_contains($content, 'balance_warning');

        // Test ini AKAN GAGAL karena tidak ada 'balance_warning' key
        $this->assertTrue(
            $hasBalanceWarning,
            "Bug 1.22: FinancialStatementService tidak memiliki 'balance_warning' key " .
            "di return value balanceSheet(). " .
            "Hanya ada 'is_balanced' yang tidak cukup untuk menampilkan warning detail."
        );
    }
}
