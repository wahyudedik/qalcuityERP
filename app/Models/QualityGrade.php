<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QualityGrade extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'grade_code',
        'grade_name',
        'rank',
        'criteria',
        'min_freshness_score',
        'price_multiplier',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rank' => 'integer',
            'min_freshness_score' => 'decimal:2',
            'price_multiplier' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function catchLogs(): HasMany
    {
        return $this->hasMany(CatchLog::class, 'quality_grade_id');
    }
}
