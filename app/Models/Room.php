<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use AuditsChanges, SoftDeletes;
    use BelongsToTenant;

    // Status constants
    const STATUS_AVAILABLE = 'available';

    const STATUS_OCCUPIED = 'occupied';

    const STATUS_MAINTENANCE = 'maintenance';

    const STATUS_CLEANING = 'cleaning';

    const STATUS_BLOCKED = 'blocked';

    const STATUS_OUT_OF_ORDER = 'out_of_order';

    const STATUS_DIRTY = 'dirty';

    const STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_OCCUPIED,
        self::STATUS_MAINTENANCE,
        self::STATUS_CLEANING,
        self::STATUS_BLOCKED,
        self::STATUS_OUT_OF_ORDER,
        self::STATUS_DIRTY,
    ];

    protected $appends = ['housekeeping_stats'];

    protected $fillable = [
        'tenant_id',
        'room_type_id',
        'number',
        'floor',
        'building',
        'status',
        'is_active',
        'notes',
        'last_cleaned_at',
        'cleaned_by',
        'last_inspected_at',
        'inspected_by',
        'cleaning_count_today',
        'requires_deep_clean',
        'last_deep_clean_at',
        'next_deep_clean_due',
        'occupancy_days',
        'housekeeping_notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_cleaned_at' => 'datetime',
            'last_inspected_at' => 'datetime',
            'cleaning_count_today' => 'integer',
            'requires_deep_clean' => 'boolean',
            'last_deep_clean_at' => 'date',
            'next_deep_clean_due' => 'date',
            'occupancy_days' => 'integer',
            'housekeeping_notes' => 'array',
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

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function housekeepingTasks(): HasMany
    {
        return $this->hasMany(HousekeepingTask::class);
    }

    public function checkInOuts(): HasMany
    {
        return $this->hasMany(CheckInOut::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    /**
     * Update room status and log the change
     */
    public function updateStatus(string $newStatus, ?int $userId = null): void
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => $newStatus,
            'cleaned_by' => in_array($newStatus, [self::STATUS_AVAILABLE, self::STATUS_CLEANING]) ? ($userId ?? auth()->id()) : null,
            'last_cleaned_at' => $newStatus === self::STATUS_AVAILABLE ? now() : $this->last_cleaned_at,
        ]);

        ActivityLog::record(
            'room_status_changed',
            "Room {$this->number} status changed from {$oldStatus} to {$newStatus}",
            $this,
            [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'user_id' => $userId ?? auth()->id(),
            ]
        );
    }

    /**
     * Mark room as dirty after checkout
     */
    public function markAsDirty(): void
    {
        $this->updateStatus('dirty');
        $this->increment('occupancy_days');
    }

    /**
     * Increment cleaning count for today
     */
    public function incrementCleaningCount(): void
    {
        if ($this->cleaning_count_today < 3) {
            $this->increment('cleaning_count_today');
        }
    }

    /**
     * Check if room needs deep clean
     */
    public function checkDeepCleanRequired(): bool
    {
        // Deep clean required every 30 days or after 5 consecutive occupancies
        $needsDeepClean = $this->occupancy_days >= 5 ||
            ($this->last_deep_clean_at && $this->last_deep_clean_at->diffInDays(now()) >= 30);

        if ($needsDeepClean) {
            $this->update([
                'requires_deep_clean' => true,
                'next_deep_clean_due' => now()->addDays(7),
            ]);
        }

        return $needsDeepClean;
    }

    /**
     * Mark deep clean as completed
     */
    public function markDeepCleanCompleted(): void
    {
        $this->update([
            'requires_deep_clean' => false,
            'last_deep_clean_at' => now(),
            'next_deep_clean_due' => now()->addDays(30),
            'occupancy_days' => 0,
        ]);
    }

    /**
     * Get housekeeping statistics for this room
     */
    public function getHousekeepingStatsAttribute(): array
    {
        return [
            'times_cleaned_today' => $this->cleaning_count_today,
            'days_since_last_clean' => $this->last_cleaned_at?->diffInDays(now()) ?? 0,
            'days_until_deep_clean' => $this->next_deep_clean_due?->diffInDays(now()) ?? 0,
            'consecutive_occupancy_days' => $this->occupancy_days,
        ];
    }

    public function reservationRooms(): HasMany
    {
        return $this->hasMany(ReservationRoom::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available')->where('is_active', true);
    }

    public function scopeByFloor(Builder $query, string $floor): Builder
    {
        return $query->where('floor', $floor);
    }

    public function scopeByType(Builder $query, int $typeId): Builder
    {
        return $query->where('room_type_id', $typeId);
    }
}
