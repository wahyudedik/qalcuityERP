<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\BPJSClaim;
use App\Models\Patient;
use App\Services\DashboardCacheService;
use Illuminate\Http\Request;

class BPJSClaimController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = BPJSClaim::with(['patient'])->where('tenant_id', $tenantId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $claims = $query->orderBy('submission_date', 'desc')->paginate(20)->withQueryString();

        // Optimized stats with single query + caching
        $cacheKey = "stats:bpjs_claims:{$tenantId}";
        $statistics = DashboardCacheService::getStats($cacheKey, function () use ($tenantId) {
            $stats = BPJSClaim::where('tenant_id', $tenantId)
                ->selectRaw('status, COUNT(*) as count, SUM(claim_amount) as total_amount')
                ->groupBy('status')
                ->get();

            return [
                'total' => $stats->sum('count'),
                'pending' => $stats->firstWhere('status', 'pending')->count ?? 0,
                'approved' => $stats->firstWhere('status', 'approved')->count ?? 0,
                'rejected' => $stats->firstWhere('status', 'rejected')->count ?? 0,
                'total_amount' => $stats->sum('total_amount'),
            ];
        }, 300); // 5 minutes TTL

        return view('healthcare.bpjs-claims.index', compact('claims', 'statistics'));
    }

    public function create()
    {
        $patients = Patient::whereNotNull('bpjs_number')->get();
        return view('healthcare.bpjs-claims.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'claim_amount' => 'required|numeric|min:0',
            'diagnosis_code' => 'required|string|max:50',
            'procedure_code' => 'nullable|string|max:50',
            'submission_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $validated['claim_number'] = 'BPJS-' . now()->format('Ymd') . '-' . str_pad(BPJSClaim::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        $validated['status'] = 'pending';
        $validated['tenant_id'] = $tenantId;

        $claim = BPJSClaim::create($validated);

        // Clear cache
        DashboardCacheService::clearStats("stats:bpjs_claims:{$tenantId}");

        return redirect()->route('healthcare.bpjs-claims.show', $claim)
            ->with('success', 'BPJS claim created');
    }

    public function show(BPJSClaim $claim)
    {
        $claim->load(['patient']);
        return view('healthcare.bpjs-claims.show', compact('claim'));
    }

    public function submit(BPJSClaim $claim)
    {
        $claim->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Claim submitted to BPJS']);
    }

    public function updateStatus(Request $request, BPJSClaim $claim)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'approved_amount' => 'nullable|numeric|min:0',
            'rejection_reason' => 'nullable|string',
        ]);

        $claim->update([
            'status' => $validated['status'],
            'approved_amount' => $validated['approved_amount'] ?? 0,
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'adjudication_date' => now(),
        ]);

        // Clear cache
        DashboardCacheService::clearStats("stats:bpjs_claims:{$tenantId}");

        return response()->json(['success' => true, 'message' => 'Claim status updated']);
    }

    public function destroy(BPJSClaim $claim)
    {
        $tenantId = auth()->user()->tenant_id;

        $claim->delete();

        // Clear cache
        DashboardCacheService::clearStats("stats:bpjs_claims:{$tenantId}");

        return response()->json(['success' => true, 'message' => 'Claim deleted']);
    }
    /**
     * Show the form for editing.
     * Route: healthcare/bpjs-claims/{bpjs_claim}/edit
     */
    public function edit($model)
    {
        $this->authorize('update', $model);
        
        return view('healthcare.b-p-j-s-claim.edit', compact('model'));
    }
    /**
     * Update the specified resource.
     * Route: healthcare/bpjs-claims/{bpjs_claim}
     */
    public function update(Request $request, $model)
    {
        $this->authorize('update', $model);
        
        $validated = $request->validate([
            // TODO: Add validation rules
        ]);
        
        $model->update($validated);
        
        return redirect()->route('healthcare.bpjs-claims.update')
            ->with('success', 'Updated successfully.');
    }
}
