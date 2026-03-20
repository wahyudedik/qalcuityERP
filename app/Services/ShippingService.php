<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ShippingService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.rajaongkir.com/starter';

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.key', '');
    }

    public function getRates(string $origin, string $destination, float $weight, string $courier): array
    {
        if (!$this->apiKey) {
            return $this->mockRates($courier, $weight);
        }

        $response = Http::withHeaders(['key' => $this->apiKey])
            ->post("{$this->baseUrl}/cost", [
                'origin'      => $origin,
                'destination' => $destination,
                'weight'      => (int) ($weight * 1000), // grams
                'courier'     => strtolower($courier),
            ]);

        if ($response->successful()) {
            return $response->json('rajaongkir.results.0.costs', []);
        }

        return $this->mockRates($courier, $weight);
    }

    public function track(string $courier, string $trackingNumber): array
    {
        if (!$this->apiKey) {
            return ['status' => 'demo', 'message' => 'Tracking tidak tersedia di mode demo.'];
        }

        $response = Http::withHeaders(['key' => $this->apiKey])
            ->post("{$this->baseUrl}/waybill", [
                'waybill' => $trackingNumber,
                'courier' => strtolower($courier),
            ]);

        return $response->json('rajaongkir.result', ['status' => 'error']);
    }

    private function mockRates(string $courier, float $weight): array
    {
        $base = (int) ($weight * 10000);
        return [
            ['service' => 'REG', 'description' => 'Reguler', 'cost' => [['value' => $base, 'etd' => '3-5']]],
            ['service' => 'YES', 'description' => 'Yakin Esok Sampai', 'cost' => [['value' => $base * 2, 'etd' => '1']]],
        ];
    }
}
