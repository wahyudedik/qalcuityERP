<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FbPayment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'fb_order_id',
        'payment_number',
        'amount',
        'payment_method',
        'status',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(FbOrder::class, 'fb_order_id');
    }
}
