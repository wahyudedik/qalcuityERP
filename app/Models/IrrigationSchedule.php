<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class IrrigationSchedule extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'crop_cycle_id',
        'zone_name',
        'schedule_type',
        'irrigation_time',
        'duration_minutes',
        'frequency',
        'custom_days',
        'water_volume_liters',
        'irrigation_method',
        'is_active',
        'weather_adjusted',
        'last_irrigated_at',
        'next_irrigation_at',
        'total_irrigations',
        'total_water_used_liters',
        'notes',
    ];

    protected $casts = [
        'irrigation_time' => 'datetime:H:i',
        'duration_minutes' => 'integer',
        'custom_days' => 'array',
        'water_volume_liters' => 'float',
        'is_active' => 'boolean',
        'weather_adjusted' => 'boolean',
        'last_irrigated_at' => 'datetime',
        'next_irrigation_at' => 'datetime',
        'total_irrigations' => 'integer',
        'total_water_used_liters' => 'float',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function cropCycle()
    {
        return $this->belongsTo(CropCycle::class);
    }
    public function logs()
    {
        return $this->hasMany(IrrigationLog::class);
    }

    public function shouldIrrigateToday(): bool
    {
        if (!$this->is_active)
            return false;

        return match ($this->frequency) {
            'hourly' => true,
            'daily' => true,
            'weekly' => Carbon::now()->dayOfWeek === 1, // Monday
            'custom' => in_array(Carbon::now()->dayOfWeek, $this->custom_days ?? []),
            default => false
        };
    }

    public function recordIrrigation(int $duration, float $waterUsed): void
    {
        $this->increment('total_irrigations');
        $this->increment('total_water_used_liters', $waterUsed);
        $this->update([
            'last_irrigated_at' => now(),
            'next_irrigation_at' => $this->calculateNextIrrigation(),
        ]);
    }

    protected function calculateNextIrrigation(): ?Carbon
    {
        if (!$this->is_active)
            return null;

        $next = Carbon::now()->addDay()->setTimeFromTimeString($this->irrigation_time);
        return $next;
    }
}
