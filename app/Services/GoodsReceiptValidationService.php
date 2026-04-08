<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\GoodsReceipt;
use Illuminate\Support\Facades\Log;

/**
 * GoodsReceiptValidationService - Prevent goods receipt over-acceptance
 * 
 * BUG-PO-002 FIX: Validate GR quantity does not exceed PO quantity
 * 
 * Security Rules:
 * 1. Cannot receive more than ordered quantity
 * 2. Must track cumulative received across multiple GRs
 * 3. Must validate accepted quantity (not just received)
 * 4. Must allow partial receipts (multiple GRs for same PO)
 */
class GoodsReceiptValidationService
{
    /**
     * BUG-PO-002 FIX: Validate if GR items exceed PO quantities
     * 
     * @param PurchaseOrder $po
     * @param array $grItems Array of ['purchase_order_item_id' => X, 'quantity_accepted' => Y]
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateReceipt(PurchaseOrder $po, array $grItems): array
    {
        $errors = [];
        $po->load('items');

        foreach ($grItems as $index => $grItem) {
            $poItemId = $grItem['purchase_order_item_id'];
            $newAcceptedQty = (float) $grItem['quantity_accepted'];
            $newReceivedQty = (float) ($grItem['quantity_received'] ?? $grItem['quantity_accepted']);

            // Find PO item
            $poItem = $po->items->find($poItemId);

            if (!$poItem) {
                $errors[] = [
                    'item_index' => $index,
                    'error' => 'Item tidak ditemukan di PO',
                    'field' => 'purchase_order_item_id',
                ];
                continue;
            }

            // Calculate already received (excluding current GR if updating)
            $alreadyReceived = $this->getAlreadyReceived($poItem, $grItem['goods_receipt_id'] ?? null);
            $remaining = $poItem->quantity_ordered - $alreadyReceived;

            // Check if accepted quantity exceeds remaining
            if ($newAcceptedQty > $remaining) {
                $errors[] = [
                    'item_index' => $index,
                    'product_name' => $poItem->product->name ?? 'Product #' . $poItem->product_id,
                    'error' => 'Over-acceptance detected',
                    'field' => 'quantity_accepted',
                    'ordered' => $poItem->quantity_ordered,
                    'already_received' => $alreadyReceived,
                    'remaining' => $remaining,
                    'attempted' => $newAcceptedQty,
                    'overage' => $newAcceptedQty - $remaining,
                ];
            }

            // Check if received quantity exceeds remaining
            if ($newReceivedQty > $remaining) {
                $errors[] = [
                    'item_index' => $index,
                    'product_name' => $poItem->product->name ?? 'Product #' . $poItem->product_id,
                    'error' => 'Received quantity exceeds remaining PO quantity',
                    'field' => 'quantity_received',
                    'ordered' => $poItem->quantity_ordered,
                    'already_received' => $alreadyReceived,
                    'remaining' => $remaining,
                    'attempted' => $newReceivedQty,
                    'overage' => $newReceivedQty - $remaining,
                ];
            }

            // Check if accepted > received (logical error)
            if ($newAcceptedQty > $newReceivedQty) {
                $errors[] = [
                    'item_index' => $index,
                    'product_name' => $poItem->product->name ?? 'Product #' . $poItem->product_id,
                    'error' => 'Accepted quantity cannot exceed received quantity',
                    'field' => 'quantity_accepted',
                    'received' => $newReceivedQty,
                    'accepted' => $newAcceptedQty,
                ];
            }

            // Check for negative quantities
            if ($newReceivedQty < 0 || $newAcceptedQty < 0) {
                $errors[] = [
                    'item_index' => $index,
                    'error' => 'Quantity cannot be negative',
                    'field' => 'quantity',
                ];
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * BUG-PO-002 FIX: Calculate already received quantity for PO item
     * 
     * Excludes the current GR if updating (to avoid double-counting)
     * 
     * @param PurchaseOrderItem $poItem
     * @param int|null $excludeGrId Exclude this GR from calculation
     * @return float
     */
    public function getAlreadyReceived(PurchaseOrderItem $poItem, ?int $excludeGrId = null): float
    {
        $query = \App\Models\GoodsReceiptItem::where('purchase_order_item_id', $poItem->id);

        if ($excludeGrId) {
            $query->where('goods_receipt_id', '!=', $excludeGrId);
        }

        return $query->sum('quantity_accepted');
    }

    /**
     * BUG-PO-002 FIX: Get remaining quantity for PO item
     * 
     * @param PurchaseOrderItem $poItem
     * @return float
     */
    public function getRemainingQuantity(PurchaseOrderItem $poItem): float
    {
        $alreadyReceived = $this->getAlreadyReceived($poItem);
        return max(0, $poItem->quantity_ordered - $alreadyReceived);
    }

    /**
     * BUG-PO-002 FIX: Get receipt summary for PO
     * 
     * @param PurchaseOrder $po
     * @return array
     */
    public function getReceiptSummary(PurchaseOrder $po): array
    {
        $po->load('items');

        $items = [];
        $totalOrdered = 0;
        $totalReceived = 0;
        $totalRemaining = 0;

        foreach ($po->items as $item) {
            $alreadyReceived = $this->getAlreadyReceived($item);
            $remaining = max(0, $item->quantity_ordered - $alreadyReceived);

            $items[] = [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'Product #' . $item->product_id,
                'ordered' => $item->quantity_ordered,
                'received' => $alreadyReceived,
                'remaining' => $remaining,
                'percentage' => $item->quantity_ordered > 0
                    ? round(($alreadyReceived / $item->quantity_ordered) * 100, 2)
                    : 100,
            ];

            $totalOrdered += $item->quantity_ordered;
            $totalReceived += $alreadyReceived;
            $totalRemaining += $remaining;
        }

        return [
            'po_number' => $po->number,
            'po_status' => $po->status,
            'items' => $items,
            'summary' => [
                'total_ordered' => $totalOrdered,
                'total_received' => $totalReceived,
                'total_remaining' => $totalRemaining,
                'completion_percentage' => $totalOrdered > 0
                    ? round(($totalReceived / $totalOrdered) * 100, 2)
                    : 100,
                'is_complete' => $totalRemaining <= 0,
            ],
        ];
    }

    /**
     * BUG-PO-002 FIX: Validate and auto-correct GR quantities
     * 
     * If user tries to over-receive, auto-correct to remaining quantity
     * 
     * @param PurchaseOrder $po
     * @param array $grItems
     * @return array ['corrected_items' => array, 'warnings' => array]
     */
    public function autoCorrectQuantities(PurchaseOrder $po, array $grItems): array
    {
        $correctedItems = [];
        $warnings = [];
        $po->load('items');

        foreach ($grItems as $index => $grItem) {
            $poItemId = $grItem['purchase_order_item_id'];
            $poItem = $po->items->find($poItemId);

            if (!$poItem) {
                $correctedItems[] = $grItem;
                continue;
            }

            $alreadyReceived = $this->getAlreadyReceived($poItem, $grItem['goods_receipt_id'] ?? null);
            $remaining = max(0, $poItem->quantity_ordered - $alreadyReceived);

            $correctedItem = $grItem;

            // Auto-correct received quantity
            $newReceivedQty = (float) ($grItem['quantity_received'] ?? $grItem['quantity_accepted']);
            if ($newReceivedQty > $remaining) {
                $correctedItem['quantity_received'] = $remaining;
                $warnings[] = [
                    'item_index' => $index,
                    'product_name' => $poItem->product->name ?? 'Product #' . $poItem->product_id,
                    'warning' => 'Quantity received auto-corrected from ' . $newReceivedQty . ' to ' . $remaining . ' (remaining PO quantity)',
                    'original' => $newReceivedQty,
                    'corrected' => $remaining,
                ];
                $newReceivedQty = $remaining;
            }

            // Auto-correct accepted quantity
            $newAcceptedQty = (float) $grItem['quantity_accepted'];
            if ($newAcceptedQty > $newReceivedQty) {
                $correctedItem['quantity_accepted'] = $newReceivedQty;
                $warnings[] = [
                    'item_index' => $index,
                    'product_name' => $poItem->product->name ?? 'Product #' . $poItem->product_id,
                    'warning' => 'Quantity accepted auto-corrected from ' . $newAcceptedQty . ' to ' . $newReceivedQty,
                    'original' => $newAcceptedQty,
                    'corrected' => $newReceivedQty,
                ];
            } elseif ($newAcceptedQty > $remaining) {
                $correctedItem['quantity_accepted'] = $remaining;
                $warnings[] = [
                    'item_index' => $index,
                    'product_name' => $poItem->product->name ?? 'Product #' . $poItem->product_id,
                    'warning' => 'Quantity accepted auto-corrected from ' . $newAcceptedQty . ' to ' . $remaining,
                    'original' => $newAcceptedQty,
                    'corrected' => $remaining,
                ];
            }

            $correctedItems[] = $correctedItem;
        }

        return [
            'corrected_items' => $correctedItems,
            'warnings' => $warnings,
            'has_corrections' => !empty($warnings),
        ];
    }

    /**
     * BUG-PO-002 FIX: Check if PO is fully received
     * 
     * @param PurchaseOrder $po
     * @return bool
     */
    public function isFullyReceived(PurchaseOrder $po): bool
    {
        $po->load('items');

        foreach ($po->items as $item) {
            $remaining = $this->getRemainingQuantity($item);
            if ($remaining > 0) {
                return false;
            }
        }

        return true;
    }
}
