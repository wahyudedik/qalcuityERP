<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'user_id', 'customer_id', 'number', 'name', 'description',
        'type', 'status', 'start_date', 'end_date',
        'budget', 'actual_cost', 'progress', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date'  => 'date',
            'end_date'    => 'date',
            'budget'      => 'decimal:2',
            'actual_cost' => 'decimal:2',
            'progress'    => 'decimal:2',
        ];
    }

    // Status constants
    const STATUS_PLANNING  = 'planning';
    const STATUS_ACTIVE    = 'active';
    const STATUS_ON_HOLD   = 'on_hold';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PLANNING,
        self::STATUS_ACTIVE,
        self::STATUS_ON_HOLD,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    public const VALID_TRANSITIONS = [
        'planning'  => ['active', 'cancelled'],
        'active'    => ['on_hold', 'completed', 'cancelled'],
        'on_hold'   => ['active', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function tasks(): HasMany { return $this->hasMany(ProjectTask::class); }
    public function expenses(): HasMany { return $this->hasMany(ProjectExpense::class); }
    public function rabItems(): HasMany { return $this->hasMany(RabItem::class); }
    public function timesheets(): HasMany { return $this->hasMany(Timesheet::class); }
    public function billingConfig() { return $this->hasOne(ProjectBillingConfig::class); }
    public function milestones(): HasMany { return $this->hasMany(ProjectMilestone::class)->orderBy('sort_order'); }
    public function projectInvoices(): HasMany { return $this->hasMany(ProjectInvoice::class); }

    /** Recalculate progress — hybrid: weight × effectiveProgress for each task */
    public function recalculateProgress(): void
    {
        $tasks = $this->tasks()->whereNotIn('status', ['cancelled'])->get();
        if ($tasks->isEmpty()) return;

        $totalWeight = $tasks->sum('weight');
        if ($totalWeight <= 0) return;

        $weightedProgress = $tasks->sum(fn ($t) => $t->weight * $t->effectiveProgress() / 100);
        $this->update(['progress' => round(($weightedProgress / $totalWeight) * 100, 2)]);
    }

    /** Recalculate actual_cost dari semua expenses */
    public function recalculateActualCost(): void
    {
        $total = $this->expenses()->sum('amount');
        $this->update(['actual_cost' => $total]);
    }

    public function budgetVariance(): float
    {
        return (float) $this->budget - (float) $this->actual_cost;
    }

    public function budgetUsedPercent(): float
    {
        return $this->budget > 0
            ? round(($this->actual_cost / $this->budget) * 100, 1)
            : 0;
    }
}
