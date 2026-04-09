<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\Teleconsultation;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeleconsultationController extends Controller
{
    public function index(Request $request)
    {
        $query = Teleconsultation::with(['patient', 'doctor']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('consultation_type')) {
            $query->where('consultation_type', $request->consultation_type);
        }

        $consultations = $query->orderBy('scheduled_at', 'desc')->paginate(20);

        $statistics = [
            'total' => Teleconsultation::count(),
            'scheduled' => Teleconsultation::where('status', 'scheduled')->count(),
            'in_progress' => Teleconsultation::where('status', 'in_progress')->count(),
            'completed' => Teleconsultation::where('status', 'completed')->count(),
            'cancelled' => Teleconsultation::where('status', 'cancelled')->count(),
        ];

        return view('healthcare.teleconsultations.index', compact('consultations', 'statistics'));
    }

    public function create()
    {
        $patients = Patient::where('is_active', true)->get();
        $doctors = Doctor::where('is_active', true)->get();
        return view('healthcare.teleconsultations.create', compact('patients', 'doctors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'consultation_type' => 'required|in:video,audio,chat',
            'scheduled_at' => 'required|date|after:now',
            'chief_complaint' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $validated['consultation_number'] = 'TC-' . now()->format('Ymd') . '-' . str_pad(Teleconsultation::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        $validated['status'] = 'scheduled';
        $validated['created_by'] = Auth::id();

        $consultation = Teleconsultation::create($validated);

        return redirect()->route('healthcare.teleconsultations.show', $consultation)
            ->with('success', 'Teleconsultation scheduled');
    }

    public function show(Teleconsultation $teleconsultation)
    {
        $teleconsultation->load(['patient', 'doctor', 'prescriptions', 'feedback']);
        return view('healthcare.teleconsultations.show', compact('teleconsultation'));
    }

    public function start(Teleconsultation $teleconsultation)
    {
        $teleconsultation->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Consultation started']);
    }

    public function complete(Request $request, Teleconsultation $teleconsultation)
    {
        $validated = $request->validate([
            'diagnosis' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $teleconsultation->update([
            'status' => 'completed',
            'completed_at' => now(),
            'diagnosis' => $validated['diagnosis'] ?? null,
            'treatment_plan' => $validated['treatment_plan'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Consultation completed']);
    }

    public function joinRoom(Teleconsultation $teleconsultation)
    {
        return response()->json([
            'success' => true,
            'room_url' => $teleconsultation->room_url,
            'room_token' => $teleconsultation->room_token,
        ]);
    }

    public function cancel(Teleconsultation $teleconsultation)
    {
        $teleconsultation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Consultation cancelled']);
    }

    public function destroy(Teleconsultation $teleconsultation)
    {
        $teleconsultation->delete();
        return response()->json(['success' => true, 'message' => 'Consultation deleted']);
    }
}
