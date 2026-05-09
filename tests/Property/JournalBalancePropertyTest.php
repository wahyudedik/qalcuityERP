<?php

namespace Tests\Property;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Tenant;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Tests\TestCase;

/**
 * Property-Based Tests for Journal Entry Balance Invariant.
 *
 * Feature: erp-comprehensive-audit-fix
 *
 * **Validates: Requirements 10.2**
 */
class JournalBalancePropertyTest extends TestCase
{
    use TestTrait;

    /**
     * Property 2: Journal Entry Balance Invariant
     *
     * For any journal entry that is successfully saved to the database,
     * the total debit amount must always equal the total credit amount
     * (difference = 0).
     *
     * **Validates: Requirements 10.2**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_journal_balance_invariant(): void
    {
        $this
            ->forAll(
                Generators::choose(1, 10),  // number of journal lines
                Generators::choose(100, 10000)  // base amount
            )
            ->then(function ($lineCount, $baseAmount) {
                // Create tenant, user, and seed COA
                $tenant = $this->createTenant();
                $user = $this->createAdminUser($tenant);
                $this->seedCoa($tenant->id);

                // Get some accounts to use
                $accounts = ChartOfAccount::where('tenant_id', $tenant->id)
                    ->where('is_active', true)
                    ->limit(10)
                    ->get();

                $this->assertGreaterThan(0, $accounts->count(),
                    'Must have at least one account to create journal entries');

                // Create a journal entry
                $journal = JournalEntry::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'number' => 'JE-'.uniqid(),
                    'date' => now(),
                    'description' => 'Property test journal',
                    'status' => 'draft',
                ]);

                // Create balanced journal lines
                // Strategy: create pairs of debit/credit lines with same amount
                $totalDebit = 0;
                $totalCredit = 0;

                for ($i = 0; $i < $lineCount; $i++) {
                    $amount = $baseAmount + ($i * 100);
                    $account = $accounts->random();

                    // Create debit line
                    JournalEntryLine::create([
                        'journal_entry_id' => $journal->id,
                        'account_id' => $account->id,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => 'Debit line '.$i,
                    ]);
                    $totalDebit += $amount;

                    // Create matching credit line
                    $creditAccount = $accounts->random();
                    JournalEntryLine::create([
                        'journal_entry_id' => $journal->id,
                        'account_id' => $creditAccount->id,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => 'Credit line '.$i,
                    ]);
                    $totalCredit += $amount;
                }

                // Refresh journal to get updated lines
                $journal->refresh();

                // Verify balance invariant
                $actualDebit = $journal->totalDebit();
                $actualCredit = $journal->totalCredit();
                $difference = abs($actualDebit - $actualCredit);

                $this->assertLessThan(
                    0.01,
                    $difference,
                    'Journal entry must be balanced (debit = credit). '.
                    "Debit: {$actualDebit}, Credit: {$actualCredit}, Difference: {$difference}"
                );

                $this->assertTrue(
                    $journal->isBalanced(),
                    'Journal->isBalanced() must return true for balanced journal'
                );

                // Verify the journal can be validated without throwing exception
                try {
                    $journal->validateBalance();
                    $this->assertTrue(true, 'validateBalance() should not throw for balanced journal');
                } catch (\RuntimeException $e) {
                    $this->fail('validateBalance() threw exception for balanced journal: '.$e->getMessage());
                }
            });
    }

    /**
     * Property 2 (variant): Unbalanced Journal Rejection
     *
     * For any journal entry where debit != credit, the system must reject
     * the journal and throw a validation exception.
     *
     * **Validates: Requirements 10.2**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_unbalanced_journal_rejection(): void
    {
        $this
            ->forAll(
                Generators::choose(100, 10000),  // debit amount
                Generators::choose(1, 100)       // difference amount (makes it unbalanced)
            )
            ->then(function ($debitAmount, $difference) {
                // Create tenant, user, and seed COA
                $tenant = $this->createTenant();
                $user = $this->createAdminUser($tenant);
                $this->seedCoa($tenant->id);

                // Get some accounts
                $accounts = ChartOfAccount::where('tenant_id', $tenant->id)
                    ->where('is_active', true)
                    ->limit(2)
                    ->get();

                $this->assertGreaterThanOrEqual(2, $accounts->count());

                // Create an unbalanced journal entry
                $journal = JournalEntry::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'number' => 'JE-UNBAL-'.uniqid(),
                    'date' => now(),
                    'description' => 'Unbalanced journal test',
                    'status' => 'draft',
                ]);

                // Create debit line
                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $accounts[0]->id,
                    'debit' => $debitAmount,
                    'credit' => 0,
                    'description' => 'Debit line',
                ]);

                // Create credit line with different amount (unbalanced)
                $creditAmount = $debitAmount + $difference;
                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $accounts[1]->id,
                    'debit' => 0,
                    'credit' => $creditAmount,
                    'description' => 'Credit line',
                ]);

                $journal->refresh();

                // Verify the journal is NOT balanced
                $this->assertFalse(
                    $journal->isBalanced(),
                    "Journal with debit={$debitAmount} and credit={$creditAmount} should not be balanced"
                );

                // Verify validateBalance() throws exception
                $this->expectException(\RuntimeException::class);
                $this->expectExceptionMessageMatches('/tidak balance/i');
                $journal->validateBalance();
            });
    }
}
