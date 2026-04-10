<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Carbon\Carbon $check_in_date
 * @property \Carbon\Carbon $check_out_date
 * @property \Carbon\Carbon|null $actual_check_in
 * @property \Carbon\Carbon|null $actual_check_out
 * @property \Carbon\Carbon|null $cancelled_at
 */
class Reservation extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'guest_id',
        'group_booking_id',
        'room_type_id',
        'room_id',
        'reservation_number',
        'status',
        'check_in_date',
        'check_out_date',
        'actual_check_in_at',
        'actual_check_out_at',
        'adults',
        'children',
        'expected_arrival_time',
        'nights',
        'rate_per_night',
        'total_amount',
        'discount',
        'tax',
        'grand_total',
        'source',
        'is_walk_in',
        'is_vip',
        'special_requests',
        'purpose_of_stay',
        'cancelled_at',
        'cancel_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'actual_check_in_at' => 'datetime',
            'actual_check_out_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'expected_arrival_time' => 'string',
            'is_walk_in' => 'boolean',
            'is_vip' => 'boolean',
            'rate_per_night' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'adults' => 'integer',
            'children' => 'integer',
            'nights' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the pre-arrival form for this reservation
     */
    public function preArrivalForm()
    {
        return $this->hasOne(PreArrivalForm::class);
    }

    /**
     * Get minibar charges for this reservation
     */
    public function minibarCharges()
    {
        return $this->hasMany(MinibarCharge::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function checkInOuts(): HasMany
    {
        return $this->hasMany(CheckInOut::class);
    }

    public function reservationRooms(): HasMany
    {
        return $this->hasMany(ReservationRoom::class);
    }

    public function groupBooking(): BelongsTo
    {
        return $this->belongsTo(GroupBooking::class);
    }

    public function roomChanges(): HasMany
    {
        return $this->hasMany(ReservationRoomChange::class);
    }

    public function earlyLateRequests(): HasMany
    {
        return $this->hasMany(EarlyLateRequest::class);
    }

    public function walkInRecord(): HasOne
    {
        return $this->hasOne(WalkInReservation::class);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('check_in_date', '>', now())
            ->whereIn('status', ['pending', 'confirmed']);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('check_in_date', today())
            ->orWhereDate('check_out_date', today());
    }

    public function isCheckedIn(): bool
    {
        return $this->status === 'checked_in';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Generate unique reservation number
     */
    public static function generateReservationNumber(int $tenantId): string
    {
        $prefix = 'RES';
        $date = now()->format('Ymd');
        $lastReservation = static::where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastReservation ? (int) substr($lastReservation->reservation_number, -4) + 1 : 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }
}
