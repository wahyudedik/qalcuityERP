<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DairyMilkingSession extends Model
{
    use BelongsToTenant;

    protected $table = 'dairy_milking_sessions';

    protected $fillable = [
        'tenant_id',
        'session_code',
        'session_date',
        'session_type',
        'start_time',
        'end_time',
        'total_animals_milked',
        'total_milk_volume',
        'average_milk_per_animal',
        'operator_name',
        'equipment_notes',
        'issues',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'total_animals_milked' => 'integer',
            'total_milk_volume' => 'decimal:2',
            'average_milk_per_animal' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get session type label
     */
    public function getSessionTypeLabelAttribute(): string
    {
        return match ($this->session_type) {
            'morning' => 'Pagi (05:00-08:00)',
            'afternoon' => 'Siang (13:00-16:00)',
            'evening' => 'Sore (18:00-21:00)',
            default => ucfirst($this->session_type)
        };
    }

    /**
     * Calculate session duration in minutes
     */
    public function getDurationMinutesAttribute(): ?int
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);

        return $start->diffInMinutes($end);
    }

    /**
     * Scope for sessions by date
     */
    public function scopeByDate($query, $date)
    {
        return $query->where('session_date', $date);
    }

    /**
     * Scope for sessions by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('session_type', $type);
    }
}
