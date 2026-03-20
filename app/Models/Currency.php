<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'tenant_id', 'code', 'name', 'symbol', 'rate_to_idr',
        'is_base', 'is_active', 'rate_updated_at',
    ];

    protected $casts = ['rate_to_idr' => 'float', 'is_base' => 'boolean', 'is_active' => 'boolean', 'rate_updated_at' => 'datetime'];

    public function toIdr(float $amount): float { return $amount * $this->rate_to_idr; }
    public function fromIdr(float $idrAmount): float { return $this->rate_to_idr > 0 ? $idrAmount / $this->rate_to_idr : 0; }
}
