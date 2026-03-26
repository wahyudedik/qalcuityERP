<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpnameSession extends Model
{
    protected $fillable = ['tenant_id','warehouse_id','number','opname_date','status','user_id','notes'];
    protected function casts(): array { return ['opname_date'=>'date']; }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(StockOpnameItem::class, 'session_id'); }
}
