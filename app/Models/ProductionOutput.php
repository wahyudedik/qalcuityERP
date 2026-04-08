<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOutput extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'work_order_id', 'tenant_id', 'user_id',
        'good_qty', 'reject_qty', 'reject_reason', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'good_qty'   => 'decimal:3',
            'reject_qty' => 'decimal:3',
            'output_qty' => 'decimal:3', // generated column, read-only
        ];
    }

    public function workOrder(): BelongsTo { return $this->belongsTo(WorkOrder::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
