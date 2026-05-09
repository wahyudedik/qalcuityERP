<?php

namespace App\Services\ERP;

use App\Models\Shipment;
use App\Services\ShippingService;

class ShippingTools
{
    public function __construct(
        private int $tenantId,
        private int $userId,
        private ShippingService $shipping = new ShippingService
    ) {}

    public static function definitions(): array
    {
        return [
            [
                'name' => 'check_shipping_rate',
                'description' => 'Cek ongkos kirim dari kurir (JNE, J&T, SiCepat, dll)',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'origin' => ['type' => 'string', 'description' => 'Kota asal (kode kota RajaOngkir)'],
                        'destination' => ['type' => 'string', 'description' => 'Kota tujuan'],
                        'weight_kg' => ['type' => 'number', 'description' => 'Berat paket dalam kg'],
                        'courier' => ['type' => 'string', 'description' => 'Nama kurir: jne, jnt, sicepat, pos'],
                    ],
                    'required' => ['origin', 'destination', 'weight_kg', 'courier'],
                ],
            ],
            [
                'name' => 'track_shipment',
                'description' => 'Lacak status pengiriman berdasarkan nomor resi',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'courier' => ['type' => 'string', 'description' => 'Nama kurir'],
                        'tracking_number' => ['type' => 'string', 'description' => 'Nomor resi'],
                    ],
                    'required' => ['courier', 'tracking_number'],
                ],
            ],
            [
                'name' => 'list_shipments',
                'description' => 'Daftar pengiriman tenant',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'status' => ['type' => 'string', 'description' => 'Filter status: pending, shipped, delivered'],
                    ],
                ],
            ],
        ];
    }

    public function checkShippingRate(array $args): array
    {
        $rates = $this->shipping->getRates(
            $args['origin'],
            $args['destination'],
            $args['weight_kg'],
            $args['courier']
        );

        return ['status' => 'success', 'rates' => $rates];
    }

    public function trackShipment(array $args): array
    {
        $result = $this->shipping->track($args['courier'], $args['tracking_number']);

        return ['status' => 'success', 'tracking' => $result];
    }

    public function listShipments(array $args): array
    {
        $q = Shipment::where('tenant_id', $this->tenantId);
        if (! empty($args['status'])) {
            $q->where('status', $args['status']);
        }

        return ['status' => 'success', 'shipments' => $q->latest()->take(20)->get()->toArray()];
    }
}
