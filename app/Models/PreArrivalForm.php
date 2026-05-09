<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreArrivalForm extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'reservation_id',
        'guest_id',
        'id_number',
        'id_type',
        'id_expiry',
        'nationality',
        'date_of_birth',
        'gender',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'room_preference',
        'bed_preference',
        'special_requests',
        'dietary_requirements',
        'amenities_requested',
        'estimated_arrival_time',
        'transportation_method',
        'flight_number',
        'airport_pickup_required',
        'terms_accepted',
        'marketing_consent',
        'data_processing_consent',
        'status',
        'submitted_at',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'id_expiry' => 'date',
        'date_of_birth' => 'date',
        'special_requests' => 'array',
        'amenities_requested' => 'array',
        'airport_pickup_required' => 'boolean',
        'terms_accepted' => 'boolean',
        'marketing_consent' => 'boolean',
        'data_processing_consent' => 'boolean',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the form
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the reservation
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the guest
     */
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Get the verifier
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Mark form as submitted
     */
    public function markAsSubmitted(): void
    {
        $this->update([
            'status' => 'completed',
            'submitted_at' => now(),
        ]);
    }

    /**
     * Mark form as verified
     */
    public function markAsVerified(int $userId): void
    {
        $this->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $userId,
        ]);
    }

    /**
     * Check if form is complete
     */
    public function isComplete(): bool
    {
        return $this->submitted_at !== null;
    }

    /**
     * Check if form is verified
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'gray',
            'completed' => 'blue',
            'verified' => 'green',
            default => 'gray',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'completed' => 'Completed',
            'verified' => 'Verified',
            default => ucfirst($this->status),
        };
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Submitted forms only
     */
    public function scopeSubmitted($query)
    {
        return $query->whereNotNull('submitted_at');
    }

    /**
     * Scope: Pending verification
     */
    public function scopePendingVerification($query)
    {
        return $query->where('status', 'completed');
    }
}
