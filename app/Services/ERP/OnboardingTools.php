<?php

namespace App\Services\ERP;

use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\Warehouse;

class OnboardingTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    // ─── Industry Presets ─────────────────────────────────────────

    public const INDUSTRY_PRESETS = [
        'fnb' => [
            'label'      => 'F&B / Kuliner',
            'warehouses' => ['Dapur Utama', 'Gudang Bahan Baku'],
            'categories' => ['Bahan Baku', 'Gas & Listrik', 'Gaji Karyawan', 'Sewa Tempat', 'Peralatan Dapur', 'Kemasan', 'Marketing'],
            'products'   => [
                ['name' => 'Kopi Hitam',    'unit' => 'gelas',  'category' => 'Minuman', 'price_sell' => 8000],
                ['name' => 'Kopi Susu',     'unit' => 'gelas',  'category' => 'Minuman', 'price_sell' => 12000],
                ['name' => 'Teh Manis',     'unit' => 'gelas',  'category' => 'Minuman', 'price_sell' => 6000],
                ['name' => 'Mie Ayam',      'unit' => 'porsi',  'category' => 'Makanan', 'price_sell' => 15000],
                ['name' => 'Nasi Goreng',   'unit' => 'porsi',  'category' => 'Makanan', 'price_sell' => 18000],
            ],
            'shortcuts'  => [
                '"jual kopi 2 gelas" → create_quick_sale',
                '"produksi 50 gelas kopi susu" → produce_with_recipe',
                '"bahan baku menipis?" → get_low_stock',
                '"laba menu kopi berapa?" → get_recipe_cost',
                '"rekap omzet hari ini" → get_pos_summary',
            ],
        ],
        'retail' => [
            'label'      => 'Retail / Toko',
            'warehouses' => ['Toko Utama', 'Gudang Stok'],
            'categories' => ['Pembelian Barang', 'Gaji Karyawan', 'Sewa Toko', 'Listrik & Air', 'Kantong & Kemasan', 'Promosi'],
            'products'   => [
                ['name' => 'Produk A', 'unit' => 'pcs', 'category' => 'Umum', 'price_sell' => 10000],
                ['name' => 'Produk B', 'unit' => 'pcs', 'category' => 'Umum', 'price_sell' => 25000],
                ['name' => 'Produk C', 'unit' => 'pcs', 'category' => 'Umum', 'price_sell' => 50000],
            ],
            'shortcuts'  => [
                '"jual [produk] [qty]" → create_quick_sale',
                '"stok menipis?" → get_low_stock',
                '"tambah stok [produk] [qty]" → add_stock',
                '"buat PO ke supplier X" → create_purchase_order',
                '"laporan penjualan hari ini" → get_sales_summary',
            ],
        ],
        'manufacture' => [
            'label'      => 'Manufaktur / Konveksi',
            'warehouses' => ['Gudang Bahan Baku', 'Gudang Produk Jadi', 'Gudang Reject'],
            'categories' => ['Bahan Baku', 'Upah Produksi', 'Overhead Pabrik', 'Listrik Mesin', 'Maintenance', 'Packaging', 'Gaji Staff'],
            'products'   => [
                ['name' => 'Bahan Baku Utama', 'unit' => 'kg',  'category' => 'Bahan Baku', 'price_sell' => 0],
                ['name' => 'Produk Jadi S',    'unit' => 'pcs', 'category' => 'Produk Jadi', 'price_sell' => 50000],
                ['name' => 'Produk Jadi M',    'unit' => 'pcs', 'category' => 'Produk Jadi', 'price_sell' => 55000],
                ['name' => 'Produk Jadi L',    'unit' => 'pcs', 'category' => 'Produk Jadi', 'price_sell' => 60000],
            ],
            'shortcuts'  => [
                '"buat WO 1000 pcs produk X" → create_work_order',
                '"mulai WO-XXX" → update_work_order_status',
                '"catat hasil produksi WO-XXX" → record_production_output',
                '"progress produksi hari ini" → get_production_summary',
                '"buat resep/BOM produk X" → create_recipe',
            ],
        ],
        'distributor' => [
            'label'      => 'Distributor / Grosir',
            'warehouses' => ['Gudang Pusat', 'Gudang Cabang 1', 'Gudang Cabang 2'],
            'categories' => ['Pembelian Barang', 'Ongkos Kirim', 'Gaji Driver', 'Sewa Gudang', 'Asuransi', 'Operasional'],
            'products'   => [
                ['name' => 'Produk Distribusi A', 'unit' => 'karton', 'category' => 'Distribusi', 'price_sell' => 100000],
                ['name' => 'Produk Distribusi B', 'unit' => 'karton', 'category' => 'Distribusi', 'price_sell' => 150000],
            ],
            'shortcuts'  => [
                '"stok gudang A berapa?" → get_warehouse_stock',
                '"transfer 100 karton dari gudang pusat ke cabang 1" → transfer_stock',
                '"buat SO ke toko X tempo 30 hari" → create_sales_order',
                '"tagihan yang belum dibayar" → get_receivables',
                '"buat PO ke supplier" → create_purchase_order',
            ],
        ],
        'construction' => [
            'label'      => 'Konstruksi / Kontraktor',
            'warehouses' => ['Gudang Material', 'Lokasi Proyek A'],
            'categories' => ['Material Bangunan', 'Upah Tukang', 'Sewa Alat Berat', 'Transportasi', 'Overhead Proyek', 'Administrasi'],
            'products'   => [
                ['name' => 'Semen',    'unit' => 'sak',  'category' => 'Material', 'price_sell' => 0],
                ['name' => 'Besi Beton', 'unit' => 'batang', 'category' => 'Material', 'price_sell' => 0],
                ['name' => 'Pasir',    'unit' => 'm3',   'category' => 'Material', 'price_sell' => 0],
                ['name' => 'Bata Merah', 'unit' => 'pcs', 'category' => 'Material', 'price_sell' => 0],
            ],
            'shortcuts'  => [
                '"buat proyek [nama] budget [angka]" → create_project',
                '"catat pengeluaran semen 5 juta proyek X" → add_project_expense',
                '"progress proyek X berapa?" → get_project_status',
                '"catat kerja 8 jam proyek X" → log_timesheet',
                '"proyek mana yang over budget?" → get_project_financial_report',
            ],
        ],
        'service' => [
            'label'      => 'Jasa / Konsultan',
            'warehouses' => ['Kantor Utama'],
            'categories' => ['Gaji Karyawan', 'Sewa Kantor', 'Listrik & Internet', 'Transportasi', 'Software & Tools', 'Marketing', 'Pelatihan'],
            'products'   => [
                ['name' => 'Jasa Konsultasi', 'unit' => 'jam',    'category' => 'Jasa', 'price_sell' => 500000],
                ['name' => 'Jasa Desain',     'unit' => 'proyek', 'category' => 'Jasa', 'price_sell' => 2000000],
                ['name' => 'Jasa Instalasi',  'unit' => 'unit',   'category' => 'Jasa', 'price_sell' => 1500000],
            ],
            'shortcuts'  => [
                '"buat proyek [nama] untuk client X" → create_project',
                '"log 5 jam kerja proyek X" → log_timesheet',
                '"buat invoice untuk client X" → create_sales_order (payment_type=credit)',
                '"laporan proyek aktif" → get_project_summary',
                '"piutang yang belum dibayar" → get_receivables',
            ],
        ],
        'agriculture' => [
            'label'      => 'Pertanian / Perkebunan',
            'warehouses' => ['Gudang Panen', 'Gudang Pupuk & Pestisida'],
            'categories' => ['Bibit & Pupuk', 'Pestisida', 'Upah Panen', 'Sewa Lahan', 'Irigasi', 'Transportasi', 'Peralatan', 'Olah Tanah'],
            'products'   => [
                ['name' => 'Hasil Panen Utama', 'unit' => 'kg',   'category' => 'Hasil Panen', 'price_sell' => 5000],
                ['name' => 'Pupuk Urea',        'unit' => 'kg',   'category' => 'Input',       'price_sell' => 0],
                ['name' => 'Pupuk NPK',         'unit' => 'kg',   'category' => 'Input',       'price_sell' => 0],
                ['name' => 'Pestisida',         'unit' => 'liter','category' => 'Input',       'price_sell' => 0],
            ],
            'shortcuts'  => [
                '"tambah lahan A1 sawah 2 hektar" → create_farm_plot',
                '"daftar lahan" atau "status semua blok" → get_farm_plots',
                '"blok A1 sudah ditanam padi" → update_plot_status',
                '"pupuk urea 50 kg di blok A1 biaya 200 ribu" → record_farm_activity',
                '"panen 500 kg padi dari lahan B2 grade A" → log_harvest',
                '"jual 1 ton ke pengepul X" → create_sales_order',
                '"lahan mana yang siap panen?" → get_farm_plots status=ready_harvest',
                '"biaya per lahan" → get_farm_cost_analysis',
            ],
        ],
        'livestock' => [
            'label'      => 'Peternakan',
            'warehouses' => ['Gudang Pakan', 'Gudang Obat & Vaksin'],
            'categories' => ['Pakan Ternak', 'Obat & Vaksin', 'Upah Pekerja', 'Sewa Kandang', 'Peralatan Kandang', 'Transportasi', 'DOC / Bibit'],
            'products'   => [
                ['name' => 'Pakan Starter',  'unit' => 'kg',   'category' => 'Pakan',  'price_sell' => 0],
                ['name' => 'Pakan Grower',   'unit' => 'kg',   'category' => 'Pakan',  'price_sell' => 0],
                ['name' => 'Pakan Finisher', 'unit' => 'kg',   'category' => 'Pakan',  'price_sell' => 0],
                ['name' => 'Vaksin ND-IB',   'unit' => 'dosis','category' => 'Vaksin', 'price_sell' => 0],
            ],
            'shortcuts'  => [
                '"masukkan 1000 DOC ayam broiler ke kandang A" → add_livestock',
                '"daftar ternak" atau "populasi ayam" → get_livestock',
                '"ayam mati 15 ekor di FLK-001" → record_livestock_movement type=death',
                '"kasih pakan 50 kg starter ke FLK-001" → record_feed',
                '"FCR ayam FLK-001" → get_fcr',
                '"jual 200 ekor ayam harga 10 juta" → record_livestock_movement type=sold',
                '"kesehatan ternak FLK-001" → get_livestock_health',
                '"jadwal vaksin ayam" → get_livestock_health',
            ],
        ],
    ];

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'setup_business',
                'description' => 'Setup awal bisnis sekaligus: buat gudang utama, daftarkan produk-produk awal, dan buat kategori pengeluaran dasar. '
                    . 'Gunakan untuk: '
                    . '"setup bisnis warung kopi: produk kopi, teh, snack", '
                    . '"inisialisasi toko saya dengan produk A, B, C", '
                    . '"setup awal bisnis kuliner saya".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'business_name'  => ['type' => 'string', 'description' => 'Nama bisnis atau toko'],
                        'warehouse_name' => ['type' => 'string', 'description' => 'Nama gudang utama (opsional, default: nama bisnis)'],
                        'products'       => [
                            'type'  => 'array',
                            'items' => [
                                'type'       => 'object',
                                'properties' => [
                                    'name'       => ['type' => 'string'],
                                    'price_sell' => ['type' => 'number'],
                                    'unit'       => ['type' => 'string'],
                                    'category'   => ['type' => 'string'],
                                ],
                                'required' => ['name'],
                            ],
                            'description' => 'Daftar produk awal (opsional)',
                        ],
                        'expense_categories' => [
                            'type'  => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Kategori pengeluaran (opsional, default: Bahan Baku, Operasional, Gaji)',
                        ],
                    ],
                    'required' => ['business_name'],
                ],
            ],
            [
                'name'        => 'apply_industry_template',
                'description' => 'Terapkan template/preset industri: setup produk, kategori pengeluaran, dan gudang sesuai jenis bisnis. '
                    . 'Gunakan untuk: "setup template F&B", "terapkan preset konveksi", '
                    . '"setup bisnis konstruksi", "template untuk toko retail", '
                    . '"preset industri pertanian", "setup template distributor".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'industry' => [
                            'type'        => 'string',
                            'description' => 'Jenis industri: fnb, retail, manufacture, distributor, construction, service, agriculture, livestock',
                        ],
                        'include_products'   => ['type' => 'boolean', 'description' => 'Tambahkan produk contoh (default: true)'],
                        'include_categories' => ['type' => 'boolean', 'description' => 'Tambahkan kategori pengeluaran (default: true)'],
                        'include_warehouses' => ['type' => 'boolean', 'description' => 'Tambahkan gudang (default: true)'],
                    ],
                    'required' => ['industry'],
                ],
            ],
            [
                'name'        => 'get_industry_shortcuts',
                'description' => 'Tampilkan daftar command shortcuts dan tips penggunaan AI untuk industri tertentu. '
                    . 'Gunakan untuk: "command apa saja untuk bisnis F&B?", "tips penggunaan untuk konveksi", '
                    . '"shortcut untuk distributor", "cara pakai AI untuk konstruksi".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'industry' => [
                            'type'        => 'string',
                            'description' => 'Jenis industri: fnb, retail, manufacture, distributor, construction, service, agriculture, livestock. Kosong = tampilkan semua.',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function setupBusiness(array $args): array
    {
        $results = [];

        // 1. Buat gudang utama
        $warehouseName = $args['warehouse_name'] ?? $args['business_name'];
        $existingWarehouse = Warehouse::where('tenant_id', $this->tenantId)->first();

        if (!$existingWarehouse) {
            $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $warehouseName), 0, 4)) . '-01';
            $warehouse = Warehouse::create([
                'tenant_id' => $this->tenantId,
                'name'      => $warehouseName,
                'code'      => $code,
                'is_active' => true,
            ]);
            $results[] = "✅ Gudang **{$warehouse->name}** dibuat.";
        } else {
            $warehouse = $existingWarehouse;
            $results[] = "ℹ️ Menggunakan gudang yang sudah ada: **{$warehouse->name}**.";
        }

        // 2. Buat produk awal
        $productList = $args['products'] ?? [];
        $createdProducts = [];
        foreach ($productList as $item) {
            $name = trim($item['name']);
            if (!$name) continue;

            $exists = Product::where('tenant_id', $this->tenantId)->where('name', $name)->exists();
            if ($exists) continue;

            $sku = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 6)) . '-' . rand(100, 999);

            $product = Product::create([
                'tenant_id'  => $this->tenantId,
                'name'       => $name,
                'sku'        => $sku,
                'price_sell' => $item['price_sell'] ?? 0,
                'price_buy'  => 0,
                'unit'       => $item['unit'] ?? 'pcs',
                'category'   => $item['category'] ?? null,
                'stock_min'  => 5,
                'is_active'  => true,
            ]);

            ProductStock::create([
                'product_id'   => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity'     => 0,
            ]);

            $createdProducts[] = $name;
        }

        if (!empty($createdProducts)) {
            $results[] = "✅ Produk ditambahkan: **" . implode('**, **', $createdProducts) . "**.";
        }

        // 3. Buat kategori pengeluaran
        $categories = $args['expense_categories'] ?? ['Bahan Baku', 'Operasional', 'Gaji'];
        $createdCats = [];
        foreach ($categories as $catName) {
            $catName = trim($catName);
            if (!$catName) continue;

            $exists = ExpenseCategory::where('tenant_id', $this->tenantId)->where('name', $catName)->exists();
            if ($exists) continue;

            $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $catName), 0, 5)) . '-' . rand(10, 99);
            ExpenseCategory::create([
                'tenant_id' => $this->tenantId,
                'name'      => $catName,
                'code'      => $code,
                'type'      => 'expense',
                'is_active' => true,
            ]);
            $createdCats[] = $catName;
        }

        if (!empty($createdCats)) {
            $results[] = "✅ Kategori pengeluaran: **" . implode('**, **', $createdCats) . "**.";
        }

        return [
            'status'  => 'success',
            'message' => "Setup bisnis **{$args['business_name']}** selesai!\n\n" . implode("\n", $results)
                . "\n\nSistem siap digunakan. Anda bisa mulai mencatat penjualan, stok, dan keuangan.",
        ];
    }

    public function applyIndustryTemplate(array $args): array
    {
        $industry = strtolower($args['industry']);
        $preset   = self::INDUSTRY_PRESETS[$industry] ?? null;

        if (!$preset) {
            $available = implode(', ', array_keys(self::INDUSTRY_PRESETS));
            return ['status' => 'error', 'message' => "Industri \"{$industry}\" tidak dikenali. Pilihan: {$available}."];
        }

        $includeProducts   = $args['include_products']   ?? true;
        $includeCategories = $args['include_categories'] ?? true;
        $includeWarehouses = $args['include_warehouses'] ?? true;

        $results = ["## Template **{$preset['label']}** diterapkan\n"];

        // 1. Gudang
        if ($includeWarehouses) {
            $createdWh = [];
            foreach ($preset['warehouses'] as $whName) {
                $exists = Warehouse::where('tenant_id', $this->tenantId)->where('name', $whName)->exists();
                if ($exists) continue;

                $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $whName), 0, 4)) . '-' . rand(10, 99);
                Warehouse::create([
                    'tenant_id' => $this->tenantId,
                    'name'      => $whName,
                    'code'      => $code,
                    'is_active' => true,
                ]);
                $createdWh[] = $whName;
            }
            if (!empty($createdWh)) {
                $results[] = "✅ Gudang: **" . implode('**, **', $createdWh) . "**";
            } else {
                $results[] = "ℹ️ Gudang sudah ada, tidak ada yang ditambahkan.";
            }
        }

        // 2. Kategori pengeluaran
        if ($includeCategories) {
            $createdCats = [];
            foreach ($preset['categories'] as $catName) {
                $exists = ExpenseCategory::where('tenant_id', $this->tenantId)->where('name', $catName)->exists();
                if ($exists) continue;

                $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $catName), 0, 5)) . '-' . rand(10, 99);
                ExpenseCategory::create([
                    'tenant_id' => $this->tenantId,
                    'name'      => $catName,
                    'code'      => $code,
                    'type'      => 'expense',
                    'is_active' => true,
                ]);
                $createdCats[] = $catName;
            }
            if (!empty($createdCats)) {
                $results[] = "✅ Kategori biaya: **" . implode('**, **', $createdCats) . "**";
            }
        }

        // 3. Produk contoh
        if ($includeProducts) {
            $warehouse = Warehouse::where('tenant_id', $this->tenantId)->first();
            $createdProducts = [];

            foreach ($preset['products'] as $item) {
                $exists = Product::where('tenant_id', $this->tenantId)->where('name', $item['name'])->exists();
                if ($exists) continue;

                $sku = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $item['name']), 0, 6)) . '-' . rand(100, 999);
                $product = Product::create([
                    'tenant_id'  => $this->tenantId,
                    'name'       => $item['name'],
                    'sku'        => $sku,
                    'price_sell' => $item['price_sell'] ?? 0,
                    'price_buy'  => 0,
                    'unit'       => $item['unit'] ?? 'pcs',
                    'category'   => $item['category'] ?? null,
                    'stock_min'  => 5,
                    'is_active'  => true,
                ]);

                if ($warehouse) {
                    ProductStock::firstOrCreate(
                        ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                        ['quantity' => 0]
                    );
                }

                $createdProducts[] = $item['name'];
            }

            if (!empty($createdProducts)) {
                $results[] = "✅ Produk contoh: **" . implode('**, **', $createdProducts) . "**";
            }
        }

        // 4. Shortcuts info
        $shortcutLines = array_map(fn($s) => "- {$s}", $preset['shortcuts']);
        $results[] = "\n**Command shortcuts untuk {$preset['label']}:**\n" . implode("\n", $shortcutLines);

        return [
            'status'  => 'success',
            'message' => implode("\n", $results),
            'data'    => ['industry' => $industry, 'label' => $preset['label']],
        ];
    }

    public function getIndustryShortcuts(array $args): array
    {
        $industry = strtolower($args['industry'] ?? '');

        if ($industry && isset(self::INDUSTRY_PRESETS[$industry])) {
            $preset = self::INDUSTRY_PRESETS[$industry];
            $lines  = array_map(fn($s) => "- {$s}", $preset['shortcuts']);
            return [
                'status'  => 'success',
                'message' => "**Command shortcuts untuk {$preset['label']}:**\n\n" . implode("\n", $lines)
                    . "\n\nKetik perintah di atas langsung ke chat untuk memulai.",
            ];
        }

        // Tampilkan semua industri
        $all = [];
        foreach (self::INDUSTRY_PRESETS as $key => $preset) {
            $shortcuts = array_map(fn($s) => "  - {$s}", $preset['shortcuts']);
            $all[] = "**{$preset['label']}** (`{$key}`):\n" . implode("\n", $shortcuts);
        }

        return [
            'status'  => 'success',
            'message' => "## Shortcuts per Industri\n\n" . implode("\n\n", $all)
                . "\n\n> Gunakan `apply_industry_template` dengan nama industri untuk setup otomatis.",
        ];
    }
}
