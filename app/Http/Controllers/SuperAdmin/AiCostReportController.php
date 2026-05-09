<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AiUsageCostLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Controller untuk laporan biaya AI per tenant, use case, dan provider.
 *
 * Fitur:
 * - Widget "Biaya AI Bulan Ini" dengan total per provider dan per use case
 * - Endpoint laporan biaya dengan filter rentang tanggal, tenant, use case
 * - Tabel "Top 10 Use Case by Cost" dalam 30 hari terakhir
 *
 * Requirements: 6.7, 6.9, 10.4
 */
class AiCostReportController extends Controller
{
    /**
     * Halaman dashboard biaya AI bulan ini.
     *
     * Requirements: 6.7
     */
    public function index(): View
    {
        // Total biaya bulan ini per provider
        $costByProvider = $this->getCostByProvider();

        // Total biaya bulan ini per use case
        $costByUseCase = $this->getCostByUseCase();

        // Top 10 use case by cost (30 hari terakhir)
        $topUseCases = $this->getTopUseCases();

        // Total biaya bulan ini (semua provider)
        $totalCostThisMonth = array_sum(array_column($costByProvider, 'total_cost'));

        // Total request bulan ini
        $totalRequestsThisMonth = array_sum(array_column($costByProvider, 'request_count'));

        return view('super-admin.ai-cost.index', compact(
            'costByProvider',
            'costByUseCase',
            'topUseCases',
            'totalCostThisMonth',
            'totalRequestsThisMonth'
        ));
    }

    /**
     * Endpoint laporan biaya AI dengan filter.
     *
     * Query parameters:
     * - from: tanggal mulai (Y-m-d)
     * - to: tanggal akhir (Y-m-d)
     * - tenant_id: filter tenant tertentu (opsional)
     * - use_case: filter use case tertentu (opsional)
     *
     * Requirements: 6.9
     */
    public function report(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'from' => 'nullable|date',
                'to' => 'nullable|date|after_or_equal:from',
                'tenant_id' => 'nullable|integer|exists:tenants,id',
                'use_case' => 'nullable|string|max:100',
            ]);

            $from = $validated['from'] ?? now()->startOfMonth()->toDateString();
            $to = $validated['to'] ?? now()->toDateString();
            $tenantId = $validated['tenant_id'] ?? null;
            $useCase = $validated['use_case'] ?? null;

            $query = AiUsageCostLog::withoutGlobalScope('tenant')
                ->whereBetween('created_at', [$from, $to]);

            if ($tenantId !== null) {
                $query->where('tenant_id', $tenantId);
            }

            if ($useCase !== null) {
                $query->where('use_case', $useCase);
            }

            // Agregasi per tenant, use case, provider
            $data = $query
                ->selectRaw('
                    tenant_id,
                    use_case,
                    provider,
                    COUNT(*) as request_count,
                    SUM(input_tokens) as total_input_tokens,
                    SUM(output_tokens) as total_output_tokens,
                    SUM(estimated_cost_idr) as total_cost
                ')
                ->groupBy('tenant_id', 'use_case', 'provider')
                ->orderByDesc('total_cost')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'from' => $from,
                    'to' => $to,
                    'report' => $data,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('AiCostReportController: gagal mengambil laporan biaya', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil laporan biaya: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Top 10 use case by cost (30 hari terakhir).
     *
     * Requirements: 10.4
     */
    public function topUseCases(): JsonResponse
    {
        try {
            $data = $this->getTopUseCases();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('AiCostReportController: gagal mengambil top use cases', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil top use cases: '.$e->getMessage(),
            ], 500);
        }
    }

    // ─── Private Helpers ──────────────────────────────────────────

    /**
     * Total biaya bulan ini per provider.
     * Cache: 5 menit TTL.
     *
     * Requirements: 6.7
     */
    private function getCostByProvider(): array
    {
        return Cache::remember('ai_cost_report:cost_by_provider', 300, function () {
            try {
                $from = now()->startOfMonth();
                $to = now();

                $data = AiUsageCostLog::withoutGlobalScope('tenant')
                    ->whereBetween('created_at', [$from, $to])
                    ->selectRaw('provider, COUNT(*) as request_count, SUM(estimated_cost_idr) as total_cost')
                    ->groupBy('provider')
                    ->orderByDesc('total_cost')
                    ->get();

                return $data->map(fn ($item) => [
                    'provider' => $item->provider,
                    'request_count' => $item->request_count,
                    'total_cost' => round($item->total_cost, 2),
                ])->toArray();
            } catch (\Throwable $e) {
                Log::debug('AiCostReportController: could not load cost by provider.', ['error' => $e->getMessage()]);

                return [];
            }
        });
    }

    /**
     * Total biaya bulan ini per use case.
     * Cache: 5 menit TTL.
     *
     * Requirements: 6.7
     */
    private function getCostByUseCase(): array
    {
        return Cache::remember('ai_cost_report:cost_by_use_case', 300, function () {
            try {
                $from = now()->startOfMonth();
                $to = now();

                $data = AiUsageCostLog::withoutGlobalScope('tenant')
                    ->whereBetween('created_at', [$from, $to])
                    ->selectRaw('use_case, COUNT(*) as request_count, SUM(estimated_cost_idr) as total_cost')
                    ->groupBy('use_case')
                    ->orderByDesc('total_cost')
                    ->get();

                return $data->map(fn ($item) => [
                    'use_case' => $item->use_case,
                    'request_count' => $item->request_count,
                    'total_cost' => round($item->total_cost, 2),
                ])->toArray();
            } catch (\Throwable $e) {
                Log::debug('AiCostReportController: could not load cost by use case.', ['error' => $e->getMessage()]);

                return [];
            }
        });
    }

    /**
     * Top 10 use case by cost (30 hari terakhir).
     * Cache: 5 menit TTL.
     *
     * Requirements: 10.4
     */
    private function getTopUseCases(): array
    {
        return Cache::remember('ai_cost_report:top_use_cases', 300, function () {
            try {
                $from = now()->subDays(30);
                $to = now();

                $data = AiUsageCostLog::withoutGlobalScope('tenant')
                    ->whereBetween('created_at', [$from, $to])
                    ->selectRaw('use_case, COUNT(*) as request_count, SUM(estimated_cost_idr) as total_cost')
                    ->groupBy('use_case')
                    ->orderByDesc('total_cost')
                    ->limit(10)
                    ->get();

                return $data->map(fn ($item) => [
                    'use_case' => $item->use_case,
                    'request_count' => $item->request_count,
                    'total_cost' => round($item->total_cost, 2),
                ])->toArray();
            } catch (\Throwable $e) {
                Log::debug('AiCostReportController: could not load top use cases.', ['error' => $e->getMessage()]);

                return [];
            }
        });
    }
}
