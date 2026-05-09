<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * SimulationService — Task 50
 * Hitung proyeksi dampak skenario bisnis "What If".
 */
class SimulationService
{
    public function __construct(protected GeminiService $gemini) {}

    /**
     * Jalankan simulasi berdasarkan tipe skenario.
     * Return array results + ai_narrative.
     */
    public function run(int $tenantId, string $scenarioType, array $params): array
    {
        $results = match ($scenarioType) {
            'price_increase' => $this->simulatePriceIncrease($tenantId, $params),
            'new_branch' => $this->simulateNewBranch($tenantId, $params),
            'stock_out' => $this->simulateStockOut($tenantId, $params),
            'cost_reduction' => $this->simulateCostReduction($tenantId, $params),
            'demand_change' => $this->simulateDemandChange($tenantId, $params),
            default => throw new \InvalidArgumentException("Tipe skenario tidak dikenal: {$scenarioType}"),
        };

        $narrative = $this->generateNarrative($scenarioType, $params, $results);

        return ['results' => $results, 'ai_narrative' => $narrative];
    }

    // ─── Scenario: Kenaikan Harga ─────────────────────────────────

    private function simulatePriceIncrease(int $tenantId, array $params): array
    {
        $pct = (float) ($params['price_change_pct'] ?? 10);
        $productId = $params['product_id'] ?? null; // null = semua produk
        $periodDays = (int) ($params['period_days'] ?? 30);

        // Ambil data penjualan historis
        $query = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.status', '!=', 'cancelled')
            ->whereBetween('sales_orders.date', [now()->subDays($periodDays)->toDateString(), now()->toDateString()])
            ->selectRaw('products.id, products.name, SUM(sales_order_items.quantity) as total_qty, SUM(sales_order_items.total) as total_revenue, AVG(sales_order_items.price) as avg_price');

        if ($productId) {
            $query->where('sales_order_items.product_id', $productId);
        }

        $sales = $query->groupBy('products.id', 'products.name')->get();

        if ($sales->isEmpty()) {
            return ['error' => 'Tidak ada data penjualan untuk periode yang dipilih.'];
        }

        $currentRevenue = $sales->sum('total_revenue');
        $projectedRevenue = $currentRevenue * (1 + $pct / 100);

        // Estimasi elastisitas sederhana: kenaikan harga 10% → penurunan demand ~5%
        $elasticity = -0.5;
        $demandChange = $pct * $elasticity;
        $adjustedRevenue = $projectedRevenue * (1 + $demandChange / 100);

        $fmt = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');

        return [
            'scenario' => "Kenaikan harga {$pct}%",
            'period_days' => $periodDays,
            'current_revenue' => $currentRevenue,
            'projected_revenue_no_elasticity' => $projectedRevenue,
            'projected_revenue_with_elasticity' => $adjustedRevenue,
            'revenue_change' => $adjustedRevenue - $currentRevenue,
            'revenue_change_pct' => round(($adjustedRevenue - $currentRevenue) / max($currentRevenue, 1) * 100, 1),
            'demand_change_pct' => $demandChange,
            'products_affected' => $sales->count(),
            'top_products' => $sales->take(5)->map(fn ($s) => [
                'name' => $s->name,
                'current_revenue' => $s->total_revenue,
                'projected_revenue' => $s->total_revenue * (1 + $pct / 100) * (1 + $demandChange / 100),
            ])->values()->toArray(),
            'formatted' => [
                'current' => $fmt($currentRevenue),
                'projected' => $fmt($adjustedRevenue),
                'change' => $fmt(abs($adjustedRevenue - $currentRevenue)),
            ],
        ];
    }

    // ─── Scenario: Buka Cabang Baru ───────────────────────────────

    private function simulateNewBranch(int $tenantId, array $params): array
    {
        $fixedCostMonthly = (float) ($params['fixed_cost_monthly'] ?? 10_000_000);
        $revenueProjection = (float) ($params['revenue_projection'] ?? 0);
        $months = (int) ($params['months'] ?? 12);

        // Jika tidak ada proyeksi manual, estimasi dari rata-rata cabang existing
        if ($revenueProjection <= 0) {
            $avgMonthlyRevenue = SalesOrder::where('tenant_id', $tenantId)
                ->whereNotIn('status', ['cancelled'])
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->sum('total');
            $revenueProjection = $avgMonthlyRevenue * 0.6; // asumsi cabang baru 60% dari existing
        }

        $totalFixedCost = $fixedCostMonthly * $months;
        $totalRevenue = $revenueProjection * $months;
        $grossProfit = $totalRevenue * 0.35; // asumsi margin 35%
        $netProfit = $grossProfit - $totalFixedCost;
        $breakEvenMonths = $fixedCostMonthly > 0
            ? ceil($fixedCostMonthly / max($revenueProjection * 0.35, 1))
            : 0;

        $fmt = fn ($n) => 'Rp '.number_format(abs($n), 0, ',', '.');

        return [
            'scenario' => "Buka cabang baru ({$months} bulan)",
            'months' => $months,
            'fixed_cost_monthly' => $fixedCostMonthly,
            'revenue_projection' => $revenueProjection,
            'total_fixed_cost' => $totalFixedCost,
            'total_revenue' => $totalRevenue,
            'gross_profit' => $grossProfit,
            'net_profit' => $netProfit,
            'break_even_months' => $breakEvenMonths,
            'is_profitable' => $netProfit > 0,
            'formatted' => [
                'fixed_cost' => $fmt($fixedCostMonthly).'/bulan',
                'revenue' => $fmt($revenueProjection).'/bulan',
                'net_profit' => ($netProfit >= 0 ? '+' : '-').$fmt($netProfit)." ({$months} bulan)",
                'break_even' => "{$breakEvenMonths} bulan",
            ],
        ];
    }

    // ─── Scenario: Stok Habis ─────────────────────────────────────

    private function simulateStockOut(int $tenantId, array $params): array
    {
        $productIds = $params['product_ids'] ?? [];
        $days = (int) ($params['days'] ?? 30);

        // Jika tidak ada produk spesifik, ambil top 5 produk terlaris
        if (empty($productIds)) {
            $productIds = DB::table('sales_order_items')
                ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
                ->where('sales_orders.tenant_id', $tenantId)
                ->where('sales_orders.status', '!=', 'cancelled')
                ->whereBetween('sales_orders.date', [now()->subDays(30)->toDateString(), now()->toDateString()])
                ->selectRaw('sales_order_items.product_id, SUM(sales_order_items.total) as total_revenue')
                ->groupBy('sales_order_items.product_id')
                ->orderByDesc('total_revenue')
                ->limit(5)
                ->pluck('product_id')
                ->toArray();
        }

        if (empty($productIds)) {
            return ['error' => 'Tidak ada data produk untuk disimulasikan.'];
        }

        $lostRevenue = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.status', '!=', 'cancelled')
            ->whereIn('sales_order_items.product_id', $productIds)
            ->whereBetween('sales_orders.date', [now()->subDays($days)->toDateString(), now()->toDateString()])
            ->selectRaw('products.name, SUM(sales_order_items.total) as revenue, SUM(sales_order_items.quantity) as qty')
            ->groupBy('products.id', 'products.name')
            ->get();

        $totalLost = $lostRevenue->sum('revenue');
        $fmt = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');

        return [
            'scenario' => "Stok habis {$days} hari ke depan",
            'days' => $days,
            'products_count' => count($productIds),
            'total_lost_revenue' => $totalLost,
            'daily_lost' => $totalLost / max($days, 1),
            'products' => $lostRevenue->map(fn ($p) => [
                'name' => $p->name,
                'revenue' => $p->revenue,
                'qty' => $p->qty,
            ])->values()->toArray(),
            'formatted' => [
                'total_lost' => $fmt($totalLost),
                'daily_lost' => $fmt($totalLost / max($days, 1)).'/hari',
            ],
        ];
    }

    // ─── Scenario: Efisiensi Biaya ────────────────────────────────

    private function simulateCostReduction(int $tenantId, array $params): array
    {
        $pct = (float) ($params['cost_reduction_pct'] ?? 10);
        $periodDays = (int) ($params['period_days'] ?? 30);

        $totalExpense = Transaction::where('tenant_id', $tenantId)
            ->where('type', 'expense')
            ->whereBetween('date', [now()->subDays($periodDays)->toDateString(), now()->toDateString()])
            ->sum('amount');

        $totalRevenue = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('date', [now()->subDays($periodDays)->toDateString(), now()->toDateString()])
            ->sum('total');

        $currentProfit = $totalRevenue - $totalExpense;
        $savedCost = $totalExpense * ($pct / 100);
        $newProfit = $currentProfit + $savedCost;
        $marginBefore = $totalRevenue > 0 ? round($currentProfit / $totalRevenue * 100, 1) : 0;
        $marginAfter = $totalRevenue > 0 ? round($newProfit / $totalRevenue * 100, 1) : 0;

        $fmt = fn ($n) => 'Rp '.number_format(abs($n), 0, ',', '.');

        return [
            'scenario' => "Efisiensi biaya {$pct}%",
            'period_days' => $periodDays,
            'total_expense' => $totalExpense,
            'total_revenue' => $totalRevenue,
            'current_profit' => $currentProfit,
            'saved_cost' => $savedCost,
            'new_profit' => $newProfit,
            'margin_before' => $marginBefore,
            'margin_after' => $marginAfter,
            'profit_increase' => $newProfit - $currentProfit,
            'formatted' => [
                'expense' => $fmt($totalExpense),
                'saved' => $fmt($savedCost),
                'profit_before' => ($currentProfit >= 0 ? '' : '-').$fmt($currentProfit),
                'profit_after' => ($newProfit >= 0 ? '' : '-').$fmt($newProfit),
            ],
        ];
    }

    // ─── Scenario: Perubahan Demand ───────────────────────────────

    private function simulateDemandChange(int $tenantId, array $params): array
    {
        $pct = (float) ($params['demand_change_pct'] ?? 20); // positif = naik, negatif = turun
        $periodDays = (int) ($params['period_days'] ?? 30);

        $currentRevenue = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('date', [now()->subDays($periodDays)->toDateString(), now()->toDateString()])
            ->sum('total');

        $currentOrders = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('date', [now()->subDays($periodDays)->toDateString(), now()->toDateString()])
            ->count();

        $projectedRevenue = $currentRevenue * (1 + $pct / 100);
        $projectedOrders = (int) round($currentOrders * (1 + $pct / 100));

        // Estimasi kebutuhan stok tambahan
        $additionalStockNeeded = $pct > 0
            ? "Perlu tambah stok ~{$pct}% dari level saat ini"
            : 'Stok saat ini mungkin berlebih ~'.abs($pct).'%';

        $fmt = fn ($n) => 'Rp '.number_format(abs($n), 0, ',', '.');

        return [
            'scenario' => ($pct >= 0 ? 'Kenaikan' : 'Penurunan')." demand {$pct}%",
            'period_days' => $periodDays,
            'demand_change_pct' => $pct,
            'current_revenue' => $currentRevenue,
            'projected_revenue' => $projectedRevenue,
            'revenue_change' => $projectedRevenue - $currentRevenue,
            'current_orders' => $currentOrders,
            'projected_orders' => $projectedOrders,
            'stock_note' => $additionalStockNeeded,
            'formatted' => [
                'current' => $fmt($currentRevenue),
                'projected' => $fmt($projectedRevenue),
                'change' => ($pct >= 0 ? '+' : '-').$fmt(abs($projectedRevenue - $currentRevenue)),
            ],
        ];
    }

    // ─── AI Narrative ─────────────────────────────────────────────

    private function generateNarrative(string $scenarioType, array $params, array $results): string
    {
        if (isset($results['error'])) {
            return $results['error'];
        }

        $scenarioLabels = [
            'price_increase' => 'kenaikan harga',
            'new_branch' => 'pembukaan cabang baru',
            'stock_out' => 'kehabisan stok',
            'cost_reduction' => 'efisiensi biaya',
            'demand_change' => 'perubahan permintaan',
        ];

        $label = $scenarioLabels[$scenarioType] ?? $scenarioType;
        $summary = json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $prompt = 'Kamu adalah analis bisnis ERP. Berikan narasi singkat (3-4 kalimat) dalam Bahasa Indonesia '
            ."tentang hasil simulasi skenario {$label} berikut. "
            ."Fokus pada dampak finansial, risiko, dan rekomendasi tindakan.\n\nData:\n{$summary}";

        try {
            $response = $this->gemini->chat($prompt, []);

            return $response['text'] ?? $this->buildFallbackNarrative($scenarioType, $results);
        } catch (\Throwable) {
            return $this->buildFallbackNarrative($scenarioType, $results);
        }
    }

    private function buildFallbackNarrative(string $type, array $results): string
    {
        return match ($type) {
            'price_increase' => sprintf(
                'Simulasi kenaikan harga %.1f%% memproyeksikan perubahan pendapatan sebesar %s. '
                .'Pertimbangkan elastisitas harga sebelum menerapkan perubahan.',
                $results['revenue_change_pct'] ?? 0,
                $results['formatted']['change'] ?? '-'
            ),
            'new_branch' => sprintf(
                'Pembukaan cabang baru diproyeksikan %s dalam %d bulan dengan break-even di bulan ke-%d.',
                ($results['is_profitable'] ?? false) ? 'menguntungkan' : 'merugi',
                $results['months'] ?? 12,
                $results['break_even_months'] ?? 0
            ),
            default => 'Simulasi telah dihitung. Tinjau detail hasil untuk pengambilan keputusan.',
        };
    }
}
