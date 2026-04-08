<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateReferral extends Model
{
    use BelongsToTenant;
    protected $fillable = ['affiliate_id', 'tenant_id', 'referred_at', 'source'];

    protected function casts(): array
    {
        return ['referred_at' => 'datetime'];
    }

    public function affiliate(): BelongsTo { return $this->belongsTo(Affiliate::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
