<?php
namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PickingList extends Model
{
    use BelongsToTenant;
    protected $fillable = ['tenant_id','warehouse_id','number','reference_type','reference_id','assigned_to','status','user_id','started_at','completed_at','notes'];
    protected function casts(): array { return ['started_at'=>'datetime','completed_at'=>'datetime']; }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(PickingListItem::class); }

    public static function generateNumber(int $tenantId): string {
        $count = self::where('tenant_id', $tenantId)->count() + 1;
        return 'PICK-' . date('Ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
