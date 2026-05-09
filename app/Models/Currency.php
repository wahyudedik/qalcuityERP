<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Currency Model
 *
 * BUG-FIN-003 FIX: Added stale rate detection methods
 */
class Currency extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'symbol',
        'rate_to_idr',
        'is_base',
        'is_active',
        'rate_updated_at',
    ];

    protected $casts = ['rate_to_idr' => 'float', 'is_base' => 'boolean', 'is_active' => 'boolean', 'rate_updated_at' => 'datetime'];

    public function toIdr(float $amount): float
    {
        return $amount * $this->rate_to_idr;
    }

    public function fromIdr(float $idrAmount): float
    {
        return $this->rate_to_idr > 0 ? $idrAmount / $this->rate_to_idr : 0;
    }

    /**
     * BUG-FIN-003 FIX: Check if exchange rate is stale
     *
     * Rate dianggap stale jika:
     * - Lebih dari 7 hari tidak diupdate (warning)
     * - Lebih dari 30 hari tidak diupdate (critical)
     *
     * @param  int  $warningDays  Warning threshold (default: 7 days)
     * @param  int  $criticalDays  Critical threshold (default: 30 days)
     * @return string 'fresh', 'stale_warning', 'stale_critical'
     */
    public function getRateStalenessStatus(int $warningDays = 7, int $criticalDays = 30): string
    {
        // Base currency (IDR) doesn't need updates
        if ($this->is_base) {
            return 'fresh';
        }

        // No rate_updated_at means never updated
        if (! $this->rate_updated_at) {
            return 'stale_critical';
        }

        $daysSinceUpdate = $this->rate_updated_at->diffInDays(now());

        if ($daysSinceUpdate >= $criticalDays) {
            return 'stale_critical';
        }

        if ($daysSinceUpdate >= $warningDays) {
            return 'stale_warning';
        }

        return 'fresh';
    }

    /**
     * BUG-FIN-003 FIX: Get days since last rate update
     *
     * @return int|null Days since update, null if never updated
     */
    public function getDaysSinceLastUpdate(): ?int
    {
        if (! $this->rate_updated_at) {
            return null;
        }

        return $this->rate_updated_at->diffInDays(now());
    }

    /**
     * BUG-FIN-003 FIX: Check if rate needs update
     *
     * @param  int  $maxDays  Maximum allowed days (default: 7)
     */
    public function needsUpdate(int $maxDays = 7): bool
    {
        return in_array($this->getRateStalenessStatus(), ['stale_warning', 'stale_critical']);
    }
}
