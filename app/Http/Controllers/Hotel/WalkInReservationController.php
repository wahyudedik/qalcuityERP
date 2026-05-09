<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\RoomType;
use App\Models\WalkInReservation;
use App\Services\GuestPreferenceService;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalkInReservationController extends Controller
{
    private ReservationService $reservationService;

    private GuestPreferenceService $preferenceService;

    public function __construct(
        ReservationService $reservationService,
        GuestPreferenceService $preferenceService
    ) {
        $this->reservationService = $reservationService;
        $this->preferenceService = $preferenceService;
    }

    // tenantId() inherited from parent Controller

    /**
     * Display list of walk-in reservations
     */
    public function index(Request $request)
    {
        $tid = $this->tenantId();

        $query = WalkInReservation::with(['reservation.guest', 'reservation.roomType', 'handler'])
            ->where('tenant_id', $tid);

        if ($request->source) {
            $query->where('source', $request->source);
        }

        if ($request->date_from) {
            $query->whereDate('arrival_time', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('arrival_time', '<=', $request->date_to);
        }

        $walkIns = $query->orderByDesc('arrival_time')->paginate(20)->withQueryString();

        $sources = ['phone', 'email', 'website', 'ota', 'referral', 'street_walk'];

        return view('hotel.walk-ins.index', compact('walkIns', 'sources'));
    }

    /**
     * Show form to create walk-in reservation
     */
    public function create()
    {
        $roomTypes = RoomType::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('hotel.walk-ins.create', compact('roomTypes'));
    }

    /**
     * Process walk-in reservation creation
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // Guest information
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'guest_id_type' => 'nullable|in:ktp,passport,sim',
            'guest_id_number' => 'nullable|string|max:100',

            // Reservation information
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date|today_or_after',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'nullable|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'expected_arrival_time' => 'nullable|date_format:H:i',

            // Walk-in specific
            'source' => 'required|in:phone,email,website,ota,referral,street_walk',
            'special_circumstances' => 'nullable|string|max:500',
        ]);

        $tid = $this->tenantId();

        return DB::transaction(function () use ($data, $tid) {
            // Find or create guest
            $guest = null;
            if ($data['guest_email']) {
                $guest = Guest::where('email', $data['guest_email'])
                    ->where('tenant_id', $tid)
                    ->first();
            }

            $isNewGuest = ! $guest;

            if (! $guest) {
                // Create new guest
                $count = Guest::where('tenant_id', $tid)->count() + 1;
                $guestCode = 'GST-'.str_pad($count, 5, '0', STR_PAD_LEFT);

                $guest = Guest::create([
                    'tenant_id' => $tid,
                    'guest_code' => $guestCode,
                    'name' => $data['guest_name'],
                    'email' => $data['guest_email'] ?? null,
                    'phone' => $data['guest_phone'] ?? null,
                    'id_type' => $data['guest_id_type'] ?? null,
                    'id_number' => $data['guest_id_number'] ?? null,
                    'total_stays' => 0,
                ]);
            }

            // Verify room type
            $roomType = RoomType::where('id', $data['room_type_id'])
                ->where('tenant_id', $tid)
                ->where('is_active', true)
                ->first();

            if (! $roomType) {
                throw new \Exception('Invalid room type selected.');
            }

            // Create reservation
            $reservationData = [
                'tenant_id' => $tid,
                'guest_id' => $guest->id,
                'room_type_id' => $data['room_type_id'],
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'adults' => $data['adults'] ?? 1,
                'children' => $data['children'] ?? 0,
                'expected_arrival_time' => $data['expected_arrival_time'] ?? now()->format('H:i'),
                'source' => $data['source'],
                'is_walk_in' => true,
                'is_vip' => $guest->isVip(),
                'created_by' => auth()->id(),
            ];

            // Apply guest preferences if returning guest
            if (! $isNewGuest) {
                $this->preferenceService->applyPreferencesToReservation($guest, $reservationData);
            }

            $reservation = $this->reservationService->createReservation($reservationData);

            // Create walk-in record
            $walkInNumber = WalkInReservation::generateWalkInNumber($tid);
            $walkIn = WalkInReservation::create([
                'tenant_id' => $tid,
                'reservation_id' => $reservation->id,
                'guest_id' => $guest->id,
                'walk_in_number' => $walkInNumber,
                'arrival_time' => now(),
                'source' => $data['source'],
                'is_new_guest' => $isNewGuest,
                'special_circumstances' => $data['special_circumstances'] ?? null,
                'handled_by' => auth()->id(),
            ]);

            ActivityLog::record(
                'walk_in_reservation_created',
                "Walk-in reservation created: {$walkInNumber} for {$guest->name}",
                $reservation,
                [
                    'walk_in_number' => $walkInNumber,
                    'is_new_guest' => $isNewGuest,
                    'source' => $data['source'],
                ]
            );

            // Award bonus points for walk-in if VIP
            if ($guest->isVip()) {
                $this->preferenceService->awardPoints($guest, 50, 'VIP walk-in reservation');
            }

            return redirect()->route('hotel.reservations.show', $reservation)
                ->with('success', "Walk-in reservation {$reservation->reservation_number} created successfully.");
        });
    }

    /**
     * Quick check-in for walk-in guest
     */
    public function quickCheckIn(Request $request)
    {
        $data = $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_phone' => 'required|string|max:20',
            'room_type_id' => 'required|exists:room_types,id',
            'nights' => 'required|integer|min:1',
            'rate_per_night' => 'required|numeric|min:0',
        ]);

        $tid = $this->tenantId();

        return DB::transaction(function () use ($data, $tid) {
            // Find existing guest by phone
            $guest = Guest::where('phone', $data['guest_phone'])
                ->where('tenant_id', $tid)
                ->first();

            $isNewGuest = ! $guest;

            if (! $guest) {
                $count = Guest::where('tenant_id', $tid)->count() + 1;
                $guestCode = 'GST-'.str_pad($count, 5, '0', STR_PAD_LEFT);

                $guest = Guest::create([
                    'tenant_id' => $tid,
                    'guest_code' => $guestCode,
                    'name' => $data['guest_name'],
                    'phone' => $data['guest_phone'],
                    'total_stays' => 0,
                ]);
            }

            // Get room type
            $roomType = RoomType::where('id', $data['room_type_id'])
                ->where('tenant_id', $tid)
                ->where('is_active', true)
                ->first();

            if (! $roomType) {
                throw new \Exception('Invalid room type.');
            }

            // Create same-day reservation
            $checkIn = today();
            $checkOut = today()->addDays($data['nights']);
            $nights = $checkIn->diffInDays($checkOut);
            $totalAmount = $data['rate_per_night'] * $nights;
            $taxRate = 11; // Default tax
            $taxAmount = round($totalAmount * ($taxRate / 100), 2);
            $grandTotal = round($totalAmount + $taxAmount, 2);

            $reservationNumber = 'WI-'.date('Ymd').'-'.str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $reservation = Reservation::create([
                'tenant_id' => $tid,
                'guest_id' => $guest->id,
                'room_type_id' => $roomType->id,
                'reservation_number' => $reservationNumber,
                'status' => 'confirmed',
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'adults' => 1,
                'children' => 0,
                'nights' => $nights,
                'rate_per_night' => $data['rate_per_night'],
                'total_amount' => $totalAmount,
                'discount' => 0,
                'tax' => $taxAmount,
                'grand_total' => $grandTotal,
                'source' => 'street_walk',
                'is_walk_in' => true,
                'is_vip' => $guest->isVip(),
                'created_by' => auth()->id(),
            ]);

            // Create walk-in record
            $walkInNumber = WalkInReservation::generateWalkInNumber($tid);
            $walkIn = WalkInReservation::create([
                'tenant_id' => $tid,
                'reservation_id' => $reservation->id,
                'guest_id' => $guest->id,
                'walk_in_number' => $walkInNumber,
                'arrival_time' => now(),
                'source' => 'street_walk',
                'is_new_guest' => $isNewGuest,
                'handled_by' => auth()->id(),
            ]);

            ActivityLog::record(
                'quick_check_in',
                "Quick check-in: {$walkInNumber} for {$guest->name}",
                $reservation,
                ['walk_in_number' => $walkInNumber]
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'reservation_id' => $reservation->id,
                    'reservation_number' => $reservation->reservation_number,
                    'guest_id' => $guest->id,
                    'guest_name' => $guest->name,
                ],
            ]);
        });
    }

    /**
     * Get walk-in statistics
     */
    public function statistics(Request $request)
    {
        $tid = $this->tenantId();
        $startDate = $request->start_date ?? today()->startOfMonth();
        $endDate = $request->end_date ?? today()->endOfDay();

        $totalWalkIns = WalkInReservation::where('tenant_id', $tid)
            ->whereBetween('arrival_time', [$startDate, $endDate])
            ->count();

        $newGuests = WalkInReservation::where('tenant_id', $tid)
            ->whereBetween('arrival_time', [$startDate, $endDate])
            ->where('is_new_guest', true)
            ->count();

        $sources = WalkInReservation::where('tenant_id', $tid)
            ->whereBetween('arrival_time', [$startDate, $endDate])
            ->selectRaw('source, COUNT(*) as count')
            ->groupBy('source')
            ->get()
            ->pluck('count', 'source')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'total_walk_ins' => $totalWalkIns,
                'new_guests' => $newGuests,
                'returning_guests' => $totalWalkIns - $newGuests,
                'sources' => $sources,
            ],
        ]);
    }

    /**
     * Display the specified resource.
     * Route: hotel/walk-ins/{walk_in}
     */
    public function show($model)
    {
        $this->authorize('view', $model);

        return view('hotel.walk-in-reservation.show', compact('model'));
    }

    /**
     * Show the form for editing.
     * Route: hotel/walk-ins/{walk_in}/edit
     */
    public function edit($model)
    {
        $this->authorize('update', $model);

        return view('hotel.walk-in-reservation.edit', compact('model'));
    }

    /**
     * Update the specified resource.
     * Route: hotel/walk-ins/{walk_in}
     */
    public function update(Request $request, $model)
    {
        $this->authorize('update', $model);

        $validated = $request->validate([
            // TODO: Add validation rules
        ]);

        $model->update($validated);

        return redirect()->route('hotel.walk-ins.update')
            ->with('success', 'Updated successfully.');
    }

    /**
     * Remove the specified resource.
     * Route: hotel/walk-ins/{walk_in}
     */
    public function destroy($model)
    {
        $this->authorize('delete', $model);

        $model->delete();

        return back()->with('success', 'Deleted successfully.');
    }
}
