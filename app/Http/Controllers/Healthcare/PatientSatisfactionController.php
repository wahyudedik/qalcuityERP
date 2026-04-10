<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\PatientSatisfaction;
use App\Models\PatientVisit;
use Illuminate\Http\Request;

class PatientSatisfactionController extends Controller
{
    public function index(Request $request)
    {
        $query = PatientSatisfaction::with(['patient', 'doctor', 'visit']);

        if ($request->filled('rating')) {
            $query->where('overall_rating', $request->rating);
        }

        $surveys = $query->orderBy('created_at', 'desc')->paginate(20);

        $statistics = [
            'total_surveys' => PatientSatisfaction::count(),
            'average_rating' => round(PatientSatisfaction::avg('overall_rating'), 2),
            'excellent' => PatientSatisfaction::where('overall_rating', 5)->count(),
            'good' => PatientSatisfaction::where('overall_rating', 4)->count(),
            'average' => PatientSatisfaction::where('overall_rating', 3)->count(),
            'poor' => PatientSatisfaction::whereIn('overall_rating', [1, 2])->count(),
        ];

        return view('healthcare.patient-satisfaction.index', compact('surveys', 'statistics'));
    }

    public function create()
    {
        $visits = PatientVisit::where('status', 'discharged')
            ->whereDoesntHave('satisfactionSurvey')
            ->with('patient')
            ->get();

        return view('healthcare.patient-satisfaction.create', compact('visits'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_visit_id' => 'required|exists:patient_visits,id',
            'overall_rating' => 'required|integer|min:1|max:5',
            'doctor_rating' => 'nullable|integer|min:1|max:5',
            'nurse_rating' => 'nullable|integer|min:1|max:5',
            'facility_rating' => 'nullable|integer|min:1|max:5',
            'cleanliness_rating' => 'nullable|integer|min:1|max:5',
            'comments' => 'nullable|string',
            'would_recommend' => 'nullable|boolean',
        ]);

        $validated['would_recommend'] = $request->has('would_recommend');

        $survey = PatientSatisfaction::create($validated);

        return redirect()->route('healthcare.patient-satisfaction.show', $survey)
            ->with('success', 'Satisfaction survey submitted');
    }

    public function show(PatientSatisfaction $survey)
    {
        $survey->load(['patient', 'doctor', 'visit']);
        return view('healthcare.patient-satisfaction.show', compact('survey'));
    }

    public function statistics()
    {
        $stats = [
            'overall' => round(PatientSatisfaction::avg('overall_rating'), 2),
            'by_doctor' => PatientSatisfaction::selectRaw('doctor_id, AVG(overall_rating) as avg_rating, COUNT(*) as count')
                ->whereNotNull('doctor_id')
                ->groupBy('doctor_id')
                ->get(),
            'by_department' => PatientSatisfaction::join('patient_visits', 'patient_satisfactions.patient_visit_id', '=', 'patient_visits.id')
                ->selectRaw('patient_visits.department, AVG(patient_satisfactions.overall_rating) as avg_rating, COUNT(*) as count')
                ->groupBy('patient_visits.department')
                ->get(),
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }

    public function destroy(PatientSatisfaction $survey)
    {
        $survey->delete();
        return response()->json(['success' => true, 'message' => 'Survey deleted']);
    }
    /**
     * Show the form for editing.
     * Route: healthcare/patient-satisfaction/{patient_satisfaction}/edit
     */
    public function edit($model)
    {
        $this->authorize('update', $model);
        
        return view('healthcare.patient-satisfaction.edit', compact('model'));
    }
    /**
     * Update the specified resource.
     * Route: healthcare/patient-satisfaction/{patient_satisfaction}
     */
    public function update(Request $request, $model)
    {
        $this->authorize('update', $model);
        
        $validated = $request->validate([
            // TODO: Add validation rules
        ]);
        
        $model->update($validated);
        
        return redirect()->route('healthcare.patient-satisfaction.update')
            ->with('success', 'Updated successfully.');
    }
}
