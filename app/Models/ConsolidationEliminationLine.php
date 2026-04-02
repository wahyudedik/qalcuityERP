<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsolidationEliminationLine extends Model
{
    protected $fillable = [
        'elimination_id',
        'master_account_id',
        'debit',
        'credit',
        'description',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function elimination(): BelongsTo
    {
        return $this->belongsTo(ConsolidationElimination::class);
    }

    public function masterAccount(): BelongsTo
    {
        return $this->belongsTo(ConsolidationMasterAccount::class);
    }
}
