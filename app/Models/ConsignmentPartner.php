<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsignmentPartner extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'contact_person', 'phone', 'email',
        'address', 'commission_pct', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return ['commission_pct' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(ConsignmentShipment::class, 'partner_id');
    }

    public function salesReports(): HasMany
    {
        return $this->hasMany(ConsignmentSalesReport::class, 'partner_id');
    }
}
