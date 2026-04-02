<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsolidationAdjustmentLine extends Model
{
    protected $fillable = [
        'adjustment_id',
        'master_account_id',
        'debit',
        'credit',
        'description',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(ConsolidationAdjustment::class);
    }

    public function masterAccount(): BelongsTo
    {
        return $this->belongsTo(ConsolidationMasterAccount::class);
    }
}
