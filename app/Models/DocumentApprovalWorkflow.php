<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentApprovalWorkflow extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'document_type',
        'approval_steps',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'approval_steps' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the tenant that owns the workflow
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get all approval requests for this workflow
     */
    public function approvalRequests(): HasMany
    {
        return $this->hasMany(DocumentApprovalRequest::class);
    }

    /**
     * Scope to get active workflows
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by document type
     */
    public function scopeForDocumentType($query, string $type)
    {
        return $query->where(function ($q) use ($type) {
            $q->where('document_type', $type)
                ->orWhereNull('document_type');
        });
    }

    /**
     * Get the next step number
     */
    public function getNextStepNumber(): int
    {
        return count($this->approval_steps ?? []) + 1;
    }

    /**
     * Check if workflow has steps
     */
    public function hasSteps(): bool
    {
        return !empty($this->approval_steps) && is_array($this->approval_steps);
    }

    /**
     * Get step configuration by step number
     */
    public function getStepConfig(int $stepNumber): ?array
    {
        $steps = $this->approval_steps ?? [];
        return $steps[$stepNumber - 1] ?? null;
    }
}
