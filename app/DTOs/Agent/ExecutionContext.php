<?php

namespace App\DTOs\Agent;

class ExecutionContext
{
    private array $outputs = [];

    /**
     * Ambil output dari langkah tertentu.
     */
    public function get(int $stepOrder): mixed
    {
        return $this->outputs[$stepOrder] ?? null;
    }

    /**
     * Simpan output dari langkah tertentu.
     */
    public function set(int $stepOrder, mixed $output): void
    {
        $this->outputs[$stepOrder] = $output;
    }

    /**
     * Cek apakah output untuk langkah tertentu tersedia.
     */
    public function has(int $stepOrder): bool
    {
        return array_key_exists($stepOrder, $this->outputs);
    }

    /**
     * Ambil semua accumulated outputs.
     */
    public function all(): array
    {
        return $this->outputs;
    }
}
