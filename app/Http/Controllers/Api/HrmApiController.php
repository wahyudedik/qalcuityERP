<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Department;
use Illuminate\Http\Request;

class HrmApiController extends ApiBaseController
{
    /**
     * Get employees
     */
    public function employees(Request $request)
    {
        $query = Employee::where('tenant_id', $this->getTenantId())
            ->with(['department', 'position']);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('full_name', 'like', "%{$request->search}%")
                ->orWhere('employee_code', 'like', "%{$request->search}%");
        }

        $employees = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($employees);
    }

    /**
     * Get employee detail
     */
    public function employee($id)
    {
        $employee = Employee::where('tenant_id', $this->getTenantId())
            ->with(['department', 'position', 'attendances', 'leaveRequests', 'payrolls'])
            ->findOrFail($id);

        return $this->success($employee);
    }

    /**
     * Create employee
     */
    public function createEmployee(Request $request)
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|unique:employees,employee_code',
            'full_name' => 'required|string',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'hire_date' => 'required|date',
            'base_salary' => 'required|numeric|min:0',
            'status' => 'nullable|in:active,inactive,on_leave,terminated',
        ]);

        $employee = Employee::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'status' => $validated['status'] ?? 'active',
        ]));

        return $this->success($employee, 'Employee created successfully', 201);
    }

    /**
     * Update employee
     */
    public function updateEmployee(Request $request, $id)
    {
        $employee = Employee::where('tenant_id', $this->getTenantId())->findOrFail($id);

        $validated = $request->validate([
            'employee_code' => 'sometimes|string|unique:employees,employee_code,' . $id,
            'full_name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:employees,email,' . $id,
            'phone' => 'nullable|string',
            'department_id' => 'sometimes|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'hire_date' => 'sometimes|date',
            'base_salary' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:active,inactive,on_leave,terminated',
        ]);

        $employee->update($validated);

        return $this->success($employee, 'Employee updated successfully');
    }

    /**
     * Get attendance records
     */
    public function attendance(Request $request)
    {
        $query = Attendance::where('tenant_id', $this->getTenantId())
            ->with(['employee']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('check_in', $request->date);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('check_in', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('check_in', '<=', $request->date_to);
        }

        $attendances = $query->latest('check_in')->paginate($request->get('per_page', 20));

        return $this->success($attendances);
    }

    /**
     * Check in
     */
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $attendance = Attendance::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'check_in' => now(),
        ]));

        return $this->success($attendance, 'Check-in successful', 201);
    }

    /**
     * Check out
     */
    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
        ]);

        $attendance = Attendance::where('tenant_id', $this->getTenantId())
            ->findOrFail($validated['attendance_id']);

        $attendance->update(['check_out' => now()]);

        return $this->success($attendance, 'Check-out successful');
    }

    /**
     * Get leave requests
     */
    public function leaveRequests(Request $request)
    {
        $query = LeaveRequest::where('tenant_id', $this->getTenantId())
            ->with(['employee', 'approvedBy']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaveRequests = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($leaveRequests);
    }

    /**
     * Request leave
     */
    public function requestLeave(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|in:annual,sick,maternity,paternity,unpaid,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
        ]);

        $leaveRequest = LeaveRequest::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'status' => 'pending',
        ]));

        return $this->success($leaveRequest, 'Leave request submitted successfully', 201);
    }

    /**
     * Update leave status
     */
    public function updateLeaveStatus(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::where('tenant_id', $this->getTenantId())->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,cancelled',
            'notes' => 'nullable|string',
        ]);

        $leaveRequest->update(array_merge($validated, [
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]));

        return $this->success($leaveRequest, 'Leave status updated successfully');
    }

    /**
     * Get payroll records
     */
    public function payroll(Request $request)
    {
        $query = Payroll::where('tenant_id', $this->getTenantId())
            ->with(['employee']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payrolls = $query->latest('period')->paginate($request->get('per_page', 20));

        return $this->success($payrolls);
    }

    /**
     * Process payroll
     */
    public function processPayroll(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|string',
            'employee_ids' => 'nullable|array',
        ]);

        $query = Employee::where('tenant_id', $this->getTenantId())
            ->where('status', 'active');

        if (!empty($validated['employee_ids'])) {
            $query->whereIn('id', $validated['employee_ids']);
        }

        $employees = $query->get();
        $processed = [];

        foreach ($employees as $employee) {
            $payroll = Payroll::create([
                'tenant_id' => $this->getTenantId(),
                'employee_id' => $employee->id,
                'period' => $validated['period'],
                'base_salary' => $employee->base_salary,
                'allowances' => 0,
                'deductions' => 0,
                'net_salary' => $employee->base_salary,
                'status' => 'pending',
            ]);

            $processed[] = $payroll;
        }

        return $this->success($processed, 'Payroll processed for ' . count($processed) . ' employees', 201);
    }

    /**
     * Get payroll slip
     */
    public function payrollSlip($id)
    {
        $payroll = Payroll::where('tenant_id', $this->getTenantId())
            ->with(['employee', 'employee.department'])
            ->findOrFail($id);

        return $this->success($payroll);
    }

    /**
     * Get departments
     */
    public function departments(Request $request)
    {
        $query = Department::where('tenant_id', $this->getTenantId())
            ->withCount('employees');

        $departments = $query->paginate($request->get('per_page', 20));

        return $this->success($departments);
    }

    /**
     * Get employee performance
     */
    public function employeePerformance($employeeId)
    {
        $employee = Employee::where('tenant_id', $this->getTenantId())
            ->findOrFail($employeeId);

        $attendances = Attendance::where('tenant_id', $this->getTenantId())
            ->where('employee_id', $employeeId)
            ->whereMonth('check_in', now()->month)
            ->get();

        $leaveRequests = LeaveRequest::where('tenant_id', $this->getTenantId())
            ->where('employee_id', $employeeId)
            ->whereYear('created_at', now()->year)
            ->get();

        return $this->success([
            'employee' => $employee,
            'attendance_summary' => [
                'total_present' => $attendances->count(),
                'on_time' => $attendances->filter(function ($att) {
                    return $att->check_in && $att->check_in->format('H:i') <= '09:00';
                })->count(),
                'late' => $attendances->filter(function ($att) {
                    return $att->check_in && $att->check_in->format('H:i') > '09:00';
                })->count(),
            ],
            'leave_summary' => [
                'total_requests' => $leaveRequests->count(),
                'approved' => $leaveRequests->where('status', 'approved')->count(),
                'pending' => $leaveRequests->where('status', 'pending')->count(),
                'rejected' => $leaveRequests->where('status', 'rejected')->count(),
            ],
        ]);
    }
}
