<?php

namespace App\Http\Controllers\Telecom;

use App\Http\Controllers\Controller;
use App\Models\NetworkDevice;
use App\Models\LocationHistory;
use App\Models\MobileDeviceTrack;
use App\Models\GeofenceAlert;
use App\Services\Telecom\GeofencingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LocationTrackingController extends Controller
{
    protected GeofencingService $geofencingService;

    public function __construct()
    {
        $this->geofencingService = new GeofencingService();
    }

    /**
     * Update device location (API endpoint for devices/mobile apps).
     */
    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:network_devices,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy_meters' => 'nullable|integer|min:1',
            'altitude_meters' => 'nullable|numeric',
            'speed_kmh' => 'nullable|numeric|min:0',
            'heading_degrees' => 'nullable|numeric|between:0,360',
            'source' => 'nullable|string|in:manual,gps,api,mobile_app',
            'metadata' => 'nullable|array',
            'recorded_at' => 'nullable|date',
        ]);

        $device = NetworkDevice::findOrFail($validated['device_id']);

        // Check tenant ownership
        if ($device->tenant_id !== Auth::user()->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $result = $this->geofencingService->processLocationUpdate(
                $device,
                $validated['latitude'],
                $validated['longitude'],
                [
                    'accuracy_meters' => $validated['accuracy_meters'] ?? null,
                    'altitude_meters' => $validated['altitude_meters'] ?? null,
                    'speed_kmh' => $validated['speed_kmh'] ?? null,
                    'heading_degrees' => $validated['heading_degrees'] ?? null,
                    'source' => $validated['source'] ?? 'api',
                    'metadata' => $validated['metadata'] ?? null,
                    'recorded_at' => $validated['recorded_at'] ?? now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully',
                'geofence_alerts' => $result['geofence_alerts'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get location history for a device.
     */
    public function getLocationHistory(Request $request, $deviceId)
    {
        $tenantId = Auth::user()->tenant_id;

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'source' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:1000',
        ]);

        $query = LocationHistory::where('tenant_id', $tenantId)
            ->where('device_id', $deviceId)
            ->with('device');

        if ($validated['start_date'] ?? false) {
            $query->where('recorded_at', '>=', $validated['start_date']);
        }

        if ($validated['end_date'] ?? false) {
            $query->where('recorded_at', '<=', $validated['end_date']);
        }

        if ($validated['source'] ?? false) {
            $query->where('source', $validated['source']);
        }

        $limit = $validated['limit'] ?? 100;
        $history = $query->orderBy('recorded_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'latitude' => $record->latitude,
                    'longitude' => $record->longitude,
                    'accuracy_meters' => $record->accuracy_meters,
                    'altitude_meters' => $record->altitude_meters,
                    'speed_kmh' => $record->speed_kmh,
                    'heading_degrees' => $record->heading_degrees,
                    'source' => $record->source,
                    'recorded_at' => $record->recorded_at->toISOString(),
                    'coordinates' => $record->coordinates,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $history,
            'total' => $history->count(),
        ]);
    }

    /**
     * Track mobile device location (for route tracking).
     */
    public function trackMobileDevice(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:network_devices,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'session_id' => 'nullable|string|max:255',
            'accuracy_meters' => 'nullable|integer|min:1',
            'battery_level' => 'nullable|numeric|min:0|max:100',
            'network_type' => 'nullable|string|in:wifi,4g,5g,3g,2g',
            'route_metadata' => 'nullable|array',
            'tracked_at' => 'nullable|date',
        ]);

        $device = NetworkDevice::findOrFail($validated['device_id']);

        // Check tenant ownership
        if ($device->tenant_id !== Auth::user()->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Create or use session ID
        $sessionId = $validated['session_id'] ?? (string) Str::uuid();

        $track = MobileDeviceTrack::create([
            'tenant_id' => $device->tenant_id,
            'device_id' => $device->id,
            'session_id' => $sessionId,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'accuracy_meters' => $validated['accuracy_meters'] ?? null,
            'battery_level' => $validated['battery_level'] ?? null,
            'network_type' => $validated['network_type'] ?? null,
            'route_metadata' => $validated['route_metadata'] ?? null,
            'tracked_at' => $validated['tracked_at'] ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location tracked successfully',
            'session_id' => $sessionId,
            'track_id' => $track->id,
        ]);
    }

    /**
     * Get route tracking data for a device.
     */
    public function getRouteTracking(Request $request, $deviceId)
    {
        $tenantId = Auth::user()->tenant_id;

        $validated = $request->validate([
            'session_id' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = MobileDeviceTrack::where('tenant_id', $tenantId)
            ->where('device_id', $deviceId);

        if ($validated['session_id'] ?? false) {
            $query->where('session_id', $validated['session_id']);
        }

        if ($validated['start_date'] ?? false) {
            $query->where('tracked_at', '>=', $validated['start_date']);
        }

        if ($validated['end_date'] ?? false) {
            $query->where('tracked_at', '<=', $validated['end_date']);
        }

        $tracks = $query->orderBy('tracked_at', 'asc')
            ->get()
            ->map(function ($track) {
                return [
                    'id' => $track->id,
                    'session_id' => $track->session_id,
                    'latitude' => $track->latitude,
                    'longitude' => $track->longitude,
                    'accuracy_meters' => $track->accuracy_meters,
                    'battery_level' => $track->battery_level,
                    'network_type' => $track->network_type,
                    'tracked_at' => $track->tracked_at->toISOString(),
                    'coordinates' => $track->coordinates,
                ];
            });

        // Calculate session stats if session_id is provided
        $stats = null;
        if ($validated['session_id'] ?? false) {
            $firstTrack = $tracks->first();
            if ($firstTrack) {
                $trackModel = MobileDeviceTrack::find($firstTrack['id']);
                $stats = [
                    'total_points' => $tracks->count(),
                    'total_distance_meters' => round($trackModel->getSessionDistance(), 2),
                    'start_time' => $tracks->first()['tracked_at'],
                    'end_time' => $tracks->last()['tracked_at'],
                    'duration_minutes' => $tracks->first() ?
                        round((strtotime($tracks->last()['tracked_at']) - strtotime($tracks->first()['tracked_at'])) / 60, 2) : 0,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $tracks,
            'stats' => $stats,
            'total' => $tracks->count(),
        ]);
    }

    /**
     * Get available tracking sessions for a device.
     */
    public function getTrackingSessions($deviceId)
    {
        $tenantId = Auth::user()->tenant_id;

        $sessions = MobileDeviceTrack::where('tenant_id', $tenantId)
            ->where('device_id', $deviceId)
            ->whereNotNull('session_id')
            ->selectRaw('session_id, MIN(tracked_at) as start_time, MAX(tracked_at) as end_time, COUNT(*) as points')
            ->groupBy('session_id')
            ->orderBy('start_time', 'desc')
            ->get()
            ->map(function ($session) {
                return [
                    'session_id' => $session->session_id,
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'points' => $session->points,
                    'duration_minutes' => round((strtotime($session->end_time) - strtotime($session->start_time)) / 60, 2),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $sessions,
            'total' => $sessions->count(),
        ]);
    }

    /**
     * Get geofence alerts for a device.
     */
    public function getGeofenceAlerts(Request $request, $deviceId)
    {
        $tenantId = Auth::user()->tenant_id;

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'event_type' => 'nullable|in:enter,exit',
            'limit' => 'nullable|integer|min:1|max:500',
        ]);

        $query = GeofenceAlert::where('tenant_id', $tenantId)
            ->where('device_id', $deviceId)
            ->with(['zone', 'device']);

        if ($validated['start_date'] ?? false) {
            $query->where('triggered_at', '>=', $validated['start_date']);
        }

        if ($validated['end_date'] ?? false) {
            $query->where('triggered_at', '<=', $validated['end_date']);
        }

        if ($validated['event_type'] ?? false) {
            $query->where('event_type', $validated['event_type']);
        }

        $limit = $validated['limit'] ?? 50;
        $alerts = $query->orderBy('triggered_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($alert) {
                return [
                    'id' => $alert->id,
                    'event_type' => $alert->event_type,
                    'message' => $alert->formatted_message,
                    'zone_name' => $alert->zone?->name,
                    'device_name' => $alert->device?->name,
                    'latitude' => $alert->latitude,
                    'longitude' => $alert->longitude,
                    'distance_from_center_meters' => $alert->distance_from_center_meters,
                    'triggered_at' => $alert->triggered_at->toISOString(),
                    'is_notified' => $alert->is_notified,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $alerts,
            'total' => $alerts->count(),
        ]);
    }
}
