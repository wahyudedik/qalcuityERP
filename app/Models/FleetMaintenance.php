<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FleetMaintenance extends Model
{
    protected $fillable = [
        'tenant_id', 'vehicle_id', 'type', 'description',
        'scheduled_date', 'completed_date', 'odometer_at', 'cost',
        'vendor', 'status', 'next_km', 'next_date',
        'journal_entry_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'completed_date' => 'date',
            'next_date'      => 'date',
            'cost'           => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function vehicle(): BelongsTo { return $this->belongsTo(FleetVehicle::class, 'vehicle_id'); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }
}
