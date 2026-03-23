<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqResponse extends Model
{
    protected $fillable = [
        'rfq_id', 'supplier_id', 'response_date', 'total_price',
        'delivery_days', 'payment_terms', 'notes', 'is_selected', 'item_prices',
    ];

    protected $casts = [
        'response_date' => 'date',
        'total_price'   => 'decimal:2',
        'is_selected'   => 'boolean',
        'item_prices'   => 'array',
    ];

    public function rfq(): BelongsTo      { return $this->belongsTo(Rfq::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
}
