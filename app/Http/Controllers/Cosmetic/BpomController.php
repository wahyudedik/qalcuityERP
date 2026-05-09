<?php

namespace App\Http\Controllers\Cosmetic;

use App\Http\Controllers\Controller;
use App\Models\CosmeticBatchRecord;
use App\Models\CosmeticFormula;
use App\Models\ProductRegistration;
use App\Models\SafetyDataSheet;
use App\Services\BpomRegistrationService;
use Illuminate\Http\Request;

class BpomController extends Controller
{
    protected $bpomService;

    public function __construct(BpomRegistrationService $bpomService)
    {
        $this->bpomService = $bpomService;
    }

    /**
     * TASK-2.35: BPOM registration dashboard
     */
    public function dashboard(Request $request)
    {
        $stats = $this->bpomService->getRegistrationStats(auth()->user()->tenant_id);
        $expiringInfo = $this->bpomService->getExpiringRegistrations(auth()->user()->tenant_id, 90);

        $query = ProductRegistration::with(['formula', 'submitter', 'documents'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('product_category', $request->category);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                    ->orWhere('product_name', 'like', "%{$search}%");
            });
        }

        $registrations = $query->paginate(20);

        $categories = ProductRegistration::where('tenant_id', auth()->user()->tenant_id)
            ->distinct()
            ->pluck('product_category')
            ->sort()
            ->values();

        return view('cosmetic.bpom.dashboard', compact(
            'stats',
            'expiringInfo',
            'registrations',
            'categories'
        ));
    }

    /**
     * Show create registration form
     */
    public function create()
    {
        $formulas = CosmeticFormula::where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('status', ['approved', 'production'])
            ->orderBy('formula_name')
            ->get();

        return view('cosmetic.bpom.create', compact('formulas'));
    }

    /**
     * TASK-2.34: Store new BPOM registration
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'formula_id' => 'required|exists:cosmetic_formulas,id',
            'registration_number' => 'nullable|string|unique:product_registrations,registration_number',
            'product_name' => 'required|string|max:255',
            'product_category' => 'required|string',
            'registration_type' => 'required|in:notification,certification',
            'submission_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:submission_date',
            'notes' => 'nullable|string',
        ]);

        try {
            $registration = $this->bpomService->createRegistration(
                auth()->user()->tenant_id,
                $validated['formula_id'],
                $validated
            );

            return redirect()->route('cosmetic.bpom.show', $registration)
                ->with('success', 'BPOM registration created successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show registration details
     */
    public function show($id)
    {
        $registration = ProductRegistration::with([
            'formula',
            'formula.ingredients',
            'documents',
            'submitter',
        ])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        // Check compliance
        $complianceChecklist = [];
        if ($registration->formula) {
            $complianceChecklist = $this->bpomService->getComplianceChecklist($registration->formula);
        }

        return view('cosmetic.bpom.show', compact('registration', 'complianceChecklist'));
    }

    /**
     * Submit registration
     */
    public function submit($id)
    {
        try {
            $registration = ProductRegistration::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($id);

            $this->bpomService->submitRegistration($registration);

            return back()->with('success', 'Registration submitted to BPOM!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve registration
     */
    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'notified_by' => 'nullable|string|max:255',
        ]);

        try {
            $registration = ProductRegistration::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($id);

            $this->bpomService->approveRegistration(
                $registration,
                $validated['notified_by'] ?? 'BPOM Official'
            );

            return back()->with('success', 'Registration approved!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject registration
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        try {
            $registration = ProductRegistration::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($id);

            $this->bpomService->rejectRegistration($registration, $validated['rejection_reason']);

            return back()->with('success', 'Registration rejected.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Upload document
     */
    public function uploadDocument(Request $request, $id)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:certificate,formula,label,test_report,sds,other',
            'document_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        try {
            $registration = ProductRegistration::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($id);

            $this->bpomService->uploadDocument(
                $registration,
                $request->file('file'),
                $validated
            );

            return back()->with('success', 'Document uploaded successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * TASK-2.38: Compliance checklist
     */
    public function complianceChecklist($formulaId)
    {
        $formula = CosmeticFormula::where('tenant_id', auth()->user()->tenant_id)
            ->with(['ingredients', 'stabilityTests'])
            ->findOrFail($formulaId);

        $checklist = $this->bpomService->getComplianceChecklist($formula);

        return view('cosmetic.bpom.compliance-checklist', compact('formula', 'checklist'));
    }

    /**
     * TASK-2.36: QC Laboratory integration
     */
    public function qcIntegration()
    {
        // Show pending QC tests for registered products
        $registrations = ProductRegistration::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', 'approved')
            ->with(['formula.stabilityTests'])
            ->get();

        return view('cosmetic.bpom.qc-integration', compact('registrations'));
    }

    /**
     * TASK-2.37: Generate CoA
     */
    public function generateCoA($batchId)
    {
        try {
            $batch = CosmeticBatchRecord::where('tenant_id', auth()->user()->tenant_id)
                ->with(['formula', 'qualityChecks', 'qcInspector'])
                ->findOrFail($batchId);

            return $this->bpomService->generateCertificateOfAnalysis($batch);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Safety Data Sheets listing
     */
    public function safetyDataSheets()
    {
        $sdsList = SafetyDataSheet::with(['formula', 'registration'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderByDesc('issue_date')
            ->paginate(20);

        $needsReview = SafetyDataSheet::where('tenant_id', auth()->user()->tenant_id)
            ->needsReview()
            ->count();

        return view('cosmetic.bpom.sds.index', compact('sdsList', 'needsReview'));
    }

    /**
     * Create SDS
     */
    public function createSds()
    {
        $formulas = CosmeticFormula::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', 'approved')
            ->orderBy('formula_name')
            ->get();

        return view('cosmetic.bpom.sds.create', compact('formulas'));
    }

    /**
     * Store SDS
     */
    public function storeSds(Request $request)
    {
        $validated = $request->validate([
            'formula_id' => 'nullable|exists:cosmetic_formulas,id',
            'registration_id' => 'nullable|exists:product_registrations,id',
            'product_name' => 'required|string|max:255',
            'version' => 'nullable|string|max:20',
            'issue_date' => 'required|date',
            'review_date' => 'nullable|date|after:issue_date',
            'hazard_statements' => 'nullable|array',
            'precautionary_statements' => 'nullable|array',
            'first_aid_measures' => 'nullable|string',
            'fire_fighting_measures' => 'nullable|string',
            'handling_storage' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        try {
            $sds = new SafetyDataSheet;
            $sds->tenant_id = auth()->user()->tenant_id;
            $sds->formula_id = $validated['formula_id'] ?? null;
            $sds->registration_id = $validated['registration_id'] ?? null;
            $sds->sds_number = SafetyDataSheet::getNextSdsNumber();
            $sds->product_name = $validated['product_name'];
            $sds->version = $validated['version'] ?? '1.0';
            $sds->issue_date = $validated['issue_date'];
            $sds->review_date = $validated['review_date'] ?? null;
            $sds->hazard_statements = $validated['hazard_statements'] ?? null;
            $sds->precautionary_statements = $validated['precautionary_statements'] ?? null;
            $sds->first_aid_measures = $validated['first_aid_measures'] ?? null;
            $sds->fire_fighting_measures = $validated['fire_fighting_measures'] ?? null;
            $sds->handling_storage = $validated['handling_storage'] ?? null;
            $sds->status = 'draft';

            if ($request->hasFile('file')) {
                $path = $request->file('file')->store('sds-documents', 'public');
                $sds->file_path = $path;
            }

            $sds->save();

            return redirect()->route('cosmetic.bpom.sds.index')
                ->with('success', 'Safety Data Sheet created!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Activate SDS
     */
    public function activateSds($id)
    {
        try {
            $sds = SafetyDataSheet::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($id);

            $sds->activate();

            return back()->with('success', 'SDS activated!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete registration
     */
    public function destroy($id)
    {
        try {
            $registration = ProductRegistration::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($id);

            $registration->delete();

            return redirect()->route('cosmetic.bpom.dashboard')
                ->with('success', 'Registration deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
