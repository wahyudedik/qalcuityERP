<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SpaBooking;
use App\Models\SpaBookingItem;
use App\Models\SpaPackage;
use App\Models\SpaPackageItem;
use App\Models\SpaProductSale;
use App\Models\SpaTherapist;
use App\Models\SpaTreatment;
use App\Models\TherapistSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpaController extends Controller
{
    // tenantId() inherited from parent Controller

    /**
     * Spa Dashboard
     */
    public function dashboard()
    {
        $today = today();

        $stats = [
            'today_bookings' => SpaBooking::where('tenant_id', $this->tenantId())
                ->whereDate('booking_date', $today)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->count(),
            'today_revenue' => SpaBooking::where('tenant_id', $this->tenantId())
                ->whereDate('booking_date', $today)
                ->where('status', 'completed')
                ->sum('total_amount'),
            'pending_bookings' => SpaBooking::where('tenant_id', $this->tenantId())
                ->where('status', 'pending')
                ->count(),
            'available_therapists' => SpaTherapist::where('tenant_id', $this->tenantId())
                ->where('status', 'available')
                ->where('is_active', true)
                ->count(),
        ];

        $todayBookings = SpaBooking::where('tenant_id', $this->tenantId())
            ->whereDate('booking_date', $today)
            ->with(['therapist', 'treatment', 'package', 'guest'])
            ->orderBy('start_time')
            ->get();

        $upcomingBookings = SpaBooking::where('tenant_id', $this->tenantId())
            ->where('booking_date', '>', $today)
            ->where('status', 'confirmed')
            ->with(['therapist', 'treatment', 'package'])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        return view('hotel.spa.dashboard', compact('stats', 'todayBookings', 'upcomingBookings'));
    }

    /**
     * Treatments Management
     */
    public function treatments()
    {
        $treatments = SpaTreatment::where('tenant_id', $this->tenantId())
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $categories = SpaTreatment::where('tenant_id', $this->tenantId())
            ->distinct()
            ->pluck('category');

        return view('hotel.spa.treatments.index', compact('treatments', 'categories'));
    }

    /**
     * Store treatment
     */
    public function storeTreatment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'duration_minutes' => 'required|integer|min:15',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'preparation_time' => 'nullable|integer|min:0',
            'cleanup_time' => 'nullable|integer|min:0',
            'max_daily_bookings' => 'nullable|integer|min:1',
            'requires_consultation' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = $this->tenantId();
        $validated['cost'] = $validated['cost'] ?? 0;

        SpaTreatment::create($validated);

        return redirect()->route('hotel.spa.treatments.index')
            ->with('success', 'Treatment created successfully');
    }

    /**
     * Update treatment
     */
    public function updateTreatment(Request $request, SpaTreatment $treatment)
    {
        if ($treatment->tenant_id !== $this->tenantId()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'duration_minutes' => 'required|integer|min:15',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'preparation_time' => 'nullable|integer|min:0',
            'cleanup_time' => 'nullable|integer|min:0',
            'max_daily_bookings' => 'nullable|integer|min:1',
            'requires_consultation' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['cost'] = $validated['cost'] ?? 0;

        $treatment->update($validated);

        return back()->with('success', 'Treatment updated successfully');
    }

    /**
     * Packages Management
     */
    public function packages()
    {
        $packages = SpaPackage::where('tenant_id', $this->tenantId())
            ->withCount('items')
            ->orderBy('name')
            ->get();

        return view('hotel.spa.packages.index', compact('packages'));
    }

    /**
     * Show package details
     */
    public function showPackage(SpaPackage $package)
    {
        if ($package->tenant_id !== $this->tenantId()) {
            abort(403);
        }

        $package->load('items.treatment');
        $availableTreatments = SpaTreatment::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('hotel.spa.packages.show', compact('package', 'availableTreatments'));
    }

    /**
     * Store package
     */
    public function storePackage(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'package_price' => 'required|numeric|min:0',
            'total_duration_minutes' => 'required|integer|min:30',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = $this->tenantId();

        DB::transaction(function () use ($validated, $request) {
            $package = SpaPackage::create($validated);

            // Add package items
            if ($request->has('treatments')) {
                foreach ($request->input('treatments') as $index => $treatmentData) {
                    $treatment = SpaTreatment::find($treatmentData['id']);
                    if ($treatment) {
                        SpaPackageItem::create([
                            'tenant_id' => $this->tenantId(),
                            'package_id' => $package->id,
                            'treatment_id' => $treatment->id,
                            'sequence_order' => $index + 1,
                            'duration_override' => $treatmentData['duration_override'] ?? null,
                        ]);
                    }
                }
            }

            $package->recalculatePrice();
        });

        return redirect()->route('hotel.spa.packages.index')
            ->with('success', 'Package created successfully');
    }

    /**
     * Therapists Management
     */
    public function therapists()
    {
        $therapists = SpaTherapist::where('tenant_id', $this->tenantId())
            ->withCount([
                'bookings as today_bookings_count' => function ($q) {
                    $q->whereDate('booking_date', today())
                        ->whereNotIn('status', ['cancelled', 'no_show']);
                },
            ])
            ->orderBy('name')
            ->get();

        return view('hotel.spa.therapists.index', compact('therapists'));
    }

    /**
     * Store therapist
     */
    public function storeTherapist(Request $request)
    {
        $validated = $request->validate([
            'employee_number' => 'required|string|unique:spa_therapists,employee_number',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'specializations' => 'nullable|array',
            'hourly_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = $this->tenantId();
        $validated['hourly_rate'] = $validated['hourly_rate'] ?? 10; // Default 10% commission

        SpaTherapist::create($validated);

        return redirect()->route('hotel.spa.therapists.index')
            ->with('success', 'Therapist added successfully');
    }

    /**
     * Therapist Schedule
     */
    public function therapistSchedule(SpaTherapist $therapist)
    {
        if ($therapist->tenant_id !== $this->tenantId()) {
            abort(403);
        }

        $validated = request()->validate(['date' => 'nullable|date']);
        $date = $validated['date'] ?? today()->format('Y-m-d');

        $schedule = TherapistSchedule::where('therapist_id', $therapist->id)
            ->where('schedule_date', $date)
            ->orderBy('start_time')
            ->get();

        $bookings = SpaBooking::where('therapist_id', $therapist->id)
            ->where('booking_date', $date)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->orderBy('start_time')
            ->get();

        return view('hotel.spa.therapists.schedule', compact('therapist', 'date', 'schedule', 'bookings'));
    }

    /**
     * Bookings Management
     */
    public function bookings(Request $request)
    {
        $query = SpaBooking::where('tenant_id', $this->tenantId())
            ->with(['therapist', 'treatment', 'package', 'guest']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('booking_date', $request->input('date'));
        }

        if ($request->filled('therapist_id')) {
            $query->where('therapist_id', $request->input('therapist_id'));
        }

        $bookings = $query->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(30);

        $therapists = SpaTherapist::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('hotel.spa.bookings.index', compact('bookings', 'therapists'));
    }

    /**
     * Create booking form
     */
    public function createBooking()
    {
        $treatments = SpaTreatment::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $packages = SpaPackage::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $therapists = SpaTherapist::where('tenant_id', $this->tenantId())
            ->where('status', 'available')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('hotel.spa.bookings.create', compact('treatments', 'packages', 'therapists'));
    }

    /**
     * Store booking
     */
    public function storeBooking(Request $request)
    {
        $validated = $request->validate([
            'guest_id' => 'nullable|exists:hotel_guests,id',
            'room_number' => 'nullable|exists:hotel_rooms,room_number',
            'therapist_id' => 'nullable|exists:spa_therapists,id',
            'treatment_id' => 'nullable|exists:spa_treatments,id',
            'package_id' => 'nullable|exists:spa_packages,id',
            'booking_date' => 'required|date',
            'start_time' => 'required',
            'duration_minutes' => 'required|integer|min:15',
            'special_requests' => 'nullable|string',
        ]);

        // Calculate end time
        $startTime = Carbon::parse($validated['start_time']);
        $endTime = $startTime->copy()->addMinutes($validated['duration_minutes']);

        // Check therapist availability
        if ($validated['therapist_id']) {
            $therapist = SpaTherapist::find($validated['therapist_id']);
            if (! $therapist->isAvailableAt($validated['booking_date'], $validated['start_time'], $endTime->format('H:i:s'))) {
                return back()->withErrors(['therapist_id' => 'Therapist is not available at this time'])->withInput();
            }
        }

        // Calculate pricing
        $amount = 0;
        if ($validated['treatment_id']) {
            $treatment = SpaTreatment::find($validated['treatment_id']);
            $amount = $treatment->price;
        } elseif ($validated['package_id']) {
            $package = SpaPackage::find($validated['package_id']);
            $amount = $package->package_price;
        }

        $taxAmount = $amount * 0.10; // 10% tax
        $serviceCharge = $amount * 0.05; // 5% service charge
        $totalAmount = $amount + $taxAmount + $serviceCharge;

        DB::transaction(function () use ($validated, $endTime, $amount, $taxAmount, $serviceCharge, $totalAmount) {
            $booking = SpaBooking::create([
                'tenant_id' => $this->tenantId(),
                'guest_id' => $validated['guest_id'] ?? null,
                'room_number' => $validated['room_number'] ?? null,
                'therapist_id' => $validated['therapist_id'] ?? null,
                'treatment_id' => $validated['treatment_id'] ?? null,
                'package_id' => $validated['package_id'] ?? null,
                'booking_date' => $validated['booking_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $endTime->format('H:i:s'),
                'duration_minutes' => $validated['duration_minutes'],
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'service_charge' => $serviceCharge,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'special_requests' => $validated['special_requests'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // If package, create booking items
            if ($validated['package_id']) {
                $package = SpaPackage::with('items')->find($validated['package_id']);
                $currentTime = Carbon::parse($validated['start_time']);

                foreach ($package->items as $index => $item) {
                    $duration = $item->duration_override ?? $item->treatment->duration_minutes;
                    $itemEnd = $currentTime->copy()->addMinutes($duration);

                    SpaBookingItem::create([
                        'tenant_id' => $this->tenantId(),
                        'booking_id' => $booking->id,
                        'treatment_id' => $item->treatment_id,
                        'sequence_order' => $index + 1,
                        'scheduled_start' => $currentTime->format('H:i:s'),
                        'scheduled_end' => $itemEnd->format('H:i:s'),
                        'status' => 'pending',
                    ]);

                    $currentTime = $itemEnd;
                }
            }

            // Increment booked_today counter
            if ($validated['treatment_id'] && $validated['booking_date'] == today()->format('Y-m-d')) {
                SpaTreatment::find($validated['treatment_id'])->incrementBookedToday();
            }
        });

        return redirect()->route('hotel.spa.bookings.index')
            ->with('success', 'Booking created successfully');
    }

    /**
     * Confirm booking
     */
    public function confirmBooking(SpaBooking $booking)
    {
        if ($booking->tenant_id !== $this->tenantId()) {
            abort(403);
        }

        $booking->confirm();

        return back()->with('success', 'Booking confirmed');
    }

    /**
     * Complete booking
     */
    public function completeBooking(SpaBooking $booking)
    {
        if ($booking->tenant_id !== $this->tenantId()) {
            abort(403);
        }

        $booking->complete();

        return back()->with('success', 'Booking completed');
    }

    /**
     * Cancel booking
     */
    public function cancelBooking(Request $request, SpaBooking $booking)
    {
        if ($booking->tenant_id !== $this->tenantId()) {
            abort(403);
        }

        if (! $booking->canBeCancelled()) {
            return back()->with('error', 'This booking cannot be cancelled');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string',
        ]);

        $booking->cancel($validated['cancellation_reason']);

        return back()->with('success', 'Booking cancelled');
    }

    /**
     * Product Sales
     */
    public function productSales(Request $request)
    {
        $query = SpaProductSale::where('tenant_id', $this->tenantId())
            ->with(['product', 'booking', 'soldBy']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('sale_date', [
                $request->input('start_date'),
                $request->input('end_date'),
            ]);
        }

        $sales = $query->orderBy('sale_date', 'desc')->paginate(30);

        $stats = [
            'total_sales' => SpaProductSale::where('tenant_id', $this->tenantId())->sum('total_price'),
            'total_profit' => SpaProductSale::where('tenant_id', $this->tenantId())->sum('profit'),
            'total_items_sold' => SpaProductSale::where('tenant_id', $this->tenantId())->sum('quantity'),
        ];

        return view('hotel.spa.product-sales.index', compact('sales', 'stats'));
    }

    /**
     * Record product sale
     */
    public function recordProductSale(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'nullable|exists:spa_bookings,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $product = Product::find($validated['product_id']);

        SpaProductSale::create([
            'tenant_id' => $this->tenantId(),
            'booking_id' => $validated['booking_id'] ?? null,
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'total_price' => $validated['quantity'] * $validated['unit_price'],
            'cost_price' => $validated['quantity'] * $validated['cost_price'],
            'sold_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Deduct from inventory if applicable
        if ($product && method_exists($product, 'decrementStock')) {
            $product->decrementStock($validated['quantity']);
        }

        return back()->with('success', 'Product sale recorded');
    }

    /**
     * Spa Reports
     */
    public function reports(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Revenue by treatment
        $revenueByTreatment = SpaBooking::withoutGlobalScopes()
            ->where('spa_bookings.tenant_id', $this->tenantId())
            ->whereBetween('spa_bookings.booking_date', [$startDate, $endDate])
            ->where('spa_bookings.status', 'completed')
            ->join('spa_treatments', 'spa_bookings.treatment_id', '=', 'spa_treatments.id')
            ->select(
                'spa_treatments.name',
                'spa_treatments.category',
                DB::raw('COUNT(*) as booking_count'),
                DB::raw('SUM(spa_bookings.total_amount) as total_revenue')
            )
            ->groupBy('spa_treatments.id', 'spa_treatments.name', 'spa_treatments.category')
            ->orderByDesc('total_revenue')
            ->get();

        // Therapist performance
        $therapistPerformance = SpaTherapist::where('tenant_id', $this->tenantId())
            ->withCount([
                'bookings as completed_count' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('booking_date', [$startDate, $endDate])
                        ->where('status', 'completed');
                },
            ])
            ->withSum([
                'bookings as total_revenue' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('booking_date', [$startDate, $endDate])
                        ->where('status', 'completed');
                },
            ], 'total_amount')
            ->orderByDesc('total_revenue')
            ->get();

        // Daily revenue trend
        $dailyRevenue = SpaBooking::where('tenant_id', $this->tenantId())
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->select(DB::raw('DATE(booking_date) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $stats = [
            'total_bookings' => SpaBooking::where('tenant_id', $this->tenantId())
                ->whereBetween('booking_date', [$startDate, $endDate])
                ->count(),
            'completed_bookings' => SpaBooking::where('tenant_id', $this->tenantId())
                ->whereBetween('booking_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->count(),
            'total_revenue' => SpaBooking::where('tenant_id', $this->tenantId())
                ->whereBetween('booking_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->sum('total_amount'),
            'avg_booking_value' => SpaBooking::where('tenant_id', $this->tenantId())
                ->whereBetween('booking_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->avg('total_amount') ?? 0,
        ];

        return view('hotel.spa.reports.index', compact(
            'stats',
            'revenueByTreatment',
            'therapistPerformance',
            'dailyRevenue',
            'startDate',
            'endDate'
        ));
    }

    /**
     * CreatePackage.
     * Route: hotel/spa/packages/create
     */
    public function createPackage(Request $request)
    {
        // TODO: Add authorization
        // $this->authorize('ACTION', MODEL::class);

        $validated = $request->validate([
            // TODO: Add validation rules
        ]);

        // TODO: Implement CreatePackage logic

        return back()->with('success', 'CreatePackage completed successfully.');
    }
}
