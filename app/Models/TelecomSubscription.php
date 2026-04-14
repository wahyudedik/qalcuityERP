<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TelecomSubscription extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'package_id',
        'device_id',
        'subscription_number',
        'status',
        'activated_at',
        'suspended_at',
        'cancelled_at',
        'expires_at',
        'billing_cycle',
        'next_billing_date',
        'last_billing_date',
        'quota_used_bytes',
        'quota_reset_bytes',
        'quota_period_start',
        'quota_period_end',
        'quota_exceeded',
        'hotspot_username',
        'hotspot_password_encrypted',
        'pppoe_username',
        'pppoe_password_encrypted',
        'static_ip_address',
        'mac_address_registered',
        'priority_level',
        'current_price',
        'notes',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'suspended_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'expires_at' => 'datetime',
        'next_billing_date' => 'date',
        'last_billing_date' => 'date',
        'quota_period_start' => 'datetime',
        'quota_period_end' => 'datetime',
        'quota_used_bytes' => 'integer',
        'quota_reset_bytes' => 'integer',
        'quota_exceeded' => 'boolean',
        'priority_level' => 'integer',
        'current_price' => 'decimal:2',
    ];

    protected $hidden = [
        'hotspot_password_encrypted',
        'pppoe_password_encrypted',
    ];

    /**
     * Get the tenant.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the package.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(InternetPackage::class, 'package_id');
    }

    /**
     * Get the assigned device.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(NetworkDevice::class, 'device_id');
    }

    /**
     * Get hotspot users for this subscription.
     */
    public function hotspotUsers(): HasMany
    {
        return $this->hasMany(HotspotUser::class, 'subscription_id');
    }

    /**
     * Get usage tracking records.
     */
    public function usageRecords(): HasMany
    {
        return $this->hasMany(UsageTracking::class, 'subscription_id');
    }

    /**
     * Get bandwidth allocations.
     */
    public function bandwidthAllocations(): HasMany
    {
        return $this->hasMany(BandwidthAllocation::class, 'subscription_id');
    }

    /**
     * Get alerts for this subscription.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(NetworkAlert::class, 'subscription_id');
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if subscription is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return now()->greaterThan($this->expires_at);
    }

    /**
     * Check if quota exceeded.
     */
    public function isQuotaExceeded(): bool
    {
        return $this->quota_exceeded;
    }

    /**
     * Activate subscription.
     */
    public function activate(): void
    {
        $this->update([
            'status' => 'active',
            'activated_at' => now(),
        ]);
    }

    /**
     * Suspend subscription.
     */
    public function suspend(): void
    {
        $this->update([
            'status' => 'suspended',
            'suspended_at' => now(),
        ]);
    }

    /**
     * Cancel subscription.
     */
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Reset quota.
     */
    public function resetQuota(): void
    {
        $this->update([
            'quota_used_bytes' => 0,
            'quota_exceeded' => false,
            'quota_period_start' => now(),
            'quota_period_end' => $this->calculateNextQuotaReset(),
        ]);
    }

    /**
     * Calculate next quota reset date.
     */
    private function calculateNextQuotaReset(): ?string
    {
        if (!$this->package) {
            return null;
        }

        $period = $this->package->quota_period;

        return match ($period) {
            'hourly' => now()->addHour()->toDateTimeString(),
            'daily' => now()->addDay()->toDateTimeString(),
            'weekly' => now()->addWeek()->toDateTimeString(),
            'monthly' => now()->addMonth()->toDateTimeString(),
            'yearly' => now()->addYear()->toDateTimeString(),
            default => now()->addMonth()->toDateTimeString(),
        };
    }

    /**
     * Get decrypted hotspot password.
     */
    public function getDecryptedHotspotPasswordAttribute(): ?string
    {
        if (!$this->hotspot_password_encrypted) {
            return null;
        }

        return decrypt($this->hotspot_password_encrypted);
    }

    /**
     * Set encrypted hotspot password.
     */
    public function setHotspotPasswordAttribute(?string $value): void
    {
        if ($value) {
            $this->attributes['hotspot_password_encrypted'] = encrypt($value);
        }
    }

    /**
     * Get decrypted PPPoE password.
     */
    public function getDecryptedPppoePasswordAttribute(): ?string
    {
        if (!$this->pppoe_password_encrypted) {
            return null;
        }

        return decrypt($this->pppoe_password_encrypted);
    }

    /**
     * Set encrypted PPPoE password.
     */
    public function setPppoePasswordAttribute(?string $value): void
    {
        if ($value) {
            $this->attributes['pppoe_password_encrypted'] = encrypt($value);
        }
    }

    /**
     * Scope for active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<', now());
    }

    /**
     * Scope for subscriptions with quota exceeded.
     */
    public function scopeQuotaExceeded($query)
    {
        return $query->where('quota_exceeded', true);
    }

    /**
     * Scope for subscriptions expiring soon.
     */
    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }
}