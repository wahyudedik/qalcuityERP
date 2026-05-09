<?php

namespace App\Http\Controllers\Api;

use App\Models\BreedingRecord;
use App\Models\LivestockHealthRecord;
use App\Models\LivestockHerd;
use Illuminate\Http\Request;

class LivestockApiController extends ApiBaseController
{
    public function animals(Request $request)
    {
        $query = LivestockHerd::where('tenant_id', $this->getTenantId())
            ->with(['healthRecords', 'movements']);

        if ($request->filled('type')) {
            $query->where('animal_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $animals = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($animals);
    }

    public function animal($id)
    {
        $animal = LivestockHerd::where('tenant_id', $this->getTenantId())
            ->with(['healthRecords', 'movements', 'vaccinations', 'feedLogs'])
            ->findOrFail($id);

        return $this->success($animal);
    }

    public function createAnimal(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'animal_type' => 'required|string|max:50',
            'breed' => 'nullable|string|max:100',
            'initial_count' => 'required|integer|min:1',
            'entry_date' => 'required|date',
            'entry_age_days' => 'nullable|integer|min:0',
            'entry_weight_kg' => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'target_harvest_date' => 'nullable|date',
            'target_weight_kg' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $herd = LivestockHerd::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'code' => LivestockHerd::generateCode($this->getTenantId(), $validated['animal_type']),
            'current_count' => $validated['initial_count'],
            'status' => 'active',
        ]));

        return $this->success($herd, 'Livestock herd created successfully', 201);
    }

    public function healthRecords(Request $request)
    {
        $query = LivestockHealthRecord::where('tenant_id', $this->getTenantId())
            ->with(['herd']);

        if ($request->filled('livestock_herd_id')) {
            $query->where('livestock_herd_id', $request->livestock_herd_id);
        }

        $records = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($records);
    }

    public function recordHealth(Request $request)
    {
        $validated = $request->validate([
            'livestock_herd_id' => 'required|exists:livestock_herds,id',
            'type' => 'required|in:illness,treatment,observation,quarantine,recovery',
            'date' => 'required|date',
            'condition' => 'required|string|max:255',
            'affected_count' => 'nullable|integer|min:0',
            'death_count' => 'nullable|integer|min:0',
            'symptoms' => 'nullable|string|max:500',
            'medication' => 'nullable|string|max:255',
            'medication_cost' => 'nullable|numeric|min:0',
            'administered_by' => 'nullable|string|max:100',
            'severity' => 'nullable|in:low,medium,high,critical',
            'notes' => 'nullable|string',
        ]);

        $record = LivestockHealthRecord::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'user_id' => auth()->id(),
            'status' => $validated['type'] === 'recovery' ? 'resolved' : 'active',
        ]));

        return $this->success($record, 'Health record created successfully', 201);
    }

    public function breeding(Request $request)
    {
        $query = BreedingRecord::where('tenant_id', $this->getTenantId())
            ->with(['herd', 'recordedBy']);

        $records = $query->latest('mating_date')->paginate($request->get('per_page', 20));

        return $this->success($records);
    }

    public function recordBreeding(Request $request)
    {
        $validated = $request->validate([
            'livestock_herd_id' => 'nullable|exists:livestock_herds,id',
            'dam_id' => 'required|string',
            'sire_id' => 'required|string',
            'mating_date' => 'required|date',
            'mating_type' => 'required|in:natural,artificial_insemination,embryo_transfer',
            'expected_due_date' => 'nullable|date|after:mating_date',
            'genetics_line' => 'nullable|string',
            'genetic_traits' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $record = BreedingRecord::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'status' => 'pending',
            'recorded_by' => auth()->id(),
        ]));

        return $this->success($record, 'Breeding record created successfully', 201);
    }
}
