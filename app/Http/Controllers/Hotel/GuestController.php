<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Guest;
use App\Models\GuestPreference;
use App\Services\GuestPreferenceService;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GuestController extends Controller
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

    public function index(Request $request)
    {
        $tid = $this->tenantId();

        $query = Guest::where('tenant_id', $tid);

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                    ->orWhere('email', 'like', "%$s%")
                    ->orWhere('phone', 'like', "%$s%");
            });
        }

        $guests = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('hotel.guests.index', compact('guests'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'id_type' => 'nullable|string|max:50',
            'id_number' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $tid = $this->tenantId();

        // Generate guest code
        $count = Guest::where('tenant_id', $tid)->count() + 1;
        $guestCode = 'GST-' . str_pad($count, 5, '0', STR_PAD_LEFT);

        $guest = Guest::create([
            'tenant_id' => $tid,
            'guest_code' => $guestCode,
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?? null,
            'id_type' => $data['id_type'] ?? null,
            'id_number' => $data['id_number'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'notes' => $data['notes'] ?? null,
            'total_stays' => 0,
        ]);

        ActivityLog::record('guest_created', "Guest created: {$guest->name} ({$guest->guest_code})", $guest, [], $guest->toArray());

        return back()->with('success', "Guest {$guest->name} created successfully.");
    }

    public function show(Guest $guest)
    {
        abort_unless($guest->tenant_id === $this->tenantId(), 403);

        $reservations = $this->reservationService->getReservationsByGuest($guest->id);

        return view('hotel.guests.show', compact('guest', 'reservations'));
    }

    public function update(Request $request, Guest $guest)
    {
        abort_unless($guest->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'id_type' => 'nullable|string|max:50',
            'id_number' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $old = $guest->getOriginal();
        $guest->update($data);

        ActivityLog::record('guest_updated', "Guest updated: {$guest->name}", $guest, $old, $guest->fresh()->toArray());

        return back()->with('success', "Guest {$guest->name} updated successfully.");
    }

    public function destroy(Guest $guest)
    {
        abort_unless($guest->tenant_id === $this->tenantId(), 403);

        // Check for active reservations
        if ($guest->reservations()->whereIn('status', ['confirmed', 'checked_in'])->exists()) {
            return back()->with('error', 'Cannot delete guest with active reservations.');
        }

        ActivityLog::record('guest_deleted', "Guest deleted: {$guest->name} ({$guest->guest_code})", $guest, $guest->toArray());
        $guest->delete();

        return back()->with('success', 'Guest deleted successfully.');
    }

    public function history(Guest $guest)
    {
        abort_unless($guest->tenant_id === $this->tenantId(), 403);

        $reservations = $this->reservationService->getReservationsByGuest($guest->id);

        return response()->json([
            'success' => true,
            'data' => $reservations->map(function ($r) {
                return [
                    'id' => $r->id,
                    'reservation_number' => $r->reservation_number,
                    'room_type' => $r->roomType?->name,
                    'room' => $r->room?->number,
                    'check_in_date' => $r->check_in_date->toDateString(),
                    'check_out_date' => $r->check_out_date->toDateString(),
                    'status' => $r->status,
                    'grand_total' => $r->grand_total,
                ];
            }),
        ]);
    }

    public function search(Request $request)
    {
        $tid = $this->tenantId();

        $query = $request->get('q', '');

        $guests = Guest::where('tenant_id', $tid)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('guest_code', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $guests->map(function ($g) {
                return [
                    'id' => $g->id,
                    'name' => $g->name,
                    'email' => $g->email,
                    'phone' => $g->phone,
                    'guest_code' => $g->guest_code,
                ];
            }),
        ]);
    }

    /**
     * Show guest preferences
     */
    public function preferences(Guest $guest)
    {
        abort_unless($guest->tenant_id === $this->tenantId(), 403);

        $preferences = $this->preferenceService->getGuestPreferences($guest);

        return view('hotel.guests.preferences', compact('guest', 'preferences'));
    }

    /**
     * Store guest preference
     */
    public function storePreference(Request $request, Guest $guest)
    {
        abort_unless($guest->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'category' => 'required|string|max:50',
            'preference_key' => 'required|string|max:100',
            'preference_value' => 'nullable|string|max:500',
            'priority' => 'nullable|integer|min:1|max:3',
            'is_auto_applied' => 'nullable|boolean',
        ]);

        $preference = $this->preferenceService->setPreference(
            $guest,
            $data['category'],
            $data['preference_key'],
            $data['preference_value'] ?? null,
            $data['priority'] ?? 1,
            $data['is_auto_applied'] ?? true
        );

        ActivityLog::record(
            'guest_preference_added',
            "Preference added for {$guest->name}: {$data['category']}.{$data['preference_key']}",
            $guest,
            ['preference' => $data]
        );

        return back()->with('success', 'Preference saved successfully.');
    }

    /**
     * Update guest preference
     */
    public function updatePreference(Request $request, Guest $guest, int $preferenceId)
    {
        abort_unless($guest->tenant_id === $this->tenantId(), 403);

        $preference = GuestPreference::findOrFail($preferenceId);

        $data = $request->validate([
            'preference_value' => 'nullable|string|max:500',
            'priority' => 'nullable|integer|min:1|max:3',
            'is_auto_applied' => 'nullable|boolean',
        ]);

        $preference->update($data);
        $this->preferenceService->refreshGuestPreferencesCache($guest);

        ActivityLog::record(
            'guest_preference_updated',
            "Preference updated for {$guest->name}",
            $preference,
            $data
        );

        return back()->with('success', 'Preference updated successfully.');
    }

    /**
     * Delete guest preference
     */
    public function destroyPreference(Guest $guest, int $preferenceId)
    {
        abort_unless($guest->tenant_id === $this->tenantId(), 403);

        $preference = GuestPreference::findOrFail($preferenceId);
        $this->preferenceService->deletePreference($guest, $preference->category, $preference->preference_key);

        ActivityLog::record(
            'guest_preference_deleted',
            "Preference deleted for {$guest->name}",
            $preference
        );

        return back()->with('success', 'Preference deleted successfully.');
    }

    /**
     * Award loyalty points to guest
     */
    public function awardPoints(Request $request, Guest $guest)
    {
        abort_unless($guest->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'points' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        $this->preferenceService->awardPoints($guest, $data['points'], $data['reason'] ?? '');

        return back()->with('success', "{$data['points']} points awarded to {$guest->name}.");
    }

    /**
     * Update guest VIP level manually
     */
    public function updateVipLevel(Request $request, Guest $guest)
    {
        abort_unless($guest->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'vip_level' => ['required', Rule::in(Guest::VIP_LEVELS)],
        ]);

        $oldLevel = $guest->vip_level;
        $guest->update(['vip_level' => $data['vip_level']]);

        ActivityLog::record(
            'vip_level_manual_update',
            "VIP level manually changed for {$guest->name} from $oldLevel to {$data['vip_level']}",
            $guest,
            ['old_level' => $oldLevel, 'new_level' => $data['vip_level']]
        );

        return back()->with('success', "VIP level updated to {$data['vip_level']}.");
    }

    /**
     * Get suggested preferences based on guest history
     */
    public function getSuggestions(Guest $guest)
    {
        abort_unless($guest->tenant_id === $this->tenantId(), 403);

        $suggestions = $this->preferenceService->getSuggestedPreferences($guest);
        $loyaltyRecommendations = $this->preferenceService->getLoyaltyRecommendations($guest);

        return response()->json([
            'success' => true,
            'data' => [
                'suggestions' => $suggestions,
                'loyalty_recommendations' => $loyaltyRecommendations,
            ],
        ]);
    }

    /**
     * Apply a suggested preference
     */
    public function applySuggestion(Request $request, Guest $guest)
    {
        abort_unless($guest->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'category' => 'required|string|max:50',
            'preference_key' => 'required|string|max:100',
            'preference_value' => 'nullable|string|max:500',
        ]);

        $preference = $this->preferenceService->setPreference(
            $guest,
            $data['category'],
            $data['preference_key'],
            $data['preference_value'] ?? null,
            2,
            true
        );

        ActivityLog::record(
            'guest_preference_applied_from_suggestion',
            "Applied suggested preference for {$guest->name}: {$data['category']}.{$data['preference_key']}",
            $guest,
            ['preference' => $data]
        );

        return back()->with('success', 'Preference applied successfully.');
    }

    /**
     * Redeem loyalty points for reward
     */
    public function redeemPoints(Request $request, Guest $guest)
    {
        abort_unless($guest->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'points' => 'required|integer|min:1',
            'reward_name' => 'required|string|max:255',
            'reason' => 'nullable|string|max:500',
        ]);

        $success = $this->preferenceService->redeemPoints(
            $guest,
            $data['points'],
            "Redeemed: {$data['reward_name']} - {$data['reason']}"
        );

        if (!$success) {
            return back()->with('error', 'Insufficient loyalty points.');
        }

        return back()->with('success', "{$data['reward_name']} redeemed successfully!");
    }
    /**
     * Show the form for creating.
     * Route: hotel/guests/create
     */
    public function create()
    {
        $this->authorize('create', self::class);

        return view('hotel.guest.create');
    }
    /**
     * Show the form for editing.
     * Route: hotel/guests/{guest}/edit
     */
    public function edit($model)
    {
        $this->authorize('update', $model);

        return view('hotel.guest.edit', compact('model'));
    }
}
