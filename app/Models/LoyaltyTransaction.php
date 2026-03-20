<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyTransaction extends Model
{
    protected $fillable = [
        'tenant_id', 'customer_id', 'program_id', 'type', 'points',
        'transaction_amount', 'reference', 'notes', 'expires_at',
    ];

    protected $casts = ['expires_at' => 'datetime', 'transaction_amount' => 'float'];

    public function customer() { return $this->belongsTo(Customer::class); }
}
