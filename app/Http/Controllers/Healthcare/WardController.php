<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\PatientVisit;
use App\Models\Ward;
use Illuminate\Http\Request;

class WardController extends Controller
{
    /**
     * Display a listing of wards.
     */
    public function index(Request $request)
    {
        $query = Ward::query()->withCount(['beds', 'occupiedBeds']);

        if ($request->filled('ward_type')) {
            $query->where('ward_type', $request->ward_type);
        }

        if ($request->filled('floor')) {
            $query->where('floor', $request->floor);
        }

        $wards = $query->orderBy('ward_code')->paginate(20);

        $statistics = [
            'total_wards' => Ward::count(),
            'total_beds' => Bed::count(),
            'occupied_beds' => Bed::where('status', 'occupied')->count(),
            'available_beds' => Bed::where('status', 'available')->count(),
            'occupancy_rate' => Bed::count() > 0
                ? round((Bed::where('status', 'occupied')->count() / Bed::count()) * 100, 2)
                : 0,
        ];

        return view('healthcare.wards.index', compact('wards', 'statistics'));
    }

    /**
     * Show the form for creating a new ward.
     */
    public function create()
    {
        return view('healthcare.wards.create');
    }

    /**
     * Store a newly created ward.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ward_code' => 'required|string|unique:wards,ward_code|max:50',
            'ward_name' => 'required|string|max:255',
            'ward_type' => 'required|in:general,icu,nicu,maternity,pediatric,psychiatric,isolation',
            'floor' => 'required|integer|min:1',
            'total_beds' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'contact_extension' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $ward = Ward::create($validated);

        return redirect()->route('healthcare.wards.show', $ward)
            ->with('success', 'Ward created successfully: '.$ward->ward_code);
    }

    /**
     * Display the specified ward.
     */
    public function show(Ward $ward)
    {
        $ward->load([
            'beds' => function ($query) {
                $query->orderBy('bed_number');
            },
        ]);

        $statistics = [
            'total_beds' => $ward->beds->count(),
            'occupied_beds' => $ward->beds->where('status', 'occupied')->count(),
            'available_beds' => $ward->beds->where('status', 'available')->count(),
            'maintenance_beds' => $ward->beds->where('status', 'maintenance')->count(),
            'occupancy_rate' => $ward->beds->count() > 0
                ? round(($ward->beds->where('status', 'occupied')->count() / $ward->beds->count()) * 100, 2)
                : 0,
        ];

        $currentPatients = PatientVisit::where('ward_id', $ward->id)
            ->where('status', 'admitted')
            ->with(['patient', 'bed', 'doctor'])
            ->get();

        return view('healthcare.wards.show', compact('ward', 'statistics', 'currentPatients'));
    }

    /**
     * Show the form for editing the specified ward.
     */
    public function edit(Ward $ward)
    {
        return view('healthcare.wards.edit', compact('ward'));
    }

    /**
     * Update the specified ward.
     */
    public function update(Request $request, Ward $ward)
    {
        $validated = $request->validate([
            'ward_name' => 'required|string|max:255',
            'ward_type' => 'required|in:general,icu,nicu,maternity,pediatric,psychiatric,isolation',
            'floor' => 'required|integer|min:1',
            'total_beds' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'contact_extension' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $ward->update($validated);

        return redirect()->route('healthcare.wards.index')
            ->with('success', 'Ward updated successfully');
    }

    /**
     * Remove the specified ward.
     */
    public function destroy(Ward $ward)
    {
        if ($ward->beds()->where('status', 'occupied')->exists()) {
            return redirect()->route('healthcare.wards.index')
                ->with('error', 'Cannot delete ward with occupied beds');
        }

        $ward->delete();

        return redirect()->route('healthcare.wards.index')
            ->with('success', 'Ward deleted successfully');
    }

    /**
     * Get ward statistics.
     */
    public function statistics()
    {
        $wards = Ward::withCount(['beds', 'occupiedBeds'])->get();

        $wardStats = $wards->map(function ($ward) {
            return [
                'id' => $ward->id,
                'ward_code' => $ward->ward_code,
                'ward_name' => $ward->ward_name,
                'ward_type' => $ward->ward_type,
                'total_beds' => $ward->beds_count,
                'occupied_beds' => $ward->occupied_beds_count,
                'available_beds' => $ward->beds_count - $ward->occupied_beds_count,
                'occupancy_rate' => $ward->beds_count > 0
                    ? round(($ward->occupied_beds_count / $ward->beds_count) * 100, 2)
                    : 0,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $wardStats,
        ]);
    }
}
