<?php

namespace App\Http\Controllers\Telecom;

use App\Http\Controllers\Controller;
use App\Models\InternetPackage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    /**
     * Display a listing of packages.
     */
    public function index(Request $request)
    {
        $query = InternetPackage::where('tenant_id', auth()->user()->tenant_id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by quota type
        if ($request->filled('quota_type')) {
            if ($request->quota_type === 'unlimited') {
                $query->whereNull('quota_bytes');
            } else {
                $query->whereNotNull('quota_bytes');
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $packages = $query->withCount('subscriptions')
            ->orderBy('price', 'asc')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => InternetPackage::where('tenant_id', auth()->user()->tenant_id)->count(),
            'active' => InternetPackage::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->count(),
            'inactive' => InternetPackage::where('tenant_id', auth()->user()->tenant_id)->where('is_active', false)->count(),
            'unlimited' => InternetPackage::where('tenant_id', auth()->user()->tenant_id)->whereNull('quota_bytes')->count(),
        ];

        return view('telecom.packages.index', compact('packages', 'stats'));
    }

    /**
     * Show the form for creating a new package.
     */
    public function create()
    {
        return view('telecom.packages.create');
    }

    /**
     * Store a newly created package.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'features' => 'nullable|string',
            'download_speed_mbps' => 'required|numeric|min:0.1',
            'upload_speed_mbps' => 'required|numeric|min:0.1',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => ['required', Rule::in(['monthly', 'quarterly', 'yearly'])],
            'quota_type' => 'nullable|in:unlimited,limited',
            'quota_gb' => 'nullable|numeric|min:1',
            'quota_bytes' => 'nullable|integer|min:0',
            'quota_period' => ['nullable', Rule::in(['hourly', 'daily', 'weekly', 'monthly'])],
            'setup_fee' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        // Convert quota_gb to quota_bytes if provided
        $quotaBytes = null;
        if ($request->quota_type === 'limited' && $request->filled('quota_gb')) {
            $quotaBytes = (int) ($request->quota_gb * 1073741824);
        } elseif ($request->filled('quota_bytes')) {
            $quotaBytes = (int) $validated['quota_bytes'];
        }

        // Parse features from textarea (one per line)
        $features = null;
        if ($request->filled('features')) {
            $features = array_filter(array_map('trim', explode("\n", $request->features)));
        }

        try {
            InternetPackage::create([
                'tenant_id' => auth()->user()->tenant_id,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'features' => $features,
                'download_speed_mbps' => $validated['download_speed_mbps'],
                'upload_speed_mbps' => $validated['upload_speed_mbps'],
                'price' => $validated['price'],
                'billing_cycle' => $validated['billing_cycle'],
                'quota_bytes' => $quotaBytes,
                'quota_period' => $validated['quota_period'] ?? 'monthly',
                'installation_fee' => $validated['setup_fee'] ?? 0,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('telecom.packages.index')
                ->with('success', 'Package berhasil ditambahkan.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Gagal menambahkan package: '.$e->getMessage()]);
        }
    }

    /**
     * Display the specified package.
     */
    public function show(InternetPackage $package)
    {
        if ($package->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $package->load('subscriptions.customer');

        return view('telecom.packages.show', compact('package'));
    }

    /**
     * Show the form for editing the specified package.
     */
    public function edit(InternetPackage $package)
    {
        if ($package->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        return view('telecom.packages.edit', compact('package'));
    }

    /**
     * Update the specified package.
     */
    public function update(Request $request, InternetPackage $package)
    {
        if ($package->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'download_speed_mbps' => 'required|numeric|min:0.1',
            'upload_speed_mbps' => 'required|numeric|min:0.1',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => ['required', Rule::in(['monthly', 'quarterly', 'yearly'])],
            'quota_bytes' => 'nullable|integer|min:0',
            'quota_period' => ['nullable', Rule::in(['hourly', 'daily', 'weekly', 'monthly'])],
            'setup_fee' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        try {
            $package->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'download_speed_mbps' => $validated['download_speed_mbps'],
                'upload_speed_mbps' => $validated['upload_speed_mbps'],
                'price' => $validated['price'],
                'billing_cycle' => $validated['billing_cycle'],
                'quota_bytes' => $validated['quota_bytes'] ?? null,
                'quota_period' => $validated['quota_period'] ?? null,
                'installation_fee' => $validated['setup_fee'] ?? 0,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('telecom.packages.index')
                ->with('success', 'Package berhasil diupdate.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Gagal mengupdate package: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified package.
     */
    public function destroy(InternetPackage $package)
    {
        if ($package->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        // Check if package has active subscriptions
        if ($package->subscriptions()->where('status', 'active')->exists()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus package yang memiliki subscription aktif.']);
        }

        try {
            $packageName = $package->name;
            $package->delete();

            return redirect()->route('telecom.packages.index')
                ->with('success', "Package '{$packageName}' berhasil dihapus.");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus package: '.$e->getMessage()]);
        }
    }

    /**
     * Toggle package active status.
     */
    public function toggleStatus(InternetPackage $package)
    {
        if ($package->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $package->update(['is_active' => ! $package->is_active]);

        $message = $package->is_active
            ? 'Package diaktifkan.'
            : 'Package dinonaktifkan.';

        return back()->with('success', $message);
    }
}
