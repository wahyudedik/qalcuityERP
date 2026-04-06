<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SafetyDataSheet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'formula_id',
        'registration_id',
        'sds_number',
        'product_name',
        'version',
        'issue_date',
        'review_date',
        'hazard_statements',
        'precautionary_statements',
        'first_aid_measures',
        'fire_fighting_measures',
        'handling_storage',
        'file_path',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'review_date' => 'date',
        'hazard_statements' => 'array',
        'precautionary_statements' => 'array',
    ];

    // Status labels
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Active',
            'outdated' => 'Outdated',
            default => 'Draft'
        };
    }

    // Check if needs review (older than 3 years)
    public function needsReview(): bool
    {
        if (!$this->review_date) {
            return $this->issue_date->diffInYears(now()) >= 3;
        }
        return $this->review_date->isPast();
    }

    // Activate SDS
    public function activate(): void
    {
        $this->status = 'active';
        $this->save();
    }

    // Mark as outdated
    public function markOutdated(): void
    {
        $this->status = 'outdated';
        $this->save();
    }

    // Create new version
    public function createNewVersion(): self
    {
        $newVersion = $this->replicate();
        $currentVersion = floatval($this->version);
        $newVersion->version = number_format($currentVersion + 0.1, 1);
        $newVersion->sds_number = self::getNextSdsNumber();
        $newVersion->issue_date = now();
        $newVersion->status = 'draft';
        $newVersion->save();

        // Mark old as outdated
        $this->markOutdated();

        return $newVersion;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeNeedsReview($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('review_date')
                    ->orWhere('review_date', '<=', now());
            });
    }

    // Relationships
    public function formula(): BelongsTo
    {
        return $this->belongsTo(CosmeticFormula::class, 'formula_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(ProductRegistration::class, 'registration_id');
    }

    // Generate next SDS number
    public static function getNextSdsNumber(): string
    {
        $year = now()->format('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'SDS-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
