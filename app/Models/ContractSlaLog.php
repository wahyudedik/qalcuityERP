<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractSlaLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'contract_id', 'tenant_id', 'incident_type', 'description',
        'reported_at', 'responded_at', 'resolved_at', 'sla_met',
        'notes', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
            'responded_at' => 'datetime',
            'resolved_at' => 'datetime',
            'sla_met' => 'boolean',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function responseHours(): ?float
    {
        if (! $this->responded_at) {
            return null;
        }

        return round($this->reported_at->diffInMinutes($this->responded_at) / 60, 1);
    }

    public function resolutionHours(): ?float
    {
        if (! $this->resolved_at) {
            return null;
        }

        return round($this->reported_at->diffInMinutes($this->resolved_at) / 60, 1);
    }
}
