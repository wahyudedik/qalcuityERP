<?php

namespace App\DTOs;

class TransactionChainDTO
{
    public function __construct(
        public readonly ChainNodeDTO $current,
        public readonly array        $upstream,
        public readonly array        $downstream,
        public readonly array        $all,
    ) {}
}
