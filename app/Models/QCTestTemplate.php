<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class QCTestTemplate extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'template_name',
        'template_code',
        'test_category',
        'test_parameters',
        'acceptance_criteria',
        'procedure',
        'is_active',
    ];

    protected $casts = [
        'test_parameters' => 'array',
        'acceptance_criteria' => 'array',
        'is_active' => 'boolean',
    ];

    // Category labels
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->test_category) {
            'microbial' => 'Microbial Testing',
            'heavy_metal' => 'Heavy Metal Testing',
            'preservative' => 'Preservative Efficacy',
            'patch_test' => 'Patch Test',
            'physical' => 'Physical Testing',
            'chemical' => 'Chemical Testing',
            default => ucfirst(str_replace('_', ' ', $this->test_category))
        };
    }

    // Scope: Active templates only
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: Filter by category
    public function scopeCategory($query, $category)
    {
        return $query->where('test_category', $category);
    }

    // Relationship: Tests using this template
    public function testResults()
    {
        return $this->hasMany(QCTestResult::class, 'template_id');
    }

    // Get full parameters with criteria
    public function getFullParametersAttribute(): array
    {
        $parameters = $this->test_parameters ?? [];
        $criteria = $this->acceptance_criteria ?? [];

        return collect($parameters)->map(function ($param) use ($criteria) {
            $param['criteria'] = $criteria[$param['name']] ?? null;
            return $param;
        })->toArray();
    }

    // Generate next template code
    public static function getNextTemplateCode(): string
    {
        $count = self::count() + 1;
        return 'TMP-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    // Duplicate template for new version
    public function duplicate(string $newName): self
    {
        $newTemplate = $this->replicate();
        $newTemplate->template_name = $newName;
        $newTemplate->template_code = self::getNextTemplateCode();
        $newTemplate->save();

        return $newTemplate;
    }
}
