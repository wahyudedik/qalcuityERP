<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SafetyDataSheet extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(CosmeticFormula::class, 'formula_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(ProductRegistration::class, 'registration_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOutdated(): bool
    {
        return $this->status === 'outdated';
    }

    public function needsReview(): bool
    {
        if (! $this->review_date) {
            return false;
        }

        return now()->gt($this->review_date);
    }

    public function activate(): void
    {
        $this->status = 'active';
        $this->save();
    }

    public function markOutdated(): void
    {
        $this->status = 'outdated';
        $this->save();
    }

    public static function getNextSdsNumber(): string
    {
        $year = now()->format('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;

        return 'SDS-'.$year.'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeNeedsReview($query)
    {
        return $query->whereNotNull('review_date')
            ->where('review_date', '<=', now());
    }
}
