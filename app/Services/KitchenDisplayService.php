<?php

namespace App\Services;

use App\Models\FbOrder;
use App\Models\KitchenOrderItem;
use App\Models\KitchenOrderTicket;
use App\Models\MenuItem;

class KitchenDisplayService
{
    /**
     * Create kitchen tickets from order
     */
    public function createTicketsFromOrder(FbOrder $order): array
    {
        // BUG-FB-002 FIX: Use idempotent ticket service to prevent duplicates
        $ticketService = new KitchenTicketService();
        return $ticketService->createTicketsForOrder($order);
    }

    /**
     * Get active tickets for KDS display
     */
    public function getActiveTickets(int $tenantId, ?string $station = null)
    {
        $query = KitchenOrderTicket::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'preparing'])
            ->with(['order.guest', 'items.menuItem'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc');

        if ($station) {
            $query->where('station', $station);
        }

        return $query->get();
    }

    /**
     * Start preparing ticket
     */
    public function startTicket(KitchenOrderTicket $ticket): void
    {
        $ticket->startPreparing();
    }

    /**
     * Complete ticket
     */
    public function completeTicket(KitchenOrderTicket $ticket): void
    {
        $ticket->markReady();

        // Check if all tickets for this order are ready
        $this->checkOrderCompletion($ticket->fb_order_id);
    }

    /**
     * Get KDS statistics
     */
    public function getKdsStats(int $tenantId): array
    {
        return [
            'pending' => KitchenOrderTicket::where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->count(),
            'preparing' => KitchenOrderTicket::where('tenant_id', $tenantId)
                ->where('status', 'preparing')
                ->count(),
            'ready' => KitchenOrderTicket::where('tenant_id', $tenantId)
                ->where('status', 'ready')
                ->count(),
            'overdue' => KitchenOrderTicket::where('tenant_id', $tenantId)
                ->where('status', 'preparing')
                ->get()
                ->filter(fn($t) => $t->isOverdue())
                ->count(),
            'avg_prep_time' => KitchenOrderTicket::where('tenant_id', $tenantId)
                ->whereNotNull('completed_at')
                ->whereDate('created_at', today())
                ->get()
                ->map(fn($t) => $t->getElapsedTime())
                ->avg() ?? 0,
        ];
    }

    /**
     * Get overdue tickets
     */
    public function getOverdueTickets(int $tenantId)
    {
        return KitchenOrderTicket::where('tenant_id', $tenantId)
            ->where('status', 'preparing')
            ->with(['order.guest', 'items.menuItem'])
            ->get()
            ->filter(fn($ticket) => $ticket->isOverdue());
    }

    /**
     * Group order items by kitchen station
     */
    private function groupItemsByStation(FbOrder $order): array
    {
        $stations = [];

        foreach ($order->items as $item) {
            $menuItem = MenuItem::find($item->menu_item_id);
            $station = $menuItem?->category ?? 'general'; // Use category as station

            if (!isset($stations[$station])) {
                $stations[$station] = [];
            }

            $stations[$station][] = [
                'menu_item_id' => $item->menu_item_id,
                'quantity' => $item->quantity,
                'special_instructions' => $item->special_instructions,
                'modifiers' => $item->modifiers ?? [],
            ];
        }

        return $stations;
    }

    /**
     * Determine order priority
     */
    private function determinePriority(FbOrder $order): string
    {
        // VIP guests or large orders get priority
        if ($order->total_amount > 1000000) {
            return 'vip';
        }

        if ($order->guest && $order->guest->vip_status ?? false) {
            return 'vip';
        }

        return 'normal';
    }

    /**
     * Calculate estimated preparation time
     */
    private function calculateEstimatedTime(array $items): int
    {
        $maxTime = 0;

        foreach ($items as $item) {
            $menuItem = MenuItem::find($item['menu_item_id']);
            $prepTime = $menuItem?->preparation_time ?? 15; // default 15 minutes
            $maxTime = max($maxTime, $prepTime);
        }

        return $maxTime;
    }

    /**
     * Check if all tickets for an order are completed
     */
    private function checkOrderCompletion(int $orderId): void
    {
        $allTickets = KitchenOrderTicket::where('fb_order_id', $orderId)->get();
        $allReady = $allTickets->every(fn($t) => in_array($t->status, ['ready', 'served']));

        if ($allReady) {
            // Update order status to ready
            FbOrder::where('id', $orderId)->update(['status' => 'ready']);
        }
    }
}
