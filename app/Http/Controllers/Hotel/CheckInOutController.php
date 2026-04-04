<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\CheckInOutService;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\Request;

class CheckInOutController extends Controller
{
    private CheckInOutService $checkInOutService;
    private RoomAvailabilityService $availabilityService;

    public function __construct(
        CheckInOutService $checkInOutService,
        RoomAvailabilityService $availabilityService
    ) {
        $this->checkInOutService = $checkInOutService;
        $this->availabilityService = $availabilityService;
    }

    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function checkInForm(Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        // Get available rooms for the reservation's room type
        $availableRooms = $this->availabilityService->getAvailableRooms(
            $reservation->tenant_id,
            $reservation->check_in_date->toDateString(),
            $reservation->check_out_date->toDateString(),
            $reservation->room_type_id
        );

        return view('hotel.check-in.form', compact('reservation', 'availableRooms'));
    }

    public function processCheckIn(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'key_card_number' => 'nullable|string|max:50',
            'deposit_amount' => 'nullable|numeric|min:0',
            'deposit_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        try {
            $checkInOut = $this->checkInOutService->processCheckIn($reservation->id, [
                'room_id' => $data['room_id'],
                'key_card_number' => $data['key_card_number'] ?? null,
                'deposit_amount' => $data['deposit_amount'] ?? 0,
                'deposit_method' => $data['deposit_method'] ?? null,
                'notes' => $data['notes'] ?? null,
                'processed_by' => auth()->id(),
            ]);

            ActivityLog::record('check_in_processed', "Check-in processed: {$reservation->reservation_number}", $reservation);

            return redirect()->route('hotel.reservations.show', $reservation)->with('success', "Check-in completed for reservation {$reservation->reservation_number}.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function checkOutForm(Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        // Calculate charges
        $charges = $this->checkInOutService->calculateCharges($reservation->id);

        return view('hotel.check-out.form', compact('reservation', 'charges'));
    }

    public function processCheckOut(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'notes' => 'nullable|string',
        ]);

        try {
            $checkInOut = $this->checkInOutService->processCheckOut($reservation->id, [
                'notes' => $data['notes'] ?? null,
                'processed_by' => auth()->id(),
            ]);

            ActivityLog::record('check_out_processed', "Check-out processed: {$reservation->reservation_number}", $reservation);

            return redirect()->route('hotel.reservations.show', $reservation)->with('success', "Check-out completed for reservation {$reservation->reservation_number}.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
