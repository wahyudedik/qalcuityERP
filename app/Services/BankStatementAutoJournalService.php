<?php

namespace App\Services;

use App\DTOs\JournalPreviewDTO;
use App\Models\AccountingPeriod;
use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BankStatementAutoJournalService
 *
 * Generate journal entries otomatis dari bank statements menggunakan AI
 *
 * Features:
 * - Auto-generate journal dari single/multiple bank statements
 * - AI-powered account suggestion
 * - Handle special cases (transfer, fees, interest)
 * - Preview before save
 * - Batch processing with transaction support
 */
class BankStatementAutoJournalService
{
    public function __construct(
        private AccountingAiService $aiService,
        private DocumentNumberService $docNumberService
    ) {}

    // ═══════════════════════════════════════════════════════════════
    // 2.2 Generate Journal From Single Statement
    // ═══════════════════════════════════════════════════════════════

    /**
     * Generate journal entry dari single bank statement
     *
     * @return JournalPreviewDTO Preview journal yang akan dibuat
     */
    public function generateJournalFromStatement(BankStatement $statement): JournalPreviewDTO
    {
        $tenantId = $statement->tenant_id;
        $description = $statement->description;
        $amount = $statement->amount;
        $type = $statement->type; // 'debit' atau 'credit'

        // Detect transaction category
        $category = $this->detectTransactionCategory($statement);

        // Get bank account COA
        $bankAccountCOA = $this->getBankAccountCOA($statement->bank_account_id, $tenantId);

        // Generate journal lines based on category
        $lines = match ($category) {
            'bank_transfer' => $this->generateTransferLines($statement, $bankAccountCOA),
            'bank_fee' => $this->generateBankFeeLines($statement, $bankAccountCOA),
            'bank_interest' => $this->generateBankInterestLines($statement, $bankAccountCOA),
            'unknown' => $this->generateUnknownLines($statement, $bankAccountCOA),
            default => $this->generateStandardLines($statement, $bankAccountCOA, $category)
        };

        // Determine confidence & basis
        $confidence = $this->calculateConfidence($category, $lines);
        $aiBasis = $this->getAIBasis($category, $statement);

        // Warnings
        $warnings = $this->generateWarnings($category, $lines);

        // Format date
        $transactionDate = $statement->transaction_date;
        $dateString = $transactionDate instanceof Carbon
            ? $transactionDate->format('Y-m-d')
            : date('Y-m-d', strtotime($transactionDate));

        return new JournalPreviewDTO(
            date: $dateString,
            description: $description,
            reference: $statement->reference ?? 'BANK-'.$statement->id,
            journalType: $category,
            lines: $lines,
            confidence: $confidence,
            aiBasis: $aiBasis,
            warnings: $warnings,
            bankStatementId: $statement->id,
            bankAccountId: $statement->bank_account_id
        );
    }

    // ═══════════════════════════════════════════════════════════════
    // 2.3 Generate Journals From Multiple Statements (Batch)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Generate multiple journals dengan transaction support
     *
     * @param  Collection  $statements  Collection of BankStatement
     * @param  int  $userId  User ID untuk posted_by
     * @param  bool  $autoPost  Langsung post journal jika true
     * @return array ['success' => [...], 'failed' => [...]]
     */
    public function generateJournalsFromStatements(
        Collection $statements,
        int $userId,
        bool $autoPost = false
    ): array {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($statements as $statement) {
                try {
                    // Generate preview
                    $preview = $this->generateJournalFromStatement($statement);

                    // Validate
                    $errors = $preview->validate();
                    if (! empty($errors)) {
                        throw new \Exception(implode(', ', $errors));
                    }

                    // Create journal
                    $journal = $this->createJournalFromPreview($preview, $userId);

                    // Auto-post if requested
                    if ($autoPost) {
                        $journal->post($userId);
                    }

                    // Update statement status
                    $statement->update([
                        'status' => 'journalized',
                        'matched_transaction_id' => $journal->id,
                    ]);

                    $results['success'][] = [
                        'statement_id' => $statement->id,
                        'journal_id' => $journal->id,
                        'journal_number' => $journal->number,
                    ];

                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'statement_id' => $statement->id,
                        'error' => $e->getMessage(),
                    ];

                    Log::error('Failed to generate journal from bank statement', [
                        'statement_id' => $statement->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            return $results;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Batch journal generation failed', [
                'error' => $e->getMessage(),
                'statements_count' => $statements->count(),
            ]);

            throw $e;
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // 2.4 Preview Journal (Without Saving)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Preview journal tanpa save ke database
     * Alias untuk generateJournalFromStatement untuk consistency
     */
    public function previewJournal(BankStatement $statement): JournalPreviewDTO
    {
        return $this->generateJournalFromStatement($statement);
    }

    // ═══════════════════════════════════════════════════════════════
    // 2.5 Auto-Post Journals
    // ═══════════════════════════════════════════════════════════════

    /**
     * Auto-generate dan post journals dari statement IDs
     *
     * @param  Collection  $statementIds  Array of statement IDs
     * @param  int  $userId  User ID
     * @return array Results
     */
    public function autoPostJournals(Collection $statementIds, int $userId): array
    {
        $statements = BankStatement::whereIn('id', $statementIds)
            ->where('status', 'unmatched')
            ->get();

        if ($statements->isEmpty()) {
            return [
                'success' => [],
                'failed' => [],
                'message' => 'Tidak ada statement yang perlu diproses',
            ];
        }

        return $this->generateJournalsFromStatements($statements, $userId, true);
    }

    // ═══════════════════════════════════════════════════════════════
    // 2.6 Handle Edge Cases - Transaction Detection
    // ═══════════════════════════════════════════════════════════════

    /**
     * Detect kategori transaksi dari bank statement
     */
    private function detectTransactionCategory(BankStatement $statement): string
    {
        $description = strtolower($statement->description);
        $amount = $statement->amount;

        // 1. Transfer antar rekening
        if ($this->isTransfer($statement)) {
            return 'bank_transfer';
        }

        // 2. Bunga bank (jasa giro)
        if ($this->isBankInterest($description)) {
            return 'bank_interest';
        }

        // 3. Admin fee bank
        if ($this->isBankFee($description)) {
            return 'bank_fee';
        }

        // 4. Unknown - perlu review manual
        if ($this->isUnknown($description, $amount)) {
            return 'unknown';
        }

        // 5. Standard transaction (income/expense)
        return 'standard';
    }

    /**
     * Detect transfer antar rekening
     */
    private function isTransfer(BankStatement $statement): bool
    {
        $description = strtolower($statement->description);

        $transferKeywords = [
            'transfer antar',
            'transfer ke rekening',
            'transfer dari rekening',
            'transfer antar rekening',
            'internal transfer',
        ];

        foreach ($transferKeywords as $keyword) {
            if (str_contains($description, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect bunga bank / jasa giro
     */
    private function isBankInterest(string $description): bool
    {
        $interestKeywords = [
            'bunga',
            'jasa giro',
            'interest',
            'bunga tabungan',
            'jasa',
        ];

        foreach ($interestKeywords as $keyword) {
            if (str_contains($description, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect admin fee bank
     */
    private function isBankFee(string $description): bool
    {
        $feeKeywords = [
            'biaya admin',
            'admin fee',
            'biaya bulanan',
            'provisi',
            'materai',
            'biaya layanan',
        ];

        foreach ($feeKeywords as $keyword) {
            if (str_contains($description, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect unknown transaction (perlu manual review)
     */
    private function isUnknown(string $description, float $amount): bool
    {
        // Jika description terlalu pendek dan tidak ada keyword
        if (strlen($description) < 10) {
            return true;
        }

        // Jika amount sangat besar (> 100M) - perlu review
        if ($amount > 100000000000) {
            return true;
        }

        return false;
    }

    // ═══════════════════════════════════════════════════════════════
    // Journal Line Generators
    // ═══════════════════════════════════════════════════════════════

    /**
     * Generate journal lines untuk standard transaction
     */
    private function generateStandardLines(
        BankStatement $statement,
        ?ChartOfAccount $bankAccountCOA,
        string $category
    ): array {
        $tenantId = $statement->tenant_id;
        $amount = $statement->amount;
        $type = $statement->type;

        // AI suggestion untuk opposite account
        $aiSuggestion = $this->aiService->categorizeStatement(
            $tenantId,
            $statement->description,
            $type,
            $amount
        );

        $oppositeAccount = ChartOfAccount::find($aiSuggestion['account_id'] ?? null);

        if (! $oppositeAccount || ! $bankAccountCOA) {
            // Fallback jika account tidak ditemukan
            return $this->generateFallbackLines($statement, $bankAccountCOA, $type, $amount);
        }

        $lines = [];

        if ($type === 'credit') {
            // Uang masuk ke bank
            // Debit: Bank Account
            // Credit: Opposite Account (Income/Receivable)
            $lines[] = [
                'account_id' => $bankAccountCOA->id,
                'account_code' => $bankAccountCOA->code,
                'account_name' => $bankAccountCOA->name,
                'debit' => $amount,
                'credit' => 0,
                'description' => $statement->description,
            ];

            $lines[] = [
                'account_id' => $oppositeAccount->id,
                'account_code' => $oppositeAccount->code,
                'account_name' => $oppositeAccount->name,
                'debit' => 0,
                'credit' => $amount,
                'description' => $statement->description,
            ];
        } else {
            // Uang keluar dari bank
            // Debit: Opposite Account (Expense/Payable)
            // Credit: Bank Account
            $lines[] = [
                'account_id' => $oppositeAccount->id,
                'account_code' => $oppositeAccount->code,
                'account_name' => $oppositeAccount->name,
                'debit' => $amount,
                'credit' => 0,
                'description' => $statement->description,
            ];

            $lines[] = [
                'account_id' => $bankAccountCOA->id,
                'account_code' => $bankAccountCOA->code,
                'account_name' => $bankAccountCOA->name,
                'debit' => 0,
                'credit' => $amount,
                'description' => $statement->description,
            ];
        }

        return $lines;
    }

    /**
     * Generate journal lines untuk transfer antar rekening
     */
    private function generateTransferLines(
        BankStatement $statement,
        ?ChartOfAccount $bankAccountCOA
    ): array {
        $amount = $statement->amount;
        $type = $statement->type;

        // Untuk transfer, kita butuh destination bank account
        // Simplified: gunakan clearing account
        $clearingAccount = ChartOfAccount::where('tenant_id', $statement->tenant_id)
            ->where(function ($q) {
                $q->where('code', 'like', '1103%') // Transfer in transit
                    ->orWhere('name', 'like', '%transfer%')
                    ->orWhere('name', 'like', '%kliring%');
            })
            ->where('is_active', true)
            ->where('is_header', false)
            ->first();

        if (! $clearingAccount || ! $bankAccountCOA) {
            return $this->generateFallbackLines($statement, $bankAccountCOA, $type, $amount);
        }

        $lines = [];

        if ($type === 'credit') {
            // Transfer masuk
            $lines[] = [
                'account_id' => $bankAccountCOA->id,
                'account_code' => $bankAccountCOA->code,
                'account_name' => $bankAccountCOA->name,
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Transfer masuk: '.$statement->description,
            ];

            $lines[] = [
                'account_id' => $clearingAccount->id,
                'account_code' => $clearingAccount->code,
                'account_name' => $clearingAccount->name,
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Transfer masuk: '.$statement->description,
            ];
        } else {
            // Transfer keluar
            $lines[] = [
                'account_id' => $clearingAccount->id,
                'account_code' => $clearingAccount->code,
                'account_name' => $clearingAccount->name,
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Transfer keluar: '.$statement->description,
            ];

            $lines[] = [
                'account_id' => $bankAccountCOA->id,
                'account_code' => $bankAccountCOA->code,
                'account_name' => $bankAccountCOA->name,
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Transfer keluar: '.$statement->description,
            ];
        }

        return $lines;
    }

    /**
     * Generate journal lines untuk bank fee
     */
    private function generateBankFeeLines(
        BankStatement $statement,
        ?ChartOfAccount $bankAccountCOA
    ): array {
        $amount = $statement->amount;

        // Cari akun beban bank
        $bankFeeAccount = ChartOfAccount::where('tenant_id', $statement->tenant_id)
            ->where(function ($q) {
                $q->where('code', 'like', '6201%') // Bank charges
                    ->orWhere('name', 'like', '%biaya bank%')
                    ->orWhere('name', 'like', '%admin fee%')
                    ->orWhere('name', 'like', '%beban bank%');
            })
            ->where('is_active', true)
            ->where('is_header', false)
            ->first();

        if (! $bankFeeAccount || ! $bankAccountCOA) {
            return $this->generateFallbackLines($statement, $bankAccountCOA, 'debit', $amount);
        }

        return [
            [
                'account_id' => $bankFeeAccount->id,
                'account_code' => $bankFeeAccount->code,
                'account_name' => $bankFeeAccount->name,
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Bank fee: '.$statement->description,
            ],
            [
                'account_id' => $bankAccountCOA->id,
                'account_code' => $bankAccountCOA->code,
                'account_name' => $bankAccountCOA->name,
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Bank fee: '.$statement->description,
            ],
        ];
    }

    /**
     * Generate journal lines untuk bank interest
     */
    private function generateBankInterestLines(
        BankStatement $statement,
        ?ChartOfAccount $bankAccountCOA
    ): array {
        $amount = $statement->amount;

        // Cari akun pendapatan bunga
        $interestAccount = ChartOfAccount::where('tenant_id', $statement->tenant_id)
            ->where(function ($q) {
                $q->where('code', 'like', '4201%') // Interest income
                    ->orWhere('name', 'like', '%bunga%')
                    ->orWhere('name', 'like', '%jasa giro%')
                    ->orWhere('name', 'like', '%pendapatan bunga%');
            })
            ->where('is_active', true)
            ->where('is_header', false)
            ->first();

        if (! $interestAccount || ! $bankAccountCOA) {
            return $this->generateFallbackLines($statement, $bankAccountCOA, 'credit', $amount);
        }

        return [
            [
                'account_id' => $bankAccountCOA->id,
                'account_code' => $bankAccountCOA->code,
                'account_name' => $bankAccountCOA->name,
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Bank interest: '.$statement->description,
            ],
            [
                'account_id' => $interestAccount->id,
                'account_code' => $interestAccount->code,
                'account_name' => $interestAccount->name,
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Bank interest: '.$statement->description,
            ],
        ];
    }

    /**
     * Generate journal lines untuk unknown transaction
     */
    private function generateUnknownLines(
        BankStatement $statement,
        ?ChartOfAccount $bankAccountCOA
    ): array {
        $amount = $statement->amount;
        $type = $statement->type;

        // Gunakan suspense account
        $suspenseAccount = ChartOfAccount::where('tenant_id', $statement->tenant_id)
            ->where(function ($q) {
                $q->where('code', 'like', '1900%') // Suspense
                    ->orWhere('name', 'like', '%suspense%')
                    ->orWhere('name', 'like', '%sementara%');
            })
            ->where('is_active', true)
            ->where('is_header', false)
            ->first();

        if (! $suspenseAccount || ! $bankAccountCOA) {
            return $this->generateFallbackLines($statement, $bankAccountCOA, $type, $amount);
        }

        $lines = [];

        if ($type === 'credit') {
            $lines[] = [
                'account_id' => $bankAccountCOA->id,
                'account_code' => $bankAccountCOA->code,
                'account_name' => $bankAccountCOA->name,
                'debit' => $amount,
                'credit' => 0,
                'description' => '[REVIEW REQUIRED] '.$statement->description,
            ];

            $lines[] = [
                'account_id' => $suspenseAccount->id,
                'account_code' => $suspenseAccount->code,
                'account_name' => $suspenseAccount->name,
                'debit' => 0,
                'credit' => $amount,
                'description' => '[REVIEW REQUIRED] '.$statement->description,
            ];
        } else {
            $lines[] = [
                'account_id' => $suspenseAccount->id,
                'account_code' => $suspenseAccount->code,
                'account_name' => $suspenseAccount->name,
                'debit' => $amount,
                'credit' => 0,
                'description' => '[REVIEW REQUIRED] '.$statement->description,
            ];

            $lines[] = [
                'account_id' => $bankAccountCOA->id,
                'account_code' => $bankAccountCOA->code,
                'account_name' => $bankAccountCOA->name,
                'debit' => 0,
                'credit' => $amount,
                'description' => '[REVIEW REQUIRED] '.$statement->description,
            ];
        }

        return $lines;
    }

    /**
     * Fallback lines jika account tidak ditemukan
     */
    private function generateFallbackLines(
        BankStatement $statement,
        ?ChartOfAccount $bankAccountCOA,
        string $type,
        float $amount
    ): array {
        if (! $bankAccountCOA) {
            // Critical: tidak ada bank account COA
            return [];
        }

        $lines = [];

        if ($type === 'credit') {
            $lines[] = [
                'account_id' => $bankAccountCOA->id,
                'account_code' => $bankAccountCOA->code,
                'account_name' => $bankAccountCOA->name,
                'debit' => $amount,
                'credit' => 0,
                'description' => $statement->description,
            ];
        } else {
            $lines[] = [
                'account_id' => $bankAccountCOA->id,
                'account_code' => $bankAccountCOA->code,
                'account_name' => $bankAccountCOA->name,
                'debit' => 0,
                'credit' => $amount,
                'description' => $statement->description,
            ];
        }

        return $lines;
    }

    // ═══════════════════════════════════════════════════════════════
    // Helper Methods
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get bank account COA dari BankAccount
     */
    private function getBankAccountCOA(int $bankAccountId, int $tenantId): ?ChartOfAccount
    {
        $bankAccount = BankAccount::find($bankAccountId);
        if (! $bankAccount) {
            return null;
        }

        // Cari COA berdasarkan account number atau name
        return ChartOfAccount::where('tenant_id', $tenantId)
            ->where(function ($q) use ($bankAccount) {
                $q->where('code', 'like', '1101%') // Cash & Bank
                    ->orWhere('code', 'like', '1102%')
                    ->orWhere('name', 'like', '%'.$bankAccount->bank_name.'%')
                    ->orWhere('name', 'like', '%'.$bankAccount->account_number.'%');
            })
            ->where('is_active', true)
            ->where('is_header', false)
            ->first();
    }

    /**
     * Calculate confidence level
     */
    private function calculateConfidence(string $category, array $lines): string
    {
        if (empty($lines)) {
            return 'low';
        }

        $hasValidAccounts = collect($lines)->every(fn ($line) => ! empty($line['account_id']));

        if (! $hasValidAccounts) {
            return 'low';
        }

        return match ($category) {
            'bank_fee', 'bank_interest' => 'high',
            'bank_transfer' => 'medium',
            'standard' => 'medium',
            'unknown' => 'low',
            default => 'low',
        };
    }

    /**
     * Get AI basis explanation
     */
    private function getAIBasis(string $category, BankStatement $statement): string
    {
        return match ($category) {
            'bank_transfer' => 'Transfer antar rekening terdeteksi dari deskripsi',
            'bank_fee' => 'Biaya admin bank terdeteksi dari kata kunci',
            'bank_interest' => 'Bunga bank/jasa giro terdeteksi dari kata kunci',
            'unknown' => 'Transaksi tidak dikenal, menggunakan suspense account - PERLU REVIEW MANUAL',
            'standard' => 'Kategorisasi berdasarkan AI pattern matching',
            default => 'Auto-generated journal',
        };
    }

    /**
     * Generate warnings
     */
    private function generateWarnings(string $category, array $lines): array
    {
        $warnings = [];

        if ($category === 'unknown') {
            $warnings[] = 'Transaksi tidak dikenal - perlu review manual';
        }

        $hasInvalidAccount = collect($lines)->contains(fn ($line) => empty($line['account_id']));
        if ($hasInvalidAccount) {
            $warnings[] = 'Ada account yang tidak ditemukan - menggunakan fallback';
        }

        return $warnings;
    }

    /**
     * Create journal entry from preview DTO
     */
    private function createJournalFromPreview(JournalPreviewDTO $preview, int $userId): JournalEntry
    {
        // Get tenant_id dari statement atau fallback
        $tenantId = $preview->bankStatementId
            ? BankStatement::find($preview->bankStatementId)?->tenant_id
            : null;

        if (! $tenantId) {
            throw new \Exception('Tenant ID tidak ditemukan untuk journal preview');
        }

        // Get accounting period
        $period = AccountingPeriod::where('tenant_id', $tenantId)
            ->where('period', '<=', $preview->date)
            ->where('is_open', true)
            ->orderByDesc('period')
            ->first();

        // Generate journal number
        $journalNumber = $this->docNumberService->generate(
            $tenantId,
            'je',
            'AUTO'
        );

        // Create journal entry
        $journal = JournalEntry::create([
            'tenant_id' => $tenantId,
            'period_id' => $period?->id,
            'user_id' => $userId,
            'number' => $journalNumber,
            'date' => $preview->date,
            'description' => $preview->description,
            'reference' => $preview->reference,
            'reference_type' => 'bank_statement',
            'reference_id' => $preview->bankStatementId,
            'currency_code' => 'IDR',
            'currency_rate' => 1.0,
            'status' => 'draft',
        ]);

        // Create journal lines
        foreach ($preview->lines as $lineData) {
            JournalEntryLine::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $lineData['account_id'],
                'debit' => $lineData['debit'] ?? 0,
                'credit' => $lineData['credit'] ?? 0,
                'description' => $lineData['description'] ?? $preview->description,
            ]);
        }

        return $journal;
    }
}
