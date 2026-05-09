<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AiProviderSwitchLog;
use App\Models\AiUsageCostLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Controller untuk monitoring AI routing dan usage statistics.
 *
 * Fitur:
 * - Dashboard monitoring dengan charts (distribusi request, fallback events, response time)
 * - Statistik routing dalam format JSON untuk integrasi eksternal
 * - Cache agregasi dengan TTL 5 menit
 *
 * Requirements: 10.1, 10.2, 10.5, 10.8
 */
class AiRoutingMonitorController extends Controller
{
    /**
     * Halaman monitoring dashboard dengan charts dan statistik.
     *
     * Requirements: 10.1, 10.8
     */
    public function index(): View
    {
        // Distribusi request per use case (24 jam terakhir)
        $useCaseDistribution = $this->getUseCaseDistribution();

        // Distribusi request per provider (24 jam terakhir)
        $providerDistribution = $this->getProviderDistribution();

        // Tren fallback event (24 jam terakhir, per jam)
        $fallbackTrend = $this->getFallbackTrend();

        // Rata-rata response time per use case (24 jam terakhir)
        $responseTimeByUseCase = $this->getResponseTimeByUseCase();

        // Rata-rata response time per provider (24 jam terakhir)
        $responseTimeByProvider = $this->getResponseTimeByProvider();

        // Jumlah fallback event per use case (24 jam terakhir)
        $fallbackCountByUseCase = $this->getFallbackCountByUseCase();

        return view('super-admin.ai-routing.monitor', compact(
            'useCaseDistribution',
            'providerDistribution',
            'fallbackTrend',
            'responseTimeByUseCase',
            'responseTimeByProvider',
            'fallbackCountByUseCase'
        ));
    }

    /**
     * Endpoint JSON untuk statistik routing (untuk integrasi eksternal).
     *
     * Requirements: 10.5
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'use_case_distribution' => $this->getUseCaseDistribution(),
                'provider_distribution' => $this->getProviderDistribution(),
                'fallback_trend' => $this->getFallbackTrend(),
                'response_time_by_use_case' => $this->getResponseTimeByUseCase(),
                'response_time_by_provider' => $this->getResponseTimeByProvider(),
                'fallback_count_by_use_case' => $this->getFallbackCountByUseCase(),
                'generated_at' => now()->toIso8601String(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Throwable $e) {
            Log::error('AiRoutingMonitorController: gagal mengambil statistik routing', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik routing: '.$e->getMessage(),
            ], 500);
        }
    }

    // ─── Private Helpers ──────────────────────────────────────────

    /**
     * Distribusi request per use case (24 jam terakhir).
     * Cache: 5 menit TTL.
     *
     * Requirements: 10.1, 10.2
     */
    private function getUseCaseDistribution(): array
    {
        return Cache::remember('ai_routing_monitor:use_case_distribution', 300, function () {
            try {
                $from = now()->subHours(24);
                $to = now();

                $data = AiUsageCostLog::withoutGlobalScope('tenant')
                    ->whereBetween('created_at', [$from, $to])
                    ->selectRaw('use_case, COUNT(*) as count')
                    ->groupBy('use_case')
                    ->orderByDesc('count')
                    ->get();

                return $data->map(fn ($item) => [
                    'use_case' => $item->use_case,
                    'count' => $item->count,
                ])->toArray();
            } catch (\Throwable $e) {
                Log::debug('AiRoutingMonitorController: could not load use case distribution.', ['error' => $e->getMessage()]);

                return [];
            }
        });
    }

    /**
     * Distribusi request per provider (24 jam terakhir).
     * Cache: 5 menit TTL.
     *
     * Requirements: 10.1, 10.2
     */
    private function getProviderDistribution(): array
    {
        return Cache::remember('ai_routing_monitor:provider_distribution', 300, function () {
            try {
                $from = now()->subHours(24);
                $to = now();

                $data = AiUsageCostLog::withoutGlobalScope('tenant')
                    ->whereBetween('created_at', [$from, $to])
                    ->selectRaw('provider, COUNT(*) as count')
                    ->groupBy('provider')
                    ->orderByDesc('count')
                    ->get();

                return $data->map(fn ($item) => [
                    'provider' => $item->provider,
                    'count' => $item->count,
                ])->toArray();
            } catch (\Throwable $e) {
                Log::debug('AiRoutingMonitorController: could not load provider distribution.', ['error' => $e->getMessage()]);

                return [];
            }
        });
    }

    /**
     * Tren fallback event (24 jam terakhir, per jam).
     * Cache: 5 menit TTL.
     *
     * Requirements: 10.1, 10.2
     */
    private function getFallbackTrend(): array
    {
        return Cache::remember('ai_routing_monitor:fallback_trend', 300, function () {
            try {
                $from = now()->subHours(24);
                $to = now();

                $data = AiProviderSwitchLog::withoutGlobalScope('tenant')
                    ->whereBetween('created_at', [$from, $to])
                    ->where('reason', 'use_case_fallback')
                    ->selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as hour, COUNT(*) as count')
                    ->groupBy('hour')
                    ->orderBy('hour')
                    ->get();

                return $data->map(fn ($item) => [
                    'hour' => $item->hour,
                    'count' => $item->count,
                ])->toArray();
            } catch (\Throwable $e) {
                Log::debug('AiRoutingMonitorController: could not load fallback trend.', ['error' => $e->getMessage()]);

                return [];
            }
        });
    }

    /**
     * Rata-rata response time per use case (24 jam terakhir).
     * Cache: 5 menit TTL.
     *
     * Requirements: 10.8
     */
    private function getResponseTimeByUseCase(): array
    {
        return Cache::remember('ai_routing_monitor:response_time_by_use_case', 300, function () {
            try {
                $from = now()->subHours(24);
                $to = now();

                $data = AiUsageCostLog::withoutGlobalScope('tenant')
                    ->whereBetween('created_at', [$from, $to])
                    ->whereNotNull('response_time_ms')
                    ->selectRaw('use_case, AVG(response_time_ms) as avg_response_time')
                    ->groupBy('use_case')
                    ->orderBy('use_case')
                    ->get();

                return $data->map(fn ($item) => [
                    'use_case' => $item->use_case,
                    'avg_response_time' => round($item->avg_response_time, 2),
                ])->toArray();
            } catch (\Throwable $e) {
                Log::debug('AiRoutingMonitorController: could not load response time by use case.', ['error' => $e->getMessage()]);

                return [];
            }
        });
    }

    /**
     * Rata-rata response time per provider (24 jam terakhir).
     * Cache: 5 menit TTL.
     *
     * Requirements: 10.8
     */
    private function getResponseTimeByProvider(): array
    {
        return Cache::remember('ai_routing_monitor:response_time_by_provider', 300, function () {
            try {
                $from = now()->subHours(24);
                $to = now();

                $data = AiUsageCostLog::withoutGlobalScope('tenant')
                    ->whereBetween('created_at', [$from, $to])
                    ->whereNotNull('response_time_ms')
                    ->selectRaw('provider, AVG(response_time_ms) as avg_response_time')
                    ->groupBy('provider')
                    ->orderBy('provider')
                    ->get();

                return $data->map(fn ($item) => [
                    'provider' => $item->provider,
                    'avg_response_time' => round($item->avg_response_time, 2),
                ])->toArray();
            } catch (\Throwable $e) {
                Log::debug('AiRoutingMonitorController: could not load response time by provider.', ['error' => $e->getMessage()]);

                return [];
            }
        });
    }

    /**
     * Jumlah fallback event per use case (24 jam terakhir).
     * Cache: 5 menit TTL.
     *
     * Requirements: 10.1
     */
    private function getFallbackCountByUseCase(): array
    {
        return Cache::remember('ai_routing_monitor:fallback_count_by_use_case', 300, function () {
            try {
                $from = now()->subHours(24);
                $to = now();

                $data = AiProviderSwitchLog::withoutGlobalScope('tenant')
                    ->whereBetween('created_at', [$from, $to])
                    ->where('reason', 'use_case_fallback')
                    ->whereNotNull('use_case')
                    ->selectRaw('use_case, COUNT(*) as count')
                    ->groupBy('use_case')
                    ->orderByDesc('count')
                    ->get();

                return $data->map(fn ($item) => [
                    'use_case' => $item->use_case,
                    'count' => $item->count,
                ])->toArray();
            } catch (\Throwable $e) {
                Log::debug('AiRoutingMonitorController: could not load fallback count by use case.', ['error' => $e->getMessage()]);

                return [];
            }
        });
    }
}
