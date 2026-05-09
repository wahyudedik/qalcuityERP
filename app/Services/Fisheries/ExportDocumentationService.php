<?php

namespace App\Services\Fisheries;

use App\Models\CustomsDeclaration;
use App\Models\ExportPermit;
use App\Models\ExportShipment;
use App\Models\HealthCertificate;
use Illuminate\Support\Str;

class ExportDocumentationService
{
    /**
     * Apply for export permit
     */
    public function applyForPermit(int $tenantId, array $data): ExportPermit
    {
        return ExportPermit::create([
            'tenant_id' => $tenantId,
            'permit_number' => $data['permit_number'] ?? 'EP-'.date('Ymd').'-'.Str::upper(Str::random(5)),
            'permit_type' => $data['permit_type'] ?? 'general',
            'destination_country' => $data['destination_country'],
            'destination_address' => $data['destination_address'] ?? null,
            'issue_date' => $data['issue_date'] ?? now(),
            'expiry_date' => $data['expiry_date'],
            'issuing_authority' => $data['issuing_authority'],
            'authorized_quantity' => $data['authorized_quantity'] ?? null,
            'authorized_species' => $data['authorized_species'] ?? [],
            'status' => 'active',
            'conditions' => $data['conditions'] ?? null,
            'document_path' => $data['document_path'] ?? null,
        ]);
    }

    /**
     * Issue health certificate
     */
    public function issueHealthCertificate(int $tenantId, array $data): HealthCertificate
    {
        return HealthCertificate::create([
            'tenant_id' => $tenantId,
            'certificate_number' => $data['certificate_number'] ?? 'HC-'.date('Ymd').'-'.Str::upper(Str::random(5)),
            'product_batch_id' => $data['product_batch_id'] ?? null,
            'catch_log_id' => $data['catch_log_id'] ?? null,
            'certificate_type' => $data['certificate_type'] ?? 'health',
            'inspection_date' => $data['inspection_date'] ?? now(),
            'issue_date' => now(),
            'expiry_date' => $data['expiry_date'],
            'issued_by' => $data['issued_by'],
            'issuing_authority' => $data['issuing_authority'],
            'inspection_results' => $data['inspection_results'] ?? null,
            'certifications' => $data['certifications'] ?? null,
            'status' => 'valid',
            'document_path' => $data['document_path'] ?? null,
        ]);
    }

    /**
     * Create customs declaration
     */
    public function createCustomsDeclaration(int $tenantId, array $data): CustomsDeclaration
    {
        return CustomsDeclaration::create([
            'tenant_id' => $tenantId,
            'declaration_number' => $data['declaration_number'] ?? 'CD-'.date('Ymd').'-'.Str::upper(Str::random(5)),
            'shipment_id' => $data['shipment_id'] ?? null,
            'export_permit_id' => $data['export_permit_id'] ?? null,
            'hs_code' => $data['hs_code'],
            'country_of_origin' => $data['country_of_origin'] ?? 'Indonesia',
            'destination_country' => $data['destination_country'],
            'declared_value' => $data['declared_value'],
            'currency' => $data['currency'] ?? 'IDR',
            'total_weight' => $data['total_weight'],
            'package_count' => $data['package_count'] ?? 0,
            'package_type' => $data['package_type'] ?? null,
            'goods_description' => $data['goods_description'],
            'declaration_date' => now(),
            'status' => 'draft',
            'customs_office' => $data['customs_office'] ?? null,
            'document_path' => $data['document_path'] ?? null,
        ]);
    }

    /**
     * Submit customs declaration
     */
    public function submitCustomsDeclaration(int $declarationId): bool
    {
        try {
            $declaration = CustomsDeclaration::findOrFail($declarationId);
            $declaration->update(['status' => 'submitted']);

            // TODO: Integrate with customs API
            \Log::info('Customs declaration submitted', ['declaration_id' => $declarationId]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Submit customs declaration failed', [
                'declaration_id' => $declarationId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Create export shipment
     */
    public function createShipment(int $tenantId, array $data): ExportShipment
    {
        return ExportShipment::create([
            'tenant_id' => $tenantId,
            'shipment_number' => $data['shipment_number'] ?? 'ES-'.date('Ymd').'-'.Str::upper(Str::random(5)),
            'customs_declaration_id' => $data['customs_declaration_id'] ?? null,
            'transport_id' => $data['transport_id'] ?? null,
            'shipment_date' => $data['shipment_date'] ?? now(),
            'estimated_arrival' => $data['estimated_arrival'] ?? null,
            'origin_port' => $data['origin_port'],
            'destination_port' => $data['destination_port'],
            'shipping_method' => $data['shipping_method'] ?? 'sea',
            'carrier_name' => $data['carrier_name'] ?? null,
            'tracking_number' => $data['tracking_number'] ?? null,
            'total_value' => $data['total_value'] ?? null,
            'incoterm' => $data['incoterm'] ?? null,
            'status' => 'preparing',
            'shipping_documents' => $data['shipping_documents'] ?? [],
        ]);
    }

    /**
     * Update shipment status
     */
    public function updateShipmentStatus(int $shipmentId, string $status, ?string $actualArrival = null): bool
    {
        try {
            $shipment = ExportShipment::findOrFail($shipmentId);

            $updateData = ['status' => $status];

            if ($status === 'delivered' && $actualArrival) {
                $updateData['actual_arrival'] = $actualArrival;
            }

            $shipment->update($updateData);

            return true;
        } catch (\Exception $e) {
            \Log::error('Update shipment status failed', [
                'shipment_id' => $shipmentId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get all export documents for tenant
     */
    public function getExportDocuments(int $tenantId, ?string $type = null): array
    {
        $documents = [];

        if (! $type || $type === 'permits') {
            $documents['permits'] = ExportPermit::where('tenant_id', $tenantId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($permit) {
                    return [
                        'type' => 'permit',
                        'number' => $permit->permit_number,
                        'destination' => $permit->destination_country,
                        'status' => $permit->status,
                        'is_valid' => $permit->isValid(),
                        'days_until_expiry' => $permit->daysUntilExpiry(),
                    ];
                });
        }

        if (! $type || $type === 'certificates') {
            $documents['certificates'] = HealthCertificate::where('tenant_id', $tenantId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($cert) {
                    return [
                        'type' => 'certificate',
                        'number' => $cert->certificate_number,
                        'cert_type' => $cert->certificate_type,
                        'status' => $cert->status,
                        'is_valid' => $cert->isValid(),
                    ];
                });
        }

        if (! $type || $type === 'declarations') {
            $documents['declarations'] = CustomsDeclaration::where('tenant_id', $tenantId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($decl) {
                    return [
                        'type' => 'declaration',
                        'number' => $decl->declaration_number,
                        'destination' => $decl->destination_country,
                        'status' => $decl->status,
                        'is_cleared' => $decl->isCleared(),
                    ];
                });
        }

        if (! $type || $type === 'shipments') {
            $documents['shipments'] = ExportShipment::where('tenant_id', $tenantId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($shipment) {
                    return [
                        'type' => 'shipment',
                        'number' => $shipment->shipment_number,
                        'destination' => $shipment->destination_port,
                        'status' => $shipment->status,
                        'is_in_transit' => $shipment->isInTransit(),
                        'is_delivered' => $shipment->isDelivered(),
                    ];
                });
        }

        return $documents;
    }

    /**
     * Validate export readiness
     */
    public function validateExportReadiness(int $shipmentId): array
    {
        $shipment = ExportShipment::with('customsDeclaration.exportPermit')->findOrFail($shipmentId);

        $checks = [
            'has_customs_declaration' => $shipment->customsDeclaration !== null,
            'customs_submitted' => $shipment->customsDeclaration?->status === 'submitted',
            'customs_cleared' => $shipment->customsDeclaration?->isCleared(),
            'has_export_permit' => $shipment->customsDeclaration?->exportPermit !== null,
            'permit_valid' => $shipment->customsDeclaration?->exportPermit?->isValid(),
            'has_health_certificate' => false, // Check if related to catch/batch with certificate
            'all_documents_complete' => false,
        ];

        $checks['all_documents_complete'] = $checks['has_customs_declaration']
            && $checks['customs_cleared']
            && $checks['has_export_permit']
            && $checks['permit_valid'];

        return [
            'shipment_id' => $shipmentId,
            'shipment_number' => $shipment->shipment_number,
            'is_ready' => $checks['all_documents_complete'],
            'checks' => $checks,
            'missing_documents' => array_keys(array_filter($checks, function ($value) {
                return ! $value;
            })),
        ];
    }

    /**
     * Generate export report
     */
    public function generateExportReport(int $tenantId, string $periodStart, string $periodEnd): array
    {
        $permits = ExportPermit::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->get();

        $shipments = ExportShipment::where('tenant_id', $tenantId)
            ->whereBetween('shipment_date', [$periodStart, $periodEnd])
            ->get();

        $totalValue = $shipments->sum('total_value');
        $deliveredCount = $shipments->where('status', 'delivered')->count();

        return [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_permits' => $permits->count(),
            'active_permits' => $permits->where('status', 'active')->count(),
            'total_shipments' => $shipments->count(),
            'delivered_shipments' => $deliveredCount,
            'in_transit_shipments' => $shipments->where('status', 'in_transit')->count(),
            'total_export_value' => $totalValue,
            'top_destinations' => $shipments->groupBy('destination_port')
                ->map(fn ($group) => $group->count())
                ->sortByDesc()
                ->take(5),
        ];
    }
}
