<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerSubscriptionPlan extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'code', 'description', 'price',
        'billing_cycle', 'trial_days', 'is_active', 'features',
    ];

    protected function casts(): array
    {
        return [
            'price'     => 'decimal:2',
            'is_active' => 'boolean',
            'features'  => 'array',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function subscriptions(): HasMany { return $this->hasMany(CustomerSubscription::class, 'plan_id'); }

    public function cycleLabel(): string
    {
        return ['monthly'=>'Bulanan','quarterly'=>'Triwulan','semi_annual'=>'Semester','annual'=>'Tahunan'][$this->billing_cycle] ?? $this->billing_cycle;
    }
}
