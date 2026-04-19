<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoultryEggProduction extends Model
{
use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'livestock_herd_id',
        'record_date',
        'eggs_collected',
        'eggs_broken',
        'eggs_double_yolk',
        'eggs_small',
        'eggs_medium',
        'eggs_large',
        'eggs_extra_large',
        'total_weight_kg',
        'laying_rate_percentage',
        'feed_consumed_kg',
        'feed_conversion_ratio',
        'notes',
        'recorded_by'
    ];

    protected $casts = [
        'record_date' => 'date',
        'eggs_collected' => 'integer',
        'eggs_broken' => 'integer',
        'eggs_double_yolk' => 'integer',
        'eggs_small' => 'integer',
        'eggs_medium' => 'integer',
        'eggs_large' => 'integer',
        'eggs_extra_large' => 'integer',
        'total_weight_kg' => 'decimal:2',
        'laying_rate_percentage' => 'decimal:2',
        'feed_consumed_kg' => 'decimal:2',
        'feed_conversion_ratio' => 'decimal:3',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function herd(): BelongsTo
    {
        return $this->belongsTo(LivestockHerd::class, 'livestock_herd_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Calculate good eggs (not broken)
     */
    public function getGoodEggsAttribute(): int
    {
        return $this->eggs_collected - $this->eggs_broken;
    }

    /**
     * Calculate breakage rate percentage
     */
    public function getBreakageRateAttribute(): float
    {
        if ($this->eggs_collected == 0) {
            return 0;
        }

        return round(($this->eggs_broken / $this->eggs_collected) * 100, 2);
    }

    /**
     * Check if laying rate is good (>75% is excellent for layers)
     */
    public function isGoodLayingRate(): bool
    {
        return $this->laying_rate_percentage >= 75;
    }

    /**
     * Get FCR interpretation
     */
    public function getFcrInterpretationAttribute(): string
    {
        if (!$this->feed_conversion_ratio) {
            return 'N/A';
        }

        if ($this->feed_conversion_ratio <= 2.0) {
            return 'Excellent';
        } elseif ($this->feed_conversion_ratio <= 2.5) {
            return 'Good';
        } elseif ($this->feed_conversion_ratio <= 3.0) {
            return 'Average';
        } else {
            return 'Poor';
        }
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('record_date', $date);
    }

    public function scopeByHerd($query, $herdId)
    {
        return $query->where('livestock_herd_id', $herdId);
    }

    public function scopeHighProduction($query)
    {
        return $query->where('laying_rate_percentage', '>=', 80);
    }
}