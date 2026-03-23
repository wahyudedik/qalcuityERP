<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\PeriodBackup;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Transaction;
use Illuminate\Support\Facades\Storage;

/**
 * PeriodBackupService
 *
 * Membuat snapshot data transaksional per periode ke file ZIP (JSON).
 * Data yang di-backup: jurnal, invoice, sales order, PO, transaksi keuangan.
 * Master data (produk, pelanggan, dll) tidak di-backup karena berubah terus.
 */
class PeriodBackupService
{
    public function generate(PeriodBackup $backup): void
    {
        $backup->update(['status' => 'processing']);

        try {
            $tenantId = $backup->tenant_id;
            $from     = $backup->period_start->toDateString();
            $to       = $backup->period_end->toDateString();

            $data = [
                'meta' => [
                    'tenant_id'    => $tenantId,
                    'label'        => $backup->label,
                    'period_start' => $from,
                    'period_end'   => $to,
                    'type'         => $backup->type,
                    'generated_at' => now()->toIso8601String(),
                    'app_version'  => config('app.version', '1.0'),
                ],
                'journal_entries' => $this->exportJournals($tenantId, $from, $to),
                'invoices'        => $this->exportInvoices($tenantId, $from, $to),
                'sales_orders'    => $this->exportSalesOrders($tenantId, $from, $to),
                'purchase_orders' => $this->exportPurchaseOrders($tenantId, $from, $to),
                'transactions'    => $this->exportTransactions($tenantId, $from, $to),
            ];

            $summary = [
                'journal_entries' => count($data['journal_entries']),
                'invoices'        => count($data['invoices']),
                'sales_orders'    => count($data['sales_orders']),
                'purchase_orders' => count($data['purchase_orders']),
                'transactions'    => count($data['transactions']),
            ];

            // Simpan sebagai JSON (gzip compressed)
            $json     = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $filename = "backups/tenant_{$tenantId}/" . $this->buildFilename($backup);
            Storage::put($filename, $json);

            $backup->update([
                'status'       => 'completed',
                'file_path'    => $filename,
                'file_size'    => strlen($json),
                'summary'      => $summary,
                'completed_at' => now(),
            ]);

        } catch (\Throwable $e) {
            $backup->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function buildFilename(PeriodBackup $backup): string
    {
        $slug = \Str::slug($backup->label);
        return "backup_{$backup->type}_{$slug}_{$backup->id}.json";
    }

    private function exportJournals(int $tenantId, string $from, string $to): array
    {
        return JournalEntry::where('tenant_id', $tenantId)
            ->whereBetween('date', [$from, $to])
            ->with('lines')
            ->get()
            ->map(fn($je) => [
                'id'          => $je->id,
                'number'      => $je->number,
                'date'        => $je->date->toDateString(),
                'description' => $je->description,
                'reference'   => $je->reference,
                'status'      => $je->status,
                'currency'    => $je->currency_code,
                'lines'       => $je->lines->map(fn($l) => [
                    'account_id'  => $l->account_id,
                    'debit'       => $l->debit,
                    'credit'      => $l->credit,
                    'description' => $l->description,
                ])->toArray(),
            ])
            ->toArray();
    }

    private function exportInvoices(int $tenantId, string $from, string $to): array
    {
        return Invoice::where('tenant_id', $tenantId)
            ->whereBetween('date', [$from, $to])
            ->with('items')
            ->get()
            ->map(fn($inv) => [
                'id'         => $inv->id,
                'number'     => $inv->number,
                'date'       => $inv->date->toDateString(),
                'due_date'   => $inv->due_date?->toDateString(),
                'customer_id'=> $inv->customer_id,
                'total'      => $inv->total,
                'paid'       => $inv->paid_amount,
                'status'     => $inv->status,
                'items'      => $inv->items->map(fn($i) => [
                    'description' => $i->description,
                    'qty'         => $i->quantity,
                    'price'       => $i->unit_price,
                    'total'       => $i->total,
                ])->toArray(),
            ])
            ->toArray();
    }

    private function exportSalesOrders(int $tenantId, string $from, string $to): array
    {
        return SalesOrder::where('tenant_id', $tenantId)
            ->whereBetween('date', [$from, $to])
            ->with('items')
            ->get()
            ->map(fn($so) => [
                'id'          => $so->id,
                'number'      => $so->number,
                'date'        => $so->date->toDateString(),
                'customer_id' => $so->customer_id,
                'total'       => $so->total,
                'status'      => $so->status,
            ])
            ->toArray();
    }

    private function exportPurchaseOrders(int $tenantId, string $from, string $to): array
    {
        return PurchaseOrder::where('tenant_id', $tenantId)
            ->whereBetween('order_date', [$from, $to])
            ->get()
            ->map(fn($po) => [
                'id'          => $po->id,
                'number'      => $po->po_number,
                'date'        => $po->order_date->toDateString(),
                'supplier_id' => $po->supplier_id,
                'total'       => $po->total_amount,
                'status'      => $po->status,
            ])
            ->toArray();
    }

    private function exportTransactions(int $tenantId, string $from, string $to): array
    {
        return Transaction::where('tenant_id', $tenantId)
            ->whereBetween('date', [$from, $to])
            ->get()
            ->map(fn($t) => [
                'id'          => $t->id,
                'date'        => $t->date->toDateString(),
                'type'        => $t->type,
                'amount'      => $t->amount,
                'description' => $t->description,
                'reference'   => $t->reference,
            ])
            ->toArray();
    }
}
