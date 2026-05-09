<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\CriticalAlert;
use App\Models\Patient;
use App\Models\TriageAssessment;
use Illuminate\Http\Request;

class EmergencyController extends Controller
{
    /**
     * Display ER dashboard.
     */
    public function index()
    {
        $statistics = [
            'total_patients' => TriageAssessment::whereDate('triage_date', today())->count(),
            'critical' => TriageAssessment::where('triage_level', 'red')
                ->whereDate('triage_date', today())->count(),
            'emergency' => TriageAssessment::where('triage_level', 'yellow')
                ->whereDate('triage_date', today())->count(),
            'urgent' => TriageAssessment::where('triage_level', 'green')
                ->whereDate('triage_date', today())->count(),
            'active_alerts' => CriticalAlert::where('status', 'active')
                ->whereDate('created_at', today())->count(),
        ];

        $criticalPatients = TriageAssessment::with('patient')
            ->where('triage_level', 'red')
            ->whereDate('triage_date', today())
            ->latest()
            ->get();

        return view('healthcare.er.index', compact('statistics', 'criticalPatients'));
    }

    /**
     * Display ER patients.
     */
    public function patients(Request $request)
    {
        $query = TriageAssessment::with(['patient', 'doctor']);

        if ($request->filled('triage_level')) {
            $query->where('triage_level', $request->triage_level);
        }

        if ($request->filled('date')) {
            $query->whereDate('triage_date', $request->date);
        } else {
            $query->whereDate('triage_date', today());
        }

        $patients = $query->latest()->paginate(20);

        return view('healthcare.er.patients', compact('patients'));
    }

    /**
     * Display ER dashboard.
     */
    public function dashboard()
    {
        $statistics = [
            'total_patients' => TriageAssessment::whereDate('assessment_date', today())->count(),
            'by_triage_level' => TriageAssessment::whereDate('assessment_date', today())
                ->selectRaw('urgency_level, COUNT(*) as count')
                ->groupBy('urgency_level')
                ->pluck('count', 'urgency_level'),
            'active_alerts' => CriticalAlert::where('status', 'active')
                ->whereDate('created_at', today())->count(),
            'avg_wait_time' => TriageAssessment::whereDate('assessment_date', today())
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg')
                ->value('avg'),
        ];

        $activeAlerts = CriticalAlert::with('patient')
            ->where('status', 'active')
            ->whereDate('created_at', today())
            ->orderByDesc('severity')
            ->limit(10)
            ->get();

        return view('healthcare.er.dashboard', compact('statistics', 'activeAlerts'));
    }

    /**
     * Create critical alert.
     */
    public function createAlert(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'alert_type' => 'required|in:critical_lab,critical_vitals,allergy,medication_error,cardiac_arrest,respiratory_distress,sepsis,stroke,trauma,other',
            'severity' => 'required|in:low,medium,high,critical,life_threatening',
            'description' => 'required|string',
            'requires_escalation' => 'boolean',
        ]);

        $alert = CriticalAlert::create($validated);

        return back()->with('success', 'Critical alert created successfully');
    }

    /**
     * Display critical alerts.
     */
    public function alerts(Request $request)
    {
        $query = CriticalAlert::with(['patient', 'doctor']);

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $alerts = $query->latest()->paginate(20);

        return view('healthcare.er.alerts', compact('alerts'));
    }

    /**
     * Display ER throughput analytics.
     */
    public function throughput(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth());
        $dateTo = $request->get('date_to', now()->endOfMonth());

        $analytics = [
            'total_patients' => TriageAssessment::whereBetween('triage_date', [$dateFrom, $dateTo])->count(),
            'by_triage_level' => TriageAssessment::whereBetween('triage_date', [$dateFrom, $dateTo])
                ->selectRaw('triage_level, COUNT(*) as count')
                ->groupBy('triage_level')
                ->pluck('count', 'triage_level'),
            'by_disposition' => TriageAssessment::whereBetween('triage_date', [$dateFrom, $dateTo])
                ->whereNotNull('disposition')
                ->selectRaw('disposition, COUNT(*) as count')
                ->groupBy('disposition')
                ->pluck('count', 'disposition'),
            'avg_wait_time' => TriageAssessment::whereBetween('triage_date', [$dateFrom, $dateTo])
                ->whereNotNull('disposition')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg')
                ->value('avg'),
        ];

        return view('healthcare.er.throughput', compact('analytics', 'dateFrom', 'dateTo'));
    }

    /**
     * Admit ER patient to inpatient.
     */
    public function admit(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'triage_id' => 'required|exists:triage_assessments,id',
            'bed_id' => 'required|exists:beds,id',
            'doctor_id' => 'required|exists:doctors,id',
            'admission_diagnosis' => 'required|string',
        ]);

        // Create admission
        $admission = Admission::create([
            'patient_id' => $validated['patient_id'],
            'bed_id' => $validated['bed_id'],
            'doctor_id' => $validated['doctor_id'],
            'admission_date' => now(),
            'admission_diagnosis' => $validated['admission_diagnosis'],
            'admission_type' => 'emergency',
            'source' => 'emergency_room',
            'triage_assessment_id' => $validated['triage_id'],
        ]);

        // Update triage disposition
        $triage = TriageAssessment::findOrFail($validated['triage_id']);
        $triage->update(['disposition' => 'admitted']);

        return redirect()->route('healthcare.inpatient.admissions.show', $admission)
            ->with('success', 'Patient admitted to inpatient successfully');
    }
}
