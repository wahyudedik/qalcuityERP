<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\MedicalEquipment;
use Illuminate\Http\Request;

class MedicalEquipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = MedicalEquipment::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('equipment_type')) {
            $query->where('equipment_type', $request->equipment_type);
        }

        $equipment = $query->orderBy('equipment_code')->paginate(20);

        $statistics = [
            'total' => MedicalEquipment::count(),
            'available' => MedicalEquipment::where('status', 'available')->count(),
            'in_use' => MedicalEquipment::where('status', 'in_use')->count(),
            'maintenance' => MedicalEquipment::where('status', 'maintenance')->count(),
            'out_of_service' => MedicalEquipment::where('status', 'out_of_service')->count(),
        ];

        return view('healthcare.medical-equipment.index', compact('equipment', 'statistics'));
    }

    public function create()
    {
        return view('healthcare.medical-equipment.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_code' => 'required|string|unique:medical_equipment,equipment_code|max:50',
            'equipment_name' => 'required|string|max:255',
            'equipment_type' => 'required|string|max:100',
            'manufacturer' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:available,in_use,maintenance,out_of_service',
            'notes' => 'nullable|string',
        ]);

        $equipment = MedicalEquipment::create($validated);

        return redirect()->route('healthcare.medical-equipment.show', $equipment)
            ->with('success', 'Equipment added successfully');
    }

    public function show(MedicalEquipment $equipment)
    {
        $equipment->load(['maintenanceLogs', 'calibrationRecords']);

        return view('healthcare.medical-equipment.show', compact('equipment'));
    }

    public function edit(MedicalEquipment $equipment)
    {
        return view('healthcare.medical-equipment.edit', compact('equipment'));
    }

    public function update(Request $request, MedicalEquipment $equipment)
    {
        $validated = $request->validate([
            'equipment_name' => 'required|string|max:255',
            'equipment_type' => 'required|string|max:100',
            'manufacturer' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:available,in_use,maintenance,out_of_service',
            'notes' => 'nullable|string',
        ]);

        $equipment->update($validated);

        return redirect()->route('healthcare.medical-equipment.index')
            ->with('success', 'Equipment updated successfully');
    }

    public function logMaintenance(Request $request, MedicalEquipment $equipment)
    {
        $validated = $request->validate([
            'maintenance_type' => 'required|in:preventive,corrective,calibration',
            'maintenance_date' => 'required|date',
            'technician' => 'required|string|max:255',
            'description' => 'required|string',
            'cost' => 'nullable|numeric|min:0',
            'next_maintenance_date' => 'nullable|date|after:maintenance_date',
        ]);

        $equipment->maintenanceLogs()->create($validated);

        return response()->json(['success' => true, 'message' => 'Maintenance logged']);
    }

    public function destroy(MedicalEquipment $equipment)
    {
        $equipment->delete();

        return response()->json(['success' => true, 'message' => 'Equipment deleted']);
    }
}
