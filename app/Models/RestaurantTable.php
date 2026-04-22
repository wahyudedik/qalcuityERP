<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantTable extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'table_number',
        'capacity',
        'location',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'table_number' => 'integer',
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(TableReservation::class, 'table_id');
    }

    /**
     * Check if table is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->is_active;
    }

    /**
     * Occupy table
     */
    public function occupy(): void
    {
        $this->update(['status' => 'occupied']);
    }

    /**
     * Release table
     */
    public function release(): void
    {
        $this->update(['status' => 'available']);
    }
}
