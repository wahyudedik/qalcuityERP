<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProactiveInsight extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'condition_type',
        'urgency',
        'title',
        'description',
        'business_impact',
        'recommendations',
        'condition_data',
        'condition_hash',
        'suppressed_until',
    ];

    protected function casts(): array
    {
        return [
            'recommendations'  => 'array',
            'condition_data'   => 'array',
            'suppressed_until' => 'datetime',
        ];
    }

    public function reads(): HasMany
    {
        return $this->hasMany(InsightRead::class, 'insight_id');
    }
}
