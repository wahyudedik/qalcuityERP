<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EcommerceChannel extends Model
{
    protected $fillable = [
        'tenant_id', 'platform', 'shop_name', 'shop_id',
        'api_key', 'api_secret', 'access_token', 'is_active', 'last_sync_at',
    ];

    protected $casts = ['is_active' => 'boolean', 'last_sync_at' => 'datetime'];

    // ─── Encrypt sensitive fields at rest ────────────────────────

    public function setApiKeyAttribute(?string $value): void
    {
        $this->attributes['api_key'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getApiKeyAttribute(?string $value): ?string
    {
        if (!$value) return null;
        try { return Crypt::decryptString($value); } catch (\Throwable) { return null; }
    }

    public function setApiSecretAttribute(?string $value): void
    {
        $this->attributes['api_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getApiSecretAttribute(?string $value): ?string
    {
        if (!$value) return null;
        try { return Crypt::decryptString($value); } catch (\Throwable) { return null; }
    }

    public function setAccessTokenAttribute(?string $value): void
    {
        $this->attributes['access_token'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getAccessTokenAttribute(?string $value): ?string
    {
        if (!$value) return null;
        try { return Crypt::decryptString($value); } catch (\Throwable) { return null; }
    }

    // ─── Relations ────────────────────────────────────────────────

    public function orders() { return $this->hasMany(EcommerceOrder::class, 'channel_id'); }
}
