<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoultryFlockPerformance extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'livestock_herd_id',
        'record_date',
        'birds_alive',
        'mortality_count',
        'mortality_rate_percentage',
        'average_weight_kg',
        'feed_consumed_kg',
        'water_consumed_liters',
        'average_daily_gain',
        'feed_conversion_ratio',
        'health_status',
        'observations',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'record_date' => 'date',
            'mortality_rate_percentage' => 'decimal:2',
            'average_weight_kg' => 'decimal:3',
            'feed_consumed_kg' => 'decimal:2',
            'water_consumed_liters' => 'decimal:2',
            'average_daily_gain' => 'decimal:3',
            'feed_conversion_ratio' => 'decimal:2',
        ];
    }

    public function herd(): BelongsTo
    {
        return $this->belongsTo(LivestockHerd::class, 'livestock_herd_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
