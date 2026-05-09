<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseBin extends Model
{
    use BelongsToTenant;

    protected $fillable = ['warehouse_id', 'zone_id', 'tenant_id', 'code', 'aisle', 'rack', 'shelf', 'max_capacity', 'bin_type', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'max_capacity' => 'integer'];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(WarehouseZone::class, 'zone_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(BinStock::class, 'bin_id');
    }

    public function usedCapacity(): float
    {
        return (float) $this->stocks()->sum('quantity');
    }

    public function availableCapacity(): ?int
    {
        return $this->max_capacity > 0 ? max(0, $this->max_capacity - (int) $this->usedCapacity()) : null;
    }
}
