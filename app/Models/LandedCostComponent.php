<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandedCostComponent extends Model
{
    protected $fillable = [
        'landed_cost_id', 'name', 'type', 'amount', 'vendor', 'reference',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function landedCost(): BelongsTo { return $this->belongsTo(LandedCost::class); }
}
