<?php

namespace App\Http\Controllers;

use App\Models\IotDevice;
use App\Models\IotTelemetryLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class IotDeviceController extends Controller
{
    public function index(): View
    {
        $devices = IotDevice::where('tenant_id', $this->tenantId())
            ->withCount('telemetryLogs')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('iot.devices.index', compact('devices'));
    }

    public function create(): View
    {
        return view('iot.devices.create', [
            'deviceTypes'   => IotDevice::deviceTypes(),
            'targetModules' => IotDevice::targetModules(),
            'sensorTypes'   => IotDevice::sensorTypes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'device_type'      => 'required|in:esp32,arduino,raspberry_pi,generic',
            'location'         => 'nullable|string|max:255',
            'target_module'    => 'required|in:inventory,manufacturing,livestock,fisheries,agriculture,hrm,healthcare,general',
            'sensor_types'     => 'nullable|array',
            'sensor_types.*'   => 'string',
            'firmware_version' => 'nullable|string|max:50',
            'is_active'        => 'boolean',
            'config'           => 'nullable|array',
            'notes'            => 'nullable|string',
        ]);

        $validated['tenant_id']    = $this->tenantId();
        $validated['device_id']    = 'DEV-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $validated['device_token'] = IotDevice::generateToken();
        $validated['is_active']    = $request->boolean('is_active', true);

        $device = IotDevice::create($validated);

        return redirect()->route('iot.devices.show', $device)
            ->with('success', 'Device IoT berhasil ditambahkan. Simpan token di bawah untuk dipakai di firmware.');
    }

    public function show(IotDevice $device): View
    {
        $this->authorizeTenant($device);

        $recentLogs = IotTelemetryLog::where('iot_device_id', $device->id)
            ->orderBy('recorded_at', 'desc')
            ->limit(50)
            ->get();

        // Statistik per sensor type
        $stats = IotTelemetryLog::where('iot_device_id', $device->id)
            ->selectRaw('sensor_type, COUNT(*) as total, AVG(value) as avg_value, MAX(value) as max_value, MIN(value) as min_value')
            ->groupBy('sensor_type')
            ->get();

        return view('iot.devices.show', compact('device', 'recentLogs', 'stats'));
    }

    public function edit(IotDevice $device): View
    {
        $this->authorizeTenant($device);

        return view('iot.devices.edit', [
            'device'        => $device,
            'deviceTypes'   => IotDevice::deviceTypes(),
            'targetModules' => IotDevice::targetModules(),
            'sensorTypes'   => IotDevice::sensorTypes(),
        ]);
    }

    public function update(Request $request, IotDevice $device): RedirectResponse
    {
        $this->authorizeTenant($device);

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'device_type'      => 'required|in:esp32,arduino,raspberry_pi,generic',
            'location'         => 'nullable|string|max:255',
            'target_module'    => 'required|in:inventory,manufacturing,livestock,fisheries,agriculture,hrm,healthcare,general',
            'sensor_types'     => 'nullable|array',
            'sensor_types.*'   => 'string',
            'firmware_version' => 'nullable|string|max:50',
            'is_active'        => 'boolean',
            'config'           => 'nullable|array',
            'notes'            => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $device->update($validated);

        return redirect()->route('iot.devices.show', $device)
            ->with('success', 'Device IoT berhasil diperbarui.');
    }

    public function destroy(IotDevice $device): RedirectResponse
    {
        $this->authorizeTenant($device);
        $device->delete();

        return redirect()->route('iot.devices.index')
            ->with('success', 'Device IoT berhasil dihapus.');
    }

    /** Regenerate token — dipanggil jika token bocor */
    public function regenerateToken(IotDevice $device): JsonResponse
    {
        $this->authorizeTenant($device);

        $device->update(['device_token' => IotDevice::generateToken()]);

        return response()->json([
            'success'      => true,
            'device_token' => $device->device_token,
            'message'      => 'Token berhasil diperbarui. Update firmware Anda.',
        ]);
    }

    /** Telemetry chart data untuk dashboard */
    public function telemetryData(Request $request, IotDevice $device): JsonResponse
    {
        $this->authorizeTenant($device);

        $sensorType = $request->get('sensor_type', 'temperature');
        $hours      = (int) $request->get('hours', 24);

        $data = IotTelemetryLog::where('iot_device_id', $device->id)
            ->where('sensor_type', $sensorType)
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'value', 'unit']);

        return response()->json(['success' => true, 'data' => $data]);
    }

    private function authorizeTenant(IotDevice $device): void
    {
        if ($device->tenant_id !== $this->tenantId()) {
            abort(403, 'Akses ditolak.');
        }
    }
}
