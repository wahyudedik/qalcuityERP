<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerSubscription extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'customer_id', 'plan_id', 'subscription_number',
        'start_date', 'end_date', 'trial_ends_at', 'next_billing_date',
        'price_override', 'discount_pct', 'auto_renew', 'status',
        'cancel_reason', 'user_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date'       => 'date',
            'end_date'         => 'date',
            'trial_ends_at'    => 'date',
            'next_billing_date'=> 'date',
            'price_override'   => 'decimal:2',
            'discount_pct'     => 'decimal:2',
            'auto_renew'       => 'boolean',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function plan(): BelongsTo { return $this->belongsTo(CustomerSubscriptionPlan::class, 'plan_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function invoices(): HasMany { return $this->hasMany(SubscriptionInvoice::class, 'subscription_id'); }

    public function effectivePrice(): float
    {
        $base = $this->price_override ?? $this->plan->price ?? 0;
        $discount = round($base * $this->discount_pct / 100, 2);
        return $base - $discount;
    }

    public function isTrialing(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function mrr(): float
    {
        $price = $this->effectivePrice();
        return match($this->plan->billing_cycle ?? 'monthly') {
            'monthly'     => $price,
            'quarterly'   => round($price / 3, 2),
            'semi_annual' => round($price / 6, 2),
            'annual'      => round($price / 12, 2),
            default       => $price,
        };
    }

    public static function generateNumber(int $tenantId): string
    {
        $count = self::where('tenant_id', $tenantId)->count() + 1;
        return 'SUB-' . date('Ym') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
