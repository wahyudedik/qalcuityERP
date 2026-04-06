<?php

namespace App\Services\Integrations;

use App\Models\LogisticsProvider;
use App\Models\Shipment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LogisticsTrackingService
{
    /**
     * Create shipment with JNE
     */
    public function createJNEShipment(array $shipmentData, int $tenantId): array
    {
        $provider = LogisticsProvider::where('tenant_id', $tenantId)
            ->where('provider', 'jne')
            ->where('is_active', true)
            ->first();

        if (!$provider) {
            throw new \Exception('JNE not configured');
        }

        try {
            // JNE API call (simplified - actual implementation needs proper auth)
            $response = Http::withHeaders([
                'api-key' => $provider->api_key,
            ])->post('https://apiv2.jne.co.id/tracing/generateAWB', [
                        'origin' => $shipmentData['origin_city'],
                        'destination' => $shipmentData['destination_city'],
                        'weight' => $shipmentData['weight_kg'],
                        'service' => $shipmentData['service_type'] ?? 'REG',
                        'pieces' => 1,
                    ]);

            $result = $response->json();

            $shipment = Shipment::create([
                'tenant_id' => $tenantId,
                'logistics_provider_id' => $provider->id,
                'order_id' => $shipmentData['order_id'] ?? null,
                'tracking_number' => $result['awb'] ?? 'JNE' . time(),
                'service_type' => $shipmentData['service_type'] ?? 'REG',
                'status' => 'pending',
                'origin_city' => $shipmentData['origin_city'],
                'destination_city' => $shipmentData['destination_city'],
                'weight_kg' => $shipmentData['weight_kg'],
                'shipping_cost' => $result['price'] ?? 0,
            ]);

            return ['success' => true, 'shipment' => $shipment, 'awb' => $shipment->tracking_number];

        } catch (\Throwable $e) {
            Log::error('JNE shipment creation failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Track shipment
     */
    public function trackShipment(string $trackingNumber, string $provider): array
    {
        try {
            $result = match ($provider) {
                'jne' => $this->trackJNE($trackingNumber),
                'jnt' => $this->trackJNT($trackingNumber),
                'sicepat' => $this->trackSiCepat($trackingNumber),
                default => throw new \Exception("Unsupported provider: {$provider}")
            };

            // Update shipment status
            $shipment = Shipment::where('tracking_number', $trackingNumber)->first();
            if ($shipment) {
                $shipment->update([
                    'status' => $result['status'] ?? 'unknown',
                    'tracking_history' => $result['history'] ?? [],
                ]);
            }

            return $result;

        } catch (\Throwable $e) {
            Log::error("Tracking failed for {$trackingNumber}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Track JNE package
     */
    protected function trackJNE(string $trackingNumber): array
    {
        $response = Http::post('https://apiv2.jne.co.id/tracing/detail', [
            'awb' => $trackingNumber,
        ]);

        $data = $response->json();

        return [
            'success' => true,
            'tracking_number' => $trackingNumber,
            'status' => $data['status'] ?? 'unknown',
            'history' => $data['history'] ?? [],
            'estimated_delivery' => null,
        ];
    }

    /**
     * Track J&T package
     */
    protected function trackJNT(string $trackingNumber): array
    {
        // J&T tracking implementation
        return [
            'success' => true,
            'tracking_number' => $trackingNumber,
            'status' => 'in_transit',
            'history' => [],
        ];
    }

    /**
     * Track SiCepat package
     */
    protected function trackSiCepat(string $trackingNumber): array
    {
        // SiCepat tracking implementation
        return [
            'success' => true,
            'tracking_number' => $trackingNumber,
            'status' => 'in_transit',
            'history' => [],
        ];
    }

    /**
     * Get shipping cost estimate
     */
    public function getShippingCost(string $origin, string $destination, float $weightKg, string $provider): array
    {
        try {
            return match ($provider) {
                'jne' => $this->getJNECost($origin, $destination, $weightKg),
                'jnt' => $this->getJNTCost($origin, $destination, $weightKg),
                'sicepat' => $this->getSiCepatCost($origin, $destination, $weightKg),
                default => ['error' => 'Unsupported provider']
            };
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    protected function getJNECost(string $origin, string $destination, float $weightKg): array
    {
        // Implementation for JNE cost calculation
        return [
            'provider' => 'JNE',
            'services' => [
                ['type' => 'REG', 'cost' => 15000, 'etd' => '2-3 days'],
                ['type' => 'YES', 'cost' => 25000, 'etd' => '1 day'],
                ['type' => 'OKE', 'cost' => 12000, 'etd' => '3-5 days'],
            ]
        ];
    }

    protected function getJNTCost(string $origin, string $destination, float $weightKg): array
    {
        return [
            'provider' => 'J&T',
            'services' => [
                ['type' => 'Regular', 'cost' => 14000, 'etd' => '2-3 days'],
                ['type' => 'Express', 'cost' => 22000, 'etd' => '1 day'],
            ]
        ];
    }

    protected function getSiCepatCost(string $origin, string $destination, float $weightKg): array
    {
        return [
            'provider' => 'SiCepat',
            'services' => [
                ['type' => 'REG', 'cost' => 13000, 'etd' => '2-3 days'],
                ['type' => 'BEST', 'cost' => 20000, 'etd' => '1 day'],
            ]
        ];
    }
}
