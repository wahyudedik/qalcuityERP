<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkCenter extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'cost_per_hour',
        'overhead_rate_per_hour',
        'monthly_fixed_overhead',
        'capacity_per_day',
        'start_time',
        'end_time',
        'break_minutes',
        'efficiency_percent',
        'current_utilization',
        'planned_hours_today',
        'actual_hours_today',
        'last_maintenance_date',
        'next_maintenance_date',
        'maintenance_interval_days',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'cost_per_hour' => 'decimal:2',
            'overhead_rate_per_hour' => 'decimal:2',
            'monthly_fixed_overhead' => 'decimal:2',
            'capacity_per_day' => 'integer',
            'break_minutes' => 'integer',
            'efficiency_percent' => 'decimal:2',
            'current_utilization' => 'decimal:2',
            'planned_hours_today' => 'decimal:2',
            'actual_hours_today' => 'decimal:2',
            'maintenance_interval_days' => 'integer',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'last_maintenance_date' => 'date',
            'next_maintenance_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(WorkOrderOperation::class);
    }

    /**
     * Calculate available capacity for today
     */
    public function getAvailableCapacity(): float
    {
        if (!$this->start_time || !$this->end_time) {
            return $this->capacity_per_day;
        }

        $totalHours = $this->start_time->diffInHours($this->end_time);
        $breakHours = $this->break_minutes / 60;
        $netHours = max(0, $totalHours - $breakHours);

        return $netHours * ($this->efficiency_percent / 100);
    }

    /**
     * Get remaining capacity for today
     */
    public function getRemainingCapacity(): float
    {
        return max(0, $this->getAvailableCapacity() - $this->planned_hours_today);
    }

    /**
     * Check if work center is currently operational
     */
    public function isOperational(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        $startTime = $this->start_time ? $now->copy()->setTimeFromTimeString($this->start_time) : null;
        $endTime = $this->end_time ? $now->copy()->setTimeFromTimeString($this->end_time) : null;

        if (!$startTime || !$endTime) {
            return true;
        }

        return $now->between($startTime, $endTime);
    }

    /**
     * Check if maintenance is due
     */
    public function isMaintenanceDue(): bool
    {
        if (!$this->next_maintenance_date) {
            return false;
        }

        return now()->gte($this->next_maintenance_date);
    }

    /**
     * Update utilization percentage
     */
    public function updateUtilization(): void
    {
        $available = $this->getAvailableCapacity();

        if ($available > 0) {
            $this->current_utilization = min(100, ($this->planned_hours_today / $available) * 100);
            $this->save();
        }
    }

    /**
     * Add planned hours
     */
    public function addPlannedHours(float $hours): void
    {
        $this->planned_hours_today += $hours;
        $this->updateUtilization();
    }

    /**
     * Record actual hours worked
     */
    public function recordActualHours(float $hours): void
    {
        $this->actual_hours_today += $hours;
        $this->save();
    }

    /**
     * Schedule next maintenance
     */
    public function scheduleNextMaintenance(): void
    {
        $this->last_maintenance_date = now();
        $this->next_maintenance_date = now()->addDays($this->maintenance_interval_days);
        $this->save();
    }

    /**
     * Get utilization status label
     */
    public function getUtilizationStatus(): string
    {
        $utilization = $this->current_utilization;

        if ($utilization >= 90) {
            return 'critical';
        } elseif ($utilization >= 75) {
            return 'high';
        } elseif ($utilization >= 50) {
            return 'moderate';
        } else {
            return 'low';
        }
    }

    /**
     * Get capacity forecast for next N days
     */
    public function getCapacityForecast(int $days = 7): array
    {
        $forecast = [];
        $availablePerDay = $this->getAvailableCapacity();

        for ($i = 0; $i < $days; $i++) {
            $date = now()->addDays($i);

            // Get planned work orders for this date
            $plannedHours = WorkOrderOperation::where('work_center_id', $this->id)
                ->whereDate('scheduled_date', $date)
                ->where('tenant_id', $this->tenant_id)
                ->sum('estimated_hours');

            $forecast[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->format('l'),
                'available_hours' => $availablePerDay,
                'planned_hours' => $plannedHours,
                'remaining_hours' => max(0, $availablePerDay - $plannedHours),
                'utilization_percent' => $availablePerDay > 0 ? round(($plannedHours / $availablePerDay) * 100, 2) : 0,
            ];
        }

        return $forecast;
    }
}
