<?php

namespace App\Services;

use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Kurangi stok dari beberapa gudang sekaligus dalam satu database transaction
     * dengan pessimistic locking untuk mencegah race condition stok negatif.
     *
     * @param  int    $orderId
     * @param  array  $warehouseItems  Array of ['warehouse_id', 'product_id', 'quantity']
     * @throws \DomainException  Jika stok tidak mencukupi di salah satu gudang
     */
    public function deductStockMultiWarehouse(int $orderId, array $warehouseItems): void
    {
        DB::transaction(function () use ($orderId, $warehouseItems) {
            foreach ($warehouseItems as $item) {
                $stock = WarehouseStock::where('warehouse_id', $item['warehouse_id'])
                    ->where('product_id', $item['product_id'])
                    ->lockForUpdate()  // Pessimistic locking
                    ->firstOrFail();

                if ($stock->quantity < $item['quantity']) {
                    throw new \DomainException(
                        "Stok tidak cukup di gudang {$item['warehouse_id']} untuk produk {$item['product_id']}."
                    );
                }

                $stock->decrement('quantity', $item['quantity']);
            }
        });
    }
}
