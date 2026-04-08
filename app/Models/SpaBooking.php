<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpaBooking extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'booking_number',
        'guest_id',
        'room_number',
        'reservation_id',
        'therapist_id',
        'treatment_id',
        'package_id',
        'booking_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'amount',
        'tax_amount',
        'service_charge',
        'total_amount',
        'status',
        'special_requests',
        'therapist_notes',
        'cancellation_reason',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'duration_minutes' => 'integer',
            'amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'service_charge' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = static::generateBookingNumber($booking->tenant_id);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(HotelGuest::class, 'guest_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class, 'room_number', 'room_number');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(HotelReservation::class, 'reservation_id');
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(SpaTherapist::class, 'therapist_id');
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(SpaTreatment::class, 'treatment_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(SpaPackage::class, 'package_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SpaBookingItem::class, 'booking_id')->orderBy('sequence_order');
    }

    public function productSales(): HasMany
    {
        return $this->hasMany(SpaProductSale::class, 'booking_id');
    }

    public function review(): HasMany
    {
        return $this->hasMany(SpaReview::class, 'booking_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate unique booking number
     */
    public static function generateBookingNumber(int $tenantId): string
    {
        $prefix = 'SPA';
        $date = now()->format('Ymd');
        $lastBooking = static::where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastBooking ? (intval(substr($lastBooking->booking_number, -4)) + 1) : 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    /**
     * Check if booking can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Cancel the booking
     */
    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);

        // Decrement booked_today counter if treatment exists
        if ($this->treatment_id && $this->booking_date->isToday()) {
            $this->treatment->decrement('booked_today');
        }
    }

    /**
     * Confirm the booking
     */
    public function confirm(): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Mark as completed
     */
    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Increment therapist's total treatments
        if ($this->therapist) {
            $this->therapist->incrementTreatments();
        }

        // Decrement booked_today if today
        if ($this->treatment_id && $this->booking_date->isToday()) {
            $this->treatment->decrement('booked_today');
        }
    }

    /**
     * Calculate commission for therapist
     */
    public function calculateCommission(): float
    {
        if (!$this->therapist) {
            return 0;
        }

        return $this->therapist->calculateCommission($this->amount);
    }
}
