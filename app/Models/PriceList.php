<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceList extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'code', 'type', 'description',
        'valid_from', 'valid_until', 'is_active',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PriceListItem::class);
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_price_lists')
            ->withPivot('priority')
            ->withTimestamps();
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        $today = today();
        if ($this->valid_from && $today->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_until && $today->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'tier' => 'Tier / Level',
            'contract' => 'Kontrak',
            'promo' => 'Promosi',
            default => $this->type,
        };
    }
}
