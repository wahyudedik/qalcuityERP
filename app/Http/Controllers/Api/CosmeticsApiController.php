<?php

namespace App\Http\Controllers\Api;

use App\Models\BpomRegistration;
use App\Models\CosmeticBatch;
use App\Models\CosmeticFormulation;
use Illuminate\Http\Request;

class CosmeticsApiController extends ApiBaseController
{
    public function formulations(Request $request)
    {
        $query = CosmeticFormulation::where('tenant_id', $this->getTenantId())
            ->with(['bpomRegistration']);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $formulations = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($formulations);
    }

    public function formulation($id)
    {
        $formulation = CosmeticFormulation::where('tenant_id', $this->getTenantId())
            ->with(['bpomRegistration', 'batches'])
            ->findOrFail($id);

        return $this->success($formulation);
    }

    public function createFormulation(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category' => 'required|string',
            'ingredients' => 'required|array',
            'instructions' => 'nullable|string',
            'shelf_life_months' => 'nullable|integer',
        ]);

        $formulation = CosmeticFormulation::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
        ]));

        return $this->success($formulation, 'Formulation created successfully', 201);
    }

    public function bpomRegistrations(Request $request)
    {
        $query = BpomRegistration::where('tenant_id', $this->getTenantId())
            ->with(['formulation']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $registrations = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($registrations);
    }

    public function registerBpom(Request $request)
    {
        $validated = $request->validate([
            'formulation_id' => 'required|exists:cosmetic_formulations,id',
            'registration_number' => 'nullable|string',
            'application_date' => 'required|date',
            'documents' => 'nullable|array',
        ]);

        $registration = BpomRegistration::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'status' => 'pending',
        ]));

        return $this->success($registration, 'BPOM registration submitted successfully', 201);
    }

    public function batches(Request $request)
    {
        $query = CosmeticBatch::where('tenant_id', $this->getTenantId())
            ->with(['formulation']);

        if ($request->filled('formulation_id')) {
            $query->where('formulation_id', $request->formulation_id);
        }

        $batches = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($batches);
    }

    public function createBatch(Request $request)
    {
        $validated = $request->validate([
            'formulation_id' => 'required|exists:cosmetic_formulations,id',
            'batch_number' => 'required|string|unique:cosmetic_batches,batch_number',
            'production_date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'quantity' => 'required|numeric|min:0',
        ]);

        $batch = CosmeticBatch::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
        ]));

        return $this->success($batch, 'Batch created successfully', 201);
    }
}
