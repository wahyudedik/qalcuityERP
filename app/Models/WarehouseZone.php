<?php
namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseZone extends Model
{
    use BelongsToTenant;
    protected $fillable = ['warehouse_id','tenant_id','code','name','type','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function bins(): HasMany { return $this->hasMany(WarehouseBin::class, 'zone_id'); }
}
