<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\GroupBooking;
use App\Models\Guest;
use App\Models\Reservation;
use App\Services\GroupBookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupBookingController extends Controller
{
    private GroupBookingService $groupService;

    public function __construct(GroupBookingService $groupService)
    {
        $this->groupService = $groupService;
    }

    private function tenantId(): int
    {
        return Auth::user()->tenant_id ?? abort(401, 'Unauthenticated.');
    }

    private function userId(): int
    {
        return Auth::id() ?? abort(401, 'Unauthenticated.');
    }

    /**
     * Display list of group bookings
     */
    public function index(Request $request)
    {
        $tid = $this->tenantId();

        $query = GroupBooking::with(['organizer', 'reservations'])
            ->where('tenant_id', $tid);

        // Filters
        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('group_name', 'like', "%$s%")
                    ->orWhere('group_code', 'like', "%$s%");
            });
        }

        $groupBookings = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $types = ['corporate', 'family', 'tour', 'event', 'government', 'other'];
        $statuses = ['pending', 'confirmed', 'active', 'completed', 'cancelled'];

        return view('hotel.group-bookings.index', compact('groupBookings', 'types', 'statuses'));
    }

    /**
     * Show form to create new group booking
     */
    public function create()
    {
        $guests = Guest::where('tenant_id', $this->tenantId())
            ->orderBy('name')
            ->limit(100)
            ->get();

        return view('hotel.group-bookings.create', compact('guests'));
    }

    /**
     * Store new group booking
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'organizer_guest_id' => 'required|exists:guests,id',
            'group_name' => 'required|string|max:255',
            'type' => 'required|in:corporate,family,tour,event,government,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_rooms' => 'nullable|integer|min:1',
            'total_guests' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'benefits' => 'nullable|array',
        ]);

        $data['tenant_id'] = $this->tenantId();
        $data['created_by'] = $this->userId();

        try {
            $groupBooking = $this->groupService->createGroupBooking($data);

            return redirect()->route('hotel.group-bookings.show', $groupBooking)
                ->with('success', "Group booking {$groupBooking->group_code} created successfully.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display group booking details
     */
    public function show(GroupBooking $groupBooking)
    {
        abort_unless($groupBooking->tenant_id === $this->tenantId(), 403);

        $groupBooking->load(['organizer', 'reservations.guest', 'creator']);
        $reservations = $this->groupService->getGroupReservations($groupBooking->id);

        return view('hotel.group-bookings.show', compact('groupBooking', 'reservations'));
    }

    /**
     * Add reservation to group
     */
    public function addReservation(Request $request, GroupBooking $groupBooking)
    {
        abort_unless($groupBooking->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
        ]);

        try {
            $this->groupService->addReservationToGroup($groupBooking->id, $data['reservation_id']);

            return back()->with('success', 'Reservation added to group successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove reservation from group
     */
    public function removeReservation(Reservation $reservation)
    {
        abort_unless($reservation->groupBooking->tenant_id === $this->tenantId(), 403);

        try {
            $this->groupService->removeReservationFromGroup($reservation->id);

            return back()->with('success', 'Reservation removed from group successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Confirm group booking
     */
    public function confirm(GroupBooking $groupBooking)
    {
        abort_unless($groupBooking->tenant_id === $this->tenantId(), 403);

        try {
            $this->groupService->confirmGroupBooking($groupBooking->id);

            return back()->with('success', "Group booking {$groupBooking->group_code} confirmed.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel group booking
     */
    public function cancel(Request $request, GroupBooking $groupBooking)
    {
        abort_unless($groupBooking->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        try {
            $this->groupService->cancelGroupBooking($groupBooking->id, $data['cancel_reason']);

            return back()->with('success', "Group booking {$groupBooking->group_code} cancelled.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Process payment for group booking
     */
    public function processPayment(Request $request, GroupBooking $groupBooking)
    {
        abort_unless($groupBooking->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
        ]);

        try {
            $this->groupService->processPayment(
                $groupBooking->id,
                $data['amount'],
                $data['payment_method']
            );

            return back()->with('success', "Payment of {$data['amount']} processed successfully.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Add benefit to group booking
     */
    public function addBenefit(Request $request, GroupBooking $groupBooking)
    {
        abort_unless($groupBooking->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'benefit' => 'required|string|max:255',
        ]);

        try {
            $this->groupService->addBenefit($groupBooking->id, $data['benefit']);

            return back()->with('success', 'Benefit added successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Search groups
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $groups = $this->groupService->searchGroups($query, $this->tenantId());

        return response()->json([
            'success' => true,
            'data' => $groups->map(function ($g) {
                return [
                    'id' => $g->id,
                    'group_code' => $g->group_code,
                    'group_name' => $g->group_name,
                    'status' => $g->status,
                ];
            }),
        ]);
    }
    /**
     * Show the form for editing.
     * Route: hotel/group-bookings/{group_booking}/edit
     */
    public function edit($model)
    {
        $this->authorize('update', $model);

        return view('hotel.group-booking.edit', compact('model'));
    }
    /**
     * Update the specified resource.
     * Route: hotel/group-bookings/{group_booking}
     */
    public function update(Request $request, $model)
    {
        $this->authorize('update', $model);

        $validated = $request->validate([
            // TODO: Add validation rules
        ]);

        $model->update($validated);

        return redirect()->route('hotel.group-bookings.update')
            ->with('success', 'Updated successfully.');
    }
    /**
     * Remove the specified resource.
     * Route: hotel/group-bookings/{group_booking}
     */
    public function destroy($model)
    {
        $this->authorize('delete', $model);

        $model->delete();

        return back()->with('success', 'Deleted successfully.');
    }

    /**
     * Create room block with multiple individual reservations
     */
    public function createRoomBlock(Request $request, GroupBooking $groupBooking)
    {
        abort_unless($groupBooking->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'rooms' => 'required|array|min:1',
            'rooms.*.room_type_id' => 'required|exists:room_types,id',
            'rooms.*.room_id' => 'nullable|exists:rooms,id',
            'rooms.*.guest_id' => 'nullable|exists:guests,id',
            'rooms.*.guest_first_name' => 'nullable|string|max:255',
            'rooms.*.guest_last_name' => 'nullable|string|max:255',
            'rooms.*.guest_email' => 'nullable|email',
            'rooms.*.guest_phone' => 'nullable|string',
            'rooms.*.adults' => 'required|integer|min:1',
            'rooms.*.children' => 'nullable|integer|min:0',
            'rooms.*.rate_per_night' => 'required|numeric|min:0',
            'rooms.*.discount' => 'nullable|numeric|min:0',
            'rooms.*.special_requests' => 'nullable|string',
        ]);

        try {
            $reservations = $this->groupService->createIndividualReservations(
                $groupBooking->id,
                $data['rooms']
            );

            return redirect()->route('hotel.group-bookings.show', $groupBooking)
                ->with('success', "Created {$reservations->count()} individual reservations for group.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Show billing summary for group
     */
    public function billing(GroupBooking $groupBooking)
    {
        abort_unless($groupBooking->tenant_id === $this->tenantId(), 403);

        $billingSummary = $this->groupService->generateGroupBillingSummary($groupBooking->id);
        $splitDetails = $this->groupService->splitGroupBill($groupBooking->id);

        return view('hotel.group-bookings.billing', compact('groupBooking', 'billingSummary', 'splitDetails'));
    }

    /**
     * Split group bill among members
     */
    public function splitBill(GroupBooking $groupBooking)
    {
        abort_unless($groupBooking->tenant_id === $this->tenantId(), 403);

        $splitDetails = $this->groupService->splitGroupBill($groupBooking->id);

        return response()->json([
            'success' => true,
            'data' => $splitDetails,
        ]);
    }

    /**
     * Process master payment for entire group
     */
    public function groupPayment(Request $request, GroupBooking $groupBooking)
    {
        abort_unless($groupBooking->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,credit_card,debit_card,transfer,qris',
            'reference' => 'nullable|string|max:255',
        ]);

        try {
            $updatedGroup = $this->groupService->processGroupPayment(
                $groupBooking->id,
                $data['amount'],
                $data['method'],
                $data['reference'] ?? null
            );

            return redirect()->route('hotel.group-bookings.billing', $groupBooking)
                ->with('success', "Payment of Rp " . number_format($data['amount'], 0, ',', '.') . " processed successfully.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Check in individual group member
     */
    public function checkInMember(Request $request, GroupBooking $groupBooking, Reservation $reservation)
    {
        abort_unless($groupBooking->tenant_id === $this->tenantId(), 403);
        abort_unless($reservation->group_booking_id === $groupBooking->id, 403);

        $data = $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
            'deposit_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            $checkInOut = $this->groupService->checkInGroupMember($reservation->id, [
                'room_id' => $data['room_id'] ?? null,
                'deposit_amount' => $data['deposit_amount'] ?? null,
                'notes' => $data['notes'] ?? null,
                'processed_by' => $this->userId(),
            ]);

            // Activate group if not already active
            if ($groupBooking->status === 'confirmed') {
                $this->groupService->activateGroupBooking($groupBooking->id);
            }

            return back()->with('success', "Guest {$reservation->guest->full_name} checked in successfully.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Check out individual group member
     */
    public function checkOutMember(Request $request, GroupBooking $groupBooking, Reservation $reservation)
    {
        abort_unless($groupBooking->tenant_id === $this->tenantId(), 403);
        abort_unless($reservation->group_booking_id === $groupBooking->id, 403);

        $data = $request->validate([
            'payment_method' => 'required|in:cash,credit_card,debit_card,transfer,qris',
            'amount_paid' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            $checkInOut = $this->groupService->checkOutGroupMember($reservation->id, [
                'notes' => $data['notes'] ?? null,
                'processed_by' => $this->userId(),
            ]);

            return back()->with('success', "Guest {$reservation->guest->full_name} checked out successfully.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
