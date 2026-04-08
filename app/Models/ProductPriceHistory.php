<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPriceHistory extends Model
{
    use BelongsToTenant;
    protected $table = 'product_price_history';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'old_price',
        'new_price',
        'orders_before_7d',
        'orders_after_7d',
        'revenue_before_7d',
        'revenue_after_7d',
    ];

    protected function casts(): array
    {
        return [
            'old_price' => 'decimal:2',
            'new_price' => 'decimal:2',
            'revenue_before_7d' => 'decimal:2',
            'revenue_after_7d' => 'decimal:2',
            'orders_before_7d' => 'integer',
            'orders_after_7d' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
