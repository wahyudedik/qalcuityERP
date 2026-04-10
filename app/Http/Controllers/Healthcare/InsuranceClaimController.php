<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\InsuranceClaim;
use App\Models\Patient;
use App\Models\PatientInsurance;
use App\Models\MedicalBill;
use Illuminate\Http\Request;

class InsuranceClaimController extends Controller
{
    public function index(Request $request)
    {
        $query = InsuranceClaim::with(['patient', 'insurance', 'medicalBill']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $claims = $query->orderBy('submission_date', 'desc')->paginate(20);

        $statistics = [
            'total_claims' => InsuranceClaim::count(),
            'pending' => InsuranceClaim::where('status', 'pending')->count(),
            'submitted' => InsuranceClaim::where('status', 'submitted')->count(),
            'approved' => InsuranceClaim::where('status', 'approved')->count(),
            'rejected' => InsuranceClaim::where('status', 'rejected')->count(),
            'total_claimed' => InsuranceClaim::sum('claim_amount'),
            'total_approved' => InsuranceClaim::where('status', 'approved')->sum('approved_amount'),
        ];

        return view('healthcare.insurance-claims.index', compact('claims', 'statistics'));
    }

    public function create(MedicalBill $bill)
    {
        $bill->load(['patient.insurances']);
        return view('healthcare.insurance-claims.create', compact('bill'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'medical_bill_id' => 'required|exists:medical_bills,id',
            'patient_id' => 'required|exists:patients,id',
            'insurance_id' => 'required|exists:patient_insurances,id',
            'claim_amount' => 'required|numeric|min:0',
            'diagnosis_code' => 'required|string|max:50',
            'claim_notes' => 'nullable|string',
        ]);

        $validated['claim_number'] = 'CLM-' . now()->format('Ymd') . '-' . str_pad(InsuranceClaim::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        $validated['submission_date'] = now();
        $validated['status'] = 'pending';

        $claim = InsuranceClaim::create($validated);

        return redirect()->route('healthcare.insurance-claims.show', $claim)
            ->with('success', 'Insurance claim created: ' . $claim->claim_number);
    }

    public function show(InsuranceClaim $claim)
    {
        $claim->load(['patient', 'insurance', 'medicalBill']);
        return view('healthcare.insurance-claims.show', compact('claim'));
    }

    public function submit(InsuranceClaim $claim)
    {
        $claim->update([
            'status' => 'submitted',
            'submitted_to_insurance_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Claim submitted to insurance']);
    }

    public function adjudicate(Request $request, InsuranceClaim $claim)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,partial',
            'approved_amount' => 'nullable|numeric|min:0',
            'rejection_reason' => 'nullable|string',
            'adjudication_notes' => 'nullable|string',
        ]);

        $claim->update([
            'status' => $validated['status'],
            'approved_amount' => $validated['approved_amount'] ?? 0,
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'adjudication_notes' => $validated['adjudication_notes'] ?? null,
            'adjudication_date' => now(),
        ]);

        return redirect()->route('healthcare.insurance-claims.show', $claim)
            ->with('success', 'Claim adjudication recorded');
    }

    public function resubmit(InsuranceClaim $claim)
    {
        if ($claim->status !== 'rejected') {
            return response()->json(['success' => false, 'message' => 'Only rejected claims can be resubmitted'], 400);
        }

        $claim->update([
            'status' => 'submitted',
            'resubmission_count' => $claim->resubmission_count + 1,
            'last_resubmission_date' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Claim resubmitted']);
    }

    public function print(InsuranceClaim $claim)
    {
        $claim->load(['patient', 'insurance', 'medicalBill']);
        return view('healthcare.insurance-claims.print', compact('claim'));
    }

    public function destroy(InsuranceClaim $claim)
    {
        if ($claim->status === 'approved') {
            return response()->json(['success' => false, 'message' => 'Cannot delete approved claim'], 400);
        }

        $claim->delete();
        return response()->json(['success' => true, 'message' => 'Claim deleted']);
    }
    /**
     * Show the form for editing.
     * Route: healthcare/insurance-claims/{insurance_claim}/edit
     */
    public function edit($model)
    {
        $this->authorize('update', $model);
        
        return view('healthcare.insurance-claim.edit', compact('model'));
    }
    /**
     * Update the specified resource.
     * Route: healthcare/insurance-claims/{insurance_claim}
     */
    public function update(Request $request, $model)
    {
        $this->authorize('update', $model);
        
        $validated = $request->validate([
            // TODO: Add validation rules
        ]);
        
        $model->update($validated);
        
        return redirect()->route('healthcare.insurance-claims.update')
            ->with('success', 'Updated successfully.');
    }
}
