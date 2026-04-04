<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RatePlan extends Model
{
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'room_type_id',
        'name',
        'code',
        'description',
        'type',
        'base_rate',
        'min_stay',
        'max_stay',
        'advance_booking_days',
        'is_refundable',
        'cancellation_hours',
        'includes_breakfast',
        'inclusions',
        'is_active',
        'valid_from',
        'valid_to',
    ];

    protected function casts(): array
    {
        return [
            'base_rate' => 'decimal:2',
            'min_stay' => 'integer',
            'max_stay' => 'integer',
            'advance_booking_days' => 'integer',
            'is_refundable' => 'boolean',
            'cancellation_hours' => 'integer',
            'includes_breakfast' => 'boolean',
            'inclusions' => 'array',
            'is_active' => 'boolean',
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(DynamicPricingRule::class);
    }

    /**
     * Get the effective rate for a specific date after applying rules
     */
    public function getEffectiveRate(\Carbon\Carbon $date): float
    {
        $rate = $this->base_rate;

        // Apply active pricing rules
        $rules = $this->pricingRules()
            ->where('is_active', true)
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $date);
            })
            ->orderBy('priority')
            ->get();

        foreach ($rules as $rule) {
            if ($rule->matchesConditions($date)) {
                if ($rule->adjustment_type === 'percentage') {
                    $rate = $rate * (1 + ($rule->adjustment_value / 100));
                } else {
                    $rate = $rate + $rule->adjustment_value;
                }
            }
        }

        return max(0, round($rate, 2));
    }

    /**
     * Check if rate plan is valid for given date
     */
    public function isValidForDate(\Carbon\Carbon $date): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->valid_from && $date->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_to && $date->gt($this->valid_to)) {
            return false;
        }

        return true;
    }

    /**
     * Calculate total for stay duration
     */
    public function calculateTotal(\Carbon\Carbon $checkIn, \Carbon\Carbon $checkOut): float
    {
        $nights = $checkIn->diffInDays($checkOut);
        $total = 0;

        for ($i = 0; $i < $nights; $i++) {
            $date = $checkIn->copy()->addDays($i);
            $total += $this->getEffectiveRate($date);
        }

        return round($total, 2);
    }
}
