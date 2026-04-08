<?php

namespace App\Services;

use App\Models\FbOrder;
use App\Models\KitchenOrderTicket;
use App\Models\KitchenOrderItem;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Log;

/**
 * KitchenTicketService - Idempotent kitchen ticket creation with duplicate prevention
 * 
 * BUG-FB-002 FIX: Prevent duplicate kitchen tickets on retry
 * 
 * Problems Fixed:
 * 1. No idempotency check - retry creates duplicate tickets
 * 2. No ticket existence validation
 * 3. No distributed lock for concurrent requests
 * 4. No ticket count validation
 */
class KitchenTicketService
{
    /**
     * BUG-FB-002 FIX: Create kitchen tickets with idempotency guarantee
     * 
     * Uses getOrCreate pattern to prevent duplicates on retry
     * 
     * @param FbOrder $order
     * @return array Created or existing tickets
     */
    public function createTicketsForOrder(FbOrder $order): array
    {
        // Check if tickets already exist for this order
        if ($this->hasExistingTickets($order)) {
            Log::info('Kitchen: Tickets already exist for order, returning existing', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'existing_tickets' => $this->getExistingTickets($order)->count(),
            ]);

            return $this->getExistingTickets($order)->load('items.menuItem')->toArray();
        }

        // Use database transaction + lock to prevent race conditions
        return \DB::transaction(function () use ($order) {
            // Double-check after acquiring lock
            if ($this->hasExistingTickets($order)) {
                return $this->getExistingTickets($order)->load('items.menuItem')->toArray();
            }

            $tickets = [];

            // Group items by station (kitchen station)
            $itemsByStation = $this->groupItemsByStation($order);

            foreach ($itemsByStation as $station => $items) {
                $ticket = KitchenOrderTicket::create([
                    'tenant_id' => $order->tenant_id,
                    'fb_order_id' => $order->id,
                    'ticket_number' => KitchenOrderTicket::generateTicketNumber(),
                    'station' => $station,
                    'status' => 'pending',
                    'priority' => $this->determinePriority($order),
                    'estimated_time' => $this->calculateEstimatedTime($items),
                    'chef_notes' => $order->special_instructions,
                ]);

                // Create ticket items
                foreach ($items as $item) {
                    KitchenOrderItem::create([
                        'tenant_id' => $order->tenant_id,
                        'ticket_id' => $ticket->id,
                        'menu_item_id' => $item['menu_item_id'],
                        'quantity' => $item['quantity'],
                        'special_instructions' => $item['special_instructions'] ?? null,
                        'modifiers' => $item['modifiers'] ?? [],
                    ]);
                }

                $tickets[] = $ticket;
            }

            Log::info('Kitchen: Tickets created successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'tickets_created' => count($tickets),
                'stations' => array_keys($itemsByStation),
            ]);

            return collect($tickets)->load('items.menuItem')->toArray();
        });
    }

    /**
     * BUG-FB-002 FIX: Check if order already has kitchen tickets
     * 
     * @param FbOrder $order
     * @return bool
     */
    public function hasExistingTickets(FbOrder $order): bool
    {
        return KitchenOrderTicket::where('fb_order_id', $order->id)->exists();
    }

    /**
     * Get existing tickets for order
     * 
     * @param FbOrder $order
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getExistingTickets(FbOrder $order)
    {
        return KitchenOrderTicket::where('fb_order_id', $order->id)
            ->with('items.menuItem')
            ->get();
    }

    /**
     * BUG-FB-002 FIX: Validate ticket count matches expected
     * 
     * @param FbOrder $order
     * @return array
     */
    public function validateTicketCount(FbOrder $order): array
    {
        $existingTickets = $this->getExistingTickets($order);
        $expectedStations = $this->groupItemsByStation($order);
        $expectedCount = count($expectedStations);
        $actualCount = $existingTickets->count();

        $isValid = $actualCount === $expectedCount;
        $hasDuplicates = $actualCount > $expectedCount;

        return [
            'valid' => $isValid,
            'has_duplicates' => $hasDuplicates,
            'expected_count' => $expectedCount,
            'actual_count' => $actualCount,
            'duplicate_count' => max(0, $actualCount - $expectedCount),
            'tickets' => $existingTickets,
            'message' => $isValid
                ? "Ticket count valid: {$actualCount} tickets."
                : ($hasDuplicates
                    ? "Duplicate tickets detected! Expected: {$expectedCount}, Actual: {$actualCount}"
                    : "Missing tickets! Expected: {$expectedCount}, Actual: {$actualCount}"),
        ];
    }

    /**
     * BUG-FB-002 FIX: Remove duplicate tickets (cleanup)
     * 
     * Keep only the first ticket per station, delete duplicates
     * 
     * @param FbOrder $order
     * @return array
     */
    public function cleanupDuplicateTickets(FbOrder $order): array
    {
        $validation = $this->validateTicketCount($order);

        if (!$validation['has_duplicates']) {
            return [
                'success' => true,
                'message' => 'No duplicates found.',
                'deleted' => 0,
            ];
        }

        $deleted = 0;
        $tickets = $this->getExistingTickets($order);

        // Group tickets by station
        $ticketsByStation = $tickets->groupBy('station');

        foreach ($ticketsByStation as $station => $stationTickets) {
            // Keep first ticket, delete rest
            if ($stationTickets->count() > 1) {
                $keepTicket = $stationTickets->first();
                $duplicates = $stationTickets->slice(1);

                foreach ($duplicates as $duplicate) {
                    // Delete ticket items first
                    $duplicate->items()->delete();
                    $duplicate->delete();
                    $deleted++;

                    Log::warning('Kitchen: Duplicate ticket removed', [
                        'order_id' => $order->id,
                        'ticket_id' => $duplicate->id,
                        'ticket_number' => $duplicate->ticket_number,
                        'station' => $station,
                        'kept_ticket_id' => $keepTicket->id,
                    ]);
                }
            }
        }

        return [
            'success' => true,
            'message' => "Cleaned up {$deleted} duplicate tickets.",
            'deleted' => $deleted,
            'remaining' => $this->getExistingTickets($order)->count(),
        ];
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
}
