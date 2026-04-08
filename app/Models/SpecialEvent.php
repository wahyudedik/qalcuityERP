<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpecialEvent extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'impact_level',
        'expected_demand_increase',
        'affects_pricing',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'expected_demand_increase' => 'decimal:2',
            'affects_pricing' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Check if event is active on given date
     */
    public function isActiveOn(\Carbon\Carbon $date): bool
    {
        return $date->between($this->start_date, $this->end_date);
    }

    /**
     * Get events affecting a date range
     */
    public static function getEventsForPeriod(int $tenantId, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate)
    {
        return static::where('tenant_id', $tenantId)
            ->where('affects_pricing', true)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->orderBy('impact_level')
            ->get();
    }

    /**
     * Get impact multiplier for pricing
     */
    public function getImpactMultiplier(): float
    {
        return match ($this->impact_level) {
            'low' => 1.05,
            'medium' => 1.15,
            'high' => 1.30,
            'very_high' => 1.50,
            default => 1.0,
        };
    }
}
