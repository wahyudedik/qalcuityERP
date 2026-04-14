<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreedingRecord extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'livestock_herd_id',
        'dam_id',
        'sire_id',
        'mating_date',
        'mating_type',
        'expected_due_date',
        'actual_birth_date',
        'offspring_count',
        'live_births',
        'stillbirths',
        'birth_weight_avg_kg',
        'genetics_line',
        'genetic_traits',
        'status',
        'notes',
        'recorded_by'
    ];

    protected $casts = [
        'mating_date' => 'date',
        'expected_due_date' => 'date',
        'actual_birth_date' => 'date',
        'offspring_count' => 'integer',
        'live_births' => 'integer',
        'stillbirths' => 'integer',
        'birth_weight_avg_kg' => 'decimal:2',
        'genetic_traits' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function herd(): BelongsTo
    {
        return $this->belongsTo(LivestockHerd::class, 'livestock_herd_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getMatingTypeLabelAttribute(): string
    {
        return match ($this->mating_type) {
            'natural' => 'Natural Mating',
            'artificial_insemination' => 'Artificial Insemination (AI)',
            'embryo_transfer' => 'Embryo Transfer (ET)',
            default => ucfirst(str_replace('_', ' ', $this->mating_type))
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending/Mating',
            'pregnant' => 'Pregnant',
            'born' => 'Offspring Born',
            'failed' => 'Failed/Aborted',
            default => ucfirst($this->status)
        };
    }

    /**
     * Calculate days until expected birth
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->expected_due_date) {
            return null;
        }

        return now()->diffInDays($this->expected_due_date, false);
    }

    /**
     * Check if offspring was born on time
     */
    public function isOnTime(): bool
    {
        if (!$this->expected_due_date || !$this->actual_birth_date) {
            return false;
        }

        // Consider on-time if within ±7 days of expected date
        $diff = abs($this->expected_due_date->diffInDays($this->actual_birth_date));
        return $diff <= 7;
    }

    /**
     * Calculate survival rate percentage
     */
    public function getSurvivalRateAttribute(): ?float
    {
        if (!$this->offspring_count || $this->offspring_count == 0) {
            return null;
        }

        return round(($this->live_births / $this->offspring_count) * 100, 2);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePregnant($query)
    {
        return $query->where('status', 'pregnant');
    }

    public function scopeUpcomingBirths($query, $days = 30)
    {
        return $query->where('status', 'pregnant')
            ->whereBetween('expected_due_date', [now(), now()->addDays($days)]);
    }
}