<?php

namespace App\Jobs;

use App\Services\RedisHealthService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RedisHealthAlertNotification;
use App\Models\User;

/**
 * Redis Health Monitoring Job
 *
 * Performs periodic Redis health checks and sends alerts when issues are detected.
 * This job runs on a schedule to proactively monitor Redis connectivity and authentication.
 */
class RedisHealthMonitoringJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        // Use database queue to avoid Redis dependency for this monitoring job
        $this->onQueue('database');
    }

    /**
     * Execute the job.
     */
    public function handle(RedisHealthService $redisHealth): void
    {
        Log::info('Starting Redis health monitoring check');

        try {
            // Perform comprehensive health check on all connections
            $healthResults = $redisHealth->checkAllConnections();

            // Log the overall health status
            Log::info('Redis health check completed', [
                'overall_healthy' => $healthResults['overall_healthy'],
                'timestamp' => $healthResults['timestamp'],
                'connections_checked' => count($healthResults['connections']),
            ]);

            // Check if any connections are unhealthy
            $unhealthyConnections = $this->getUnhealthyConnections($healthResults['connections']);

            if (!empty($unhealthyConnections)) {
                $this->handleUnhealthyConnections($unhealthyConnections, $healthResults);
            }

            // Check for authentication-specific issues
            $authIssues = $this->getAuthenticationIssues($healthResults['connections']);

            if (!empty($authIssues)) {
                $this->handleAuthenticationIssues($authIssues, $healthResults);
            }

            // Store health metrics for trending analysis
            $this->storeHealthMetrics($healthResults);
        } catch (\Exception $e) {
            Log::error('Redis health monitoring job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Send critical alert about monitoring failure
            $this->sendCriticalAlert('Redis health monitoring system failure', [
                'error' => $e->getMessage(),
                'job_failed' => true,
            ]);
        }
    }

    /**
     * Get unhealthy connections from health results
     *
     * @param array $connections
     * @return array
     */
    private function getUnhealthyConnections(array $connections): array
    {
        return array_filter($connections, function ($connection) {
            return !$connection['healthy'];
        });
    }

    /**
     * Get connections with authentication issues
     *
     * @param array $connections
     * @return array
     */
    private function getAuthenticationIssues(array $connections): array
    {
        return array_filter($connections, function ($connection) {
            return $connection['status'] === 'auth_failed';
        });
    }

    /**
     * Handle unhealthy Redis connections
     *
     * @param array $unhealthyConnections
     * @param array $fullResults
     * @return void
     */
    private function handleUnhealthyConnections(array $unhealthyConnections, array $fullResults): void
    {
        $connectionNames = array_keys($unhealthyConnections);

        Log::warning('Unhealthy Redis connections detected', [
            'unhealthy_connections' => $connectionNames,
            'total_connections' => count($fullResults['connections']),
            'recommendations' => $fullResults['recommendations'],
        ]);

        // Send alert for unhealthy connections
        $this->sendHealthAlert('Redis connections unhealthy', [
            'unhealthy_connections' => $unhealthyConnections,
            'recommendations' => $fullResults['recommendations'],
            'severity' => 'warning',
        ]);
    }

    /**
     * Handle Redis authentication issues
     *
     * @param array $authIssues
     * @param array $fullResults
     * @return void
     */
    private function handleAuthenticationIssues(array $authIssues, array $fullResults): void
    {
        $connectionNames = array_keys($authIssues);

        Log::critical('Redis authentication failures detected', [
            'auth_failed_connections' => $connectionNames,
            'environment' => app()->environment(),
            'action_required' => 'Check REDIS_PASSWORD configuration',
        ]);

        // Send critical alert for authentication failures
        $this->sendCriticalAlert('Redis authentication failures detected', [
            'auth_failed_connections' => $authIssues,
            'environment' => app()->environment(),
            'action_required' => 'Verify REDIS_PASSWORD configuration and Redis server settings',
            'severity' => 'critical',
        ]);
    }

    /**
     * Send health alert notification
     *
     * @param string $title
     * @param array $details
     * @return void
     */
    private function sendHealthAlert(string $title, array $details): void
    {
        try {
            // Get super admin users for critical infrastructure alerts
            $superAdmins = User::where('is_super_admin', true)
                ->where('is_active', true)
                ->get();

            if ($superAdmins->isNotEmpty()) {
                Notification::send($superAdmins, new RedisHealthAlertNotification($title, $details));

                Log::info('Redis health alert sent', [
                    'title' => $title,
                    'recipients' => $superAdmins->count(),
                    'severity' => $details['severity'] ?? 'info',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send Redis health alert', [
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send critical alert notification
     *
     * @param string $title
     * @param array $details
     * @return void
     */
    private function sendCriticalAlert(string $title, array $details): void
    {
        $details['severity'] = 'critical';
        $this->sendHealthAlert($title, $details);

        // Also log as critical for immediate attention
        Log::critical('Redis critical alert', [
            'title' => $title,
            'details' => $details,
        ]);
    }

    /**
     * Store health metrics for trending analysis
     *
     * @param array $healthResults
     * @return void
     */
    private function storeHealthMetrics(array $healthResults): void
    {
        try {
            // Store basic health metrics in cache for dashboard display
            $metrics = [
                'overall_healthy' => $healthResults['overall_healthy'],
                'timestamp' => $healthResults['timestamp'],
                'connection_count' => count($healthResults['connections']),
                'healthy_connections' => count(array_filter($healthResults['connections'], fn($c) => $c['healthy'])),
                'response_times' => array_map(fn($c) => $c['response_time'], $healthResults['connections']),
                'average_response_time' => $this->calculateAverageResponseTime($healthResults['connections']),
            ];

            // Store in cache with 1-hour TTL for dashboard access
            cache()->put('redis_health_metrics', $metrics, 3600);

            Log::debug('Redis health metrics stored', $metrics);
        } catch (\Exception $e) {
            Log::warning('Failed to store Redis health metrics', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calculate average response time from connections
     *
     * @param array $connections
     * @return float
     */
    private function calculateAverageResponseTime(array $connections): float
    {
        $responseTimes = array_map(fn($c) => $c['response_time'], $connections);
        $validTimes = array_filter($responseTimes, fn($time) => is_numeric($time) && $time > 0);

        return !empty($validTimes) ? round(array_sum($validTimes) / count($validTimes), 2) : 0.0;
    }

    /**
     * Handle job failure
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('Redis health monitoring job failed permanently', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'attempts' => $this->attempts(),
        ]);

        // Send critical alert about job failure
        $this->sendCriticalAlert('Redis health monitoring job failed', [
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'job_permanently_failed' => true,
        ]);
    }
}
