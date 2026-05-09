<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeSalaryComponent;
use App\Models\SalaryComponent;
use Illuminate\Http\Request;

class SalaryComponentController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    // ── Master Komponen ──────────────────────────────────────

    public function index()
    {
        $tid = $this->tid();
        $components = SalaryComponent::where('tenant_id', $tid)->orderBy('type')->orderBy('name')->get();
        $employees = Employee::where('tenant_id', $tid)->where('status', 'active')->orderBy('name')->get();

        return view('payroll.components', compact('components', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:30',
            'type' => 'required|in:allowance,deduction',
            'calc_type' => 'required|in:fixed,percent_base',
            'default_amount' => 'required|numeric|min:0',
            'taxable' => 'boolean',
            'description' => 'nullable|string|max:255',
        ]);

        SalaryComponent::create(array_merge($data, [
            'tenant_id' => $this->tid(),
            'taxable' => $request->boolean('taxable'),
        ]));

        return back()->with('success', 'Komponen gaji berhasil ditambahkan.');
    }

    public function update(Request $request, SalaryComponent $component)
    {
        abort_unless($component->tenant_id === $this->tid(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:30',
            'type' => 'required|in:allowance,deduction',
            'calc_type' => 'required|in:fixed,percent_base',
            'default_amount' => 'required|numeric|min:0',
            'taxable' => 'boolean',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:255',
        ]);

        $component->update(array_merge($data, [
            'taxable' => $request->boolean('taxable'),
            'is_active' => $request->boolean('is_active', true),
        ]));

        return back()->with('success', 'Komponen gaji diperbarui.');
    }

    public function destroy(SalaryComponent $component)
    {
        abort_unless($component->tenant_id === $this->tid(), 403);
        $component->delete();

        return back()->with('success', 'Komponen dihapus.');
    }

    // ── Komponen per Karyawan ────────────────────────────────

    public function employeeComponents(Employee $employee)
    {
        abort_unless($employee->tenant_id === $this->tid(), 403);

        $assigned = EmployeeSalaryComponent::where('employee_id', $employee->id)
            ->with('component')
            ->get();
        $components = SalaryComponent::where('tenant_id', $this->tid())
            ->where('is_active', true)
            ->orderBy('type')->orderBy('name')
            ->get();

        return response()->json([
            'assigned' => $assigned,
            'components' => $components,
        ]);
    }

    public function assignComponent(Request $request, Employee $employee)
    {
        abort_unless($employee->tenant_id === $this->tid(), 403);

        $data = $request->validate([
            'salary_component_id' => 'required|exists:salary_components,id',
            'amount' => 'required|numeric|min:0',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        EmployeeSalaryComponent::updateOrCreate(
            ['employee_id' => $employee->id, 'salary_component_id' => $data['salary_component_id']],
            array_merge($data, ['tenant_id' => $this->tid(), 'is_active' => true])
        );

        return back()->with('success', 'Komponen gaji karyawan disimpan.');
    }

    public function removeComponent(Employee $employee, EmployeeSalaryComponent $empComponent)
    {
        abort_unless($employee->tenant_id === $this->tid(), 403);
        abort_unless($empComponent->employee_id === $employee->id, 403);
        $empComponent->delete();

        return back()->with('success', 'Komponen dihapus dari karyawan.');
    }

    // ── AJAX: komponen karyawan untuk modal ─────────────────

    public function employeeComponentsJson(Employee $employee)
    {
        abort_unless($employee->tenant_id === $this->tid(), 403);

        $assigned = EmployeeSalaryComponent::where('employee_id', $employee->id)
            ->where('is_active', true)
            ->with('component')
            ->get()
            ->map(fn ($ec) => [
                'id' => $ec->id,
                'comp_id' => $ec->salary_component_id,
                'name' => $ec->component->name,
                'type' => $ec->component->type,
                'calc_type' => $ec->component->calc_type,
                'amount' => $ec->amount,
            ]);

        $all = SalaryComponent::where('tenant_id', $this->tid())
            ->where('is_active', true)
            ->orderBy('type')->orderBy('name')
            ->get(['id', 'name', 'type', 'calc_type', 'default_amount']);

        return response()->json(['assigned' => $assigned, 'all' => $all]);
    }

    public function saveEmployeeComponents(Request $request, Employee $employee)
    {
        abort_unless($employee->tenant_id === $this->tid(), 403);

        $request->validate([
            'components' => 'array',
            'components.*.component_id' => 'required|exists:salary_components,id',
            'components.*.amount' => 'required|numeric|min:0',
        ]);

        // Delete existing then re-insert (simple upsert)
        EmployeeSalaryComponent::where('employee_id', $employee->id)->delete();

        foreach ($request->components ?? [] as $row) {
            EmployeeSalaryComponent::create([
                'tenant_id' => $this->tid(),
                'employee_id' => $employee->id,
                'salary_component_id' => $row['component_id'],
                'amount' => $row['amount'],
                'is_active' => true,
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
