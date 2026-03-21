<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

class HrmController extends Controller
{
    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $tid   = $this->tenantId();
        $query = Employee::where('tenant_id', $tid);

        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('position', 'like', "%$s%")->orWhere('department', 'like', "%$s%"));
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->department) {
            $query->where('department', $request->department);
        }

        $employees   = $query->orderBy('name')->paginate(20)->withQueryString();
        $departments = Employee::where('tenant_id', $tid)->whereNotNull('department')->distinct()->pluck('department');
        $totalActive = Employee::where('tenant_id', $tid)->where('status', 'active')->count();

        return view('hrm.index', compact('employees', 'departments', 'totalActive'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'position'   => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'salary'     => 'nullable|numeric|min:0',
            'phone'      => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:255',
            'join_date'  => 'nullable|date',
            'address'    => 'nullable|string',
        ]);

        $tid   = $this->tenantId();
        $count = Employee::where('tenant_id', $tid)->count() + 1;

        Employee::create([
            'tenant_id'   => $tid,
            'employee_id' => 'EMP-' . now()->format('Ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT),
            'name'        => $data['name'],
            'position'    => $data['position'] ?? null,
            'department'  => $data['department'] ?? null,
            'salary'      => $data['salary'] ?? null,
            'phone'       => $data['phone'] ?? null,
            'email'       => $data['email'] ?? null,
            'join_date'   => $data['join_date'] ?? today(),
            'address'     => $data['address'] ?? null,
            'status'      => 'active',
        ]);

        return back()->with('success', "Karyawan {$data['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, Employee $employee)
    {
        abort_unless($employee->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'position'   => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'salary'     => 'nullable|numeric|min:0',
            'phone'      => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:255',
            'join_date'  => 'nullable|date',
            'address'    => 'nullable|string',
            'status'     => 'required|in:active,inactive,resigned',
        ]);

        $employee->update($data);

        return back()->with('success', "Data {$employee->name} berhasil diperbarui.");
    }

    public function destroy(Employee $employee)
    {
        abort_unless($employee->tenant_id === $this->tenantId(), 403);
        $employee->update(['status' => 'resigned']);
        return back()->with('success', "{$employee->name} ditandai sebagai resign.");
    }

    public function attendance(Request $request)
    {
        $tid  = $this->tenantId();
        $date = $request->date ? \Carbon\Carbon::parse($request->date) : today();

        $employees = Employee::where('tenant_id', $tid)->where('status', 'active')->orderBy('name')->get();

        $attendances = Attendance::where('tenant_id', $tid)
            ->whereDate('date', $date)
            ->pluck('status', 'employee_id');

        $summary = Attendance::where('tenant_id', $tid)
            ->whereDate('date', $date)
            ->selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return view('hrm.attendance', compact('employees', 'attendances', 'date', 'summary'));
    }

    public function storeAttendance(Request $request)
    {
        $data = $request->validate([
            'date'    => 'required|date',
            'records' => 'required|array',
            'records.*.employee_id' => 'required|exists:employees,id',
            'records.*.status'      => 'required|in:present,absent,late,leave,sick',
            'records.*.notes'       => 'nullable|string',
        ]);

        $tid = $this->tenantId();

        foreach ($data['records'] as $rec) {
            Attendance::updateOrCreate(
                ['tenant_id' => $tid, 'employee_id' => $rec['employee_id'], 'date' => $data['date']],
                [
                    'status'   => $rec['status'],
                    'notes'    => $rec['notes'] ?? null,
                    'check_in' => $rec['status'] === 'present' ? now()->toTimeString() : null,
                ]
            );
        }

        return back()->with('success', 'Absensi berhasil disimpan.');
    }
}
