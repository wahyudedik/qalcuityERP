<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandedCostAllocation extends Model
{
    protected $fillable = [
        'landed_cost_id', 'product_id', 'original_cost', 'quantity',
        'weight', 'allocated_cost', 'landed_unit_cost',
    ];

    protected function casts(): array
    {
        return [
            'original_cost' => 'decimal:2',
            'quantity' => 'decimal:3',
            'weight' => 'decimal:3',
            'allocated_cost' => 'decimal:2',
            'landed_unit_cost' => 'decimal:2',
        ];
    }

    public function landedCost(): BelongsTo
    {
        return $this->belongsTo(LandedCost::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
