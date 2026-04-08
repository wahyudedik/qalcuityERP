<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PressRun extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'print_job_id',
        'press_machine',
        'run_start',
        'run_end',
        'target_quantity',
        'produced_quantity',
        'waste_quantity',
        'production_speed',
        'current_status',
        'ink_levels_c',
        'ink_levels_m',
        'ink_levels_y',
        'ink_levels_k',
        'registration_accuracy',
        'quality_checks',
        'operator_id',
        'run_notes'
    ];

    protected $casts = [
        'run_start' => 'datetime',
        'run_end' => 'datetime',
        'quality_checks' => 'array',
        'production_speed' => 'decimal:2',
        'registration_accuracy' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function printJob()
    {
        return $this->belongsTo(PrintJob::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->current_status) {
            'setup' => 'yellow',
            'running' => 'green',
            'paused' => 'orange',
            'stopped' => 'red',
            'completed' => 'blue',
            default => 'gray'
        };
    }

    public function getCompletionPercentageAttribute(): float
    {
        return $this->target_quantity > 0 ? ($this->produced_quantity / $this->target_quantity) * 100 : 0;
    }

    public function getWastePercentageAttribute(): float
    {
        return $this->produced_quantity > 0 ? ($this->waste_quantity / $this->produced_quantity) * 100 : 0;
    }
}
