<?php

namespace App\DTOs;

class ChainNodeDTO
{
    public function __construct(
        public readonly string $type,
        public readonly int    $id,
        public readonly string $number,
        public readonly string $date,
        public readonly string $status,
        public readonly float  $amount,
        public readonly string $url,
    ) {}
}
