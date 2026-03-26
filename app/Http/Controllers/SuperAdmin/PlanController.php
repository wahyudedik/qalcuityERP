<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->get();
        return view('super-admin.plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('super-admin.plans.form', ['plan' => new SubscriptionPlan()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'slug'             => 'required|string|max:50|unique:subscription_plans,slug',
            'price_monthly'    => 'required|numeric|min:0',
            'price_yearly'     => 'required|numeric|min:0',
            'max_users'        => 'required|integer|min:-1',
            'max_ai_messages'  => 'required|integer|min:-1',
            'trial_days'       => 'required|integer|min:0',
            'sort_order'       => 'required|integer|min:0',
            'is_active'        => 'boolean',
        ]);

        $data['features'] = $this->buildFeatures($request);
        $data['is_active'] = $request->boolean('is_active', true);

        SubscriptionPlan::create($data);

        return redirect()->route('super-admin.plans.index')
            ->with('success', "Paket \"{$data['name']}\" berhasil dibuat.");
    }

    public function edit(SubscriptionPlan $plan): View
    {
        return view('super-admin.plans.form', compact('plan'));
    }

    public function update(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'slug'             => 'required|string|max:50|unique:subscription_plans,slug,' . $plan->id,
            'price_monthly'    => 'required|numeric|min:0',
            'price_yearly'     => 'required|numeric|min:0',
            'max_users'        => 'required|integer|min:-1',
            'max_ai_messages'  => 'required|integer|min:-1',
            'trial_days'       => 'required|integer|min:0',
            'sort_order'       => 'required|integer|min:0',
            'is_active'        => 'boolean',
        ]);

        $data['features'] = $this->buildFeatures($request);
        $data['is_active'] = $request->boolean('is_active', true);

        $plan->update($data);

        return redirect()->route('super-admin.plans.index')
            ->with('success', "Paket \"{$plan->name}\" berhasil diperbarui.");
    }

    public function destroy(SubscriptionPlan $plan): RedirectResponse
    {
        if ($plan->tenants()->count() > 0) {
            return back()->with('error', 'Paket tidak bisa dihapus karena masih digunakan oleh tenant.');
        }

        $plan->delete();
        return redirect()->route('super-admin.plans.index')
            ->with('success', 'Paket berhasil dihapus.');
    }

    public function toggleActive(SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update(['is_active' => !$plan->is_active]);
        return back()->with('success', "Paket \"{$plan->name}\" " . ($plan->is_active ? 'diaktifkan' : 'dinonaktifkan') . '.');
    }

    public function seed(): RedirectResponse
    {
        foreach (SubscriptionPlan::defaultPlans() as $planData) {
            SubscriptionPlan::updateOrCreate(['slug' => $planData['slug']], $planData);
        }
        return redirect()->route('super-admin.plans.index')
            ->with('success', '3 paket default berhasil dibuat/diperbarui.');
    }

    private function parseFeatures(string $raw): array
    {
        return array_values(array_filter(
            array_map('trim', explode("\n", $raw))
        ));
    }

    /**
     * Build features array from checkbox list + custom input.
     */
    private function buildFeatures(Request $request): array
    {
        $features = $request->input('features_list', []);
        $custom = $request->input('features_custom', '');
        if ($custom) {
            $extras = array_filter(array_map('trim', explode(',', $custom)));
            $features = array_merge($features, $extras);
        }
        // Fallback: if old textarea format is used
        if (empty($features) && $request->filled('features')) {
            $features = $this->parseFeatures($request->input('features', ''));
        }
        return array_values(array_unique($features));
    }
}
