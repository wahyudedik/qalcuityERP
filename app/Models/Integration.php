<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Integration extends Model
{
use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'slug',
        'type',
        'status',
        'config',
        'oauth_tokens',
        'sync_frequency',
        'last_sync_at',
        'next_sync_at',
        'metadata',
        'activated_at',
    ];

    protected $casts = [
        'config' => 'array',
        'oauth_tokens' => 'array',
        'metadata' => 'array',
        'last_sync_at' => 'datetime',
        'next_sync_at' => 'datetime',
        'activated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function configs()
    {
        return $this->hasMany(IntegrationConfig::class);
    }

    public function syncLogs()
    {
        return $this->hasMany(IntegrationSyncLog::class);
    }

    public function webhooks()
    {
        return $this->hasMany(WebhookSubscription::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeEcommerce($query)
    {
        return $query->where('type', 'e-commerce');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Helpers
     */
    public function isConnected(): bool
    {
        return $this->status === 'active' && $this->oauth_tokens !== null;
    }

    public function hasError(): bool
    {
        return $this->status === 'error';
    }

    public function markAsActive(): void
    {
        $this->update([
            'status' => 'active',
            'activated_at' => now(),
        ]);
    }

    public function markAsError(): void
    {
        $this->update(['status' => 'error']);
    }

    public function updateLastSync(): void
    {
        $this->update(['last_sync_at' => now()]);
    }

    /**
     * Get decrypted config value
     */
    public function getConfigValue(string $key, $default = null)
    {
        $config = $this->configs()->where('key', $key)->first();

        if (!$config) {
            return $default;
        }

        return $config->is_encrypted ? decrypt($config->value) : $config->value;
    }

    /**
     * Set encrypted config value
     */
    public function setConfigValue(string $key, string $value, bool $encrypt = false): void
    {
        $this->configs()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $encrypt ? encrypt($value) : $value,
                'is_encrypted' => $encrypt,
            ]
        );
    }

    /**
     * Get OAuth access token
     */
    public function getAccessToken(): ?string
    {
        return $this->oauth_tokens['access_token'] ?? null;
    }

    /**
     * Check if OAuth token is expired
     */
    public function isTokenExpired(): bool
    {
        $expiresAt = $this->oauth_tokens['expires_at'] ?? null;

        if (!$expiresAt) {
            return true;
        }

        return now()->greaterThan($expiresAt);
    }

    /**
     * Get connector class name
     */
    public function getConnectorClass(): string
    {
        $className = ucfirst($this->slug) . 'Connector';

        return "App\\Services\\Integrations\\{$className}";
    }
}