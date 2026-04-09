<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TourPackage;
use App\Models\TourBooking;
use App\Models\TourItinerary;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class TourTravelApiController extends ApiBaseController
{
    public function packages(Request $request)
    {
        $query = TourPackage::where('tenant_id', $this->getTenantId())
            ->with(['itineraries']);

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
            ->with(['itineraries', 'bookings'])
            ->findOrFail($id);
        return $this->success($package);
    }

    public function createPackage(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'destination' => 'required|string',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'includes' => 'nullable|array',
            'max_participants' => 'nullable|integer',
        ]);

        $package = TourPackage::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
        ]));

        return $this->success($package, 'Tour package created successfully', 201);
    }

    public function bookings(Request $request)
    {
        $query = TourBooking::where('tenant_id', $this->getTenantId())
            ->with(['package', 'customer']);

        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
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
            ->with(['package', 'customer'])
            ->findOrFail($id);
        return $this->success($booking);
    }

    public function createBooking(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:tour_packages,id',
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string',
            'participants' => 'required|integer|min:1',
            'departure_date' => 'required|date',
            'special_requests' => 'nullable|string',
        ]);

        $booking = TourBooking::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'booking_number' => 'TRV-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => 'confirmed',
        ]));

        return $this->success($booking, 'Booking created successfully', 201);
    }

    public function updateBookingStatus(Request $request, $id)
    {
        $booking = TourBooking::where('tenant_id', $this->getTenantId())->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,in_progress,completed,cancelled',
        ]);

        $booking->update($validated);

        return $this->success($booking, 'Booking status updated successfully');
    }

    public function itineraries(Request $request)
    {
        $query = TourItinerary::where('tenant_id', $this->getTenantId())
            ->with(['package']);

        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        $itineraries = $query->orderBy('day')->paginate($request->get('per_page', 20));
        return $this->success($itineraries);
    }

    public function createItinerary(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:tour_packages,id',
            'day' => 'required|integer|min:1',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'activities' => 'nullable|array',
        ]);

        $itinerary = TourItinerary::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
        ]));

        return $this->success($itinerary, 'Itinerary created successfully', 201);
    }

    public function vehicles(Request $request)
    {
        $query = Vehicle::where('tenant_id', $this->getTenantId());

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
            'plate_number' => 'required|string|unique:vehicles,plate_number',
            'type' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'driver_name' => 'required|string',
            'driver_phone' => 'nullable|string',
            'status' => 'nullable|in:available,in_use,maintenance',
        ]);

        $vehicle = Vehicle::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'status' => $validated['status'] ?? 'available',
        ]));

        return $this->success($vehicle, 'Vehicle created successfully', 201);
    }
}
