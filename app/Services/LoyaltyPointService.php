<?php

namespace App\Services;

use App\Models\LoyaltyPoint;
use App\Models\LoyaltyProgram;
use App\Models\LoyaltyTransaction;
use App\Models\LoyaltyTier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * LoyaltyPointService - Race condition-free loyalty points management
 * 
 * BUG-CRM-003 FIX: Atomic operations with pessimistic locking
 * 
 * Problems Fixed:
 * 1. Non-atomic increment (read-modify-write race condition)
 * 2. Non-atomic decrement with balance check race
 * 3. No database transaction for earn/redeem
 * 4. Concurrent requests causing incorrect balance
 */
class LoyaltyPointService
{
    /**
     * BUG-CRM-003 FIX: Atomically earn points with pessimistic locking
     * 
     * @param int $tenantId
     * @param int $customerId
     * @param float $transactionAmount
     * @param int|null $pointsOverride
     * @param string|null $reference
     * @return array
     */
    public function earnPoints(
        int $tenantId,
        int $customerId,
        float $transactionAmount,
        ?int $pointsOverride = null,
        ?string $reference = null
    ): array {
        return DB::transaction(function () use ($tenantId, $customerId, $transactionAmount, $pointsOverride, $reference) {
            $program = LoyaltyProgram::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->lockForUpdate() // Prevent concurrent program changes
                ->firstOrFail();

            $points = $pointsOverride ?? $program->calculatePoints($transactionAmount);

            if ($points <= 0) {
                return [
                    'success' => false,
                    'message' => 'No points earned from this transaction.',
                ];
            }

            // BUG-CRM-003 FIX: Use SELECT FOR UPDATE to lock the row
            $lp = LoyaltyPoint::where('tenant_id', $tenantId)
                ->where('customer_id', $customerId)
                ->where('program_id', $program->id)
                ->lockForUpdate() // Pessimistic lock - blocks other transactions
                ->first();

            // Create if not exists (still inside transaction)
            if (!$lp) {
                $lp = LoyaltyPoint::create([
                    'tenant_id' => $tenantId,
                    'customer_id' => $customerId,
                    'program_id' => $program->id,
                    'total_points' => 0,
                    'lifetime_points' => 0,
                    'tier' => 'Bronze',
                ]);
            }

            // BUG-CRM-003 FIX: Atomic increment using database-level operation
            // This is atomic - no race condition possible
            $affected = DB::table('loyalty_points')
                ->where('id', $lp->id)
                ->increment('total_points', $points);

            if ($affected === 0) {
                throw new \Exception('Failed to update loyalty points');
            }

            // Also increment lifetime_points atomically
            DB::table('loyalty_points')
                ->where('id', $lp->id)
                ->increment('lifetime_points', $points);

            // Refresh model to get updated values
            $lp->refresh();

            // Recalculate tier
            $newTier = LoyaltyTier::where('program_id', $program->id)
                ->where('min_points', '<=', $lp->lifetime_points)
                ->orderByDesc('min_points')
                ->value('name') ?? 'Bronze';

            if ($newTier !== $lp->tier) {
                $lp->update(['tier' => $newTier, 'tier_updated_at' => now()]);
            }

            // Create transaction record
            $transaction = LoyaltyTransaction::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'program_id' => $program->id,
                'type' => 'earn',
                'points' => $points,
                'transaction_amount' => $transactionAmount,
                'reference' => $reference,
                'balance_after' => $lp->total_points,
            ]);

            Log::info('Loyalty: Points earned (atomic)', [
                'customer_id' => $customerId,
                'points' => $points,
                'new_balance' => $lp->total_points,
                'transaction_id' => $transaction->id,
            ]);

            return [
                'success' => true,
                'points_earned' => $points,
                'new_balance' => $lp->total_points,
                'tier' => $lp->tier,
                'transaction' => $transaction,
            ];
        });
    }

    /**
     * BUG-CRM-003 FIX: Atomically redeem points with balance check inside lock
     * 
     * @param int $tenantId
     * @param int $customerId
     * @param int $pointsToRedeem
     * @param string|null $reference
     * @return array
     */
    public function redeemPoints(
        int $tenantId,
        int $customerId,
        int $pointsToRedeem,
        ?string $reference = null
    ): array {
        return DB::transaction(function () use ($tenantId, $customerId, $pointsToRedeem, $reference) {
            $program = LoyaltyProgram::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();

            // BUG-CRM-003 FIX: Lock the loyalty points row
            $lp = LoyaltyPoint::where('tenant_id', $tenantId)
                ->where('customer_id', $customerId)
                ->where('program_id', $program->id)
                ->lockForUpdate() // Pessimistic lock - prevents concurrent redeem
                ->firstOrFail();

            // BUG-CRM-003 FIX: Balance check INSIDE the lock (race condition fix)
            if ($lp->total_points < $pointsToRedeem) {
                return [
                    'success' => false,
                    'message' => "Poin tidak mencukupi. Balance: {$lp->total_points}, Required: {$pointsToRedeem}",
                    'current_balance' => $lp->total_points,
                ];
            }

            if ($pointsToRedeem < $program->min_redeem_points) {
                return [
                    'success' => false,
                    'message' => "Minimum redeem {$program->min_redeem_points} poin.",
                ];
            }

            // BUG-CRM-003 FIX: Atomic decrement
            $affected = DB::table('loyalty_points')
                ->where('id', $lp->id)
                ->where('total_points', '>=', $pointsToRedeem) // Double-check in WHERE clause
                ->decrement('total_points', $pointsToRedeem);

            if ($affected === 0) {
                // This can happen if concurrent request redeemed points
                return [
                    'success' => false,
                    'message' => 'Failed to redeem points. Balance may have changed.',
                    'current_balance' => $lp->fresh()->total_points,
                ];
            }

            // Refresh to get new balance
            $lp->refresh();

            // Create transaction record
            $transaction = LoyaltyTransaction::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'program_id' => $program->id,
                'type' => 'redeem',
                'points' => -$pointsToRedeem,
                'reference' => $reference,
                'balance_after' => $lp->total_points,
            ]);

            $value = $pointsToRedeem * $program->idr_per_point;

            Log::info('Loyalty: Points redeemed (atomic)', [
                'customer_id' => $customerId,
                'points' => $pointsToRedeem,
                'new_balance' => $lp->total_points,
                'value' => $value,
                'transaction_id' => $transaction->id,
            ]);

            return [
                'success' => true,
                'points_redeemed' => $pointsToRedeem,
                'value' => $value,
                'new_balance' => $lp->total_points,
                'transaction' => $transaction,
            ];
        });
    }

    /**
     * BUG-CRM-003 FIX: Get accurate balance with transaction history
     * 
     * @param int $tenantId
     * @param int $customerId
     * @param int $programId
     * @return array
     */
    public function getBalance(int $tenantId, int $customerId, int $programId): array
    {
        $lp = LoyaltyPoint::where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->where('program_id', $programId)
            ->first();

        if (!$lp) {
            return [
                'has_account' => false,
                'balance' => 0,
                'lifetime_points' => 0,
                'tier' => 'Bronze',
            ];
        }

        // Verify balance by summing transactions
        $calculatedBalance = LoyaltyTransaction::where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->where('program_id', $programId)
            ->sum('points');

        $balanceMismatch = abs($calculatedBalance - $lp->total_points) > 0.01;

        if ($balanceMismatch) {
            Log::warning('Loyalty: Balance mismatch detected', [
                'customer_id' => $customerId,
                'stored_balance' => $lp->total_points,
                'calculated_balance' => $calculatedBalance,
                'difference' => $calculatedBalance - $lp->total_points,
            ]);
        }

        return [
            'has_account' => true,
            'balance' => $lp->total_points,
            'lifetime_points' => $lp->lifetime_points,
            'tier' => $lp->tier,
            'tier_updated_at' => $lp->tier_updated_at,
            'calculated_balance' => $calculatedBalance,
            'balance_verified' => !$balanceMismatch,
        ];
    }

    /**
     * BUG-CRM-003 FIX: Recalculate balance from transactions (repair tool)
     * 
     * @param int $tenantId
     * @param int $customerId
     * @param int $programId
     * @return array
     */
    public function recalculateBalance(int $tenantId, int $customerId, int $programId): array
    {
        return DB::transaction(function () use ($tenantId, $customerId, $programId) {
            $lp = LoyaltyPoint::where('tenant_id', $tenantId)
                ->where('customer_id', $customerId)
                ->where('program_id', $programId)
                ->lockForUpdate()
                ->first();

            if (!$lp) {
                return ['success' => false, 'message' => 'Loyalty account not found'];
            }

            // Calculate correct balance from all transactions
            $correctBalance = LoyaltyTransaction::where('tenant_id', $tenantId)
                ->where('customer_id', $customerId)
                ->where('program_id', $programId)
                ->sum('points');

            $oldBalance = $lp->total_points;
            $difference = $correctBalance - $oldBalance;

            // Update to correct balance
            $lp->update(['total_points' => $correctBalance]);

            Log::info('Loyalty: Balance recalculated', [
                'customer_id' => $customerId,
                'old_balance' => $oldBalance,
                'new_balance' => $correctBalance,
                'difference' => $difference,
            ]);

            return [
                'success' => true,
                'old_balance' => $oldBalance,
                'new_balance' => $correctBalance,
                'difference' => $difference,
            ];
        });
    }

    /**
     * BUG-CRM-003 FIX: Transfer points between customers (atomic)
     * 
     * @param int $tenantId
     * @param int $fromCustomerId
     * @param int $toCustomerId
     * @param int $programId
     * @param int $points
     * @return array
     */
    public function transferPoints(
        int $tenantId,
        int $fromCustomerId,
        int $toCustomerId,
        int $programId,
        int $points
    ): array {
        return DB::transaction(function () use ($tenantId, $fromCustomerId, $toCustomerId, $programId, $points) {
            // Lock both accounts (order by ID to prevent deadlock)
            $lockOrder = [$fromCustomerId, $toCustomerId];
            sort($lockOrder);

            $fromLp = LoyaltyPoint::where('tenant_id', $tenantId)
                ->where('customer_id', $lockOrder[0])
                ->where('program_id', $programId)
                ->lockForUpdate()
                ->firstOrFail();

            $toLp = $fromCustomerId === $toCustomerId
                ? $fromLp
                : LoyaltyPoint::where('tenant_id', $tenantId)
                    ->where('customer_id', $lockOrder[1])
                    ->where('program_id', $programId)
                    ->lockForUpdate()
                    ->firstOrFail();

            // Check balance
            if ($fromLp->total_points < $points) {
                return [
                    'success' => false,
                    'message' => 'Insufficient points for transfer',
                ];
            }

            // Atomic decrement from source
            DB::table('loyalty_points')
                ->where('id', $fromLp->id)
                ->decrement('total_points', $points);

            // Atomic increment to destination
            DB::table('loyalty_points')
                ->where('id', $toLp->id)
                ->increment('total_points', $points);

            $fromLp->refresh();
            $toLp->refresh();

            // Create transaction records
            LoyaltyTransaction::create([
                'tenant_id' => $tenantId,
                'customer_id' => $fromCustomerId,
                'program_id' => $programId,
                'type' => 'transfer_out',
                'points' => -$points,
                'reference' => "Transfer to customer #{$toCustomerId}",
                'balance_after' => $fromLp->total_points,
            ]);

            LoyaltyTransaction::create([
                'tenant_id' => $tenantId,
                'customer_id' => $toCustomerId,
                'program_id' => $programId,
                'type' => 'transfer_in',
                'points' => $points,
                'reference' => "Transfer from customer #{$fromCustomerId}",
                'balance_after' => $toLp->total_points,
            ]);

            return [
                'success' => true,
                'points_transferred' => $points,
                'from_balance' => $fromLp->total_points,
                'to_balance' => $toLp->total_points,
            ];
        });
    }
}
