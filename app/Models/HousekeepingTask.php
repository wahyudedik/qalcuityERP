<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HousekeepingTask extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

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
        if (!$this->scheduled_at) {
            return false;
        }
        return now()->isAfter($this->scheduled_at) && !$this->completed_at;
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
     * Mark for inspection
     */
    public function markForInspection(): void
    {
        $this->update(['status' => 'inspected']);
    }
}
