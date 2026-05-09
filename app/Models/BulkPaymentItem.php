<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BulkPaymentItem extends Model
{
    protected $fillable = ['bulk_payment_id', 'payable_id', 'payable_type', 'amount'];

    protected $casts = ['amount' => 'decimal:2'];

    public function bulkPayment(): BelongsTo
    {
        return $this->belongsTo(BulkPayment::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}
