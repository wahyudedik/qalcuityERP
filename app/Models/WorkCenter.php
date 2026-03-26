<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkCenter extends Model
{
    protected $fillable = [
        'tenant_id', 'code', 'name', 'cost_per_hour',
        'capacity_per_day', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'cost_per_hour'    => 'decimal:2',
            'capacity_per_day' => 'integer',
            'is_active'        => 'boolean',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function operations(): HasMany { return $this->hasMany(WorkOrderOperation::class); }
}
