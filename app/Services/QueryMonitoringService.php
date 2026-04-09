<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryMonitoringService
{
    protected static $queries = [];
    protected static $startTime;

    /**
     * Start monitoring queries
     */
    public static function start()
    {
        self::$startTime = microtime(true);
        self::$queries = [];

        DB::listen(function ($query) {
            self::$queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
                'connection' => $query->connectionName,
            ];
        });
    }

    /**
     * Get query statistics
     * 
     * @return array
     */
    public static function getStats(): array
    {
        $totalTime = microtime(true) - self::$startTime;
        $totalQueries = count(self::$queries);
        $slowQueries = array_filter(self::$queries, fn($q) => $q['time'] > 100);

        // Detect potential N+1 queries (same query executed more than 5 times)
        $queryCounts = [];
        foreach (self::$queries as $query) {
            $normalizedSql = preg_replace('/\?.+?(?=\s|$)/', '?', $query['sql']);
            $key = md5($normalizedSql);
            $queryCounts[$key] = ($queryCounts[$key] ?? 0) + 1;
        }

        $nPlusQueries = array_filter($queryCounts, fn($count) => $count > 5);

        return [
            'total_queries' => $totalQueries,
            'total_time_ms' => round($totalTime * 1000, 2),
            'slow_queries' => count($slowQueries),
            'n_plus_one_suspects' => count($nPlusQueries),
            'queries' => array_slice(self::$queries, -50), // Last 50 queries
            'n_plus_one_details' => $nPlusQueries,
        ];
    }

    /**
     * Log slow queries
     */
    public static function logSlowQueries($threshold = 100)
    {
        $slowQueries = array_filter(self::$queries, fn($q) => $q['time'] > $threshold);

        if (!empty($slowQueries)) {
            Log::warning('Slow queries detected', [
                'count' => count($slowQueries),
                'threshold_ms' => $threshold,
                'queries' => array_map(fn($q) => [
                    'sql' => $q['sql'],
                    'time_ms' => $q['time'],
                ], $slowQueries),
            ]);
        }
    }

    /**
     * Detect N+1 query patterns
     */
    public static function detectNPlusOne()
    {
        $queryPatterns = [];

        foreach (self::$queries as $query) {
            // Normalize SQL by replacing values with ?
            $normalized = preg_replace('/\'[^\']+\'/', '?', $query['sql']);
            $normalized = preg_replace('/\b\d+\b/', '?', $normalized);

            $pattern = md5($normalized);
            if (!isset($queryPatterns[$pattern])) {
                $queryPatterns[$pattern] = [
                    'sql' => $query['sql'],
                    'count' => 0,
                    'total_time' => 0,
                ];
            }

            $queryPatterns[$pattern]['count']++;
            $queryPatterns[$pattern]['total_time'] += $query['time'];
        }

        // Find queries executed more than 10 times (potential N+1)
        return array_filter($queryPatterns, fn($p) => $p['count'] > 10);
    }
}
