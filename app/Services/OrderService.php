<?php

namespace App\Services;

use App\Models\FbOrder;
use App\Models\FbOrderItem;
use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create new F&B order
     */
    public function createOrder(array $data): FbOrder
    {
        return DB::transaction(function () use ($data) {
            $order = FbOrder::create([
                'tenant_id' => auth()->user()->current_tenant_id,
                'order_number' => FbOrder::generateOrderNumber($data['order_type']),
                'order_type' => $data['order_type'],
                'guest_id' => $data['guest_id'] ?? null,
                'reservation_id' => $data['reservation_id'] ?? null,
                'room_number' => $data['room_number'] ?? null,
                'table_number' => $data['table_number'] ?? null,
                'created_by' => auth()->id(),
                'server_id' => $data['server_id'] ?? null,
                'status' => 'pending',
                'special_instructions' => $data['special_instructions'] ?? null,
            ]);

            // Add order items
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $this->addOrderItem($order, $itemData);
                }
            }

            // Calculate totals
            $order->calculateTotals();

            ActivityLog::record(
                'fb_order_created',
                "Created order #{$order->order_number}",
                $order,
                ['order_id' => $order->id]
            );

            return $order;
        });
    }

    /**
     * Add item to order
     */
    public function addOrderItem(FbOrder $order, array $itemData): FbOrderItem
    {
        $menuItem = MenuItem::findOrFail($itemData['menu_item_id']);

        $orderItem = FbOrderItem::create([
            'tenant_id' => $order->tenant_id,
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'item_name' => $menuItem->name,
            'quantity' => $itemData['quantity'],
            'unit_price' => $menuItem->price,
            'subtotal' => $menuItem->price * $itemData['quantity'],
            'special_requests' => $itemData['special_requests'] ?? null,
            'status' => 'pending',
        ]);

        // Increment sold count
        $menuItem->incrementSold($itemData['quantity']);

        return $orderItem;
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(int $orderId, string $newStatus): FbOrder
    {
        $order = FbOrder::findOrFail($orderId);
        $order->updateStatus($newStatus);

        return $order;
    }

    /**
     * Cancel order
     */
    public function cancelOrder(int $orderId): FbOrder
    {
        $order = FbOrder::findOrFail($orderId);

        if (!$order->canBeCancelled()) {
            throw new \Exception('Order cannot be cancelled at this stage');
        }

        $order->update(['status' => 'cancelled']);

        ActivityLog::record(
            'fb_order_cancelled',
            "Cancelled order #{$order->order_number}",
            $order,
            ['order_id' => $order->id]
        );

        return $order;
    }

    /**
     * Process payment for order
     */
    public function processPayment(int $orderId, string $paymentMethod): FbOrder
    {
        $order = FbOrder::findOrFail($orderId);

        $order->update([
            'payment_status' => 'paid',
            'payment_method' => $paymentMethod,
        ]);

        // If room charge, link to reservation
        if ($paymentMethod === 'room_charge' && $order->reservation_id) {
            // This would integrate with billing system
        }

        ActivityLog::record(
            'fb_order_paid',
            "Payment processed for order #{$order->order_number}",
            $order,
            ['payment_method' => $paymentMethod]
        );

        return $order;
    }

    /**
     * Get today's orders summary
     */
    public function getTodaySummary(int $tenantId): array
    {
        return [
            'total_orders' => FbOrder::where('tenant_id', $tenantId)
                ->whereDate('created_at', today())
                ->count(),
            'total_revenue' => FbOrder::where('tenant_id', $tenantId)
                ->whereDate('created_at', today())
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
            'pending_orders' => FbOrder::where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->count(),
            'completed_orders' => FbOrder::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->whereDate('created_at', today())
                ->count(),
        ];
    }
}
