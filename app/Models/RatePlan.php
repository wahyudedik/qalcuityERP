<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RatePlan extends Model
{
    use BelongsToTenant;
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
     * BUG-HOTEL-004 FIX: Check if this rate plan overlaps with another rate plan
     * 
     * @param RatePlan $other The other rate plan to check against
     * @return bool True if there is an overlap
     */
    public function overlapsWith(RatePlan $other): bool
    {
        // Different room types can have overlapping dates (that's fine)
        if ($this->room_type_id !== $other->room_type_id) {
            return false;
        }

        // Same rate plan (no self-comparison)
        if ($this->id === $other->id) {
            return false;
        }

        // If either has no date range, they overlap (infinite range)
        if (!$this->valid_from && !$this->valid_to) {
            return true;
        }

        if (!$other->valid_from && !$other->valid_to) {
            return true;
        }

        // Check date overlap logic
        // Two ranges [A, B] and [C, D] overlap if: A <= D AND C <= B
        $thisStart = $this->valid_from ?? now()->subYears(10);
        $thisEnd = $this->valid_to ?? now()->addYears(10);
        $otherStart = $other->valid_from ?? now()->subYears(10);
        $otherEnd = $other->valid_to ?? now()->addYears(10);

        return $thisStart <= $otherEnd && $otherStart <= $thisEnd;
    }

    /**
     * BUG-HOTEL-004 FIX: Find all overlapping rate plans for the same room type
     * 
     * @param int $tenantId Tenant ID
     * @param int $roomTypeId Room type ID
     * @param string|null $validFrom Start date
     * @param string|null $validTo End date
     * @param int|null $excludeId Exclude specific rate plan ID (for updates)
     * @return \Illuminate\Support\Collection Collection of overlapping rate plans
     */
    public static function findOverlapping(
        int $tenantId,
        int $roomTypeId,
        ?string $validFrom = null,
        ?string $validTo = null,
        ?int $excludeId = null
    ): \Illuminate\Support\Collection {
        // If no date range specified, it overlaps with everything
        if (!$validFrom && !$validTo) {
            $query = self::where('tenant_id', $tenantId)
                ->where('room_type_id', $roomTypeId)
                ->where('is_active', true);
        } else {
            // Check for date overlap
            // Overlap condition: (start1 <= end2) AND (start2 <= end1)
            $query = self::where('tenant_id', $tenantId)
                ->where('room_type_id', $roomTypeId)
                ->where('is_active', true)
                ->where(function ($q) use ($validFrom, $validTo) {
                    // Case 1: Other plan has no date range (infinite)
                    $q->where(function ($q2) {
                        $q2->whereNull('valid_from')
                            ->whereNull('valid_to');
                    })
                        // Case 2: Date ranges overlap
                        ->orWhere(function ($q2) use ($validFrom, $validTo) {
                        if ($validFrom) {
                            // Other's end >= our start
                            $q2->where(function ($q3) use ($validFrom) {
                                $q3->whereNull('valid_to')
                                    ->orWhere('valid_to', '>=', $validFrom);
                            });
                        }

                        if ($validTo) {
                            // Other's start <= our end
                            $q2->where(function ($q3) use ($validTo) {
                                $q3->whereNull('valid_from')
                                    ->orWhere('valid_from', '<=', $validTo);
                            });
                        }
                    });
                });
        }

        // Exclude specific ID (useful for updates)
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get();
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
