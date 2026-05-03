<?php

namespace App\DTOs\Audit;

class AuditFinding
{
    public function __construct(
        public readonly string $category,
        public readonly Severity $severity,
        public readonly string $title,
        public readonly string $description,
        public readonly ?string $file,
        public readonly ?int $line,
        public readonly ?string $recommendation,
        public readonly array $metadata = [],
    ) {}
}
