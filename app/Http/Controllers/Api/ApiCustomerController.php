<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;

class ApiCustomerController extends ApiBaseController
{
    public function index(Request $request)
    {
        $customers = Customer::where('tenant_id', $this->tenantId())
            ->when($request->search, fn ($q) => $q->where('name', 'like', '%'.$request->search.'%'))
            ->paginate(50);

        return $this->ok($customers);
    }

    public function show(int $id)
    {
        $customer = Customer::where('tenant_id', $this->tenantId())->findOrFail($id);

        return $this->ok($customer);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        $customer = Customer::create(array_merge($validated, [
            'tenant_id' => $this->tenantId(),
            'is_active' => true,
        ]));

        return $this->created($customer);
    }

    public function update(Request $request, int $id)
    {
        $customer = Customer::where('tenant_id', $this->tenantId())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        $customer->update($validated);

        return $this->ok($customer);
    }
}
