<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ApiStatsController extends ApiBaseController
{
    public function summary(Request $request)
    {
        $tenantId = $this->tenantId();
        $period = $request->get('period', now()->format('Y-m'));
        [$y, $m] = explode('-', $period);

        $revenue = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereYear('date', $y)->whereMonth('date', $m)->sum('total');

        $orders = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereYear('date', $y)->whereMonth('date', $m)->count();

        $income = Transaction::where('tenant_id', $tenantId)->where('type', 'income')
            ->whereYear('date', $y)->whereMonth('date', $m)->sum('amount');
        $expense = Transaction::where('tenant_id', $tenantId)->where('type', 'expense')
            ->whereYear('date', $y)->whereMonth('date', $m)->sum('amount');

        $overdueAr = Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', today())
            ->sum('remaining_amount');

        return $this->ok([
            'period' => $period,
            'revenue' => (float) $revenue,
            'orders' => (int) $orders,
            'income' => (float) $income,
            'expense' => (float) $expense,
            'profit' => (float) ($income - $expense),
            'overdue_ar' => (float) $overdueAr,
            'total_customers' => Customer::where('tenant_id', $tenantId)->where('is_active', true)->count(),
            'total_products' => Product::where('tenant_id', $tenantId)->where('is_active', true)->count(),
        ]);
    }
}
