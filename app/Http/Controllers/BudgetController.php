<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $period = $request->period ?? now()->format('Y-m');

        $budgets = Budget::where('tenant_id', $this->tid())
            ->where('period', $period)
            ->where('status', 'active')
            ->orderBy('department')
            ->orderBy('name')
            ->get();

        // Periods available
        $periods = Budget::where('tenant_id', $this->tid())
            ->distinct()->orderByDesc('period')->pluck('period');

        $totalBudget   = $budgets->sum('amount');
        $totalRealized = $budgets->sum('realized');
        $overCount     = $budgets->filter(fn($b) => $b->realized > $b->amount)->count();

        return view('budget.index', compact('budgets', 'period', 'periods', 'totalBudget', 'totalRealized', 'overCount'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'department'  => 'nullable|string|max:100',
            'category'    => 'nullable|string|max:100',
            'amount'      => 'required|numeric|min:0',
            'period'      => 'required|string|regex:/^\d{4}-\d{2}$/',
            'period_type' => 'required|in:monthly,quarterly,annual',
            'notes'       => 'nullable|string',
        ]);

        Budget::create(['tenant_id' => $this->tid(), 'realized' => 0, 'status' => 'active'] + $data);
        return back()->with('success', "Anggaran {$data['name']} berhasil dibuat.");
    }

    public function update(Request $request, Budget $budget)
    {
        abort_unless($budget->tenant_id === $this->tid(), 403);

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'department' => 'nullable|string|max:100',
            'category'   => 'nullable|string|max:100',
            'amount'     => 'required|numeric|min:0',
            'realized'   => 'nullable|numeric|min:0',
            'notes'      => 'nullable|string',
        ]);

        $budget->update($data);
        return back()->with('success', 'Anggaran berhasil diperbarui.');
    }

    public function destroy(Budget $budget)
    {
        abort_unless($budget->tenant_id === $this->tid(), 403);
        $budget->update(['status' => 'inactive']);
        return back()->with('success', 'Anggaran dinonaktifkan.');
    }
}
