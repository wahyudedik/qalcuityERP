<?php

namespace App\Http\Controllers;

use App\Exports\AgingReportExport;
use App\Exports\BalanceSheetExport;
use App\Exports\BudgetReportExport;
use App\Exports\CashFlowExport;
use App\Exports\FinanceReportExport;
use App\Exports\HrmReportExport;
use App\Exports\IncomeStatementExport;
use App\Exports\InventoryReportExport;
use App\Exports\PayrollExport;
use App\Exports\ReceivablesReportExport;
use App\Exports\SalesReportExport;
use App\Models\Attendance;
use App\Models\Budget;
use App\Models\Invoice;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use App\Models\Transaction;
use App\Services\CashFlowProjectionService;
use App\Services\FinancialStatementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        return view('reports.index');
    }

    private function requireTenantId(Request $request): int
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(is_null($tenantId), 403, 'Fitur laporan tidak tersedia untuk akun Super Admin.');

        return $tenantId;
    }

    /**
     * BUG-REP-001 FIX: Validate date range for reports
     */
    protected function validateDateRange(Request $request): void
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ], [
            'start_date.required' => 'Tanggal awal wajib diisi.',
            'start_date.date' => 'Format tanggal awal tidak valid. Gunakan: YYYY-MM-DD',
            'end_date.required' => 'Tanggal akhir wajib diisi.',
            'end_date.date' => 'Format tanggal akhir tidak valid. Gunakan: YYYY-MM-DD',
            'end_date.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal awal.',
        ]);
    }

    // ─── Excel Exports ────────────────────────────────────────────

    public function exportSalesExcel(Request $request)
    {
        $this->validateDateRange($request); // BUG-REP-001 FIX

        $tenantId = $this->requireTenantId($request);
        $filename = 'laporan-penjualan-'.$request->start_date.'-sd-'.$request->end_date.'.xlsx';

        return Excel::download(
            new SalesReportExport($tenantId, $request->start_date, $request->end_date),
            $filename
        );
    }

    public function exportFinanceExcel(Request $request)
    {
        $this->validateDateRange($request); // BUG-REP-001 FIX

        $tenantId = $this->requireTenantId($request);
        $filename = 'laporan-keuangan-'.$request->start_date.'-sd-'.$request->end_date.'.xlsx';

        return Excel::download(
            new FinanceReportExport($tenantId, $request->start_date, $request->end_date),
            $filename
        );
    }

    public function exportInventoryExcel(Request $request)
    {
        $tenantId = $this->requireTenantId($request);
        $filename = 'laporan-inventori-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new InventoryReportExport($tenantId), $filename);
    }

    // ─── PDF Exports ──────────────────────────────────────────────

    public function exportSalesPdf(Request $request)
    {
        $this->validateDateRange($request); // BUG-REP-001 FIX

        $tenantId = $this->requireTenantId($request);
        $orders = SalesOrder::with(['customer', 'user'])
            ->where('tenant_id', $tenantId)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->orderBy('date')
            ->get();

        $totalRevenue = $orders->whereNotIn('status', ['cancelled'])->sum('total');

        $pdf = Pdf::loadView('reports.pdf', [
            'title' => 'Laporan Penjualan',
            'tenant_name' => $request->user()->tenant?->name ?? 'Qalcuity ERP',
            'period' => $request->start_date.' s/d '.$request->end_date,
            'summary' => [
                ['label' => 'Total Order', 'value' => $orders->count()],
                ['label' => 'Total Pendapatan', 'value' => 'Rp '.number_format($totalRevenue, 0, ',', '.')],
                ['label' => 'Order Selesai', 'value' => $orders->where('status', 'delivered')->count()],
                ['label' => 'Order Dibatalkan', 'value' => $orders->where('status', 'cancelled')->count()],
            ],
            'headers' => ['No. Order', 'Tanggal', 'Pelanggan', 'Status', 'Total'],
            'rows' => $orders->map(fn ($o) => [
                $o->number,
                $o->date->format('d/m/Y'),
                $o->customer?->name ?? '(Walk-in)',
                strtoupper($o->status),
                'Rp '.number_format($o->total, 0, ',', '.'),
            ])->toArray(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-penjualan-'.$request->start_date.'.pdf');
    }

    public function exportFinancePdf(Request $request)
    {
        $this->validateDateRange($request); // BUG-REP-001 FIX

        $tenantId = $this->requireTenantId($request);
        $transactions = Transaction::with('category')
            ->where('tenant_id', $tenantId)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->orderBy('date')
            ->get();

        $income = $transactions->where('type', 'income')->sum('amount');
        $expense = $transactions->where('type', 'expense')->sum('amount');

        $pdf = Pdf::loadView('reports.pdf', [
            'title' => 'Laporan Keuangan',
            'tenant_name' => $request->user()->tenant?->name ?? 'Qalcuity ERP',
            'period' => $request->start_date.' s/d '.$request->end_date,
            'summary' => [
                ['label' => 'Total Pemasukan', 'value' => 'Rp '.number_format($income, 0, ',', '.')],
                ['label' => 'Total Pengeluaran', 'value' => 'Rp '.number_format($expense, 0, ',', '.')],
                ['label' => 'Profit/Rugi', 'value' => 'Rp '.number_format($income - $expense, 0, ',', '.')],
            ],
            'headers' => ['No. Transaksi', 'Tanggal', 'Tipe', 'Kategori', 'Keterangan', 'Nominal'],
            'rows' => $transactions->map(fn ($t) => [
                $t->number,
                $t->date->format('d/m/Y'),
                strtoupper($t->type),
                $t->category?->name ?? '-',
                $t->description,
                'Rp '.number_format($t->amount, 0, ',', '.'),
            ])->toArray(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-keuangan-'.$request->start_date.'.pdf');
    }

    public function exportInventoryPdf(Request $request)
    {
        $tenantId = $this->requireTenantId($request);
        $stocks = ProductStock::with(['product', 'warehouse'])
            ->whereHas('product', fn ($q) => $q->where('tenant_id', $tenantId)->where('is_active', true))
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->select('product_stocks.*')
            ->orderBy('products.name')
            ->get();

        $lowCount = $stocks->filter(fn ($s) => $s->quantity <= $s->product->stock_min)->count();

        $pdf = Pdf::loadView('reports.pdf', [
            'title' => 'Laporan Inventori',
            'tenant_name' => $request->user()->tenant?->name ?? 'Qalcuity ERP',
            'period' => 'Per '.now()->format('d M Y'),
            'summary' => [
                ['label' => 'Total Item', 'value' => $stocks->count()],
                ['label' => 'Stok Menipis', 'value' => $lowCount],
                ['label' => 'Stok Aman', 'value' => $stocks->count() - $lowCount],
            ],
            'headers' => ['Produk', 'SKU', 'Gudang', 'Satuan', 'Stok', 'Min', 'Status'],
            'rows' => $stocks->map(fn ($s) => [
                $s->product->name,
                $s->product->sku ?? '-',
                $s->warehouse->name,
                $s->product->unit,
                $s->quantity,
                $s->product->stock_min,
                $s->quantity <= $s->product->stock_min ? 'MENIPIS' : 'AMAN',
            ])->toArray(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-inventori-'.now()->format('Y-m-d').'.pdf');
    }

    // ─── HRM Export ───────────────────────────────────────────────

    public function exportHrmExcel(Request $request)
    {
        $this->validateDateRange($request); // BUG-REP-001 FIX
        $tenantId = $this->requireTenantId($request);
        $filename = 'laporan-kehadiran-'.$request->start_date.'-sd-'.$request->end_date.'.xlsx';

        return Excel::download(new HrmReportExport($tenantId, $request->start_date, $request->end_date), $filename);
    }

    public function exportHrmPdf(Request $request)
    {
        $this->validateDateRange($request); // BUG-REP-001 FIX
        $tenantId = $this->requireTenantId($request);

        $attendances = Attendance::with(['employee'])
            ->whereHas('employee', fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->orderBy('date')
            ->get();

        $byStatus = $attendances->groupBy('status')->map->count();

        $pdf = Pdf::loadView('reports.pdf', [
            'title' => 'Laporan Kehadiran Karyawan',
            'tenant_name' => $request->user()->tenant?->name ?? 'Qalcuity ERP',
            'period' => $request->start_date.' s/d '.$request->end_date,
            'summary' => [
                ['label' => 'Total Hadir', 'value' => $byStatus->get('present', 0)],
                ['label' => 'Terlambat', 'value' => $byStatus->get('late', 0)],
                ['label' => 'Absen', 'value' => $byStatus->get('absent', 0)],
                ['label' => 'Izin/Sakit', 'value' => ($byStatus->get('leave', 0) + $byStatus->get('sick', 0))],
            ],
            'headers' => ['Tanggal', 'Karyawan', 'Posisi', 'Status', 'Check In', 'Check Out'],
            'rows' => $attendances->map(fn ($a) => [
                $a->date->format('d/m/Y'),
                $a->employee->name,
                $a->employee->position ?? '-',
                strtoupper($a->status),
                $a->check_in ?? '-',
                $a->check_out ?? '-',
            ])->toArray(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-kehadiran-'.$request->start_date.'.pdf');
    }

    // ─── Receivables Export ───────────────────────────────────────

    public function exportReceivablesExcel(Request $request)
    {
        $this->validateDateRange($request); // BUG-REP-001 FIX
        $tenantId = $this->requireTenantId($request);
        $filename = 'laporan-piutang-'.$request->start_date.'-sd-'.$request->end_date.'.xlsx';

        return Excel::download(new ReceivablesReportExport($tenantId, $request->start_date, $request->end_date), $filename);
    }

    public function exportReceivablesPdf(Request $request)
    {
        $this->validateDateRange($request); // BUG-REP-001 FIX
        $tenantId = $this->requireTenantId($request);

        $invoices = Invoice::with(['customer'])
            ->where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$request->start_date.' 00:00:00', $request->end_date.' 23:59:59'])
            ->orderBy('due_date')
            ->get();

        $totalAmount = $invoices->sum('total_amount');
        $totalPaid = $invoices->sum('paid_amount');
        $overdue = $invoices->filter(fn ($i) => $i->status !== 'paid' && $i->due_date < now())->count();

        $pdf = Pdf::loadView('reports.pdf', [
            'title' => 'Laporan Piutang',
            'tenant_name' => $request->user()->tenant?->name ?? 'Qalcuity ERP',
            'period' => $request->start_date.' s/d '.$request->end_date,
            'summary' => [
                ['label' => 'Total Tagihan', 'value' => 'Rp '.number_format($totalAmount, 0, ',', '.')],
                ['label' => 'Sudah Dibayar', 'value' => 'Rp '.number_format($totalPaid, 0, ',', '.')],
                ['label' => 'Belum Dibayar', 'value' => 'Rp '.number_format($totalAmount - $totalPaid, 0, ',', '.')],
                ['label' => 'Jatuh Tempo', 'value' => $overdue.' invoice'],
            ],
            'headers' => ['No. Invoice', 'Customer', 'Jumlah', 'Terbayar', 'Sisa', 'Jatuh Tempo', 'Status'],
            'rows' => $invoices->map(fn ($i) => [
                $i->number,
                $i->customer?->name ?? '-',
                'Rp '.number_format($i->total_amount, 0, ',', '.'),
                'Rp '.number_format($i->paid_amount, 0, ',', '.'),
                'Rp '.number_format($i->remaining_amount, 0, ',', '.'),
                $i->due_date?->format('d/m/Y') ?? '-',
                strtoupper($i->status),
            ])->toArray(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-piutang-'.$request->start_date.'.pdf');
    }

    // ─── Income Statement Excel ───────────────────────────────────

    public function exportIncomeStatementExcel(Request $request)
    {
        $this->validateDateRange($request); // BUG-REP-001 FIX
        $tenantId = $this->requireTenantId($request);
        $tenantName = $request->user()->tenant?->name ?? 'Qalcuity ERP';

        return Excel::download(
            new IncomeStatementExport($tenantId, $request->start_date, $request->end_date, $tenantName),
            'laba-rugi-'.$request->start_date.'-'.$request->end_date.'.xlsx'
        );
    }

    // ─── Payroll Excel ────────────────────────────────────────────

    public function exportPayrollExcel(Request $request)
    {
        $request->validate(['period' => 'required|date_format:Y-m']);
        $tenantId = $this->requireTenantId($request);
        $tenantName = $request->user()->tenant?->name ?? 'Qalcuity ERP';

        return Excel::download(
            new PayrollExport($tenantId, $request->period, $tenantName),
            'payroll-'.$request->period.'.xlsx'
        );
    }

    // ─── AR Aging Excel ───────────────────────────────────────────

    public function exportAgingExcel(Request $request)
    {
        $tenantId = $this->requireTenantId($request);
        $tenantName = $request->user()->tenant?->name ?? 'Qalcuity ERP';

        return Excel::download(
            new AgingReportExport($tenantId, $tenantName),
            'aging-piutang-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    // ─── Profit & Loss PDF ────────────────────────────────────────

    public function exportProfitLossPdf(Request $request)
    {
        $this->validateDateRange($request); // BUG-REP-001 FIX
        $tenantId = $this->requireTenantId($request);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);

        $income = Transaction::where('tenant_id', $tenantId)->where('type', 'income')
            ->whereBetween('date', [$start, $end])->sum('amount');
        $expense = Transaction::where('tenant_id', $tenantId)->where('type', 'expense')
            ->whereBetween('date', [$start, $end])->sum('amount');

        $expenseByCategory = Transaction::where('tenant_id', $tenantId)->where('type', 'expense')
            ->whereBetween('date', [$start, $end])
            ->leftJoin('expense_categories', 'transactions.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('COALESCE(expense_categories.name, "Tidak Berkategori") as category, SUM(transactions.amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $profit = $income - $expense;

        $pdf = Pdf::loadView('reports.pdf', [
            'title' => 'Laporan Laba Rugi',
            'tenant_name' => $request->user()->tenant?->name ?? 'Qalcuity ERP',
            'period' => $start->format('d M Y').' s/d '.$end->format('d M Y'),
            'summary' => [
                ['label' => 'Total Pendapatan', 'value' => 'Rp '.number_format($income, 0, ',', '.')],
                ['label' => 'Total Pengeluaran', 'value' => 'Rp '.number_format($expense, 0, ',', '.')],
                ['label' => 'Laba / Rugi Bersih', 'value' => 'Rp '.number_format($profit, 0, ',', '.')],
                ['label' => 'Status', 'value' => $profit >= 0 ? 'LABA' : 'RUGI'],
            ],
            'headers' => ['Kategori Biaya', 'Total', 'Persentase'],
            'rows' => $expenseByCategory->map(fn ($r) => [
                $r->category,
                'Rp '.number_format($r->total, 0, ',', '.'),
                $expense > 0 ? round(($r->total / $expense) * 100, 1).'%' : '0%',
            ])->toArray(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-laba-rugi-'.$request->start_date.'.pdf');
    }

    // ─── Balance Sheet ────────────────────────────────────────────

    public function exportBalanceSheetExcel(Request $request)
    {
        $request->validate(['as_of' => 'required|date']);
        $tenantId = $this->requireTenantId($request);
        $tenantName = $request->user()->tenant?->name ?? 'Qalcuity ERP';
        $filename = 'neraca-'.$request->as_of.'.xlsx';

        return Excel::download(
            new BalanceSheetExport($tenantId, $request->as_of, $tenantName),
            $filename
        );
    }

    public function exportBalanceSheetPdf(Request $request)
    {
        $request->validate(['as_of' => 'required|date']);
        $tenantId = $this->requireTenantId($request);
        $asOf = $request->as_of;
        $data = app(FinancialStatementService::class)->balanceSheet($tenantId, $asOf);
        $tenant = $request->user()->tenant;

        $pdf = Pdf::loadView('accounting.pdf.balance-sheet', compact('data', 'asOf', 'tenant'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('neraca-'.$asOf.'.pdf');
    }

    // ─── Cash Flow Statement ──────────────────────────────────────

    public function exportCashFlowExcel(Request $request)
    {
        $this->validateDateRange($request); // BUG-REP-001 FIX
        $tenantId = $this->requireTenantId($request);
        $tenantName = $request->user()->tenant?->name ?? 'Qalcuity ERP';
        $filename = 'arus-kas-'.$request->start_date.'-sd-'.$request->end_date.'.xlsx';

        return Excel::download(
            new CashFlowExport($tenantId, $request->start_date, $request->end_date, $tenantName),
            $filename
        );
    }

    public function exportCashFlowPdf(Request $request)
    {
        $this->validateDateRange($request); // BUG-REP-001 FIX
        $tenantId = $this->requireTenantId($request);
        $from = $request->start_date;
        $to = $request->end_date;
        $data = app(FinancialStatementService::class)->cashFlowStatement($tenantId, $from, $to);
        $tenant = $request->user()->tenant;

        $pdf = Pdf::loadView('accounting.pdf.cash-flow', compact('data', 'from', 'to', 'tenant'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('arus-kas-'.$from.'-sd-'.$to.'.pdf');
    }

    // ─── Budget vs Actual ─────────────────────────────────────────

    public function exportBudgetExcel(Request $request)
    {
        $request->validate(['period' => 'required|string|regex:/^\d{4}-\d{2}$/']);
        $tenantId = $this->requireTenantId($request);
        $filename = 'budget-vs-aktual-'.$request->period.'.xlsx';

        return Excel::download(
            new BudgetReportExport($tenantId, $request->period),
            $filename
        );
    }

    // ─── Cash Flow Projection ─────────────────────────────────────

    public function cashFlowProjection(Request $request)
    {
        $tenantId = $this->requireTenantId($request);
        $days = (int) $request->get('days', 90);
        $days = in_array($days, [30, 60, 90]) ? $days : 90;

        $data = app(CashFlowProjectionService::class)->project($tenantId, $days);

        return view('reports.cash-flow-projection', compact('data', 'days'));
    }

    public function cashFlowProjectionData(Request $request)
    {
        $tenantId = $this->requireTenantId($request);
        $days = (int) $request->get('days', 90);
        $days = in_array($days, [30, 60, 90]) ? $days : 90;

        $data = app(CashFlowProjectionService::class)->project($tenantId, $days);

        // Return chart-friendly arrays
        $labels = array_keys($data['daily']);
        $balances = array_column(array_values($data['daily']), 'balance');
        $inflows = array_column(array_values($data['daily']), 'inflow');
        $outflows = array_column(array_values($data['daily']), 'outflow');

        return response()->json([
            'labels' => $labels,
            'balances' => $balances,
            'inflows' => $inflows,
            'outflows' => $outflows,
            'weeks' => $data['weeks'],
            'alerts' => $data['alerts'],
            'totals' => $data['totals'],
            'opening' => $data['opening_balance'],
        ]);
    }

    public function exportBudgetPdf(Request $request)
    {
        $request->validate(['period' => 'required|string|regex:/^\d{4}-\d{2}$/']);
        $tenantId = $this->requireTenantId($request);
        $period = $request->period;

        $budgets = Budget::where('tenant_id', $tenantId)
            ->where('period', $period)
            ->where('status', 'active')
            ->orderBy('department')
            ->orderBy('name')
            ->get();

        $totalBudget = $budgets->sum('amount');
        $totalRealized = $budgets->sum('realized');
        $overCount = $budgets->filter(fn ($b) => $b->realized > $b->amount)->count();
        $usagePct = $totalBudget > 0 ? round($totalRealized / $totalBudget * 100, 1) : 0;

        $pdf = Pdf::loadView('reports.budget-pdf', [
            'tenant_name' => $request->user()->tenant?->name ?? 'Qalcuity ERP',
            'period' => $period,
            'budgets' => $budgets,
            'total_budget' => $totalBudget,
            'total_realized' => $totalRealized,
            'over_count' => $overCount,
            'usage_pct' => $usagePct,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('budget-vs-aktual-'.$period.'.pdf');
    }
}
