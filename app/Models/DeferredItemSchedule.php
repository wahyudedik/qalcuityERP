<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeferredItemSchedule extends Model
{
    protected $fillable = [
        'deferred_item_id', 'period_number', 'recognition_date',
        'amount', 'status', 'journal_entry_id',
    ];

    protected $casts = [
        'recognition_date' => 'date',
        'amount'           => 'decimal:2',
    ];

    public function deferredItem(): BelongsTo { return $this->belongsTo(DeferredItem::class); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }

    public function isPending(): bool { return $this->status === 'pending'; }
    public function isPosted(): bool  { return $this->status === 'posted'; }
}
