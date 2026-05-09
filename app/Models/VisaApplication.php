<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VisaApplication extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'tour_booking_id',
        'passenger_id',
        'application_number',
        'destination_country',
        'visa_type',
        'applicant_name',
        'passport_number',
        'passport_expiry',
        'application_date',
        'intended_travel_date',
        'status',
        'submission_date',
        'approval_date',
        'expiry_date',
        'fee_amount',
        'currency',
        'requirements_checklist',
        'notes',
        'agent_id',
    ];

    protected $casts = [
        'passport_expiry' => 'date',
        'application_date' => 'date',
        'intended_travel_date' => 'date',
        'submission_date' => 'date',
        'approval_date' => 'date',
        'expiry_date' => 'date',
        'fee_amount' => 'decimal:2',
        'requirements_checklist' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(TourBooking::class, 'tour_booking_id');
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(BookingPassenger::class, 'passenger_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function documents()
    {
        return $this->hasMany(TravelDocument::class, 'visa_application_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'preparing' => 'gray',
            'submitted' => 'blue',
            'processing' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            'expired' => 'gray',
            default => 'gray'
        };
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isExpired(): bool
    {
        if (! $this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast();
    }

    public function daysUntilExpiry(): ?int
    {
        if (! $this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }
}
