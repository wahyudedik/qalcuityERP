<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkCenter extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'cost_per_hour',
        'overhead_rate_per_hour',
        'monthly_fixed_overhead', // BUG-MFG-003 FIX
        'capacity_per_day',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'cost_per_hour' => 'decimal:2',
            'overhead_rate_per_hour' => 'decimal:2', // BUG-MFG-003 FIX
            'monthly_fixed_overhead' => 'decimal:2', // BUG-MFG-003 FIX
            'capacity_per_day' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function operations(): HasMany
    {
        return $this->hasMany(WorkOrderOperation::class);
    }
}
