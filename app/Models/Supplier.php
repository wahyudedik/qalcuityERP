<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'company',
        'address',
        'npwp',
        'bank_name',
        'bank_account',
        'bank_holder',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    // BUG-PO-003 FIX: Add scorecards relationship for supplier rating evaluation
    public function scorecards(): HasMany
    {
        return $this->hasMany(SupplierScorecard::class);
    }
}
