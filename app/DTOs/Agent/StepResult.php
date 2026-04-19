<?php

namespace App\DTOs\Agent;

class StepResult
{
    public function __construct(
        public readonly int $stepOrder,
        public readonly string $status,        // success | failed
        public readonly mixed $output,
        public readonly ?string $errorMessage = null,
    ) {}

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }
}
