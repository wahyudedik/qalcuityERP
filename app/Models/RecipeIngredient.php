<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredient extends Model
{
    protected $fillable = ['recipe_id', 'product_id', 'quantity_per_batch', 'unit'];

    protected function casts(): array
    {
        return ['quantity_per_batch' => 'decimal:3'];
    }

    public function recipe(): BelongsTo { return $this->belongsTo(Recipe::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
