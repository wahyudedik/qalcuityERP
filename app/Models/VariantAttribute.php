<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VariantAttribute extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'attribute_name',
        'attribute_type',
        'attribute_values',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'attribute_values' => 'array',
        'is_required' => 'boolean',
    ];

    // Type labels
    public function getTypeLabelAttribute(): string
    {
        return match ($this->attribute_type) {
            'select' => 'Dropdown Select',
            'color' => 'Color Picker',
            'text' => 'Text Input',
            'number' => 'Number Input',
            default => ucfirst($this->attribute_type)
        };
    }

    // Scopes
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Add value to attribute
    public function addValue(string $value): void
    {
        $values = $this->attribute_values ?? [];
        if (!in_array($value, $values)) {
            $values[] = $value;
            $this->attribute_values = $values;
            $this->save();
        }
    }

    // Remove value from attribute
    public function removeValue(string $value): void
    {
        $values = $this->attribute_values ?? [];
        $this->attribute_values = array_values(array_diff($values, [$value]));
        $this->save();
    }

    // Validate value
    public function isValidValue($value): bool
    {
        if ($this->attribute_type === 'text') {
            return true;
        }

        $values = $this->attribute_values ?? [];
        return in_array($value, $values);
    }
}
