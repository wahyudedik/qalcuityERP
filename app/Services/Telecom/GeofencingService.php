<?php

namespace App\Services\Telecom;

use App\Models\NetworkDevice;
use App\Models\GeofenceZone;
use App\Models\GeofenceAlert;
use App\Models\LocationHistory;
use Illuminate\Support\Facades\Log;

class GeofencingService
{
    /**
     * Check if device is within any of its assigned geofence zones.
     * Creates alerts if device entered or exited zones.
     */
    public function checkDeviceGeofences(NetworkDevice $device): array
    {
        if (!$device->hasCoordinates()) {
            return ['success' => false, 'message' => 'Device has no coordinates'];
        }

        $alerts = [];
        $zones = $device->geofenceZones()->wherePivot('is_enabled', true)->get();

        foreach ($zones as $zone) {
            $pivotData = $device->geofenceZones()->where('zone_id', $zone->id)->first()->pivot;
            $alertType = $pivotData->alert_type; // 'enter', 'exit', or 'both'

            $isInside = $zone->containsPoint($device->latitude, $device->longitude);
            $distanceFromCenter = $zone->getDistanceFromCenter($device->latitude, $device->longitude);

            // Get last alert for this device-zone combination
            $lastAlert = GeofenceAlert::where('device_id', $device->id)
                ->where('zone_id', $zone->id)
                ->orderBy('triggered_at', 'desc')
                ->first();

            // Determine current state
            $lastEventWasEnter = $lastAlert && $lastAlert->event_type === 'enter';

            // Check if we need to trigger an alert
            if ($isInside && !$lastEventWasEnter && in_array($alertType, ['enter', 'both'])) {
                // Device ENTERED zone
                $alert = $this->createGeofenceAlert(
                    $device,
                    $zone,
                    'enter',
                    $distanceFromCenter
                );
                $alerts[] = $alert;

                Log::info("Geofence: Device {$device->name} ENTERED zone {$zone->name}");
            } elseif (!$isInside && $lastEventWasEnter && in_array($alertType, ['exit', 'both'])) {
                // Device EXITED zone
                $alert = $this->createGeofenceAlert(
                    $device,
                    $zone,
                    'exit',
                    $distanceFromCenter
                );
                $alerts[] = $alert;

                Log::warning("Geofence: Device {$device->name} EXITED zone {$zone->name}");
            }
        }

        return [
            'success' => true,
            'alerts_created' => count($alerts),
            'alerts' => $alerts,
        ];
    }

    /**
     * Process location update from device.
     * Saves to history and checks geofences.
     */
    public function processLocationUpdate(
        NetworkDevice $device,
        float $latitude,
        float $longitude,
        array $additionalData = []
    ): array {
        // Update device location
        $device->update([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'last_seen_at' => now(),
        ]);

        // Save to location history
        $locationHistory = LocationHistory::create([
            'tenant_id' => $device->tenant_id,
            'device_id' => $device->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy_meters' => $additionalData['accuracy_meters'] ?? null,
            'altitude_meters' => $additionalData['altitude_meters'] ?? null,
            'speed_kmh' => $additionalData['speed_kmh'] ?? null,
            'heading_degrees' => $additionalData['heading_degrees'] ?? null,
            'source' => $additionalData['source'] ?? 'api',
            'metadata' => $additionalData['metadata'] ?? null,
            'recorded_at' => $additionalData['recorded_at'] ?? now(),
        ]);

        // Check geofences
        $geofenceResult = $this->checkDeviceGeofences($device);

        // Send notifications if alerts were created
        if ($geofenceResult['alerts_created'] > 0) {
            foreach ($geofenceResult['alerts'] as $alert) {
                $this->sendGeofenceNotification($alert);
            }
        }

        return [
            'success' => true,
            'location_history' => $locationHistory,
            'geofence_alerts' => $geofenceResult['alerts_created'],
        ];
    }

    /**
     * Create a geofence alert record.
     */
    protected function createGeofenceAlert(
        NetworkDevice $device,
        GeofenceZone $zone,
        string $eventType,
        float $distanceFromCenter
    ): GeofenceAlert {
        $message = $eventType === 'enter'
            ? "Device '{$device->name}' entered zone '{$zone->name}'"
            : "Device '{$device->name}' exited zone '{$zone->name}'";

        return GeofenceAlert::create([
            'tenant_id' => $device->tenant_id,
            'device_id' => $device->id,
            'zone_id' => $zone->id,
            'event_type' => $eventType,
            'latitude' => $device->latitude,
            'longitude' => $device->longitude,
            'distance_from_center_meters' => round($distanceFromCenter),
            'message' => $message,
            'is_notified' => false,
            'triggered_at' => now(),
        ]);
    }

    /**
     * Send notification for geofence alert.
     */
    public function sendGeofenceNotification(GeofenceAlert $alert): bool
    {
        try {
            // Get zone alert settings
            $alertSettings = $alert->zone->alert_settings ?? [];

            // Check if notifications are enabled
            if (!($alertSettings['enabled'] ?? true)) {
                $alert->markAsNotified();
                return false;
            }

            // Send based on configured channels
            $channels = $alertSettings['channels'] ?? ['database'];

            if (in_array('database', $channels)) {
                // Database notification (already saved as alert)
                $alert->markAsNotified();
            }

            if (in_array('email', $channels) && !empty($alertSettings['email_recipients'])) {
                // TODO: Send email notification
                // Notification::route('mail', $alertSettings['email_recipients'])
                //     ->notify(new GeofenceAlertNotification($alert));
                Log::info("Email notification would be sent for alert {$alert->id}");
            }

            if (in_array('webhook', $channels) && !empty($alertSettings['webhook_url'])) {
                // TODO: Send webhook
                // Http::post($alertSettings['webhook_url'], $alert->toArray());
                Log::info("Webhook notification would be sent for alert {$alert->id}");
            }

            Log::info("Geofence notification sent for alert {$alert->id}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send geofence notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check all devices with coordinates for geofence violations.
     * Used by scheduled task.
     */
    public function checkAllDevices(): array
    {
        $devices = NetworkDevice::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('geofenceZones')
            ->get();

        $results = [
            'total_checked' => $devices->count(),
            'alerts_created' => 0,
            'devices_with_alerts' => 0,
        ];

        foreach ($devices as $device) {
            if ($device->geofenceZones->count() === 0) {
                continue;
            }

            $result = $this->checkDeviceGeofences($device);

            if ($result['alerts_created'] > 0) {
                $results['alerts_created'] += $result['alerts_created'];
                $results['devices_with_alerts']++;
            }
        }

        Log::info("Geofence check completed: {$results['total_checked']} devices checked, {$results['alerts_created']} alerts created");

        return $results;
    }

    /**
     * Get devices currently outside their assigned zones.
     */
    public function getDevicesOutsideZones(int $tenantId): array
    {
        $devices = NetworkDevice::where('tenant_id', $tenantId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with([
                'geofenceZones' => function ($query) {
                    $query->wherePivot('is_enabled', true)
                        ->wherePivot('alert_type', '!=', 'enter');
                }
            ])
            ->get();

        $devicesOutside = [];

        foreach ($devices as $device) {
            if ($device->geofenceZones->count() === 0) {
                continue;
            }

            $outsideZones = [];
            foreach ($device->geofenceZones as $zone) {
                if (!$zone->containsPoint($device->latitude, $device->longitude)) {
                    $outsideZones[] = [
                        'zone_id' => $zone->id,
                        'zone_name' => $zone->name,
                        'distance_from_center' => $zone->getDistanceFromCenter($device->latitude, $device->longitude),
                    ];
                }
            }

            if (!empty($outsideZones)) {
                $devicesOutside[] = [
                    'device' => $device,
                    'outside_zones' => $outsideZones,
                ];
            }
        }

        return $devicesOutside;
    }

    /**
     * Get devices currently inside their assigned zones.
     */
    public function getDevicesInsideZones(int $tenantId): array
    {
        $devices = NetworkDevice::where('tenant_id', $tenantId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with([
                'geofenceZones' => function ($query) {
                    $query->wherePivot('is_enabled', true);
                }
            ])
            ->get();

        $devicesInside = [];

        foreach ($devices as $device) {
            if ($device->geofenceZones->count() === 0) {
                continue;
            }

            $insideZones = [];
            foreach ($device->geofenceZones as $zone) {
                if ($zone->containsPoint($device->latitude, $device->longitude)) {
                    $insideZones[] = [
                        'zone_id' => $zone->id,
                        'zone_name' => $zone->name,
                        'distance_from_center' => $zone->getDistanceFromCenter($device->latitude, $device->longitude),
                    ];
                }
            }

            if (!empty($insideZones)) {
                $devicesInside[] = [
                    'device' => $device,
                    'inside_zones' => $insideZones,
                ];
            }
        }

        return $devicesInside;
    }
}
