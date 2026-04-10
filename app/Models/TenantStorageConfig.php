<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantStorageConfig extends Model
{
    protected $fillable = [
        'tenant_id',
        'provider',
        'bucket_name',
        'region',
        'access_key',
        'secret_key',
        'endpoint',
        'additional_config',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'additional_config' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Get the tenant that owns the config
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope to get active configs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get default config
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to filter by provider
     */
    public function scopeProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Get decrypted secret key
     */
    public function getDecryptedSecretKeyAttribute(): ?string
    {
        if (!$this->secret_key) {
            return null;
        }

        // Implement decryption logic here
        return decrypt($this->secret_key);
    }

    /**
     * Set encrypted secret key
     */
    public function setSecretKeyAttribute(?string $value): void
    {
        if ($value) {
            $this->attributes['secret_key'] = encrypt($value);
        }
    }

    /**
     * Get decrypted access key
     */
    public function getDecryptedAccessKeyAttribute(): ?string
    {
        if (!$this->access_key) {
            return null;
        }

        return decrypt($this->access_key);
    }

    /**
     * Set encrypted access key
     */
    public function setAccessKeyAttribute(?string $value): void
    {
        if ($value) {
            $this->attributes['access_key'] = encrypt($value);
        }
    }

    /**
     * Check if this is S3 storage
     */
    public function isS3(): bool
    {
        return $this->provider === 's3';
    }

    /**
     * Check if this is Google Cloud Storage
     */
    public function isGCS(): bool
    {
        return $this->provider === 'gcs';
    }

    /**
     * Check if this is Azure Blob Storage
     */
    public function isAzure(): bool
    {
        return $this->provider === 'azure';
    }

    /**
     * Get storage configuration array
     */
    public function getStorageConfig(): array
    {
        $config = [
            'provider' => $this->provider,
            'bucket' => $this->bucket_name,
            'region' => $this->region,
            'key' => $this->decrypted_access_key,
            'secret' => $this->decrypted_secret_key,
        ];

        if ($this->endpoint) {
            $config['endpoint'] = $this->endpoint;
        }

        return array_merge($config, $this->additional_config ?? []);
    }
}
