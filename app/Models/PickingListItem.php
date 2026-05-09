<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickingListItem extends Model
{
    protected $fillable = ['picking_list_id', 'product_id', 'bin_id', 'quantity_requested', 'quantity_picked', 'status'];

    protected function casts(): array
    {
        return ['quantity_requested' => 'decimal:3', 'quantity_picked' => 'decimal:3'];
    }

    public function pickingList(): BelongsTo
    {
        return $this->belongsTo(PickingList::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class, 'bin_id');
    }
}
