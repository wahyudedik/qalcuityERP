<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\RadiologyExam;
use App\Models\RadiologyOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RadiologyExamController extends Controller
{
    /**
     * Display a listing of radiology orders.
     */
    public function index(Request $request)
    {
        $query = RadiologyOrder::with(['patient', 'doctor', 'exam']);

        if ($request->filled('exam_type')) {
            $query->whereHas('exam', function ($q) use ($request) {
                $q->where('modality', $request->exam_type);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $exams = $query->orderBy('scheduled_date', 'desc')->paginate(20);

        $statistics = [
            'total_exams' => RadiologyOrder::count(),
            'scheduled' => RadiologyOrder::where('status', 'scheduled')->count(),
            'in_progress' => RadiologyOrder::where('status', 'in_progress')->count(),
            'completed' => RadiologyOrder::where('status', 'completed')->count(),
            'reports_pending' => RadiologyOrder::where('status', 'completed')->whereNull('reported_at')->count(),
        ];

        return view('healthcare.radiology-exams.index', compact('exams', 'statistics'));
    }

    /**
     * Show the form for creating a new radiology exam.
     */
    public function create(Request $request)
    {
        $order = null;
        if ($request->filled('order_id')) {
            $order = RadiologyOrder::with('patient')->findOrFail($request->order_id);
        }

        $examCatalog = RadiologyExam::where('is_active', true)->orderBy('exam_name')->get();

        return view('healthcare.radiology-exams.create', compact('order', 'examCatalog'));
    }

    /**
     * Store a newly created radiology order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'exam_id' => 'required|exists:radiology_exams,id',
            'scheduled_date' => 'required|date',
            'priority' => 'required|in:routine,urgent,stat',
            'clinical_indication' => 'required|string',
            'clinical_history' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $order = RadiologyOrder::create([
            'patient_id' => $validated['patient_id'],
            'exam_id' => $validated['exam_id'],
            'ordered_by' => Auth::id(),
            'order_number' => 'RAD-ORD-' . now()->format('Ymd') . '-' . str_pad(RadiologyOrder::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
            'order_date' => now(),
            'scheduled_date' => $validated['scheduled_date'],
            'priority' => $validated['priority'],
            'clinical_indication' => $validated['clinical_indication'],
            'clinical_history' => $validated['clinical_history'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'scheduled',
        ]);

        return redirect()->route('healthcare.radiology-exams.show', $order)
            ->with('success', 'Radiology exam scheduled successfully');
    }

    /**
     * Display the specified radiology order.
     */
    public function show(RadiologyOrder $radiology_exam)
    {
        $radiology_exam->load(['patient', 'doctor', 'exam', 'results']);

        return view('healthcare.radiology-exams.show', compact('radiology_exam'));
    }

    /**
     * Enter exam results and report.
     */
    public function enterReport(Request $request, RadiologyOrder $exam)
    {
        $validated = $request->validate([
            'findings' => 'required|string',
            'impression' => 'required|string',
            'recommendations' => 'nullable|string',
        ]);

        $exam->update([
            'reported_at' => now(),
            'reported_by' => Auth::id(),
            'status' => 'reported',
        ]);

        $exam->results()->create([
            'patient_id' => $exam->patient_id,
            'reported_by' => Auth::id(),
            'findings' => $validated['findings'],
            'impression' => $validated['impression'],
            'recommendations' => $validated['recommendations'] ?? null,
            'report_date' => now(),
            'status' => 'final',
        ]);

        return redirect()->route('healthcare.radiology-exams.show', $exam)
            ->with('success', 'Radiology report completed');
    }

    /**
     * Update exam status.
     */
    public function updateStatus(Request $request, RadiologyOrder $exam)
    {
        $validated = $request->validate([
            'status' => 'required|in:ordered,scheduled,in_progress,completed,reported,cancelled',
        ]);

        $exam->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Exam status updated successfully',
        ]);
    }

    /**
     * Print radiology report.
     */
    public function print(RadiologyOrder $radiology_exam)
    {
        $radiology_exam->load(['patient', 'doctor', 'exam', 'results']);

        return view('healthcare.radiology-exams.print', compact('radiology_exam'));
    }

    /**
     * Remove the specified radiology order.
     */
    public function destroy(RadiologyOrder $radiology_exam)
    {
        if ($radiology_exam->status === 'completed' || $radiology_exam->status === 'reported') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a completed or reported exam',
            ], 400);
        }

        $radiology_exam->delete();

        return response()->json([
            'success' => true,
            'message' => 'Radiology order deleted successfully',
        ]);
    }

    /**
     * Show the form for editing.
     */
    public function edit(RadiologyOrder $radiology_exam)
    {
        $examCatalog = RadiologyExam::where('is_active', true)->orderBy('exam_name')->get();

        return view('healthcare.radiology-exams.edit', compact('radiology_exam', 'examCatalog'));
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, RadiologyOrder $radiology_exam)
    {
        $validated = $request->validate([
            'scheduled_date' => 'nullable|date',
            'priority' => 'nullable|in:routine,urgent,stat',
            'clinical_indication' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $radiology_exam->update($validated);

        return redirect()->route('healthcare.radiology-exams.show', $radiology_exam)
            ->with('success', 'Updated successfully.');
    }

    /**
     * Complete an exam.
     */
    public function complete(Request $request, RadiologyOrder $exam)
    {
        $exam->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Exam marked as completed.');
    }
}
