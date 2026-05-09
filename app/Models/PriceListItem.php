<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceListItem extends Model
{
    protected $fillable = [
        'price_list_id', 'product_id', 'price', 'discount_percent', 'min_qty',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'min_qty' => 'decimal:2',
    ];

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Harga efektif setelah diskon */
    public function effectivePrice(): float
    {
        $price = (float) $this->price;
        if ($this->discount_percent > 0) {
            $price = $price * (1 - ($this->discount_percent / 100));
        }

        return round($price, 2);
    }
}
