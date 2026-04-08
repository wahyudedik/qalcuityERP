<?php

namespace App\Services\ERP;

use App\Models\Attendance;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Payable;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Transaction;
use Carbon\Carbon;

class SmartQueryTools
{
    public function __construct(protected int $tenantId, protected int $userId)
    {
    }

    public static function definitions(): array
    {
        return [
            [
                'name' => 'smart_query',
                'description' => 'Query data bisnis secara fleksibel untuk pertanyaan yang tidak ter-cover tool lain. Gunakan untuk: '
                    . '"customer mana yang belum bayar lebih dari 30 hari?", '
                    . '"produk apa yang belum pernah terjual bulan ini?", '
                    . '"karyawan siapa yang absen terbanyak?", '
                    . '"supplier mana yang paling sering kita beli?", '
                    . '"produk dengan margin tertinggi?", '
                    . '"customer dengan pembelian terbesar?", '
                    . '"stok produk yang hampir habis minggu ini?".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'query_type' => [
                            'type' => 'string',
                            'description' => 'Jenis query: overdue_customers, unsold_products, absent_employees, top_customers, top_suppliers, high_margin_products, low_stock_alert, inactive_customers, overdue_payables, top_selling_products',
                        ],
                        'days' => ['type' => 'integer', 'description' => 'Jumlah hari untuk filter (default: 30)'],
                        'limit' => ['type' => 'integer', 'description' => 'Jumlah hasil (default: 10)'],
                    ],
                    'required' => ['query_type'],
                ],
            ],
        ];
    }

    public function smartQuery(array $args): array
    {
        $type = $args['query_type'];
        $days = (int) ($args['days'] ?? 30);
        $limit = (int) ($args['limit'] ?? 10);

        return match ($type) {
            'overdue_customers' => $this->overdueCustomers($days, $limit),
            'unsold_products' => $this->unsoldProducts($days, $limit),
            'absent_employees' => $this->absentEmployees($days, $limit),
            'top_customers' => $this->topCustomers($days, $limit),
            'top_suppliers' => $this->topSuppliers($days, $limit),
            'high_margin_products' => $this->highMarginProducts($limit),
            'low_stock_alert' => $this->lowStockAlert($limit),
            'inactive_customers' => $this->inactiveCustomers($days, $limit),
            'overdue_payables' => $this->overduePayables($limit),
            'top_selling_products' => $this->topSellingProducts($days, $limit),
            default => ['status' => 'error', 'message' => "Query type '{$type}' tidak dikenali."],
        };
    }

    private function overdueCustomers(int $days, int $limit): array
    {
        $rows = Invoice::where('tenant_id', $this->tenantId)
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', now()->subDays($days))
            ->with('customer')
            ->orderByDesc('total_amount')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty())
            return ['status' => 'success', 'message' => "Tidak ada customer dengan piutang jatuh tempo lebih dari {$days} hari.", 'data' => []];

        return [
            'status' => 'success',
            'data' => $rows->map(fn($i) => [
                'customer' => $i->customer?->name ?? '-',
                'invoice' => $i->invoice_number,
                'jumlah' => 'Rp ' . number_format($i->total_amount, 0, ',', '.'),
                'jatuh_tempo' => $i->due_date?->format('d M Y'),
                'hari_lewat' => $i->due_date ? $i->due_date->diffInDays(now()) . ' hari' : '-',
            ])->toArray()
        ];
    }

    private function unsoldProducts(int $days, int $limit): array
    {
        $soldIds = SalesOrderItem::whereHas(
            'salesOrder',
            fn($q) =>
            $q->where('tenant_id', $this->tenantId)
                ->where('date', '>=', now()->subDays($days))
        )->pluck('product_id')->unique();

        $rows = Product::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->whereNotIn('id', $soldIds)
            ->withSum('stocks as total_stock', 'quantity')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty())
            return ['status' => 'success', 'message' => "Semua produk aktif terjual dalam {$days} hari terakhir.", 'data' => []];

        return [
            'status' => 'success',
            'data' => $rows->map(fn($p) => [
                'produk' => $p->name,
                'stok' => ($p->total_stock ?? 0) . ' ' . $p->unit,
                'harga' => 'Rp ' . number_format($p->price_sell, 0, ',', '.'),
            ])->toArray()
        ];
    }

    private function absentEmployees(int $days, int $limit): array
    {
        $rows = Attendance::where('tenant_id', $this->tenantId)
            ->where('status', 'absent')
            ->where('date', '>=', now()->subDays($days))
            ->selectRaw('employee_id, COUNT(*) as total_absen')
            ->groupBy('employee_id')
            ->orderByDesc('total_absen')
            ->limit($limit)
            ->with('employee')
            ->get();

        if ($rows->isEmpty())
            return ['status' => 'success', 'message' => "Tidak ada data absensi dalam {$days} hari terakhir.", 'data' => []];

        return [
            'status' => 'success',
            'data' => $rows->map(fn($r) => [
                'karyawan' => $r->employee?->name ?? '-',
                'total_absen' => $r->total_absen . ' hari',
            ])->toArray()
        ];
    }

    private function topCustomers(int $days, int $limit): array
    {
        $rows = SalesOrder::where('tenant_id', $this->tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->where('date', '>=', now()->subDays($days))
            ->selectRaw('customer_id, SUM(total) as total_belanja, COUNT(*) as total_order')
            ->groupBy('customer_id')
            ->orderByDesc('total_belanja')
            ->limit($limit)
            ->with('customer')
            ->get();

        if ($rows->isEmpty())
            return ['status' => 'success', 'message' => 'Belum ada data penjualan.', 'data' => []];

        return [
            'status' => 'success',
            'data' => $rows->map(fn($r) => [
                'customer' => $r->customer?->name ?? 'Walk-in',
                'total_belanja' => 'Rp ' . number_format($r->total_belanja, 0, ',', '.'),
                'total_order' => $r->total_order . ' order',
            ])->toArray()
        ];
    }

    private function topSuppliers(int $days, int $limit): array
    {
        $rows = \App\Models\PurchaseOrder::where('tenant_id', $this->tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->where('order_date', '>=', now()->subDays($days))
            ->selectRaw('supplier_id, SUM(total_amount) as total_pembelian, COUNT(*) as total_po')
            ->groupBy('supplier_id')
            ->orderByDesc('total_pembelian')
            ->limit($limit)
            ->with('supplier')
            ->get();

        if ($rows->isEmpty())
            return ['status' => 'success', 'message' => 'Belum ada data pembelian.', 'data' => []];

        return [
            'status' => 'success',
            'data' => $rows->map(fn($r) => [
                'supplier' => $r->supplier?->name ?? '-',
                'total_pembelian' => 'Rp ' . number_format($r->total_pembelian, 0, ',', '.'),
                'total_po' => $r->total_po . ' PO',
            ])->toArray()
        ];
    }

    private function highMarginProducts(int $limit): array
    {
        $rows = Product::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->where('price_buy', '>', 0)
            ->orderByRaw('((price_sell - price_buy) / price_buy) DESC')
            ->limit($limit)
            ->get();

        return [
            'status' => 'success',
            'data' => $rows->map(fn($p) => [
                'produk' => $p->name,
                'harga_beli' => 'Rp ' . number_format($p->price_buy, 0, ',', '.'),
                'harga_jual' => 'Rp ' . number_format($p->price_sell, 0, ',', '.'),
                'margin' => $p->price_buy > 0 ? round((($p->price_sell - $p->price_buy) / $p->price_buy) * 100, 1) . '%' : '-',
                'profit_unit' => 'Rp ' . number_format($p->price_sell - $p->price_buy, 0, ',', '.'),
            ])->toArray()
        ];
    }

    private function lowStockAlert(int $limit): array
    {
        // BUG-INV-002 FIX: Eager load with selective columns
        $rows = ProductStock::whereHas('product', fn($q) => $q->where('tenant_id', $this->tenantId)->where('is_active', true))
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->whereColumn('product_stocks.quantity', '<=', 'products.stock_min')
            ->select('product_stocks.*')
            ->with([
                'product' => function ($q) {
                    $q->select('id', 'name', 'unit', 'stock_min');
                },
                'warehouse' => function ($q) {
                    $q->select('id', 'name');
                }
            ])
            ->limit($limit)
            ->get();

        if ($rows->isEmpty())
            return ['status' => 'success', 'message' => 'Semua stok dalam kondisi aman.', 'data' => []];

        return [
            'status' => 'success',
            'data' => $rows->map(fn($s) => [
                'produk' => $s->product?->name,
                'gudang' => $s->warehouse?->name,
                'stok' => $s->quantity . ' ' . $s->product?->unit,
                'stok_min' => $s->product?->stock_min . ' ' . $s->product?->unit,
            ])->toArray()
        ];
    }

    private function inactiveCustomers(int $days, int $limit): array
    {
        $activeIds = SalesOrder::where('tenant_id', $this->tenantId)
            ->where('date', '>=', now()->subDays($days))
            ->pluck('customer_id')->unique();

        $rows = Customer::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->whereNotIn('id', $activeIds)
            ->withMax('salesOrders as last_order_date', 'date')
            ->orderBy('last_order_date')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty())
            return ['status' => 'success', 'message' => "Semua customer aktif dalam {$days} hari terakhir.", 'data' => []];

        return [
            'status' => 'success',
            'data' => $rows->map(fn($c) => [
                'customer' => $c->name,
                'telepon' => $c->phone ?? '-',
                'terakhir_beli' => $c->last_order_date ? Carbon::parse($c->last_order_date)->format('d M Y') : 'Belum pernah',
            ])->toArray()
        ];
    }

    private function overduePayables(int $limit): array
    {
        $rows = Payable::where('tenant_id', $this->tenantId)
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', today())
            ->with('supplier')
            ->orderBy('due_date')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty())
            return ['status' => 'success', 'message' => 'Tidak ada hutang yang jatuh tempo.', 'data' => []];

        return [
            'status' => 'success',
            'data' => $rows->map(fn($p) => [
                'supplier' => $p->supplier?->name ?? '-',
                'jumlah' => 'Rp ' . number_format($p->total_amount, 0, ',', '.'),
                'jatuh_tempo' => $p->due_date?->format('d M Y'),
                'hari_lewat' => $p->due_date ? $p->due_date->diffInDays(now()) . ' hari' : '-',
            ])->toArray()
        ];
    }

    private function topSellingProducts(int $days, int $limit): array
    {
        $rows = SalesOrderItem::whereHas(
            'salesOrder',
            fn($q) =>
            $q->where('tenant_id', $this->tenantId)
                ->whereNotIn('status', ['cancelled'])
                ->where('date', '>=', now()->subDays($days))
        )->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->selectRaw('products.name, SUM(sales_order_items.quantity) as total_qty, SUM(sales_order_items.total) as total_omzet')
            ->groupBy('products.name')
            ->orderByDesc('total_omzet')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty())
            return ['status' => 'success', 'message' => 'Belum ada data penjualan.', 'data' => []];

        return [
            'status' => 'success',
            'data' => $rows->map(fn($r) => [
                'produk' => $r->name,
                'qty_terjual' => $r->total_qty,
                'omzet' => 'Rp ' . number_format($r->total_omzet, 0, ',', '.'),
            ])->toArray()
        ];
    }
}
