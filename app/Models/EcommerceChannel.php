<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcommerceChannel extends Model
{
    protected $fillable = [
        'tenant_id', 'platform', 'shop_name', 'shop_id',
        'api_key', 'api_secret', 'access_token', 'is_active', 'last_sync_at',
    ];

    protected $casts = ['is_active' => 'boolean', 'last_sync_at' => 'datetime'];

    public function orders() { return $this->hasMany(EcommerceOrder::class, 'channel_id'); }
}
