<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliatePayout extends Model
{
    protected $fillable = [
        'affiliate_id', 'requested_by', 'amount', 'payment_method', 'reference',
        'status', 'requested_at', 'processed_by', 'processed_at', 'notes', 'reject_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function affiliate(): BelongsTo { return $this->belongsTo(Affiliate::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function processor(): BelongsTo { return $this->belongsTo(User::class, 'processed_by'); }
}
