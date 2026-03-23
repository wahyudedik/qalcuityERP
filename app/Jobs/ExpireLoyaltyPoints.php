<?php

namespace App\Jobs;

use App\Models\ErpNotification;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyTransaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireLoyaltyPoints implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function handle(): void
    {
        // Cari semua transaksi earn yang sudah expired dan belum di-expire
        $expiredTxns = LoyaltyTransaction::where('type', 'earn')
            ->where('expires_at', '<=', now())
            ->whereNotNull('expires_at')
            ->whereNotExists(function ($q) {
                // Belum ada transaksi expire yang mereferensikan txn ini
                $q->select(DB::raw(1))
                  ->from('loyalty_transactions as lt2')
                  ->where('lt2.type', 'expire')
                  ->whereRaw("lt2.reference = CONCAT('expire:', loyalty_transactions.id)");
            })
            ->with('customer')
            ->get();

        if ($expiredTxns->isEmpty()) return;

        $grouped = $expiredTxns->groupBy('tenant_id');
        $totalExpired = 0;

        foreach ($grouped as $tenantId => $txns) {
            foreach ($txns as $txn) {
                DB::transaction(function () use ($txn, $tenantId, &$totalExpired) {
                    // Catat transaksi expire
                    LoyaltyTransaction::create([
                        'tenant_id'          => $tenantId,
                        'customer_id'        => $txn->customer_id,
                        'program_id'         => $txn->program_id,
                        'type'               => 'expire',
                        'points'             => -$txn->points,
                        'transaction_amount' => 0,
                        'reference'          => 'expire:' . $txn->id,
                        'notes'              => 'Poin kadaluarsa otomatis',
                    ]);

                    // Kurangi total_points di LoyaltyPoint
                    $loyaltyPoint = LoyaltyPoint::where('tenant_id', $tenantId)
                        ->where('customer_id', $txn->customer_id)
                        ->where('program_id', $txn->program_id)
                        ->first();

                    if ($loyaltyPoint) {
                        $newTotal = max(0, $loyaltyPoint->total_points - $txn->points);
                        $loyaltyPoint->update(['total_points' => $newTotal]);
                    }

                    $totalExpired++;
                });
            }

            // Notifikasi admin per tenant
            $admin = User::where('tenant_id', $tenantId)->where('role', 'admin')->first();
            if ($admin && $txns->count() > 0) {
                $totalPoints = $txns->sum('points');
                ErpNotification::create([
                    'tenant_id' => $tenantId,
                    'user_id'   => $admin->id,
                    'type'      => 'loyalty_expired',
                    'title'     => '⭐ Poin Loyalitas Kadaluarsa',
                    'body'      => "{$txns->count()} transaksi poin ({$totalPoints} poin total) telah kadaluarsa hari ini.",
                    'data'      => ['count' => $txns->count(), 'total_points' => $totalPoints],
                ]);
            }
        }

        Log::info("ExpireLoyaltyPoints: expired={$totalExpired} transactions");
    }
}
