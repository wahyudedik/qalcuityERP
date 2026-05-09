<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CostCenter;
use App\Services\CostCenterService;
use Illuminate\Http\Request;

class CostCenterController extends Controller
{
    public function __construct(private CostCenterService $service) {}

    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $query = CostCenter::where('tenant_id', $this->tid())->with('parent');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('code', 'like', "%$s%")->orWhere('name', 'like', "%$s%"));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $centers = $query->orderBy('code')->get();
        $parents = CostCenter::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('code')->get();

        return view('cost-centers.index', compact('centers', 'parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'type' => 'required|in:department,branch,project,product_line',
            'parent_id' => 'nullable|exists:cost_centers,id',
            'description' => 'nullable|string|max:255',
        ]);

        $tid = $this->tid();

        try {
            $cc = $this->service->create($tid, array_merge($data, ['is_active' => true]));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['code' => $e->getMessage()])->withInput();
        }

        ActivityLog::record('cost_center_created', "Cost center dibuat: {$cc->code} - {$cc->name}", $cc);

        return back()->with('success', 'Cost center berhasil ditambahkan.');
    }

    public function update(Request $request, CostCenter $costCenter)
    {
        abort_if($costCenter->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:department,branch,project,product_line',
            'parent_id' => 'nullable|exists:cost_centers,id',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:255',
        ]);

        $costCenter->update($data);

        return back()->with('success', 'Cost center berhasil diperbarui.');
    }

    public function destroy(CostCenter $costCenter)
    {
        abort_if($costCenter->tenant_id !== $this->tid(), 403);

        try {
            $this->service->delete($costCenter);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Cost center berhasil dihapus.');
    }

    /** Laporan P&L per cost center */
    public function report(Request $request)
    {
        $tid = $this->tid();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $centers = CostCenter::where('tenant_id', $tid)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $report = $this->service->plReport($tid, $from, $to);
        $totals = $this->service->plTotals($report);

        return view('cost-centers.report', compact('report', 'totals', 'from', 'to', 'centers'));
    }
}
