<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\PACSStudy;
use App\Models\RadiologyExam;
use App\Models\RadiologyOrder;
use Illuminate\Http\Request;

class RadiologyController extends Controller
{
    /**
     * Display radiology dashboard.
     */
    public function index()
    {
        $statistics = [
            'pending_exams' => RadiologyOrder::where('status', 'scheduled')->count(),
            'in_progress' => RadiologyOrder::where('status', 'in_progress')->count(),
            'completed_today' => RadiologyOrder::where('status', 'completed')->whereDate('completed_at', today())->count(),
            'pending_reports' => RadiologyOrder::where('status', 'completed')->whereNull('reported_at')->count(),
        ];

        $radiologyOrders = RadiologyOrder::with(['patient', 'doctor'])
            ->latest()
            ->paginate(20);

        return view('healthcare.radiology.index', compact('statistics', 'radiologyOrders'));
    }

    /**
     * Show form to create a new radiology order.
     */
    public function create()
    {
        $visits = \App\Models\PatientVisit::with('patient')
            ->where('visit_status', 'in_consultation')
            ->latest()
            ->get();

        return view('healthcare.radiology.create', compact('visits'));
    }

    /**
     * Store new radiology exam.
     */
    public function storeExam(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'exam_id' => 'required|exists:radiology_exams,id',
            'body_part' => 'required|string|max:255',
            'scheduled_date' => 'required|date',
            'priority' => 'required|in:routine,urgent,stat',
            'clinical_indication' => 'nullable|string',
        ]);

        $order = RadiologyOrder::create([
            'patient_id' => $validated['patient_id'],
            'ordered_by' => $validated['doctor_id'],
            'exam_id' => $validated['exam_id'],
            'scheduled_date' => $validated['scheduled_date'],
            'priority' => $validated['priority'],
            'clinical_indication' => $validated['clinical_indication'] ?? '',
            'order_date' => now(),
            'order_number' => 'RAD-ORD-' . now()->format('Ymd') . '-' . str_pad(RadiologyOrder::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
            'status' => 'scheduled',
        ]);

        return redirect()->route('healthcare.radiology.exams.show', $order)
            ->with('success', 'Radiology exam scheduled successfully');
    }

    /**
     * Display radiology exams.
     */
    public function exams(Request $request)
    {
        $query = RadiologyOrder::with(['patient', 'doctor']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('exam_type')) {
            $query->whereHas('exam', function ($q) use ($request) {
                $q->where('modality', $request->exam_type);
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_date', $request->date);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $exams = $query->latest()->paginate(20)->withQueryString();

        // Stats
        $statistics = [
            'total_exams' => RadiologyOrder::count(),
            'scheduled_today' => RadiologyOrder::whereDate('scheduled_date', today())->count(),
            'completed_today' => RadiologyOrder::where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),
            'pending_reports' => RadiologyOrder::where('status', 'completed')
                ->whereNull('reported_at')
                ->count(),
            'urgent_exams' => RadiologyOrder::where('priority', 'stat')
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->count(),
        ];

        return view('healthcare.radiology.exams', compact('exams', 'statistics'));
    }

    /**
     * Display radiology exam details.
     */
    public function showExam(RadiologyOrder $exam)
    {
        $exam->load(['patient', 'doctor', 'images']);

        return view('healthcare.radiology.exam-show', compact('exam'));
    }

    /**
     * Upload images for radiology exam.
     */
    public function uploadImages(RadiologyOrder $exam, Request $request)
    {
        $validated = $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|file|mimes:jpg,jpeg,png,dicom|max:10240',
        ]);

        $uploadedImages = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store('radiology/' . $exam->id, 'public');

            $radiologyImage = $exam->images()->create([
                'patient_id' => $exam->patient_id,
                'image_path' => $path,
                'image_type' => $image->getClientOriginalExtension(),
                'file_size' => $image->getSize(),
                'uploaded_by' => auth()->id(),
            ]);

            $uploadedImages[] = $radiologyImage;
        }

        return back()->with('success', count($uploadedImages) . ' images uploaded successfully');
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
    public function addReport(RadiologyOrder $exam, Request $request)
    {
        $validated = $request->validate([
            'findings' => 'required|string',
            'impression' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'radiologist_id' => 'required|exists:doctors,id',
        ]);

        $exam->update([
            'reported_by' => $validated['radiologist_id'],
            'reported_at' => now(),
            'status' => 'reported',
        ]);

        // Store detailed report in radiology_results
        $exam->results()->create([
            'patient_id' => $exam->patient_id,
            'reported_by' => $validated['radiologist_id'],
            'findings' => $validated['findings'],
            'impression' => $validated['impression'] ?? null,
            'recommendations' => $validated['recommendations'] ?? null,
            'report_date' => now(),
            'status' => 'final',
        ]);

        return back()->with('success', 'Radiology report added successfully');
    }

    /**
     * Display radiology dashboard.
     */
    public function dashboard()
    {
        $statistics = [
            'pending_exams' => RadiologyOrder::where('status', 'scheduled')->count(),
            'in_progress' => RadiologyOrder::where('status', 'in_progress')->count(),
            'completed_today' => RadiologyOrder::where('status', 'completed')->whereDate('completed_at', today())->count(),
            'pending_reports' => RadiologyOrder::where('status', 'completed')->whereNull('reported_at')->count(),
        ];

        $radiologyOrders = RadiologyOrder::with(['patient', 'doctor'])
            ->latest()
            ->paginate(20);

        return view('healthcare.radiology.index', compact('statistics', 'radiologyOrders'));
    }

    /**
     * Display radiology schedule.
     */
    public function schedule(Request $request)
    {
        $date = $request->get('date', today());

        $exams = RadiologyOrder::with(['patient', 'doctor'])
            ->whereDate('scheduled_date', $date)
            ->orderBy('scheduled_date')
            ->get();

        return view('healthcare.radiology.schedule', compact('exams', 'date'));
    }

    /**
     * Display radiology reports.
     */
    public function reports(Request $request)
    {
        $query = RadiologyOrder::whereNotNull('reported_at')
            ->with(['patient', 'doctor']);

        if ($request->filled('date_from')) {
            $query->whereDate('reported_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('reported_at', '<=', $request->date_to);
        }

        $exams = $query->latest()->paginate(20);

        return view('healthcare.radiology.reports', compact('exams'));
    }
}
