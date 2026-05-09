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
     * Display a listing of radiology exams.
     */
    public function index(Request $request)
    {
        $query = RadiologyExam::query()->with(['radiologyOrder.patient', 'radiologist']);

        if ($request->filled('exam_type')) {
            $query->where('exam_type', $request->exam_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $exams = $query->orderBy('exam_date', 'desc')->paginate(20);

        $statistics = [
            'total_exams' => RadiologyExam::count(),
            'scheduled' => RadiologyExam::where('status', 'scheduled')->count(),
            'in_progress' => RadiologyExam::where('status', 'in_progress')->count(),
            'completed' => RadiologyExam::where('status', 'completed')->count(),
            'reports_pending' => RadiologyExam::where('status', 'completed')->whereNull('report')->count(),
        ];

        return view('healthcare.radiology-exams.index', compact('exams', 'statistics'));
    }

    /**
     * Show the form for creating a new radiology exam.
     */
    public function create(RadiologyOrder $order)
    {
        $order->load('patient');

        return view('healthcare.radiology-exams.create', compact('order'));
    }

    /**
     * Store a newly created radiology exam.
     */
    public function store(Request $request, RadiologyOrder $order)
    {
        $validated = $request->validate([
            'exam_type' => 'required|in:xray,mri,ct_scan,ultrasound,mammography,fluoroscopy',
            'body_part' => 'required|string|max:255',
            'exam_date' => 'required|date',
            'technician_id' => 'required|exists:users,id',
            'radiologist_id' => 'nullable|exists:users,id',
            'clinical_history' => 'nullable|string',
            'contrast_used' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['radiology_order_id'] = $order->id;
        $validated['status'] = 'scheduled';
        $validated['contrast_used'] = $request->has('contrast_used');

        $exam = RadiologyExam::create($validated);

        $order->update(['status' => 'in_progress']);

        return redirect()->route('healthcare.radiology-exams.show', $exam)
            ->with('success', 'Radiology exam scheduled successfully');
    }

    /**
     * Display the specified radiology exam.
     */
    public function show(RadiologyExam $exam)
    {
        $exam->load(['radiologyOrder.patient', 'technician', 'radiologist']);

        return view('healthcare.radiology-exams.show', compact('exam'));
    }

    /**
     * Enter exam results and report.
     */
    public function enterReport(Request $request, RadiologyExam $exam)
    {
        $validated = $request->validate([
            'findings' => 'required|string',
            'impression' => 'required|string',
            'recommendations' => 'nullable|string',
            'image_urls' => 'nullable|array',
        ]);

        $exam->update([
            'findings' => $validated['findings'],
            'impression' => $validated['impression'],
            'recommendations' => $validated['recommendations'] ?? null,
            'image_urls' => $validated['image_urls'] ?? [],
            'status' => 'completed',
            'report_date' => now(),
            'radiologist_id' => Auth::id(),
        ]);

        return redirect()->route('healthcare.radiology-exams.show', $exam)
            ->with('success', 'Radiology report completed');
    }

    /**
     * Update exam status.
     */
    public function updateStatus(Request $request, RadiologyExam $exam)
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
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
    public function print(RadiologyExam $exam)
    {
        $exam->load(['radiologyOrder.patient', 'radiologist']);

        return view('healthcare.radiology-exams.print', compact('exam'));
    }

    /**
     * Remove the specified radiology exam.
     */
    public function destroy(RadiologyExam $exam)
    {
        if ($exam->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a completed exam with report',
            ], 400);
        }

        $exam->delete();

        return response()->json([
            'success' => true,
            'message' => 'Radiology exam deleted successfully',
        ]);
    }

    /**
     * Show the form for editing.
     * Route: healthcare/radiology-exams/{radiology_exam}/edit
     */
    public function edit($model)
    {
        $this->authorize('update', $model);

        return view('healthcare.radiology-exam.edit', compact('model'));
    }

    /**
     * Update the specified resource.
     * Route: healthcare/radiology-exams/{radiology_exam}
     */
    public function update(Request $request, $model)
    {
        $this->authorize('update', $model);

        $validated = $request->validate([
            // TODO: Add validation rules
        ]);

        $model->update($validated);

        return redirect()->route('healthcare.radiology-exams.update')
            ->with('success', 'Updated successfully.');
    }

    /**
     * Complete.
     * Route: healthcare/radiology-exams/{exam}/complete
     */
    public function complete(Request $request, $model)
    {
        $this->authorize('update', $model);

        $validated = $request->validate([
            // TODO: Add validation rules
        ]);

        // TODO: Implement Complete logic

        return back()->with('success', 'Complete completed successfully.');
    }
}
