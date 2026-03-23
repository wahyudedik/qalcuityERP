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
    public function index(Request $request): View
    {
        $query = Tenant::withCount('users')->with('admins');

        if ($search = $request->input('search')) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        if ($status = $request->input('status')) {
            if ($status === 'active')   $query->where('is_active', true);
            if ($status === 'inactive') $query->where('is_active', false);
            if ($status === 'expired')  $query->where('is_active', true)->where(fn($q) => $q
                ->where(fn($q2) => $q2->where('plan', 'trial')->where('trial_ends_at', '<', now()))
                ->orWhere(fn($q2) => $q2->where('plan', '!=', 'trial')->whereNotNull('plan_expires_at')->where('plan_expires_at', '<', now())));
        }

        if ($plan = $request->input('plan')) {
            $query->where('plan', $plan);
        }

        $tenants = $query->latest()->paginate(20)->withQueryString();

        // Stats from DB (not paginated collection)
        $stats = [
            'total'    => Tenant::count(),
            'active'   => Tenant::where('is_active', true)->count(),
            'inactive' => Tenant::where('is_active', false)->count(),
            'trial'    => Tenant::where('plan', 'trial')->count(),
        ];

        return view('super-admin.tenants.index', compact('tenants', 'stats'));
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
