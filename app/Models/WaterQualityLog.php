<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaterQualityLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'pond_id',
        'recorded_at',
        'temperature',
        'ph_level',
        'dissolved_oxygen',
        'salinity',
        'ammonia',
        'nitrate',
        'nitrite',
        'turbidity',
        'recorded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'temperature' => 'decimal:2',
            'ph_level' => 'decimal:2',
            'dissolved_oxygen' => 'decimal:2',
            'salinity' => 'decimal:2',
            'ammonia' => 'decimal:3',
            'nitrate' => 'decimal:3',
            'nitrite' => 'decimal:3',
            'turbidity' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function pond(): BelongsTo
    {
        return $this->belongsTo(AquaculturePond::class, 'pond_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
