<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CosmeticBatchRecord extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'formula_id',
        'batch_number',
        'production_date',
        'expiry_date',
        'planned_quantity',
        'actual_quantity',
        'yield_percentage',
        'status',
        'created_by',
        'produced_by',
        'qc_by',
        'qc_completed_at',
        'production_notes',
        'qc_notes',
    ];

    protected $casts = [
        'production_date' => 'date',
        'expiry_date' => 'date',
        'planned_quantity' => 'decimal:2',
        'actual_quantity' => 'decimal:2',
        'yield_percentage' => 'decimal:2',
        'qc_completed_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(CosmeticFormula::class, 'formula_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function producer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'produced_by');
    }

    public function qcInspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'qc_by');
    }

    public function qualityChecks(): HasMany
    {
        return $this->hasMany(BatchQualityCheck::class, 'batch_id');
    }

    public function reworkLogs(): HasMany
    {
        return $this->hasMany(BatchReworkLog::class, 'batch_id');
    }

    /**
     * Status helpers
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isQcPending(): bool
    {
        return $this->status === 'qc_pending';
    }

    public function isReleased(): bool
    {
        return $this->status === 'released';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isOnHold(): bool
    {
        return $this->status === 'on_hold';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'in_progress' => 'In Progress',
            'qc_pending' => 'QC Pending',
            'released' => 'Released',
            'rejected' => 'Rejected',
            'on_hold' => 'On Hold',
            default => ucfirst(str_replace('_', ' ', $this->status))
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'in_progress' => 'blue',
            'qc_pending' => 'yellow',
            'released' => 'green',
            'rejected' => 'red',
            'on_hold' => 'orange',
            default => 'gray'
        };
    }

    /**
     * Calculate yield percentage
     */
    public function calculateYield(): float
    {
        if ($this->planned_quantity <= 0) {
            return 0;
        }

        $this->yield_percentage = round(
            ($this->actual_quantity / $this->planned_quantity) * 100,
            2
        );

        return $this->yield_percentage;
    }

    /**
     * Check if batch is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return now()->gt($this->expiry_date);
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Check if quality checks all passed
     */
    public function allQcPassed(): bool
    {
        $checks = $this->qualityChecks;

        if ($checks->count() === 0) {
            return false;
        }

        return $checks->every(fn($check) => $check->result === 'pass');
    }

    /**
     * Get failed QC checks
     */
    public function getFailedQcChecks()
    {
        return $this->qualityChecks()->where('result', 'fail')->get();
    }

    /**
     * Get next batch number
     */
    public static function getNextBatchNumber(): string
    {
        $year = now()->format('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'BMR-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get production duration in days
     */
    public function getProductionDurationAttribute(): ?int
    {
        if (!$this->production_date) {
            return null;
        }

        $endDate = $this->qc_completed_at ?? now();
        return $this->production_date->diffInDays($endDate);
    }

    /**
     * Check if batch can be released
     */
    public function canBeReleased(): bool
    {
        // Must have actual quantity recorded
        if (!$this->actual_quantity || $this->actual_quantity <= 0) {
            return false;
        }

        // Must pass all QC checks
        if (!$this->allQcPassed()) {
            return false;
        }

        // Must not have open rework
        $openRework = $this->reworkLogs()
            ->where('status', 'in_progress')
            ->exists();

        if ($openRework) {
            return false;
        }

        return true;
    }

    /**
     * Scopes
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeQcPending($query)
    {
        return $query->where('status', 'qc_pending');
    }

    public function scopeReleased($query)
    {
        return $query->where('status', 'released');
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now());
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    /**
     * Release batch (move to released status)
     */
    public function release(int $userId): void
    {
        $this->status = 'released';
        $this->qc_by = $userId;
        $this->qc_completed_at = now();
        $this->save();
    }

    /**
     * Reject batch
     */
    public function reject(int $userId, string $notes = ''): void
    {
        $this->status = 'rejected';
        $this->qc_by = $userId;
        $this->qc_notes = $notes;
        $this->qc_completed_at = now();
        $this->save();
    }
}
