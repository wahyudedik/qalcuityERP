<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table Reservation untuk Restaurant (bukan hotel)
 */
class TableReservation extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'table_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'party_size',
        'reservation_date',
        'reservation_time',
        'duration_minutes',
        'status', // confirmed, seated, completed, cancelled, no_show
        'special_requests',
        'deposit_amount',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'party_size' => 'integer',
            'reservation_date' => 'date',
            'reservation_time' => 'datetime:H:i',
            'duration_minutes' => 'integer',
            'deposit_amount' => 'decimal:2',
            'reserved_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(FbOrder::class, 'table_reservation_id');
    }

    /**
     * Check if reservation is for today
     */
    public function isToday(): bool
    {
        return $this->reservation_date->isToday();
    }

    /**
     * Get reservation duration in hours
     */
    public function getDurationHours(): float
    {
        return $this->duration_minutes / 60;
    }

    /**
     * Calculate end time
     */
    public function getEndTime(): string
    {
        $startTime = \Carbon\Carbon::parse($this->reservation_time);
        return $startTime->addMinutes($this->duration_minutes)->format('H:i');
    }
}
