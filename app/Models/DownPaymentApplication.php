<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DownPaymentApplication extends Model
{
    protected $fillable = [
        'down_payment_id', 'invoice_id', 'invoice_type',
        'amount', 'applied_date', 'applied_by', 'notes',
    ];

    protected $casts = [
        'applied_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function downPayment(): BelongsTo
    {
        return $this->belongsTo(DownPayment::class);
    }

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }
}
