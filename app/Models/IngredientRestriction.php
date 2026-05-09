<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IngredientRestriction extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'ingredient_name',
        'cas_number',
        'restriction_type',
        'max_limit',
        'regulation_reference',
        'notes',
    ];

    protected $casts = [
        'max_limit' => 'decimal:2',
    ];

    // Restriction type labels
    public function getTypeLabelAttribute(): string
    {
        return match ($this->restriction_type) {
            'banned' => 'Banned',
            'restricted' => 'Restricted',
            'limited' => 'Limited',
            default => ucfirst($this->restriction_type)
        };
    }

    // Scopes
    public function scopeBanned($query)
    {
        return $query->where('restriction_type', 'banned');
    }

    public function scopeRestricted($query)
    {
        return $query->where('restriction_type', 'restricted');
    }

    // Check if ingredient is banned
    public function isBanned(): bool
    {
        return $this->restriction_type === 'banned';
    }

    // Check if ingredient has limit
    public function hasLimit(): bool
    {
        return $this->max_limit !== null;
    }

    // Validate ingredient percentage
    public function validatePercentage(float $percentage): array
    {
        if ($this->isBanned()) {
            return [
                'valid' => false,
                'message' => 'This ingredient is banned',
            ];
        }

        if ($this->hasLimit() && $percentage > $this->max_limit) {
            return [
                'valid' => false,
                'message' => "Exceeds maximum limit of {$this->max_limit}%",
            ];
        }

        return [
            'valid' => true,
            'message' => 'Compliant',
        ];
    }
}
