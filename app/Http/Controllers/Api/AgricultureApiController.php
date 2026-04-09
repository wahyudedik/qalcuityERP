<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\Harvest;
use App\Models\Field;
use App\Models\PlantingCycle;
use Illuminate\Http\Request;

class AgricultureApiController extends ApiBaseController
{
    public function crops(Request $request)
    {
        $query = Crop::where('tenant_id', $this->getTenantId())
            ->with(['field', 'plantingCycle']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $crops = $query->latest()->paginate($request->get('per_page', 20));
        return $this->success($crops);
    }

    public function crop($id)
    {
        $crop = Crop::where('tenant_id', $this->getTenantId())
            ->with(['field', 'plantingCycle', 'harvests'])
            ->findOrFail($id);
        return $this->success($crop);
    }

    public function createCrop(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'field_id' => 'required|exists:fields,id',
            'planting_date' => 'required|date',
            'expected_harvest_date' => 'nullable|date',
            'quantity' => 'nullable|numeric',
            'status' => 'nullable|in:planted,growing,ready,harvested',
        ]);

        $crop = Crop::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'status' => $validated['status'] ?? 'planted',
        ]));

        return $this->success($crop, 'Crop created successfully', 201);
    }

    public function harvests(Request $request)
    {
        $query = Harvest::where('tenant_id', $this->getTenantId())
            ->with(['crop', 'field']);

        if ($request->filled('crop_id')) {
            $query->where('crop_id', $request->crop_id);
        }

        $harvests = $query->latest()->paginate($request->get('per_page', 20));
        return $this->success($harvests);
    }

    public function recordHarvest(Request $request)
    {
        $validated = $request->validate([
            'crop_id' => 'required|exists:crops,id',
            'field_id' => 'required|exists:fields,id',
            'quantity' => 'required|numeric|min:0',
            'quality' => 'nullable|in:premium,standard,low',
            'notes' => 'nullable|string',
        ]);

        $harvest = Harvest::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'harvest_date' => now(),
        ]));

        return $this->success($harvest, 'Harvest recorded successfully', 201);
    }

    public function fields(Request $request)
    {
        $query = Field::where('tenant_id', $this->getTenantId())
            ->with(['crops']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $fields = $query->latest()->paginate($request->get('per_page', 20));
        return $this->success($fields);
    }

    public function createField(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'area_size' => 'required|numeric|min:0',
            'location' => 'nullable|string',
            'soil_type' => 'nullable|string',
            'status' => 'nullable|in:available,planted,harvested,maintenance',
        ]);

        $field = Field::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'status' => $validated['status'] ?? 'available',
        ]));

        return $this->success($field, 'Field created successfully', 201);
    }

    public function plantingCycles(Request $request)
    {
        $query = PlantingCycle::where('tenant_id', $this->getTenantId())
            ->with(['crop', 'field']);

        $cycles = $query->latest()->paginate($request->get('per_page', 20));
        return $this->success($cycles);
    }

    public function createPlantingCycle(Request $request)
    {
        $validated = $request->validate([
            'crop_id' => 'required|exists:crops,id',
            'field_id' => 'required|exists:fields,id',
            'cycle_number' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
        ]);

        $cycle = PlantingCycle::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
        ]));

        return $this->success($cycle, 'Planting cycle created successfully', 201);
    }
}
