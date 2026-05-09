<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\BanquetEvent;
use App\Services\BanquetService;
use Illuminate\Http\Request;

class BanquetController extends Controller
{
    protected $banquetService;

    public function __construct(BanquetService $banquetService)
    {
        $this->banquetService = $banquetService;
    }

    public function index()
    {
        $tenantId = $this->tenantId();

        $upcomingEvents = BanquetEvent::where('tenant_id', $tenantId)
            ->where('event_date', '>=', today())
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->orderBy('event_date')
            ->limit(10)
            ->get();

        $stats = [
            'total_events' => BanquetEvent::where('tenant_id', $tenantId)->count(),
            'upcoming_events' => BanquetEvent::where('tenant_id', $tenantId)
                ->where('event_date', '>=', today())
                ->whereIn('status', ['confirmed', 'in_progress'])
                ->count(),
            'month_revenue' => BanquetEvent::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->whereMonth('event_date', now()->month)
                ->sum('total_amount'),
        ];

        return view('hotel.fb.banquet.index', compact('upcomingEvents', 'stats'));
    }

    public function create()
    {
        return view('hotel.fb.banquet.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:20',
            'client_email' => 'nullable|email',
            'company_name' => 'nullable|string|max:255',
            'event_type' => 'required|in:wedding,conference,meeting,birthday,corporate,social,other',
            'event_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'expected_guests' => 'required|integer|min:1',
            'venue_room' => 'nullable|string',
            'setup_requirements' => 'nullable|string',
            'venue_rental_fee' => 'nullable|numeric|min:0',
            'assigned_coordinator' => 'nullable|exists:users,id',
            'internal_notes' => 'nullable|string',
        ]);

        $event = $this->banquetService->createEvent($validated);

        return redirect()->route('hotel.fb.banquet.show', $event->id)
            ->with('success', 'Banquet event created successfully');
    }

    public function show(int $id)
    {
        $event = BanquetEvent::with(['orders.menuItem', 'coordinator', 'clientGuest'])->findOrFail($id);

        return view('hotel.fb.banquet.show', compact('event'));
    }

    public function confirmEvent(Request $request, int $id)
    {
        $validated = $request->validate([
            'deposit_amount' => 'nullable|numeric|min:0',
        ]);

        $event = $this->banquetService->confirmEvent($id, $validated['deposit_amount'] ?? 0);

        return back()->with('success', 'Event confirmed successfully');
    }

    public function completeEvent(int $id)
    {
        $event = $this->banquetService->completeEvent($id);

        return back()->with('success', 'Event marked as completed');
    }

    public function cancelEvent(int $id)
    {
        $event = $this->banquetService->cancelEvent($id);

        return back()->with('success', 'Event cancelled');
    }

    public function updateGuestCount(Request $request, int $id)
    {
        $validated = $request->validate([
            'confirmed_guests' => 'required|integer|min:1',
        ]);

        $event = $this->banquetService->updateGuestCount($id, $validated['confirmed_guests']);

        return back()->with('success', 'Guest count updated');
    }
}
