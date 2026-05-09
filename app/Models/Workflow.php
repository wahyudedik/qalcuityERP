<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'trigger_type', // event, schedule, condition
        'trigger_config', // JSON configuration
        'is_active',
        'priority',
        'execution_count',
        'last_executed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'trigger_config' => 'array',
            'is_active' => 'boolean',
            'priority' => 'integer',
            'execution_count' => 'integer',
            'last_executed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(WorkflowAction::class)->orderBy('order');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WorkflowExecutionLog::class);
    }

    /**
     * Execute this workflow
     */
    public function execute(array $context = []): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $this->increment('execution_count');
        $this->update(['last_executed_at' => now()]);

        // Log execution
        $log = WorkflowExecutionLog::create([
            'tenant_id' => $this->tenant_id,
            'workflow_id' => $this->id,
            'triggered_by' => $context['triggered_by'] ?? null,
            'context_data' => $context,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            // Execute all active actions in order
            foreach ($this->actions()->where('is_active', true)->orderBy('order')->get() as $action) {
                $result = $action->execute($context);

                if (! $result['success']) {
                    throw new \Exception('Action failed: '.($result['error'] ?? 'Unknown error'));
                }
            }

            $log->complete('success');

            return true;
        } catch (\Exception $e) {
            $log->complete('failed', $e->getMessage());

            return false;
        }
    }
}
