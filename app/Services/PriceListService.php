<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Product;

class PriceListService
{
    /**
     * Dapatkan harga terbaik untuk customer + produk + qty tertentu.
     * Cek semua price list yang berlaku untuk customer, ambil harga terendah.
     * Jika tidak ada price list, kembalikan harga normal produk.
     */
    public function getPrice(int $customerId, int $productId, float $qty = 1): array
    {
        $customer = Customer::with(['priceLists' => fn($q) =>
            $q->where('is_active', true)
              ->orderBy('customer_price_lists.priority')
        ])->find($customerId);

        if (! $customer) {
            return $this->defaultPrice($productId);
        }

        $today = today();
        $bestPrice = null;
        $bestDiscount = 0;
        $appliedList = null;

        foreach ($customer->priceLists as $priceList) {
            // Cek validitas tanggal
            if ($priceList->valid_from && $today->lt($priceList->valid_from)) continue;
            if ($priceList->valid_until && $today->gt($priceList->valid_until)) continue;

            // Cari item yang cocok dengan qty minimum terpenuhi
            $item = PriceListItem::where('price_list_id', $priceList->id)
                ->where('product_id', $productId)
                ->where('min_qty', '<=', $qty)
                ->orderByDesc('min_qty') // ambil tier tertinggi yang terpenuhi
                ->first();

            if (! $item) continue;

            $effective = $item->effectivePrice();

            if ($bestPrice === null || $effective < $bestPrice) {
                $bestPrice    = $effective;
                $bestDiscount = (float) $item->discount_percent;
                $appliedList  = $priceList;
            }
        }

        if ($bestPrice !== null) {
            return [
                'price'            => $bestPrice,
                'discount_percent' => $bestDiscount,
                'price_list_id'    => $appliedList->id,
                'price_list_name'  => $appliedList->name,
                'source'           => 'price_list',
            ];
        }

        return $this->defaultPrice($productId);
    }

    /**
     * Dapatkan harga untuk semua produk sekaligus (bulk, untuk form quotation/invoice).
     * Return: ['product_id' => ['price' => ..., 'price_list_name' => ...], ...]
     */
    public function getPricesForCustomer(int $customerId, array $productIds): array
    {
        $result = [];
        foreach ($productIds as $productId) {
            $result[$productId] = $this->getPrice($customerId, $productId);
        }
        return $result;
    }

    private function defaultPrice(int $productId): array
    {
        $product = Product::find($productId);
        return [
            'price'            => $product ? (float) $product->price : 0,
            'discount_percent' => 0,
            'price_list_id'    => null,
            'price_list_name'  => null,
            'source'           => 'default',
        ];
    }
}
