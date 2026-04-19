<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\MinibarInventory;
use App\Models\MinibarTransaction;
use App\Services\MinibarService;
use Illuminate\Http\Request;

class MinibarController extends Controller
{
    protected $minibarService;

    public function __construct(MinibarService $minibarService)
    {
        $this->minibarService = $minibarService;
    }

    public function index()
    {
        $tenantId = $this->tenantId();

        $lowStockRooms = $this->minibarService->getLowStockRooms($tenantId);

        $recentTransactions = MinibarTransaction::where('tenant_id', $tenantId)
            ->with(['menuItem', 'reservation.guest'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('hotel.fb.minibar.index', compact('lowStockRooms', 'recentTransactions'));
    }

    public function roomStock(int $roomNumber)
    {
        $inventory = MinibarInventory::where('room_number', $roomNumber)
            ->with('menuItem')
            ->orderBy('menu_item_id')
            ->get();

        return view('hotel.fb.minibar.room-stock', compact('roomNumber', 'inventory'));
    }

    public function recordConsumption(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'room_number' => 'required|integer',
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $transaction = $this->minibarService->recordConsumption($validated);

        return back()->with('success', 'Consumption recorded successfully');
    }

    public function restock(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|integer',
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $inventory = $this->minibarService->restockMinibar(
            $validated['room_number'],
            $validated['menu_item_id'],
            $validated['quantity']
        );

        return back()->with('success', 'Minibar restocked successfully');
    }

    public function reservationCharges(int $reservationId)
    {
        $charges = $this->minibarService->getReservationCharges($reservationId);
        return view('hotel.fb.minibar.charges', compact('reservationId', 'charges'));
    }

    public function billAllCharges(int $reservationId)
    {
        $this->minibarService->billAllCharges($reservationId);
        return back()->with('success', 'All charges billed to reservation');
    }
}
