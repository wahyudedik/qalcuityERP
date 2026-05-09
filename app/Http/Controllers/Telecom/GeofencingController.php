<?php

namespace App\Http\Controllers\Telecom;

use App\Http\Controllers\Controller;
use App\Models\GeofenceZone;
use App\Models\NetworkDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GeofencingController extends Controller
{
    /**
     * Display geofencing zones list.
     */
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;

        $zones = GeofenceZone::where('tenant_id', $tenantId)
            ->withCount(['devices', 'alerts'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_zones' => GeofenceZone::where('tenant_id', $tenantId)->count(),
            'active_zones' => GeofenceZone::where('tenant_id', $tenantId)->where('is_active', true)->count(),
            'circular_zones' => GeofenceZone::where('tenant_id', $tenantId)->where('zone_type', 'circular')->count(),
            'polygon_zones' => GeofenceZone::where('tenant_id', $tenantId)->where('zone_type', 'polygon')->count(),
        ];

        return view('telecom.geofencing.index', compact('zones', 'stats'));
    }

    /**
     * Show create zone form.
     */
    public function create()
    {
        $devices = NetworkDevice::where('tenant_id', Auth::user()->tenant_id)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get();

        return view('telecom.geofencing.create', compact('devices'));
    }

    /**
     * Store new geofence zone.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'zone_type' => 'required|in:circular,polygon',
            'center_latitude' => 'nullable|numeric|between:-90,90',
            'center_longitude' => 'nullable|numeric|between:-180,180',
            'radius_meters' => 'nullable|integer|min:10|max:50000',
            'polygon_coordinates' => 'nullable|json',
            'is_active' => 'boolean',
            'alert_settings' => 'nullable|array',
            'device_ids' => 'nullable|array',
            'device_ids.*' => 'exists:network_devices,id',
            'alert_type' => 'nullable|in:enter,exit,both',
        ]);

        DB::beginTransaction();

        try {
            $zone = GeofenceZone::create([
                'tenant_id' => Auth::user()->tenant_id,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'zone_type' => $validated['zone_type'],
                'center_latitude' => $validated['center_latitude'] ?? null,
                'center_longitude' => $validated['center_longitude'] ?? null,
                'radius_meters' => $validated['radius_meters'] ?? null,
                'polygon_coordinates' => $validated['polygon_coordinates'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'alert_settings' => $validated['alert_settings'] ?? [
                    'enabled' => true,
                    'channels' => ['database'],
                ],
            ]);

            // Assign devices to zone
            if (! empty($validated['device_ids'])) {
                $alertType = $validated['alert_type'] ?? 'both';
                $zone->devices()->attach($validated['device_ids'], [
                    'alert_type' => $alertType,
                    'is_enabled' => true,
                ]);
            }

            DB::commit();

            return redirect()->route('telecom.geofencing.index')
                ->with('success', 'Geofence zone created successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to create zone: '.$e->getMessage()]);
        }
    }

    /**
     * Show zone details.
     */
    public function show($id)
    {
        $tenantId = Auth::user()->tenant_id;

        $zone = GeofenceZone::where('tenant_id', $tenantId)
            ->with([
                'devices',
                'alerts' => function ($query) {
                    $query->latest()->limit(50);
                },
            ])
            ->findOrFail($id);

        $unassignedDevices = NetworkDevice::where('tenant_id', $tenantId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereDoesntHave('geofenceZones', function ($query) use ($id) {
                $query->where('zone_id', $id);
            })
            ->get();

        $recentAlerts = $zone->alerts()->latest()->limit(20)->get();

        return view('telecom.geofencing.show', compact('zone', 'unassignedDevices', 'recentAlerts'));
    }

    /**
     * Show edit zone form.
     */
    public function edit($id)
    {
        $tenantId = Auth::user()->tenant_id;

        $zone = GeofenceZone::where('tenant_id', $tenantId)->findOrFail($id);

        $devices = NetworkDevice::where('tenant_id', $tenantId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get();

        $assignedDeviceIds = $zone->devices()->pluck('network_devices.id')->toArray();

        return view('telecom.geofencing.edit', compact('zone', 'devices', 'assignedDeviceIds'));
    }

    /**
     * Update geofence zone.
     */
    public function update(Request $request, $id)
    {
        $tenantId = Auth::user()->tenant_id;
        $zone = GeofenceZone::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'zone_type' => 'required|in:circular,polygon',
            'center_latitude' => 'nullable|numeric|between:-90,90',
            'center_longitude' => 'nullable|numeric|between:-180,180',
            'radius_meters' => 'nullable|integer|min:10|max:50000',
            'polygon_coordinates' => 'nullable|json',
            'is_active' => 'boolean',
            'alert_settings' => 'nullable|array',
            'device_ids' => 'nullable|array',
            'device_ids.*' => 'exists:network_devices,id',
            'alert_type' => 'nullable|in:enter,exit,both',
        ]);

        DB::beginTransaction();

        try {
            $zone->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'zone_type' => $validated['zone_type'],
                'center_latitude' => $validated['center_latitude'] ?? null,
                'center_longitude' => $validated['center_longitude'] ?? null,
                'radius_meters' => $validated['radius_meters'] ?? null,
                'polygon_coordinates' => $validated['polygon_coordinates'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'alert_settings' => $validated['alert_settings'] ?? $zone->alert_settings,
            ]);

            // Sync devices
            if (isset($validated['device_ids'])) {
                $alertType = $validated['alert_type'] ?? 'both';
                $syncData = [];
                foreach ($validated['device_ids'] as $deviceId) {
                    $syncData[$deviceId] = [
                        'alert_type' => $alertType,
                        'is_enabled' => true,
                    ];
                }
                $zone->devices()->sync($syncData);
            }

            DB::commit();

            return redirect()->route('telecom.geofencing.show', $zone->id)
                ->with('success', 'Geofence zone updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to update zone: '.$e->getMessage()]);
        }
    }

    /**
     * Delete geofence zone.
     */
    public function destroy($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $zone = GeofenceZone::where('tenant_id', $tenantId)->findOrFail($id);

        $zone->delete();

        return redirect()->route('telecom.geofencing.index')
            ->with('success', 'Geofence zone deleted successfully');
    }

    /**
     * Toggle zone active status.
     */
    public function toggleStatus($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $zone = GeofenceZone::where('tenant_id', $tenantId)->findOrFail($id);

        $zone->update(['is_active' => ! $zone->is_active]);

        return back()->with('success', 'Zone status updated');
    }

    /**
     * Assign devices to zone.
     */
    public function assignDevices(Request $request, $id)
    {
        $tenantId = Auth::user()->tenant_id;
        $zone = GeofenceZone::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'device_ids' => 'required|array',
            'device_ids.*' => 'exists:network_devices,id',
            'alert_type' => 'required|in:enter,exit,both',
        ]);

        $syncData = [];
        foreach ($validated['device_ids'] as $deviceId) {
            $syncData[$deviceId] = [
                'alert_type' => $validated['alert_type'],
                'is_enabled' => true,
            ];
        }

        $zone->devices()->sync($syncData, false);

        return back()->with('success', 'Devices assigned to zone successfully');
    }

    /**
     * Remove device from zone.
     */
    public function removeDevice($zoneId, $deviceId)
    {
        $tenantId = Auth::user()->tenant_id;
        $zone = GeofenceZone::where('tenant_id', $tenantId)->findOrFail($zoneId);

        $zone->devices()->detach($deviceId);

        return back()->with('success', 'Device removed from zone');
    }

    /**
     * Get zone map preview (API).
     */
    public function getMapPreview($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $zone = GeofenceZone::where('tenant_id', $tenantId)->findOrFail($id);

        $devicesInZone = $zone->devices()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($device) {
                return [
                    'id' => $device->id,
                    'name' => $device->name,
                    'latitude' => $device->latitude,
                    'longitude' => $device->longitude,
                    'status' => $device->status,
                ];
            });

        return response()->json([
            'success' => true,
            'zone' => [
                'id' => $zone->id,
                'name' => $zone->name,
                'zone_type' => $zone->zone_type,
                'center_latitude' => $zone->center_latitude,
                'center_longitude' => $zone->center_longitude,
                'radius_meters' => $zone->radius_meters,
                'polygon_coordinates' => $zone->polygon_coordinates,
            ],
            'devices' => $devicesInZone,
        ]);
    }
}
