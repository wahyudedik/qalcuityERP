<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Achievement extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'icon',
        'category',
        'color',
        'points',
        'requirement_type',
        'requirement_model',
        'requirement_action',
        'requirement_value',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'requirement_value' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('category');
    }
}
