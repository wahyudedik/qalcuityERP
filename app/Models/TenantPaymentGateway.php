<?php

namespace App\Models;

use App\Services\PaymentGatewayService;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPaymentGateway extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'provider',
        'environment',
        'credentials',
        'settings',
        'is_active',
        'is_default',
        'webhook_url',
        'webhook_secret',
        'last_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'settings' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'last_verified_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get decrypted credentials
     */
    public function getDecryptedCredentials(): array
    {
        return $this->credentials;
    }

    /**
     * Set and encrypt credentials
     */
    public function setCredentials(array $credentials): void
    {
        $this->credentials = $credentials;
    }

    /**
     * Get default gateway for tenant
     */
    public static function getDefaultGateway(int $tenantId): ?self
    {
        return static::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Get active gateways for tenant
     */
    public static function getActiveGateways(int $tenantId): Collection
    {
        return static::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Set as default gateway
     */
    public function setAsDefault(): void
    {
        // Unset other defaults for same provider
        static::where('tenant_id', $this->tenant_id)
            ->where('provider', $this->provider)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    /**
     * Get webhook URL for this gateway
     */
    public function getWebhookUrlAttribute(): string
    {
        if ($this->attributes['webhook_url']) {
            return $this->attributes['webhook_url'];
        }

        return route('payment.webhook', ['provider' => $this->provider, 'tenant' => $this->tenant_id]);
    }

    /**
     * Verify gateway credentials
     */
    public function verifyCredentials(): array
    {
        try {
            $service = new PaymentGatewayService($this->tenant_id);
            $result = $service->verifyGateway($this->provider);

            if ($result['success']) {
                $this->update([
                    'last_verified_at' => now(),
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get gateway display name
     */
    public function getDisplayNameAttribute(): string
    {
        return match ($this->provider) {
            'midtrans' => 'Midtrans',
            'xendit' => 'Xendit',
            'duitku' => 'Duitku',
            'tripay' => 'Tripay',
            default => ucfirst($this->provider),
        };
    }

    /**
     * Get environment label
     */
    public function getEnvironmentLabelAttribute(): string
    {
        return $this->environment === 'production' ? 'Production' : 'Sandbox';
    }
}
