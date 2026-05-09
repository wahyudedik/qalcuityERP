<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\OperatingRoom;
use App\Models\Patient;
use App\Models\SurgerySchedule;
use App\Services\DashboardCacheService;
use Illuminate\Http\Request;

class SurgeryScheduleController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = SurgerySchedule::with(['patient', 'surgeon', 'operatingRoom'])
            ->where('tenant_id', $tenantId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('surgery_type')) {
            $query->where('surgery_type', $request->surgery_type);
        }

        $schedules = $query->orderBy('scheduled_date', 'desc')->paginate(20)->withQueryString();

        // Stats - optimized with caching
        $cacheKey = "stats:surgery_schedules:{$tenantId}";
        $statistics = DashboardCacheService::getStats($cacheKey, function () use ($tenantId) {
            $stats = SurgerySchedule::where('tenant_id', $tenantId)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN status = \'scheduled\' THEN 1 ELSE 0 END) as scheduled,
                    SUM(CASE WHEN status = \'in_progress\' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = \'completed\' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = \'cancelled\' THEN 1 ELSE 0 END) as cancelled
                ')
                ->first();

            return [
                'total' => $stats->total ?? 0,
                'scheduled' => $stats->scheduled ?? 0,
                'in_progress' => $stats->in_progress ?? 0,
                'completed' => $stats->completed ?? 0,
                'cancelled' => $stats->cancelled ?? 0,
            ];
        }, 300);

        return view('healthcare.surgery-schedules.index', compact('schedules', 'statistics'));
    }

    public function create()
    {
        // ✅ OPTIMIZED: Added tenant_id filter to prevent cross-tenant data leakage
        $patients = Patient::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->get();
        $doctors = Doctor::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->get();
        $operatingRooms = OperatingRoom::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->get();

        return view('healthcare.surgery-schedules.create', compact('patients', 'doctors', 'operatingRooms'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'surgeon_id' => 'required|exists:doctors,id',
            'operating_room_id' => 'required|exists:operating_rooms,id',
            'surgery_type' => 'required|string|max:255',
            'scheduled_date' => 'required|date',
            'start_time' => 'required',
            'estimated_duration' => 'required|integer|min:15',
            'diagnosis' => 'nullable|string',
            'procedure_notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $tenantId;
        $validated['surgery_number'] = 'SRG-'.now()->format('Ymd').'-'.str_pad(SurgerySchedule::where('tenant_id', $tenantId)->whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        $validated['status'] = 'scheduled';

        $schedule = SurgerySchedule::create($validated);

        // Clear cache
        DashboardCacheService::clearStats("stats:surgery_schedules:{$tenantId}");

        return redirect()->route('healthcare.surgery-schedules.show', $schedule)
            ->with('success', 'Surgery scheduled successfully');
    }

    public function show(SurgerySchedule $schedule)
    {
        $schedule->load(['patient', 'surgeon', 'operatingRoom', 'surgeryTeam', 'equipment']);

        return view('healthcare.surgery-schedules.show', compact('schedule'));
    }

    public function start(SurgerySchedule $schedule)
    {
        $tenantId = auth()->user()->tenant_id;

        $schedule->update([
            'status' => 'in_progress',
            'actual_start_time' => now(),
        ]);

        // Clear cache
        DashboardCacheService::clearStats("stats:surgery_schedules:{$tenantId}");

        return response()->json(['success' => true, 'message' => 'Surgery started']);
    }

    public function complete(Request $request, SurgerySchedule $schedule)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'surgery_notes' => 'nullable|string',
            'complications' => 'nullable|string',
            'outcome' => 'nullable|in:successful,partial,unsuccessful',
        ]);

        $schedule->update([
            'status' => 'completed',
            'actual_end_time' => now(),
            'surgery_notes' => $validated['surgery_notes'] ?? null,
            'complications' => $validated['complications'] ?? null,
            'outcome' => $validated['outcome'] ?? null,
        ]);

        // Clear cache
        DashboardCacheService::clearStats("stats:surgery_schedules:{$tenantId}");

        return response()->json(['success' => true, 'message' => 'Surgery completed']);
    }

    public function updateStatus(Request $request, SurgerySchedule $schedule)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled,postponed',
        ]);

        $schedule->update($validated);

        // Clear cache
        DashboardCacheService::clearStats("stats:surgery_schedules:{$tenantId}");

        return response()->json(['success' => true, 'message' => 'Status updated']);
    }

    public function destroy(SurgerySchedule $schedule)
    {
        $tenantId = auth()->user()->tenant_id;

        if ($schedule->status === 'in_progress') {
            return response()->json(['success' => false, 'message' => 'Cannot delete surgery in progress'], 400);
        }

        $schedule->delete();

        // Clear cache
        DashboardCacheService::clearStats("stats:surgery_schedules:{$tenantId}");

        return response()->json(['success' => true, 'message' => 'Surgery deleted']);
    }

    /**
     * Show the form for editing.
     * Route: healthcare/surgery-schedules/{surgery_schedule}/edit
     */
    public function edit($model)
    {
        $this->authorize('update', $model);

        return view('healthcare.surgery-schedule.edit', compact('model'));
    }

    /**
     * Update the specified resource.
     * Route: healthcare/surgery-schedules/{surgery_schedule}
     */
    public function update(Request $request, $model)
    {
        $this->authorize('update', $model);

        $validated = $request->validate([
            // TODO: Add validation rules
        ]);

        $model->update($validated);

        return redirect()->route('healthcare.surgery-schedules.update')
            ->with('success', 'Updated successfully.');
    }
}
