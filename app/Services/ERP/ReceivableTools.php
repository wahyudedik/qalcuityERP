<?php

namespace App\Services\ERP;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payable;
use App\Models\Payment;
use App\Models\Supplier;
use Illuminate\Support\Str;

class ReceivableTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    // ─── Tool Definitions ─────────────────────────────────────────

    public static function definitions(): array
    {
        return [
            [
                'name' => 'record_payment',
                'description' => 'Catat pembayaran piutang dari customer atau hutang ke supplier. '
                    .'Gunakan untuk: "customer Budi bayar tagihan 500 ribu", '
                    .'"bayar hutang supplier PT Maju 2 juta", '
                    .'"lunasi invoice INV-001", "catat pelunasan piutang Toko A".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'type' => ['type' => 'string', 'description' => 'receivable (piutang customer) atau payable (hutang supplier)'],
                        'party_name' => ['type' => 'string', 'description' => 'Nama customer atau supplier'],
                        'amount' => ['type' => 'number', 'description' => 'Jumlah yang dibayar (Rupiah)'],
                        'invoice_number' => ['type' => 'string', 'description' => 'Nomor invoice/payable spesifik (opsional, jika tidak diisi akan bayar yang paling lama)'],
                        'payment_method' => ['type' => 'string', 'description' => 'Metode bayar: cash, transfer, qris (default: cash)'],
                        'notes' => ['type' => 'string', 'description' => 'Catatan pembayaran (opsional)'],
                    ],
                    'required' => ['type', 'party_name', 'amount'],
                ],
            ],
            [
                'name' => 'get_receivables',
                'description' => 'Tampilkan daftar piutang customer yang belum lunas. '
                    .'Gunakan untuk: "tagihan yang belum dibayar", "piutang outstanding", '
                    .'"siapa yang masih hutang ke kita", "daftar invoice belum lunas".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'customer_name' => ['type' => 'string', 'description' => 'Filter per customer (opsional)'],
                        'overdue_only' => ['type' => 'boolean', 'description' => 'true = hanya yang sudah jatuh tempo'],
                    ],
                ],
            ],
            [
                'name' => 'get_payables',
                'description' => 'Tampilkan daftar hutang ke supplier yang belum lunas. '
                    .'Gunakan untuk: "hutang ke supplier", "kewajiban bayar", '
                    .'"payable outstanding", "tagihan dari supplier yang belum dibayar".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'supplier_name' => ['type' => 'string', 'description' => 'Filter per supplier (opsional)'],
                        'overdue_only' => ['type' => 'boolean', 'description' => 'true = hanya yang sudah jatuh tempo'],
                    ],
                ],
            ],
            [
                'name' => 'get_aging_report',
                'description' => 'Laporan aging piutang atau hutang — dikelompokkan berdasarkan keterlambatan. '
                    .'Gunakan untuk: "laporan aging piutang", "piutang jatuh tempo", '
                    .'"hutang yang sudah lewat jatuh tempo", "aging report".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'type' => ['type' => 'string', 'description' => 'receivable (piutang) atau payable (hutang). Default: receivable'],
                    ],
                ],
            ],
        ];
    }

    // ─── Executors ────────────────────────────────────────────────

    public function recordPayment(array $args): array
    {
        $type = $args['type'] ?? 'receivable';
        $amount = (float) $args['amount'];

        if ($amount <= 0) {
            return ['status' => 'error', 'message' => 'Jumlah pembayaran harus lebih dari 0.'];
        }

        if ($type === 'receivable') {
            return $this->recordReceivablePayment($args, $amount);
        }

        return $this->recordPayablePayment($args, $amount);
    }

    protected function recordReceivablePayment(array $args, float $amount): array
    {
        // Cari customer
        $customer = Customer::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['party_name']}%")
            ->first();

        if (! $customer) {
            return ['status' => 'not_found', 'message' => "Customer '{$args['party_name']}' tidak ditemukan."];
        }

        // Cari invoice — spesifik atau yang paling lama (oldest unpaid first)
        $query = Invoice::where('tenant_id', $this->tenantId)
            ->where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'partial']);

        if (! empty($args['invoice_number'])) {
            $query->where('number', $args['invoice_number']);
        } else {
            $query->orderBy('due_date'); // bayar yang paling lama dulu
        }

        $invoice = $query->first();

        if (! $invoice) {
            return ['status' => 'not_found', 'message' => "Tidak ada tagihan yang belum lunas untuk customer **{$customer->name}**."];
        }

        if ($invoice->status === 'paid') {
            return ['status' => 'error', 'message' => "Invoice **{$invoice->number}** sudah lunas."];
        }

        if ($amount > $invoice->remaining_amount) {
            $selisih = number_format($amount - $invoice->remaining_amount, 0, ',', '.');
            $sisa = number_format($invoice->remaining_amount, 0, ',', '.');

            return [
                'status' => 'error',
                'message' => "Jumlah pembayaran melebihi sisa tagihan. Sisa tagihan: Rp {$sisa}. Kelebihan: Rp {$selisih}.",
            ];
        }

        Payment::create([
            'tenant_id' => $this->tenantId,
            'payable_type' => Invoice::class,
            'payable_id' => $invoice->id,
            'amount' => $amount,
            'payment_method' => $args['payment_method'] ?? 'cash',
            'payment_date' => today(),
            'notes' => $args['notes'] ?? null,
            'user_id' => $this->userId,
        ]);

        $invoice->updatePaymentStatus();
        $invoice->refresh();

        $statusLabel = match ($invoice->status) {
            'paid' => 'LUNAS',
            'partial' => 'Sebagian',
            default => 'Belum Bayar',
        };

        return [
            'status' => 'success',
            'message' => "Pembayaran dari **{$customer->name}** sebesar **Rp ".number_format($amount, 0, ',', '.')."** berhasil dicatat.\n"
                ."Invoice: **{$invoice->number}**\n"
                .'Sisa tagihan: **Rp '.number_format($invoice->remaining_amount, 0, ',', '.')."**\n"
                ."Status: **{$statusLabel}**",
            'data' => [
                'invoice_number' => $invoice->number,
                'paid' => $amount,
                'remaining' => $invoice->remaining_amount,
                'status' => $invoice->status,
            ],
        ];
    }

    protected function recordPayablePayment(array $args, float $amount): array
    {
        $supplier = Supplier::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['party_name']}%")
            ->first();

        if (! $supplier) {
            return ['status' => 'not_found', 'message' => "Supplier '{$args['party_name']}' tidak ditemukan."];
        }

        $query = Payable::where('tenant_id', $this->tenantId)
            ->where('supplier_id', $supplier->id)
            ->whereIn('status', ['unpaid', 'partial']);

        if (! empty($args['invoice_number'])) {
            $query->where('number', $args['invoice_number']);
        } else {
            $query->orderBy('due_date');
        }

        $payable = $query->first();

        if (! $payable) {
            return ['status' => 'not_found', 'message' => "Tidak ada hutang yang belum lunas ke supplier **{$supplier->name}**."];
        }

        if ($payable->status === 'paid') {
            return ['status' => 'error', 'message' => "Hutang **{$payable->number}** sudah lunas."];
        }

        if ($amount > $payable->remaining_amount) {
            $selisih = number_format($amount - $payable->remaining_amount, 0, ',', '.');
            $sisa = number_format($payable->remaining_amount, 0, ',', '.');

            return [
                'status' => 'error',
                'message' => "Jumlah pembayaran melebihi sisa hutang. Sisa hutang: Rp {$sisa}. Kelebihan: Rp {$selisih}.",
            ];
        }

        Payment::create([
            'tenant_id' => $this->tenantId,
            'payable_type' => Payable::class,
            'payable_id' => $payable->id,
            'amount' => $amount,
            'payment_method' => $args['payment_method'] ?? 'cash',
            'payment_date' => today(),
            'notes' => $args['notes'] ?? null,
            'user_id' => $this->userId,
        ]);

        $payable->updatePaymentStatus();
        $payable->refresh();

        $statusLabel = match ($payable->status) {
            'paid' => 'LUNAS',
            'partial' => 'Sebagian',
            default => 'Belum Bayar',
        };

        return [
            'status' => 'success',
            'message' => "Pembayaran ke **{$supplier->name}** sebesar **Rp ".number_format($amount, 0, ',', '.')."** berhasil dicatat.\n"
                ."Hutang: **{$payable->number}**\n"
                .'Sisa hutang: **Rp '.number_format($payable->remaining_amount, 0, ',', '.')."**\n"
                ."Status: **{$statusLabel}**",
            'data' => [
                'payable_number' => $payable->number,
                'paid' => $amount,
                'remaining' => $payable->remaining_amount,
                'status' => $payable->status,
            ],
        ];
    }

    public function getReceivables(array $args): array
    {
        $query = Invoice::where('tenant_id', $this->tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->with('customer')
            ->orderBy('due_date');

        if (! empty($args['customer_name'])) {
            $query->whereHas('customer', fn ($q) => $q->where('name', 'like', "%{$args['customer_name']}%")
            );
        }

        if (! empty($args['overdue_only'])) {
            $query->where('due_date', '<', today());
        }

        $invoices = $query->get();

        if ($invoices->isEmpty()) {
            return ['status' => 'success', 'message' => 'Tidak ada piutang yang outstanding.'];
        }

        $totalOutstanding = $invoices->sum('remaining_amount');

        return [
            'status' => 'success',
            'total_outstanding' => 'Rp '.number_format($totalOutstanding, 0, ',', '.'),
            'count' => $invoices->count(),
            'data' => $invoices->map(fn ($inv) => [
                'nomor' => $inv->number,
                'customer' => $inv->customer->name,
                'total' => 'Rp '.number_format($inv->total_amount, 0, ',', '.'),
                'sudah_bayar' => 'Rp '.number_format($inv->paid_amount, 0, ',', '.'),
                'sisa' => 'Rp '.number_format($inv->remaining_amount, 0, ',', '.'),
                'jatuh_tempo' => $inv->due_date->format('d M Y'),
                'status' => $inv->status,
                'terlambat' => $inv->daysOverdue() > 0 ? $inv->daysOverdue().' hari' : 'Belum jatuh tempo',
            ])->toArray(),
        ];
    }

    public function getPayables(array $args): array
    {
        $query = Payable::where('tenant_id', $this->tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->with('supplier')
            ->orderBy('due_date');

        if (! empty($args['supplier_name'])) {
            $query->whereHas('supplier', fn ($q) => $q->where('name', 'like', "%{$args['supplier_name']}%")
            );
        }

        if (! empty($args['overdue_only'])) {
            $query->where('due_date', '<', today());
        }

        $payables = $query->get();

        if ($payables->isEmpty()) {
            return ['status' => 'success', 'message' => 'Tidak ada hutang yang outstanding.'];
        }

        $totalOutstanding = $payables->sum('remaining_amount');

        return [
            'status' => 'success',
            'total_outstanding' => 'Rp '.number_format($totalOutstanding, 0, ',', '.'),
            'count' => $payables->count(),
            'data' => $payables->map(fn ($p) => [
                'nomor' => $p->number,
                'supplier' => $p->supplier->name,
                'total' => 'Rp '.number_format($p->total_amount, 0, ',', '.'),
                'sudah_bayar' => 'Rp '.number_format($p->paid_amount, 0, ',', '.'),
                'sisa' => 'Rp '.number_format($p->remaining_amount, 0, ',', '.'),
                'jatuh_tempo' => $p->due_date->format('d M Y'),
                'status' => $p->status,
                'terlambat' => $p->daysOverdue() > 0 ? $p->daysOverdue().' hari' : 'Belum jatuh tempo',
            ])->toArray(),
        ];
    }

    public function getAgingReport(array $args): array
    {
        $type = $args['type'] ?? 'receivable';

        if ($type === 'receivable') {
            $records = Invoice::where('tenant_id', $this->tenantId)
                ->whereIn('status', ['unpaid', 'partial'])
                ->with('customer')
                ->get();
        } else {
            $records = Payable::where('tenant_id', $this->tenantId)
                ->whereIn('status', ['unpaid', 'partial'])
                ->with('supplier')
                ->get();
        }

        if ($records->isEmpty()) {
            $label = $type === 'receivable' ? 'piutang' : 'hutang';

            return ['status' => 'success', 'message' => "Tidak ada {$label} yang outstanding."];
        }

        // Kelompokkan ke bucket aging
        $buckets = [
            'current' => ['label' => 'Belum Jatuh Tempo', 'items' => [], 'total' => 0],
            '1_30' => ['label' => '1 - 30 Hari',       'items' => [], 'total' => 0],
            '31_60' => ['label' => '31 - 60 Hari',      'items' => [], 'total' => 0],
            '61_90' => ['label' => '61 - 90 Hari',      'items' => [], 'total' => 0],
            'over_90' => ['label' => '> 90 Hari',         'items' => [], 'total' => 0],
        ];

        foreach ($records as $record) {
            $days = $record->daysOverdue();
            $party = $type === 'receivable' ? $record->customer->name : $record->supplier->name;
            $number = $record->number;
            $remaining = $record->remaining_amount;

            $item = [
                'nomor' => $number,
                'pihak' => $party,
                'sisa' => 'Rp '.number_format($remaining, 0, ',', '.'),
                'jatuh_tempo' => $record->due_date->format('d M Y'),
                'hari' => $days > 0 ? "{$days} hari" : 'Belum jatuh tempo',
            ];

            $bucket = match (true) {
                $days <= 0 => 'current',
                $days <= 30 => '1_30',
                $days <= 60 => '31_60',
                $days <= 90 => '61_90',
                default => 'over_90',
            };

            $buckets[$bucket]['items'][] = $item;
            $buckets[$bucket]['total'] += $remaining;
        }

        $grandTotal = $records->sum('remaining_amount');
        $label = $type === 'receivable' ? 'Piutang' : 'Hutang';

        $report = [];
        foreach ($buckets as $key => $bucket) {
            if (empty($bucket['items'])) {
                continue;
            }
            $report[] = [
                'kategori' => $bucket['label'],
                'jumlah' => count($bucket['items']),
                'total' => 'Rp '.number_format($bucket['total'], 0, ',', '.'),
                'items' => $bucket['items'],
            ];
        }

        return [
            'status' => 'success',
            'type' => $label,
            'grand_total' => 'Rp '.number_format($grandTotal, 0, ',', '.'),
            'data' => $report,
        ];
    }

    // ─── Helper: Buat Invoice dari Sales Order ────────────────────

    /**
     * Dipanggil dari SalesTools saat SO dibuat dengan payment_type = credit.
     */
    public static function createInvoiceFromOrder(
        int $tenantId,
        int $salesOrderId,
        int $customerId,
        float $total,
        string $dueDate
    ): Invoice {
        return Invoice::create([
            'tenant_id' => $tenantId,
            'sales_order_id' => $salesOrderId,
            'customer_id' => $customerId,
            'number' => 'INV-'.strtoupper(Str::random(8)),
            'total_amount' => $total,
            'paid_amount' => 0,
            'remaining_amount' => $total,
            'status' => 'unpaid',
            'due_date' => $dueDate,
        ]);
    }

    /**
     * Dipanggil dari PurchasingTools saat PO dibuat dengan payment_type = credit.
     */
    public static function createPayableFromOrder(
        int $tenantId,
        int $purchaseOrderId,
        int $supplierId,
        float $total,
        string $dueDate
    ): Payable {
        return Payable::create([
            'tenant_id' => $tenantId,
            'purchase_order_id' => $purchaseOrderId,
            'supplier_id' => $supplierId,
            'number' => 'PAY-'.strtoupper(Str::random(8)),
            'total_amount' => $total,
            'paid_amount' => 0,
            'remaining_amount' => $total,
            'status' => 'unpaid',
            'due_date' => $dueDate,
        ]);
    }
}
