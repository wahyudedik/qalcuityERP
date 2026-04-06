<?php

namespace App\Http\Controllers\TourTravel;

use App\Http\Controllers\Controller;
use App\Models\TourBooking;
use App\Models\TourPackage;
use App\Models\BookingPassenger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TourBookingController extends Controller
{
    /**
     * Display bookings dashboard
     */
    public function index(Request $request)
    {
        $stats = [
            'total_bookings' => TourBooking::where('tenant_id', auth()->user()->tenant_id)->count(),
            'pending_bookings' => TourBooking::where('tenant_id', auth()->user()->tenant_id)
                ->byStatus('pending')->count(),
            'confirmed_bookings' => TourBooking::where('tenant_id', auth()->user()->tenant_id)
                ->byStatus('confirmed')->count(),
            'upcoming_departures' => TourBooking::where('tenant_id', auth()->user()->tenant_id)
                ->upcoming()->count(),
            'total_revenue' => TourBooking::where('tenant_id', auth()->user()->tenant_id)
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
        ];

        $bookings = TourBooking::where('tenant_id', auth()->user()->tenant_id)
            ->with(['tourPackage', 'customer', 'assignedGuide'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('tour-travel.bookings.index', compact('stats', 'bookings'));
    }

    /**
     * Show create booking form
     */
    public function create()
    {
        $packages = TourPackage::where('tenant_id', auth()->user()->tenant_id)
            ->active()
            ->orderBy('name')
            ->get();

        return view('tour-travel.bookings.create', compact('packages'));
    }

    /**
     * Store new booking
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tour_package_id' => 'required|exists:tour_packages,id',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string',
            'departure_date' => 'required|date|after:today',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'infants' => 'nullable|integer|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'special_requests' => 'nullable|string',
            'passengers' => 'required|array|min:1',
            'passengers.*.full_name' => 'required|string',
            'passengers.*.passport_number' => 'nullable|string',
            'passengers.*.type' => 'required|in:adult,child,infant',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                $package = TourPackage::findOrFail($validated['tour_package_id']);

                $totalPax = $validated['adults'] + ($validated['children'] ?? 0) + ($validated['infants'] ?? 0);
                $subtotal = $package->price_per_person * $totalPax;
                $discount = $validated['discount_amount'] ?? 0;
                $tax = $validated['tax_amount'] ?? 0;
                $totalAmount = $subtotal - $discount + $tax;

                $booking = new TourBooking();
                $booking->tenant_id = auth()->user()->tenant_id;
                $booking->booking_number = 'TB-' . now()->format('Y') . '-' . str_pad(TourBooking::count() + 1, 4, '0', STR_PAD_LEFT);
                $booking->fill($validated);
                $booking->unit_price = $package->price_per_person;
                $booking->discount_amount = $discount;
                $booking->tax_amount = $tax;
                $booking->currency = $package->currency;
                $booking->status = 'pending';
                $booking->payment_status = 'unpaid';
                $booking->created_by = auth()->id();
                $booking->save();

                // Save passengers
                foreach ($validated['passengers'] as $passengerData) {
                    BookingPassenger::create([
                        'tour_booking_id' => $booking->id,
                        'full_name' => $passengerData['full_name'],
                        'passport_number' => $passengerData['passport_number'] ?? null,
                        'type' => $passengerData['type'],
                    ]);
                }
            });

            return redirect()->route('tour-travel.bookings.index')
                ->with('success', 'Booking created successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display booking details
     */
    public function show($id)
    {
        $booking = TourBooking::with([
            'tourPackage.itineraryDays',
            'passengers',
            'visaApplications',
            'documents',
            'assignedGuide',
            'customer'
        ])->findOrFail($id);

        return view('tour-travel.bookings.show', compact('booking'));
    }

    /**
     * Confirm booking
     */
    public function confirm($id)
    {
        $booking = TourBooking::findOrFail($id);
        $booking->confirm();

        return back()->with('success', 'Booking confirmed!');
    }

    /**
     * Cancel booking
     */
    public function cancel(Request $request, $id)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string',
        ]);

        $booking = TourBooking::findOrFail($id);
        $booking->cancel($validated['cancellation_reason']);

        return back()->with('success', 'Booking cancelled!');
    }

    /**
     * Record payment
     */
    public function recordPayment(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $booking = TourBooking::findOrFail($id);
        $booking->addPayment($validated['amount']);

        return back()->with('success', 'Payment recorded!');
    }

    /**
     * Mark booking as completed
     */
    public function complete($id)
    {
        $booking = TourBooking::findOrFail($id);
        $booking->markAsCompleted();

        return back()->with('success', 'Booking marked as completed!');
    }

    /**
     * Assign guide to booking
     */
    public function assignGuide(Request $request, $id)
    {
        $validated = $request->validate([
            'guide_id' => 'required|exists:users,id',
        ]);

        $booking = TourBooking::findOrFail($id);
        $booking->update(['assigned_guide' => $validated['guide_id']]);

        return back()->with('success', 'Guide assigned!');
    }
}
