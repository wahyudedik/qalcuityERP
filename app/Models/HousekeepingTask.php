<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HousekeepingTask extends Model
{
    use AuditsChanges, SoftDeletes;
    use BelongsToTenant;

    // Task type constants — keep ALL values (including legacy duplicates for backward compat)
    const TYPE_CHECKOUT_CLEAN = 'checkout_clean';

    const TYPE_STAY_CLEAN = 'stay_clean';

    const TYPE_DEEP_CLEAN = 'deep_clean';       // legacy alias

    const TYPE_DEEP_CLEANING = 'deep_cleaning';    // canonical

    const TYPE_INSPECTION = 'inspection';

    const TYPE_REGULAR_CLEANING = 'regular_cleaning';

    const TYPE_TURNDOWN = 'turndown';         // legacy alias

    const TYPE_TURNDOWN_SERVICE = 'turndown_service'; // canonical

    const TYPES = [
        self::TYPE_CHECKOUT_CLEAN,
        self::TYPE_STAY_CLEAN,
        self::TYPE_DEEP_CLEAN,
        self::TYPE_DEEP_CLEANING,
        self::TYPE_INSPECTION,
        self::TYPE_REGULAR_CLEANING,
        self::TYPE_TURNDOWN,
        self::TYPE_TURNDOWN_SERVICE,
    ];

    // Status constants
    const STATUS_PENDING = 'pending';

    const STATUS_ASSIGNED = 'assigned';

    const STATUS_IN_PROGRESS = 'in_progress';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ASSIGNED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'tenant_id',
        'room_id',
        'assigned_to',
        'type',
        'status',
        'priority',
        'estimated_duration',
        'scheduled_at',
        'started_at',
        'completed_at',
        'actual_duration',
        'inspected_by',
        'notes',
        'checklist',
        'photos',
        'inspection_notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'checklist' => 'array',
            'photos' => 'array',
            'estimated_duration' => 'integer',
            'actual_duration' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function inspectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    /**
     * Get task duration in minutes
     */
    public function getDurationAttribute(): ?int
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInMinutes($this->completed_at);
        }

        return $this->actual_duration;
    }

    /**
     * Check if task is overdue
     */
    public function isOverdue(): bool
    {
        if (! $this->scheduled_at) {
            return false;
        }

        return now()->isAfter($this->scheduled_at) && ! $this->completed_at;
    }

    /**
     * Start the task
     */
    public function start(?int $userId = null): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
            'assigned_to' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Complete the task
     */
    public function complete(?array $checklist = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'actual_duration' => $this->started_at?->diffInMinutes(now()),
            'checklist' => $checklist ?? $this->checklist,
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Mark for inspection (sets status to completed, awaiting supervisor review)
     */
    public function markForInspection(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }
}
