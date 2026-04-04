<?php

namespace App\Services;

use App\Models\BanquetEvent;
use App\Models\BanquetEventOrder;
use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;

class BanquetService
{
    /**
     * Create new banquet event
     */
    public function createEvent(array $data): BanquetEvent
    {
        return DB::transaction(function () use ($data) {
            $event = BanquetEvent::create([
                'tenant_id' => auth()->user()->current_tenant_id,
                'event_number' => BanquetEvent::generateEventNumber(),
                'event_name' => $data['event_name'],
                'description' => $data['description'] ?? null,
                'client_guest_id' => $data['client_guest_id'] ?? null,
                'client_name' => $data['client_name'],
                'client_phone' => $data['client_phone'],
                'client_email' => $data['client_email'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'event_type' => $data['event_type'],
                'event_date' => $data['event_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'expected_guests' => $data['expected_guests'],
                'venue_room' => $data['venue_room'] ?? null,
                'setup_requirements' => $data['setup_requirements'] ?? null,
                'venue_rental_fee' => $data['venue_rental_fee'] ?? 0,
                'status' => 'inquiry',
                'assigned_coordinator' => $data['assigned_coordinator'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
            ]);

            ActivityLog::record(
                'banquet_event_created',
                "Created banquet event: {$event->event_number}",
                $event,
                ['event_id' => $event->id]
            );

            return $event;
        });
    }

    /**
     * Add menu items to banquet event
     */
    public function addMenuItems(int $eventId, array $items): void
    {
        $event = BanquetEvent::findOrFail($eventId);
        $totalFoodBeverage = 0;

        foreach ($items as $itemData) {
            $menuItem = MenuItem::findOrFail($itemData['menu_item_id']);

            $order = BanquetEventOrder::create([
                'tenant_id' => $event->tenant_id,
                'banquet_event_id' => $eventId,
                'menu_item_id' => $menuItem->id,
                'quantity' => $itemData['quantity'],
                'unit_price' => $menuItem->price,
                'total_price' => $menuItem->price * $itemData['quantity'],
                'special_instructions' => $itemData['special_instructions'] ?? null,
                'serving_time' => $itemData['serving_time'] ?? null,
            ]);

            $totalFoodBeverage += $order->total_price;
        }

        // Update event totals
        $event->update([
            'food_beverage_total' => $totalFoodBeverage,
        ]);
        $event->calculateTotal();
    }

    /**
     * Confirm banquet event
     */
    public function confirmEvent(int $eventId, float $depositAmount = 0): BanquetEvent
    {
        $event = BanquetEvent::findOrFail($eventId);

        $event->update([
            'status' => 'confirmed',
            'deposit_amount' => $depositAmount,
            'confirmed_guests' => $event->expected_guests,
        ]);

        ActivityLog::record(
            'banquet_event_confirmed',
            "Confirmed banquet event: {$event->event_number}",
            $event,
            ['deposit_amount' => $depositAmount]
        );

        return $event;
    }

    /**
     * Update confirmed guests count
     */
    public function updateGuestCount(int $eventId, int $guestCount): BanquetEvent
    {
        $event = BanquetEvent::findOrFail($eventId);

        $event->update(['confirmed_guests' => $guestCount]);

        // Recalculate food & beverage if needed
        if ($guestCount != $event->expected_guests) {
            // This would recalculate based on per-person pricing
        }

        return $event;
    }

    /**
     * Complete banquet event
     */
    public function completeEvent(int $eventId): BanquetEvent
    {
        $event = BanquetEvent::findOrFail($eventId);

        $event->update(['status' => 'completed']);

        ActivityLog::record(
            'banquet_event_completed',
            "Completed banquet event: {$event->event_number}",
            $event,
            []
        );

        return $event;
    }

    /**
     * Cancel banquet event
     */
    public function cancelEvent(int $eventId): BanquetEvent
    {
        $event = BanquetEvent::findOrFail($eventId);

        $event->update(['status' => 'cancelled']);

        ActivityLog::record(
            'banquet_event_cancelled',
            "Cancelled banquet event: {$event->event_number}",
            $event,
            []
        );

        return $event;
    }

    /**
     * Get upcoming events
     */
    public function getUpcomingEvents(int $tenantId, int $days = 30): array
    {
        return BanquetEvent::where('tenant_id', $tenantId)
            ->where('event_date', '>=', today())
            ->where('event_date', '<=', today()->addDays($days))
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->orderBy('event_date')
            ->with('coordinator')
            ->get();
    }

    /**
     * Get event revenue summary
     */
    public function getRevenueSummary(int $tenantId, string $period = 'month'): array
    {
        $query = BanquetEvent::where('tenant_id', $tenantId)
            ->where('status', 'completed');

        if ($period === 'month') {
            $query->whereMonth('event_date', now()->month)
                ->whereYear('event_date', now()->year);
        } elseif ($period === 'year') {
            $query->whereYear('event_date', now()->year);
        }

        $events = $query->get();

        return [
            'total_events' => $events->count(),
            'total_revenue' => $events->sum('total_amount'),
            'average_event_value' => $events->avg('total_amount') ?? 0,
            'total_deposit_collected' => $events->sum('deposit_amount'),
        ];
    }
}
