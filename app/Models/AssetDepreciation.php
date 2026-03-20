<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetDepreciation extends Model
{
    protected $fillable = ['tenant_id', 'asset_id', 'period', 'depreciation_amount', 'book_value_after'];
    protected $casts = ['depreciation_amount' => 'float', 'book_value_after' => 'float'];

    public function asset() { return $this->belongsTo(Asset::class); }
}
