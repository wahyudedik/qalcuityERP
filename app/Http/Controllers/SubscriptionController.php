<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $tenant = $user->tenant;
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        return view('subscription.index', compact('tenant', 'plans'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $tenant = $user->tenant;
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        return view('subscription.index', compact('tenant', 'plans'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        abort_unless($tenantId, 403);

        $data = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $plan = SubscriptionPlan::findOrFail($data['plan_id']);
        $tenant = auth()->user()->tenant;

        $tenant->update([
            'subscription_plan_id' => $plan->id,
        ]);

        return redirect()->route('subscription.index')
            ->with('success', "Langganan berhasil diperbarui ke paket {$plan->name}.");
    }

    public function edit(int $id): View
    {
        $tenantId = auth()->user()->tenant_id;
        abort_unless($tenantId, 403);

        $tenant = auth()->user()->tenant;
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        return view('subscription.index', compact('tenant', 'plans'));
    }

    public function update(Request $request, int $id)
    {
        $tenantId = auth()->user()->tenant_id;
        abort_unless($tenantId, 403);

        $data = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $plan = SubscriptionPlan::findOrFail($data['plan_id']);
        $tenant = auth()->user()->tenant;

        $tenant->update([
            'subscription_plan_id' => $plan->id,
        ]);

        return redirect()->route('subscription.index')
            ->with('success', "Langganan berhasil diubah ke paket {$plan->name}.");
    }

    public function destroy(int $id)
    {
        $tenantId = auth()->user()->tenant_id;
        abort_unless($tenantId, 403);

        $tenant = auth()->user()->tenant;
        abort_unless($tenant && $tenant->id === $id, 403);

        $tenant->update([
            'subscription_plan_id' => null,
        ]);

        return redirect()->route('subscription.index')
            ->with('success', 'Langganan berhasil dibatalkan.');
    }
}
