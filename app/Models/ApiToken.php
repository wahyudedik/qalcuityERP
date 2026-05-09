<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'token',
        'abilities',
        'last_used_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function generate(int $tenantId, string $name, array $abilities = ['read'], ?Carbon $expiresAt = null): self
    {
        // SEC-002: Default 90 days expiry jika tidak diset
        if (! $expiresAt) {
            $expiresAt = now()->addDays(90);
        }

        return self::create([
            'tenant_id' => $tenantId,
            'name' => $name,
            'token' => Str::random(60),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function can(string $ability): bool
    {
        return in_array($ability, $this->abilities ?? []) || in_array('*', $this->abilities ?? []);
    }
}
