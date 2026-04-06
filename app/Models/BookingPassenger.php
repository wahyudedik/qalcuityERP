<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPassenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_booking_id',
        'full_name',
        'passport_number',
        'passport_expiry',
        'nationality',
        'date_of_birth',
        'gender',
        'phone',
        'email',
        'type',
        'dietary_requirements',
        'medical_conditions',
        'special_assistance',
    ];

    protected $casts = [
        'passport_expiry' => 'date',
        'date_of_birth' => 'date',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(TourBooking::class, 'tour_booking_id');
    }

    public function visaApplications()
    {
        return $this->hasMany(VisaApplication::class, 'passenger_id');
    }

    public function documents()
    {
        return $this->hasMany(TravelDocument::class, 'passenger_id');
    }

    public function getAgeAttribute(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }

        return $this->date_of_birth->age;
    }

    public function isPassportValid(): bool
    {
        if (!$this->passport_expiry) {
            return false;
        }

        // Passport should be valid for at least 6 months after travel
        return $this->passport_expiry->gt(now()->addMonths(6));
    }
}
