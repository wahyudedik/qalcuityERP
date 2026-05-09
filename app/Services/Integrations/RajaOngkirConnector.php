<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * RajaOngkir Logistics Connector
 *
 * Handles shipping rate calculation and tracking
 * Supports JNE, TIKI, POS, and other Indonesian couriers
 */
class RajaOngkirConnector extends BaseConnector
{
    protected string $apiUrl;

    protected string $apiKey;

    protected string $accountType; // starter, basic, pro

    public function __construct(Integration $integration)
    {
        parent::__construct($integration);

        $this->apiKey = $integration->getConfigValue('api_key');
        $this->accountType = $integration->getConfigValue('account_type') ?? 'starter';

        $this->apiUrl = $this->accountType === 'pro'
            ? 'https://rajaongkir.komerce.id/api'
            : 'https://api.rajaongkir.com/starter';
    }

    public function authenticate(): bool
    {
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->get("{$this->apiUrl}/province");

            if ($response->successful()) {
                $this->integration->markAsActive();

                return true;
            }

            return false;
        } catch (Throwable $e) {
            Log::error('RajaOngkir authentication failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Get shipping cost
     */
    public function getShippingCost(array $params): array
    {
        try {
            // Check cache first
            $cacheKey = "shipping_cost_{$params['origin']}_{$params['destination']}_{$params['weight']}";
            $cached = Cache::get($cacheKey);

            if ($cached) {
                return $cached;
            }

            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->post("{$this->apiUrl}/cost", [
                'origin' => $params['origin'], // City ID
                'destination' => $params['destination'], // City ID
                'weight' => $params['weight'], // grams
                'courier' => $params['courier'] ?? 'jne', // jne, tiki, pos, etc.
            ]);

            if ($response->successful()) {
                $data = $response->json()['rajaongkir']['results'] ?? [];

                $results = [];
                foreach ($data as $courier) {
                    foreach ($courier['costs'] ?? [] as $cost) {
                        $results[] = [
                            'courier' => strtoupper($courier['code']),
                            'service' => $cost['service'],
                            'cost' => $cost['cost'][0]['value'] ?? 0,
                            'etd' => $cost['cost'][0]['etd'] ?? '-',
                            'description' => $cost['description'] ?? '',
                        ];
                    }
                }

                // Cache for 1 hour
                Cache::put($cacheKey, $results, 3600);

                return ['success' => true, 'rates' => $results];
            }

            return ['success' => false, 'error' => 'Failed to get shipping cost'];
        } catch (Throwable $e) {
            return $this->handleError($e, 'getShippingCost');
        }
    }

    /**
     * Get all provinces
     */
    public function getProvinces(): array
    {
        try {
            $cacheKey = 'rajaongkir_provinces';
            $cached = Cache::get($cacheKey);

            if ($cached) {
                return $cached;
            }

            $response = Http::withHeaders(['key' => $this->apiKey])
                ->get("{$this->apiUrl}/province");

            if ($response->successful()) {
                $provinces = $response->json()['rajaongkir']['results'] ?? [];

                Cache::put($cacheKey, $provinces, 86400); // Cache 24 hours

                return ['success' => true, 'provinces' => $provinces];
            }

            return ['success' => false];
        } catch (Throwable $e) {
            return $this->handleError($e, 'getProvinces');
        }
    }

    /**
     * Get cities by province
     */
    public function getCities(int $provinceId): array
    {
        try {
            $response = Http::withHeaders(['key' => $this->apiKey])
                ->get("{$this->apiUrl}/city", ['province' => $provinceId]);

            if ($response->successful()) {
                $cities = $response->json()['rajaongkir']['results'] ?? [];

                return ['success' => true, 'cities' => $cities];
            }

            return ['success' => false];
        } catch (Throwable $e) {
            return $this->handleError($e, 'getCities');
        }
    }

    /**
     * Track shipment (Pro account only)
     */
    public function trackShipment(string $waybill, string $courier): array
    {
        try {
            $response = Http::withHeaders(['key' => $this->apiKey])
                ->post("{$this->apiUrl}/waybill", [
                    'waybill' => $waybill,
                    'courier' => strtolower($courier),
                ]);

            if ($response->successful()) {
                $data = $response->json()['rajaongkir']['result'] ?? [];

                return [
                    'success' => true,
                    'status' => $data['deliver_status'] ?? 'unknown',
                    'history' => $data['manifest'] ?? [],
                ];
            }

            return ['success' => false, 'error' => 'Failed to track shipment'];
        } catch (Throwable $e) {
            return $this->handleError($e, 'trackShipment');
        }
    }

    // E-commerce methods (not applicable)
    public function syncProducts(): array
    {
        return ['success' => true, 'processed' => 0, 'failed' => 0];
    }

    public function syncOrders(): array
    {
        return ['success' => true, 'processed' => 0, 'failed' => 0];
    }

    public function syncInventory(): array
    {
        return ['success' => true, 'processed' => 0, 'failed' => 0];
    }

    public function registerWebhooks(): array
    {
        return ['success' => true, 'registered' => []];
    }

    public function handleWebhook(array $payload): void
    {
        Log::info('RajaOngkir webhook', ['data' => $payload]);
    }
}
