<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowExecutionLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'workflow_id',
        'triggered_by', // event name or schedule
        'context_data',
        'status', // running, success, failed
        'error_message',
        'started_at',
        'completed_at',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'context_data' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'duration_ms' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Calculate duration when completing
     */
    public function complete(string $status, ?string $errorMessage = null): void
    {
        $this->update([
            'status' => $status,
            'error_message' => $errorMessage,
            'completed_at' => now(),
            'duration_ms' => $this->started_at ? now()->diffInMilliseconds($this->started_at) : 0,
        ]);
    }
}
