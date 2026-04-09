<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    /**
     * Display operating rooms.
     */
    public function operatingRooms()
    {
        // Get ORs from medical_equipment table
        $operatingRooms = []; // Will fetch MedicalEquipment where equipment_type includes OR

        $statistics = [
            'total_or' => 0,
            'available' => 0,
            'in_use' => 0,
            'maintenance' => 0,
            'utilization_rate' => 0,
        ];

        return view('healthcare.resources.or.index', compact('operatingRooms', 'statistics'));
    }

    /**
     * Schedule operating room.
     */
    public function scheduleOR(Request $request)
    {
        $validated = $request->validate([
            'operating_room_id' => 'required|exists:medical_equipment,id',
            'surgery_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'surgery_name' => 'required|string',
            'surgeon_id' => 'required|exists:doctors,id',
        ]);

        // Check for scheduling conflicts
        // Create surgery schedule

        return back()->with('success', 'Operating room scheduled successfully');
    }

    /**
     * Display OR schedule.
     */
    public function orSchedule(Request $request)
    {
        $date = $request->get('date', today());

        $schedules = []; // Will fetch surgery schedules for the date

        return view('healthcare.resources.or.schedule', compact('schedules', 'date'));
    }

    /**
     * Store new surgery.
     */
    public function storeSurgery(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'admission_id' => 'nullable|exists:admissions,id',
            'primary_surgeon_id' => 'required|exists:doctors,id',
            'assistant_surgeon_id' => 'nullable|exists:doctors,id',
            'anesthesiologist_id' => 'nullable|exists:doctors,id',
            'operating_room_id' => 'nullable|exists:medical_equipment,id',
            'surgery_date' => 'required|date',
            'scheduled_start_time' => 'required',
            'scheduled_end_time' => 'nullable',
            'surgery_name' => 'required|string',
            'surgery_description' => 'nullable|string',
            'urgency' => 'required|in:elective,urgent,emergency',
            'pre_op_diagnosis' => 'nullable|string',
        ]);

        // Create surgery schedule with team

        return back()->with('success', 'Surgery scheduled successfully');
    }

    /**
     * Display surgeries list.
     */
    public function surgeries(Request $request)
    {
        $query = []; // Will use SurgerySchedule model

        if ($request->filled('status')) {
            // Filter by status
        }

        if ($request->filled('date')) {
            // Filter by date
        }

        $surgeries = [];

        return view('healthcare.resources.surgeries.index', compact('surgeries'));
    }

    /**
     * Start surgery.
     */
    public function startSurgery($id)
    {
        // Update surgery status to 'in_progress'
        // Record actual start time

        return back()->with('success', 'Surgery started');
    }

    /**
     * Complete surgery.
     */
    public function completeSurgery($id, Request $request)
    {
        $validated = $request->validate([
            'actual_end_time' => 'required',
            'post_op_diagnosis' => 'nullable|string',
            'surgery_notes' => 'nullable|string',
            'complications' => 'nullable|string',
        ]);

        // Update surgery status to 'completed'
        // Record actual end time and duration
        // Save notes and complications

        return back()->with('success', 'Surgery completed successfully');
    }

    /**
     * Display medical equipment.
     */
    public function equipment(Request $request)
    {
        $query = []; // Will use MedicalEquipment model

        if ($request->filled('equipment_type')) {
            // Filter by type
        }

        if ($request->filled('status')) {
            // Filter by status
        }

        $equipment = [];

        return view('healthcare.resources.equipment', compact('equipment'));
    }

    /**
     * Display resource analytics.
     */
    public function analytics(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth());
        $dateTo = $request->get('date_to', now()->endOfMonth());

        $analytics = [
            'or_utilization' => 0,
            'equipment_utilization' => 0,
            'total_surgeries' => 0,
            'avg_surgery_duration' => 0,
            'equipment_downtime' => 0,
        ];

        return view('healthcare.resources.analytics', compact('analytics', 'dateFrom', 'dateTo'));
    }

    /**
     * Display resource dashboard.
     */
    public function dashboard()
    {
        $statistics = [
            'total_or' => 0,
            'available_or' => 0,
            'surgeries_today' => 0,
            'equipment_maintenance_due' => 0,
            'avg_utilization' => 0,
        ];

        $upcomingSurgeries = [];
        $maintenanceAlerts = [];

        return view('healthcare.resources.dashboard', compact('statistics', 'upcomingSurgeries', 'maintenanceAlerts'));
    }
}
