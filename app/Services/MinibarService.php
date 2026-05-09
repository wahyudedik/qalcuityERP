<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\MinibarInventory;
use App\Models\MinibarTransaction;
use Illuminate\Support\Facades\DB;

class MinibarService
{
    /**
     * Initialize minibar for a room
     */
    public function initializeRoomMinibar(int $roomNumber, int $tenantId): void
    {
        $defaultItems = MenuItem::where('tenant_id', $tenantId)
            ->whereHas('menu', function ($q) {
                $q->where('type', 'minibar');
            })
            ->get();

        foreach ($defaultItems as $item) {
            MinibarInventory::create([
                'tenant_id' => $tenantId,
                'room_number' => $roomNumber,
                'menu_item_id' => $item->id,
                'initial_stock' => 5,
                'current_stock' => 5,
                'minimum_stock' => 2,
            ]);
        }
    }

    /**
     * Record minibar consumption
     */
    public function recordConsumption(array $data): MinibarTransaction
    {
        return DB::transaction(function () use ($data) {
            $menuItem = MenuItem::findOrFail($data['menu_item_id']);

            // Check inventory
            $inventory = MinibarInventory::where('room_number', $data['room_number'])
                ->where('menu_item_id', $data['menu_item_id'])
                ->firstOrFail();

            if (! $inventory->consume($data['quantity'])) {
                throw new \Exception('Insufficient stock in minibar');
            }

            // Create transaction
            $transaction = MinibarTransaction::create([
                'tenant_id' => $inventory->tenant_id,
                'reservation_id' => $data['reservation_id'],
                'room_number' => $data['room_number'],
                'menu_item_id' => $data['menu_item_id'],
                'quantity_consumed' => $data['quantity'],
                'unit_price' => $menuItem->price,
                'total_charge' => $menuItem->price * $data['quantity'],
                'consumption_date' => now(),
                'recorded_by' => auth()->id(),
                'billing_status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            ActivityLog::record(
                'minibar_consumed',
                "Recorded minibar consumption: {$menuItem->name} x{$data['quantity']} in room {$data['room_number']}",
                null,
                ['transaction_id' => $transaction->id]
            );

            return $transaction;
        });
    }

    /**
     * Restock minibar
     */
    public function restockMinibar(int $roomNumber, int $menuItemId, int $quantity): MinibarInventory
    {
        $inventory = MinibarInventory::where('room_number', $roomNumber)
            ->where('menu_item_id', $menuItemId)
            ->firstOrFail();

        $inventory->restock($quantity);

        ActivityLog::record(
            'minibar_restocked',
            "Restocked {$inventory->menuItem->name} (qty: {$quantity}) in room {$roomNumber}",
            null,
            ['room_number' => $roomNumber, 'menu_item_id' => $menuItemId]
        );

        return $inventory;
    }

    /**
     * Get minibar charges for reservation
     */
    public function getReservationCharges(int $reservationId): array
    {
        $transactions = MinibarTransaction::where('reservation_id', $reservationId)
            ->with('menuItem')
            ->orderBy('consumption_date', 'desc')
            ->get();

        return [
            'transactions' => $transactions,
            'total_charges' => $transactions->sum('total_charge'),
            'pending_charges' => $transactions->where('billing_status', 'pending')->sum('total_charge'),
            'billed_charges' => $transactions->where('billing_status', 'billed')->sum('total_charge'),
        ];
    }

    /**
     * Bill all pending charges to reservation
     */
    public function billAllCharges(int $reservationId): void
    {
        MinibarTransaction::where('reservation_id', $reservationId)
            ->where('billing_status', 'pending')
            ->update(['billing_status' => 'billed']);

        ActivityLog::record(
            'minibar_billed',
            "Billed all pending minibar charges for reservation #{$reservationId}",
            null,
            ['reservation_id' => $reservationId]
        );
    }

    /**
     * Get low stock rooms
     */
    public function getLowStockRooms(int $tenantId): array
    {
        return MinibarInventory::where('tenant_id', $tenantId)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->with(['menuItem', 'room'])
            ->get()
            ->groupBy('room_number');
    }
}
