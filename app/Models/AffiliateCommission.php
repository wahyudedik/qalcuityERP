<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateCommission extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'affiliate_id', 'tenant_id', 'subscription_payment_id',
        'plan_name', 'payment_amount', 'commission_rate', 'commission_amount',
        'status', 'approved_by', 'approved_at', 'paid_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'payment_amount'   => 'decimal:2',
            'commission_rate'  => 'decimal:2',
            'commission_amount'=> 'decimal:2',
            'approved_at'      => 'datetime',
            'paid_at'          => 'datetime',
        ];
    }

    public function affiliate(): BelongsTo { return $this->belongsTo(Affiliate::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
}
