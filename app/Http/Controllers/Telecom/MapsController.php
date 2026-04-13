<?php

namespace App\Http\Controllers\Telecom;

use App\Http\Controllers\Controller;
use App\Models\NetworkDevice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MapsController extends Controller
{
    /**
     * Display the network maps page.
     */
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;

        $stats = [
            'total_devices' => NetworkDevice::where('tenant_id', $tenantId)->count(),
            'devices_with_location' => NetworkDevice::where('tenant_id', $tenantId)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->count(),
            'online_devices' => NetworkDevice::where('tenant_id', $tenantId)
                ->where('status', 'online')
                ->count(),
        ];

        return view('telecom.maps.index', compact('stats'));
    }

    /**
     * Get all devices with coordinates for map display.
     */
    public function getDevices(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $query = NetworkDevice::where('tenant_id', $tenantId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->withCount(['subscriptions', 'hotspotUsers']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by device type
        if ($request->filled('type')) {
            $query->where('device_type', $request->type);
        }

        // Search by name or location
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Filter by specific device ID
        if ($request->filled('device_id')) {
            $query->where('id', $request->device_id);
        }

        $devices = $query->get()->map(function ($device) {
            return [
                'id' => $device->id,
                'name' => $device->name,
                'device_type' => $device->device_type,
                'brand' => $device->brand,
                'status' => $device->status,
                'ip_address' => $device->ip_address,
                'location' => $device->location,
                'latitude' => (float) $device->latitude,
                'longitude' => (float) $device->longitude,
                'coverage_radius' => $device->coverage_radius,
                'subscriptions_count' => $device->subscriptions_count,
                'hotspot_users_count' => $device->hotspot_users_count,
                'last_seen_at' => $device->last_seen_at?->diffForHumans() ?? 'Never',
                'coordinates' => $device->coordinates,
                'google_maps_url' => $device->google_maps_url,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $devices,
            'total' => $devices->count(),
        ]);
    }

    /**
     * Get detail for a specific device.
     */
    public function getDeviceDetail($id)
    {
        $tenantId = Auth::user()->tenant_id;

        $device = NetworkDevice::where('tenant_id', $tenantId)
            ->where('id', $id)
            ->with([
                'parentDevice',
                'subscriptions' => function ($q) {
                    $q->where('status', 'active')->with('customer');
                }
            ])
            ->withCount(['subscriptions', 'hotspotUsers'])
            ->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $device->id,
                'name' => $device->name,
                'device_type' => $device->device_type,
                'brand' => $device->brand,
                'model' => $device->model,
                'status' => $device->status,
                'ip_address' => $device->ip_address,
                'location' => $device->location,
                'latitude' => (float) $device->latitude,
                'longitude' => (float) $device->longitude,
                'coverage_radius' => $device->coverage_radius,
                'subscriptions_count' => $device->subscriptions_count,
                'active_subscriptions' => $device->subscriptions->count(),
                'hotspot_users_count' => $device->hotspot_users_count,
                'last_seen_at' => $device->last_seen_at?->diffForHumans() ?? 'Never',
                'parent_device' => $device->parentDevice?->name,
                'coordinates' => $device->coordinates,
                'google_maps_url' => $device->google_maps_url,
            ],
        ]);
    }

    /**
     * Get devices nearby a location.
     */
    public function getDevicesNearby(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:100|max:50000',
        ]);

        $radius = $validated['radius'] ?? 5000; // Default 5km

        $devices = NetworkDevice::where('tenant_id', $tenantId)
            ->nearby($validated['latitude'], $validated['longitude'], $radius)
            ->withCount(['subscriptions', 'hotspotUsers'])
            ->get()
            ->map(function ($device) use ($validated) {
                /** @var \App\Models\NetworkDevice $device */
                $distance = $device->getDistanceTo($validated['latitude'], $validated['longitude']);

                return [
                    'id' => $device->id,
                    'name' => $device->name,
                    'device_type' => $device->device_type,
                    'status' => $device->status,
                    'ip_address' => $device->ip_address,
                    'location' => $device->location,
                    'latitude' => (float) $device->latitude,
                    'longitude' => (float) $device->longitude,
                    'distance_meters' => round($distance),
                    'distance_km' => round($distance / 1000, 2),
                    'coverage_radius' => $device->coverage_radius,
                    'subscriptions_count' => $device->subscriptions_count,
                    'hotspot_users_count' => $device->hotspot_users_count,
                ];
            })
            ->sortBy('distance_meters');

        return response()->json([
            'success' => true,
            'data' => $devices,
            'total' => $devices->count(),
            'radius_meters' => $radius,
        ]);
    }

    /**
     * Export maps data to PDF.
     */
    public function exportToPdf(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        // Get devices with filters
        $query = NetworkDevice::where('tenant_id', $tenantId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->withCount(['subscriptions', 'hotspotUsers']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('device_type')) {
            $query->where('device_type', $request->device_type);
        }

        $devices = $query->orderBy('name')->get();

        $stats = [
            'total_devices' => $devices->count(),
            'online_devices' => $devices->where('status', 'online')->count(),
            'offline_devices' => $devices->where('status', 'offline')->count(),
            'maintenance_devices' => $devices->where('status', 'maintenance')->count(),
            'total_subscriptions' => $devices->sum('subscriptions_count'),
            'total_hotspot_users' => $devices->sum('hotspot_users_count'),
        ];

        $pdf = Pdf::loadView('telecom.maps.pdf-export', compact('devices', 'stats'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true,
            ]);

        return $pdf->download('network-maps-report-' . date('Y-m-d') . '.pdf');
    }
}
