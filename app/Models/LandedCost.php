<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandedCost extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'number', 'purchase_order_id', 'goods_receipt_id',
        'date', 'description', 'allocation_method', 'total_additional_cost',
        'status', 'journal_entry_id', 'user_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date'                  => 'date',
            'total_additional_cost' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function goodsReceipt(): BelongsTo { return $this->belongsTo(GoodsReceipt::class); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function components(): HasMany { return $this->hasMany(LandedCostComponent::class); }
    public function allocations(): HasMany { return $this->hasMany(LandedCostAllocation::class); }

    public static function generateNumber(int $tenantId): string
    {
        $count = self::where('tenant_id', $tenantId)->count() + 1;
        return 'LC-' . date('Ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
