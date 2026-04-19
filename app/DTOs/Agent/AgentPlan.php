<?php

namespace App\DTOs\Agent;

class AgentPlan
{
    public function __construct(
        public readonly string $goal,
        public readonly array $steps,      // AgentStep[]
        public readonly string $summary,
        public readonly bool $hasWriteOps,
        public readonly string $language,
    ) {}
}
