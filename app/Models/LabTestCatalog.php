<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestCatalog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'test_code',
        'test_name',
        'category',
        'subcategory',
        'description',
        'price',
        'cost',
        'turnaround_time',
        'is_stat_available',
        'stat_turnaround_time',
        'sample_type',
        'container_type',
        'minimum_volume',
        'collection_instructions',
        'is_active',
        'requires_fasting',
        'is_package',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'turnaround_time' => 'integer',
        'is_stat_available' => 'boolean',
        'stat_turnaround_time' => 'integer',
        'minimum_volume' => 'integer',
        'is_active' => 'boolean',
        'requires_fasting' => 'boolean',
        'is_package' => 'boolean',
    ];

    /**
     * Scope: Active tests only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Search by code or name
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('test_code', 'like', "%{$searchTerm}%")
                ->orWhere('test_name', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Scope: Requires fasting
     */
    public function scopeRequiresFasting($query)
    {
        return $query->where('requires_fasting', true);
    }

    /**
     * Scope: STAT tests available
     */
    public function scopeStatAvailable($query)
    {
        return $query->where('is_stat_available', true);
    }

    /**
     * Get full test name with code
     */
    public function getFullTestNameAttribute()
    {
        return "{$this->test_code} - {$this->test_name}";
    }

    /**
     * Calculate profit margin
     */
    public function getProfitMarginAttribute()
    {
        if ($this->price > 0) {
            return (($this->price - $this->cost) / $this->price) * 100;
        }
        return 0;
    }

    /**
     * Get turnaround time display
     */
    public function getTurnaroundDisplayAttribute()
    {
        if ($this->turnaround_time < 24) {
            return "{$this->turnaround_time} hours";
        }

        $days = floor($this->turnaround_time / 24);
        return "{$days} day(s)";
    }
}
