<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrepressWorkflow extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'print_job_id',
        'workflow_stage',
        'status',
        'instructions',
        'adjustments',
        'imposition_layout',
        'pages_per_sheet',
        'proof_type',
        'started_at',
        'completed_at',
        'technician_id',
        'rejection_reason',
    ];

    protected $casts = [
        'adjustments' => 'array',
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

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'gray',
            'in_progress' => 'blue',
            'completed' => 'green',
            'rejected' => 'red',
            default => 'gray'
        };
    }
}
