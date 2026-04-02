<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EcommerceChannel extends Model
{
    use AuditsChanges;

    protected array $auditExclude = ['api_key', 'api_secret', 'access_token', 'refresh_token'];

    protected $fillable = [
        'tenant_id',
        'platform',
        'shop_name',
        'shop_id',
        'api_key',
        'api_secret',
        'access_token',
        'is_active',
        'last_sync_at',
        'stock_sync_enabled',
        'price_sync_enabled',
        'last_stock_sync_at',
        'last_price_sync_at',
        'sync_errors',
        'webhook_secret',
        'webhook_enabled',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'last_sync_at'        => 'datetime',
        'stock_sync_enabled'  => 'boolean',
        'price_sync_enabled'  => 'boolean',
        'last_stock_sync_at'  => 'datetime',
        'last_price_sync_at'  => 'datetime',
        'sync_errors'         => 'array',
        'webhook_enabled'     => 'boolean',
    ];

    // ─── Encrypt sensitive fields at rest ────────────────────────

    public function setApiKeyAttribute(?string $value): void
    {
        $this->attributes['api_key'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getApiKeyAttribute(?string $value): ?string
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setApiSecretAttribute(?string $value): void
    {
        $this->attributes['api_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getApiSecretAttribute(?string $value): ?string
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setAccessTokenAttribute(?string $value): void
    {
        $this->attributes['access_token'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getAccessTokenAttribute(?string $value): ?string
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }

    // ─── Relations ────────────────────────────────────────────────

    public function orders()
    {
        return $this->hasMany(EcommerceOrder::class, 'channel_id');
    }

    public function productMappings()
    {
        return $this->hasMany(EcommerceProductMapping::class, 'channel_id');
    }

    public function syncLogs()
    {
        return $this->hasMany(MarketplaceSyncLog::class, 'channel_id');
    }

    public function webhookLogs()
    {
        return $this->hasMany(EcommerceWebhookLog::class, 'channel_id');
    }
}
