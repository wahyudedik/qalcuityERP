<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\MedicalCertificate;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalCertificateController extends Controller
{
    public function index(Request $request)
    {
        $query = MedicalCertificate::with(['patient', 'doctor']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $certificates = $query->orderBy('issue_date', 'desc')->paginate(20);

        $statistics = [
            'total' => MedicalCertificate::count(),
            'approved' => MedicalCertificate::where('status', 'approved')->count(),
            'pending' => MedicalCertificate::where('status', 'pending')->count(),
            'rejected' => MedicalCertificate::where('status', 'rejected')->count(),
        ];

        return view('healthcare.medical-certificates.index', compact('certificates', 'statistics'));
    }

    public function create()
    {
        $patients = Patient::where('is_active', true)->get();
        return view('healthcare.medical-certificates.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'type' => 'required|in:sick_leave,fitness,medical_report,vaccination',
            'issue_date' => 'required|date',
            'valid_until' => 'nullable|date|after:issue_date',
            'diagnosis' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['certificate_number'] = 'MC-' . now()->format('Ymd') . '-' . str_pad(MedicalCertificate::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        $validated['doctor_id'] = Auth::id();
        $validated['status'] = 'approved';

        $certificate = MedicalCertificate::create($validated);

        return redirect()->route('healthcare.medical-certificates.show', $certificate)
            ->with('success', 'Medical certificate created');
    }

    public function show(MedicalCertificate $certificate)
    {
        $certificate->load(['patient', 'doctor']);
        return view('healthcare.medical-certificates.show', compact('certificate'));
    }

    public function print(MedicalCertificate $certificate)
    {
        $certificate->load(['patient', 'doctor']);
        return view('healthcare.medical-certificates.print', compact('certificate'));
    }

    public function destroy(MedicalCertificate $certificate)
    {
        $certificate->delete();
        return response()->json(['success' => true, 'message' => 'Certificate deleted']);
    }
}
