<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\DashboardCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments.
     */
    public function index(Request $request)
    {
        // Get authenticated user's tenant ID with null safety
        $user = Auth::user();
        $tenantId = $user?->tenant_id ?? abort(401, 'Unauthenticated.');

        $query = Appointment::with(['patient', 'doctor', 'department', 'createdBy'])
            ->where('tenant_id', $tenantId);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('appointment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('appointment_date', '<=', $request->date_to);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
            })->orWhereHas('doctor', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->whereHas('doctor', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        $appointments = $query->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(20)->withQueryString();

        // Stats - optimized with caching
        $cacheKey = "stats:appointments:{$tenantId}";
        $statistics = DashboardCacheService::getStats($cacheKey, function () use ($tenantId) {
            $stats = Appointment::where('tenant_id', $tenantId)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN DATE(appointment_date) = CURDATE() THEN 1 ELSE 0 END) as today,
                    SUM(CASE WHEN status = \'scheduled\' THEN 1 ELSE 0 END) as scheduled,
                    SUM(CASE WHEN status = \'completed\' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = \'cancelled\' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN status = \'no_show\' THEN 1 ELSE 0 END) as no_show
                ')
                ->first();

            return [
                'total_appointments' => $stats->total ?? 0,
                'today' => $stats->today ?? 0,
                'scheduled' => $stats->scheduled ?? 0,
                'completed' => $stats->completed ?? 0,
                'cancelled' => $stats->cancelled ?? 0,
                'no_show' => $stats->no_show ?? 0,
            ];
        }, 300);

        return view('healthcare.appointments.index', compact('appointments', 'statistics'));
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request)
    {
        // Get authenticated user's tenant ID with null safety
        $user = Auth::user();
        $tenantId = $user?->tenant_id ?? abort(401, 'Unauthenticated.');

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'schedule_id' => 'nullable|exists:medical_staff_schedules,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'reason_for_visit' => 'required|string|max:500',
            'appointment_type' => 'required|in:consultation,follow_up,check_up,procedure,telemedicine,emergency',
            'visit_type' => 'required|in:new_patient,return_patient',
            'symptoms' => 'nullable|string|max:1000',
            'special_requests' => 'nullable|string|max:500',
            'is_urgent' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check for scheduling conflicts
        $conflict = Appointment::where('doctor_id', $validated['doctor_id'])
            ->where('appointment_date', $validated['appointment_date'])
            ->where('status', 'scheduled')
            ->where(function ($q) use ($validated) {
                $q->where('appointment_time', $validated['appointment_time']);
            })
            ->exists();

        if ($conflict) {
            return back()->withInput()->with('error', 'Doctor has a scheduling conflict at this time');
        }

        $validated['tenant_id'] = $tenantId;
        $validated['created_by'] = Auth::id();
        $appointment = Appointment::create($validated);

        return redirect()->route('healthcare.appointments.show', $appointment)
            ->with('success', 'Appointment booked successfully');
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        // Verify tenant isolation with null safety
        $user = Auth::user();
        if ($appointment->tenant_id !== $user?->tenant_id) {
            abort(403, 'Unauthorized action.');
        }

        $appointment->load(['patient', 'doctor', 'department', 'createdBy', 'cancelledBy']);

        return view('healthcare.appointments.show', compact('appointment'));
    }

    /**
     * Update the specified appointment.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        // Verify tenant isolation with null safety
        $user = Auth::user();
        if ($appointment->tenant_id !== $user?->tenant_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'reason_for_visit' => 'nullable|string|max:500',
            'symptoms' => 'nullable|string|max:1000',
            'special_requests' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|in:scheduled,confirmed,checked_in,in_progress,completed,cancelled,no_show,rescheduled',
        ]);

        $appointment->update($validated);

        return redirect()->route('healthcare.appointments.show', $appointment)
            ->with('success', 'Appointment updated successfully');
    }

    /**
     * Remove the specified appointment from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $this->authorize('delete', $appointment);

        // Check if appointment can be deleted
        if (in_array($appointment->status, ['completed', 'in_progress'])) {
            return back()->with('error', 'Cannot delete completed or in-progress appointments');
        }

        $appointment->delete();

        return redirect()->route('healthcare.appointments.index')
            ->with('success', 'Appointment deleted successfully');
    }

    /**
     * Cancel the appointment.
     */
    public function cancel(Appointment $appointment)
    {
        if ($appointment->status === 'completed') {
            return back()->with('error', 'Cannot cancel completed appointment');
        }

        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => request('reason'),
        ]);

        return back()->with('success', 'Appointment cancelled successfully');
    }

    /**
     * Check-in patient for appointment.
     */
    public function checkIn(Appointment $appointment)
    {
        if ($appointment->status !== 'scheduled') {
            return back()->with('error', 'Only scheduled appointments can be checked in');
        }

        $appointment->update([
            'status' => 'in_progress',
            'check_in_time' => now(),
        ]);

        // Auto-assign queue number if outpatient
        if ($appointment->visit_type === 'general' || $appointment->visit_type === 'specialist') {
            $queue = \App\Models\QueueManagement::create([
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'queue_type' => $appointment->visit_type === 'specialist' ? 'specialist' : 'outpatient',
                'status' => 'waiting',
            ]);
        }

        return back()->with('success', 'Patient checked in successfully');
    }
}
