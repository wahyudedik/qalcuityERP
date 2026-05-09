<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Bed;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmissionController extends Controller
{
    /**
     * Show form to create a new admission.
     */
    public function create(Request $request)
    {
        $patients = Patient::where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'medical_record_number']);

        $doctors = Doctor::where('status', 'active')
            ->orderBy('id')
            ->get();

        $availableBeds = Bed::where('status', 'available')
            ->with('ward')
            ->get();

        $selectedPatientId = $request->query('patient_id');

        return view('healthcare.inpatient.admissions.create', compact(
            'patients',
            'doctors',
            'availableBeds',
            'selectedPatientId'
        ));
    }

    /**
     * Display a listing of admissions.
     */
    public function index(Request $request)
    {
        $query = Admission::with(['patient', 'bed.ward', 'doctor']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('ward_id')) {
            $query->whereHas('bed', function ($q) use ($request) {
                $q->where('ward_id', $request->ward_id);
            });
        }

        $admissions = $query->latest()->paginate(20);

        $statistics = [
            'total_active' => Admission::whereIn('status', ['active', 'transferred'])->count(),
            'today_admissions' => Admission::whereDate('admission_date', today())->count(),
            'today_discharges' => Admission::whereDate('actual_discharge_date', today())->count(),
            'avg_length_of_stay' => Admission::whereNotNull('actual_discharge_date')
                ->selectRaw('AVG(DATEDIFF(actual_discharge_date, admission_date)) as avg')
                ->value('avg'),
        ];

        return view('healthcare.inpatient.admissions.index', compact('admissions', 'statistics'));
    }

    /**
     * Display the specified admission.
     */
    public function show(Admission $admission)
    {
        $admission->load(['patient', 'bed.ward', 'doctor', 'wardRounds']);

        return view('healthcare.inpatient.admissions.show', compact('admission'));
    }

    /**
     * Store a newly created admission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'bed_id' => 'required|exists:beds,id',
            'admitting_doctor_id' => 'required|exists:doctors,id',
            'admission_date' => 'required|date',
            'admission_diagnosis' => 'required|string',
            'admission_type' => 'required|in:emergency,elective,referral,maternity',
            'estimated_discharge_date' => 'nullable|date|after:admission_date',
        ]);

        // Check if bed is available
        $bed = Bed::findOrFail($validated['bed_id']);
        if ($bed->status !== 'available') {
            return back()->withInput()->with('error', 'Bed is not available');
        }

        DB::beginTransaction();
        try {
            $validated['status'] = 'active';
            $admission = Admission::create($validated);

            // Mark bed as occupied
            $bed->markAsOccupied($validated['patient_id'], $admission->id);

            DB::commit();

            return redirect()->route('healthcare.inpatient.admissions.show', $admission)
                ->with('success', 'Patient admitted successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Failed to admit patient: '.$e->getMessage());
        }
    }

    /**
     * Update the specified admission.
     */
    public function update(Request $request, Admission $admission)
    {
        $validated = $request->validate([
            'admission_diagnosis' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'estimated_discharge_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $admission->update($validated);

        return back()->with('success', 'Admission updated successfully');
    }

    /**
     * Discharge patient.
     */
    public function discharge(Admission $admission, Request $request)
    {
        $validated = $request->validate([
            'discharge_diagnosis' => 'required|string',
            'discharge_summary' => 'required|string',
            'discharge_status' => 'required|in:recovered,improved,unchanged,worsened,referred,ama',
            'discharge_type' => 'required|in:normal,transfer,against_medical_advice',
            'actual_cost' => 'nullable|numeric',
            'cleaned_by' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $admission->discharge($validated);

            DB::commit();

            return redirect()->route('healthcare.inpatient.admissions.show', $admission)
                ->with('success', 'Patient discharged successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to discharge patient: '.$e->getMessage());
        }
    }

    /**
     * Transfer patient to another bed/ward.
     */
    public function transfer(Admission $admission, Request $request)
    {
        $validated = $request->validate([
            'new_bed_id' => 'required|exists:beds,id',
            'transfer_reason' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $newBed = Bed::findOrFail($validated['new_bed_id']);
            $admission->transfer($newBed->ward_id, $validated['new_bed_id']);

            DB::commit();

            return back()->with('success', 'Patient transferred successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to transfer patient: '.$e->getMessage());
        }
    }

    /**
     * Display ward rounds.
     */
    public function rounds(Request $request)
    {
        $query = Admission::with(['patient', 'bed.ward'])
            ->whereIn('status', ['active', 'transferred']);

        if ($request->filled('ward_id')) {
            $query->whereHas('bed', function ($q) use ($request) {
                $q->where('ward_id', $request->ward_id);
            });
        }

        $admissions = $query->get();

        return view('healthcare.inpatient.rounds.index', compact('admissions'));
    }

    /**
     * Record ward round.
     */
    public function recordRounds(Request $request)
    {
        $validated = $request->validate([
            'admission_id' => 'required|exists:admissions,id',
            'round_date' => 'required|date',
            'vital_signs' => 'nullable|array',
            'assessment' => 'required|string',
            'plan' => 'required|string',
            'doctor_id' => 'required|exists:doctors,id',
        ]);

        $admission = Admission::findOrFail($validated['admission_id']);
        $admission->wardRounds()->create($validated);

        return back()->with('success', 'Ward round recorded successfully');
    }

    /**
     * Display admission dashboard.
     */
    public function dashboard()
    {
        $statistics = [
            'total_active' => Admission::whereIn('status', ['active', 'transferred'])->count(),
            'today_admissions' => Admission::whereDate('admission_date', today())->count(),
            'today_discharges' => Admission::whereDate('actual_discharge_date', today())->count(),
            'pending_discharge' => Admission::where('status', 'pending_discharge')->count(),
            'avg_length_of_stay' => Admission::whereNotNull('actual_discharge_date')
                ->selectRaw('AVG(DATEDIFF(actual_discharge_date, admission_date)) as avg')
                ->value('avg'),
        ];

        $recentAdmissions = Admission::with(['patient', 'bed.ward'])
            ->latest()
            ->limit(10)
            ->get();

        $pendingDischarges = Admission::with(['patient', 'bed.ward'])
            ->where('status', 'pending_discharge')
            ->get();

        return view('healthcare.inpatient.dashboard', compact('statistics', 'recentAdmissions', 'pendingDischarges'));
    }
}
