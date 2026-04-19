<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternetPackage extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'description',
        'download_speed_mbps',
        'upload_speed_mbps',
        'burst_download_mbps',
        'burst_upload_mbps',
        'quota_bytes',
        'quota_period',
        'rollover_enabled',
        'price',
        'billing_cycle',
        'installation_fee',
        'overage_price_per_gb',
        'features',
        'max_devices',
        'priority_traffic',
        'static_ip',
        'static_ip_address',
        'is_active',
        'is_public',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'rollover_enabled' => 'boolean',
        'priority_traffic' => 'boolean',
        'static_ip' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'price' => 'decimal:2',
        'installation_fee' => 'decimal:2',
        'overage_price_per_gb' => 'decimal:2',
        'download_speed_mbps' => 'integer',
        'upload_speed_mbps' => 'integer',
        'burst_download_mbps' => 'integer',
        'burst_upload_mbps' => 'integer',
        'quota_bytes' => 'integer',
        'max_devices' => 'integer',
    ];

    /**
     * Get the tenant that owns the package.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get subscriptions using this package.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(TelecomSubscription::class, 'package_id');
    }

    /**
     * Get vouchers created from this package.
     */
    public function vouchers(): HasMany
    {
        return $this->hasMany(VoucherCode::class, 'package_id');
    }

    /**
     * Check if package has unlimited quota.
     */
    public function isUnlimited(): bool
    {
        return $this->quota_bytes === null || $this->quota_bytes == 0;
    }

    /**
     * Get quota in GB.
     */
    public function getQuotaGbAttribute(): float
    {
        if ($this->isUnlimited()) {
            return -1; // Represents unlimited
        }

        return round($this->quota_bytes / (1024 * 1024 * 1024), 2);
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Scope for active packages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for public packages.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope ordered by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    /**
     * Calculate overage charge for excess usage.
     */
    public function calculateOverageCharge(int $excessBytes): float
    {
        if ($this->isUnlimited() || $excessBytes <= 0) {
            return 0;
        }

        $excessGb = $excessBytes / (1024 * 1024 * 1024);
        return round($excessGb * $this->overage_price_per_gb, 2);
    }
}