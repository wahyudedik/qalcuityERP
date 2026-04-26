<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompostingBatch extends Model
{
    use BelongsToTenant;

    protected $table = 'composting_batches';

    protected $fillable = [
        'tenant_id',
        'batch_code',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'initial_weight_kg',
        'current_weight_kg',
        'final_weight_kg',
        'moisture_percentage',
        'temperature_celsius',
        'ph_level',
        'status',
        'quality_score',
        'ingredients',
        'turning_schedule',
        'notes',
        'managed_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expected_end_date' => 'date',
            'actual_end_date' => 'date',
            'initial_weight_kg' => 'decimal:2',
            'current_weight_kg' => 'decimal:2',
            'final_weight_kg' => 'decimal:2',
            'moisture_percentage' => 'decimal:2',
            'temperature_celsius' => 'decimal:2',
            'ph_level' => 'decimal:2',
            'quality_score' => 'decimal:2',
            'ingredients' => 'json',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'managed_by');
    }
}
