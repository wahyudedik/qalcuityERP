<?php

namespace App\Http\Controllers\TourTravel;

use App\Http\Controllers\Controller;
use App\Models\TourPackage;
use App\Models\ItineraryDay;
use App\Models\TourBooking;
use App\Models\BookingPassenger;
use App\Models\TourSupplier;
use App\Models\PackageSupplierAllocation;
use App\Models\VisaApplication;
use App\Models\TravelDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TourPackageController extends Controller
{
    /**
     * Display tour packages dashboard
     */
    public function index(Request $request)
    {
        $stats = [
            'total_packages' => TourPackage::where('tenant_id', auth()->user()->tenant_id)->count(),
            'active_packages' => TourPackage::where('tenant_id', auth()->user()->tenant_id)->active()->count(),
            'total_bookings' => TourBooking::where('tenant_id', auth()->user()->tenant_id)->count(),
            'upcoming_departures' => TourBooking::where('tenant_id', auth()->user()->tenant_id)
                ->upcoming()
                ->count(),
            'pending_visas' => VisaApplication::where('tenant_id', auth()->user()->tenant_id)
                ->whereIn('status', ['preparing', 'submitted', 'processing'])
                ->count(),
        ];

        $packages = TourPackage::where('tenant_id', auth()->user()->tenant_id)
            ->withCount([
                'bookings' => function ($q) {
                    $q->whereIn('status', ['pending', 'confirmed', 'paid']);
                }
            ])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('tour-travel.packages.index', compact('stats', 'packages'));
    }

    /**
     * Show create package form
     */
    public function create()
    {
        return view('tour-travel.packages.create');
    }

    /**
     * Store new tour package
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'destination' => 'required|string',
            'category' => 'required|in:domestic,international,adventure,luxury,cultural,beach,mountain,city_tour',
            'duration_days' => 'required|integer|min:1',
            'duration_nights' => 'nullable|integer|min:0',
            'min_pax' => 'nullable|integer|min:1',
            'max_pax' => 'nullable|integer|gte:min_pax',
            'price_per_person' => 'required|numeric|min:0',
            'cost_per_person' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'description' => 'nullable|string',
            'inclusions' => 'nullable|array',
            'exclusions' => 'nullable|array',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                $package = new TourPackage();
                $package->tenant_id = auth()->user()->tenant_id;
                $package->package_code = 'TOUR-' . now()->format('Y') . '-' . str_pad(TourPackage::count() + 1, 4, '0', STR_PAD_LEFT);
                $package->fill($validated);
                $package->status = 'draft';
                $package->created_by = auth()->id();
                $package->save();

                // Save itinerary days if provided
                if ($request->has('itinerary_days')) {
                    foreach ($request->input('itinerary_days') as $dayData) {
                        ItineraryDay::create([
                            'tour_package_id' => $package->id,
                            'day_number' => $dayData['day_number'],
                            'title' => $dayData['title'],
                            'description' => $dayData['description'] ?? null,
                            'activities' => $dayData['activities'] ?? null,
                            'accommodation' => $dayData['accommodation'] ?? null,
                            'meals' => $dayData['meals'] ?? null,
                            'transport_mode' => $dayData['transport_mode'] ?? null,
                        ]);
                    }
                }
            });

            return redirect()->route('tour-travel.packages.index')
                ->with('success', 'Tour package created successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display package details
     */
    public function show($id)
    {
        $package = TourPackage::with([
            'itineraryDays',
            'supplierAllocations.supplier',
            'bookings' => function ($q) {
                $q->latest()->take(10);
            },
            'createdBy'
        ])->findOrFail($id);

        return view('tour-travel.packages.show', compact('package'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $package = TourPackage::with('itineraryDays')->findOrFail($id);
        return view('tour-travel.packages.edit', compact('package'));
    }

    /**
     * Update package
     */
    public function update(Request $request, $id)
    {
        $package = TourPackage::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'destination' => 'required|string',
            'category' => 'required|string',
            'duration_days' => 'required|integer|min:1',
            'price_per_person' => 'required|numeric|min:0',
            'cost_per_person' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,active,inactive,archived',
        ]);

        $package->update($validated);

        return back()->with('success', 'Package updated successfully!');
    }

    /**
     * Activate/Deactivate package
     */
    public function toggleStatus($id)
    {
        $package = TourPackage::findOrFail($id);
        $package->status = $package->status === 'active' ? 'inactive' : 'active';
        $package->save();

        return back()->with('success', 'Package status updated!');
    }

    /**
     * Add itinerary day
     */
    public function addItineraryDay(Request $request, $id)
    {
        $validated = $request->validate([
            'day_number' => 'required|integer|min:1',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'activities' => 'nullable|array',
            'accommodation' => 'nullable|string',
            'meals' => 'nullable|array',
            'transport_mode' => 'nullable|string',
        ]);

        $package = TourPackage::findOrFail($id);

        ItineraryDay::create(array_merge($validated, [
            'tour_package_id' => $package->id,
        ]));

        return back()->with('success', 'Itinerary day added!');
    }

    /**
     * Assign supplier to package
     */
    public function assignSupplier(Request $request, $id)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:tour_suppliers,id',
            'service_type' => 'required|in:accommodation,transport,activity,meal,guide',
            'service_description' => 'nullable|string',
            'day_number' => 'nullable|integer',
            'cost_per_unit' => 'required|numeric|min:0',
            'unit_type' => 'required|in:per_person,per_room,per_vehicle,fixed',
        ]);

        $package = TourPackage::findOrFail($id);

        PackageSupplierAllocation::create(array_merge($validated, [
            'tour_package_id' => $package->id,
        ]));

        // Recalculate package cost
        $this->recalculatePackageCost($package);

        return back()->with('success', 'Supplier assigned!');
    }

    protected function recalculatePackageCost(TourPackage $package): void
    {
        $totalCost = $package->supplierAllocations()->sum('cost_per_unit');
        $package->update(['cost_per_person' => $totalCost]);
    }
}
