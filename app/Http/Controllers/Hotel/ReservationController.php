<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\EarlyLateRequest;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\ReservationRoomChange;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\ReservationService;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    private ReservationService $reservationService;
    private RoomAvailabilityService $availabilityService;

    public function __construct(
        ReservationService $reservationService,
        RoomAvailabilityService $availabilityService
    ) {
        $this->reservationService = $reservationService;
        $this->availabilityService = $availabilityService;
    }

    // tenantId() inherited from parent Controller

    public function index(Request $request)
    {
        $tid = $this->tenantId();

        $query = Reservation::with(['guest', 'roomType', 'room'])
            ->where('tenant_id', $tid);

        // Filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->source) {
            $query->where('source', $request->source);
        }

        if ($request->date_from) {
            $query->where('check_in_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->where('check_out_date', '<=', $request->date_to);
        }

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('reservation_number', 'like', "%$s%")
                    ->orWhereHas('guest', function ($subQ) use ($s) {
                        $subQ->where('name', 'like', "%$s%")
                            ->orWhere('email', 'like', "%$s%")
                            ->orWhere('phone', 'like', "%$s%");
                    });
            });
        }

        $reservations = $query->orderBy('check_in_date', 'desc')->paginate(20)->withQueryString();

        // Filter options
        $statuses = ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'];
        $sources = ['direct', 'bookingcom', 'agoda', 'expedia', 'airbnb', 'tripadvisor', 'other'];

        return view('hotel.reservations.index', compact('reservations', 'statuses', 'sources'));
    }

    public function create()
    {
        $tid = $this->tenantId();

        $roomTypes = RoomType::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();
        $guests = Guest::where('tenant_id', $tid)->orderBy('name')->limit(50)->get();

        return view('hotel.reservations.create', compact('roomTypes', 'guests'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_type_id' => 'required|exists:room_types,id',
            'room_id' => 'nullable|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'nullable|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'discount' => 'nullable|numeric|min:0',
            'source' => 'nullable|string|max:50',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        $tid = $this->tenantId();

        // Verify guest belongs to tenant
        $guest = Guest::where('id', $data['guest_id'])->where('tenant_id', $tid)->first();
        if (!$guest) {
            return back()->withErrors(['guest_id' => 'Invalid guest.'])->withInput();
        }

        // Verify room type belongs to tenant
        $roomType = RoomType::where('id', $data['room_type_id'])->where('tenant_id', $tid)->first();
        if (!$roomType) {
            return back()->withErrors(['room_type_id' => 'Invalid room type.'])->withInput();
        }

        try {
            $reservation = $this->reservationService->createReservation([
                'tenant_id' => $tid,
                'guest_id' => $data['guest_id'],
                'room_type_id' => $data['room_type_id'],
                'room_id' => $data['room_id'] ?? null,
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'adults' => $data['adults'] ?? 1,
                'children' => $data['children'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'source' => $data['source'] ?? 'direct',
                'special_requests' => $data['special_requests'] ?? null,
                'created_by' => auth()->id(),
            ]);

            ActivityLog::record('reservation_created', "Reservation created: {$reservation->reservation_number}", $reservation, [], $reservation->toArray());

            return redirect()->route('hotel.reservations.show', $reservation)->with('success', "Reservation {$reservation->reservation_number} created successfully.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show(Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        $reservation->load(['guest', 'roomType', 'room', 'checkInOuts', 'reservationRooms.room']);

        return view('hotel.reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        $tid = $this->tenantId();

        $roomTypes = RoomType::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();
        $guests = Guest::where('tenant_id', $tid)->orderBy('name')->limit(50)->get();

        return view('hotel.reservations.edit', compact('reservation', 'roomTypes', 'guests'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'nullable|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'discount' => 'nullable|numeric|min:0',
            'source' => 'nullable|string|max:50',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        try {
            $old = $reservation->getOriginal();
            $reservation = $this->reservationService->updateReservation($reservation->id, $data);

            ActivityLog::record('reservation_updated', "Reservation updated: {$reservation->reservation_number}", $reservation, $old, $reservation->fresh()->toArray());

            return redirect()->route('hotel.reservations.show', $reservation)->with('success', "Reservation {$reservation->reservation_number} updated successfully.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        // Cancel instead of delete
        return $this->cancel(request(), $reservation);
    }

    public function confirm(Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        try {
            $reservation = $this->reservationService->confirmReservation($reservation->id);

            ActivityLog::record('reservation_confirmed', "Reservation confirmed: {$reservation->reservation_number}", $reservation);

            return back()->with('success', "Reservation {$reservation->reservation_number} confirmed.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancel(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'cancel_reason' => 'nullable|string|max:500',
        ]);

        try {
            $reservation = $this->reservationService->cancelReservation($reservation->id, $data['cancel_reason'] ?? null);

            ActivityLog::record('reservation_cancelled', "Reservation cancelled: {$reservation->reservation_number}", $reservation);

            return back()->with('success', "Reservation {$reservation->reservation_number} cancelled.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function calendar(Request $request)
    {
        $tid = $this->tenantId();

        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        // Get reservations for the month
        $reservations = Reservation::with(['guest', 'roomType', 'room'])
            ->where('tenant_id', $tid)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->whereMonth('check_in_date', '<=', $month)
            ->whereYear('check_in_date', '<=', $year)
            ->whereMonth('check_out_date', '>=', $month)
            ->whereYear('check_out_date', '>=', $year)
            ->get();

        $roomTypes = RoomType::with('rooms')->where('tenant_id', $tid)->where('is_active', true)->get();

        return view('hotel.reservations.calendar', compact('reservations', 'roomTypes', 'month', 'year'));
    }

    public function calculateRate(Request $request)
    {
        $data = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $tid = $this->tenantId();

        try {
            $calculation = $this->reservationService->calculateRate(
                $data['room_type_id'],
                $data['check_in_date'],
                $data['check_out_date'],
                $tid
            );

            return response()->json([
                'success' => true,
                'data' => $calculation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show room change form (upgrade/downgrade)
     */
    public function showRoomChange(Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        if (!in_array($reservation->status, ['confirmed', 'checked_in'])) {
            return back()->with('error', 'Can only change rooms for confirmed or checked-in reservations.');
        }

        $reservation->load(['roomType', 'room']);

        // Get available rooms for the dates
        $availableRooms = app(RoomAvailabilityService::class)->getAvailableRooms(
            $reservation->tenant_id,
            $reservation->check_in_date,
            $reservation->check_out_date
        );

        $roomTypes = RoomType::where('tenant_id', $reservation->tenant_id)
            ->where('is_active', true)
            ->with([
                'rooms' => function ($query) use ($availableRooms) {
                    $query->whereIn('id', $availableRooms->pluck('id'));
                }
            ])
            ->get();

        return view('hotel.reservations.room-change', compact('reservation', 'roomTypes', 'availableRooms'));
    }

    /**
     * Process room change
     */
    public function processRoomChange(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'to_room_id' => 'required|exists:rooms,id',
            'room_type_id' => 'required|exists:room_types,id',
            'change_type' => 'required|in:upgrade,downgrade,same_category',
            'rate_difference' => 'required|numeric',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $roomChange = $this->reservationService->processRoomChange(
                $reservation->id,
                $data['to_room_id'],
                $data['room_type_id'],
                $data['change_type'],
                $data['rate_difference'],
                $data['reason'],
                $data['notes'] ?? null
            );

            return redirect()->route('hotel.reservations.show', $reservation)
                ->with('success', "Room {$data['change_type']} processed successfully.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Request early check-in or late check-out
     */
    public function requestEarlyLate(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'request_type' => 'required|in:early_checkin,late_checkout',
            'requested_time' => 'required|date_format:Y-m-d H:i',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $request = $this->reservationService->requestEarlyLate(
                $reservation->id,
                $data['request_type'],
                $data['requested_time'],
                $data['reason'] ?? null
            );

            ActivityLog::record(
                'early_late_requested',
                "{$data['request_type']} requested for reservation {$reservation->reservation_number}",
                $reservation,
                ['requested_time' => $data['requested_time']]
            );

            return back()->with('success', "{$data['request_type']} request submitted for approval.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Approve early check-in or late check-out request
     */
    public function approveEarlyLate(EarlyLateRequest $request)
    {
        abort_unless($request->tenant_id === $this->tenantId(), 403);

        try {
            $this->reservationService->approveEarlyLateRequest($request->id);

            return back()->with('success', 'Request approved successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reject early check-in or late check-out request
     */
    public function rejectEarlyLate(Request $request, EarlyLateRequest $earlyLateRequest)
    {
        abort_unless($earlyLateRequest->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $this->reservationService->rejectEarlyLateRequest($earlyLateRequest->id, $data['rejection_reason']);

            return back()->with('success', 'Request rejected.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get pending early/late requests
     */
    public function getPendingRequests()
    {
        $requests = EarlyLateRequest::with(['reservation.guest', 'reservation.roomType'])
            ->where('tenant_id', $this->tenantId())
            ->pending()
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests->map(function ($r) {
                return [
                    'id' => $r->id,
                    'request_type' => $r->request_type,
                    'requested_time' => $r->requested_time->format('d M Y H:i'),
                    'guest_name' => $r->reservation->guest->name,
                    'reservation_number' => $r->reservation->reservation_number,
                    'reason' => $r->reason,
                ];
            }),
        ]);
    }

    /**
     * Record actual check-in
     */
    public function recordCheckIn(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'actual_time' => 'nullable|date_format:Y-m-d H:i',
        ]);

        try {
            $actualTime = isset($data['actual_time'])
                ? \Carbon\Carbon::parse($data['actual_time'])
                : now();

            $this->reservationService->recordActualCheckIn($reservation->id, $actualTime);

            return back()->with('success', 'Check-in time recorded successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Record actual check-out
     */
    public function recordCheckOut(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'actual_time' => 'nullable|date_format:Y-m-d H:i',
        ]);

        try {
            $actualTime = isset($data['actual_time'])
                ? \Carbon\Carbon::parse($data['actual_time'])
                : now();

            $this->reservationService->recordActualCheckOut($reservation->id, $actualTime);

            return back()->with('success', 'Check-out time recorded successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get room changes history for a reservation
     */
    public function getRoomChanges(Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        $changes = ReservationRoomChange::with(['fromRoom', 'toRoom', 'processor'])
            ->where('reservation_id', $reservation->id)
            ->orderByDesc('effective_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $changes->map(function ($c) {
                return [
                    'change_type' => $c->change_type,
                    'from_room' => $c->fromRoom?->number ?? 'N/A',
                    'to_room' => $c->toRoom->number,
                    'rate_difference' => $c->rate_difference,
                    'effective_date' => $c->effective_date->format('d M Y'),
                    'processed_by' => $c->processor?->name ?? 'System',
                ];
            }),
        ]);
    }
}
