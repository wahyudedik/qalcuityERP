<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\PACSStudy;
use App\Models\RadiologyExam;
use Illuminate\Http\Request;

class RadiologyController extends Controller
{
    /**
     * Display radiology dashboard.
     */
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        $statistics = [
            'pending_exams' => RadiologyExam::where('tenant_id', $tenantId)->where('status', 'scheduled')->count(),
            'in_progress' => RadiologyExam::where('tenant_id', $tenantId)->where('status', 'in_progress')->count(),
            'completed_today' => RadiologyExam::where('tenant_id', $tenantId)->where('status', 'completed')->whereDate('exam_date', today())->count(),
            'pending_reports' => RadiologyExam::where('tenant_id', $tenantId)->where('status', 'completed')->whereNull('report_text')->count(),
        ];

        $recentExams = RadiologyExam::with(['patient', 'doctor'])
            ->where('tenant_id', $tenantId)
            ->latest()
            ->limit(10)
            ->get();

        return view('healthcare.radiology.index', compact('statistics', 'recentExams'));
    }

    /**
     * Store new radiology exam.
     */
    public function storeExam(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'exam_type' => 'required|string|max:255',
            'body_part' => 'required|string|max:255',
            'exam_date' => 'required|date',
            'priority' => 'required|in:routine,urgent,stat',
            'clinical_indication' => 'nullable|string',
            'medical_record_id' => 'nullable|exists:patient_medical_records,id',
        ]);

        $exam = RadiologyExam::create($validated);

        return redirect()->route('healthcare.radiology.exams.show', $exam)
            ->with('success', 'Radiology exam scheduled successfully');
    }

    /**
     * Display radiology exams.
     */
    public function exams(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = RadiologyExam::with(['patient', 'doctor'])
            ->where('tenant_id', $tenantId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('exam_type')) {
            $query->where('exam_type', $request->exam_type);
        }

        if ($request->filled('date')) {
            $query->whereDate('exam_date', $request->date);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('exam_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('exam_date', '<=', $request->date_to);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $exams = $query->latest()->paginate(20)->withQueryString();

        // Stats - with tenant isolation
        $statistics = [
            'total_exams' => RadiologyExam::where('tenant_id', $tenantId)->count(),
            'scheduled_today' => RadiologyExam::where('tenant_id', $tenantId)
                ->whereDate('exam_date', today())
                ->count(),
            'completed_today' => RadiologyExam::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->whereDate('exam_date', today())
                ->count(),
            'pending_reports' => RadiologyExam::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->whereNull('report_text')
                ->count(),
            'urgent_exams' => RadiologyExam::where('tenant_id', $tenantId)
                ->where('priority', 'stat')
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->count(),
        ];

        return view('healthcare.radiology.exams', compact('exams', 'statistics'));
    }

    /**
     * Display radiology exam details.
     */
    public function showExam(RadiologyExam $exam)
    {
        $exam->load(['patient', 'doctor', 'images', 'pacsStudy']);

        return view('healthcare.radiology.exam-show', compact('exam'));
    }

    /**
     * Upload images for radiology exam.
     */
    public function uploadImages(RadiologyExam $exam, Request $request)
    {
        $validated = $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|file|mimes:jpg,jpeg,png,dicom|max:10240',
        ]);

        $uploadedImages = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store('radiology/'.$exam->id, 'public');

            $radiologyImage = $exam->images()->create([
                'patient_id' => $exam->patient_id,
                'image_path' => $path,
                'image_type' => $image->getClientOriginalExtension(),
                'file_size' => $image->getSize(),
                'uploaded_by' => auth()->id(),
            ]);

            $uploadedImages[] = $radiologyImage;
        }

        return back()->with('success', count($uploadedImages).' images uploaded successfully');
    }

    /**
     * Display PACS viewer.
     */
    public function pacsViewer($studyId)
    {
        $study = PACSStudy::with(['images', 'radiologyExam'])
            ->findOrFail($studyId);

        return view('healthcare.radiology.pacs-viewer', compact('study'));
    }

    /**
     * Add report to radiology exam.
     */
    public function addReport(RadiologyExam $exam, Request $request)
    {
        $validated = $request->validate([
            'report_text' => 'required|string',
            'findings' => 'nullable|string',
            'impression' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'radiologist_id' => 'required|exists:doctors,id',
        ]);

        $exam->update([
            'report_text' => $validated['report_text'],
            'findings' => $validated['findings'],
            'impression' => $validated['impression'],
            'recommendations' => $validated['recommendations'],
            'reported_by' => $validated['radiologist_id'],
            'reported_at' => now(),
            'status' => 'reported',
        ]);

        return back()->with('success', 'Radiology report added successfully');
    }

    /**
     * Display radiology dashboard.
     */
    public function dashboard()
    {
        $statistics = [
            'pending_exams' => RadiologyExam::where('status', 'scheduled')->count(),
            'in_progress' => RadiologyExam::where('status', 'in_progress')->count(),
            'completed_today' => RadiologyExam::where('status', 'completed')->whereDate('exam_date', today())->count(),
            'pending_reports' => RadiologyExam::where('status', 'completed')->whereNull('report_text')->count(),
        ];

        return view('healthcare.radiology.dashboard', compact('statistics'));
    }

    /**
     * Display radiology schedule.
     */
    public function schedule(Request $request)
    {
        $date = $request->get('date', today());

        $exams = RadiologyExam::with(['patient', 'doctor'])
            ->whereDate('exam_date', $date)
            ->orderBy('exam_date')
            ->get();

        return view('healthcare.radiology.schedule', compact('exams', 'date'));
    }

    /**
     * Display radiology reports.
     */
    public function reports(Request $request)
    {
        $query = RadiologyExam::whereNotNull('report_text')
            ->with(['patient', 'doctor']);

        if ($request->filled('date_from')) {
            $query->whereDate('exam_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('exam_date', '<=', $request->date_to);
        }

        $exams = $query->latest()->paginate(20);

        return view('healthcare.radiology.reports', compact('exams'));
    }
}
