<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    protected $fillable = [
        'journal_entry_id', 'account_id',
        'debit', 'credit', 'foreign_amount',
        'description', 'cost_center_id',
    ];

    protected $casts = [
        'debit'          => 'decimal:2',
        'credit'         => 'decimal:2',
        'foreign_amount' => 'decimal:2',
    ];

    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }
    public function account(): BelongsTo { return $this->belongsTo(ChartOfAccount::class, 'account_id'); }
}
