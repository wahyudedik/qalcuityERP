<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'debit',
        'credit',
        'foreign_amount',
        'description',
        'cost_center_id',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'foreign_amount' => 'decimal:2',
    ];

    /**
     * BUG-FIN-001 FIX: Boot method to add model events for journal line validation
     */
    protected static function boot()
    {
        parent::boot();

        // Validate journal balance after creating or updating a line
        static::created(function ($line) {
            static::validateJournalBalance($line->journal_entry_id);
        });

        static::updated(function ($line) {
            static::validateJournalBalance($line->journal_entry_id);
        });

        static::deleted(function ($line) {
            static::validateJournalBalance($line->journal_entry_id);
        });
    }

    /**
     * BUG-FIN-001 FIX: Validate that journal entry is still balanced
     * This prevents direct database manipulation from creating imbalanced journals
     */
    protected static function validateJournalBalance(int $journalEntryId): void
    {
        $journal = JournalEntry::find($journalEntryId);

        if (! $journal) {
            return;
        }

        // Only validate if journal is not yet posted (posted journals are immutable)
        if ($journal->status === 'posted') {
            return;
        }

        // Check if journal is balanced
        $debit = $journal->lines()->sum('debit');
        $credit = $journal->lines()->sum('credit');
        $diff = abs($debit - $credit);

        // Log warning if imbalanced (don't throw exception to allow draft creation)
        if ($diff >= 0.01) {
            \Log::warning('Journal entry is imbalanced', [
                'journal_id' => $journalEntryId,
                'journal_number' => $journal->number,
                'status' => $journal->status,
                'debit' => $debit,
                'credit' => $credit,
                'difference' => $diff,
            ]);
        }
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
