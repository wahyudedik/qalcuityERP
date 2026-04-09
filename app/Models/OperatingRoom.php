<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatingRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'room_name',
        'type',
        'capacity',
        'equipment',
        'specializations',
        'has_laminar_flow',
        'has_hybrid_imaging',
        'availability_schedule',
        'start_time',
        'end_time',
        'is_available_247',
        'status',
        'is_active',
        'floor',
        'wing',
        'department',
        'specifications',
        'notes',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'equipment' => 'array',
        'specializations' => 'array',
        'availability_schedule' => 'array',
        'has_laminar_flow' => 'boolean',
        'has_hybrid_imaging' => 'boolean',
        'is_available_247' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Scope: Available rooms
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
            ->where('is_active', true);
    }

    /**
     * Scope: By type
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: With specific specialization
     */
    public function scopeHasSpecialization($query, $specialization)
    {
        return $query->whereJsonContains('specializations', $specialization);
    }

    /**
     * Check if room is available at given time
     */
    public function isAvailableAt($date, $startTime, $endTime)
    {
        if ($this->status !== 'available' || !$this->is_active) {
            return false;
        }

        // Check existing surgeries
        $conflict = $this->surgerySchedules()
            ->whereDate('scheduled_date', $date)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('scheduled_start_time', [$startTime, $endTime])
                    ->orWhereBetween('scheduled_end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('scheduled_start_time', '<=', $startTime)
                            ->where('scheduled_end_time', '>=', $endTime);
                    });
            })
            ->exists();

        return !$conflict;
    }

    /**
     * Get utilization rate for date range
     */
    public function getUtilizationRate($startDate, $endDate)
    {
        $totalMinutes = $this->getTotalAvailableMinutes($startDate, $endDate);
        $usedMinutes = $this->getUsedMinutes($startDate, $endDate);

        if ($totalMinutes > 0) {
            return round(($usedMinutes / $totalMinutes) * 100, 2);
        }

        return 0;
    }

    /**
     * Get total available minutes
     */
    protected function getTotalAvailableMinutes($startDate, $endDate)
    {
        $days = now()->parse($startDate)->diffInDays(now()->parse($endDate)) + 1;
        $dailyMinutes = now()->parse($this->start_time)->diffInMinutes(now()->parse($this->end_time));

        return $days * $dailyMinutes;
    }

    /**
     * Get used minutes from surgeries
     */
    protected function getUsedMinutes($startDate, $endDate)
    {
        return $this->surgerySchedules()
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('actual_duration') ?: 0;
    }
}
