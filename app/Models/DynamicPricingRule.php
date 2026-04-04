<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DynamicPricingRule extends Model
{
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'rate_plan_id',
        'name',
        'description',
        'rule_type',
        'conditions',
        'adjustment_type',
        'adjustment_value',
        'priority',
        'is_active',
        'valid_from',
        'valid_to',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'adjustment_value' => 'decimal:2',
            'is_active' => 'boolean',
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }

    /**
     * Check if rule conditions match for given date
     */
    public function matchesConditions(\Carbon\Carbon $date): bool
    {
        $conditions = $this->conditions ?? [];

        switch ($this->rule_type) {
            case 'occupancy_based':
                return $this->checkOccupancyCondition($conditions);

            case 'seasonal':
                return $this->checkSeasonalCondition($conditions, $date);

            case 'day_of_week':
                return $this->checkDayOfWeekCondition($conditions, $date);

            case 'length_of_stay':
                return $this->checkLengthOfStayCondition($conditions);

            case 'advance_booking':
                return $this->checkAdvanceBookingCondition($conditions, $date);

            case 'event_based':
                return $this->checkEventCondition($conditions, $date);

            default:
                return true;
        }
    }

    private function checkOccupancyCondition(array $conditions): bool
    {
        // This would need occupancy data - simplified version
        return true;
    }

    private function checkSeasonalCondition(array $conditions, \Carbon\Carbon $date): bool
    {
        $seasonStart = $conditions['season_start'] ?? null;
        $seasonEnd = $conditions['season_end'] ?? null;

        if (!$seasonStart || !$seasonEnd) {
            return false;
        }

        return $date->between($seasonStart, $seasonEnd);
    }

    private function checkDayOfWeekCondition(array $conditions, \Carbon\Carbon $date): bool
    {
        $days = $conditions['days'] ?? []; // ['monday', 'friday', 'saturday']

        if (empty($days)) {
            return false;
        }

        return in_array(strtolower($date->format('l')), $days);
    }

    private function checkLengthOfStayCondition(array $conditions): bool
    {
        // This needs to be checked against reservation
        return true;
    }

    private function checkAdvanceBookingCondition(array $conditions, \Carbon\Carbon $date): bool
    {
        $minDays = $conditions['min_days'] ?? 0;
        $maxDays = $conditions['max_days'] ?? 999;

        $daysUntil = now()->diffInDays($date, false);

        return $daysUntil >= $minDays && $daysUntil <= $maxDays;
    }

    private function checkEventCondition(array $conditions, \Carbon\Carbon $date): bool
    {
        $eventId = $conditions['event_id'] ?? null;

        if (!$eventId) {
            return false;
        }

        $event = SpecialEvent::find($eventId);

        return $event && $date->between($event->start_date, $event->end_date);
    }

    /**
     * Calculate adjusted rate
     */
    public function applyAdjustment(float $baseRate): float
    {
        if ($this->adjustment_type === 'percentage') {
            return $baseRate * (1 + ($this->adjustment_value / 100));
        } else {
            return $baseRate + $this->adjustment_value;
        }
    }
}
