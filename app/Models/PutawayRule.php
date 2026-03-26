<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PutawayRule extends Model
{
    protected $fillable = ['tenant_id','warehouse_id','product_category','product_id','zone_id','bin_id','priority','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function zone(): BelongsTo { return $this->belongsTo(WarehouseZone::class, 'zone_id'); }
    public function bin(): BelongsTo { return $this->belongsTo(WarehouseBin::class, 'bin_id'); }
}
