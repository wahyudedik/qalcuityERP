<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SourcingOpportunity extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'category',
        'estimated_annual_spend',
        'potential_suppliers_count',
        'priority',
        'status',
        'potential_savings',
        'savings_percentage',
        'target_completion_date',
        'actual_completion_date',
        'assigned_to',
        'strategy_notes',
        'risks'
    ];

    protected $casts = [
        'estimated_annual_spend' => 'decimal:2',
        'potential_savings' => 'decimal:2',
        'savings_percentage' => 'decimal:2',
        'target_completion_date' => 'date',
        'actual_completion_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'critical' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'blue',
            default => 'gray'
        };
    }
}