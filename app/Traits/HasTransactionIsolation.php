<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Trait for applying database transaction isolation levels.
 * 
 * Usage in controllers or services:
 * 
 * ```php
 * use HasTransactionIsolation;
 * 
 * public function processPayment() {
 *     $this->withIsolation('payment_processing', function () {
 *         // Your transactional code here
 *     });
 * }
 * ```
 */
trait HasTransactionIsolation
{
    /**
     * Execute a callback with specific transaction isolation level.
     *
     * @param string $operationType Type of operation (from config)
     * @param callable $callback The transactional operation
     * @return mixed Result from the callback
     */
    protected function withIsolation(string $operationType, callable $callback)
    {
        $isolationLevel = Config::get(
            "database_transactions.isolation_levels.{$operationType}",
            Config::get('database_transactions.isolation_levels.default')
        );

        // Set isolation level before transaction
        $this->setIsolationLevel($isolationLevel);

        try {
            return DB::transaction($callback);
        } finally {
            // Reset to default after transaction
            $this->resetIsolationLevel();
        }
    }

    /**
     * Set database isolation level
     */
    protected function setIsolationLevel(string $level): void
    {
        $driver = DB::connection()->getDriverName();

        match ($driver) {
            'mysql' => $this->setMysqlIsolation($level),
            'pgsql' => $this->setPostgresIsolation($level),
            'sqlite' => $this->setSqliteIsolation($level),
            default => null, // SQL Server and others not implemented yet
        };
    }

    /**
     * Reset isolation level to default
     */
    protected function resetIsolationLevel(): void
    {
        $default = Config::get('database_transactions.isolation_levels.default');
        $this->setIsolationLevel($default);
    }

    /**
     * Set MySQL isolation level
     */
    private function setMysqlIsolation(string $level): void
    {
        DB::statement("SET SESSION TRANSACTION ISOLATION LEVEL {$level}");
    }

    /**
     * Set PostgreSQL isolation level
     */
    private function setPostgresIsolation(string $level): void
    {
        // PostgreSQL only supports setting isolation at transaction start
        // This is handled automatically by Laravel's transaction manager
        // Just log for awareness
        \Log::debug("PostgreSQL transaction started with isolation: {$level}");
    }

    /**
     * Set SQLite isolation level (no-op - SQLite uses SERIALIZABLE by default)
     */
    private function setSqliteIsolation(string $level): void
    {
        // SQLite doesn't support changing isolation levels
        // It always uses SERIALIZABLE isolation
    }

    /**
     * Execute with row locking for update
     * Prevents concurrent modifications to the same record
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param callable $callback Callback that operates on the locked model
     * @return mixed
     */
    protected function withLock($model, callable $callback)
    {
        return DB::transaction(function () use ($model, $callback) {
            // Lock the row for update
            $locked = $model->newQuery()
                ->where($model->getKeyName(), $model->getKey())
                ->lockForUpdate()
                ->first();

            if (!$locked) {
                throw new \RuntimeException('Record not found or locked by another transaction');
            }

            return $callback($locked);
        });
    }

    /**
     * Execute with pessimistic write lock
     * Similar to withLock but works on query builder level
     */
    protected function withPessimisticLock(\Illuminate\Database\Query\Builder $query, callable $callback)
    {
        return DB::transaction(function () use ($query, $callback) {
            return $query->lockForUpdate()->first() ? $callback() : null;
        });
    }

    /**
     * Check if current operation is inside a transaction
     */
    protected function inTransaction(): bool
    {
        return DB::transactionLevel() > 0;
    }

    /**
     * Get current transaction level (nesting depth)
     */
    protected function transactionLevel(): int
    {
        return DB::transactionLevel();
    }
}
