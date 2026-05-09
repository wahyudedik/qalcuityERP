<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Subcontractor Management untuk Konstruksi
 */
class Subcontractor extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'specialization', // electrical, plumbing, structural, finishing, etc
        'license_number',
        'tax_id',
        'status', // active, inactive, blacklisted
        'rating',
        'total_projects',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:1',
            'total_projects' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(SubcontractorContract::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'subcontractor_project')
            ->withPivot('role', 'start_date', 'end_date', 'contract_value')
            ->withTimestamps();
    }

    /**
     * Get active contracts count
     */
    public function getActiveContractsCount(): int
    {
        return $this->contracts()->where('status', 'active')->count();
    }

    /**
     * Calculate average rating from completed projects
     */
    public function calculateAverageRating(): float
    {
        $completedContracts = $this->contracts()
            ->where('status', 'completed')
            ->whereNotNull('performance_rating')
            ->avg('performance_rating');

        return round($completedContracts ?? 0, 1);
    }
}
