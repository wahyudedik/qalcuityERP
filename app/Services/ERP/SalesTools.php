<?php

namespace App\Services\ERP;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use Illuminate\Support\Str;
use App\Services\ERP\ReceivableTools;

class SalesTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'create_sales_order',
                'description' => 'Buat Sales Order baru. Mendukung pembayaran cash maupun tempo (kredit). '
                    . 'Gunakan untuk: "jual 500 pcs ke Toko B tempo 30 hari", '
                    . '"buat SO untuk PT Maju: produk A 100 pcs harga 5000 cash", '
                    . '"order dari Budi: kopi 10 dus 80000 tempo 14 hari".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'customer_name' => ['type' => 'string', 'description' => 'Nama customer'],
                        'items'         => [
                            'type'  => 'array',
                            'items' => [
                                'type'       => 'object',
                                'properties' => [
                                    'product_name' => ['type' => 'string', 'description' => 'Nama produk'],
                                    'quantity'     => ['type' => 'number', 'description' => 'Jumlah'],
                                    'price'        => ['type' => 'number', 'description' => 'Harga satuan (opsional, default harga jual produk)'],
                                ],
                                'required' => ['product_name', 'quantity'],
                            ],
                            'description' => 'Daftar item yang dijual',
                        ],
                        'payment_type'  => ['type' => 'string', 'description' => 'cash atau credit (tempo). Default: cash'],
                        'due_days'      => ['type' => 'integer', 'description' => 'Jatuh tempo dalam hari (wajib jika payment_type=credit, misal: 30)'],
                        'notes'         => ['type' => 'string', 'description' => 'Catatan order (opsional)'],
                        'discount'      => ['type' => 'number', 'description' => 'Diskon total dalam Rupiah (opsional)'],
                    ],
                    'required' => ['customer_name', 'items'],
                ],
            ],
            [
                'name'        => 'get_sales_summary',
                'description' => 'Tampilkan ringkasan penjualan dalam periode tertentu.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'Periode: today, this_week, this_month, last_month, atau format YYYY-MM'],
                    ],
                    'required' => ['period'],
                ],
            ],
            [
                'name'        => 'get_customer_info',
                'description' => 'Cari informasi pelanggan dan riwayat ordernya.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'customer_name' => ['type' => 'string', 'description' => 'Nama atau perusahaan pelanggan'],
                    ],
                    'required' => ['customer_name'],
                ],
            ],
            [
                'name'        => 'get_pending_orders',
                'description' => 'Tampilkan sales order yang masih pending atau dalam proses.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'status' => ['type' => 'string', 'description' => 'Filter status: pending, confirmed, processing, shipped'],
                    ],
                ],
            ],
            [
                'name'        => 'create_customer',
                'description' => 'Tambah pelanggan baru. Gunakan untuk: '
                    . '"tambah pelanggan Budi nomor 08123456789", '
                    . '"daftarkan pelanggan PT Maju email maju@email.com", '
                    . '"buat kontak pelanggan baru Siti alamat Bandung".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'name'    => ['type' => 'string', 'description' => 'Nama pelanggan'],
                        'phone'   => ['type' => 'string', 'description' => 'Nomor telepon/HP'],
                        'email'   => ['type' => 'string', 'description' => 'Alamat email (opsional)'],
                        'company' => ['type' => 'string', 'description' => 'Nama perusahaan (opsional)'],
                        'address' => ['type' => 'string', 'description' => 'Alamat lengkap (opsional)'],
                    ],
                    'required' => ['name'],
                ],
            ],
            [
                'name'        => 'update_customer',
                'description' => 'Ubah data pelanggan yang sudah ada. Gunakan untuk: '
                    . '"ubah nomor Budi jadi 08999", "update email pelanggan Siti", "nonaktifkan pelanggan PT Maju".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'customer_name' => ['type' => 'string', 'description' => 'Nama pelanggan yang ingin diubah'],
                        'new_name'      => ['type' => 'string', 'description' => 'Nama baru (opsional)'],
                        'phone'         => ['type' => 'string', 'description' => 'Nomor baru (opsional)'],
                        'email'         => ['type' => 'string', 'description' => 'Email baru (opsional)'],
                        'company'       => ['type' => 'string', 'description' => 'Perusahaan baru (opsional)'],
                        'address'       => ['type' => 'string', 'description' => 'Alamat baru (opsional)'],
                        'is_active'     => ['type' => 'boolean', 'description' => 'true = aktif, false = nonaktif'],
                    ],
                    'required' => ['customer_name'],
                ],
            ],
            [
                'name'        => 'list_customers',
                'description' => 'Tampilkan daftar pelanggan. Gunakan untuk: "daftar pelanggan", "siapa saja pelanggan kita", "cari pelanggan Budi".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'search' => ['type' => 'string', 'description' => 'Kata kunci nama/perusahaan (opsional)'],
                    ],
                ],
            ],
            [
                'name'        => 'update_order_status',
                'description' => 'Update status sales order. Gunakan untuk: '
                    . '"order SO-001 sudah dikirim", "konfirmasi SO-ABC", "SO-XYZ sudah lunas", '
                    . '"batalkan order Budi", "tandai SO-001 selesai".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'order_number'   => ['type' => 'string', 'description' => 'Nomor sales order (misal: SO-001)'],
                        'status'         => ['type' => 'string', 'description' => 'Status baru: confirmed, processing, shipped, delivered, completed, cancelled'],
                        'notes'          => ['type' => 'string', 'description' => 'Catatan tambahan (opsional)'],
                        'delivery_date'  => ['type' => 'string', 'description' => 'Tanggal pengiriman YYYY-MM-DD (opsional)'],
                    ],
                    'required' => ['order_number', 'status'],
                ],
            ],
            [
                'name'        => 'create_quotation',
                'description' => 'Buat penawaran harga (quotation) untuk pelanggan. Gunakan untuk: '
                    . '"buat penawaran untuk Budi: kopi 10 dus harga 80000/dus", '
                    . '"buat quotation PT Maju untuk produk A 5 pcs 50000", '
                    . '"kirim penawaran ke Siti: teh 20 karton 45000".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'customer_name' => ['type' => 'string', 'description' => 'Nama pelanggan'],
                        'items'         => [
                            'type'  => 'array',
                            'items' => [
                                'type'       => 'object',
                                'properties' => [
                                    'product_name' => ['type' => 'string', 'description' => 'Nama produk'],
                                    'quantity'     => ['type' => 'number', 'description' => 'Jumlah'],
                                    'price'        => ['type' => 'number', 'description' => 'Harga satuan'],
                                    'description'  => ['type' => 'string', 'description' => 'Deskripsi item jika produk tidak ada di sistem'],
                                ],
                                'required' => ['quantity', 'price'],
                            ],
                            'description' => 'Daftar item penawaran',
                        ],
                        'valid_days'    => ['type' => 'integer', 'description' => 'Berlaku berapa hari (default: 7)'],
                        'notes'         => ['type' => 'string', 'description' => 'Catatan penawaran (opsional)'],
                        'discount'      => ['type' => 'number', 'description' => 'Diskon total dalam Rupiah (opsional)'],
                    ],
                    'required' => ['customer_name', 'items'],
                ],
            ],
        ];
    }

    // ─── Executors ────────────────────────────────────────────────

    public function createSalesOrder(array $args): array
    {
        $customer = Customer::where('tenant_id', $this->tenantId)
            ->where(fn($q) => $q->where('name', 'like', "%{$args['customer_name']}%")
                ->orWhere('company', 'like', "%{$args['customer_name']}%"))
            ->first();

        if (!$customer) {
            return ['status' => 'error', 'message' => "Customer '{$args['customer_name']}' tidak ditemukan. Tambahkan dulu dengan create_customer."];
        }

        $subtotal  = 0;
        $itemsData = [];

        foreach ($args['items'] as $item) {
            $product = \App\Models\Product::where('tenant_id', $this->tenantId)
                ->where(fn($q) => $q->where('name', 'like', "%{$item['product_name']}%")
                    ->orWhere('sku', $item['product_name']))
                ->first();

            if (!$product) {
                return ['status' => 'error', 'message' => "Produk '{$item['product_name']}' tidak ditemukan."];
            }

            $price    = $item['price'] ?? $product->price_sell;
            $qty      = $item['quantity'];
            $total    = $price * $qty;
            $subtotal += $total;

            $itemsData[] = [
                'product_id' => $product->id,
                'quantity'   => $qty,
                'price'      => $price,
                'discount'   => 0,
                'total'      => $total,
            ];
        }

        $discount   = $args['discount'] ?? 0;
        $grandTotal = $subtotal - $discount;
        $paymentType = $args['payment_type'] ?? 'cash';
        $dueDays    = $args['due_days'] ?? null;
        $dueDate    = $paymentType === 'credit' && $dueDays
            ? today()->addDays($dueDays)->toDateString()
            : null;

        $so = SalesOrder::create([
            'tenant_id'    => $this->tenantId,
            'customer_id'  => $customer->id,
            'user_id'      => $this->userId,
            'number'       => 'SO-' . strtoupper(Str::random(8)),
            'status'       => 'confirmed',
            'date'         => today(),
            'subtotal'     => $subtotal,
            'discount'     => $discount,
            'tax'          => 0,
            'total'        => $grandTotal,
            'payment_type' => $paymentType,
            'due_date'     => $dueDate,
            'notes'        => $args['notes'] ?? null,
        ]);

        $so->items()->createMany($itemsData);

        // Jika kredit, buat Invoice otomatis
        $invoiceMsg = '';
        if ($paymentType === 'credit' && $dueDate) {
            $invoice = ReceivableTools::createInvoiceFromOrder(
                $this->tenantId,
                $so->id,
                $customer->id,
                $grandTotal,
                $dueDate
            );
            $invoiceMsg = "\nInvoice **{$invoice->number}** dibuat — jatuh tempo: **" . today()->addDays($dueDays)->format('d M Y') . "** ({$dueDays} hari).";
        }

        $itemSummary = collect($itemsData)->zip(collect($args['items'])->pluck('product_name'))->map(fn($pair) =>
            "- {$pair[1]}: {$pair[0]['quantity']} × Rp " . number_format($pair[0]['price'], 0, ',', '.') .
            " = Rp " . number_format($pair[0]['total'], 0, ',', '.')
        )->implode("\n");

        return [
            'status'  => 'success',
            'message' => "Sales Order **{$so->number}** untuk **{$customer->name}** berhasil dibuat.\n\n"
                . "{$itemSummary}\n\n"
                . "**Total: Rp " . number_format($grandTotal, 0, ',', '.') . "**"
                . ($paymentType === 'credit' ? " (Tempo)" : " (Cash)")
                . $invoiceMsg,
            'data' => [
                'so_number'    => $so->number,
                'total'        => $grandTotal,
                'payment_type' => $paymentType,
                'due_date'     => $dueDate,
            ],
        ];
    }

    public function getSalesSummary(array $args): array
    {
        $query = SalesOrder::where('tenant_id', $this->tenantId)
            ->whereNotIn('status', ['cancelled']);

        $query = $this->applyPeriod($query, $args['period']);

        $total   = $query->sum('total');
        $count   = $query->count();
        $average = $count > 0 ? $total / $count : 0;

        return [
            'status' => 'success',
            'data'   => [
                'period'        => $args['period'],
                'total_orders'  => $count,
                'total_revenue' => 'Rp ' . number_format($total, 0, ',', '.'),
                'average_order' => 'Rp ' . number_format($average, 0, ',', '.'),
            ],
        ];
    }

    public function getCustomerInfo(array $args): array
    {
        $customer = Customer::where('tenant_id', $this->tenantId)
            ->where(fn($q) => $q->where('name', 'like', "%{$args['customer_name']}%")
                ->orWhere('company', 'like', "%{$args['customer_name']}%"))
            ->first();

        if (!$customer) {
            return ['status' => 'not_found', 'message' => "Pelanggan '{$args['customer_name']}' tidak ditemukan."];
        }

        $totalOrders   = SalesOrder::where('customer_id', $customer->id)->count();
        $totalRevenue  = SalesOrder::where('customer_id', $customer->id)->whereNotIn('status', ['cancelled'])->sum('total');
        $lastOrder     = SalesOrder::where('customer_id', $customer->id)->latest()->first();

        return [
            'status' => 'success',
            'data'   => [
                'name'          => $customer->name,
                'company'       => $customer->company,
                'email'         => $customer->email,
                'phone'         => $customer->phone,
                'total_orders'  => $totalOrders,
                'total_revenue' => 'Rp ' . number_format($totalRevenue, 0, ',', '.'),
                'last_order'    => $lastOrder?->date?->format('d M Y'),
                'last_status'   => $lastOrder?->status,
            ],
        ];
    }

    public function getPendingOrders(array $args): array
    {
        $statuses = ['pending', 'confirmed', 'processing', 'shipped'];
        if (!empty($args['status'])) {
            $statuses = [$args['status']];
        }

        $orders = SalesOrder::where('tenant_id', $this->tenantId)
            ->whereIn('status', $statuses)
            ->with('customer')
            ->latest()
            ->limit(20)
            ->get();

        if ($orders->isEmpty()) {
            return ['status' => 'success', 'message' => 'Tidak ada order yang pending.'];
        }

        return [
            'status' => 'success',
            'data'   => $orders->map(fn($o) => [
                'number'   => $o->number,
                'customer' => $o->customer?->name ?? '(Walk-in)',
                'date'     => $o->date->format('d M Y'),
                'total'    => 'Rp ' . number_format($o->total, 0, ',', '.'),
                'status'   => $o->status,
            ])->toArray(),
        ];
    }

    public function updateOrderStatus(array $args): array
    {
        $order = SalesOrder::where('tenant_id', $this->tenantId)
            ->where('number', $args['order_number'])
            ->first();

        if (!$order) {
            // Try partial match
            $order = SalesOrder::where('tenant_id', $this->tenantId)
                ->where('number', 'like', "%{$args['order_number']}%")
                ->latest()
                ->first();
        }

        if (!$order) {
            return ['status' => 'not_found', 'message' => "Sales order '{$args['order_number']}' tidak ditemukan."];
        }

        $validTransitions = [
            'pending'    => ['confirmed', 'cancelled'],
            'confirmed'  => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped'    => ['delivered', 'completed'],
            'delivered'  => ['completed'],
        ];

        $currentStatus = $order->status;
        $newStatus     = $args['status'];

        // Warn if invalid transition but still allow it (AI flexibility)
        $allowed = $validTransitions[$currentStatus] ?? [];
        $warning = !in_array($newStatus, $allowed) && !in_array($currentStatus, ['completed', 'cancelled'])
            ? " (catatan: transisi dari {$currentStatus} ke {$newStatus} tidak biasa)"
            : '';

        $updates = ['status' => $newStatus];
        if (!empty($args['notes'])) {
            $updates['notes'] = $args['notes'];
        }
        if (!empty($args['delivery_date'])) {
            $updates['delivery_date'] = $args['delivery_date'];
        }

        $order->update($updates);

        $statusLabel = match ($newStatus) {
            'confirmed'  => 'Dikonfirmasi',
            'processing' => 'Diproses',
            'shipped'    => 'Dikirim',
            'delivered'  => 'Diterima',
            'completed'  => 'Selesai',
            'cancelled'  => 'Dibatalkan',
            default      => $newStatus,
        };

        return [
            'status'  => 'success',
            'message' => "Order **{$order->number}** berhasil diupdate ke status **{$statusLabel}**.{$warning}",
            'data'    => [
                'number'     => $order->number,
                'old_status' => $currentStatus,
                'new_status' => $newStatus,
                'total'      => 'Rp ' . number_format($order->total, 0, ',', '.'),
            ],
        ];
    }

    public function createQuotation(array $args): array
    {
        // Cari pelanggan
        $customer = Customer::where('tenant_id', $this->tenantId)
            ->where(fn($q) => $q->where('name', 'like', "%{$args['customer_name']}%")
                ->orWhere('company', 'like', "%{$args['customer_name']}%"))
            ->first();

        if (!$customer) {
            return ['status' => 'error', 'message' => "Pelanggan '{$args['customer_name']}' tidak ditemukan. Tambahkan dulu dengan create_customer."];
        }

        $subtotal  = 0;
        $itemsData = [];

        foreach ($args['items'] as $item) {
            $product = null;
            if (!empty($item['product_name'])) {
                $product = Product::where('tenant_id', $this->tenantId)
                    ->where('name', 'like', "%{$item['product_name']}%")
                    ->first();
            }

            $price    = $item['price'];
            $qty      = $item['quantity'];
            $total    = $price * $qty;
            $subtotal += $total;

            $itemsData[] = [
                'product_id'  => $product?->id,
                'description' => $item['description'] ?? ($product?->name ?? $item['product_name'] ?? 'Item'),
                'quantity'    => $qty,
                'price'       => $price,
                'discount'    => 0,
                'total'       => $total,
            ];
        }

        $discount = $args['discount'] ?? 0;
        $grandTotal = $subtotal - $discount;

        $number = 'QT-' . strtoupper(Str::random(8));

        $quotation = Quotation::create([
            'tenant_id'   => $this->tenantId,
            'customer_id' => $customer->id,
            'user_id'     => $this->userId,
            'number'      => $number,
            'status'      => 'draft',
            'date'        => today(),
            'valid_until' => today()->addDays($args['valid_days'] ?? 7),
            'subtotal'    => $subtotal,
            'discount'    => $discount,
            'tax'         => 0,
            'total'       => $grandTotal,
            'notes'       => $args['notes'] ?? null,
        ]);

        $quotation->items()->createMany($itemsData);

        $itemSummary = collect($itemsData)->map(fn($i) =>
            "- {$i['description']}: {$i['quantity']} × Rp " . number_format($i['price'], 0, ',', '.') .
            " = Rp " . number_format($i['total'], 0, ',', '.')
        )->implode("\n");

        return [
            'status'  => 'success',
            'message' => "Penawaran **{$number}** untuk **{$customer->name}** berhasil dibuat.\n\n"
                . "{$itemSummary}\n\n"
                . "**Total: Rp " . number_format($grandTotal, 0, ',', '.') . "**"
                . ($discount > 0 ? " (diskon Rp " . number_format($discount, 0, ',', '.') . ")" : "")
                . "\nBerlaku hingga: " . today()->addDays($args['valid_days'] ?? 7)->format('d M Y') . ".",
            'data'    => ['number' => $number, 'total' => $grandTotal],
        ];
    }

    public function createCustomer(array $args): array
    {
        $existing = Customer::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['name']}%")
            ->first();

        if ($existing) {
            return ['status' => 'error', 'message' => "Pelanggan dengan nama '{$args['name']}' sudah ada."];
        }

        $customer = Customer::create([
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
            'message' => "Pelanggan **{$customer->name}** berhasil ditambahkan." .
                ($customer->phone ? " Telepon: {$customer->phone}." : '') .
                ($customer->email ? " Email: {$customer->email}." : ''),
        ];
    }

    public function updateCustomer(array $args): array
    {
        $customer = Customer::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['customer_name']}%")
            ->first();

        if (!$customer) {
            return ['status' => 'not_found', 'message' => "Pelanggan '{$args['customer_name']}' tidak ditemukan."];
        }

        $updates = array_filter([
            'name'      => $args['new_name'] ?? null,
            'phone'     => $args['phone'] ?? null,
            'email'     => $args['email'] ?? null,
            'company'   => $args['company'] ?? null,
            'address'   => $args['address'] ?? null,
            'is_active' => $args['is_active'] ?? null,
        ], fn($v) => $v !== null);

        $customer->update($updates);

        return [
            'status'  => 'success',
            'message' => "Data pelanggan **{$customer->name}** berhasil diperbarui.",
        ];
    }

    public function listCustomers(array $args): array
    {
        $query = Customer::where('tenant_id', $this->tenantId)->where('is_active', true);

        if (!empty($args['search'])) {
            $s = $args['search'];
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('company', 'like', "%$s%"));
        }

        $customers = $query->orderBy('name')->limit(30)->get();

        if ($customers->isEmpty()) {
            return ['status' => 'success', 'message' => 'Belum ada pelanggan yang terdaftar.'];
        }

        return [
            'status' => 'success',
            'data'   => $customers->map(fn($c) => [
                'nama'       => $c->name,
                'perusahaan' => $c->company ?? '-',
                'telepon'    => $c->phone ?? '-',
                'email'      => $c->email ?? '-',
            ])->toArray(),
        ];
    }

    protected function applyPeriod($query, string $period)
    {
        return match ($period) {
            'today'      => $query->whereDate('date', today()),
            'this_week'  => $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]),
            'this_month' => $query->whereMonth('date', now()->month)->whereYear('date', now()->year),
            'last_month' => $query->whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year),
            default      => strlen($period) === 7
                ? $query->whereYear('date', substr($period, 0, 4))->whereMonth('date', substr($period, 5, 2))
                : $query,
        };
    }
}
