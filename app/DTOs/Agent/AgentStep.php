<?php

namespace App\DTOs\Agent;

class AgentStep
{
    public function __construct(
        public readonly int $order,
        public readonly string $name,
        public readonly string $toolName,
        public readonly array $args,
        public readonly bool $isWriteOp,
        public readonly ?string $dependsOnStep = null,
    ) {}
}
