<?php

namespace App\Services\DemoData;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseIndustryGenerator
{
    abstract public function generate(CoreDataContext $ctx): array;

    abstract public function getIndustryName(): string;

    protected function bulkInsert(string $table, array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }

        DB::table($table)->insert($rows);

        return count($rows);
    }

    protected function logWarning(string $message, array $context = []): void
    {
        Log::warning($message, $context);
    }
}
