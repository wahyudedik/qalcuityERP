<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishSpecies extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'species_code',
        'common_name',
        'scientific_name',
        'category',
        'family',
        'avg_weight',
        'max_weight',
        'market_price_per_kg',
        'preferred_habitat',
        'characteristics',
        'description',
        'is_endangered',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'avg_weight' => 'decimal:2',
            'max_weight' => 'decimal:2',
            'market_price_per_kg' => 'decimal:2',
            'characteristics' => 'array',
            'is_endangered' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public const CATEGORIES = [
        'marine' => 'Marine',
        'freshwater' => 'Freshwater',
        'anadromous' => 'Anadromous',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function catchLogs(): HasMany
    {
        return $this->hasMany(CatchLog::class, 'species_id');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
