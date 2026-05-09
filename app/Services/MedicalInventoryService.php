<?php

namespace App\Services;

use App\Models\MedicalSupply;
use App\Models\MedicalSupplyRequest;
use App\Models\MedicalSupplyRequestItem;
use App\Models\MedicalSupplyTransaction;
use App\Models\MedicalWasteLog;
use App\Models\SterilizationLog;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MedicalInventoryService
{
    /**
     * Receive medical supplies
     */
    public function receiveSupplies(array $receiveData): MedicalSupplyTransaction
    {
        return DB::transaction(function () use ($receiveData) {
            $supply = MedicalSupply::findOrFail($receiveData['supply_id']);

            $previousQuantity = $supply->stock_quantity;
            $newQuantity = $previousQuantity + $receiveData['quantity'];

            // Create transaction
            $transaction = MedicalSupplyTransaction::create([
                'supply_id' => $supply->id,
                'created_by' => $receiveData['created_by'],
                'transaction_number' => $this->generateTransactionNumber(),
                'transaction_date' => now(),
                'transaction_type' => 'receipt',
                'quantity' => $receiveData['quantity'],
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $newQuantity,
                'reference_type' => $receiveData['reference_type'] ?? null,
                'reference_id' => $receiveData['reference_id'] ?? null,
                'reference_number' => $receiveData['reference_number'] ?? null,
                'destination_location' => $receiveData['location'] ?? $supply->storage_location,
                'batch_number' => $receiveData['batch_number'] ?? null,
                'expiry_date' => $receiveData['expiry_date'] ?? null,
                'notes' => $receiveData['notes'] ?? null,
            ]);

            // Update supply stock
            $supply->update([
                'stock_quantity' => $newQuantity,
                'expiry_date' => $receiveData['expiry_date'] ?? $supply->expiry_date,
            ]);

            Log::info('Medical supplies received', [
                'transaction_number' => $transaction->transaction_number,
                'supply' => $supply->supply_name,
                'quantity' => $receiveData['quantity'],
            ]);

            return $transaction;
        });
    }

    /**
     * Issue medical supplies
     */
    public function issueSupplies(array $issueData): MedicalSupplyTransaction
    {
        return DB::transaction(function () use ($issueData) {
            $supply = MedicalSupply::findOrFail($issueData['supply_id']);

            if ($supply->stock_quantity < $issueData['quantity']) {
                throw new Exception("Insufficient stock. Available: {$supply->stock_quantity}");
            }

            $previousQuantity = $supply->stock_quantity;
            $newQuantity = $previousQuantity - $issueData['quantity'];

            // Create transaction
            $transaction = MedicalSupplyTransaction::create([
                'supply_id' => $supply->id,
                'created_by' => $issueData['created_by'],
                'transaction_number' => $this->generateTransactionNumber(),
                'transaction_date' => now(),
                'transaction_type' => 'issue',
                'quantity' => $issueData['quantity'],
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $newQuantity,
                'reference_type' => $issueData['reference_type'] ?? null,
                'reference_id' => $issueData['reference_id'] ?? null,
                'reference_number' => $issueData['reference_number'] ?? null,
                'source_location' => $supply->storage_location,
                'destination_location' => $issueData['destination'] ?? null,
                'department_id' => $issueData['department_id'] ?? null,
                'notes' => $issueData['notes'] ?? null,
            ]);

            // Update supply stock
            $supply->update([
                'stock_quantity' => $newQuantity,
            ]);

            Log::info('Medical supplies issued', [
                'transaction_number' => $transaction->transaction_number,
                'supply' => $supply->supply_name,
                'quantity' => $issueData['quantity'],
            ]);

            return $transaction;
        });
    }

    /**
     * Adjust stock (correction)
     */
    public function adjustStock(int $supplyId, array $adjustmentData): MedicalSupplyTransaction
    {
        return DB::transaction(function () use ($supplyId, $adjustmentData) {
            $supply = MedicalSupply::findOrFail($supplyId);

            $previousQuantity = $supply->stock_quantity;
            $newQuantity = $adjustmentData['new_quantity'];
            $quantity = $newQuantity - $previousQuantity;

            // Create transaction
            $transaction = MedicalSupplyTransaction::create([
                'supply_id' => $supply->id,
                'created_by' => $adjustmentData['created_by'],
                'transaction_number' => $this->generateTransactionNumber(),
                'transaction_date' => now(),
                'transaction_type' => 'adjustment',
                'quantity' => $quantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $newQuantity,
                'notes' => $adjustmentData['notes'] ?? 'Stock adjustment',
            ]);

            // Update supply stock
            $supply->update([
                'stock_quantity' => $newQuantity,
            ]);

            Log::info('Stock adjusted', [
                'transaction_number' => $transaction->transaction_number,
                'supply' => $supply->supply_name,
                'adjustment' => $quantity,
            ]);

            return $transaction;
        });
    }

    /**
     * Create supply request
     */
    public function createSupplyRequest(array $requestData): MedicalSupplyRequest
    {
        return DB::transaction(function () use ($requestData) {
            $request = MedicalSupplyRequest::create([
                'requested_by' => $requestData['requested_by'],
                'department_id' => $requestData['department_id'],
                'request_number' => $this->generateRequestNumber(),
                'request_date' => today(),
                'required_date' => $requestData['required_date'] ?? null,
                'urgency' => $requestData['urgency'] ?? 'normal',
                'status' => 'submitted',
                'purpose' => $requestData['purpose'] ?? null,
                'justification' => $requestData['justification'] ?? null,
            ]);

            // Add request items
            foreach ($requestData['items'] as $item) {
                MedicalSupplyRequestItem::create([
                    'request_id' => $request->id,
                    'supply_id' => $item['supply_id'],
                    'quantity_requested' => $item['quantity'],
                    'status' => 'pending',
                ]);
            }

            Log::info('Supply request created', [
                'request_number' => $request->request_number,
                'items' => count($requestData['items']),
            ]);

            return $request;
        });
    }

    /**
     * Approve supply request
     */
    public function approveRequest(int $requestId, int $approvedBy): MedicalSupplyRequest
    {
        return DB::transaction(function () use ($requestId, $approvedBy) {
            $request = MedicalSupplyRequest::findOrFail($requestId);

            $request->update([
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'status' => 'approved',
            ]);

            // Update request items
            $request->items->each(function ($item) {
                $item->update([
                    'quantity_approved' => $item->quantity_requested,
                    'status' => 'approved',
                ]);
            });

            Log::info('Supply request approved', [
                'request_number' => $request->request_number,
            ]);

            return $request;
        });
    }

    /**
     * Log sterilization
     */
    public function logSterilization(array $sterilizationData): SterilizationLog
    {
        return DB::transaction(function () use ($sterilizationData) {
            $sterilization = SterilizationLog::create([
                'equipment_id' => $sterilizationData['equipment_id'] ?? null,
                'supply_id' => $sterilizationData['supply_id'] ?? null,
                'processed_by' => $sterilizationData['processed_by'],
                'sterilization_number' => $this->generateSterilizationNumber(),
                'sterilization_date' => $sterilizationData['sterilization_date'] ?? now(),
                'sterilization_method' => $sterilizationData['sterilization_method'],
                'temperature' => $sterilizationData['temperature'] ?? null,
                'duration_minutes' => $sterilizationData['duration_minutes'] ?? null,
                'pressure' => $sterilizationData['pressure'] ?? null,
                'chemical_indicator' => $sterilizationData['chemical_indicator'] ?? null,
                'biological_indicator' => $sterilizationData['biological_indicator'] ?? null,
                'load_size' => $sterilizationData['load_size'] ?? null,
                'load_contents' => $sterilizationData['load_contents'] ?? null,
                'sterilizer_id' => $sterilizationData['sterilizer_id'] ?? null,
                'next_sterilization_due' => $sterilizationData['next_sterilization_due'] ?? null,
                'compliance_standard' => $sterilizationData['compliance_standard'] ?? null,
            ]);

            Log::info('Sterilization logged', [
                'sterilization_number' => $sterilization->sterilization_number,
                'method' => $sterilization->sterilization_method,
            ]);

            return $sterilization;
        });
    }

    /**
     * Validate sterilization
     */
    public function validateSterilization(int $sterilizationId, int $validatedBy, string $result): SterilizationLog
    {
        return DB::transaction(function () use ($sterilizationId, $validatedBy, $result) {
            $sterilization = SterilizationLog::findOrFail($sterilizationId);

            $sterilization->update([
                'validated_by' => $validatedBy,
                'validation_result' => $result,
                'validated_at' => now(),
                'is_compliant' => $result === 'passed',
                'completion_date' => now(),
            ]);

            Log::info('Sterilization validated', [
                'sterilization_number' => $sterilization->sterilization_number,
                'result' => $result,
            ]);

            return $sterilization;
        });
    }

    /**
     * Log medical waste
     */
    public function logMedicalWaste(array $wasteData): MedicalWasteLog
    {
        return DB::transaction(function () use ($wasteData) {
            $wasteLog = MedicalWasteLog::create([
                'generated_by' => $wasteData['generated_by'],
                'department_id' => $wasteData['department_id'],
                'waste_log_number' => $this->generateWasteNumber(),
                'generation_date' => $wasteData['generation_date'] ?? now(),
                'waste_type' => $wasteData['waste_type'],
                'waste_description' => $wasteData['waste_description'],
                'weight_kg' => $wasteData['weight_kg'] ?? 0,
                'container_count' => $wasteData['container_count'] ?? 0,
                'container_type' => $wasteData['container_type'] ?? null,
                'handling_method' => $wasteData['handling_method'],
                'disposal_facility' => $wasteData['disposal_facility'] ?? null,
                'disposal_location' => $wasteData['disposal_location'] ?? null,
                'manifest_number' => $wasteData['manifest_number'] ?? null,
                'transporter_name' => $wasteData['transporter_name'] ?? null,
                'transporter_license' => $wasteData['transporter_license'] ?? null,
                'disposal_cost' => $wasteData['disposal_cost'] ?? 0,
                'is_compliant' => $wasteData['is_compliant'] ?? false,
            ]);

            Log::info('Medical waste logged', [
                'waste_log_number' => $wasteLog->waste_log_number,
                'waste_type' => $wasteLog->waste_type,
                'weight_kg' => $wasteLog->weight_kg,
            ]);

            return $wasteLog;
        });
    }

    /**
     * Get expiring supplies
     */
    public function getExpiringSupplies(int $days = 90): array
    {
        return MedicalSupply::expiringSoon($days)
            ->orderBy('expiry_date')
            ->get()
            ->map(function ($supply) {
                return [
                    'supply_code' => $supply->supply_code,
                    'supply_name' => $supply->supply_name,
                    'category' => $supply->category,
                    'stock_quantity' => $supply->stock_quantity,
                    'expiry_date' => $supply->expiry_date,
                    'days_until_expiry' => $supply->days_until_expiry,
                ];
            })
            ->toArray();
    }

    /**
     * Get low stock supplies
     */
    public function getLowStockSupplies(): array
    {
        return MedicalSupply::lowStock()
            ->orderBy('stock_quantity')
            ->get()
            ->map(function ($supply) {
                return [
                    'supply_code' => $supply->supply_code,
                    'supply_name' => $supply->supply_name,
                    'category' => $supply->category,
                    'stock_quantity' => $supply->stock_quantity,
                    'minimum_stock' => $supply->minimum_stock,
                    'reorder_quantity' => $supply->reorder_quantity,
                ];
            })
            ->toArray();
    }

    /**
     * Get inventory dashboard
     */
    public function getDashboardData(): array
    {
        return [
            'total_supplies' => MedicalSupply::where('is_active', true)->count(),
            'low_stock_count' => MedicalSupply::lowStock()->count(),
            'out_of_stock_count' => MedicalSupply::outOfStock()->count(),
            'expiring_soon' => MedicalSupply::expiringSoon(90)->count(),
            'expired_count' => MedicalSupply::expired()->count(),
            'pending_requests' => MedicalSupplyRequest::where('status', 'submitted')->count(),
            'total_inventory_value' => MedicalSupply::where('is_active', true)
                ->get()
                ->sum('stock_value'),
            'waste_today' => MedicalWasteLog::whereDate('generation_date', today())->sum('weight_kg'),
            'sterilizations_today' => SterilizationLog::whereDate('sterilization_date', today())->count(),
        ];
    }

    /**
     * Generate transaction number
     */
    protected function generateTransactionNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'TRX-MED-'.$date;

        $last = MedicalSupplyTransaction::where('transaction_number', 'like', $prefix.'%')
            ->orderBy('transaction_number', 'desc')
            ->first();

        return $prefix.'-'.str_pad(
            $last ? (int) substr($last->transaction_number, -4) + 1 : 1,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Generate request number
     */
    protected function generateRequestNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'REQ-MED-'.$date;

        $last = MedicalSupplyRequest::where('request_number', 'like', $prefix.'%')
            ->orderBy('request_number', 'desc')
            ->first();

        return $prefix.'-'.str_pad(
            $last ? (int) substr($last->request_number, -4) + 1 : 1,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Generate sterilization number
     */
    protected function generateSterilizationNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'STER-'.$date;

        $last = SterilizationLog::where('sterilization_number', 'like', $prefix.'%')
            ->orderBy('sterilization_number', 'desc')
            ->first();

        return $prefix.'-'.str_pad(
            $last ? (int) substr($last->sterilization_number, -4) + 1 : 1,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Generate waste number
     */
    protected function generateWasteNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'WASTE-'.$date;

        $last = MedicalWasteLog::where('waste_log_number', 'like', $prefix.'%')
            ->orderBy('waste_log_number', 'desc')
            ->first();

        return $prefix.'-'.str_pad(
            $last ? (int) substr($last->waste_log_number, -4) + 1 : 1,
            4,
            '0',
            STR_PAD_LEFT
        );
    }
}
