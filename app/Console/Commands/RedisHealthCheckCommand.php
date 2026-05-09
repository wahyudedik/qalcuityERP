<?php

namespace App\Console\Commands;

use App\Services\RedisHealthService;
use Illuminate\Console\Command;

/**
 * Redis Health Check Command
 *
 * Provides CLI interface for Redis health monitoring and diagnostics.
 */
class RedisHealthCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:health-check
                            {--connection= : Specific Redis connection to check (default, cache, session, queue)}
                            {--all : Check all Redis connections}
                            {--json : Output results in JSON format}
                            {--refresh : Force refresh of cached health status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Redis connection health and authentication status';

    /**
     * Redis health service instance
     */
    private RedisHealthService $redisHealth;

    /**
     * Create a new command instance.
     */
    public function __construct(RedisHealthService $redisHealth)
    {
        parent::__construct();
        $this->redisHealth = $redisHealth;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Redis Health Check');
        $this->info('==================');

        // Determine what to check
        if ($this->option('all')) {
            return $this->checkAllConnections();
        }

        $connection = $this->option('connection') ?? 'default';

        return $this->checkSingleConnection($connection);
    }

    /**
     * Check all Redis connections
     */
    private function checkAllConnections(): int
    {
        $forceRefresh = $this->option('refresh');
        $results = $this->redisHealth->checkAllConnections();

        if ($this->option('json')) {
            $this->line(json_encode($results, JSON_PRETTY_PRINT));

            return 0;
        }

        $this->displayOverallStatus($results);
        $this->displayConnectionResults($results['connections']);
        $this->displayRecommendations($results['recommendations']);

        return $results['overall_healthy'] ? 0 : 1;
    }

    /**
     * Check a single Redis connection
     */
    private function checkSingleConnection(string $connection): int
    {
        $forceRefresh = $this->option('refresh');
        $result = $this->redisHealth->getCachedHealthStatus($connection, $forceRefresh);

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT));

            return 0;
        }

        $this->displaySingleResult($result);

        return $result['healthy'] ? 0 : 1;
    }

    /**
     * Display overall health status
     */
    private function displayOverallStatus(array $results): void
    {
        $status = $results['overall_healthy'] ? 'HEALTHY' : 'UNHEALTHY';
        $color = $results['overall_healthy'] ? 'green' : 'red';

        $this->line('');
        $this->line("<fg={$color}>Overall Status: {$status}</>");
        $this->line("Timestamp: {$results['timestamp']}");
        $this->line('');
    }

    /**
     * Display connection results in a table
     */
    private function displayConnectionResults(array $connections): void
    {
        $headers = ['Connection', 'Status', 'Health', 'Response Time', 'Message'];
        $rows = [];

        foreach ($connections as $name => $result) {
            $rows[] = [
                $name,
                $result['status'],
                $result['healthy'] ? '✓ Healthy' : '✗ Unhealthy',
                $result['response_time'].'ms',
                $this->truncateMessage($result['message'], 50),
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Display a single connection result
     */
    private function displaySingleResult(array $result): void
    {
        $status = $result['healthy'] ? 'HEALTHY' : 'UNHEALTHY';
        $color = $result['healthy'] ? 'green' : 'red';

        $this->line('');
        $this->line("Connection: {$result['connection']}");
        $this->line("<fg={$color}>Status: {$status}</>");
        $this->line("Response Time: {$result['response_time']}ms");
        $this->line("Message: {$result['message']}");

        if (isset($result['details']) && ! empty($result['details'])) {
            $this->line('');
            $this->line('Details:');
            foreach ($result['details'] as $key => $value) {
                $this->line("  {$key}: ".(is_bool($value) ? ($value ? 'true' : 'false') : $value));
            }
        }
    }

    /**
     * Display recommendations
     */
    private function displayRecommendations(array $recommendations): void
    {
        if (empty($recommendations)) {
            return;
        }

        $this->line('');
        $this->line('<fg=yellow>Recommendations:</fg=yellow>');
        foreach ($recommendations as $recommendation) {
            $this->line("• {$recommendation}");
        }
    }

    /**
     * Truncate message for table display
     */
    private function truncateMessage(string $message, int $length): string
    {
        return strlen($message) > $length ? substr($message, 0, $length).'...' : $message;
    }
}
