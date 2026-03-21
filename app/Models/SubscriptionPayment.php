<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'tenant_id', 'plan_id', 'order_id', 'amount', 'billing',
        'gateway', 'status', 'gateway_token', 'gateway_url', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'  => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function plan(): BelongsTo   { return $this->belongsTo(SubscriptionPlan::class, 'plan_id'); }
}
