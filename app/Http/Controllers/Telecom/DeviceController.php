<?php

namespace App\Http\Controllers\Telecom;

use App\Http\Controllers\Controller;
use App\Models\NetworkDevice;
use App\Services\Telecom\RouterIntegrationService;
use App\Services\Telecom\BandwidthMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    protected RouterIntegrationService $integrationService;
    protected BandwidthMonitoringService $monitoringService;

    public function __construct()
    {
        $this->integrationService = new RouterIntegrationService();
        $this->monitoringService = new BandwidthMonitoringService();
    }

    /**
     * Display a listing of devices.
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $query = NetworkDevice::where('tenant_id', $tenantId)
            ->withCount(['subscriptions', 'hotspotUsers']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by brand
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('device_type', $request->type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $devices = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => NetworkDevice::where('tenant_id', $tenantId)->count(),
            'online' => NetworkDevice::where('tenant_id', $tenantId)->where('status', 'online')->count(),
            'offline' => NetworkDevice::where('tenant_id', $tenantId)->where('status', 'offline')->count(),
            'maintenance' => NetworkDevice::where('tenant_id', $tenantId)->where('status', 'maintenance')->count(),
        ];

        return view('telecom.devices.index', compact('devices', 'stats'));
    }

    /**
     * Show the form for creating a new device.
     */
    public function create()
    {
        $parentDevices = NetworkDevice::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', '!=', 'offline')
            ->get();

        return view('telecom.devices.create', compact('parentDevices'));
    }

    /**
     * Store a newly created device.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => ['required', Rule::in(['mikrotik', 'ubiquiti', 'cisco', 'openwrt', 'other'])],
            'model' => 'nullable|string|max:255',
            'device_type' => ['required', Rule::in(['router', 'access_point', 'switch', 'firewall'])],
            'ip_address' => 'required|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'username' => 'required|string',
            'password' => 'required|string',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'coverage_radius' => 'nullable|integer|min:1|max:50000',
            'description' => 'nullable|string',
            'parent_device_id' => 'nullable|exists:network_devices,id',
            'capabilities' => 'nullable|array',
            'configuration' => 'nullable|array',
        ]);

        try {
            $device = NetworkDevice::create([
                'tenant_id' => Auth::user()->tenant_id,
                'name' => $validated['name'],
                'brand' => $validated['brand'],
                'model' => $validated['model'] ?? null,
                'device_type' => $validated['device_type'],
                'ip_address' => $validated['ip_address'],
                'port' => $validated['port'] ?? ($validated['brand'] === 'mikrotik' ? 8728 : 443),
                'username' => $validated['username'],
                'password' => $validated['password'],
                'location' => $validated['location'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'coverage_radius' => $validated['coverage_radius'] ?? null,
                'description' => $validated['description'] ?? null,
                'parent_device_id' => $validated['parent_device_id'] ?? null,
                'capabilities' => $validated['capabilities'] ?? [],
                'configuration' => $validated['configuration'] ?? [],
                'status' => 'pending',
            ]);

            // Test connection
            $connectionTest = $this->integrationService->checkDeviceHealth($device);

            if ($connectionTest['success']) {
                $device->update(['status' => 'online']);
                $message = 'Device berhasil ditambahkan dan terhubung.';
            } else {
                $device->update(['status' => 'offline']);
                $message = 'Device berhasil ditambahkan tetapi tidak dapat terhubung. Periksa konfigurasi.';
            }

            return redirect()->route('telecom.devices.show', $device)
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Gagal menambahkan device: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified device.
     */
    public function show(NetworkDevice $device)
    {
        // Check tenant ownership
        if ($device->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $device->load(['parentDevice', 'subscriptions.customer', 'hotspotUsers']);

        // Get real-time status
        $healthCheck = null;
        $bandwidthUsage = null;

        try {
            $healthCheck = $this->integrationService->checkDeviceHealth($device);
            $bandwidthUsage = $this->monitoringService->getDeviceBandwidthUsage($device);
        } catch (\Exception $e) {
            Log::warning("Failed to get live data for device {$device->id}: " . $e->getMessage());
        }

        // Get recent alerts
        $recentAlerts = \App\Models\NetworkAlert::where('device_id', $device->id)
            ->where('tenant_id', $device->tenant_id)
            ->orderBy('triggered_at', 'desc')
            ->limit(10)
            ->get();

        return view('telecom.devices.show', compact('device', 'healthCheck', 'bandwidthUsage', 'recentAlerts'));
    }

    /**
     * Show the form for editing the specified device.
     */
    public function edit(NetworkDevice $device)
    {
        if ($device->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $parentDevices = NetworkDevice::where('tenant_id', Auth::user()->tenant_id)
            ->where('id', '!=', $device->id)
            ->where('status', '!=', 'offline')
            ->get();

        return view('telecom.devices.edit', compact('device', 'parentDevices'));
    }

    /**
     * Update the specified device.
     */
    public function update(Request $request, NetworkDevice $device)
    {
        if ($device->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => ['required', Rule::in(['mikrotik', 'ubiquiti', 'cisco', 'openwrt', 'other'])],
            'model' => 'nullable|string|max:255',
            'device_type' => ['required', Rule::in(['router', 'access_point', 'switch', 'firewall'])],
            'ip_address' => 'required|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'username' => 'required|string',
            'password' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'coverage_radius' => 'nullable|integer|min:1|max:50000',
            'description' => 'nullable|string',
            'parent_device_id' => 'nullable|exists:network_devices,id',
            'status' => ['required', Rule::in(['online', 'offline', 'maintenance', 'pending'])],
        ]);

        try {
            $updateData = [
                'name' => $validated['name'],
                'brand' => $validated['brand'],
                'model' => $validated['model'] ?? null,
                'device_type' => $validated['device_type'],
                'ip_address' => $validated['ip_address'],
                'port' => $validated['port'],
                'username' => $validated['username'],
                'location' => $validated['location'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'coverage_radius' => $validated['coverage_radius'] ?? null,
                'description' => $validated['description'] ?? null,
                'parent_device_id' => $validated['parent_device_id'] ?? null,
                'status' => $validated['status'],
            ];

            // Only update password if provided
            if (!empty($validated['password'])) {
                $updateData['password'] = $validated['password'];
            }

            $device->update($updateData);

            // Re-test connection if credentials changed
            if (!empty($validated['password']) || $device->wasChanged('ip_address') || $device->wasChanged('port')) {
                $connectionTest = $this->integrationService->checkDeviceHealth($device);

                if ($connectionTest['success']) {
                    $device->update(['status' => 'online']);
                }
            }

            return redirect()->route('telecom.devices.show', $device)
                ->with('success', 'Device berhasil diupdate.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Gagal mengupdate device: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified device.
     */
    public function destroy(NetworkDevice $device)
    {
        if ($device->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        // Check if device has active subscriptions or users
        if ($device->subscriptions()->where('status', 'active')->exists()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus device yang memiliki subscription aktif.']);
        }

        if ($device->hotspotUsers()->where('is_active', true)->exists()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus device yang memiliki user aktif. Nonaktifkan semua user terlebih dahulu.']);
        }

        try {
            $deviceName = $device->name;
            $device->delete();

            return redirect()->route('telecom.devices.index')
                ->with('success', "Device '{$deviceName}' berhasil dihapus.");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus device: ' . $e->getMessage()]);
        }
    }

    /**
     * Test connection to device.
     */
    public function testConnection(NetworkDevice $device)
    {
        if ($device->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        try {
            $result = $this->integrationService->checkDeviceHealth($device);

            if ($result['success']) {
                $device->update([
                    'status' => 'online',
                    'last_seen_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Koneksi berhasil!',
                    'details' => $result['details'] ?? [],
                ]);
            } else {
                $device->update(['status' => 'offline']);

                return response()->json([
                    'success' => false,
                    'message' => 'Koneksi gagal: ' . ($result['error'] ?? 'Unknown error'),
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saat testing koneksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle device maintenance mode.
     */
    public function toggleMaintenance(NetworkDevice $device)
    {
        if ($device->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $newStatus = $device->status === 'maintenance' ? 'online' : 'maintenance';
        $device->update(['status' => $newStatus]);

        $message = $newStatus === 'maintenance'
            ? 'Device masuk mode maintenance.'
            : 'Device keluar dari mode maintenance.';

        return back()->with('success', $message);
    }
}
