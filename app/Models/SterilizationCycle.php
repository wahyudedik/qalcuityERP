<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SterilizationCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'cycle_number',
        'equipment_id',
        'method',
        'start_time',
        'end_time',
        'temperature',
        'pressure',
        'duration_minutes',
        'operator_id',
        'quality_check_type',
        'quality_check_result',
        'quality_checked_by',
        'quality_checked_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'quality_checked_at' => 'datetime',
        'temperature' => 'decimal:2',
        'pressure' => 'decimal:2',
        'duration_minutes' => 'integer',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(MedicalEquipment::class, 'equipment_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function qualityChecker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quality_checked_by');
    }
}
