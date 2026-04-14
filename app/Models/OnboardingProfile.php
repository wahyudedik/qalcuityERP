<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnboardingProfile extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'industry',
        'business_size',
        'employee_count',
        'selected_modules',
        'preferences',
        'sample_data_generated',
        'completed_at',
        'skipped',
    ];

    protected $casts = [
        'selected_modules' => 'array',
        'preferences' => 'array',
        'sample_data_generated' => 'boolean',
        'completed_at' => 'datetime',
        'skipped' => 'boolean',
    ];

    /**
     * Get the tenant that owns the profile
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user that owns the profile
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get industry label
     */
    public function getIndustryLabelAttribute(): string
    {
        return match ($this->industry) {
            'retail' => 'Retail',
            'restaurant' => 'Restaurant',
            'hotel' => 'Hotel',
            'construction' => 'Construction',
            'agriculture' => 'Agriculture',
            'manufacturing' => 'Manufacturing',
            'services' => 'Services',
            default => ucfirst($this->industry),
        };
    }

    /**
     * Get business size label
     */
    public function getBusinessSizeLabelAttribute(): string
    {
        return match ($this->business_size) {
            'micro' => 'Micro (1-5 employees)',
            'small' => 'Small (6-50 employees)',
            'medium' => 'Medium (51-200 employees)',
            'large' => 'Large (200+ employees)',
            default => ucfirst($this->business_size),
        };
    }

    /**
     * Check if onboarding is completed
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Check if onboarding was skipped
     */
    public function isSkipped(): bool
    {
        return $this->skipped ?? false;
    }
}