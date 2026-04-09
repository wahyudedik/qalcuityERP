<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Livestock;
use App\Models\LivestockHealth;
use App\Models\LivestockBreeding;
use Illuminate\Http\Request;

class LivestockApiController extends ApiBaseController
{
    public function animals(Request $request)
    {
        $query = Livestock::where('tenant_id', $this->getTenantId())
            ->with(['healthRecords', 'breedingRecords']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $animals = $query->latest()->paginate($request->get('per_page', 20));
        return $this->success($animals);
    }

    public function animal($id)
    {
        $animal = Livestock::where('tenant_id', $this->getTenantId())
            ->with(['healthRecords', 'breedingRecords'])
            ->findOrFail($id);
        return $this->success($animal);
    }

    public function createAnimal(Request $request)
    {
        $validated = $request->validate([
            'tag_number' => 'required|string|unique:livestock,tag_number',
            'type' => 'required|string',
            'breed' => 'nullable|string',
            'gender' => 'required|in:male,female',
            'birth_date' => 'nullable|date',
            'weight' => 'nullable|numeric',
            'status' => 'nullable|in:healthy,sick,breeding,sold,deceased',
        ]);

        $animal = Livestock::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'status' => $validated['status'] ?? 'healthy',
        ]));

        return $this->success($animal, 'Animal created successfully', 201);
    }

    public function healthRecords(Request $request)
    {
        $query = LivestockHealth::where('tenant_id', $this->getTenantId())
            ->with(['livestock']);

        if ($request->filled('livestock_id')) {
            $query->where('livestock_id', $request->livestock_id);
        }

        $records = $query->latest()->paginate($request->get('per_page', 20));
        return $this->success($records);
    }

    public function recordHealth(Request $request)
    {
        $validated = $request->validate([
            'livestock_id' => 'required|exists:livestock,id',
            'type' => 'required|in:vaccination,treatment,checkup,deworming',
            'description' => 'required|string',
            'veterinarian' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'next_visit_date' => 'nullable|date',
        ]);

        $record = LivestockHealth::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'record_date' => now(),
        ]));

        return $this->success($record, 'Health record created successfully', 201);
    }

    public function breeding(Request $request)
    {
        $query = LivestockBreeding::where('tenant_id', $this->getTenantId())
            ->with(['mother', 'father']);

        $records = $query->latest()->paginate($request->get('per_page', 20));
        return $this->success($records);
    }

    public function recordBreeding(Request $request)
    {
        $validated = $request->validate([
            'mother_id' => 'required|exists:livestock,id',
            'father_id' => 'nullable|exists:livestock,id',
            'mating_date' => 'required|date',
            'expected_birth_date' => 'nullable|date',
            'status' => 'nullable|in:pregnant,given_birth,failed',
        ]);

        $record = LivestockBreeding::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'status' => $validated['status'] ?? 'pregnant',
        ]));

        return $this->success($record, 'Breeding record created successfully', 201);
    }
}
