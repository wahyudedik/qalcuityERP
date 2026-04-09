<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\LabEquipment;
use Illuminate\Http\Request;

class LabEquipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = LabEquipment::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('connection_type')) {
            $query->where('connection_type', $request->connection_type);
        }

        $equipment = $query->orderBy('name')->paginate(20);

        $statistics = [
            'total' => LabEquipment::count(),
            'connected' => LabEquipment::where('is_connected', true)->count(),
            'disconnected' => LabEquipment::where('is_connected', false)->count(),
            'auto_poll_active' => LabEquipment::where('auto_poll_enabled', true)->count(),
        ];

        return view('healthcare.lab-equipment.index', compact('equipment', 'statistics'));
    }

    public function create()
    {
        return view('healthcare.lab-equipment.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'device_id' => 'required|string|unique:lab_equipment,device_id|max:100',
            'type' => 'required|in:hematology,chemistry,immunoassay,urinalysis,coagulation,microscope',
            'connection_type' => 'required|in:hl7,astm,serial,tcp',
            'ip_address' => 'nullable|string|max:100',
            'poll_interval' => 'required|integer|min:1|max:60',
            'auto_poll_enabled' => 'boolean',
        ]);

        $validated['auto_poll_enabled'] = $request->has('auto_poll_enabled');

        $equipment = LabEquipment::create($validated);

        return redirect()->route('healthcare.lab-equipment.show', $equipment)
            ->with('success', 'Lab equipment added');
    }

    public function show(LabEquipment $equipment)
    {
        $equipment->load(['connectionLogs']);
        return view('healthcare.lab-equipment.show', compact('equipment'));
    }

    public function testConnection(LabEquipment $equipment)
    {
        return response()->json(['success' => true, 'message' => 'Connection test initiated']);
    }

    public function toggleAutoPoll(LabEquipment $equipment)
    {
        $equipment->update(['auto_poll_enabled' => !$equipment->auto_poll_enabled]);

        return response()->json(['success' => true, 'message' => 'Auto-poll toggled']);
    }

    public function destroy(LabEquipment $equipment)
    {
        $equipment->delete();
        return response()->json(['success' => true, 'message' => 'Equipment deleted']);
    }
}
