<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use BelongsToTenant, SoftDeletes;
    protected $fillable = ['tenant_id', 'name', 'code', 'address', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
    public function productStocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }
    public function zones(): HasMany
    {
        return $this->hasMany(WarehouseZone::class);
    }
    public function bins(): HasMany
    {
        return $this->hasMany(WarehouseBin::class);
    }
}
