<?php

namespace App\Services\MultiCompany;

use App\Models\InventoryTransfer;
use App\Models\InventoryTransferItem;

class InventoryTransferService
{
    /**
     * Create inventory transfer
     */
    public function createTransfer(array $data): InventoryTransfer
    {
        $transferNumber = $this->generateTransferNumber($data['company_group_id']);

        $transfer = InventoryTransfer::create([
            'company_group_id' => $data['company_group_id'],
            'from_tenant_id' => $data['from_tenant_id'],
            'to_tenant_id' => $data['to_tenant_id'],
            'transfer_number' => $transferNumber,
            'transfer_date' => $data['transfer_date'],
            'expected_arrival_date' => $data['expected_arrival_date'] ?? null,
            'status' => 'draft',
            'shipping_method' => $data['shipping_method'] ?? null,
            'shipping_cost' => $data['shipping_cost'] ?? 0.00,
            'notes' => $data['notes'] ?? null,
            'created_by_user_id' => $data['created_by_user_id'],
        ]);

        // Add items
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                InventoryTransferItem::create([
                    'inventory_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity_requested' => $item['quantity'],
                    'quantity_sent' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'] ?? 0.00,
                    'batch_number' => $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);
            }
        }

        return $transfer;
    }

    /**
     * Send transfer (update status to in_transit)
     */
    public function sendTransfer(int $transferId, string $trackingNumber = null): bool
    {
        try {
            $transfer = InventoryTransfer::findOrFail($transferId);

            $transfer->update([
                'status' => 'in_transit',
                'tracking_number' => $trackingNumber,
            ]);

            // Deduct inventory from source tenant
            $this->deductSourceInventory($transfer);

            return true;
        } catch (\Exception $e) {
            \Log::error('Send transfer failed', [
                'transfer_id' => $transferId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Receive transfer
     */
    public function receiveTransfer(int $transferId, int $receivedByUserId, array $receivedQuantities = []): bool
    {
        try {
            $transfer = InventoryTransfer::findOrFail($transferId);

            // Update items with received quantities
            foreach ($transfer->items as $item) {
                $receivedQty = $receivedQuantities[$item->id] ?? $item->quantity_sent;

                $item->update([
                    'quantity_received' => $receivedQty,
                ]);
            }

            $transfer->update([
                'status' => 'received',
                'actual_arrival_date' => now(),
                'received_by_user_id' => $receivedByUserId,
            ]);

            // Add inventory to destination tenant
            $this->addDestinationInventory($transfer);

            return true;
        } catch (\Exception $e) {
            \Log::error('Receive transfer failed', [
                'transfer_id' => $transferId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Cancel transfer
     */
    public function cancelTransfer(int $transferId): bool
    {
        try {
            $transfer = InventoryTransfer::findOrFail($transferId);

            if ($transfer->status === 'received') {
                return false; // Cannot cancel received transfers
            }

            $transfer->update(['status' => 'cancelled']);

            return true;
        } catch (\Exception $e) {
            \Log::error('Cancel transfer failed', [
                'transfer_id' => $transferId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get transfer by number
     */
    public function getTransferByNumber(string $transferNumber): ?InventoryTransfer
    {
        return InventoryTransfer::where('transfer_number', $transferNumber)
            ->with(['items.product', 'fromTenant', 'toTenant', 'createdBy', 'receivedBy'])
            ->first();
    }

    /**
     * Get pending transfers
     */
    public function getPendingTransfers(int $groupId): array
    {
        return InventoryTransfer::where('company_group_id', $groupId)
            ->whereIn('status', ['draft', 'in_transit'])
            ->with(['fromTenant', 'toTenant'])
            ->orderBy('transfer_date', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get transfer history
     */
    public function getTransferHistory(int $groupId, ?string $status = null): array
    {
        $query = InventoryTransfer::where('company_group_id', $groupId)
            ->with(['fromTenant', 'toTenant', 'items.product']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('transfer_date', 'desc')->get()->toArray();
    }

    /**
     * Generate transfer number
     */
    protected function generateTransferNumber(int $groupId): string
    {
        $prefix = 'ITF';
        $date = now()->format('Ymd');
        $sequence = str_pad(InventoryTransfer::where('company_group_id', $groupId)
            ->whereDate('created_at', today())
            ->count() + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$sequence}";
    }

    /**
     * Deduct inventory from source tenant
     */
    protected function deductSourceInventory(InventoryTransfer $transfer): void
    {
        // In production, this would update actual inventory records
        // For each item, reduce stock in from_tenant
    }

    /**
     * Add inventory to destination tenant
     */
    protected function addDestinationInventory(InventoryTransfer $transfer): void
    {
        // In production, this would update actual inventory records
        // For each item, increase stock in to_tenant
    }
}
