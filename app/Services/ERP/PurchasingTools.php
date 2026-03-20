<?php

namespace App\Services\ERP;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Support\Str;
use App\Services\ERP\ReceivableTools;

class PurchasingTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'create_purchase_order',
                'description' => 'Buat Purchase Order (PO) ke supplier untuk produk tertentu.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'supplier_name' => ['type' => 'string', 'description' => 'Nama supplier'],
                        'warehouse'     => ['type' => 'string', 'description' => 'Nama gudang tujuan'],
                        'items'         => [
                            'type'  => 'array',
                            'items' => [
                                'type'       => 'object',
                                'properties' => [
                                    'product_name' => ['type' => 'string'],
                                    'quantity'     => ['type' => 'integer'],
                                    'price'        => ['type' => 'number'],
                                ],
                                'required' => ['product_name', 'quantity'],
                            ],
                            'description' => 'Daftar produk yang dipesan',
                        ],
                        'notes'         => ['type' => 'string', 'description' => 'Catatan PO'],
                        'expected_date' => ['type' => 'string', 'description' => 'Tanggal estimasi terima (YYYY-MM-DD)'],
                        'payment_type'  => ['type' => 'string', 'description' => 'cash atau credit (tempo). Default: cash'],
                        'due_days'      => ['type' => 'integer', 'description' => 'Jatuh tempo pembayaran dalam hari (jika payment_type=credit)'],
                    ],
                    'required' => ['supplier_name', 'warehouse', 'items'],
                ],
            ],
            [
                'name'        => 'get_supplier_info',
                'description' => 'Cari informasi supplier.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'supplier_name' => ['type' => 'string', 'description' => 'Nama supplier'],
                    ],
                    'required' => ['supplier_name'],
                ],
            ],
            [
                'name'        => 'create_supplier',
                'description' => 'Tambah supplier/pemasok baru. Gunakan untuk: '
                    . '"tambah supplier PT Sumber Jaya", '
                    . '"daftarkan pemasok Toko Bahan email toko@email.com", '
                    . '"buat kontak supplier baru nomor 08123".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'name'    => ['type' => 'string', 'description' => 'Nama supplier'],
                        'phone'   => ['type' => 'string', 'description' => 'Nomor telepon (opsional)'],
                        'email'   => ['type' => 'string', 'description' => 'Alamat email (opsional)'],
                        'company' => ['type' => 'string', 'description' => 'Nama perusahaan (opsional)'],
                        'address' => ['type' => 'string', 'description' => 'Alamat (opsional)'],
                    ],
                    'required' => ['name'],
                ],
            ],
            [
                'name'        => 'update_supplier',
                'description' => 'Ubah data supplier yang sudah ada. Gunakan untuk: '
                    . '"ubah nomor supplier PT Maju", "update email pemasok Toko Bahan", "nonaktifkan supplier".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'supplier_name' => ['type' => 'string', 'description' => 'Nama supplier yang ingin diubah'],
                        'new_name'      => ['type' => 'string', 'description' => 'Nama baru (opsional)'],
                        'phone'         => ['type' => 'string', 'description' => 'Nomor baru (opsional)'],
                        'email'         => ['type' => 'string', 'description' => 'Email baru (opsional)'],
                        'company'       => ['type' => 'string', 'description' => 'Perusahaan baru (opsional)'],
                        'address'       => ['type' => 'string', 'description' => 'Alamat baru (opsional)'],
                        'is_active'     => ['type' => 'boolean', 'description' => 'true = aktif, false = nonaktif'],
                    ],
                    'required' => ['supplier_name'],
                ],
            ],
            [
                'name'        => 'list_suppliers',
                'description' => 'Tampilkan daftar supplier. Gunakan untuk: "daftar supplier", "siapa saja pemasok kita", "cari supplier Maju".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'search' => ['type' => 'string', 'description' => 'Kata kunci nama/perusahaan (opsional)'],
                    ],
                ],
            ],
            [
                'name'        => 'auto_reorder',
                'description' => 'Buat PO otomatis untuk semua produk yang stoknya di bawah minimum.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'supplier_name' => ['type' => 'string', 'description' => 'Nama supplier untuk PO'],
                        'warehouse'     => ['type' => 'string', 'description' => 'Nama gudang'],
                    ],
                    'required' => ['supplier_name', 'warehouse'],
                ],
            ],
        ];
    }

    public function createSupplier(array $args): array
    {
        $existing = Supplier::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['name']}%")
            ->first();

        if ($existing) {
            return ['status' => 'error', 'message' => "Supplier dengan nama '{$args['name']}' sudah ada."];
        }

        $supplier = Supplier::create([
            'tenant_id' => $this->tenantId,
            'name'      => $args['name'],
            'phone'     => $args['phone'] ?? null,
            'email'     => $args['email'] ?? null,
            'company'   => $args['company'] ?? null,
            'address'   => $args['address'] ?? null,
            'is_active' => true,
        ]);

        return [
            'status'  => 'success',
            'message' => "Supplier **{$supplier->name}** berhasil ditambahkan." .
                ($supplier->phone ? " Telepon: {$supplier->phone}." : '') .
                ($supplier->email ? " Email: {$supplier->email}." : ''),
        ];
    }

    public function updateSupplier(array $args): array
    {
        $supplier = Supplier::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['supplier_name']}%")
            ->first();

        if (!$supplier) {
            return ['status' => 'not_found', 'message' => "Supplier '{$args['supplier_name']}' tidak ditemukan."];
        }

        $updates = array_filter([
            'name'      => $args['new_name'] ?? null,
            'phone'     => $args['phone'] ?? null,
            'email'     => $args['email'] ?? null,
            'company'   => $args['company'] ?? null,
            'address'   => $args['address'] ?? null,
            'is_active' => $args['is_active'] ?? null,
        ], fn($v) => $v !== null);

        $supplier->update($updates);

        return [
            'status'  => 'success',
            'message' => "Data supplier **{$supplier->name}** berhasil diperbarui.",
        ];
    }

    public function listSuppliers(array $args): array
    {
        $query = Supplier::where('tenant_id', $this->tenantId)->where('is_active', true);

        if (!empty($args['search'])) {
            $s = $args['search'];
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('company', 'like', "%$s%"));
        }

        $suppliers = $query->orderBy('name')->limit(30)->get();

        if ($suppliers->isEmpty()) {
            return ['status' => 'success', 'message' => 'Belum ada supplier yang terdaftar.'];
        }

        return [
            'status' => 'success',
            'data'   => $suppliers->map(fn($s) => [
                'nama'       => $s->name,
                'perusahaan' => $s->company ?? '-',
                'telepon'    => $s->phone ?? '-',
                'email'      => $s->email ?? '-',
            ])->toArray(),
        ];
    }

    public function createPurchaseOrder(array $args): array
    {
        $supplier = Supplier::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['supplier_name']}%")
            ->first();

        if (!$supplier) {
            return ['status' => 'error', 'message' => "Supplier '{$args['supplier_name']}' tidak ditemukan."];
        }

        $warehouse = Warehouse::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['warehouse']}%")
            ->first();

        if (!$warehouse) {
            return ['status' => 'error', 'message' => "Gudang '{$args['warehouse']}' tidak ditemukan."];
        }

        $subtotal = 0;
        $itemsData = [];

        foreach ($args['items'] as $item) {
            $product = Product::where('tenant_id', $this->tenantId)
                ->where(fn($q) => $q->where('name', 'like', "%{$item['product_name']}%")
                    ->orWhere('sku', $item['product_name']))
                ->first();

            if (!$product) {
                return ['status' => 'error', 'message' => "Produk '{$item['product_name']}' tidak ditemukan."];
            }

            $price = $item['price'] ?? $product->price_buy;
            $total = $price * $item['quantity'];
            $subtotal += $total;

            $itemsData[] = [
                'product_id'        => $product->id,
                'quantity_ordered'  => $item['quantity'],
                'quantity_received' => 0,
                'price'             => $price,
                'total'             => $total,
            ];
        }

        $po = PurchaseOrder::create([
            'tenant_id'     => $this->tenantId,
            'supplier_id'   => $supplier->id,
            'user_id'       => $this->userId,
            'warehouse_id'  => $warehouse->id,
            'number'        => 'PO-' . strtoupper(Str::random(8)),
            'status'        => 'draft',
            'date'          => today(),
            'expected_date' => $args['expected_date'] ?? null,
            'subtotal'      => $subtotal,
            'total'         => $subtotal,
            'payment_type'  => $args['payment_type'] ?? 'cash',
            'due_date'      => ($args['payment_type'] ?? 'cash') === 'credit' && !empty($args['due_days'])
                ? today()->addDays($args['due_days'])->toDateString()
                : null,
            'notes'         => $args['notes'] ?? null,
        ]);

        $po->items()->createMany($itemsData);

        // Jika kredit, buat Payable otomatis
        $payableMsg = '';
        if (($args['payment_type'] ?? 'cash') === 'credit' && !empty($args['due_days']) && $po->due_date) {
            $payable = ReceivableTools::createPayableFromOrder(
                $this->tenantId,
                $po->id,
                $supplier->id,
                $subtotal,
                $po->due_date->toDateString()
            );
            $payableMsg = " Hutang **{$payable->number}** dibuat — jatuh tempo: **{$po->due_date->format('d M Y')}** ({$args['due_days']} hari).";
        }

        $paymentLabel = ($args['payment_type'] ?? 'cash') === 'credit' ? ' (Tempo)' : ' (Cash)';

        return [
            'status'    => 'success',
            'message'   => "PO **{$po->number}** berhasil dibuat untuk **{$supplier->name}**. Total: Rp " . number_format($subtotal, 0, ',', '.') . $paymentLabel . $payableMsg,
            'po_number' => $po->number,
        ];
    }

    public function getSupplierInfo(array $args): array
    {
        $supplier = Supplier::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['supplier_name']}%")
            ->first();

        if (!$supplier) {
            return ['status' => 'not_found', 'message' => "Supplier '{$args['supplier_name']}' tidak ditemukan."];
        }

        $totalPO    = PurchaseOrder::where('supplier_id', $supplier->id)->count();
        $totalSpend = PurchaseOrder::where('supplier_id', $supplier->id)->whereNotIn('status', ['cancelled'])->sum('total');

        return [
            'status' => 'success',
            'data'   => [
                'name'        => $supplier->name,
                'email'       => $supplier->email,
                'phone'       => $supplier->phone,
                'total_po'    => $totalPO,
                'total_spend' => 'Rp ' . number_format($totalSpend, 0, ',', '.'),
            ],
        ];
    }

    public function autoReorder(array $args): array
    {
        $lowStocks = ProductStock::with(['product', 'warehouse'])
            ->whereHas('product', fn($q) => $q->where('tenant_id', $this->tenantId))
            ->whereHas('warehouse', fn($q) => $q->where('name', 'like', "%{$args['warehouse']}%"))
            ->whereColumn('quantity', '<=', 'products.stock_min')
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->select('product_stocks.*')
            ->get();

        if ($lowStocks->isEmpty()) {
            return ['status' => 'success', 'message' => 'Tidak ada produk yang perlu di-reorder.'];
        }

        $items = $lowStocks->map(fn($s) => [
            'product_name' => $s->product->name,
            'quantity'     => max(1, $s->product->stock_min * 2 - $s->quantity),
        ])->toArray();

        return $this->createPurchaseOrder([
            'supplier_name' => $args['supplier_name'],
            'warehouse'     => $args['warehouse'],
            'items'         => $items,
            'notes'         => 'Auto-reorder dari sistem',
        ]);
    }
}
