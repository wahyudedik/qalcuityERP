<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'recipe_id',
        'bom_id',
        'user_id',
        'number',
        'target_quantity',
        'unit',
        'status',
        'material_cost',
        'labor_cost',
        'overhead_cost',
        'overhead_method',
        'overhead_rate',
        'calculated_overhead',
        'total_operation_hours',
        'total_cost',
        'materials_reserved',
        'materials_consumed',
        'journal_entry_id',
        'started_at',
        'completed_at',
        'notes',
        // TASK-2.13: Scheduling fields
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'priority',
        'production_line',
        // Scrap/Waste tracking
        'scrap_quantity',
        'scrap_cost',
        'scrap_reason',
        'rework_quantity',
        'rework_cost',
        // Progress tracking
        'progress_percent',
        'progress_stage',
        // Metrics
        'efficiency_rate',
        'schedule_variance',
        // Quality Control fields
        'quality_status',
        'quality_grade',
        'quality_score',
        'quality_passed_at',
        'quality_failed_at',
        'quality_notes',
    ];

    protected function casts(): array
    {
        return [
            'target_quantity' => 'decimal:3',
            'material_cost' => 'decimal:2',
            'labor_cost' => 'decimal:2',
            'overhead_cost' => 'decimal:2',
            'overhead_rate' => 'decimal:4',
            'calculated_overhead' => 'decimal:2',
            'total_operation_hours' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'materials_reserved' => 'boolean',
            'materials_consumed' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            // TASK-2.13: New field casts
            'planned_start_date' => 'date',
            'planned_end_date' => 'date',
            'actual_start_date' => 'date',
            'actual_end_date' => 'date',
            'scrap_quantity' => 'decimal:3',
            'scrap_cost' => 'decimal:2',
            'rework_quantity' => 'decimal:3',
            'rework_cost' => 'decimal:2',
            'progress_percent' => 'decimal:2',
            'efficiency_rate' => 'decimal:2',
            'schedule_variance' => 'decimal:2',
            'quality_score' => 'decimal:2',
            'quality_passed_at' => 'datetime',
            'quality_failed_at' => 'datetime',
        ];
    }

    // Status constants
    const STATUS_PENDING = 'pending';

    const STATUS_IN_PROGRESS = 'in_progress';

    const STATUS_ON_HOLD = 'on_hold';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_ON_HOLD,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    /**
     * Graf transisi status yang valid.
     * pending → in_progress | on_hold | cancelled
     * in_progress → on_hold | completed | cancelled
     * on_hold → in_progress | cancelled
     * completed / cancelled → tidak bisa berubah
     */
    public const VALID_TRANSITIONS = [
        'pending' => ['in_progress', 'on_hold', 'cancelled'],
        'in_progress' => ['on_hold', 'completed', 'cancelled'],
        'on_hold' => ['in_progress', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(ProductionOutput::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(WorkOrderOperation::class)->orderBy('sequence');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    // BUG-MFG-002 FIX: Material reservations
    public function materialReservations(): HasMany
    {
        return $this->hasMany(MaterialReservation::class);
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::VALID_TRANSITIONS[$this->status] ?? []);
    }

    /** Total good_qty dari semua output */
    public function totalGoodQty(): float
    {
        return (float) $this->outputs()->sum('good_qty');
    }

    /** Total reject_qty dari semua output */
    public function totalRejectQty(): float
    {
        return (float) $this->outputs()->sum('reject_qty');
    }

    /** Yield rate: good / (good + reject) * 100 */
    public function yieldRate(): ?float
    {
        $total = $this->totalGoodQty() + $this->totalRejectQty();

        return $total > 0 ? round(($this->totalGoodQty() / $total) * 100, 1) : null;
    }

    /** Biaya per unit good */
    public function costPerGoodUnit(): ?float
    {
        $good = $this->totalGoodQty();

        return $good > 0 ? round($this->total_cost / $good, 2) : null;
    }

    // ============================================
    // TASK-2.13: Scheduling & Progress Methods
    // ============================================

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            1 => 'Urgent',
            2 => 'High',
            3 => 'Normal',
            4 => 'Low',
            default => 'Normal',
        };
    }

    /**
     * Get priority color class
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            1 => 'red',
            2 => 'orange',
            3 => 'blue',
            4 => 'gray',
            default => 'blue',
        };
    }

    /**
     * Calculate schedule variance (days ahead/behind)
     */
    public function calculateScheduleVariance(): float
    {
        if (! $this->planned_end_date || ! $this->actual_end_date) {
            return 0;
        }

        $planned = $this->planned_end_date;
        $actual = $this->actual_end_date;

        return (float) $planned->diffInDays($actual, false);
    }

    /**
     * Calculate efficiency rate (planned vs actual hours)
     */
    public function calculateEfficiencyRate(): ?float
    {
        if (! $this->started_at || ! $this->completed_at) {
            return null;
        }

        $actualHours = $this->started_at->diffInHours($this->completed_at);

        if ($actualHours <= 0 || $this->total_operation_hours <= 0) {
            return null;
        }

        // Efficiency = (Planned Hours / Actual Hours) * 100
        return round(($this->total_operation_hours / $actualHours) * 100, 2);
    }

    /**
     * Get progress stage label
     */
    public function getProgressStageLabelAttribute(): string
    {
        return match ($this->progress_stage) {
            'setup' => 'Setup',
            'processing' => 'Processing',
            'finishing' => 'Finishing',
            'qc' => 'Quality Control',
            default => 'Not Started',
        };
    }

    /**
     * Update progress based on outputs
     */
    public function updateProgress(): void
    {
        if ($this->target_quantity <= 0) {
            return;
        }

        $totalOutput = $this->totalGoodQty() + $this->totalRejectQty();
        $this->progress_percent = min(100, round(($totalOutput / $this->target_quantity) * 100, 2));

        // Auto-determine stage
        if ($this->progress_percent > 0 && $this->progress_percent < 25) {
            $this->progress_stage = 'setup';
        } elseif ($this->progress_percent >= 25 && $this->progress_percent < 75) {
            $this->progress_stage = 'processing';
        } elseif ($this->progress_percent >= 75 && $this->progress_percent < 100) {
            $this->progress_stage = 'finishing';
        } elseif ($this->progress_percent >= 100) {
            $this->progress_stage = 'qc';
        }

        $this->save();
    }

    /**
     * Record scrap/waste
     */
    public function recordScrap(float $quantity, float $cost, ?string $reason = null): void
    {
        $this->increment('scrap_quantity', $quantity);
        $this->increment('scrap_cost', $cost);

        if ($reason) {
            $this->update(['scrap_reason' => $reason]);
        }
    }

    /**
     * Record rework
     */
    public function recordRework(float $quantity, float $cost): void
    {
        $this->increment('rework_quantity', $quantity);
        $this->increment('rework_cost', $cost);
    }

    /**
     * Get total waste cost (scrap + rework)
     */
    public function getTotalWasteCostAttribute(): float
    {
        return (float) ($this->scrap_cost + $this->rework_cost);
    }

    /**
     * Get scrap percentage
     */
    public function getScrapPercentAttribute(): ?float
    {
        $total = $this->totalGoodQty() + $this->totalRejectQty();

        if ($total <= 0) {
            return null;
        }

        return round(($this->scrap_quantity / $total) * 100, 2);
    }

    /**
     * Check if work order is overdue
     */
    public function isOverdue(): bool
    {
        if (! $this->planned_end_date || $this->status === 'completed') {
            return false;
        }

        return now()->gt($this->planned_end_date);
    }

    /**
     * Get days remaining/overdue
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (! $this->planned_end_date) {
            return null;
        }

        return now()->diffInDays($this->planned_end_date, false);
    }

    /**
     * Get Gantt chart data
     */
    public function getGanttData(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'product_name' => $this->product?->name ?? 'Unknown',
            'start' => $this->planned_start_date?->format('Y-m-d') ?? $this->created_at->format('Y-m-d'),
            'end' => $this->planned_end_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'actual_start' => $this->actual_start_date?->format('Y-m-d'),
            'actual_end' => $this->actual_end_date?->format('Y-m-d'),
            'progress' => (float) $this->progress_percent,
            'status' => $this->status,
            'priority' => $this->priority,
            'priority_label' => $this->priority_label,
            'production_line' => $this->production_line,
            'target_quantity' => (float) $this->target_quantity,
            'good_quantity' => $this->totalGoodQty(),
            'is_overdue' => $this->isOverdue(),
            'days_remaining' => $this->days_remaining,
        ];
    }
}
