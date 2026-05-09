<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinishingOperation extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'print_job_id',
        'operation_type',
        'status',
        'sequence_order',
        'operation_specs',
        'target_quantity',
        'completed_quantity',
        'waste_quantity',
        'started_at',
        'completed_at',
        'operator_id',
        'machine_used',
        'quality_notes',
        'issues',
    ];

    protected $casts = [
        'operation_specs' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
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
        return match ($this->status) {
            'pending' => 'gray',
            'in_progress' => 'blue',
            'completed' => 'green',
            'failed' => 'red',
            default => 'gray'
        };
    }

    public function getCompletionPercentageAttribute(): float
    {
        return $this->target_quantity > 0 ? ($this->completed_quantity / $this->target_quantity) * 100 : 0;
    }
}
