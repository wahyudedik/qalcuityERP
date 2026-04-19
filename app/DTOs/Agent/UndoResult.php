<?php

namespace App\DTOs\Agent;

class UndoResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly ?array $restoredData = null,
    ) {}
}
