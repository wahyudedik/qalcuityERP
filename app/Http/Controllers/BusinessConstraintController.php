<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BusinessConstraint;
use App\Services\BusinessConstraintService;
use Illuminate\Http\Request;

class BusinessConstraintController extends Controller
{
    public function __construct(private BusinessConstraintService $service) {}

    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index()
    {
        $tid = $this->tid();

        // Seed defaults jika belum ada
        BusinessConstraint::seedForTenant($tid);

        $constraints = BusinessConstraint::where('tenant_id', $tid)
            ->orderBy('key')
            ->get();

        return view('settings.business-constraints', compact('constraints'));
    }

    public function update(Request $request, BusinessConstraint $businessConstraint)
    {
        abort_if($businessConstraint->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'value' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        $businessConstraint->update([
            'value' => $data['value'],
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::record('constraint_updated',
            "Constraint \"{$businessConstraint->label}\" diubah ke: {$data['value']}", $businessConstraint);

        return back()->with('success', "Constraint \"{$businessConstraint->label}\" berhasil diperbarui.");
    }

    /** Bulk update semua constraints sekaligus */
    public function bulkUpdate(Request $request)
    {
        $tid = $this->tid();
        $data = $request->validate([
            'constraints' => 'required|array',
            'constraints.*.id' => 'required|exists:business_constraints,id',
            'constraints.*.value' => 'required|string|max:100',
            'constraints.*.active' => 'nullable|boolean',
        ]);

        foreach ($data['constraints'] as $item) {
            $constraint = BusinessConstraint::where('tenant_id', $tid)->find($item['id']);
            if ($constraint) {
                $constraint->update([
                    'value' => $item['value'],
                    'is_active' => isset($item['active']) ? (bool) $item['active'] : $constraint->is_active,
                ]);
            }
        }

        ActivityLog::record('constraints_bulk_updated', 'Business constraints diperbarui secara massal', null);

        $this->service->invalidateCache($tid);

        return back()->with('success', 'Semua constraint berhasil disimpan.');
    }
}
