<?php

namespace App\Services\ERP;

/**
 * AppGuideTools — panduan interaktif fitur-fitur Qalcuity ERP.
 * User bisa tanya "fitur apa saja?", "cara pakai inventory?", dll.
 */
class AppGuideTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    // ─── Konten Panduan ───────────────────────────────────────────

    private const MODULES = [
        'dashboard' => [
            'label' => 'Dashboard',
            'icon'  => '🏠',
            'desc'  => 'Ringkasan kondisi bisnis secara real-time: omzet hari ini, stok menipis, piutang jatuh tempo, dan aktivitas terbaru.',
            'tips'  => [
                'Ketik "kondisi bisnis hari ini" untuk ringkasan lengkap',
                'Ketik "rekap minggu ini" atau "rekap bulan ini" untuk laporan periodik',
                'Dashboard menampilkan KPI cards, grafik tren, dan alert otomatis',
            ],
            'examples' => [
                'kondisi bisnis hari ini',
                'rekap omzet bulan ini',
                'laporan harian',
            ],
        ],
        'inventory' => [
            'label' => 'Inventori & Produk',
            'icon'  => '📦',
            'desc'  => 'Kelola produk, stok, harga, dan kategori. Mendukung multi-gudang, transfer stok, dan stock opname.',
            'tips'  => [
                'Tambah produk: "tambah produk Kopi Susu harga 12000 satuan gelas"',
                'Cek stok: "stok produk apa yang menipis?" atau "cek stok kopi"',
                'Tambah stok: "tambah stok kopi 100 gelas"',
                'Upload gambar produk via form di halaman Inventori',
                'Import produk massal via menu Import CSV',
            ],
            'examples' => [
                'daftar semua produk',
                'produk apa yang stoknya menipis?',
                'tambah produk Teh Manis harga 6000',
                'update harga kopi jadi 15000',
            ],
        ],
        'pos' => [
            'label' => 'Kasir (POS)',
            'icon'  => '🖥️',
            'desc'  => 'Point of Sale untuk transaksi penjualan langsung. Mendukung barcode scanner, berbagai metode bayar, dan loyalty poin.',
            'tips'  => [
                'Buka halaman Kasir untuk transaksi manual dengan UI klik produk',
                'Via AI: "jual kopi 2 gelas 15000 cash" untuk catat penjualan cepat',
                'Mendukung pembayaran: cash, transfer, QRIS, kartu',
                'Stok otomatis berkurang setiap transaksi',
                'Produk stok 0 otomatis diblokir dan ditandai "HABIS"',
            ],
            'examples' => [
                'jual kopi 2 gelas cash',
                'catat penjualan mie ayam 3 porsi transfer',
                'rekap omzet POS hari ini',
                'jual kopi 2, teh 1, total 25000 qris',
            ],
        ],
        'purchasing' => [
            'label' => 'Pembelian & Supplier',
            'icon'  => '🛒',
            'desc'  => 'Kelola supplier, purchase order (PO), dan penerimaan barang. Stok otomatis bertambah saat PO diterima.',
            'tips'  => [
                'Tambah supplier: "tambah supplier PT Sumber Jaya email supplier@email.com"',
                'Buat PO: "buat PO ke PT Sumber Jaya: kopi 100 kg harga 50000/kg"',
                'Konfirmasi penerimaan barang untuk update stok otomatis',
                'AI bisa auto-reorder produk yang stoknya di bawah minimum',
            ],
            'examples' => [
                'daftar supplier',
                'buat purchase order ke PT Maju',
                'PO yang belum diterima',
                'auto reorder produk stok rendah',
            ],
        ],
        'sales' => [
            'label' => 'Penjualan & Sales Order',
            'icon'  => '💰',
            'desc'  => 'Buat sales order, quotation, dan kelola piutang pelanggan. Mendukung penjualan tunai dan kredit.',
            'tips'  => [
                'Buat SO: "jual 500 pcs kaos ke Toko B tempo 30 hari"',
                'Buat penawaran: "buat quotation untuk PT Maju: kopi 10 dus 80000/dus"',
                'Catat pembayaran: "customer Budi bayar 500 ribu"',
                'Lihat piutang: "tagihan yang belum dibayar"',
            ],
            'examples' => [
                'buat sales order ke Toko ABC',
                'buat penawaran untuk PT Maju',
                'piutang yang jatuh tempo',
                'customer Budi bayar 1 juta',
            ],
        ],
        'hrm' => [
            'label' => 'SDM & Karyawan',
            'icon'  => '👥',
            'desc'  => 'Kelola data karyawan, absensi harian, dan laporan kehadiran. Terintegrasi dengan penggajian.',
            'tips'  => [
                'Tambah karyawan: "tambah karyawan Siti posisi kasir gaji 3 juta"',
                'Catat absensi: "catat hadir: Siti, Budi, Andi" atau "Siti izin hari ini"',
                'Lihat rekap: "rekap absensi bulan ini"',
                'Absensi otomatis terhubung ke perhitungan gaji',
            ],
            'examples' => [
                'daftar karyawan',
                'catat hadir Siti, Budi, Andi',
                'Siti izin hari ini',
                'rekap absensi bulan ini',
            ],
        ],
        'payroll' => [
            'label' => 'Penggajian',
            'icon'  => '💳',
            'desc'  => 'Proses penggajian otomatis berdasarkan data absensi. Hitung potongan, BPJS, PPh 21, dan cetak slip gaji.',
            'tips'  => [
                'Proses gaji: "hitung gaji semua karyawan bulan Maret 2026"',
                'Lihat slip: "slip gaji Siti bulan ini"',
                'Tandai lunas: "gaji bulan ini sudah dibayar"',
                'Potongan absen dan keterlambatan dihitung otomatis',
            ],
            'examples' => [
                'proses penggajian bulan ini',
                'slip gaji Budi',
                'total penggajian bulan ini',
                'tandai gaji sudah dibayar',
            ],
        ],
        'finance' => [
            'label' => 'Keuangan & Transaksi',
            'icon'  => '📊',
            'desc'  => 'Catat pemasukan dan pengeluaran, lihat laporan keuangan, laba rugi, dan arus kas.',
            'tips'  => [
                'Catat pengeluaran: "catat pengeluaran listrik 500 ribu"',
                'Laporan laba rugi: "laporan laba rugi bulan ini"',
                'Breakdown biaya: "pengeluaran terbesar bulan ini apa?"',
                'Foto struk/nota bisa langsung dianalisis AI untuk dicatat',
            ],
            'examples' => [
                'laporan keuangan bulan ini',
                'laporan laba rugi',
                'pengeluaran terbesar bulan ini',
                'arus kas minggu ini',
            ],
        ],
        'reports' => [
            'label' => 'Laporan & Export',
            'icon'  => '📋',
            'desc'  => 'Generate laporan penjualan, keuangan, inventori, SDM dalam format PDF dan Excel. Bisa via UI atau minta ke AI.',
            'tips'  => [
                'Via AI: "buatkan link download laporan penjualan PDF"',
                'Via UI: buka menu Laporan, pilih jenis dan periode',
                'Format tersedia: PDF dan Excel untuk semua modul utama',
                'Laporan bisa dikirim ke email: "kirim laporan bulanan ke email saya"',
            ],
            'examples' => [
                'download laporan penjualan PDF',
                'export laporan keuangan Excel',
                'kirim laporan bulanan ke email',
                'laporan inventori bulan ini',
            ],
        ],
        'crm' => [
            'label' => 'CRM & Pipeline',
            'icon'  => '🎯',
            'desc'  => 'Kelola prospek dan pipeline penjualan. Catat aktivitas follow-up dan pantau konversi deal.',
            'tips'  => [
                'Tambah prospek: "ada prospek baru PT Maju minat produk kopi estimasi 50 juta"',
                'Update stage: "deal PT Maju sudah qualified"',
                'Catat follow-up: "hubungi PT Maju hari ini, tertarik lanjut"',
                'Lihat pipeline: "tampilkan semua prospek aktif"',
            ],
            'examples' => [
                'tambah lead baru PT Maju',
                'pipeline CRM hari ini',
                'follow-up yang perlu dilakukan hari ini',
                'deal PT Maju menang!',
            ],
        ],
        'assets' => [
            'label' => 'Manajemen Aset',
            'icon'  => '🏗️',
            'desc'  => 'Daftarkan aset perusahaan, hitung depresiasi otomatis, dan jadwalkan maintenance.',
            'tips'  => [
                'Daftarkan aset: "beli mobil Toyota Avanza 300 juta umur 8 tahun"',
                'Hitung depresiasi: "hitung penyusutan aset bulan ini"',
                'Jadwalkan servis: "jadwalkan servis mobil Avanza bulan depan"',
                'Depresiasi dihitung otomatis dengan metode garis lurus',
            ],
            'examples' => [
                'daftar aset perusahaan',
                'hitung depresiasi bulan ini',
                'jadwalkan maintenance kendaraan',
                'nilai total aset',
            ],
        ],
        'projects' => [
            'label' => 'Manajemen Proyek',
            'icon'  => '📐',
            'desc'  => 'Buat dan pantau proyek, catat pengeluaran, log timesheet, dan monitor realisasi vs anggaran.',
            'tips'  => [
                'Buat proyek: "buat proyek pembangunan rumah A budget 200 juta"',
                'Catat biaya: "pengeluaran semen 5 juta proyek rumah A"',
                'Log kerja: "catat kerja 8 jam hari ini proyek rumah A"',
                'Pantau budget: "proyek mana yang over budget?"',
            ],
            'examples' => [
                'buat proyek baru',
                'status proyek aktif',
                'catat pengeluaran proyek',
                'proyek yang over budget',
            ],
        ],
        'warehouse' => [
            'label' => 'Multi-Gudang',
            'icon'  => '🏭',
            'desc'  => 'Kelola stok di beberapa gudang, transfer antar gudang, dan lakukan stock opname.',
            'tips'  => [
                'Lihat stok per gudang: "stok di gudang Jakarta berapa?"',
                'Transfer stok: "transfer 100 pcs kaos dari gudang Jakarta ke Surabaya"',
                'Stock opname: "koreksi stok beras di gudang B jadi 500 kg"',
                'Stok otomatis terpecah per gudang saat ada transaksi',
            ],
            'examples' => [
                'stok semua gudang',
                'transfer stok antar gudang',
                'stock opname gudang utama',
                'daftar gudang',
            ],
        ],
        'invoice' => [
            'label' => 'Invoice & Tagihan',
            'icon'  => '🧾',
            'desc'  => 'Buat invoice profesional, kirim ke pelanggan via email, dan pantau status pembayaran.',
            'tips'  => [
                'Buat invoice via menu Invoice di sidebar',
                'Kirim invoice via email langsung dari sistem',
                'Download invoice sebagai PDF',
                'Status invoice: draft, sent, paid, overdue',
            ],
            'examples' => [
                'buat invoice untuk PT Maju',
                'invoice yang belum dibayar',
                'kirim invoice ke email pelanggan',
                'download invoice PDF',
            ],
        ],
        'ai_chat' => [
            'label' => 'AI Chat',
            'icon'  => '🤖',
            'desc'  => 'Asisten AI yang bisa menjalankan semua operasi ERP via percakapan natural. Mendukung teks, gambar, dan dokumen.',
            'tips'  => [
                'Kirim gambar struk/nota untuk dicatat otomatis sebagai pengeluaran',
                'Upload foto produk untuk diidentifikasi dan ditambah ke inventori',
                'Kirim PDF laporan untuk dianalisis dan diringkas',
                'Gunakan bahasa natural Indonesia — tidak perlu hafal perintah khusus',
                'AI bisa menjalankan beberapa operasi sekaligus dalam satu pesan',
                'Riwayat chat tersimpan per sesi dan bisa dibuka kembali',
            ],
            'examples' => [
                'kondisi bisnis hari ini',
                'jual kopi 2 gelas cash',
                'stok apa yang menipis?',
                'laporan laba rugi bulan ini',
            ],
        ],
        'approval' => [
            'label' => 'Approval Workflow',
            'icon'  => '✅',
            'desc'  => 'Sistem persetujuan untuk transaksi besar. Manager/Admin bisa approve atau reject permintaan.',
            'tips'  => [
                'Buka menu Persetujuan untuk melihat permintaan pending',
                'Approval otomatis dipicu untuk transaksi di atas batas yang dikonfigurasi',
                'Notifikasi dikirim ke approver saat ada permintaan baru',
            ],
            'examples' => [
                'permintaan approval yang pending',
                'approve semua permintaan hari ini',
            ],
        ],
        'notifications' => [
            'label' => 'Notifikasi',
            'icon'  => '🔔',
            'desc'  => 'Notifikasi real-time untuk stok menipis, piutang jatuh tempo, approval, dan aktivitas penting lainnya.',
            'tips'  => [
                'Ikon lonceng di topbar menampilkan notifikasi terbaru',
                'Buka menu Notifikasi untuk riwayat lengkap',
                'AI bisa kirim ringkasan ke email: "kirim laporan harian ke email saya"',
                'Set reminder: "ingatkan saya bayar hutang ke PT X besok"',
            ],
            'examples' => [
                'notifikasi apa yang belum dibaca?',
                'set reminder bayar hutang besok',
                'kirim ringkasan ke email saya',
            ],
        ],
        'users' => [
            'label' => 'Manajemen Pengguna',
            'icon'  => '👤',
            'desc'  => 'Admin bisa menambah pengguna dengan role berbeda: Manajer, Staff, Kasir, atau Gudang.',
            'tips'  => [
                'Buka menu Kelola Pengguna (hanya Admin)',
                'Role Kasir: hanya akses POS dan AI tools penjualan',
                'Role Gudang: hanya akses Inventori dan AI tools stok',
                'Role Manajer: akses semua modul kecuali pengaturan sistem',
                'Setiap pengguna mendapat email kredensial otomatis saat dibuat',
            ],
            'examples' => [
                'tambah pengguna baru kasir',
                'ubah role Budi jadi manajer',
            ],
        ],
    ];

    // ─── Tool Definitions ─────────────────────────────────────────

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'get_app_guide',
                'description' => 'Tampilkan panduan penggunaan aplikasi Qalcuity ERP. '
                    . 'Gunakan tool ini ketika user bertanya tentang: '
                    . '"fitur apa saja?", "cara pakai inventory", "bagaimana cara catat penjualan?", '
                    . '"apa itu POS?", "cara tambah karyawan", "panduan laporan", '
                    . '"bisa apa saja AI ini?", "cara pakai aplikasi ini", '
                    . '"menu apa saja yang ada?", "tutorial", "help", "bantuan".',
                'parameters' => [
                    'type'       => 'object',
                    'properties' => [
                        'topic' => [
                            'type'        => 'string',
                            'description' => 'Topik panduan yang diminta. Pilihan: '
                                . 'overview, dashboard, inventory, pos, purchasing, sales, hrm, payroll, '
                                . 'finance, reports, crm, assets, projects, warehouse, invoice, '
                                . 'ai_chat, approval, notifications, users. '
                                . 'Kosong atau "overview" = tampilkan semua fitur.',
                        ],
                        'show_examples' => [
                            'type'        => 'boolean',
                            'description' => 'Tampilkan contoh perintah AI (default: true)',
                        ],
                    ],
                ],
            ],
        ];
    }

    // ─── Executor ─────────────────────────────────────────────────

    public function getAppGuide(array $args): array
    {
        $topic       = strtolower(trim($args['topic'] ?? 'overview'));
        $showExamples = $args['show_examples'] ?? true;

        // Panduan spesifik satu modul
        if ($topic !== 'overview' && isset(self::MODULES[$topic])) {
            return $this->buildModuleGuide($topic, $showExamples);
        }

        // Overview semua fitur
        return $this->buildOverview($showExamples);
    }

    private function buildModuleGuide(string $topic, bool $showExamples): array
    {
        $mod = self::MODULES[$topic];

        $lines = [
            "## {$mod['icon']} {$mod['label']}",
            '',
            $mod['desc'],
            '',
            '**Tips penggunaan:**',
        ];

        foreach ($mod['tips'] as $tip) {
            $lines[] = "- {$tip}";
        }

        if ($showExamples && !empty($mod['examples'])) {
            $lines[] = '';
            $lines[] = '**Contoh perintah AI:**';
            foreach ($mod['examples'] as $ex) {
                $lines[] = "- \"{$ex}\"";
            }
        }

        // Tombol aksi lanjutan
        $actions = $this->buildModuleActions($topic);

        return [
            'status'  => 'success',
            'message' => implode("\n", $lines),
            'actions' => $actions,
        ];
    }

    private function buildOverview(bool $showExamples): array
    {
        $lines = [
            '## 🚀 Qalcuity ERP — Panduan Fitur',
            '',
            'Qalcuity ERP adalah sistem manajemen bisnis berbasis AI. Berikut semua modul yang tersedia:',
            '',
        ];

        foreach (self::MODULES as $key => $mod) {
            $lines[] = "### {$mod['icon']} {$mod['label']}";
            $lines[] = $mod['desc'];
            if ($showExamples && !empty($mod['examples'])) {
                $lines[] = '> Contoh: "' . $mod['examples'][0] . '"';
            }
            $lines[] = '';
        }

        $lines[] = '---';
        $lines[] = '💡 **Tips:** Ketik nama modul untuk panduan lebih detail. Contoh: "panduan inventory" atau "cara pakai POS".';

        // Actions untuk navigasi cepat
        $actions = [
            ['label' => '📦 Panduan Inventori',  'message' => 'panduan fitur inventori',       'style' => 'default'],
            ['label' => '🖥️ Panduan POS',         'message' => 'panduan fitur kasir POS',       'style' => 'default'],
            ['label' => '📊 Panduan Laporan',     'message' => 'panduan fitur laporan',         'style' => 'default'],
            ['label' => '🤖 Kemampuan AI',        'message' => 'apa saja yang bisa dilakukan AI chat?', 'style' => 'primary'],
        ];

        return [
            'status'  => 'success',
            'message' => implode("\n", $lines),
            'actions' => $actions,
        ];
    }

    private function buildModuleActions(string $topic): array
    {
        $actionMap = [
            'inventory'  => [
                ['label' => '📋 Lihat Produk',      'message' => 'tampilkan semua produk',          'style' => 'default'],
                ['label' => '⚠️ Stok Menipis',       'message' => 'produk apa yang stoknya menipis?','style' => 'warning'],
                ['label' => '➕ Tambah Produk',      'message' => 'cara tambah produk baru',         'style' => 'primary'],
            ],
            'pos'        => [
                ['label' => '💰 Catat Penjualan',   'message' => 'cara catat penjualan via AI',     'style' => 'primary'],
                ['label' => '📊 Rekap POS Hari Ini','message' => 'rekap omzet POS hari ini',        'style' => 'default'],
            ],
            'hrm'        => [
                ['label' => '👥 Daftar Karyawan',   'message' => 'tampilkan semua karyawan',        'style' => 'default'],
                ['label' => '📅 Catat Absensi',     'message' => 'cara catat absensi karyawan',     'style' => 'primary'],
            ],
            'finance'    => [
                ['label' => '📊 Laporan Keuangan',  'message' => 'laporan keuangan bulan ini',      'style' => 'default'],
                ['label' => '📉 Laba Rugi',         'message' => 'laporan laba rugi bulan ini',     'style' => 'primary'],
            ],
            'ai_chat'    => [
                ['label' => '📸 Upload Struk',      'message' => 'cara upload foto struk ke AI',    'style' => 'default'],
                ['label' => '🏭 Setup Bisnis',      'message' => 'cara setup bisnis baru di sistem','style' => 'primary'],
                ['label' => '📋 Semua Fitur',       'message' => 'tampilkan semua fitur aplikasi',  'style' => 'default'],
            ],
        ];

        return $actionMap[$topic] ?? [
            ['label' => '📋 Semua Fitur',   'message' => 'tampilkan semua fitur aplikasi', 'style' => 'default'],
            ['label' => '🤖 Kemampuan AI',  'message' => 'apa saja yang bisa dilakukan AI?', 'style' => 'primary'],
        ];
    }
}
