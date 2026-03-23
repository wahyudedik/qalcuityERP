<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesReturnItem extends Model
{
    protected $fillable = [
        'sales_return_id', 'product_id', 'quantity', 'price', 'total', 'condition', 'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price'    => 'decimal:2',
        'total'    => 'decimal:2',
    ];

    public function salesReturn(): BelongsTo { return $this->belongsTo(SalesReturn::class); }
    public function product(): BelongsTo     { return $this->belongsTo(Product::class); }
}
