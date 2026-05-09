<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRequest extends Model
{
    use AuditsChanges, SoftDeletes;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'room_id',
        'reported_by',
        'assigned_to',
        'title',
        'category',
        'description',
        'status',
        'priority',
        'assigned_at',
        'started_at',
        'completed_at',
        'cost',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cost' => 'decimal:2',
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

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope for urgent requests
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'reported');
    }

    /**
     * Scope for in progress requests
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Assign request to maintenance staff
     */
    public function assign(int $userId, ?string $notes = null): void
    {
        $this->update([
            'assigned_to' => $userId,
            'assigned_at' => now(),
            'status' => 'in_progress',
        ]);

        ActivityLog::record(
            'maintenance_assigned',
            "Maintenance request '{$this->title}' assigned to user {$userId}",
            $this->room,
            ['request_id' => $this->id, 'assigned_to' => $userId]
        );
    }

    /**
     * Start working on request
     */
    public function startWork(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Complete the maintenance request
     */
    public function complete(string $resolutionNotes, float $cost = 0): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'resolution_notes' => $resolutionNotes,
            'cost' => $cost,
        ]);

        // Update room status if needed
        if ($this->room && $this->room->status === 'out_of_order') {
            $this->room->update(['status' => 'dirty']);
        }

        ActivityLog::record(
            'maintenance_completed',
            "Maintenance request '{$this->title}' completed",
            $this->room,
            ['request_id' => $this->id, 'cost' => $cost]
        );
    }

    /**
     * Check if request is overdue (more than 24 hours for normal priority)
     */
    public function isOverdue(): bool
    {
        if (! $this->created_at) {
            return false;
        }

        $hoursLimit = match ($this->priority) {
            'urgent' => 2,
            'high' => 8,
            'normal' => 24,
            'low' => 72,
            default => 24,
        };

        return now()->diffInHours($this->created_at) > $hoursLimit && ! $this->completed_at;
    }

    /**
     * Get priority badge color
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'red',
            'high' => 'orange',
            'normal' => 'blue',
            'low' => 'gray',
            default => 'gray',
        };
    }
}
