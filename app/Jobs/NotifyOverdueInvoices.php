<?php

namespace App\Jobs;

use App\Models\ErpNotification;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyOverdueInvoices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function handle(): void
    {
        // Invoice yang sudah lewat due_date dan belum lunas
        $overdueInvoices = Invoice::whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', today())
            ->with(['tenant', 'customer'])
            ->get();

        if ($overdueInvoices->isEmpty()) return;

        $grouped = $overdueInvoices->groupBy('tenant_id');
        $totalNotified = 0;

        foreach ($grouped as $tenantId => $invoices) {
            $tenant = $invoices->first()->tenant;
            if (!$tenant || !$tenant->canAccess()) continue;

            // Hindari spam — cek apakah sudah ada notifikasi overdue hari ini untuk tenant ini
            $alreadyNotified = ErpNotification::where('tenant_id', $tenantId)
                ->where('type', 'invoice_overdue_batch')
                ->whereDate('created_at', today())
                ->exists();

            if ($alreadyNotified) continue;

            $admin = User::where('tenant_id', $tenantId)
                ->where('role', 'admin')
                ->first();

            if (!$admin) continue;

            $count        = $invoices->count();
            $totalAmount  = $invoices->sum('remaining_amount');
            $oldest       = $invoices->sortBy('due_date')->first();
            $maxDaysLate  = $oldest ? $oldest->daysOverdue() : 0;

            ErpNotification::create([
                'tenant_id' => $tenantId,
                'user_id'   => $admin->id,
                'type'      => 'invoice_overdue_batch',
                'title'     => '⚠️ Invoice Jatuh Tempo',
                'body'      => "{$count} invoice senilai Rp " . number_format($totalAmount, 0, ',', '.') .
                               " belum dibayar. Terlambat hingga {$maxDaysLate} hari.",
                'data'      => [
                    'count'        => $count,
                    'total_amount' => $totalAmount,
                    'max_days_late'=> $maxDaysLate,
                ],
            ]);

            // Notifikasi individual untuk invoice yang terlambat > 7 hari
            foreach ($invoices->filter(fn($inv) => $inv->daysOverdue() > 7) as $invoice) {
                $notifKey = "invoice_overdue_{$invoice->id}";

                $alreadySent = ErpNotification::where('tenant_id', $tenantId)
                    ->where('type', $notifKey)
                    ->whereDate('created_at', today())
                    ->exists();

                if ($alreadySent) continue;

                ErpNotification::create([
                    'tenant_id' => $tenantId,
                    'user_id'   => $admin->id,
                    'type'      => $notifKey,
                    'title'     => '🔴 Invoice Sangat Terlambat',
                    'body'      => "Invoice #{$invoice->number} ke {$invoice->customer?->name} " .
                                   "terlambat {$invoice->daysOverdue()} hari. " .
                                   "Sisa: Rp " . number_format($invoice->remaining_amount, 0, ',', '.'),
                    'data'      => ['invoice_id' => $invoice->id, 'days_overdue' => $invoice->daysOverdue()],
                ]);
            }

            $totalNotified += $count;
        }

        Log::info("NotifyOverdueInvoices: tenants={$grouped->count()} invoices={$totalNotified}");
    }
}
