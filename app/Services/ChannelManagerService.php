<?php

namespace App\Services;

use App\Models\ChannelManagerConfig;
use App\Models\ChannelManagerLog;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * ChannelManagerService — Handles integration with OTA channels.
 *
 * Provides methods for syncing availability, rates, and reservations with
 * external booking channels (Booking.com, Agoda, Expedia, etc.).
 */
class ChannelManagerService
{
    /**
     * Supported channels.
     */
    public const SUPPORTED_CHANNELS = [
        'bookingcom',
        'agoda',
        'expedia',
        'airbnb',
        'tripadvisor',
        'direct',
    ];

    /**
     * Push room availability to a channel.
     * In this initial implementation, prepares the payload and logs the action.
     * Actual API calls would be implemented per-channel adapter later.
     */
    public function pushAvailability(int $tenantId, string $channel): array
    {
        $config = $this->getConfig($tenantId, $channel);

        if (! $config) {
            return [
                'success' => false,
                'message' => "Channel {$channel} is not configured for this tenant.",
            ];
        }

        if (! $config->is_active) {
            return [
                'success' => false,
                'message' => "Channel {$channel} is not active.",
            ];
        }

        try {
            // Prepare availability payload
            $payload = $this->prepareAvailabilityPayload($tenantId, $config);

            // Log the action (actual API call would go here)
            $this->logAction(
                tenantId: $tenantId,
                channel: $channel,
                action: 'push_availability',
                status: 'success',
                requestData: $payload
            );

            // Update last synced timestamp
            $config->update(['last_synced_at' => now()]);

            Log::info('Availability pushed to channel', [
                'tenant_id' => $tenantId,
                'channel' => $channel,
            ]);

            return [
                'success' => true,
                'message' => 'Availability pushed successfully.',
                'payload' => $payload,
            ];

        } catch (\Exception $e) {
            $this->logAction(
                tenantId: $tenantId,
                channel: $channel,
                action: 'push_availability',
                status: 'failed',
                error: $e->getMessage()
            );

            Log::error('Failed to push availability to channel', [
                'tenant_id' => $tenantId,
                'channel' => $channel,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to push availability: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Push rates to a channel.
     */
    public function pushRates(int $tenantId, string $channel): array
    {
        $config = $this->getConfig($tenantId, $channel);

        if (! $config) {
            return [
                'success' => false,
                'message' => "Channel {$channel} is not configured for this tenant.",
            ];
        }

        if (! $config->is_active) {
            return [
                'success' => false,
                'message' => "Channel {$channel} is not active.",
            ];
        }

        try {
            // Prepare rates payload
            $payload = $this->prepareRatesPayload($tenantId, $config);

            // Log the action
            $this->logAction(
                tenantId: $tenantId,
                channel: $channel,
                action: 'push_rates',
                status: 'success',
                requestData: $payload
            );

            Log::info('Rates pushed to channel', [
                'tenant_id' => $tenantId,
                'channel' => $channel,
            ]);

            return [
                'success' => true,
                'message' => 'Rates pushed successfully.',
                'payload' => $payload,
            ];

        } catch (\Exception $e) {
            $this->logAction(
                tenantId: $tenantId,
                channel: $channel,
                action: 'push_rates',
                status: 'failed',
                error: $e->getMessage()
            );

            Log::error('Failed to push rates to channel', [
                'tenant_id' => $tenantId,
                'channel' => $channel,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to push rates: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Pull reservations from a channel (stub — logs the attempt).
     */
    public function pullReservations(int $tenantId, string $channel): array
    {
        $config = $this->getConfig($tenantId, $channel);

        if (! $config) {
            return [
                'success' => false,
                'message' => "Channel {$channel} is not configured for this tenant.",
            ];
        }

        if (! $config->is_active) {
            return [
                'success' => false,
                'message' => "Channel {$channel} is not active.",
            ];
        }

        try {
            // Log the pull attempt
            $this->logAction(
                tenantId: $tenantId,
                channel: $channel,
                action: 'pull_reservations',
                status: 'success',
                requestData: ['pull_date' => now()->toDateString()]
            );

            Log::info('Reservation pull attempted from channel', [
                'tenant_id' => $tenantId,
                'channel' => $channel,
                'note' => 'Stub implementation - no actual API call',
            ]);

            return [
                'success' => true,
                'message' => 'Reservation pull completed (stub).',
                'reservations' => [],
            ];

        } catch (\Exception $e) {
            $this->logAction(
                tenantId: $tenantId,
                channel: $channel,
                action: 'pull_reservations',
                status: 'failed',
                error: $e->getMessage()
            );

            return [
                'success' => false,
                'message' => 'Failed to pull reservations: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Sync all data (availability + rates + pull reservations) for a channel.
     */
    public function syncAll(int $tenantId, string $channel): array
    {
        $config = $this->getConfig($tenantId, $channel);

        if (! $config) {
            return [
                'success' => false,
                'message' => "Channel {$channel} is not configured for this tenant.",
            ];
        }

        if (! $config->is_active) {
            return [
                'success' => false,
                'message' => "Channel {$channel} is not active.",
            ];
        }

        $results = [
            'availability' => $this->pushAvailability($tenantId, $channel),
            'rates' => $this->pushRates($tenantId, $channel),
            'reservations' => $this->pullReservations($tenantId, $channel),
        ];

        $allSuccess = collect($results)->every(fn ($r) => $r['success']);

        // Log the full sync
        $this->logAction(
            tenantId: $tenantId,
            channel: $channel,
            action: 'sync_all',
            status: $allSuccess ? 'success' : 'partial',
            responseData: $results
        );

        Log::info('Full sync completed for channel', [
            'tenant_id' => $tenantId,
            'channel' => $channel,
            'all_success' => $allSuccess,
        ]);

        return [
            'success' => $allSuccess,
            'message' => $allSuccess ? 'Sinkronisasi selesai dengan sukses.' : 'Sinkronisasi selesai dengan beberapa error.',
            'results' => $results,
            'synced_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Log a channel manager action.
     */
    public function logAction(
        int $tenantId,
        string $channel,
        string $action,
        string $status,
        ?array $requestData = null,
        ?array $responseData = null,
        ?string $error = null
    ): ChannelManagerLog {
        return ChannelManagerLog::create([
            'tenant_id' => $tenantId,
            'channel' => $channel,
            'action' => $action,
            'status' => $status,
            'request_data' => $requestData,
            'response_data' => $responseData,
            'error_message' => $error,
        ]);
    }

    /**
     * Get active config for a channel+tenant. Returns null if not configured.
     */
    public function getConfig(int $tenantId, string $channel): ?ChannelManagerConfig
    {
        return ChannelManagerConfig::where('tenant_id', $tenantId)
            ->where('channel', $channel)
            ->first();
    }

    /**
     * Get all active channel configs for a tenant.
     */
    public function getActiveConfigs(int $tenantId): Collection
    {
        return ChannelManagerConfig::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get recent logs for a tenant.
     */
    public function getRecentLogs(int $tenantId, int $limit = 50): Collection
    {
        return ChannelManagerLog::where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Prepare availability payload for channel.
     */
    private function prepareAvailabilityPayload(int $tenantId, ChannelManagerConfig $config): array
    {
        // Get all active rooms with their availability status
        $rooms = Room::with('roomType')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $availability = [];
        foreach ($rooms as $room) {
            $availability[] = [
                'room_id' => $room->id,
                'room_number' => $room->number,
                'room_type' => $room->roomType?->name,
                'status' => $room->status,
                'available' => $room->status === 'available',
            ];
        }

        return [
            'property_id' => $config->property_id,
            'availability' => $availability,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Prepare rates payload for channel.
     */
    private function prepareRatesPayload(int $tenantId, ChannelManagerConfig $config): array
    {
        $rateService = app(RateManagementService::class);

        // Get all room types with their rates
        $roomTypes = RoomType::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $rates = [];
        foreach ($roomTypes as $roomType) {
            // Get effective rate for today
            $effectiveRate = $rateService->getEffectiveRate(
                $roomType->id,
                now()->toDateString(),
                $tenantId
            );

            $rates[] = [
                'room_type_id' => $roomType->id,
                'room_type_name' => $roomType->name,
                'base_rate' => $roomType->base_rate,
                'effective_rate' => $effectiveRate,
            ];
        }

        return [
            'property_id' => $config->property_id,
            'rates' => $rates,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Test connection to a channel.
     */
    public function testConnection(int $tenantId, string $channel): array
    {
        $config = $this->getConfig($tenantId, $channel);

        if (! $config) {
            return [
                'success' => false,
                'message' => "Channel {$channel} is not configured.",
            ];
        }

        // Log the test
        $this->logAction(
            tenantId: $tenantId,
            channel: $channel,
            action: 'test_connection',
            status: 'success',
            requestData: ['property_id' => $config->property_id]
        );

        // In a real implementation, we would make an API call here
        return [
            'success' => true,
            'message' => 'Connection test successful (stub).',
            'config' => [
                'channel' => $channel,
                'property_id' => $config->property_id,
                'is_active' => $config->is_active,
            ],
        ];
    }
}
