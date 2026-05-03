<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FleetDriver extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'employee_id',
        'name',
        'license_number',
        'license_type',
        'license_expiry',
        'phone',
        'status',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'license_expiry' => 'date',
            'is_active'      => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    public function trips(): HasMany
    {
        return $this->hasMany(FleetTrip::class, 'driver_id');
    }
    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FleetFuelLog::class, 'driver_id');
    }
}
