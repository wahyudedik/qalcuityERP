<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
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
    public function timesheets(): HasMany { return $this->hasMany(Timesheet::class); }

    /** Recalculate progress dari bobot task yang done */
    public function recalculateProgress(): void
    {
        $tasks = $this->tasks()->whereNotIn('status', ['cancelled'])->get();
        if ($tasks->isEmpty()) return;

        $totalWeight = $tasks->sum('weight');
        if ($totalWeight <= 0) return;

        $doneWeight = $tasks->where('status', 'done')->sum('weight');
        $this->update(['progress' => round(($doneWeight / $totalWeight) * 100, 2)]);
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
