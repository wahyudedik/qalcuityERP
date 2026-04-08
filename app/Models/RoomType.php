<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomType extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'description',
        'base_occupancy',
        'max_occupancy',
        'base_rate',
        'amenities',
        'images',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_rate' => 'decimal:2',
            'amenities' => 'array',
            'images' => 'array',
            'is_active' => 'boolean',
            'base_occupancy' => 'integer',
            'max_occupancy' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function roomRates(): HasMany
    {
        return $this->hasMany(RoomRate::class);
    }

    /**
     * Alias for roomRates - used for easier access in controllers
     */
    public function rates(): HasMany
    {
        return $this->roomRates();
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
