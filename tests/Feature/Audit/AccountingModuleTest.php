<?php

namespace Tests\Feature\Audit;

use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\RecurringJournal;
use App\Models\Tenant;
use App\Models\User;
use App\Services\FinancialStatementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 10: Audit & Perbaikan Modul Akuntansi
 * 
 * Test suite untuk memverifikasi semua fitur akuntansi berfungsi dengan benar:
 * - 10.1: Chart of Accounts CRUD
 * - 10.2: Journal entry balance validation
 * - 10.3: Financial reports consistency
 * - 10.4: Bank reconciliation
 * - 10.5: Multi-currency
 * - 10.6: Tax calculations (PPN 11%, PPh)
 * - 10.7: Period lock
 * - 10.8: Recurring journals
 */
class AccountingModuleTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant([
            'name' => 'Test Company',
            'is_active' => true,
        ]);

        $this->user = $this->createAdminUser($this->tenant);

        $this->actingAs($this->user);
    }

    // ── 10.1: Chart of Accounts CRUD ──────────────────────────────

    public function test_can_create_chart_of_account(): void
    {
        $response = $this->post(route('accounting.coa.store'), [
            'code' => '1-1000',
            'name' => 'Kas',
            'type' => 'asset',
            'normal_balance' => 'debit',
            'level' => 1,
            'is_header' => false,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('chart_of_accounts', [
            'tenant_id' => $this->tenant->id,
            'code' => '1-1000',
            'name' => 'Kas',
            'type' => 'asset',
        ]);
    }

    public function test_cannot_create_duplicate_account_code(): void
    {
        ChartOfAccount::create([
            'tenant_id' => $this->tenant->id,
            'code' => '1-1000',
            'name' => 'Kas',
            'type' => 'asset',
            'normal_balance' => 'debit',
            'level' => 1,
            'is_header' => false,
            'is_active' => true,
        ]);

        $response = $this->post(route('accounting.coa.store'), [
            'code' => '1-1000',
            'name' => 'Kas Lain',
            'type' => 'asset',
            'normal_balance' => 'debit',
            'level' => 1,
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_account_types_are_valid(): void
    {
        $validTypes = ['asset', 'liability', 'equity', 'revenue', 'expense'];

        foreach ($validTypes as $type) {
            $account = ChartOfAccount::factory()->create([
                'tenant_id' => $this->tenant->id,
                'type' => $type,
            ]);

            $this->assertEquals($type, $account->type);
        }
    }

    public function test_can_update_chart_of_account(): void
    {
        $account = ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Kas Kecil',
        ]);

        $response = $this->put(route('accounting.coa.update', $account), [
            'name' => 'Kas Besar',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('chart_of_accounts', [
            'id' => $account->id,
            'name' => 'Kas Besar',
        ]);
    }

    public function test_cannot_delete_account_with_journal_entries(): void
    {
        $account = ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $journal = JournalEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        JournalEntryLine::factory()->create([
            'journal_entry_id' => $journal->id,
            'account_id' => $account->id,
            'debit' => 100000,
        ]);

        $response = $this->delete(route('accounting.coa.destroy', $account));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('chart_of_accounts', ['id' => $account->id]);
    }

    // ── 10.2: Journal Entry Balance Validation ───────────────────

    public function test_journal_entry_must_be_balanced(): void
    {
        $cashAccount = ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
            'type' => 'asset',
        ]);

        $revenueAccount = ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
            'type' => 'revenue',
        ]);

        $response = $this->post(route('journals.store'), [
            'date' => now()->toDateString(),
            'description' => 'Test Journal',
            'lines' => [
                [
                    'account_id' => $cashAccount->id,
                    'debit' => 100000,
                    'credit' => 0,
                ],
                [
                    'account_id' => $revenueAccount->id,
                    'debit' => 0,
                    'credit' => 100000,
                ],
            ],
        ]);

        $response->assertRedirect(route('journals.index'));
        $response->assertSessionHas('success');

        $journal = JournalEntry::where('tenant_id', $this->tenant->id)->first();
        $this->assertTrue($journal->isBalanced());
    }

    public function test_unbalanced_journal_is_rejected(): void
    {
        $cashAccount = ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $revenueAccount = ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->post(route('journals.store'), [
            'date' => now()->toDateString(),
            'description' => 'Unbalanced Journal',
            'lines' => [
                [
                    'account_id' => $cashAccount->id,
                    'debit' => 100000,
                    'credit' => 0,
                ],
                [
                    'account_id' => $revenueAccount->id,
                    'debit' => 0,
                    'credit' => 50000, // NOT BALANCED!
                ],
            ],
        ]);

        $response->assertSessionHasErrors('lines');
    }

    public function test_journal_validates_balance_before_posting(): void
    {
        $journal = JournalEntry::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'draft',
        ]);

        $cashAccount = ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $revenueAccount = ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        JournalEntryLine::factory()->create([
            'journal_entry_id' => $journal->id,
            'account_id' => $cashAccount->id,
            'debit' => 100000,
            'credit' => 0,
        ]);

        JournalEntryLine::factory()->create([
            'journal_entry_id' => $journal->id,
            'account_id' => $revenueAccount->id,
            'debit' => 0,
            'credit' => 100000,
        ]);

        $response = $this->post(route('journals.post', $journal));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $journal->refresh();
        $this->assertEquals('posted', $journal->status);
    }

    // ── 10.3: Financial Reports Consistency ───────────────────────

    public function test_balance_sheet_generates_correctly(): void
    {
        $this->seedBasicAccounts();

        $response = $this->get(route('accounting.balance-sheet', [
            'as_of' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertViewHas('data');

        $data = $response->viewData('data');
        $this->assertArrayHasKey('assets', $data);
        $this->assertArrayHasKey('liabilities', $data);
        $this->assertArrayHasKey('equity', $data);

        // Balance sheet equation: Assets = Liabilities + Equity
        $totalAssets = $data['total_assets'];
        $totalLiabilities = $data['total_liabilities'];
        $totalEquity = $data['total_equity'];

        $this->assertEquals(
            $totalAssets,
            $totalLiabilities + $totalEquity,
            'Balance sheet must balance: Assets = Liabilities + Equity'
        );
    }

    public function test_income_statement_generates_correctly(): void
    {
        $this->seedBasicAccounts();

        $response = $this->get(route('accounting.income-statement', [
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertViewHas('data');

        $data = $response->viewData('data');
        $this->assertArrayHasKey('revenue', $data);
        $this->assertArrayHasKey('expenses', $data);
        $this->assertArrayHasKey('net_income', $data);

        // Net income = Revenue - Expenses
        $totalRevenue = $data['total_revenue'];
        $totalExpenses = $data['total_expenses'];
        $netIncome = $data['net_income'];

        $this->assertEquals(
            $netIncome,
            $totalRevenue - $totalExpenses,
            'Net income must equal revenue minus expenses'
        );
    }

    public function test_cash_flow_statement_generates_correctly(): void
    {
        $this->seedBasicAccounts();

        $response = $this->get(route('accounting.cash-flow', [
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertViewHas('data');

        $data = $response->viewData('data');
        $this->assertArrayHasKey('operating', $data);
        $this->assertArrayHasKey('investing', $data);
        $this->assertArrayHasKey('financing', $data);
    }

    // ── 10.7: Period Lock ─────────────────────────────────────────

    public function test_can_create_accounting_period(): void
    {
        $response = $this->post(route('accounting.periods.store'), [
            'name' => 'Januari 2025',
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('accounting_periods', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Januari 2025',
            'status' => 'open',
        ]);
    }

    public function test_cannot_create_overlapping_periods(): void
    {
        AccountingPeriod::factory()->create([
            'tenant_id' => $this->tenant->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ]);

        $response = $this->post(route('accounting.periods.store'), [
            'name' => 'Januari 2025 (Duplicate)',
            'start_date' => '2025-01-15',
            'end_date' => '2025-02-15',
        ]);

        $response->assertSessionHasErrors('start_date');
    }

    public function test_can_close_accounting_period(): void
    {
        $period = AccountingPeriod::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'open',
        ]);

        $response = $this->patch(route('accounting.periods.close', $period));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $period->refresh();
        $this->assertEquals('closed', $period->status);
        $this->assertNotNull($period->closed_at);
    }

    public function test_can_lock_accounting_period(): void
    {
        $period = AccountingPeriod::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'open',
        ]);

        $response = $this->patch(route('accounting.periods.lock', $period));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $period->refresh();
        $this->assertEquals('locked', $period->status);
    }

    public function test_cannot_post_journal_to_locked_period(): void
    {
        $period = AccountingPeriod::factory()->create([
            'tenant_id' => $this->tenant->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'status' => 'locked',
        ]);

        $cashAccount = ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $revenueAccount = ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->post(route('journals.store'), [
            'date' => '2025-01-15',
            'description' => 'Test Journal in Locked Period',
            'period_id' => $period->id,
            'lines' => [
                [
                    'account_id' => $cashAccount->id,
                    'debit' => 100000,
                    'credit' => 0,
                ],
                [
                    'account_id' => $revenueAccount->id,
                    'debit' => 0,
                    'credit' => 100000,
                ],
            ],
        ]);

        $response->assertSessionHasErrors();
    }

    // ── 10.8: Recurring Journals ──────────────────────────────────

    public function test_can_create_recurring_journal(): void
    {
        $cashAccount = ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $revenueAccount = ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->post(route('journals.recurring.store'), [
            'name' => 'Monthly Rent',
            'description' => 'Recurring rent payment',
            'frequency' => 'monthly',
            'start_date' => now()->toDateString(),
            'lines' => [
                [
                    'account_id' => $cashAccount->id,
                    'debit' => 0,
                    'credit' => 5000000,
                ],
                [
                    'account_id' => $revenueAccount->id,
                    'debit' => 5000000,
                    'credit' => 0,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('recurring_journals', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Monthly Rent',
            'frequency' => 'monthly',
            'is_active' => true,
        ]);
    }

    public function test_recurring_journal_must_be_balanced(): void
    {
        $cashAccount = ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $revenueAccount = ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->post(route('journals.recurring.store'), [
            'name' => 'Unbalanced Recurring',
            'frequency' => 'monthly',
            'start_date' => now()->toDateString(),
            'lines' => [
                [
                    'account_id' => $cashAccount->id,
                    'debit' => 100000,
                    'credit' => 0,
                ],
                [
                    'account_id' => $revenueAccount->id,
                    'debit' => 0,
                    'credit' => 50000, // NOT BALANCED!
                ],
            ],
        ]);

        $response->assertSessionHasErrors('lines');
    }

    public function test_can_toggle_recurring_journal_status(): void
    {
        $recurring = RecurringJournal::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);

        $response = $this->post(route('journals.recurring.toggle', $recurring));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $recurring->refresh();
        $this->assertFalse($recurring->is_active);
    }

    // ── Helper Methods ────────────────────────────────────────────

    private function seedBasicAccounts(): void
    {
        // Create basic chart of accounts for testing
        ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '1-1000',
            'name' => 'Kas',
            'type' => 'asset',
            'normal_balance' => 'debit',
        ]);

        ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '2-1000',
            'name' => 'Hutang Usaha',
            'type' => 'liability',
            'normal_balance' => 'credit',
        ]);

        ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '3-1000',
            'name' => 'Modal',
            'type' => 'equity',
            'normal_balance' => 'credit',
        ]);

        ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '4-1000',
            'name' => 'Pendapatan',
            'type' => 'revenue',
            'normal_balance' => 'credit',
        ]);

        ChartOfAccount::factory()->create([
            'tenant_id' => $this->tenant->id,
            'code' => '5-1000',
            'name' => 'Beban',
            'type' => 'expense',
            'normal_balance' => 'debit',
        ]);
    }
}
