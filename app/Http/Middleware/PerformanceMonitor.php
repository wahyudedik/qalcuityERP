<?php

namespace App\Http\Middleware;

use App\Services\QueryMonitoringService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitor
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.debug') && $request->hasHeader('X-Performance-Monitor')) {
            QueryMonitoringService::start();
        }

        $response = $next($request);

        if (config('app.debug') && $request->hasHeader('X-Performance-Monitor')) {
            $stats = QueryMonitoringService::getStats();

            // Add performance headers
            $response->headers->set('X-Query-Count', $stats['total_queries']);
            $response->headers->set('X-Query-Time', $stats['total_time_ms'].'ms');
            $response->headers->set('X-Slow-Queries', $stats['slow_queries']);
            $response->headers->set('X-NPlus-One-Suspects', $stats['n_plus_one_suspects']);

            // Log slow queries
            QueryMonitoringService::logSlowQueries(100);
        }

        return $response;
    }
}
