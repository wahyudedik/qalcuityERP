<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpaTherapist extends Model
{
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'employee_number',
        'name',
        'phone',
        'email',
        'specializations',
        'status',
        'hourly_rate',
        'rating',
        'total_treatments',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'specializations' => 'array',
            'hourly_rate' => 'decimal:2',
            'rating' => 'integer',
            'total_treatments' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(SpaBooking::class, 'therapist_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(TherapistSchedule::class, 'therapist_id');
    }

    public function timeOffRequests(): HasMany
    {
        return $this->hasMany(TherapistTimeOff::class, 'therapist_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(SpaReview::class, 'therapist_id');
    }

    /**
     * Check if therapist is available at given time
     */
    public function isAvailableAt(string $date, string $startTime, string $endTime): bool
    {
        if ($this->status !== 'available') {
            return false;
        }

        // Check for existing bookings
        $conflictingBooking = SpaBooking::where('therapist_id', $this->id)
            ->where('booking_date', $date)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->exists();

        return !$conflictingBooking;
    }

    /**
     * Get today's bookings
     */
    public function getTodayBookings()
    {
        return $this->bookings()
            ->where('booking_date', today())
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Calculate commission for a booking
     */
    public function calculateCommission(float $amount): float
    {
        return $amount * ($this->hourly_rate / 100);
    }

    /**
     * Increment total treatments count
     */
    public function incrementTreatments(int $count = 1): void
    {
        $this->increment('total_treatments', $count);
    }

    /**
     * Update average rating
     */
    public function updateRating(): void
    {
        $avgRating = $this->reviews()
            ->where('is_published', true)
            ->avg('rating');

        $this->update(['rating' => round($avgRating ?? 0)]);
    }
}
