<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Ward;
use Illuminate\Http\Request;

class BedManagementController extends Controller
{
    /**
     * Display bed management dashboard.
     */
    public function index()
    {
        $wards = Ward::withCount([
            'beds',
            'beds as available_beds' => function ($query) {
                $query->where('status', 'available');
            },
            'beds as occupied_beds' => function ($query) {
                $query->where('status', 'occupied');
            },
            'beds as maintenance_beds' => function ($query) {
                $query->where('status', 'maintenance');
            },
        ])->get();

        $statistics = [
            'total_beds' => Bed::count(),
            'available' => Bed::where('status', 'available')->count(),
            'occupied' => Bed::where('status', 'occupied')->count(),
            'maintenance' => Bed::where('status', 'maintenance')->count(),
            'occupancy_rate' => Bed::count() > 0
                ? round((Bed::where('status', 'occupied')->count() / Bed::count()) * 100, 2)
                : 0,
        ];

        return view('healthcare.inpatient.bed-management.index', compact('wards', 'statistics'));
    }

    /**
     * Display all wards.
     */
    public function wards()
    {
        $wards = Ward::withCount([
            'beds',
            'beds as available_beds' => function ($query) {
                $query->where('status', 'available');
            },
            'beds as occupied_beds' => function ($query) {
                $query->where('status', 'occupied');
            },
        ])->get();

        return view('healthcare.inpatient.wards.index', compact('wards'));
    }

    /**
     * Display beds in a ward.
     */
    public function wardBeds(Ward $ward)
    {
        $beds = $ward->beds()
            ->with(['currentPatient', 'currentAdmission'])
            ->get()
            ->groupBy('status');

        return view('healthcare.inpatient.wards.beds', compact('ward', 'beds'));
    }

    /**
     * Display occupancy report.
     */
    public function occupancy()
    {
        $wards = Ward::with(['beds'])->get();

        $occupancyData = $wards->map(function ($ward) {
            $totalBeds = $ward->beds()->count();
            $occupiedBeds = $ward->beds()->where('status', 'occupied')->count();

            return [
                'ward' => $ward,
                'total_beds' => $totalBeds,
                'occupied_beds' => $occupiedBeds,
                'available_beds' => $totalBeds - $occupiedBeds,
                'occupancy_rate' => $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 2) : 0,
            ];
        });

        return view('healthcare.inpatient.occupancy', compact('occupancyData'));
    }

    /**
     * Assign bed to patient.
     */
    public function assignBed(Request $request)
    {
        $validated = $request->validate([
            'bed_id' => 'required|exists:beds,id',
            'patient_id' => 'required|exists:patients,id',
            'admission_id' => 'required|exists:admissions,id',
        ]);

        $bed = Bed::findOrFail($validated['bed_id']);

        if ($bed->status !== 'available') {
            return back()->with('error', 'Bed is not available');
        }

        $bed->markAsOccupied($validated['patient_id'], $validated['admission_id']);

        return back()->with('success', 'Bed assigned successfully');
    }

    /**
     * Release bed.
     */
    public function releaseBed(Request $request)
    {
        $validated = $request->validate([
            'bed_id' => 'required|exists:beds,id',
            'cleaned_by' => 'nullable|string|max:255',
        ]);

        $bed = Bed::findOrFail($validated['bed_id']);

        if ($bed->status === 'available') {
            return back()->with('error', 'Bed is already available');
        }

        $bed->markAsAvailable($validated['cleaned_by'] ?? null);

        return back()->with('success', 'Bed released successfully');
    }
}
