<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpaTreatment extends Model
{
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'category',
        'duration_minutes',
        'price',
        'cost',
        'image_path',
        'benefits',
        'requires_consultation',
        'preparation_time',
        'cleanup_time',
        'max_daily_bookings',
        'booked_today',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
            'benefits' => 'array',
            'requires_consultation' => 'boolean',
            'preparation_time' => 'integer',
            'cleanup_time' => 'integer',
            'max_daily_bookings' => 'integer',
            'booked_today' => 'integer',
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(SpaBooking::class, 'treatment_id');
    }

    public function packageItems(): HasMany
    {
        return $this->hasMany(SpaPackageItem::class, 'treatment_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(SpaReview::class, 'treatment_id');
    }

    /**
     * Calculate profit margin
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->price == 0) {
            return 0;
        }
        return (($this->price - $this->cost) / $this->price) * 100;
    }

    /**
     * Check if treatment can be booked today
     */
    public function canBeBookedToday(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->max_daily_bookings && $this->booked_today >= $this->max_daily_bookings) {
            return false;
        }

        return true;
    }

    /**
     * Get total duration including prep and cleanup
     */
    public function getTotalDurationAttribute(): int
    {
        return $this->duration_minutes + $this->preparation_time + $this->cleanup_time;
    }

    /**
     * Increment daily booking count
     */
    public function incrementBookedToday(int $count = 1): void
    {
        $this->increment('booked_today', $count);
    }

    /**
     * Reset daily booking counter
     */
    public static function resetDailyCounters(int $tenantId): void
    {
        static::where('tenant_id', $tenantId)->update(['booked_today' => 0]);
    }
}
