<?php

namespace App\Exceptions;

class InsufficientPlanException extends \RuntimeException
{
    public function __construct(
        public readonly string $requiredPlan,
        public readonly string $currentPlan,
        public readonly string $useCase,
    ) {
        parent::__construct(
            "Fitur ini memerlukan plan {$requiredPlan}. Upgrade plan Anda untuk mengakses {$useCase}."
        );
    }
}
