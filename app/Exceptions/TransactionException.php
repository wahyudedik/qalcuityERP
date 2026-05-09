<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when a transaction fails and needs rollback.
 * Used for financial operations that require atomicity.
 */
class TransactionException extends Exception
{
    protected string $transactionType;

    protected array $context;

    public function __construct(
        string $message,
        string $transactionType = 'general',
        array $context = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        $this->transactionType = $transactionType;
        $this->context = $context;

        parent::__construct($message, $code, $previous);
    }

    public function getTransactionType(): string
    {
        return $this->transactionType;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public static function rollbackRequired(
        string $message,
        string $type,
        array $data = []
    ): self {
        return new self(
            message: "ROLLBACK REQUIRED: {$message}",
            transactionType: $type,
            context: array_merge($data, ['requires_rollback' => true])
        );
    }

    public static function compensationNeeded(
        string $message,
        string $type,
        array $completedSteps = []
    ): self {
        return new self(
            message: "COMPENSATION NEEDED: {$message}",
            transactionType: $type,
            context: [
                'completed_steps' => $completedSteps,
                'requires_compensation' => true,
            ]
        );
    }
}
