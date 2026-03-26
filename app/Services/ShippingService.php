<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    private string $apiKey;
    private string $baseUrl;
    private string $tier;

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.key', '');
        $this->tier   = config('services.rajaongkir.tier', 'starter');
        $this->baseUrl = match($this->tier) {
            'pro'   => 'https://pro.rajaongkir.com/api',
            'basic' => 'https://api.rajaongkir.com/basic',
            default => 'https://api.rajaongkir.com/starter',
        };
    }

    /**
     * Cek ongkos kirim.
     */
    public function getRates(string $origin, string $destination, float $weight, string $courier): array
    {
        if (!$this->apiKey) {
            return $this->mockRates($courier, $weight);
        }

        try {
            $response = Http::withHeaders(['key' => $this->apiKey])
                ->timeout(10)
                ->post("{$this->baseUrl}/cost", [
                    'origin'      => $origin,
                    'destination' => $destination,
                    'weight'      => max(1, (int) ($weight * 1000)),
                    'courier'     => strtolower($courier),
                ]);

            if ($response->successful()) {
                $results = $response->json('rajaongkir.results', []);
                if (!empty($results)) {
                    return $results[0]['costs'] ?? [];
                }
            }

            Log::warning('ShippingService getRates failed', [
                'status' => $response->status(),
                'body'   => $response->json('rajaongkir.status', []),
            ]);
        } catch (\Throwable $e) {
            Log::error('ShippingService getRates exception: ' . $e->getMessage());
        }

        return $this->mockRates($courier, $weight);
    }

    /**
     * Lacak pengiriman (hanya tier pro).
     */
    public function track(string $courier, string $trackingNumber): array
    {
        if (!$this->apiKey) {
            return ['status' => 'demo', 'message' => 'Tracking tidak tersedia di mode demo.'];
        }

        if ($this->tier !== 'pro') {
            return ['status' => 'unavailable', 'message' => 'Tracking membutuhkan RajaOngkir tier Pro.'];
        }

        try {
            $response = Http::withHeaders(['key' => $this->apiKey])
                ->timeout(10)
                ->post("{$this->baseUrl}/waybill", [
                    'waybill' => $trackingNumber,
                    'courier' => strtolower($courier),
                ]);

            if ($response->successful()) {
                return $response->json('rajaongkir.result', ['status' => 'error']);
            }
        } catch (\Throwable $e) {
            Log::error('ShippingService track exception: ' . $e->getMessage());
        }

        return ['status' => 'error', 'message' => 'Gagal melacak pengiriman.'];
    }

    /**
     * Daftar provinsi.
     */
    public function getProvinces(): array
    {
        if (!$this->apiKey) return [];

        try {
            $response = Http::withHeaders(['key' => $this->apiKey])
                ->timeout(10)
                ->get("{$this->baseUrl}/province");

            return $response->json('rajaongkir.results', []);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Daftar kota berdasarkan provinsi.
     */
    public function getCities(?int $provinceId = null): array
    {
        if (!$this->apiKey) return [];

        try {
            $params = $provinceId ? ['province' => $provinceId] : [];
            $response = Http::withHeaders(['key' => $this->apiKey])
                ->timeout(10)
                ->get("{$this->baseUrl}/city", $params);

            return $response->json('rajaongkir.results', []);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Mock rates untuk demo/tanpa API key.
     */
    private function mockRates(string $courier, float $weight): array
    {
        $base = max(8000, (int) ($weight * 10000));
        $courierName = strtoupper($courier ?: 'JNE');
        return [
            ['service' => 'REG', 'description' => "{$courierName} Reguler", 'cost' => [['value' => $base, 'etd' => '3-5 hari']]],
            ['service' => 'YES', 'description' => "{$courierName} Express", 'cost' => [['value' => (int)($base * 1.8), 'etd' => '1-2 hari']]],
            ['service' => 'OKE', 'description' => "{$courierName} Ekonomi", 'cost' => [['value' => (int)($base * 0.7), 'etd' => '4-7 hari']]],
        ];
    }
}
