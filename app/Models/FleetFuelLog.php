<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FleetFuelLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'vehicle_id', 'driver_id', 'user_id',
        'date', 'odometer', 'fuel_type', 'liters', 'price_per_liter',
        'total_cost', 'station', 'receipt_number',
        'expense_id', 'journal_entry_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'liters' => 'decimal:2',
            'price_per_liter' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(FleetVehicle::class, 'vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(FleetDriver::class, 'driver_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
