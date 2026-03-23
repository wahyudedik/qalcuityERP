<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Writeoff extends Model
{
    protected $fillable = [
        'tenant_id', 'requested_by', 'approved_by', 'number', 'type',
        'reference_type', 'reference_id', 'reference_number',
        'original_amount', 'writeoff_amount', 'reason',
        'status', 'rejection_reason', 'journal_entry_id',
        'approved_at', 'posted_at',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'writeoff_amount' => 'decimal:2',
        'approved_at'     => 'datetime',
        'posted_at'       => 'datetime',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }
    public function reference(): MorphTo { return $this->morphTo('reference'); }

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isPosted(): bool   { return $this->status === 'posted'; }

    public function typeLabel(): string
    {
        return $this->type === 'receivable' ? 'Piutang' : 'Hutang';
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending'  => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
            'approved' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
            'posted'   => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
            'rejected' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
            default    => 'bg-gray-100 text-gray-500',
        };
    }
}
