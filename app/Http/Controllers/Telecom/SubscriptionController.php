<?php

namespace App\Http\Controllers\Telecom;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\NetworkDevice;
use App\Models\InternetPackage;
use App\Models\TelecomSubscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of subscriptions.
     */
    public function index(Request $request)
    {
        $query = TelecomSubscription::where('tenant_id', auth()->user()->tenant_id)
            ->with(['customer', 'package', 'device']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by package
        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        // Filter by device
        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        // Search customer
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $subscriptions = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => TelecomSubscription::where('tenant_id', auth()->user()->tenant_id)->count(),
            'active' => TelecomSubscription::where('tenant_id', auth()->user()->tenant_id)->where('status', 'active')->count(),
            'suspended' => TelecomSubscription::where('tenant_id', auth()->user()->tenant_id)->where('status', 'suspended')->count(),
            'expired' => TelecomSubscription::where('tenant_id', auth()->user()->tenant_id)->where('status', 'expired')->count(),
            'monthly_revenue' => TelecomSubscription::where('tenant_id', auth()->user()->tenant_id)
                ->where('status', 'active')
                ->join('internet_packages', 'telecom_subscriptions.package_id', '=', 'internet_packages.id')
                ->sum('internet_packages.price'),
        ];

        $packages = InternetPackage::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->get();

        $devices = NetworkDevice::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', 'online')
            ->get();

        return view('telecom.subscriptions.index', compact('subscriptions', 'stats', 'packages', 'devices'));
    }

    /**
     * Show the form for creating a new subscription.
     */
    public function create()
    {
        $customers = Customer::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        $packages = InternetPackage::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->get();

        $devices = NetworkDevice::where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('status', ['online', 'pending'])
            ->get();

        return view('telecom.subscriptions.create', compact('customers', 'packages', 'devices'));
    }

    /**
     * Store a newly created subscription.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'package_id' => 'required|exists:internet_packages,id',
            'device_id' => 'required|exists:network_devices,id',
            'started_at' => 'required|date',
            'ends_at' => 'nullable|date|after:started_at',
            'hotspot_username' => 'nullable|string|max:255',
            'hotspot_password' => 'nullable|string|max:255',
            'auth_type' => ['required', Rule::in(['username_password', 'mac_address', 'voucher'])],
            'notes' => 'nullable|string',
        ]);

        // Verify ownership
        $customer = Customer::findOrFail($validated['customer_id']);
        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $package = InternetPackage::findOrFail($validated['package_id']);
        if ($package->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        try {
            $subscription = TelecomSubscription::create([
                'tenant_id' => auth()->user()->tenant_id,
                'customer_id' => $validated['customer_id'],
                'package_id' => $validated['package_id'],
                'device_id' => $validated['device_id'],
                'started_at' => $validated['started_at'],
                'ends_at' => $validated['ends_at'] ?? null,
                'hotspot_username' => $validated['hotspot_username'] ?? null,
                'hotspot_password' => $validated['hotspot_password'] ?? null,
                'auth_type' => $validated['auth_type'],
                'status' => 'active',
                'quota_used_bytes' => 0,
                'quota_exceeded' => false,
                'notes' => $validated['notes'] ?? null,
            ]);

            return redirect()->route('telecom.subscriptions.show', $subscription)
                ->with('success', 'Subscription berhasil dibuat.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Gagal membuat subscription: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified subscription.
     */
    public function show(TelecomSubscription $subscription)
    {
        if ($subscription->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $subscription->load(['customer', 'package', 'device']);

        // Get usage summary
        $usageService = new \App\Services\Telecom\UsageTrackingService();
        $usageSummary = $usageService->getUsageSummary($subscription, 'monthly');

        return view('telecom.subscriptions.show', compact('subscription', 'usageSummary'));
    }

    /**
     * Show the form for editing the specified subscription.
     */
    public function edit(TelecomSubscription $subscription)
    {
        if ($subscription->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $customers = Customer::where('tenant_id', auth()->user()->tenant_id)->get();
        $packages = InternetPackage::where('tenant_id', auth()->user()->tenant_id)->get();
        $devices = NetworkDevice::where('tenant_id', auth()->user()->tenant_id)->get();

        return view('telecom.subscriptions.edit', compact('subscription', 'customers', 'packages', 'devices'));
    }

    /**
     * Update the specified subscription.
     */
    public function update(Request $request, TelecomSubscription $subscription)
    {
        if ($subscription->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'package_id' => 'required|exists:internet_packages,id',
            'ends_at' => 'nullable|date',
            'status' => ['required', Rule::in(['active', 'suspended', 'cancelled', 'expired'])],
            'notes' => 'nullable|string',
        ]);

        try {
            $subscription->update($validated);

            return redirect()->route('telecom.subscriptions.show', $subscription)
                ->with('success', 'Subscription berhasil diupdate.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Gagal mengupdate subscription: ' . $e->getMessage()]);
        }
    }

    /**
     * Suspend the subscription.
     */
    public function suspend(TelecomSubscription $subscription)
    {
        if ($subscription->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $subscription->update(['status' => 'suspended']);

        // Optionally disconnect user from router
        if ($subscription->hotspot_username) {
            try {
                $adapter = \App\Services\Telecom\RouterAdapterFactory::create($subscription->device);
                $adapter->disconnectUser($subscription->hotspot_username);
            } catch (\Exception $e) {
                \Log::warning("Failed to disconnect user on suspend: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Subscription disuspend.');
    }

    /**
     * Reactivate the subscription.
     */
    public function reactivate(TelecomSubscription $subscription)
    {
        if ($subscription->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $subscription->update(['status' => 'active']);

        return back()->with('success', 'Subscription diaktifkan kembali.');
    }

    /**
     * Reset quota for subscription.
     */
    public function resetQuota(TelecomSubscription $subscription)
    {
        if ($subscription->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $subscription->resetQuota();

        return back()->with('success', 'Quota berhasil direset.');
    }
    /**
     * Remove the specified subscription.
     */
    public function destroy(TelecomSubscription $subscription)
    {
        if ($subscription->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $subscription->update(['status' => 'cancelled']);
        $subscription->delete();

        return redirect()->route('telecom.subscriptions.index')
            ->with('success', 'Subscription berhasil dibatalkan dan dihapus.');
    }
}
