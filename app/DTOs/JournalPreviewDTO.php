<?php

namespace App\DTOs;

/**
 * JournalPreviewDTO - Data Transfer Object untuk preview journal entry
 * 
 * Digunakan untuk menampilkan preview journal sebelum di-approve dan di-save ke database
 */
class JournalPreviewDTO
{
    public function __construct(
        // Journal header info
        public string $date,
        public string $description,
        public string $reference,
        public string $journalType, // 'bank_statement', 'bank_transfer', 'bank_fee', 'bank_interest'

        // Journal lines
        public array $lines, // [['account_id', 'account_code', 'account_name', 'debit', 'credit', 'description']]

        // AI info
        public string $confidence, // 'high', 'medium', 'low'
        public string $aiBasis,
        public array $warnings = [],

        // Metadata
        public ?int $bankStatementId = null,
        public ?int $bankAccountId = null,
        public float $totalDebit = 0.0,
        public float $totalCredit = 0.0,
        public bool $isBalanced = true,
    ) {
        // Calculate totals
        $this->totalDebit = array_sum(array_column($lines, 'debit'));
        $this->totalCredit = array_sum(array_column($lines, 'credit'));
        $this->isBalanced = abs($this->totalDebit - $this->totalCredit) < 0.01;
    }

    /**
     * Convert to array for JSON response
     */
    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'description' => $this->description,
            'reference' => $this->reference,
            'journal_type' => $this->journalType,
            'lines' => $this->lines,
            'confidence' => $this->confidence,
            'ai_basis' => $this->aiBasis,
            'warnings' => $this->warnings,
            'bank_statement_id' => $this->bankStatementId,
            'bank_account_id' => $this->bankAccountId,
            'total_debit' => $this->totalDebit,
            'total_credit' => $this->totalCredit,
            'is_balanced' => $this->isBalanced,
        ];
    }

    /**
     * Validate journal preview
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->lines)) {
            $errors[] = 'Journal lines tidak boleh kosong';
        }

        if (!$this->isBalanced) {
            $errors[] = 'Journal tidak balance: Debit ≠ Credit';
        }

        if ($this->totalDebit <= 0) {
            $errors[] = 'Total debit harus lebih dari 0';
        }

        foreach ($this->lines as $index => $line) {
            if (empty($line['account_id'])) {
                $errors[] = "Line #" . ($index + 1) . ": Account tidak valid";
            }

            if (($line['debit'] ?? 0) <= 0 && ($line['credit'] ?? 0) <= 0) {
                $errors[] = "Line #" . ($index + 1) . ": Debit atau Credit harus lebih dari 0";
            }
        }

        return $errors;
    }
}
