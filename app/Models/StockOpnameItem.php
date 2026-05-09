<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    protected $fillable = ['session_id', 'product_id', 'bin_id', 'system_qty', 'actual_qty', 'difference', 'notes'];

    protected function casts(): array
    {
        return ['system_qty' => 'decimal:3', 'actual_qty' => 'decimal:3', 'difference' => 'decimal:3'];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(StockOpnameSession::class, 'session_id');
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
