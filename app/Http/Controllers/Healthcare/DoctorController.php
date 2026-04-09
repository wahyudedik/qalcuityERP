<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\MedicalStaffSchedule;
use App\Models\Appointment;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    /**
     * Display a listing of doctors.
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = Doctor::with(['department', 'schedules'])
            ->where('tenant_id', $tenantId);

        // Filters
        if ($request->filled('specialization')) {
            $query->where('specialization', 'like', "%{$request->specialization}%");
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        $doctors = $query->latest()->paginate(20)->withQueryString();

        // Stats - with tenant isolation
        $statistics = [
            'total_doctors' => Doctor::where('tenant_id', $tenantId)->count(),
            'active_doctors' => Doctor::where('tenant_id', $tenantId)->where('status', 'active')->count(),
            'available_today' => Doctor::where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->whereHas('schedules', function ($q) {
                    $q->where('day_of_week', now()->dayOfWeek)->where('is_active', true);
                })
                ->count(),
            'on_leave' => Doctor::where('tenant_id', $tenantId)->where('status', 'on_leave')->count(),
        ];

        return view('healthcare.doctors.index', compact('doctors', 'statistics'));
    }

    /**
     * Display the specified doctor.
     */
    public function show(Doctor $doctor)
    {
        $doctor->load(['department', 'schedules']);

        $statistics = [
            'total_appointments' => $doctor->appointments()->count(),
            'today_appointments' => $doctor->appointments()->whereDate('appointment_date', today())->count(),
            'completed_appointments' => $doctor->appointments()->where('status', 'completed')->count(),
            'total_patients' => $doctor->appointments()->distinct('patient_id')->count('patient_id'),
        ];

        $upcomingAppointments = $doctor->appointments()
            ->where('status', 'scheduled')
            ->whereDate('appointment_date', '>=', today())
            ->with('patient')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(10)
            ->get();

        return view('healthcare.doctors.show', compact('doctor', 'statistics', 'upcomingAppointments'));
    }

    /**
     * Get doctor schedule.
     */
    public function schedule(Doctor $doctor)
    {
        $schedules = $doctor->schedules()
            ->where('date', '>=', today())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return view('healthcare.doctors.schedule', compact('doctor', 'schedules'));
    }

    /**
     * Store doctor schedule.
     */
    public function storeSchedule(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'max_appointments' => 'required|integer|min:1',
            'is_available' => 'boolean',
        ]);

        // Check for existing schedule at same time
        $exists = MedicalStaffSchedule::where('doctor_id', $doctor->id)
            ->where('date', $validated['date'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function ($q2) use ($validated) {
                        $q2->where('start_time', '<=', $validated['start_time'])
                            ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Schedule conflicts with existing schedule');
        }

        $schedule = $doctor->schedules()->create($validated);

        return back()->with('success', 'Schedule added successfully');
    }

    /**
     * Get doctor appointments.
     */
    public function appointments(Doctor $doctor, Request $request)
    {
        $query = $doctor->appointments()->with('patient');

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(20);

        return view('healthcare.doctors.appointments', compact('doctor', 'appointments'));
    }
}
