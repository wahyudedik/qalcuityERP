<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnboardingProgress extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'step_key',
        'step_name',
        'category',
        'completed',
        'completed_at',
        'order',
        'description',
        'metadata',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'completed_at' => 'datetime',
        'order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the tenant that owns the progress
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user that owns the progress
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark step as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'completed' => true,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark step as incomplete
     */
    public function markAsIncomplete(): void
    {
        $this->update([
            'completed' => false,
            'completed_at' => null,
        ]);
    }

    /**
     * Check if step is completed
     */
    public function isCompleted(): bool
    {
        return $this->completed;
    }

    /**
     * Scope: Completed steps only
     */
    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    /**
     * Scope: Incomplete steps only
     */
    public function scopeIncomplete($query)
    {
        return $query->where('completed', false);
    }

    /**
     * Scope: Filter by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Order by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'setup' => 'Setup',
            'configuration' => 'Configuration',
            'first_action' => 'First Action',
            'collaboration' => 'Collaboration',
            'module' => 'Module',
            default => ucfirst(str_replace('_', ' ', $this->category)),
        };
    }
}
