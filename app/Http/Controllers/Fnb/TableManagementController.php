<?php

namespace App\Http\Controllers\Fnb;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use App\Models\TableReservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TableManagementController extends Controller
{
    /**
     * Display table management dashboard
     */
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        $tables = RestaurantTable::where('tenant_id', $tenantId)
            ->with([
                'reservations' => function ($query) {
                    $query->whereDate('reservation_date', today())
                        ->whereIn('status', ['confirmed', 'seated'])
                        ->orderBy('reservation_time');
                },
            ])
            ->orderBy('table_number')
            ->get();

        $stats = [
            'total_tables' => $tables->count(),
            'available' => $tables->where('status', 'available')->count(),
            'occupied' => $tables->where('status', 'occupied')->count(),
            'reserved' => $tables->filter(fn ($t) => $t->reservations->isNotEmpty())->count(),
            'today_reservations' => TableReservation::where('tenant_id', $tenantId)
                ->whereDate('reservation_date', today())
                ->count(),
        ];

        return view('fnb.tables.index', compact('tables', 'stats'));
    }

    /**
     * Show reservations for a table
     */
    public function showReservations(RestaurantTable $table)
    {
        $this->authorizeAccess($table);

        $reservations = TableReservation::where('table_id', $table->id)
            ->whereDate('reservation_date', '>=', today())
            ->orderBy('reservation_date')
            ->orderBy('reservation_time')
            ->get();

        return view('fnb.tables.reservations', compact('table', 'reservations'));
    }

    /**
     * Store new reservation
     */
    public function storeReservation(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:restaurant_tables,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'party_size' => 'required|integer|min:1',
            'reservation_date' => 'required|date',
            'reservation_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:30|max:300',
            'special_requests' => 'nullable|string',
            'deposit_amount' => 'nullable|numeric|min:0',
        ]);

        // Check table availability
        $conflict = TableReservation::where('table_id', $validated['table_id'])
            ->whereDate('reservation_date', $validated['reservation_date'])
            ->whereIn('status', ['confirmed', 'seated'])
            ->get()
            ->filter(function ($existing) use ($validated) {
                $newStart = Carbon::parse($validated['reservation_time']);
                $newEnd = $newStart->copy()->addMinutes($validated['duration_minutes']);

                $existingStart = Carbon::parse($existing->reservation_time);
                $existingEnd = $existingStart->copy()->addMinutes($existing->duration_minutes);

                return $newStart < $existingEnd && $newEnd > $existingStart;
            });

        if ($conflict->isNotEmpty()) {
            return back()->withErrors(['reservation_time' => 'Table is not available at this time.']);
        }

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['status'] = 'confirmed';
        $validated['created_by'] = auth()->id();

        TableReservation::create($validated);

        return redirect()->route('fnb.tables.index')
            ->with('success', 'Reservation created successfully');
    }

    /**
     * Update reservation status
     */
    public function updateReservationStatus(Request $request, TableReservation $reservation)
    {
        $this->authorizeAccess($reservation);

        $validated = $request->validate([
            'status' => 'required|in:confirmed,seated,completed,cancelled,no_show',
        ]);

        $reservation->update(['status' => $validated['status']]);

        // Update table status
        if ($validated['status'] === 'seated') {
            $reservation->table?->occupy();
        } elseif (in_array($validated['status'], ['completed', 'cancelled', 'no_show'])) {
            $reservation->table?->release();
        }

        return back()->with('success', 'Reservation status updated');
    }

    /**
     * Cancel reservation
     */
    public function cancelReservation(TableReservation $reservation)
    {
        $this->authorizeAccess($reservation);

        $reservation->update(['status' => 'cancelled']);
        $reservation->table?->release();

        return back()->with('success', 'Reservation cancelled');
    }

    /**
     * Get available tables for date/time
     */
    public function getAvailableTables(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'party_size' => 'required|integer|min:1',
            'duration' => 'required|integer|min:30',
        ]);

        $tenantId = auth()->user()->tenant_id;

        // Get all active tables with sufficient capacity
        $availableTables = RestaurantTable::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('capacity', '>=', $validated['party_size'])
            ->where('status', 'available')
            ->get();

        // Filter out tables with conflicting reservations
        $bookedTableIds = TableReservation::where('tenant_id', $tenantId)
            ->whereDate('reservation_date', $validated['date'])
            ->whereIn('status', ['confirmed', 'seated'])
            ->get()
            ->filter(function ($reservation) use ($validated) {
                $newStart = Carbon::parse($validated['time']);
                $newEnd = $newStart->copy()->addMinutes($validated['duration']);

                $existingStart = Carbon::parse($reservation->reservation_time);
                $existingEnd = $existingStart->copy()->addMinutes($reservation->duration_minutes);

                return $newStart < $existingEnd && $newEnd > $existingStart;
            })
            ->pluck('table_id');

        $availableTables = $availableTables->whereNotIn('id', $bookedTableIds);

        return response()->json([
            'tables' => $availableTables,
            'count' => $availableTables->count(),
        ]);
    }

    private function authorizeAccess($model): void
    {
        if ($model->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access');
        }
    }
}
