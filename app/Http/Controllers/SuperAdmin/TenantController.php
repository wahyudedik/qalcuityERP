<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::withCount('users')
            ->with('admins')
            ->latest()
            ->paginate(20);

        return view('super-admin.tenants.index', compact('tenants'));
    }

    public function show(Tenant $tenant): View
    {
        $tenant->load('users', 'subscriptionPlan');
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        return view('super-admin.tenants.show', compact('tenant', 'plans'));
    }

    public function toggleActive(Tenant $tenant): RedirectResponse
    {
        $tenant->update(['is_active' => ! $tenant->is_active]);

        $status = $tenant->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('super-admin.tenants.index')
            ->with('success', "Tenant \"{$tenant->name}\" berhasil {$status}.");
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        // Hapus semua user tenant dulu
        User::where('tenant_id', $tenant->id)->delete();
        $tenant->delete();

        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Tenant dihapus beserta semua penggunanya.');
    }

    public function updatePlan(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'plan'                 => 'required|in:trial,basic,pro,enterprise',
            'subscription_plan_id' => 'nullable|exists:subscription_plans,id',
            'plan_expires_at'      => 'nullable|date|after:today',
            'trial_ends_at'        => 'nullable|date',
        ]);

        // Jika plan bukan trial, hapus trial_ends_at
        if ($data['plan'] !== 'trial') {
            $data['trial_ends_at'] = null;
        }

        $tenant->update($data);

        return redirect()->route('super-admin.tenants.show', $tenant)
            ->with('success', "Paket tenant \"{$tenant->name}\" berhasil diperbarui ke " . ucfirst($data['plan']) . '.');
    }
}
