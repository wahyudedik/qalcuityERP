<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierScorecard extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'period',
        'period_start',
        'period_end',
        'quality_score',
        'total_deliveries',
        'defective_items',
        'defect_rate',
        'delivery_score',
        'on_time_deliveries',
        'late_deliveries',
        'on_time_percentage',
        'avg_lead_time_days',
        'cost_score',
        'price_competitiveness',
        'cost_savings_identified',
        'total_spend',
        'service_score',
        'response_time_hours_avg',
        'issues_resolved',
        'total_issues',
        'issue_resolution_rate',
        'overall_score',
        'rating',
        'status',
        'strengths',
        'areas_for_improvement',
        'action_items',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'quality_score' => 'decimal:2',
        'delivery_score' => 'decimal:2',
        'cost_score' => 'decimal:2',
        'service_score' => 'decimal:2',
        'overall_score' => 'decimal:2',
        'defect_rate' => 'decimal:2',
        'on_time_percentage' => 'decimal:2',
        'avg_lead_time_days' => 'decimal:2',
        'price_competitiveness' => 'decimal:2',
        'issue_resolution_rate' => 'decimal:2',
        'total_spend' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function calculateRating(): string
    {
        if ($this->overall_score >= 90)
            return 'A';
        if ($this->overall_score >= 80)
            return 'B';
        if ($this->overall_score >= 70)
            return 'C';
        if ($this->overall_score >= 60)
            return 'D';
        return 'F';
    }

    public function calculateStatus(): string
    {
        if ($this->overall_score >= 80)
            return 'active';
        if ($this->overall_score >= 60)
            return 'warning';
        return 'critical';
    }

    public function updateRatingAndStatus(): void
    {
        $this->rating = $this->calculateRating();
        $this->status = $this->calculateStatus();
        $this->save();
    }

    public function getRatingColorAttribute(): string
    {
        return match ($this->rating) {
            'A' => 'green',
            'B' => 'blue',
            'C' => 'yellow',
            'D' => 'orange',
            'F' => 'red',
            default => 'gray'
        };
    }
}
