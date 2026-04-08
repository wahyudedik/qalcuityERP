<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NightAuditBatch extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'batch_number',
        'audit_date',
        'started_at',
        'completed_at',
        'auditor_id',
        'status',
        'notes',
        'summary_data',
        'total_rooms',
        'occupied_rooms',
        'occupancy_rate',
        'total_room_revenue',
        'room_charges_posted',
        'room_charges_posted_at',
        'total_fb_revenue',
        'fb_revenue_posted',
        'fb_revenue_posted_at',
        'total_other_revenue',
        'minibar_charges_posted',
        'minibar_charges_posted_at',
        'total_revenue',
        'adr',
        'revpar',
    ];

    protected function casts(): array
    {
        return [
            'audit_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'summary_data' => 'array',
            'total_rooms' => 'integer',
            'occupied_rooms' => 'integer',
            'occupancy_rate' => 'decimal:2',
            'total_room_revenue' => 'decimal:2',
            'room_charges_posted' => 'boolean',
            'room_charges_posted_at' => 'datetime',
            'total_fb_revenue' => 'decimal:2',
            'fb_revenue_posted' => 'boolean',
            'fb_revenue_posted_at' => 'datetime',
            'total_other_revenue' => 'decimal:2',
            'minibar_charges_posted' => 'boolean',
            'minibar_charges_posted_at' => 'datetime',
            'total_revenue' => 'decimal:2',
            'adr' => 'decimal:2',
            'revpar' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function revenuePostings(): HasMany
    {
        return $this->hasMany(RevenuePosting::class, 'audit_batch_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(NightAuditLog::class, 'audit_batch_id');
    }

    /**
     * Generate unique batch number
     */
    public static function generateBatchNumber(\Carbon\Carbon $auditDate): string
    {
        return "NA-" . $auditDate->format('Ymd');
    }

    /**
     * Check if batch is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * BUG-HOTEL-002 FIX: Check if all required steps are completed
     */
    public function areAllStepsCompleted(): bool
    {
        return $this->room_charges_posted
            && $this->fb_revenue_posted
            && $this->minibar_charges_posted;
    }

    /**
     * BUG-HOTEL-002 FIX: Get list of pending steps
     */
    public function getPendingSteps(): array
    {
        $pending = [];

        if (!$this->room_charges_posted) {
            $pending[] = 'Room charges posting';
        }

        if (!$this->fb_revenue_posted) {
            $pending[] = 'F&B revenue posting';
        }

        if (!$this->minibar_charges_posted) {
            $pending[] = 'Minibar charges posting';
        }

        return $pending;
    }

    /**
     * BUG-HOTEL-002 FIX: Get completion progress
     */
    public function getCompletionProgress(): array
    {
        $totalSteps = 3;
        $completedSteps = 0;

        if ($this->room_charges_posted)
            $completedSteps++;
        if ($this->fb_revenue_posted)
            $completedSteps++;
        if ($this->minibar_charges_posted)
            $completedSteps++;

        return [
            'total_steps' => $totalSteps,
            'completed_steps' => $completedSteps,
            'percentage' => round(($completedSteps / $totalSteps) * 100, 2),
            'pending_steps' => $this->getPendingSteps(),
        ];
    }

    /**
     * Calculate and save ADR (Average Daily Rate)
     */
    public function calculateADR(): void
    {
        if ($this->occupied_rooms > 0) {
            $this->adr = $this->total_room_revenue / $this->occupied_rooms;
        } else {
            $this->adr = 0;
        }

        $this->save();
    }

    /**
     * Calculate and save RevPAR (Revenue Per Available Room)
     */
    public function calculateRevPAR(): void
    {
        if ($this->total_rooms > 0) {
            $this->revpar = $this->total_room_revenue / $this->total_rooms;
        } else {
            $this->revpar = 0;
        }

        $this->save();
    }

    /**
     * Get occupancy rate percentage
     */
    public function getOccupancyRatePercentageAttribute(): float
    {
        if ($this->total_rooms > 0) {
            return ($this->occupied_rooms / $this->total_rooms) * 100;
        }
        return 0;
    }

    /**
     * Mark batch as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Recalculate metrics
        $this->calculateADR();
        $this->calculateRevPAR();
    }

    /**
     * Mark batch as failed
     */
    public function markAsFailed(string $reason = ''): void
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason,
        ]);
    }
}
