<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\GroupBooking;
use App\Models\Guest;
use App\Models\Reservation;
use App\Services\GroupBookingService;
use Illuminate\Http\Request;

class GroupBookingController extends Controller
{
    private GroupBookingService $groupService;

    public function __construct(GroupBookingService $groupService)
    {
        $this->groupService = $groupService;
    }

    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
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
        $data['created_by'] = auth()->id();

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
}
