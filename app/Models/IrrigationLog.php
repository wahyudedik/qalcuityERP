<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IrrigationLog extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'irrigation_schedule_id',
        'irrigated_at',
        'actual_duration_minutes',
        'actual_water_used_liters',
        'status',
        'notes',
    ];

    protected $casts = [
        'irrigated_at' => 'datetime',
        'actual_duration_minutes' => 'integer',
        'actual_water_used_liters' => 'float',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function schedule()
    {
        return $this->belongsTo(IrrigationSchedule::class, 'irrigation_schedule_id');
    }
}
