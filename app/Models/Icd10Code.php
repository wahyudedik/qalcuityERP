<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Icd10Code extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'category',
        'subcategory',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope: Active codes only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Search by code or description
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('code', 'like', "%{$searchTerm}%")
                ->orWhere('description', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Scope: By category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get formatted code with description
     */
    public function getFormattedAttribute()
    {
        return "{$this->code} - {$this->description}";
    }
}
