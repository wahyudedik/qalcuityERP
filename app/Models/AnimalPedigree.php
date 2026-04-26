<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalPedigree extends Model
{
    use BelongsToTenant;

    protected $table = 'animal_pedigrees';

    protected $fillable = [
        'tenant_id',
        'animal_id',
        'animal_name',
        'breed',
        'birth_date',
        'gender',
        'dam_id',
        'sire_id',
        'genetic_line',
        'genetic_markers',
        'birth_weight_kg',
        'performance_data',
        'registration_number',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'birth_weight_kg' => 'decimal:2',
            'genetic_markers' => 'json',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
