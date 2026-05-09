<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTransaction extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'program_id',
        'type',
        'points',
        'balance_after', // BUG-CRM-003 FIX: Track balance after transaction
        'transaction_amount',
        'reference',
        'notes',
        'expires_at',
    ];

    protected $casts = ['expires_at' => 'datetime', 'transaction_amount' => 'float'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
