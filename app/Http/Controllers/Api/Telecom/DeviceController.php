<?php

namespace App\Http\Controllers\Api\Telecom;

use App\Models\NetworkDevice;
use App\Services\Telecom\BandwidthMonitoringService;
use App\Services\Telecom\RouterIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DeviceController extends TelecomApiController
{
    protected RouterIntegrationService $integrationService;

    protected BandwidthMonitoringService $monitoringService;

    public function __construct()
    {
        $this->integrationService = new RouterIntegrationService;
        $this->monitoringService = new BandwidthMonitoringService;
    }

    /**
     * Register a new network device.
     *
     * POST /api/telecom/devices
     */
    public function store(Request $request)
    {
        try {
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
                'description' => 'nullable|string',
                'parent_device_id' => 'nullable|exists:network_devices,id',
                'capabilities' => 'nullable|array',
                'configuration' => 'nullable|array',
            ]);

            $device = NetworkDevice::create([
                'tenant_id' => auth()->user()->tenant_id,
                'name' => $validated['name'],
                'brand' => $validated['brand'],
                'model' => $validated['model'] ?? null,
                'device_type' => $validated['device_type'],
                'ip_address' => $validated['ip_address'],
                'port' => $validated['port'] ?? ($validated['brand'] === 'mikrotik' ? 8728 : 443),
                'username' => $validated['username'],
                'password' => $validated['password'], // Will be encrypted by model mutator
                'location' => $validated['location'] ?? null,
                'description' => $validated['description'] ?? null,
                'parent_device_id' => $validated['parent_device_id'] ?? null,
                'capabilities' => $validated['capabilities'] ?? [],
                'configuration' => $validated['configuration'] ?? [],
                'status' => 'pending',
            ]);

            // Test connection
            $connectionTest = $this->integrationService->testConnection($device);

            if ($connectionTest['success']) {
                $device->update(['status' => 'online']);
            } else {
                $device->update(['status' => 'offline']);
            }

            $this->logApiRequest($request, 'POST /api/telecom/devices', [
                'device_id' => $device->id,
                'connection_test' => $connectionTest['success'],
            ]);

            return $this->success([
                'device' => $device,
                'connection_test' => $connectionTest,
            ], 'Device registered successfully', 201);

        } catch (ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Failed to register device', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('Failed to register device: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get device status and health.
     *
     * GET /api/telecom/devices/{id}/status
     */
    public function status(NetworkDevice $device)
    {
        // Check tenant ownership
        if ($device->tenant_id !== auth()->user()->tenant_id) {
            return $this->error('Unauthorized', 403);
        }

        try {
            // Get current status from database
            $currentStatus = [
                'device_id' => $device->id,
                'name' => $device->name,
                'brand' => $device->brand,
                'model' => $device->model,
                'device_type' => $device->device_type,
                'ip_address' => $device->ip_address,
                'status' => $device->status,
                'last_seen_at' => $device->last_seen_at,
                'uptime_seconds' => $device->uptime_seconds,
            ];

            // Test live connection
            $liveTest = $this->integrationService->checkDeviceHealth($device);

            // Get bandwidth usage
            $bandwidthUsage = $this->monitoringService->getDeviceBandwidthUsage($device);

            $this->logApiRequest(request(), "GET /api/telecom/devices/{$device->id}/status");

            return $this->success([
                'current_status' => $currentStatus,
                'health_check' => $liveTest,
                'bandwidth_usage' => $bandwidthUsage,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get device status', [
                'device_id' => $device->id,
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to get device status: '.$e->getMessage(), 500);
        }
    }

    /**
     * List all devices for tenant.
     *
     * GET /api/telecom/devices
     */
    public function index(Request $request)
    {
        try {
            $query = NetworkDevice::where('tenant_id', auth()->user()->tenant_id);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by brand
            if ($request->has('brand')) {
                $query->where('brand', $request->brand);
            }

            // Filter by type
            if ($request->has('type')) {
                $query->where('device_type', $request->type);
            }

            $devices = $query->withCount(['subscriptions', 'hotspotUsers'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 20);

            $this->logApiRequest($request, 'GET /api/telecom/devices');

            return $this->success([
                'devices' => $devices->items(),
                'pagination' => [
                    'current_page' => $devices->currentPage(),
                    'per_page' => $devices->perPage(),
                    'total' => $devices->total(),
                    'last_page' => $devices->lastPage(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to list devices', [
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to list devices: '.$e->getMessage(), 500);
        }
    }
}
