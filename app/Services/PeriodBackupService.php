<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\PayrollRun;
use App\Models\PeriodBackup;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * PeriodBackupService — Export tenant data for a date range as JSON.
 *
 * Exports: sales orders, invoices, purchase orders, journal entries,
 * transactions, stock movements, payroll, customers, products.
 *
 * File stored in storage/app/backups/{tenant_id}/
 */
class PeriodBackupService
{
    /**
     * Generate backup file for a PeriodBackup record.
     */
    public function generate(PeriodBackup $backup): PeriodBackup
    {
        $backup->update(['status' => 'processing']);

        try {
            $tid = $backup->tenant_id;
            $from = $backup->period_start->toDateString();
            $to = $backup->period_end->toDateString();

            $data = [
                'meta' => [
                    'tenant_id' => $tid,
                    'label' => $backup->label,
                    'type' => $backup->type,
                    'period_start' => $from,
                    'period_end' => $to,
                    'exported_at' => now()->toIso8601String(),
                    'version' => '1.0',
                ],
                'sales_orders' => $this->exportSalesOrders($tid, $from, $to),
                'invoices' => $this->exportInvoices($tid, $from, $to),
                'purchase_orders' => $this->exportPurchaseOrders($tid, $from, $to),
                'journal_entries' => $this->exportJournalEntries($tid, $from, $to),
                'transactions' => $this->exportTransactions($tid, $from, $to),
                'stock_movements' => $this->exportStockMovements($tid, $from, $to),
                'payroll_runs' => $this->exportPayroll($tid, $from, $to),
                'customers' => $this->exportCustomers($tid),
                'products' => $this->exportProducts($tid),
            ];

            $summary = collect($data)
                ->except('meta')
                ->map(fn ($items) => count($items))
                ->toArray();

            // Write JSON file
            $path = "backups/{$tid}/backup-{$backup->id}-".now()->format('Ymd-His').'.json';
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            Storage::put($path, $json);

            $backup->update([
                'status' => 'completed',
                'file_path' => $path,
                'file_size' => strlen($json),
                'summary' => $summary,
                'completed_at' => now(),
            ]);

            Log::info("PeriodBackup #{$backup->id} completed: ".json_encode($summary));

        } catch (\Throwable $e) {
            $backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            Log::error("PeriodBackup #{$backup->id} failed: ".$e->getMessage());
            throw $e;
        }

        return $backup->fresh();
    }

    // ─── Exporters ────────────────────────────────────────────────

    private function exportSalesOrders(int $tid, string $from, string $to): array
    {
        return SalesOrder::where('tenant_id', $tid)
            ->whereBetween('date', [$from, $to])
            ->with('items')
            ->get()
            ->map(fn ($so) => [
                'number' => $so->number,
                'date' => $so->date->toDateString(),
                'customer_id' => $so->customer_id,
                'status' => $so->status,
                'subtotal' => (float) $so->subtotal,
                'discount' => (float) $so->discount,
                'tax_amount' => (float) $so->tax_amount,
                'total' => (float) $so->total,
                'items' => $so->items->map(fn ($i) => [
                    'product_id' => $i->product_id,
                    'quantity' => (float) $i->quantity,
                    'price' => (float) $i->price,
                    'total' => (float) $i->total,
                ])->toArray(),
            ])->toArray();
    }

    private function exportInvoices(int $tid, string $from, string $to): array
    {
        return Invoice::where('tenant_id', $tid)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->get()
            ->map(fn ($inv) => [
                'number' => $inv->number,
                'customer_id' => $inv->customer_id,
                'total_amount' => (float) $inv->total_amount,
                'paid_amount' => (float) $inv->paid_amount,
                'remaining_amount' => (float) $inv->remaining_amount,
                'status' => $inv->status,
                'due_date' => $inv->due_date?->toDateString(),
            ])->toArray();
    }

    private function exportPurchaseOrders(int $tid, string $from, string $to): array
    {
        return PurchaseOrder::where('tenant_id', $tid)
            ->whereBetween('date', [$from, $to])
            ->with('items')
            ->get()
            ->map(fn ($po) => [
                'number' => $po->number,
                'date' => $po->date->toDateString(),
                'supplier_id' => $po->supplier_id,
                'status' => $po->status,
                'total' => (float) $po->total,
                'items' => $po->items->map(fn ($i) => [
                    'product_id' => $i->product_id,
                    'quantity_ordered' => (float) $i->quantity_ordered,
                    'quantity_received' => (float) $i->quantity_received,
                    'price' => (float) $i->price,
                ])->toArray(),
            ])->toArray();
    }

    private function exportJournalEntries(int $tid, string $from, string $to): array
    {
        return JournalEntry::where('tenant_id', $tid)
            ->whereBetween('date', [$from, $to])
            ->with('lines.account')
            ->get()
            ->map(fn ($je) => [
                'number' => $je->number,
                'date' => $je->date->toDateString(),
                'description' => $je->description,
                'reference' => $je->reference,
                'reference_type' => $je->reference_type,
                'status' => $je->status,
                'lines' => $je->lines->map(fn ($l) => [
                    'account_code' => $l->account?->code,
                    'account_name' => $l->account?->name,
                    'debit' => (float) $l->debit,
                    'credit' => (float) $l->credit,
                    'description' => $l->description,
                ])->toArray(),
            ])->toArray();
    }

    private function exportTransactions(int $tid, string $from, string $to): array
    {
        return Transaction::where('tenant_id', $tid)
            ->whereBetween('date', [$from, $to])
            ->get()
            ->map(fn ($tx) => [
                'number' => $tx->number,
                'type' => $tx->type,
                'date' => $tx->date->toDateString(),
                'amount' => (float) $tx->amount,
                'description' => $tx->description,
            ])->toArray();
    }

    private function exportStockMovements(int $tid, string $from, string $to): array
    {
        return StockMovement::where('tenant_id', $tid)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->get()
            ->map(fn ($sm) => [
                'product_id' => $sm->product_id,
                'warehouse_id' => $sm->warehouse_id,
                'type' => $sm->type,
                'quantity' => (float) $sm->quantity,
                'reference' => $sm->reference,
                'created_at' => $sm->created_at->toIso8601String(),
            ])->toArray();
    }

    private function exportPayroll(int $tid, string $from, string $to): array
    {
        return PayrollRun::where('tenant_id', $tid)
            ->where('period', '>=', substr($from, 0, 7))
            ->where('period', '<=', substr($to, 0, 7))
            ->with('items.employee')
            ->get()
            ->map(fn ($run) => [
                'period' => $run->period,
                'status' => $run->status,
                'total_gross' => (float) $run->total_gross,
                'total_net' => (float) $run->total_net,
                'items' => $run->items->map(fn ($i) => [
                    'employee_name' => $i->employee?->name,
                    'base_salary' => (float) $i->base_salary,
                    'net_salary' => (float) $i->net_salary,
                ])->toArray(),
            ])->toArray();
    }

    private function exportCustomers(int $tid): array
    {
        return Customer::where('tenant_id', $tid)
            ->get(['id', 'name', 'email', 'phone', 'company', 'address', 'is_active'])
            ->toArray();
    }

    private function exportProducts(int $tid): array
    {
        return Product::where('tenant_id', $tid)
            ->get(['id', 'name', 'sku', 'category', 'unit', 'price_buy', 'price_sell', 'is_active'])
            ->toArray();
    }
}
