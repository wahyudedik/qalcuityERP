<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Site Labor Log - Detail tenaga kerja harian
 */
class SiteLaborLog extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'daily_report_id',
        'worker_name',
        'worker_type', // skilled, unskilled, supervisor, foreman
        'trade', // carpenter, mason, electrician, plumber, etc
        'hours_worked',
        'hourly_rate',
        'total_cost',
        'attendance_status', // present, absent, late, overtime
    ];

    protected function casts(): array
    {
        return [
            'hours_worked' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailySiteReport::class, 'daily_report_id');
    }

    /**
     * Calculate total cost if not set
     */
    public function calculateCost(): float
    {
        return $this->hours_worked * $this->hourly_rate;
    }
}
