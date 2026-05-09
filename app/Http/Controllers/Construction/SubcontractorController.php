<?php

namespace App\Http\Controllers\Construction;

use App\Http\Controllers\Controller;
use App\Models\Subcontractor;
use App\Models\SubcontractorContract;
use App\Notifications\Construction\ContractActivatedNotification;
use App\Services\SubcontractorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SubcontractorController extends Controller
{
    protected $subcontractorService;

    public function __construct(SubcontractorService $subcontractorService)
    {
        $this->subcontractorService = $subcontractorService;
    }

    /**
     * Display subcontractors list
     */
    public function index(Request $request)
    {
        $query = Subcontractor::where('tenant_id', auth()->user()->tenant_id);

        // Filter by specialization
        if ($request->filled('specialization')) {
            $query->where('specialization', $request->input('specialization'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $subcontractors = $query->orderBy('company_name')->paginate(20);

        return view('construction.subcontractors.index', compact('subcontractors'));
    }

    /**
     * Show create subcontractor form
     */
    public function create()
    {
        return view('construction.subcontractors.create');
    }

    /**
     * Store new subcontractor
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $subcontractor = $this->subcontractorService->registerSubcontractor($validated, auth()->user()->tenant_id);

        return redirect()->route('construction.subcontractors.show', $subcontractor)
            ->with('success', 'Subcontractor registered successfully.');
    }

    /**
     * Display subcontractor details
     */
    public function show(Subcontractor $subcontractor)
    {
        $this->authorize('view', $subcontractor);

        $subcontractor->load(['contracts.project']);
        $performance = $this->subcontractorService->getPerformanceSummary($subcontractor->id, auth()->user()->tenant_id);

        return view('construction.subcontractors.show', compact('subcontractor', 'performance'));
    }

    /**
     * Show create contract form
     */
    public function createContract(Subcontractor $subcontractor)
    {
        return view('construction.subcontractors.contracts.create', compact('subcontractor'));
    }

    /**
     * Store new contract
     */
    public function storeContract(Request $request, Subcontractor $subcontractor)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'scope_of_work' => 'required|string',
            'contract_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'payment_terms' => 'nullable|string',
            'retention_percentage' => 'nullable|numeric|min:0|max:100',
            'warranty_period_months' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['subcontractor_id'] = $subcontractor->id;
        $contract = $this->subcontractorService->createContract($validated, auth()->user()->tenant_id);

        return redirect()->route('construction.subcontractors.show', $subcontractor)
            ->with('success', 'Contract created successfully.');
    }

    /**
     * Activate contract
     */
    public function activateContract(SubcontractorContract $contract)
    {
        $this->authorize('update', $contract);

        $this->subcontractorService->activateContract($contract->id, auth()->user()->tenant_id);

        // Send notification to subcontractor contact person
        if ($contract->subcontractor->email) {
            Mail::to($contract->subcontractor->email)
                ->send(new ContractActivatedNotification($contract));
        }

        return back()->with('success', 'Contract activated successfully. Notification sent.');
    }

    /**
     * Submit payment claim
     */
    public function submitPaymentClaim(Request $request, SubcontractorContract $contract)
    {
        $validated = $request->validate([
            'billing_period' => 'required|string',
            'work_description' => 'required|string',
            'claimed_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $validated['contract_id'] = $contract->id;
        $payment = $this->subcontractorService->submitPaymentClaim($validated, auth()->user()->tenant_id);

        return back()->with('success', 'Payment claim submitted successfully.');
    }

    /**
     * Approve payment claim
     */
    public function approvePayment(Request $request, SubcontractorContract $contract)
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:subcontractor_payments,id',
            'approved_amount' => 'required|numeric|min:0',
        ]);

        $this->subcontractorService->approvePayment(
            $validated['payment_id'],
            auth()->user()->tenant_id,
            $validated['approved_amount']
        );

        return back()->with('success', 'Payment approved successfully.');
    }
}
