<?php

namespace App\Services;

use App\Models\FbOrder;
use App\Models\FbOrderItem;
use App\Models\FbPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * FbSplitBillService - Accurate split bill calculation with rounding fix
 *
 * BUG-FB-003 FIX: Ensure split amounts sum to original bill total
 *
 * Problems Fixed:
 * 1. Rounding errors cause split total ≠ original total
 * 2. Tax and service charge not distributed correctly
 * 3. No validation that splits equal original amount
 * 4. Missing remainder distribution (last person gets rounded difference)
 */
class FbSplitBillService
{
    /**
     * BUG-FB-003 FIX: Split bill by number of people (equal split)
     *
     * Uses remainder distribution to ensure accuracy
     *
     * @param  int  $splitCount  Number of ways to split
     * @return array Split details with accurate amounts
     */
    public function splitBillEqually(FbOrder $order, int $splitCount): array
    {
        if ($splitCount < 2) {
            throw new \InvalidArgumentException('Split count must be at least 2');
        }

        $totalAmount = $order->total_amount;
        $subtotal = $order->subtotal;
        $taxAmount = $order->tax_amount;
        $serviceCharge = $order->service_charge;
        $discountAmount = $order->discount_amount;

        // Calculate base split amounts
        $baseSplitTotal = floor($totalAmount / $splitCount * 100) / 100;
        $remainder = round(($totalAmount - ($baseSplitTotal * $splitCount)) * 100);

        $splits = [];
        for ($i = 1; $i <= $splitCount; $i++) {
            // Last person gets the remainder (fixes rounding error)
            $splitAmount = $baseSplitTotal;
            if ($i === $splitCount && $remainder > 0) {
                $splitAmount += $remainder / 100;
            }

            $splits[] = [
                'split_number' => $i,
                'total_amount' => round($splitAmount, 2),
                'subtotal' => round($subtotal / $splitCount, 2),
                'tax_amount' => round($taxAmount / $splitCount, 2),
                'service_charge' => round($serviceCharge / $splitCount, 2),
                'discount_amount' => round($discountAmount / $splitCount, 2),
                'items' => [], // Will be populated if splitting by items
            ];
        }

        // Validate split totals
        $splitTotal = array_sum(array_column($splits, 'total_amount'));
        $discrepancy = abs($splitTotal - $totalAmount);

        if ($discrepancy > 0.01) {
            Log::error('Split bill discrepancy detected', [
                'order_id' => $order->id,
                'original_total' => $totalAmount,
                'split_total' => $splitTotal,
                'discrepancy' => $discrepancy,
            ]);
        }

        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'original_total' => $totalAmount,
            'split_count' => $splitCount,
            'split_type' => 'equal',
            'splits' => $splits,
            'validation' => [
                'split_total' => round($splitTotal, 2),
                'discrepancy' => round($discrepancy, 2),
                'is_accurate' => $discrepancy <= 0.01,
            ],
        ];
    }

    /**
     * BUG-FB-003 FIX: Split bill by specific items
     *
     * Each person pays for their own items + proportional tax/service
     *
     * @param  array  $itemAssignments  ['person_1' => [item_id, item_id], 'person_2' => [item_id]]
     * @return array Split details
     */
    public function splitBillByItems(FbOrder $order, array $itemAssignments): array
    {
        $order->load('items');

        $splits = [];
        $totalAssigned = 0;
        $unassignedItems = [];

        foreach ($itemAssignments as $personKey => $itemIds) {
            $personSubtotal = 0;
            $personItems = [];

            foreach ($itemIds as $itemId) {
                $item = $order->items->find($itemId);

                if (! $item) {
                    continue;
                }

                $personSubtotal += $item->subtotal;
                $personItems[] = [
                    'item_id' => $item->id,
                    'item_name' => $item->item_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ];
            }

            // Calculate proportional tax and service charge
            $itemRatio = $order->subtotal > 0 ? $personSubtotal / $order->subtotal : 0;
            $personTax = round($order->tax_amount * $itemRatio, 2);
            $personServiceCharge = round($order->service_charge * $itemRatio, 2);
            $personDiscount = round($order->discount_amount * $itemRatio, 2);
            $personTotal = $personSubtotal + $personTax + $personServiceCharge - $personDiscount;

            $splits[$personKey] = [
                'items' => $personItems,
                'subtotal' => round($personSubtotal, 2),
                'tax_amount' => $personTax,
                'service_charge' => $personServiceCharge,
                'discount_amount' => $personDiscount,
                'total_amount' => round($personTotal, 2),
            ];

            $totalAssigned += $personSubtotal;
        }

        // Check for unassigned items
        $assignedItemIds = collect($itemAssignments)->flatten()->toArray();
        foreach ($order->items as $item) {
            if (! in_array($item->id, $assignedItemIds)) {
                $unassignedItems[] = [
                    'item_id' => $item->id,
                    'item_name' => $item->item_name,
                    'subtotal' => $item->subtotal,
                ];
            }
        }

        // Validate totals
        $splitTotal = array_sum(array_column($splits, 'total_amount'));
        $discrepancy = abs($splitTotal - $order->total_amount);

        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'original_total' => $order->total_amount,
            'split_type' => 'by_items',
            'splits' => $splits,
            'unassigned_items' => $unassignedItems,
            'validation' => [
                'split_total' => round($splitTotal, 2),
                'discrepancy' => round($discrepancy, 2),
                'is_accurate' => $discrepancy <= 0.01,
                'has_unassigned' => ! empty($unassignedItems),
            ],
        ];
    }

    /**
     * BUG-FB-003 FIX: Process split payment with validation
     *
     * @param  array  $payments  Array of ['amount' => X, 'method' => Y]
     * @return array Result with validation
     */
    public function processSplitPayment(FbOrder $order, array $payments): array
    {
        return DB::transaction(function () use ($order, $payments) {
            $totalPaid = array_sum(array_column($payments, 'amount'));
            $discrepancy = abs($totalPaid - $order->total_amount);

            // Validate total matches order total
            if ($discrepancy > 0.01) {
                throw new \Exception(
                    'Split payment total (Rp '.number_format($totalPaid, 0, ',', '.').
                    ') does not match order total (Rp '.number_format($order->total_amount, 0, ',', '.').
                    '). Discrepancy: Rp '.number_format($discrepancy, 0, ',', '.')
                );
            }

            $paymentRecords = [];

            foreach ($payments as $index => $payment) {
                $paymentRecord = FbPayment::create([
                    'tenant_id' => $order->tenant_id,
                    'fb_order_id' => $order->id,
                    'payment_number' => $this->generatePaymentNumber($order, $index + 1),
                    'amount' => $payment['amount'],
                    'payment_method' => $payment['method'],
                    'status' => 'completed',
                    'paid_at' => now(),
                    'notes' => $payment['notes'] ?? 'Split payment '.($index + 1),
                ]);

                $paymentRecords[] = $paymentRecord;
            }

            // Update order payment status
            if ($totalPaid >= $order->total_amount) {
                $order->update([
                    'payment_status' => 'paid',
                    'completed_at' => now(),
                ]);
            } else {
                $order->update(['payment_status' => 'partial']);
            }

            Log::info('Split payment processed', [
                'order_id' => $order->id,
                'order_total' => $order->total_amount,
                'total_paid' => $totalPaid,
                'payment_count' => count($payments),
            ]);

            return [
                'success' => true,
                'order_id' => $order->id,
                'order_total' => $order->total_amount,
                'total_paid' => $totalPaid,
                'payment_count' => count($paymentRecords),
                'payments' => $paymentRecords,
            ];
        });
    }

    /**
     * BUG-FB-003 FIX: Calculate item-level split with exact amounts
     *
     * For items shared by multiple people
     *
     * @param  int  $sharedBy  Number of people sharing
     */
    public function splitItemCost(FbOrderItem $item, int $sharedBy): array
    {
        if ($sharedBy < 1) {
            throw new \InvalidArgumentException('Shared by must be at least 1');
        }

        $itemTotal = $item->subtotal;
        $baseAmount = floor($itemTotal / $sharedBy * 100) / 100;
        $remainder = round(($itemTotal - ($baseAmount * $sharedBy)) * 100);

        $splits = [];
        for ($i = 1; $i <= $sharedBy; $i++) {
            $splitAmount = $baseAmount;

            // Last person gets remainder
            if ($i === $sharedBy && $remainder > 0) {
                $splitAmount += $remainder / 100;
            }

            $splits[] = [
                'person' => $i,
                'amount' => round($splitAmount, 2),
            ];
        }

        return [
            'item_id' => $item->id,
            'item_name' => $item->item_name,
            'item_total' => $itemTotal,
            'shared_by' => $sharedBy,
            'splits' => $splits,
            'validation' => [
                'split_total' => round(array_sum(array_column($splits, 'amount')), 2),
                'is_accurate' => abs(array_sum(array_column($splits, 'amount')) - $itemTotal) <= 0.01,
            ],
        ];
    }

    /**
     * Generate unique payment number for split payments
     */
    protected function generatePaymentNumber(FbOrder $order, int $splitIndex): string
    {
        $orderNumber = str_replace('-', '', $order->order_number);

        return "{$orderNumber}-P{$splitIndex}";
    }

    /**
     * Validate split configuration before processing
     *
     * @param  string  $splitType  'equal' or 'by_items'
     */
    public function validateSplit(FbOrder $order, string $splitType, array $splitConfig): array
    {
        try {
            if ($splitType === 'equal') {
                $result = $this->splitBillEqually($order, $splitConfig['split_count']);
            } elseif ($splitType === 'by_items') {
                $result = $this->splitBillByItems($order, $splitConfig['item_assignments']);
            } else {
                return ['valid' => false, 'error' => 'Invalid split type'];
            }

            return [
                'valid' => $result['validation']['is_accurate'],
                'result' => $result,
                'error' => $result['validation']['is_accurate'] ? null : 'Split total does not match order total',
            ];
        } catch (\Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }
}
