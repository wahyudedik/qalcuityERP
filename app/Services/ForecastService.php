<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ForecastService
{
    /**
     * Revenue forecast — linear regression on monthly SO totals.
     * Returns historical + projected months.
     */
    public function revenueForecast(int $tenantId, int $monthsHistory = 6, int $monthsForecast = 6): array
    {
        $historical = [];
        for ($i = $monthsHistory - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $total = SalesOrder::where('tenant_id', $tenantId)
                ->whereIn('status', ['confirmed', 'completed'])
                ->whereYear('date', $date->year)->whereMonth('date', $date->month)
                ->sum('total');
            $historical[] = [
                'period' => $date->format('Y-m'),
                'label' => $date->translatedFormat('M Y'),
                'amount' => (float) $total,
                'type' => 'actual',
            ];
        }

        // Linear regression
        $n = count($historical);
        $projected = [];
        if ($n >= 2) {
            $sumX = $sumY = $sumXY = $sumX2 = 0;
            foreach ($historical as $i => $h) {
                $sumX += $i;
                $sumY += $h['amount'];
                $sumXY += $i * $h['amount'];
                $sumX2 += $i * $i;
            }
            $denom = ($n * $sumX2 - $sumX * $sumX);
            $slope = $denom != 0 ? ($n * $sumXY - $sumX * $sumY) / $denom : 0;
            $intercept = ($sumY - $slope * $sumX) / $n;

            for ($i = 0; $i < $monthsForecast; $i++) {
                $date = now()->addMonths($i + 1);
                $predicted = max(0, $intercept + $slope * ($n + $i));
                $projected[] = [
                    'period' => $date->format('Y-m'),
                    'label' => $date->translatedFormat('M Y'),
                    'amount' => round($predicted, 0),
                    'type' => 'forecast',
                ];
            }
        }

        return ['historical' => $historical, 'projected' => $projected];
    }

    /**
     * Cash flow forecast — revenue in vs expenses out.
     */
    public function cashFlowForecast(int $tenantId, int $monthsHistory = 6, int $monthsForecast = 6): array
    {
        $data = [];
        // Historical
        for ($i = $monthsHistory - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $y = $date->year;
            $m = $date->month;

            $inflow = (float) Invoice::where('tenant_id', $tenantId)
                ->where('status', 'paid')
                ->whereYear('due_date', $y)->whereMonth('due_date', $m)
                ->sum('paid_amount');

            $outflow = (float) Transaction::where('tenant_id', $tenantId)
                ->where('type', 'expense')
                ->whereYear('date', $y)->whereMonth('date', $m)
                ->sum('amount');

            $data[] = [
                'period' => $date->format('Y-m'),
                'label' => $date->translatedFormat('M Y'),
                'inflow' => $inflow,
                'outflow' => $outflow,
                'net' => $inflow - $outflow,
                'type' => 'actual',
            ];
        }

        // Simple moving average forecast
        $recentInflows = array_slice(array_column($data, 'inflow'), -3);
        $recentOutflows = array_slice(array_column($data, 'outflow'), -3);
        $avgInflow = count($recentInflows) > 0 ? array_sum($recentInflows) / count($recentInflows) : 0;
        $avgOutflow = count($recentOutflows) > 0 ? array_sum($recentOutflows) / count($recentOutflows) : 0;

        // Growth trend
        $inflowGrowth = count($recentInflows) >= 2 && $recentInflows[0] > 0
            ? (end($recentInflows) - $recentInflows[0]) / $recentInflows[0] / count($recentInflows) : 0;

        for ($i = 0; $i < $monthsForecast; $i++) {
            $date = now()->addMonths($i + 1);
            $projInflow = round($avgInflow * (1 + $inflowGrowth * ($i + 1)), 0);
            $projOutflow = round($avgOutflow * 1.02, 0); // assume 2% cost increase

            $data[] = [
                'period' => $date->format('Y-m'),
                'label' => $date->translatedFormat('M Y'),
                'inflow' => $projInflow,
                'outflow' => $projOutflow,
                'net' => $projInflow - $projOutflow,
                'type' => 'forecast',
            ];
        }

        return $data;
    }

    /**
     * Demand forecast per product — based on SO item history.
     */
    public function demandForecast(int $tenantId, int $monthsForecast = 3, int $limit = 10): array
    {
        // Top products by volume last 3 months
        $products = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.sales_order_id')
            ->join('products', 'products.id', '=', 'sales_order_items.product_id')
            ->where('sales_orders.tenant_id', $tenantId)
            ->whereIn('sales_orders.status', ['confirmed', 'completed'])
            ->where('sales_orders.date', '>=', now()->subMonths(3))
            ->selectRaw('products.id, products.name, products.unit, SUM(sales_order_items.quantity) as total_qty, COUNT(DISTINCT sales_orders.id) as order_count')
            ->groupBy('products.id', 'products.name', 'products.unit')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();

        $result = [];
        foreach ($products as $p) {
            // Monthly breakdown last 3 months
            $monthly = [];
            for ($i = 2; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $qty = DB::table('sales_order_items')
                    ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.sales_order_id')
                    ->where('sales_orders.tenant_id', $tenantId)
                    ->where('sales_order_items.product_id', $p->id)
                    ->whereIn('sales_orders.status', ['confirmed', 'completed'])
                    ->whereYear('sales_orders.date', $date->year)->whereMonth('sales_orders.date', $date->month)
                    ->sum('sales_order_items.quantity');
                $monthly[] = (float) $qty;
            }

            $avgMonthly = array_sum($monthly) / max(count($monthly), 1);
            $trend = count($monthly) >= 2 && $monthly[0] > 0
                ? (end($monthly) - $monthly[0]) / $monthly[0] / count($monthly) : 0;

            // Current stock
            $stock = DB::table('product_stocks')
                ->where('product_id', $p->id)->sum('quantity');

            $forecast = [];
            for ($i = 0; $i < $monthsForecast; $i++) {
                $projected = round($avgMonthly * (1 + $trend * ($i + 1)), 0);
                $forecast[] = max(0, $projected);
            }

            $result[] = [
                'product_id' => $p->id,
                'product_name' => $p->name,
                'unit' => $p->unit,
                'monthly_avg' => round($avgMonthly, 0),
                'current_stock' => (float) $stock,
                'months_of_stock' => $avgMonthly > 0 ? round($stock / $avgMonthly, 1) : null,
                'forecast' => $forecast,
                'trend' => round($trend * 100, 1), // % per month
            ];
        }

        return $result;
    }

    /**
     * AR/AP aging forecast — when will receivables be collected.
     */
    public function receivablesForecast(int $tenantId): array
    {
        $unpaid = Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->selectRaw('
                SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) <= 0 THEN remaining_amount ELSE 0 END) as current_amount,
                SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) BETWEEN 1 AND 30 THEN remaining_amount ELSE 0 END) as days_1_30,
                SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) BETWEEN 31 AND 60 THEN remaining_amount ELSE 0 END) as days_31_60,
                SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) BETWEEN 61 AND 90 THEN remaining_amount ELSE 0 END) as days_61_90,
                SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) > 90 THEN remaining_amount ELSE 0 END) as days_90_plus,
                SUM(remaining_amount) as total
            ')->first();

        // Collection rate from last 3 months
        $collected = Invoice::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->where('updated_at', '>=', now()->subMonths(3))
            ->count();
        $total = Invoice::where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subMonths(3))
            ->count();
        $collectionRate = $total > 0 ? round($collected / $total * 100, 1) : 0;

        return [
            'aging' => $unpaid,
            'collection_rate' => $collectionRate,
            'estimated_collection' => round(($unpaid->total ?? 0) * $collectionRate / 100, 0),
        ];
    }
}
