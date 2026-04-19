<?php

namespace App\Services;

use App\Models\LoyaltyPoint;
use App\Models\LoyaltyProgram;
use App\Models\LoyaltyTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoyaltyService
{
    /**
     * Dapatkan program loyalty aktif untuk tenant.
     */
    public function getActiveProgram(int $tenantId): ?LoyaltyProgram
    {
        return LoyaltyProgram::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Dapatkan saldo poin pelanggan untuk program tertentu.
     */
    public function getBalance(int $tenantId, int $customerId): int
    {
        $program = $this->getActiveProgram($tenantId);
        if (!$program) return 0;

        $loyaltyPoint = LoyaltyPoint::where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->where('program_id', $program->id)
            ->first();

        return $loyaltyPoint ? (int) $loyaltyPoint->total_points : 0;
    }

    /**
     * Hitung poin yang akan diperoleh dari transaksi.
     */
    public function calculateEarnPoints(int $tenantId, float $amount): int
    {
        $program = $this->getActiveProgram($tenantId);
        if (!$program) return 0;

        return $program->calculatePoints($amount);
    }

    /**
     * Hitung nilai diskon dari poin yang akan ditukarkan.
     * Mengembalikan nilai dalam Rupiah.
     */
    public function calculateRedeemValue(int $tenantId, int $points): float
    {
        $program = $this->getActiveProgram($tenantId);
        if (!$program) return 0.0;

        return $points * $program->idr_per_point;
    }

    /**
     * Berikan poin ke pelanggan setelah transaksi berhasil.
     */
    public function awardPoints(int $tenantId, int $customerId, float $transactionAmount, string $reference): ?LoyaltyTransaction
    {
        $program = $this->getActiveProgram($tenantId);
        if (!$program) return null;

        $points = $program->calculatePoints($transactionAmount);
        if ($points <= 0) return null;

        return DB::transaction(function () use ($tenantId, $customerId, $program, $points, $transactionAmount, $reference) {
            // Upsert loyalty_points record
            $loyaltyPoint = LoyaltyPoint::firstOrCreate(
                ['tenant_id' => $tenantId, 'customer_id' => $customerId, 'program_id' => $program->id],
                ['total_points' => 0, 'lifetime_points' => 0, 'tier' => 'Bronze']
            );

            $newTotal = $loyaltyPoint->total_points + $points;
            $newLifetime = $loyaltyPoint->lifetime_points + $points;

            $loyaltyPoint->update([
                'total_points'    => $newTotal,
                'lifetime_points' => $newLifetime,
            ]);

            // Hitung expires_at
            $expiresAt = $program->expiry_days > 0
                ? now()->addDays($program->expiry_days)
                : null;

            // Catat transaksi earn
            $txn = LoyaltyTransaction::create([
                'tenant_id'          => $tenantId,
                'customer_id'        => $customerId,
                'program_id'         => $program->id,
                'type'               => 'earn',
                'points'             => $points,
                'balance_after'      => $newTotal,
                'transaction_amount' => $transactionAmount,
                'reference'          => $reference,
                'notes'              => "Poin dari transaksi POS #{$reference}",
                'expires_at'         => $expiresAt,
            ]);

            Log::info("LoyaltyService: awarded {$points} points to customer {$customerId} for order {$reference}");

            return $txn;
        });
    }

    /**
     * Tukarkan poin pelanggan sebagai diskon.
     * Mengembalikan nilai diskon dalam Rupiah, atau 0 jika gagal.
     */
    public function redeemPoints(int $tenantId, int $customerId, int $points, float $transactionTotal, string $reference): float
    {
        $program = $this->getActiveProgram($tenantId);
        if (!$program) return 0.0;

        if ($points < $program->min_redeem_points) {
            throw new \InvalidArgumentException(
                "Minimum penukaran poin adalah {$program->min_redeem_points} poin."
            );
        }

        $loyaltyPoint = LoyaltyPoint::where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->where('program_id', $program->id)
            ->first();

        if (!$loyaltyPoint || $loyaltyPoint->total_points < $points) {
            throw new \InvalidArgumentException(
                "Poin tidak mencukupi. Saldo: " . ($loyaltyPoint?->total_points ?? 0) . " poin."
            );
        }

        $discountValue = $this->calculateRedeemValue($tenantId, $points);

        if ($discountValue > $transactionTotal) {
            throw new \InvalidArgumentException(
                "Nilai penukaran poin (Rp " . number_format($discountValue, 0, ',', '.') . ") melebihi total transaksi."
            );
        }

        DB::transaction(function () use ($tenantId, $customerId, $program, $loyaltyPoint, $points, $discountValue, $transactionTotal, $reference) {
            $newTotal = $loyaltyPoint->total_points - $points;
            $loyaltyPoint->update(['total_points' => $newTotal]);

            LoyaltyTransaction::create([
                'tenant_id'          => $tenantId,
                'customer_id'        => $customerId,
                'program_id'         => $program->id,
                'type'               => 'redeem',
                'points'             => -$points,
                'balance_after'      => $newTotal,
                'transaction_amount' => $transactionTotal,
                'reference'          => $reference,
                'notes'              => "Penukaran {$points} poin sebagai diskon Rp " . number_format($discountValue, 0, ',', '.'),
            ]);

            Log::info("LoyaltyService: redeemed {$points} points for customer {$customerId}, discount Rp {$discountValue}");
        });

        return $discountValue;
    }

    /**
     * Validasi penukaran poin sebelum checkout.
     * Mengembalikan array ['valid' => bool, 'message' => string, 'discount' => float]
     */
    public function validateRedeem(int $tenantId, int $customerId, int $points, float $transactionTotal): array
    {
        $program = $this->getActiveProgram($tenantId);
        if (!$program) {
            return ['valid' => false, 'message' => 'Program loyalty tidak aktif.', 'discount' => 0];
        }

        if ($points < $program->min_redeem_points) {
            return [
                'valid'    => false,
                'message'  => "Minimum penukaran adalah {$program->min_redeem_points} poin.",
                'discount' => 0,
            ];
        }

        $balance = $this->getBalance($tenantId, $customerId);
        if ($balance < $points) {
            return [
                'valid'    => false,
                'message'  => "Poin tidak mencukupi. Saldo: {$balance} poin.",
                'discount' => 0,
            ];
        }

        $discountValue = $this->calculateRedeemValue($tenantId, $points);
        if ($discountValue > $transactionTotal) {
            return [
                'valid'    => false,
                'message'  => "Nilai penukaran melebihi total transaksi.",
                'discount' => 0,
            ];
        }

        return ['valid' => true, 'message' => 'OK', 'discount' => $discountValue];
    }
}
