<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\ChartOfAccount;
use App\Models\CropCycle;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\ErpNotification;
use App\Models\FarmPlot;
use App\Models\FarmPlotActivity;
use App\Models\HarvestLog;
use App\Models\Invoice;
use App\Models\Payable;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Project;
use App\Models\RabItem;
use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AI Financial Advisor — Proactive cross-module recommendations.
 *
 * Collects aggregated data from ALL modules, sends to Gemini for
 * strategic analysis, and produces actionable recommendations.
 */
class AiFinancialAdvisorService
{
    /**
     * Generate AI-powered recommendations for a tenant.
     */
    public function generateRecommendations(int $tenantId, string $period = 'weekly'): array
    {
        $tenant = Tenant::find($tenantId);
        if (!$tenant) return [];

        // Collect cross-module data snapshot
        $snapshot = $this->collectSnapshot($tenantId);

        // If not enough data, skip
        if ($snapshot['_has_data'] === false) return [];

        // Send to Gemini for analysis
        $recommendations = $this->analyzeWithAi($tenant, $snapshot, $period);

        // Save as notifications
        $this->saveRecommendations($tenantId, $recommendations);

        return $recommendations;
    }

    /**
     * Collect aggregated data snapshot from all modules.
     */
    private function collectSnapshot(int $tenantId): array
    {
        $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
        $now = now();
        $thisMonth = $now->format('Y-m');
        $lastMonth = $now->copy()->subMonth()->format('Y-m');

        $data = ['_has_data' => false];

        // ── Revenue & Sales ──
        $thisMonthRevenue = (float) SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereMonth('date', $now->month)->whereYear('date', $now->year)
            ->sum('total');
        $lastMonthRevenue = (float) SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereMonth('date', $now->copy()->subMonth()->month)
            ->whereYear('date', $now->copy()->subMonth()->year)
            ->sum('total');

        if ($thisMonthRevenue > 0 || $lastMonthRevenue > 0) {
            $data['_has_data'] = true;
            $data['revenue'] = [
                'this_month' => $thisMonthRevenue,
                'last_month' => $lastMonthRevenue,
                'change_pct' => $lastMonthRevenue > 0 ? round(($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue * 100, 1) : null,
            ];
        }

        // ── Top customers by revenue ──
        $topCustomers = DB::table('sales_orders')
            ->join('customers', 'sales_orders.customer_id', '=', 'customers.id')
            ->where('sales_orders.tenant_id', $tenantId)
            ->whereNotIn('sales_orders.status', ['cancelled'])
            ->whereMonth('sales_orders.date', $now->month)
            ->selectRaw('customers.name, SUM(sales_orders.total) as total, COUNT(*) as orders')
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total')
            ->limit(5)->get();
        if ($topCustomers->isNotEmpty()) $data['top_customers'] = $topCustomers->toArray();

        // ── Expenses ──
        $thisMonthExpense = (float) Transaction::where('tenant_id', $tenantId)
            ->where('type', 'expense')
            ->whereMonth('date', $now->month)->whereYear('date', $now->year)
            ->sum('amount');
        $lastMonthExpense = (float) Transaction::where('tenant_id', $tenantId)
            ->where('type', 'expense')
            ->whereMonth('date', $now->copy()->subMonth()->month)
            ->whereYear('date', $now->copy()->subMonth()->year)
            ->sum('amount');

        if ($thisMonthExpense > 0) {
            $data['_has_data'] = true;
            $data['expenses'] = [
                'this_month' => $thisMonthExpense,
                'last_month' => $lastMonthExpense,
                'top_categories' => Transaction::where('tenant_id', $tenantId)
                    ->where('type', 'expense')
                    ->whereMonth('date', $now->month)
                    ->with('category:id,name')
                    ->selectRaw('expense_category_id, SUM(amount) as total')
                    ->groupBy('expense_category_id')
                    ->orderByDesc('total')
                    ->limit(5)->get()
                    ->map(fn ($t) => ['category' => $t->category?->name ?? 'Lainnya', 'amount' => (float) $t->total])
                    ->toArray(),
            ];
        }

        // ── Receivables & Payables ──
        $overdueAR = (float) Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', today())
            ->sum('remaining_amount');
        $upcomingAR = (float) Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereBetween('due_date', [today(), today()->addDays(14)])
            ->sum('remaining_amount');
        $overdueAP = (float) Payable::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', today())
            ->sum('remaining_amount');
        $upcomingAP = (float) Payable::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereBetween('due_date', [today(), today()->addDays(14)])
            ->sum('remaining_amount');

        if ($overdueAR > 0 || $upcomingAR > 0 || $overdueAP > 0) {
            $data['_has_data'] = true;
            $data['cashflow'] = compact('overdueAR', 'upcomingAR', 'overdueAP', 'upcomingAP');
        }

        // ── Inventory ──
        $lowStock = ProductStock::with('product:id,name,unit,stock_min')
            ->whereHas('product', fn ($q) => $q->where('tenant_id', $tenantId)->where('is_active', true))
            ->get()
            ->filter(fn ($s) => $s->product && $s->quantity <= $s->product->stock_min && $s->product->stock_min > 0);
        if ($lowStock->isNotEmpty()) {
            $data['low_stock'] = $lowStock->take(5)->map(fn ($s) => [
                'product' => $s->product->name,
                'current' => (float) $s->quantity,
                'minimum' => $s->product->stock_min,
            ])->toArray();
        }

        // ── Projects (if any) ──
        $activeProjects = Project::where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'on_hold'])
            ->get();
        if ($activeProjects->isNotEmpty()) {
            $data['_has_data'] = true;
            $data['projects'] = $activeProjects->map(fn ($p) => [
                'name'        => $p->name,
                'progress'    => $p->progress,
                'budget'      => (float) $p->budget,
                'actual_cost' => (float) $p->actual_cost,
                'budget_pct'  => $p->budgetUsedPercent(),
                'overdue'     => $p->end_date && $p->end_date->isPast(),
            ])->toArray();
        }

        // ── Farm (if any) ──
        $farmPlots = FarmPlot::where('tenant_id', $tenantId)->where('is_active', true)->get();
        if ($farmPlots->isNotEmpty()) {
            $data['_has_data'] = true;
            $overduePlots = $farmPlots->filter(fn ($p) => $p->isHarvestOverdue());
            $data['farm'] = [
                'total_plots'   => $farmPlots->count(),
                'total_area'    => $farmPlots->sum('area_size') . ' ha',
                'overdue_harvest'=> $overduePlots->count(),
                'by_status'     => $farmPlots->groupBy('status')->map->count()->toArray(),
            ];

            // Farm cost analysis
            $totalFarmCost = (float) FarmPlotActivity::whereIn('farm_plot_id', $farmPlots->pluck('id'))->sum('cost');
            $totalHarvest = (float) HarvestLog::whereIn('farm_plot_id', $farmPlots->pluck('id'))->sum(DB::raw('total_qty - reject_qty'));
            if ($totalFarmCost > 0) {
                $data['farm']['total_cost'] = $totalFarmCost;
                $data['farm']['total_harvest_kg'] = $totalHarvest;
                $data['farm']['avg_hpp_per_kg'] = $totalHarvest > 0 ? round($totalFarmCost / $totalHarvest, 2) : null;
            }
        }

        // ── Employees ──
        $employeeCount = Employee::where('tenant_id', $tenantId)->where('status', 'active')->count();
        if ($employeeCount > 0) {
            $data['employees'] = ['active_count' => $employeeCount];
        }

        // ── Profit estimate ──
        if (isset($data['revenue']) && isset($data['expenses'])) {
            $data['profit_estimate'] = [
                'gross' => $data['revenue']['this_month'] - $data['expenses']['this_month'],
                'margin_pct' => $data['revenue']['this_month'] > 0
                    ? round(($data['revenue']['this_month'] - $data['expenses']['this_month']) / $data['revenue']['this_month'] * 100, 1)
                    : null,
            ];
        }

        return $data;
    }

    /**
     * Send snapshot to Gemini for strategic analysis.
     */
    private function analyzeWithAi(Tenant $tenant, array $snapshot, string $period): array
    {
        try {
            $gemini = app(GeminiService::class);

            $prompt = $this->buildPrompt($tenant, $snapshot, $period);

            $result = $gemini->generate($prompt);
            $text = $result['text'] ?? '';

            return $this->parseRecommendations($text);

        } catch (\Throwable $e) {
            Log::warning("AiFinancialAdvisor: Gemini call failed for tenant {$tenant->id}: {$e->getMessage()}");
            return [];
        }
    }

    private function buildPrompt(Tenant $tenant, array $snapshot, string $period): string
    {
        $json = json_encode(
            collect($snapshot)->except('_has_data')->filter()->toArray(),
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );

        $periodLabel = $period === 'weekly' ? 'mingguan' : 'harian';

        return <<<PROMPT
Kamu adalah AI Financial Advisor untuk bisnis "{$tenant->name}" (jenis: {$tenant->business_type}).

Berikut data snapshot bisnis {$periodLabel}:
```json
{$json}
```

Berdasarkan data di atas, berikan MAKSIMAL 5 rekomendasi strategis yang actionable.

ATURAN:
1. Setiap rekomendasi harus SPESIFIK dengan angka/nama dari data (bukan generik)
2. Prioritaskan: risiko keuangan > peluang efisiensi > peluang pertumbuhan
3. Jika ada data pertanian (farm), berikan rekomendasi spesifik per lahan
4. Jika ada data proyek, analisis budget vs realisasi
5. Jika ada cashflow gap (AP > AR), beri warning dengan angka spesifik
6. Bandingkan bulan ini vs bulan lalu jika data tersedia
7. Gunakan Bahasa Indonesia yang profesional

FORMAT OUTPUT (WAJIB ikuti format ini persis):
---REKOMENDASI---
[critical|warning|info] | Judul singkat | Penjelasan detail dengan angka spesifik. Sertakan saran tindakan konkret.
[critical|warning|info] | Judul singkat | Penjelasan detail.
---END---

Contoh:
---REKOMENDASI---
warning | Negosiasi ulang harga ke PT Sumber Jaya | Harga beli Pupuk Urea naik 40% dari rata-rata 3 bulan lalu. Potensi hemat Rp 2,4 juta/bulan jika negosiasi ke harga lama.
critical | Cash flow gap minggu depan | Piutang jatuh tempo Rp 45 juta tapi hutang supplier Rp 52 juta. Gap Rp 7 juta. Kirim reminder tagihan ke 3 customer overdue.
info | Lahan A1 paling efisien | HPP/kg dari A1 (Rp 3.100) 35% lebih rendah dari B2 (Rp 4.200). Pertimbangkan replikasi metode tanam A1 ke lahan lain.
---END---
PROMPT;
    }

    /**
     * Parse Gemini response into structured recommendations.
     */
    private function parseRecommendations(string $text): array
    {
        $recommendations = [];

        // Extract between ---REKOMENDASI--- and ---END---
        if (preg_match('/---REKOMENDASI---(.+?)---END---/s', $text, $match)) {
            $lines = array_filter(array_map('trim', explode("\n", $match[1])));

            foreach ($lines as $line) {
                $parts = array_map('trim', explode('|', $line, 3));
                if (count($parts) < 3) continue;

                $severity = strtolower($parts[0]);
                if (!in_array($severity, ['critical', 'warning', 'info'])) $severity = 'info';

                $recommendations[] = [
                    'severity' => $severity,
                    'title'    => $parts[1],
                    'body'     => $parts[2],
                ];
            }
        }

        // Fallback: if parsing failed, try to extract any useful text
        if (empty($recommendations) && strlen($text) > 50) {
            $recommendations[] = [
                'severity' => 'info',
                'title'    => '💡 Ringkasan AI Advisor',
                'body'     => mb_substr(strip_tags($text), 0, 500),
            ];
        }

        return array_slice($recommendations, 0, 5);
    }

    /**
     * Save recommendations as ErpNotifications.
     */
    private function saveRecommendations(int $tenantId, array $recommendations): void
    {
        if (empty($recommendations)) return;

        $recipients = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['admin', 'manager'])
            ->pluck('id');

        $icons = ['critical' => '🚨', 'warning' => '⚠️', 'info' => '💡'];

        foreach ($recommendations as $rec) {
            // Skip if same title already sent today
            $exists = ErpNotification::where('tenant_id', $tenantId)
                ->where('type', 'ai_advisor')
                ->where('title', 'like', '%' . mb_substr($rec['title'], 0, 50) . '%')
                ->whereDate('created_at', today())
                ->exists();
            if ($exists) continue;

            $icon = $icons[$rec['severity']] ?? '💡';

            foreach ($recipients as $userId) {
                ErpNotification::create([
                    'tenant_id' => $tenantId,
                    'user_id'   => $userId,
                    'type'      => 'ai_advisor',
                    'title'     => "{$icon} {$rec['title']}",
                    'body'      => $rec['body'],
                    'data'      => [
                        'severity' => $rec['severity'],
                        'source'   => 'ai_financial_advisor',
                    ],
                ]);
            }
        }
    }
}
