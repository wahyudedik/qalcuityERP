<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Ward;
use App\Models\PatientVisit;
use Illuminate\Http\Request;

class BedController extends Controller
{
    /**
     * Display a listing of beds.
     */
    public function index(Request $request)
    {
        $query = Bed::query()->with(['ward', 'currentPatient']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('ward_id')) {
            $query->where('ward_id', $request->ward_id);
        }

        if ($request->filled('bed_type')) {
            $query->where('bed_type', $request->bed_type);
        }

        $beds = $query->orderBy('bed_number')->paginate(50);

        $wards = Ward::where('is_active', true)->orderBy('ward_name')->get();

        $statistics = [
            'total_beds' => Bed::count(),
            'available' => Bed::where('status', 'available')->count(),
            'occupied' => Bed::where('status', 'occupied')->count(),
            'reserved' => Bed::where('status', 'reserved')->count(),
            'maintenance' => Bed::where('status', 'maintenance')->count(),
            'occupancy_rate' => Bed::count() > 0
                ? round((Bed::where('status', 'occupied')->count() / Bed::count()) * 100, 2)
                : 0,
        ];

        return view('healthcare.beds.index', compact('beds', 'wards', 'statistics'));
    }

    /**
     * Show the form for creating a new bed.
     */
    public function create()
    {
        $wards = Ward::where('is_active', true)->orderBy('ward_name')->get();
        return view('healthcare.beds.create', compact('wards'));
    }

    /**
     * Store a newly created bed.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ward_id' => 'required|exists:wards,id',
            'bed_number' => 'required|string|max:50',
            'bed_type' => 'required|in:standard,private,vip,icu,isolated',
            'room_number' => 'nullable|string|max:50',
            'floor' => 'required|integer|min:1',
            'daily_rate' => 'required|numeric|min:0',
            'has_window' => 'boolean',
            'has_tv' => 'boolean',
            'has_ac' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['status'] = 'available';
        $validated['has_window'] = $request->has('has_window');
        $validated['has_tv'] = $request->has('has_tv');
        $validated['has_ac'] = $request->has('has_ac');
        $validated['is_active'] = $request->has('is_active');

        $bed = Bed::create($validated);

        return redirect()->route('healthcare.beds.show', $bed)
            ->with('success', 'Bed created successfully: ' . $bed->bed_number);
    }

    /**
     * Display the specified bed.
     */
    public function show(Bed $bed)
    {
        $bed->load(['ward', 'currentPatient.patient', 'visitHistory']);

        $occupancyHistory = PatientVisit::where('bed_id', $bed->id)
            ->with(['patient', 'doctor'])
            ->orderBy('admission_date', 'desc')
            ->limit(10)
            ->get();

        return view('healthcare.beds.show', compact('bed', 'occupancyHistory'));
    }

    /**
     * Show the form for editing the specified bed.
     */
    public function edit(Bed $bed)
    {
        $wards = Ward::where('is_active', true)->orderBy('ward_name')->get();
        return view('healthcare.beds.edit', compact('bed', 'wards'));
    }

    /**
     * Update the specified bed.
     */
    public function update(Request $request, Bed $bed)
    {
        $validated = $request->validate([
            'ward_id' => 'required|exists:wards,id',
            'bed_number' => 'required|string|max:50',
            'bed_type' => 'required|in:standard,private,vip,icu,isolated',
            'room_number' => 'nullable|string|max:50',
            'floor' => 'required|integer|min:1',
            'daily_rate' => 'required|numeric|min:0',
            'has_window' => 'boolean',
            'has_tv' => 'boolean',
            'has_ac' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['has_window'] = $request->has('has_window');
        $validated['has_tv'] = $request->has('has_tv');
        $validated['has_ac'] = $request->has('has_ac');
        $validated['is_active'] = $request->has('is_active');

        $bed->update($validated);

        return redirect()->route('healthcare.beds.index')
            ->with('success', 'Bed updated successfully');
    }

    /**
     * Update bed status.
     */
    public function updateStatus(Request $request, Bed $bed)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,occupied,reserved,maintenance,cleaning',
            'notes' => 'nullable|string',
        ]);

        $bed->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bed status updated successfully',
        ]);
    }

    /**
     * Remove the specified bed.
     */
    public function destroy(Bed $bed)
    {
        if ($bed->status === 'occupied') {
            return redirect()->route('healthcare.beds.index')
                ->with('error', 'Cannot delete an occupied bed');
        }

        $bed->delete();

        return redirect()->route('healthcare.beds.index')
            ->with('success', 'Bed deleted successfully');
    }

    /**
     * Get available beds by ward.
     */
    public function availableBeds(Request $request)
    {
        $query = Bed::where('status', 'available')->where('is_active', true);

        if ($request->filled('ward_id')) {
            $query->where('ward_id', $request->ward_id);
        }

        if ($request->filled('bed_type')) {
            $query->where('bed_type', $request->bed_type);
        }

        $beds = $query->with('ward')->orderBy('bed_number')->get();

        return response()->json([
            'success' => true,
            'data' => $beds,
        ]);
    }

    /**
     * Assign patient to bed.
     */
    public function assignPatient(Request $request, Bed $bed)
    {
        $validated = $request->validate([
            'patient_visit_id' => 'required|exists:patient_visits,id',
        ]);

        if ($bed->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'Bed is not available',
            ], 400);
        }

        $bed->update([
            'status' => 'occupied',
            'occupied_at' => now(),
        ]);

        PatientVisit::where('id', $validated['patient_visit_id'])
            ->update(['bed_id' => $bed->id]);

        return response()->json([
            'success' => true,
            'message' => 'Patient assigned to bed successfully',
        ]);
    }
    /**
     * ReleasePatient.
     * Route: healthcare/beds/{bed}/release
     */
    public function releasePatient(Request $request, $model)
    {
        $this->authorize('update', $model);
        
        $validated = $request->validate([
            // TODO: Add validation rules
        ]);
        
        // TODO: Implement ReleasePatient logic
        
        return back()->with('success', 'ReleasePatient completed successfully.');
    }
    /**
     * CheckAvailability.
     * Route: healthcare/beds/availability
     */
    public function checkAvailability(Request $request, $model)
    {
        $this->authorize('update', $model);
        
        $validated = $request->validate([
            // TODO: Add validation rules
        ]);
        
        // TODO: Implement CheckAvailability logic
        
        return back()->with('success', 'CheckAvailability completed successfully.');
    }
}
