<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnomalyAlert extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'type', 'severity', 'title', 'description',
        'data', 'status', 'acknowledged_by', 'acknowledged_at',
    ];

    protected $casts = [
        'data'              => 'array',
        'acknowledged_at'   => 'datetime',
    ];

    public function tenant(): BelongsTo           { return $this->belongsTo(Tenant::class); }
    public function acknowledgedBy(): BelongsTo   { return $this->belongsTo(User::class, 'acknowledged_by'); }
}
