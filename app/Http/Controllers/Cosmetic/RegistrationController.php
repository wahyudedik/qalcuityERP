<?php

namespace App\Http\Controllers\Cosmetic;

use App\Http\Controllers\Controller;
use App\Models\ProductRegistration;
use App\Models\RegistrationDocument;
use App\Models\IngredientRestriction;
use App\Models\SafetyDataSheet;
use App\Models\CosmeticFormula;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    /**
     * Display registrations dashboard
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // Stats
        $stats = [
            'total_registrations' => ProductRegistration::where('tenant_id', $tenantId)->count(),
            'approved' => ProductRegistration::where('tenant_id', $tenantId)->approved()->count(),
            'pending' => ProductRegistration::where('tenant_id', $tenantId)->pending()->count(),
            'expiring_soon' => ProductRegistration::where('tenant_id', $tenantId)->expiringSoon()->count(),
            'expired' => ProductRegistration::where('tenant_id', $tenantId)->expired()->count(),
            'active_sds' => SafetyDataSheet::where('tenant_id', $tenantId)->active()->count(),
        ];

        // Registrations with filters
        $registrations = ProductRegistration::where('tenant_id', $tenantId)
            ->with(['formula', 'submitter'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->category, fn($q) => $q->where('product_category', $request->category))
            ->latest()
            ->paginate(20);

        return view('cosmetic.registrations.index', compact('stats', 'registrations'));
    }

    /**
     * Show create registration form
     */
    public function create()
    {
        $formulas = CosmeticFormula::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', 'approved')
            ->get();

        return view('cosmetic.registrations.create', compact('formulas'));
    }

    /**
     * Store new registration
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'formula_id' => 'nullable|exists:cosmetic_formulas,id',
            'registration_number' => 'required|unique:product_registrations,registration_number',
            'product_name' => 'required|string|max:255',
            'product_category' => 'required|string',
            'registration_type' => 'required|in:notification,certification',
            'expiry_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string',
        ]);

        $registration = ProductRegistration::create([
            'tenant_id' => auth()->user()->tenant_id,
            'formula_id' => $validated['formula_id'] ?? null,
            'registration_number' => $validated['registration_number'],
            'product_name' => $validated['product_name'],
            'product_category' => $validated['product_category'],
            'registration_type' => $validated['registration_type'],
            'expiry_date' => $validated['expiry_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Auto-check ingredient compliance
        $compliance = $registration->checkIngredientCompliance();

        $message = 'Registration created successfully!';
        if (!$compliance['compliant']) {
            $message .= ' Warning: ' . count($compliance['issues']) . ' compliance issue(s) found.';
        }

        return redirect()->route('cosmetic.registrations.index')
            ->with('success', $message);
    }

    /**
     * Submit registration
     */
    public function submit($id)
    {
        $tenantId = auth()->user()->tenant_id;
        $registration = ProductRegistration::where('tenant_id', $tenantId)->findOrFail($id);

        $registration->submit(auth()->id());

        return redirect()->back()->with('success', 'Registration submitted!');
    }

    /**
     * Approve registration
     */
    public function approve(Request $request, $id)
    {
        $tenantId = auth()->user()->tenant_id;
        $registration = ProductRegistration::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'notified_by' => 'nullable|string',
            'approval_number' => 'nullable|string',
        ]);

        $registration->approve(
            $validated['notified_by'] ?? '',
            $validated['approval_number'] ?? $registration->registration_number
        );

        return redirect()->back()->with('success', 'Registration approved!');
    }

    /**
     * Display ingredient restrictions
     */
    public function restrictions()
    {
        $tenantId = auth()->user()->tenant_id;
        $restrictions = IngredientRestriction::where('tenant_id', $tenantId)
            ->latest()
            ->paginate(50);

        return view('cosmetic.registrations.restrictions', compact('restrictions'));
    }

    /**
     * Store ingredient restriction
     */
    public function storeRestriction(Request $request)
    {
        $validated = $request->validate([
            'ingredient_name' => 'required|string|max:255',
            'cas_number' => 'nullable|string',
            'restriction_type' => 'required|in:banned,restricted,limited',
            'max_limit' => 'nullable|numeric|min:0|max:100',
            'regulation_reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        IngredientRestriction::create([
            'tenant_id' => auth()->user()->tenant_id,
            'ingredient_name' => $validated['ingredient_name'],
            'cas_number' => $validated['cas_number'] ?? null,
            'restriction_type' => $validated['restriction_type'],
            'max_limit' => $validated['max_limit'] ?? null,
            'regulation_reference' => $validated['regulation_reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Ingredient restriction added!');
    }

    /**
     * Display safety data sheets
     */
    public function sdsIndex(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $stats = [
            'total_sds' => SafetyDataSheet::where('tenant_id', $tenantId)->count(),
            'active' => SafetyDataSheet::where('tenant_id', $tenantId)->active()->count(),
            'needs_review' => SafetyDataSheet::where('tenant_id', $tenantId)->needsReview()->count(),
        ];

        $sdsList = SafetyDataSheet::where('tenant_id', $tenantId)
            ->with(['formula', 'registration'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest('issue_date')
            ->paginate(20);

        return view('cosmetic.registrations.sds', compact('stats', 'sdsList'));
    }

    /**
     * Store safety data sheet
     */
    public function storeSds(Request $request)
    {
        $validated = $request->validate([
            'formula_id' => 'nullable|exists:cosmetic_formulas,id',
            'registration_id' => 'nullable|exists:product_registrations,id',
            'product_name' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'review_date' => 'nullable|date|after:issue_date',
            'hazard_statements' => 'nullable|array',
            'precautionary_statements' => 'nullable|array',
            'first_aid_measures' => 'nullable|string',
            'fire_fighting_measures' => 'nullable|string',
            'handling_storage' => 'nullable|string',
        ]);

        $sds = SafetyDataSheet::create([
            'tenant_id' => auth()->user()->tenant_id,
            'formula_id' => $validated['formula_id'] ?? null,
            'registration_id' => $validated['registration_id'] ?? null,
            'sds_number' => SafetyDataSheet::getNextSdsNumber(),
            'product_name' => $validated['product_name'],
            'issue_date' => $validated['issue_date'],
            'review_date' => $validated['review_date'] ?? null,
            'hazard_statements' => $validated['hazard_statements'] ?? null,
            'precautionary_statements' => $validated['precautionary_statements'] ?? null,
            'first_aid_measures' => $validated['first_aid_measures'] ?? null,
            'fire_fighting_measures' => $validated['fire_fighting_measures'] ?? null,
            'handling_storage' => $validated['handling_storage'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Safety Data Sheet created!');
    }

    /**
     * Activate SDS
     */
    public function activateSds($id)
    {
        $tenantId = auth()->user()->tenant_id;
        $sds = SafetyDataSheet::where('tenant_id', $tenantId)->findOrFail($id);

        $sds->activate();

        return redirect()->back()->with('success', 'SDS activated!');
    }

    /**
     * Create new SDS version
     */
    public function newSdsVersion($id)
    {
        $tenantId = auth()->user()->tenant_id;
        $sds = SafetyDataSheet::where('tenant_id', $tenantId)->findOrFail($id);

        $newSds = $sds->createNewVersion();

        return redirect()->back()->with('success', 'New SDS version created: ' . $newSds->sds_number);
    }
}
