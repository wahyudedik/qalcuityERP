<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchReworkLog extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'batch_id',
        'rework_code',
        'reason',
        'rework_action',
        'quantity_before',
        'quantity_after',
        'loss_quantity',
        'status',
        'initiated_by',
        'completed_by',
        'completed_at',
        'final_notes',
    ];

    protected $casts = [
        'quantity_before' => 'decimal:2',
        'quantity_after' => 'decimal:2',
        'loss_quantity' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(CosmeticBatchRecord::class, 'batch_id');
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Status helpers
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'failed' => 'Failed',
            default => ucfirst(str_replace('_', ' ', $this->status))
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'yellow',
            'completed' => 'green',
            'failed' => 'red',
            default => 'gray'
        };
    }

    /**
     * Calculate loss quantity
     */
    public function calculateLoss(): float
    {
        if (!$this->quantity_after) {
            return 0;
        }

        $this->loss_quantity = round($this->quantity_before - $this->quantity_after, 2);
        return $this->loss_quantity;
    }

    /**
     * Get loss percentage
     */
    public function getLossPercentageAttribute(): float
    {
        if ($this->quantity_before <= 0) {
            return 0;
        }

        $loss = $this->loss_quantity ?? ($this->quantity_before - ($this->quantity_after ?? 0));
        return round(($loss / $this->quantity_before) * 100, 2);
    }

    /**
     * Check if rework has significant loss (>10%)
     */
    public function hasSignificantLoss(): bool
    {
        return $this->loss_percentage > 10;
    }

    /**
     * Get rework duration in hours
     */
    public function getDurationHoursAttribute(): ?float
    {
        if (!$this->created_at || !$this->completed_at) {
            return null;
        }

        return round($this->created_at->diffInHours($this->completed_at), 2);
    }

    /**
     * Get next rework code
     */
    public static function getNextReworkCode(): string
    {
        $year = now()->format('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'RW-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Complete rework
     */
    public function complete(int $userId, string $notes = ''): void
    {
        $this->status = 'completed';
        $this->completed_by = $userId;
        $this->completed_at = now();
        $this->calculateLoss();
        if ($notes) {
            $this->final_notes = $notes;
        }
        $this->save();
    }

    /**
     * Mark rework as failed
     */
    public function fail(int $userId, string $notes = ''): void
    {
        $this->status = 'failed';
        $this->completed_by = $userId;
        $this->completed_at = now();
        $this->final_notes = $notes;
        $this->save();
    }

    /**
     * Scopes
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeHighLoss($query, float $threshold = 10.0)
    {
        return $query->whereRaw('((quantity_before - COALESCE(quantity_after, 0)) / quantity_before * 100) > ?', [$threshold]);
    }

    /**
     * Get rework summary
     */
    public function getReworkSummaryAttribute(): array
    {
        return [
            'rework_code' => $this->rework_code,
            'reason' => $this->reason,
            'action' => $this->rework_action,
            'quantity_before' => $this->quantity_before,
            'quantity_after' => $this->quantity_after,
            'loss' => $this->loss_quantity,
            'loss_percentage' => $this->loss_percentage . '%',
            'status' => $this->status_label,
            'duration' => $this->duration_hours ? $this->duration_hours . ' hours' : 'N/A',
        ];
    }
}
