<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\FbOrder;
use App\Models\FbSupplyTransaction;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FbReportsController extends Controller
{
    // tenantId() inherited from parent Controller

    /**
     * Display F&B reports dashboard
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Revenue by order type
        $revenueByType = FbOrder::where('tenant_id', $this->tenantId())
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->select('order_type', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('order_type')
            ->get();

        // Daily revenue trend
        $dailyRevenue = FbOrder::where('tenant_id', $this->tenantId())
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->select(DB::raw('DATE(order_date) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top selling items
        $topItems = MenuItem::where('tenant_id', $this->tenantId())
            ->withSum([
                'orderItems as total_quantity' => function ($q) use ($startDate, $endDate) {
                    $q->whereHas('order', function ($oq) use ($startDate, $endDate) {
                        $oq->whereBetween('order_date', [$startDate, $endDate])
                            ->where('status', 'completed');
                    });
                },
            ], 'quantity')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get()
            ->filter(fn ($item) => $item->total_quantity > 0);

        // Popular categories
        $categoryStats = FbOrder::where('tenant_id', $this->tenantId())
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->join('fb_order_items', 'fb_orders.id', '=', 'fb_order_items.order_id')
            ->join('menu_items', 'fb_order_items.menu_item_id', '=', 'menu_items.id')
            ->leftJoin('product_categories', 'menu_items.category_id', '=', 'product_categories.id')
            ->select(
                'product_categories.name as category_name',
                DB::raw('COUNT(DISTINCT fb_orders.id) as order_count'),
                DB::raw('SUM(fb_order_items.quantity) as total_items_sold'),
                DB::raw('SUM(fb_order_items.line_total) as revenue')
            )
            ->groupBy('product_categories.name')
            ->orderByDesc('revenue')
            ->get();

        // Supply usage report
        $supplyUsage = FbSupplyTransaction::where('tenant_id', $this->tenantId())
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->whereIn('transaction_type', ['usage', 'waste'])
            ->join('fb_supplies', 'fb_supply_transactions.supply_id', '=', 'fb_supplies.id')
            ->select(
                'fb_supplies.name as supply_name',
                'fb_supplies.unit',
                DB::raw('SUM(CASE WHEN transaction_type = "usage" THEN ABS(quantity) ELSE 0 END) as usage_qty'),
                DB::raw('SUM(CASE WHEN transaction_type = "waste" THEN ABS(quantity) ELSE 0 END) as waste_qty'),
                DB::raw('SUM(ABS(total_cost)) as total_cost')
            )
            ->groupBy('fb_supplies.name', 'fb_supplies.unit')
            ->orderByDesc('total_cost')
            ->limit(10)
            ->get();

        // Summary stats
        $stats = [
            'total_revenue' => FbOrder::where('tenant_id', $this->tenantId())
                ->whereBetween('order_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->sum('total_amount'),
            'total_orders' => FbOrder::where('tenant_id', $this->tenantId())
                ->whereBetween('order_date', [$startDate, $endDate])
                ->count(),
            'avg_order_value' => FbOrder::where('tenant_id', $this->tenantId())
                ->whereBetween('order_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->avg('total_amount') ?? 0,
            'total_supply_cost' => FbSupplyTransaction::where('tenant_id', $this->tenantId())
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->whereIn('transaction_type', ['purchase'])
                ->sum('total_cost'),
        ];

        $stats['gross_profit'] = $stats['total_revenue'] - $stats['total_supply_cost'];
        $stats['profit_margin'] = $stats['total_revenue'] > 0
            ? ($stats['gross_profit'] / $stats['total_revenue']) * 100
            : 0;

        return view('hotel.fb.reports.index', compact(
            'stats',
            'revenueByType',
            'dailyRevenue',
            'topItems',
            'categoryStats',
            'supplyUsage',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export F&B report to CSV
     */
    public function export(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $orders = FbOrder::where('tenant_id', $this->tenantId())
            ->whereBetween('order_date', [$startDate, $endDate])
            ->with('items.menuItem')
            ->orderBy('order_date', 'desc')
            ->get();

        $filename = 'fb_report_'.$startDate.'_to_'.$endDate.'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'Order Number',
                'Date',
                'Type',
                'Status',
                'Table/Room',
                'Items',
                'Subtotal',
                'Tax',
                'Service Charge',
                'Total',
            ]);

            // Data
            foreach ($orders as $order) {
                $itemNames = $order->items->map(
                    fn ($item) => "{$item->menuItem->name} (x{$item->quantity})"
                )->implode('; ');

                fputcsv($file, [
                    $order->order_number,
                    $order->order_date->format('Y-m-d H:i'),
                    ucfirst($order->order_type),
                    ucfirst($order->status),
                    $order->table_number ?? $order->room_number ?? '-',
                    $itemNames,
                    number_format($order->subtotal, 2),
                    number_format($order->tax_amount, 2),
                    number_format($order->service_charge, 2),
                    number_format($order->total_amount, 2),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
