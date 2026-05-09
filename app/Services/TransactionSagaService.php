<?php

namespace App\Services;

use App\Exceptions\TransactionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Saga pattern implementation for multi-step financial transactions.
 *
 * A saga is a sequence of local transactions where each step updates
 * the database and publishes an event/message to trigger the next step.
 * If a step fails, compensating transactions are executed to undo
 * the effects of all previously completed steps.
 */
class TransactionSagaService
{
    /**
     * List of completed steps for potential rollback
     */
    protected array $completedSteps = [];

    /**
     * List of compensating actions (reversible operations)
     */
    protected array $compensations = [];

    /**
     * Whether the saga has been committed
     */
    protected bool $committed = false;

    /**
     * Execute a saga with multiple steps and automatic rollback on failure.
     *
     * @param  callable[]  $steps  Associative array of step_name => callable
     * @param  callable[]  $compensations  Associative array of step_name => compensation_callable
     * @param  string  $sagaType  Type identifier for this saga (e.g., 'invoice_payment')
     * @param  array  $context  Contextual data passed to all steps
     * @return array Result containing success status and data
     *
     * @throws TransactionException
     */
    public function execute(
        array $steps,
        array $compensations = [],
        string $sagaType = 'financial',
        array $context = []
    ): array {
        $this->completedSteps = [];
        $this->compensations = $compensations;
        $this->committed = false;

        try {
            DB::beginTransaction();

            foreach ($steps as $stepName => $step) {
                Log::info("Saga {$sagaType}: Executing step '{$stepName}'", [
                    'context' => $context,
                    'step_index' => count($this->completedSteps) + 1,
                ]);

                // Execute step
                $result = $step($context);

                // Track completed step
                $this->completedSteps[] = [
                    'name' => $stepName,
                    'result' => $result,
                    'context' => $context,
                ];

                // Update context with result for next step
                $context = array_merge($context, $result ?? []);

                Log::info("Saga {$sagaType}: Step '{$stepName}' completed successfully");
            }

            // All steps succeeded - commit
            DB::commit();
            $this->committed = true;

            Log::info("Saga {$sagaType}: Completed successfully", [
                'total_steps' => count($this->completedSteps),
                'steps' => array_column($this->completedSteps, 'name'),
            ]);

            return [
                'success' => true,
                'data' => $context,
                'steps_completed' => $this->completedSteps,
            ];

        } catch (\Throwable $e) {
            Log::error(
                "Saga {$sagaType}: Failed at step '".
                (isset($stepName) ? $stepName : 'unknown').
                "' - Rolling back",
                [
                    'error' => $e->getMessage(),
                    'completed_steps' => array_column($this->completedSteps, 'name'),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            // Rollback database transaction
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            // Execute compensating transactions if needed
            if (! empty($this->completedSteps)) {
                $this->executeCompensations($sagaType, $e);
            }

            throw TransactionException::compensationNeeded(
                message: $e->getMessage(),
                type: $sagaType,
                completedSteps: array_column($this->completedSteps, 'name')
            );
        }
    }

    /**
     * Execute compensating transactions in reverse order
     */
    protected function executeCompensations(string $sagaType, \Throwable $originalException): void
    {
        Log::warning("Saga {$sagaType}: Executing compensating transactions", [
            'reason' => $originalException->getMessage(),
            'steps_to_compensate' => count($this->completedSteps),
        ]);

        // Reverse the completed steps
        $stepsToCompensate = array_reverse($this->completedSteps);

        foreach ($stepsToCompensate as $step) {
            $stepName = $step['name'];

            if (isset($this->compensations[$stepName])) {
                try {
                    Log::info("Saga {$sagaType}: Compensating step '{$stepName}'");

                    $compensation = $this->compensations[$stepName];
                    $compensation($step['result'], $step['context']);

                    Log::info("Saga {$sagaType}: Compensation for '{$stepName}' completed");
                } catch (\Throwable $compensationError) {
                    // CRITICAL: Compensation failed - log for manual intervention
                    Log::critical("Saga {$sagaType}: Compensation FAILED for step '{$stepName}'", [
                        'error' => $compensationError->getMessage(),
                        'original_error' => $originalException->getMessage(),
                        'requires_manual_intervention' => true,
                    ]);

                    // Don't throw here - continue with other compensations
                    // but mark this as requiring manual review
                }
            } else {
                Log::warning("Saga {$sagaType}: No compensation defined for step '{$stepName}'");
            }
        }
    }

    /**
     * Execute a simple atomic operation with automatic rollback.
     * Simpler than full saga when you don't need compensating transactions.
     *
     * @param  callable  $operation  The database operation to execute
     * @param  string  $operationType  Type identifier for logging
     * @return mixed Result from the operation
     *
     * @throws TransactionException
     */
    public function atomic(callable $operation, string $operationType = 'transaction')
    {
        try {
            return DB::transaction($operation);
        } catch (\Throwable $e) {
            Log::error("Atomic {$operationType} failed: ".$e->getMessage(), [
                'type' => $operationType,
                'trace' => $e->getTraceAsString(),
            ]);

            throw TransactionException::rollbackRequired(
                message: "Atomic {$operationType} failed: ".$e->getMessage(),
                type: $operationType
            );
        }
    }

    /**
     * Check if saga completed successfully
     */
    public function isCommitted(): bool
    {
        return $this->committed;
    }

    /**
     * Get list of completed steps
     */
    public function getCompletedSteps(): array
    {
        return $this->completedSteps;
    }

    /**
     * Reset saga state
     */
    public function reset(): void
    {
        $this->completedSteps = [];
        $this->compensations = [];
        $this->committed = false;
    }
}
