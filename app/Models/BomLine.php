<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomLine extends Model
{
    protected $fillable = [
        'bom_id', 'product_id', 'quantity_per_batch', 'unit',
        'child_bom_id', 'sort_order', 'notes',
    ];

    protected function casts(): array
    {
        return ['quantity_per_batch' => 'decimal:3'];
    }

    public function bom(): BelongsTo { return $this->belongsTo(Bom::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function childBom(): BelongsTo { return $this->belongsTo(Bom::class, 'child_bom_id'); }
}
