<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\MedicalBill;
use App\Models\InsuranceClaim;
use App\Models\Admission;
use Illuminate\Http\Request;

// Note: InsuranceClaim model will be created in Fase 3.x remaining models

class BillingController extends Controller
{
    /**
     * Display billing dashboard.
     */
    public function index()
    {
        $statistics = [
            'total_invoices' => MedicalBill::count(),
            'pending_payment' => MedicalBill::where('payment_status', 'pending')->count(),
            'paid' => MedicalBill::where('payment_status', 'paid')->count(),
            'overdue' => MedicalBill::where('payment_status', 'pending')
                ->where('due_date', '<', now())->count(),
            'total_revenue' => MedicalBill::where('payment_status', 'paid')->sum('total_amount'),
            'pending_claims' => InsuranceClaim::where('status', 'submitted')->count(),
        ];

        return view('healthcare.billing.index', compact('statistics'));
    }

    /**
     * Display invoices.
     */
    public function invoices(Request $request)
    {
        $query = MedicalBill::with(['patient', 'admission']);

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('bill_date', '>=', $request->date_from);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bill_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->latest()->paginate(20)->withQueryString();

        $statistics = [
            'total_invoices' => MedicalBill::count(),
            'unpaid_invoices' => MedicalBill::where('payment_status', 'unpaid')->count(),
            'partial_invoices' => MedicalBill::where('payment_status', 'partial')->count(),
            'paid_today' => MedicalBill::where('payment_status', 'paid')->whereDate('updated_at', today())->count(),
            'total_revenue' => MedicalBill::where('payment_status', 'paid')->sum('paid_amount'),
        ];

        return view('healthcare.billing.invoices.index', compact('invoices', 'statistics'));
    }

    /**
     * Store new invoice.
     */
    public function storeInvoice(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'admission_id' => 'nullable|exists:admissions,id',
            'bill_type' => 'required|in:consultation,procedure,medication,laboratory,radiology,room,other',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
            'items' => 'required|array',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $subtotal = collect($validated['items'])->sum(function ($item) {
            return $item['quantity'] * $item['unit_price'];
        });

        $discount = $validated['discount_amount'] ?? 0;
        $total = $subtotal - $discount;

        $invoice = MedicalBill::create([
            'patient_id' => $validated['patient_id'],
            'admission_id' => $validated['admission_id'] ?? null,
            'bill_type' => $validated['bill_type'],
            'bill_date' => $validated['bill_date'],
            'due_date' => $validated['due_date'],
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'total_amount' => $total,
            'bill_items' => $validated['items'],
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('healthcare.billing.invoices.show', $invoice)
            ->with('success', 'Invoice created successfully');
    }

    /**
     * Display invoice details.
     */
    public function showInvoice(MedicalBill $invoice)
    {
        $invoice->load(['patient', 'admission', 'payments']);

        return view('healthcare.billing.invoice-show', compact('invoice'));
    }

    /**
     * Process invoice payment.
     */
    public function payInvoice(MedicalBill $invoice, Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,credit_card,debit_card,bank_transfer,ewallet,insurance',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $payment = $invoice->payments()->create([
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'],
            'payment_date' => now(),
            'notes' => $validated['notes'],
        ]);

        // Update invoice status
        $invoice->update([
            'amount_paid' => $invoice->amount_paid + $validated['amount'],
        ]);

        $invoice->updatePaymentStatus();

        return back()->with('success', 'Payment processed successfully');
    }

    /**
     * Display insurance claims.
     */
    public function insuranceClaims(Request $request)
    {
        $query = InsuranceClaim::with(['patient', 'bill', 'insuranceProvider']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('insurance_provider_id')) {
            $query->where('insurance_provider_id', $request->insurance_provider_id);
        }

        $claims = $query->latest()->paginate(20);

        return view('healthcare.billing.insurance-claims', compact('claims'));
    }

    /**
     * Store new insurance claim.
     */
    public function storeClaim(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'bill_id' => 'required|exists:medical_bills,id',
            'insurance_provider_id' => 'required|exists:insurance_providers,id',
            'policy_number' => 'required|string|max:255',
            'claim_amount' => 'required|numeric|min:0',
            'diagnosis_codes' => 'nullable|array',
            'supporting_documents' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $claim = InsuranceClaim::create([
            'patient_id' => $validated['patient_id'],
            'bill_id' => $validated['bill_id'],
            'insurance_provider_id' => $validated['insurance_provider_id'],
            'policy_number' => $validated['policy_number'],
            'claim_amount' => $validated['claim_amount'],
            'diagnosis_codes' => $validated['diagnosis_codes'] ?? [],
            'status' => 'draft',
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('healthcare.billing.insurance-claims.show', $claim)
            ->with('success', 'Insurance claim created successfully');
    }

    /**
     * Display insurance claim details.
     */
    public function showClaim(InsuranceClaim $claim)
    {
        $claim->load(['patient', 'bill', 'insuranceProvider']);

        return view('healthcare.billing.claim-show', compact('claim'));
    }

    /**
     * Submit insurance claim.
     */
    public function submitClaim(InsuranceClaim $claim)
    {
        if ($claim->status !== 'draft') {
            return back()->with('error', 'Only draft claims can be submitted');
        }

        $claim->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return back()->with('success', 'Insurance claim submitted successfully');
    }

    /**
     * Display payment plans.
     */
    public function paymentPlans(Request $request)
    {
        $paymentPlans = MedicalBill::whereHas('paymentPlan')
            ->with(['patient', 'paymentPlan'])
            ->paginate(20);

        return view('healthcare.billing.payment-plans', compact('paymentPlans'));
    }

    /**
     * Display billing dashboard.
     */
    public function dashboard()
    {
        $statistics = [
            'total_invoices' => MedicalBill::count(),
            'pending_payment' => MedicalBill::where('payment_status', 'pending')->count(),
            'paid' => MedicalBill::where('payment_status', 'paid')->count(),
            'overdue' => MedicalBill::where('payment_status', 'pending')
                ->where('due_date', '<', now())->count(),
            'total_revenue' => MedicalBill::where('payment_status', 'paid')->sum('total_amount'),
            'pending_claims' => InsuranceClaim::where('status', 'submitted')->count(),
            'approved_claims' => InsuranceClaim::where('status', 'approved')->count(),
        ];

        $recentInvoices = MedicalBill::with('patient')
            ->latest()
            ->limit(10)
            ->get();

        $overdueInvoices = MedicalBill::with('patient')
            ->where('payment_status', 'pending')
            ->where('due_date', '<', now())
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        return view('healthcare.billing.dashboard', compact('statistics', 'recentInvoices', 'overdueInvoices'));
    }

    /**
     * Display billing reports.
     */
    public function reports(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth());
        $dateTo = $request->get('date_to', now()->endOfMonth());

        $report = [
            'total_billed' => MedicalBill::whereDate('bill_date', '>=', $dateFrom)
                ->whereDate('bill_date', '<=', $dateTo)
                ->sum('total_amount'),
            'total_collected' => MedicalBill::where('payment_status', 'paid')
                ->whereDate('bill_date', '>=', $dateFrom)
                ->whereDate('bill_date', '<=', $dateTo)
                ->sum('amount_paid'),
            'total_pending' => MedicalBill::where('payment_status', 'pending')
                ->whereDate('bill_date', '>=', $dateFrom)
                ->whereDate('bill_date', '<=', $dateTo)
                ->sum('total_amount'),
            'collection_rate' => 0,
            'by_type' => MedicalBill::whereDate('bill_date', '>=', $dateFrom)
                ->whereDate('bill_date', '<=', $dateTo)
                ->selectRaw('bill_type, COUNT(*) as count, SUM(total_amount) as total')
                ->groupBy('bill_type')
                ->get(),
        ];

        $report['collection_rate'] = $report['total_billed'] > 0
            ? round(($report['total_collected'] / $report['total_billed']) * 100, 2)
            : 0;

        return view('healthcare.billing.reports', compact('report', 'dateFrom', 'dateTo'));
    }
}
