<?php

namespace App\Services;

use App\Models\MaterialDelivery;
use Carbon\Carbon;

/**
 * Material Delivery Tracking Service untuk Konstruksi
 */
class MaterialDeliveryService
{
    /**
     * Create material delivery record
     */
    public function createDelivery(array $data, int $tenantId): MaterialDelivery
    {
        // Generate delivery number
        $deliveryNumber = 'DEL-' . date('Ymd') . '-' . str_pad(
            MaterialDelivery::where('tenant_id', $tenantId)->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );

        return MaterialDelivery::create([
            'tenant_id' => $tenantId,
            'project_id' => $data['project_id'],
            'delivery_number' => $deliveryNumber,
            'supplier_id' => $data['supplier_id'] ?? null,
            'supplier_name' => $data['supplier_name'],
            'material_name' => $data['material_name'],
            'material_category' => $data['material_category'] ?? null,
            'quantity_ordered' => $data['quantity_ordered'],
            'quantity_delivered' => 0,
            'unit' => $data['unit'],
            'unit_price' => $data['unit_price'] ?? 0,
            'total_value' => ($data['quantity_ordered'] ?? 0) * ($data['unit_price'] ?? 0),
            'expected_date' => $data['expected_date'],
            'actual_delivery_date' => null,
            'delivery_status' => 'pending',
            'po_number' => $data['po_number'] ?? null,
            'do_number' => $data['do_number'] ?? null,
            'vehicle_number' => $data['vehicle_number'] ?? null,
            'driver_name' => $data['driver_name'] ?? null,
            'driver_phone' => $data['driver_phone'] ?? null,
            'received_by' => null,
            'quality_check_status' => 'pending',
            'quality_notes' => null,
            'photos' => [],
            'remarks' => $data['remarks'] ?? null,
        ]);
    }

    /**
     * Update delivery status to in_transit
     */
    public function markInTransit(int $deliveryId, int $tenantId): MaterialDelivery
    {
        $delivery = MaterialDelivery::where('id', $deliveryId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $delivery->update(['delivery_status' => 'in_transit']);

        return $delivery;
    }

    /**
     * Record material delivery received
     */
    public function receiveDelivery(array $data, int $deliveryId, int $tenantId): MaterialDelivery
    {
        $delivery = MaterialDelivery::where('id', $deliveryId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $photos = $this->handlePhotoUpload($data['photos'] ?? []);

        $delivery->update([
            'quantity_delivered' => $data['quantity_delivered'],
            'actual_delivery_date' => now(),
            'delivery_status' => $data['quantity_delivered'] >= $delivery->quantity_ordered
                ? 'delivered'
                : 'partial',
            'received_by' => auth()->id(),
            'quality_check_status' => $data['quality_check_status'] ?? 'pending',
            'quality_notes' => $data['quality_notes'] ?? null,
            'photos' => array_merge($delivery->photos ?? [], $photos),
            'remarks' => $data['remarks'] ?? $delivery->remarks,
        ]);

        return $delivery;
    }

    /**
     * Pass quality check
     */
    public function passQualityCheck(int $deliveryId, int $tenantId, ?string $notes = null): MaterialDelivery
    {
        $delivery = MaterialDelivery::where('id', $deliveryId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $delivery->update([
            'quality_check_status' => 'passed',
            'quality_notes' => $notes ?? $delivery->quality_notes,
        ]);

        return $delivery;
    }

    /**
     * Fail quality check
     */
    public function failQualityCheck(int $deliveryId, int $tenantId, string $reason): MaterialDelivery
    {
        $delivery = MaterialDelivery::where('id', $deliveryId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $delivery->update([
            'quality_check_status' => 'failed',
            'quality_notes' => $reason,
            'delivery_status' => 'cancelled',
        ]);

        return $delivery;
    }

    /**
     * Get delivery tracking summary for a project
     */
    public function getDeliverySummary(int $projectId, int $tenantId, ?string $period = 'month'): array
    {
        $query = MaterialDelivery::where('project_id', $projectId)
            ->where('tenant_id', $tenantId);

        // Apply period filter
        if ($period === 'week') {
            $query->whereBetween('expected_date', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereMonth('expected_date', now()->month)
                ->whereYear('expected_date', now()->year);
        }

        $deliveries = $query->get();

        return [
            'total_deliveries' => $deliveries->count(),
            'by_status' => $deliveries->groupBy('delivery_status')
                ->map->count()
                ->toArray(),
            'on_time_deliveries' => $deliveries->filter(fn($d) => $d->isOnTime())->count(),
            'delayed_deliveries' => $deliveries->filter(fn($d) => !$d->isOnTime() && $d->actual_delivery_date)->count(),
            'pending_deliveries' => $deliveries->where('delivery_status', 'pending')->count(),
            'total_value' => $deliveries->sum('total_value'),
            'avg_delay_days' => $deliveries->filter(fn($d) => !$d->isOnTime())
                ->map(fn($d) => $d->getDaysDelayed())
                ->avg() ?? 0,
            'by_category' => $deliveries->groupBy('material_category')
                ->map(fn($items) => [
                    'count' => $items->count(),
                    'total_quantity' => $items->sum('quantity_ordered'),
                    'total_value' => $items->sum('total_value'),
                ])
                ->toArray(),
            'recent_deliveries' => $deliveries->sortByDesc('created_at')->take(10)->map(fn($d) => [
                'id' => $d->id,
                'delivery_number' => $d->delivery_number,
                'material_name' => $d->material_name,
                'quantity' => "{$d->quantity_delivered}/{$d->quantity_ordered} {$d->unit}",
                'status' => $d->delivery_status,
                'expected_date' => $d->expected_date?->format('Y-m-d'),
                'actual_date' => $d->actual_delivery_date?->format('Y-m-d H:i'),
                'is_on_time' => $d->isOnTime(),
                'days_delayed' => $d->getDaysDelayed(),
                'quality_status' => $d->quality_check_status,
            ]),
        ];
    }

    /**
     * Get delayed deliveries report
     */
    public function getDelayedDeliveries(int $tenantId): array
    {
        $deliveries = MaterialDelivery::where('tenant_id', $tenantId)
            ->whereNotNull('actual_delivery_date')
            ->get()
            ->filter(fn($d) => !$d->isOnTime())
            ->sortByDesc(fn($d) => $d->getDaysDelayed());

        return [
            'total_delayed' => $deliveries->count(),
            'avg_delay_days' => $deliveries->map(fn($d) => $d->getDaysDelayed())->avg() ?? 0,
            'max_delay_days' => $deliveries->map(fn($d) => $d->getDaysDelayed())->max() ?? 0,
            'deliveries' => $deliveries->map(fn($d) => [
                'delivery_number' => $d->delivery_number,
                'material_name' => $d->material_name,
                'supplier_name' => $d->supplier_name,
                'expected_date' => $d->expected_date?->format('Y-m-d'),
                'actual_date' => $d->actual_delivery_date?->format('Y-m-d'),
                'days_delayed' => $d->getDaysDelayed(),
                'project_name' => $d->project?->name,
            ]),
        ];
    }

    /**
     * Get material shortage report
     */
    public function getShortageReport(int $projectId, int $tenantId): array
    {
        $deliveries = MaterialDelivery::where('project_id', $projectId)
            ->where('tenant_id', $tenantId)
            ->where('delivery_status', '!=', 'cancelled')
            ->get();

        $shortages = $deliveries->filter(fn($d) => $d->getShortage() > 0);

        return [
            'total_shortages' => $shortages->count(),
            'total_shortage_value' => $shortages->sum(fn($d) => $d->getShortage() * $d->unit_price),
            'items' => $shortages->map(fn($d) => [
                'delivery_number' => $d->delivery_number,
                'material_name' => $d->material_name,
                'ordered' => $d->quantity_ordered,
                'delivered' => $d->quantity_delivered,
                'shortage' => $d->getShortage(),
                'unit' => $d->unit,
                'shortage_value' => $d->getShortage() * $d->unit_price,
            ]),
        ];
    }

    /**
     * Handle photo uploads
     */
    private function handlePhotoUpload(array $photos): array
    {
        $uploadedPaths = [];

        foreach ($photos as $photo) {
            if ($photo instanceof \Illuminate\Http\UploadedFile) {
                $path = $photo->store('material-deliveries', 'public');
                $uploadedPaths[] = $path;
            }
        }

        return $uploadedPaths;
    }
}
