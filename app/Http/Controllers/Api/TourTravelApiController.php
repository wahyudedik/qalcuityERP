<?php

namespace App\Http\Controllers\Api;

use App\Models\FleetVehicle;
use App\Models\ItineraryDay;
use App\Models\TourBooking;
use App\Models\TourPackage;
use Illuminate\Http\Request;

class TourTravelApiController extends ApiBaseController
{
    public function packages(Request $request)
    {
        $query = TourPackage::where('tenant_id', $this->getTenantId())
            ->with(['itineraryDays']);

        if ($request->filled('destination')) {
            $query->where('destination', 'like', "%{$request->destination}%");
        }

        if ($request->filled('duration')) {
            $query->where('duration_days', $request->duration);
        }

        $packages = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($packages);
    }

    public function package($id)
    {
        $package = TourPackage::where('tenant_id', $this->getTenantId())
            ->with(['itineraryDays', 'bookings'])
            ->findOrFail($id);

        return $this->success($package);
    }

    public function createPackage(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'destination' => 'required|string',
            'duration_days' => 'required|integer|min:1',
            'price_per_person' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'inclusions' => 'nullable|array',
            'max_pax' => 'nullable|integer',
        ]);

        $package = TourPackage::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'status' => 'draft',
            'package_code' => 'TOUR-'.now()->format('Y').'-'.str_pad(TourPackage::count() + 1, 4, '0', STR_PAD_LEFT),
        ]));

        return $this->success($package, 'Tour package created successfully', 201);
    }

    public function bookings(Request $request)
    {
        $query = TourBooking::where('tenant_id', $this->getTenantId())
            ->with(['tourPackage', 'customer']);

        if ($request->filled('package_id')) {
            $query->where('tour_package_id', $request->package_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($bookings);
    }

    public function booking($id)
    {
        $booking = TourBooking::where('tenant_id', $this->getTenantId())
            ->with(['tourPackage', 'customer'])
            ->findOrFail($id);

        return $this->success($booking);
    }

    public function createBooking(Request $request)
    {
        $validated = $request->validate([
            'tour_package_id' => 'required|exists:tour_packages,id',
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'infants' => 'nullable|integer|min:0',
            'departure_date' => 'required|date',
            'special_requests' => 'nullable|string',
        ]);

        $package = TourPackage::where('tenant_id', $this->getTenantId())
            ->findOrFail($validated['tour_package_id']);

        $totalPax = $validated['adults'] + ($validated['children'] ?? 0) + ($validated['infants'] ?? 0);
        $totalAmount = $package->price_per_person * $totalPax;

        $booking = TourBooking::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'booking_number' => 'TB-'.date('Ymd').'-'.str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'unit_price' => $package->price_per_person,
            'total_pax' => $totalPax,
            'subtotal' => $totalAmount,
            'total_amount' => $totalAmount,
            'currency' => $package->currency ?? 'IDR',
        ]));

        return $this->success($booking, 'Booking created successfully', 201);
    }

    public function updateBookingStatus(Request $request, $id)
    {
        $booking = TourBooking::where('tenant_id', $this->getTenantId())->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,paid,completed,cancelled,refunded',
        ]);

        $booking->update($validated);

        return $this->success($booking, 'Booking status updated successfully');
    }

    public function itineraries(Request $request)
    {
        $query = ItineraryDay::query();

        if ($request->filled('package_id')) {
            $query->where('tour_package_id', $request->package_id);
        }

        // Filter by tenant through the package relationship
        $query->whereHas('tourPackage', function ($q) {
            $q->where('tenant_id', $this->getTenantId());
        });

        $itineraries = $query->orderBy('day_number')->paginate($request->get('per_page', 20));

        return $this->success($itineraries);
    }

    public function createItinerary(Request $request)
    {
        $validated = $request->validate([
            'tour_package_id' => 'required|exists:tour_packages,id',
            'day_number' => 'required|integer|min:1',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'activities' => 'nullable|array',
        ]);

        // Verify the package belongs to this tenant
        TourPackage::where('tenant_id', $this->getTenantId())
            ->findOrFail($validated['tour_package_id']);

        $itinerary = ItineraryDay::create($validated);

        return $this->success($itinerary, 'Itinerary created successfully', 201);
    }

    public function vehicles(Request $request)
    {
        $query = FleetVehicle::where('tenant_id', $this->getTenantId());

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vehicles = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($vehicles);
    }

    public function createVehicle(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|unique:fleet_vehicles,plate_number',
            'name' => 'required|string',
            'type' => 'required|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'year' => 'nullable|integer',
            'color' => 'nullable|string',
            'status' => 'nullable|in:available,in_use,maintenance',
            'notes' => 'nullable|string',
        ]);

        $vehicle = FleetVehicle::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'status' => $validated['status'] ?? 'available',
            'is_active' => true,
        ]));

        return $this->success($vehicle, 'Vehicle created successfully', 201);
    }
}
