<?php

namespace App\Services;

use App\Models\Workflow;
use App\Models\WorkflowExecutionLog;
use Illuminate\Support\Facades\Log;

class WorkflowEngine
{
    protected array $eventListeners = [];

    /**
     * Register event listener
     */
    public function registerTrigger(string $event, callable $callback): void
    {
        if (!isset($this->eventListeners[$event])) {
            $this->eventListeners[$event] = [];
        }

        $this->eventListeners[$event][] = $callback;
    }

    /**
     * Fire event and execute matching workflows
     */
    public function fireEvent(string $event, array $context = [], ?int $tenantId = null): void
    {
        try {
            // Find workflows triggered by this event
            $query = Workflow::where('trigger_type', 'event')
                ->where('is_active', true)
                ->whereJsonContains('trigger_config->event', $event)
                ->orderBy('priority', 'desc');

            // Scope to tenant if provided
            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }

            $workflows = $query->get();

            foreach ($workflows as $workflow) {
                $workflow->execute(array_merge($context, [
                    'triggered_by' => $event,
                    'fired_at' => now()->toIso8601String(),
                ]));
            }

            // Call registered listeners
            if (isset($this->eventListeners[$event])) {
                foreach ($this->eventListeners[$event] as $callback) {
                    $callback($context);
                }
            }
        } catch (\Exception $e) {
            Log::error('Workflow Event Fire Error', [
                'event' => $event,
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Execute scheduled workflows
     */
    public function executeScheduled(): void
    {
        $now = now();

        $workflows = Workflow::where('trigger_type', 'schedule')
            ->where('is_active', true)
            ->get();

        foreach ($workflows as $workflow) {
            $schedule = $workflow->trigger_config['schedule'] ?? null;

            if ($this->shouldExecute($schedule, $now)) {
                try {
                    $workflow->execute([
                        'triggered_by' => 'schedule:' . $schedule,
                        'executed_at' => $now->toIso8601String(),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Scheduled Workflow Execution Error', [
                        'workflow_id' => $workflow->id,
                        'tenant_id' => $workflow->tenant_id,
                        'schedule' => $schedule,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Check if schedule should execute now
     */
    private function shouldExecute(?string $schedule, $now): bool
    {
        if (!$schedule) {
            return false;
        }

        return match ($schedule) {
            'every_minute' => true,
            'hourly' => $now->minute === 0,
            'daily_9am', 'invoice_overdue_check' => $now->hour === 9 && $now->minute === 0,
            'daily_midnight' => $now->hour === 0 && $now->minute === 0,
            'weekly_monday' => $now->dayOfWeek === 1 && $now->hour === 0 && $now->minute === 0,
            'monthly_first', 'monthly_bonus_calculation' => $now->day === 1 && $now->hour === 0 && $now->minute === 0,
            default => false,
        };
    }

    /**
     * Get workflow statistics
     */
    public function getStatistics(int $tenantId): array
    {
        $totalWorkflows = Workflow::where('tenant_id', $tenantId)->count();
        $activeWorkflows = Workflow::where('tenant_id', $tenantId)->where('is_active', true)->count();

        $todayExecutions = WorkflowExecutionLog::where('tenant_id', $tenantId)
            ->whereDate('started_at', today())
            ->count();

        $successRate = WorkflowExecutionLog::where('tenant_id', $tenantId)
            ->whereDate('started_at', today())
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success')
            ->first();

        $successPercentage = $successRate && $successRate->total > 0
            ? round(($successRate->success / $successRate->total) * 100, 2)
            : 0;

        return [
            'total_workflows' => $totalWorkflows,
            'active_workflows' => $activeWorkflows,
            'today_executions' => $todayExecutions,
            'success_rate' => $successPercentage,
        ];
    }

    /**
     * Test workflow execution
     */
    public function testWorkflow(Workflow $workflow, array $testContext = []): array
    {
        $startTime = microtime(true);

        try {
            $success = $workflow->execute(array_merge($testContext, [
                'triggered_by' => 'test',
                'test_mode' => true,
            ]));

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'success' => $success,
                'duration_ms' => $duration,
                'message' => $success ? 'Workflow executed successfully' : 'Workflow execution failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'error' => $e->getMessage(),
            ];
        }
    }
}
