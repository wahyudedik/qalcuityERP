<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicPricingRule extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'conditions',
        'pricing_formula',
        'min_price_multiplier',
        'max_price_multiplier',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'conditions' => 'array',
        'pricing_formula' => 'array',
        'min_price_multiplier' => 'decimal:2',
        'max_price_multiplier' => 'decimal:2',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function pricingHistories()
    {
        return $this->hasMany(DynamicPricingHistory::class);
    }
}
