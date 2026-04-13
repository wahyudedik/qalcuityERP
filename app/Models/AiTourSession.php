<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiTourSession extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'tour_type',
        'current_step',
        'completed_steps',
        'is_active',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'completed_steps' => 'array',
        'is_active' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the tour session
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user that owns the tour session
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get tour type label
     */
    public function getTourTypeLabelAttribute(): string
    {
        return match ($this->tour_type) {
            'general' => 'General Tour',
            'module_specific' => 'Module Specific Tour',
            'feature_highlight' => 'Feature Highlight',
            default => ucfirst(str_replace('_', ' ', $this->tour_type)),
        };
    }

    /**
     * Complete a tour step
     */
    public function completeStep(string $step): void
    {
        $completedSteps = $this->completed_steps ?? [];

        if (!in_array($step, $completedSteps)) {
            $completedSteps[] = $step;
            $this->update([
                'completed_steps' => $completedSteps,
                'current_step' => $step,
            ]);
        }
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentageAttribute(): float
    {
        $totalSteps = $this->getTotalStepsForTourType();
        $completedCount = count($this->completed_steps ?? []);

        return $totalSteps > 0 ? round(($completedCount / $totalSteps) * 100, 2) : 0;
    }

    /**
     * Get total steps for this tour type
     */
    protected function getTotalStepsForTourType(): int
    {
        return match ($this->tour_type) {
            'general' => 5,
            'module_specific' => 3,
            'feature_highlight' => 4,
            default => 5,
        };
    }

    /**
     * Mark tour as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'is_active' => false,
            'completed_at' => now(),
        ]);
    }

    /**
     * Check if tour is completed
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }
}
