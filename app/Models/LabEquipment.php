<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabEquipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_code',
        'name',
        'manufacturer',
        'model',
        'serial_number',
        'connection_type',
        'connection_config',
        'status',
        'last_calibration',
        'next_calibration',
        'last_maintenance',
        'next_maintenance',
        'auto_poll_enabled',
        'poll_interval_minutes',
        'location',
        'notes',
    ];

    protected $casts = [
        'connection_config' => 'array',
        'last_calibration' => 'date',
        'next_calibration' => 'date',
        'last_maintenance' => 'date',
        'next_maintenance' => 'date',
        'auto_poll_enabled' => 'boolean',
        'poll_interval_minutes' => 'integer',
    ];

    public function testResults(): HasMany
    {
        return $this->hasMany(LabResult::class, 'equipment_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
