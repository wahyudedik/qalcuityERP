<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicPricingHistory extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'rule_id',
        'original_price',
        'recommended_price',
        'applied_price',
        'factors',
        'reason',
        'approved',
        'approved_by_user_id',
        'approved_at',
    ];

    protected $casts = [
        'original_price' => 'decimal:2',
        'recommended_price' => 'decimal:2',
        'applied_price' => 'decimal:2',
        'factors' => 'array',
        'approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function rule()
    {
        return $this->belongsTo(DynamicPricingRule::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
