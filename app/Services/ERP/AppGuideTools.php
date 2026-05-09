<?php

namespace App\Services\ERP;

/**
 * AppGuideTools — panduan interaktif fitur-fitur Qalcuity ERP.
 * Termasuk info navigasi sidebar (grup, ikon, URL) agar AI bisa
 * menjawab "menu invoice di mana?" dengan tepat.
 */
class AppGuideTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    // ─── Navigasi Sidebar ─────────────────────────────────────────
    // Mapping setiap fitur ke lokasi di sidebar: grup, ikon rail, dan URL.
    // Grup sidebar (rail icons):
    //   🏠 home (Dashboard)  |  ✨ ai (AI Chat)      |  🗄️ masterdata (Master Data)
    //   🏷️ sales (Penjualan) |  📦 inventory (Inventori) |  ⚙️ ops (Operasional)
    //   👥 hrm (SDM)         |  💰 finance (Keuangan) |  📊 analytics (Analitik)
    //   ⚙️ settings (Pengaturan)

    private const NAV = [
        // ── Master Data (ikon: 🗄️ database) ──
        'customers' => ['group' => 'masterdata', 'icon' => '🗄️', 'label' => 'Data Customer',       'path' => '/customers',        'section' => 'Kontak'],
        'suppliers' => ['group' => 'masterdata', 'icon' => '🗄️', 'label' => 'Data Supplier',       'path' => '/suppliers',        'section' => 'Kontak'],
        'products' => ['group' => 'masterdata', 'icon' => '🗄️', 'label' => 'Data Produk',         'path' => '/products',         'section' => 'Produk & Gudang'],
        'warehouses' => ['group' => 'masterdata', 'icon' => '🗄️', 'label' => 'Data Gudang',         'path' => '/warehouses',       'section' => 'Produk & Gudang'],

        // ── Penjualan (ikon: 🏷️ tag) ──
        'sales_order' => ['group' => 'sales',      'icon' => '🏷️', 'label' => 'Sales Order',         'path' => '/sales',            'section' => 'Transaksi'],
        'quotations' => ['group' => 'sales',      'icon' => '🏷️', 'label' => 'Penawaran (Quotation)', 'path' => '/quotations',      'section' => 'Transaksi'],
        'invoices' => ['group' => 'sales',      'icon' => '🏷️', 'label' => 'Invoice',             'path' => '/invoices',         'section' => 'Transaksi'],
        'delivery_orders' => ['group' => 'sales',      'icon' => '🏷️', 'label' => 'Surat Jalan',         'path' => '/delivery-orders',  'section' => 'Transaksi'],
        'down_payments' => ['group' => 'sales',      'icon' => '🏷️', 'label' => 'Uang Muka (DP)',      'path' => '/down-payments',    'section' => 'Transaksi'],
        'sales_returns' => ['group' => 'sales',      'icon' => '🏷️', 'label' => 'Retur Penjualan',     'path' => '/sales-returns',    'section' => 'Transaksi'],
        'price_lists' => ['group' => 'sales',      'icon' => '🏷️', 'label' => 'Daftar Harga',        'path' => '/price-lists',      'section' => 'Transaksi'],
        'crm' => ['group' => 'sales',      'icon' => '🏷️', 'label' => 'CRM & Pipeline',      'path' => '/crm',              'section' => null],
        'commission' => ['group' => 'sales',      'icon' => '🏷️', 'label' => 'Komisi Sales',        'path' => '/commission',       'section' => null],
        'helpdesk' => ['group' => 'sales',      'icon' => '🏷️', 'label' => 'Helpdesk',            'path' => '/helpdesk',         'section' => null],
        'loyalty' => ['group' => 'sales',      'icon' => '🏷️', 'label' => 'Program Loyalitas',   'path' => '/loyalty',          'section' => null],
        'pos' => ['group' => 'sales',      'icon' => '🏷️', 'label' => 'Kasir (POS)',         'path' => '/pos',              'section' => null],

        // ── Inventori (ikon: 📦 cube) ──
        'inventory' => ['group' => 'inventory',  'icon' => '📦', 'label' => 'Inventori',           'path' => '/inventory',        'section' => null],
        'transfers' => ['group' => 'inventory',  'icon' => '📦', 'label' => 'Transfer Stok',       'path' => '/inventory/transfers', 'section' => null],
        'purchasing' => ['group' => 'inventory',  'icon' => '📦', 'label' => 'Pembelian',           'path' => '/purchasing/orders', 'section' => 'Pembelian'],
        'purchase_returns' => ['group' => 'inventory',  'icon' => '📦', 'label' => 'Retur Pembelian',     'path' => '/purchase-returns',  'section' => 'Pembelian'],
        'goods_receipt' => ['group' => 'inventory',  'icon' => '📦', 'label' => 'Goods Receipt',       'path' => '/purchasing/goods-receipts', 'section' => 'Pembelian'],
        'consignment' => ['group' => 'inventory',  'icon' => '📦', 'label' => 'Konsinyasi',          'path' => '/consignment',      'section' => 'Pembelian'],
        'wms' => ['group' => 'inventory',  'icon' => '📦', 'label' => 'WMS (Zone & Bin)',    'path' => '/wms',              'section' => 'WMS Gudang'],

        // ── Operasional (ikon: ⚙️ cog) ──
        'production' => ['group' => 'ops',        'icon' => '⚙️', 'label' => 'Produksi / WO',       'path' => '/production',       'section' => null],
        'manufacturing' => ['group' => 'ops',        'icon' => '⚙️', 'label' => 'BOM Multi-Level',     'path' => '/manufacturing/bom', 'section' => null],
        'mix_design' => ['group' => 'ops',        'icon' => '⚙️', 'label' => 'Mix Design Beton',   'path' => '/manufacturing/mix-design', 'section' => null],
        'fleet' => ['group' => 'ops',        'icon' => '⚙️', 'label' => 'Fleet Kendaraan',     'path' => '/fleet',            'section' => null],
        'shipping' => ['group' => 'ops',        'icon' => '⚙️', 'label' => 'Pengiriman',          'path' => '/shipping',         'section' => null],
        'contracts' => ['group' => 'ops',        'icon' => '⚙️', 'label' => 'Kontrak',             'path' => '/contracts',        'section' => null],
        'approvals' => ['group' => 'ops',        'icon' => '⚙️', 'label' => 'Persetujuan',         'path' => '/approvals',        'section' => null],
        'ecommerce' => ['group' => 'ops',        'icon' => '⚙️', 'label' => 'E-Commerce',          'path' => '/ecommerce',        'section' => null],
        'documents' => ['group' => 'ops',        'icon' => '⚙️', 'label' => 'Dokumen',             'path' => '/documents',        'section' => null],
        'projects' => ['group' => 'ops',        'icon' => '⚙️', 'label' => 'Manajemen Proyek',    'path' => '/projects',         'section' => null],
        'timesheets' => ['group' => 'ops',        'icon' => '⚙️', 'label' => 'Timesheet',           'path' => '/timesheets',       'section' => null],
        'farm_plots' => ['group' => 'ops',        'icon' => '⚙️', 'label' => 'Manajemen Lahan',    'path' => '/farm/plots',       'section' => null],
        'crop_cycles' => ['group' => 'ops',        'icon' => '⚙️', 'label' => 'Siklus Tanam',      'path' => '/farm/cycles',      'section' => null],
        'harvest_logs' => ['group' => 'ops',        'icon' => '⚙️', 'label' => 'Pencatatan Panen',  'path' => '/farm/harvests',    'section' => null],
        'farm_analytics' => ['group' => 'ops',        'icon' => '⚙️', 'label' => 'Analisis Biaya Lahan', 'path' => '/farm/analytics', 'section' => null],
        'livestock' => ['group' => 'ops',        'icon' => '⚙️', 'label' => 'Populasi Ternak',   'path' => '/farm/livestock',   'section' => null],

        // ── SDM (ikon: 👥 users) ──
        'hrm' => ['group' => 'hrm',        'icon' => '👥', 'label' => 'SDM & Karyawan',      'path' => '/hrm',              'section' => 'Manajemen SDM'],
        'recruitment' => ['group' => 'hrm',        'icon' => '👥', 'label' => 'Rekrutmen',           'path' => '/hrm/recruitment',  'section' => 'Manajemen SDM'],
        'leave' => ['group' => 'hrm',        'icon' => '👥', 'label' => 'Manajemen Cuti',      'path' => '/hrm/leave',        'section' => 'Manajemen SDM'],
        'performance' => ['group' => 'hrm',        'icon' => '👥', 'label' => 'Penilaian Kinerja',   'path' => '/hrm/performance',  'section' => 'Manajemen SDM'],
        'shifts' => ['group' => 'hrm',        'icon' => '👥', 'label' => 'Jadwal Shift',        'path' => '/hrm/shifts',       'section' => 'Manajemen SDM'],
        'payroll' => ['group' => 'hrm',        'icon' => '👥', 'label' => 'Penggajian',          'path' => '/payroll',          'section' => 'Penggajian'],
        'self_service' => ['group' => 'hrm',        'icon' => '👥', 'label' => 'Portal Karyawan',     'path' => '/self-service',     'section' => 'Self-Service'],

        // ── Keuangan (ikon: 💰 currency) ──
        'expenses' => ['group' => 'finance',    'icon' => '💰', 'label' => 'Pengeluaran',         'path' => '/expenses',         'section' => null],
        'receivables' => ['group' => 'finance',    'icon' => '💰', 'label' => 'Piutang (AR)',        'path' => '/receivables',      'section' => null],
        'payables' => ['group' => 'finance',    'icon' => '💰', 'label' => 'Hutang (AP)',         'path' => '/payables',         'section' => null],
        'bank_accounts' => ['group' => 'finance',    'icon' => '💰', 'label' => 'Rekening Bank',       'path' => '/bank-accounts',    'section' => null],
        'bank_recon' => ['group' => 'finance',    'icon' => '💰', 'label' => 'Rekonsiliasi Bank',   'path' => '/bank/reconciliation', 'section' => null],
        'budget' => ['group' => 'finance',    'icon' => '💰', 'label' => 'Anggaran',            'path' => '/budget',           'section' => null],
        'assets' => ['group' => 'finance',    'icon' => '💰', 'label' => 'Aset',                'path' => '/assets',           'section' => null],
        'journals' => ['group' => 'finance',    'icon' => '💰', 'label' => 'Jurnal',              'path' => '/accounting/journals', 'section' => 'Akuntansi'],
        'coa' => ['group' => 'finance',    'icon' => '💰', 'label' => 'Bagan Akun (COA)',    'path' => '/accounting/coa',   'section' => 'Akuntansi'],
        'trial_balance' => ['group' => 'finance',    'icon' => '💰', 'label' => 'Neraca Saldo',        'path' => '/accounting/trial-balance', 'section' => 'Akuntansi'],
        'balance_sheet' => ['group' => 'finance',    'icon' => '💰', 'label' => 'Neraca',              'path' => '/accounting/balance-sheet', 'section' => 'Akuntansi'],
        'income_statement' => ['group' => 'finance',    'icon' => '💰', 'label' => 'Laba Rugi (P&L)',     'path' => '/accounting/income-statement', 'section' => 'Akuntansi'],
        'cash_flow' => ['group' => 'finance',    'icon' => '💰', 'label' => 'Arus Kas',            'path' => '/accounting/cash-flow', 'section' => 'Akuntansi'],

        // ── Analitik (ikon: 📊 chart) ──
        'reports' => ['group' => 'analytics',  'icon' => '📊', 'label' => 'Laporan',             'path' => '/reports',          'section' => null],
        'kpi' => ['group' => 'analytics',  'icon' => '📊', 'label' => 'KPI Dashboard',       'path' => '/kpi',              'section' => null],
        'forecast' => ['group' => 'analytics',  'icon' => '📊', 'label' => 'AI Forecasting',      'path' => '/forecast',         'section' => null],
        'anomalies' => ['group' => 'analytics',  'icon' => '📊', 'label' => 'Deteksi Anomali',     'path' => '/anomalies',        'section' => 'AI & Deteksi'],
        'simulations' => ['group' => 'analytics',  'icon' => '📊', 'label' => 'Simulasi Keuangan',   'path' => '/simulations',      'section' => 'AI & Deteksi'],

        // ── Pengaturan (ikon: ⚙️ gear) ──
        'company_profile' => ['group' => 'settings',   'icon' => '⚙️', 'label' => 'Profil Perusahaan',   'path' => '/company-profile',  'section' => null],
        'users' => ['group' => 'settings',   'icon' => '⚙️', 'label' => 'Kelola Pengguna',     'path' => '/tenant/users',     'section' => null],
        'import' => ['group' => 'settings',   'icon' => '⚙️', 'label' => 'Import CSV',          'path' => '/import',           'section' => null],
        'audit' => ['group' => 'settings',   'icon' => '⚙️', 'label' => 'Audit Trail',         'path' => '/audit',            'section' => null],
        'notifications' => ['group' => 'settings',   'icon' => '⚙️', 'label' => 'Notifikasi',          'path' => '/notifications',    'section' => null],
        'api_settings' => ['group' => 'settings',   'icon' => '⚙️', 'label' => 'API & Webhook',       'path' => '/settings/api',     'section' => null],
        'taxes' => ['group' => 'settings',   'icon' => '⚙️', 'label' => 'Pajak',               'path' => '/taxes',            'section' => 'Konfigurasi'],
        'reminders' => ['group' => 'settings',   'icon' => '⚙️', 'label' => 'Pengingat',           'path' => '/reminders',        'section' => null],
        'bot' => ['group' => 'settings',   'icon' => '⚙️', 'label' => 'Bot WA/Telegram',     'path' => '/bot/settings',     'section' => null],
        'subscription' => ['group' => 'settings',   'icon' => '⚙️', 'label' => 'Langganan',           'path' => '/subscription',     'section' => null],
    ];

    private const SIDEBAR_GROUPS = [
        'home' => ['label' => 'Dashboard',    'rail_icon' => '🏠', 'desc' => 'Ikon rumah di paling atas sidebar kiri'],
        'ai' => ['label' => 'AI Chat',      'rail_icon' => '✨', 'desc' => 'Ikon bintang/sparkle di sidebar kiri'],
        'masterdata' => ['label' => 'Master Data',  'rail_icon' => '🗄️', 'desc' => 'Ikon database di sidebar kiri'],
        'sales' => ['label' => 'Penjualan',    'rail_icon' => '🏷️', 'desc' => 'Ikon tag/label di sidebar kiri'],
        'inventory' => ['label' => 'Inventori',    'rail_icon' => '📦', 'desc' => 'Ikon kubus/box di sidebar kiri'],
        'ops' => ['label' => 'Operasional',  'rail_icon' => '⚙️', 'desc' => 'Ikon gear/roda gigi di sidebar kiri'],
        'hrm' => ['label' => 'SDM',          'rail_icon' => '👥', 'desc' => 'Ikon orang/users di sidebar kiri'],
        'finance' => ['label' => 'Keuangan',     'rail_icon' => '💰', 'desc' => 'Ikon mata uang di sidebar kiri'],
        'analytics' => ['label' => 'Analitik',     'rail_icon' => '📊', 'desc' => 'Ikon chart/grafik di sidebar kiri'],
        'settings' => ['label' => 'Pengaturan',   'rail_icon' => '⚙️', 'desc' => 'Ikon gear di bagian bawah sidebar kiri'],
    ];

    // ─── Konten Panduan per Modul ─────────────────────────────────

    private const MODULES = [
        'dashboard' => [
            'label' => 'Dashboard',
            'desc' => 'Ringkasan kondisi bisnis secara real-time: omzet hari ini, stok menipis, piutang jatuh tempo, dan aktivitas terbaru.',
            'nav' => ['home'],
            'tips' => [
                'Ketik "kondisi bisnis hari ini" untuk ringkasan lengkap',
                'Ketik "rekap minggu ini" atau "rekap bulan ini" untuk laporan periodik',
            ],
            'examples' => ['kondisi bisnis hari ini', 'rekap omzet bulan ini'],
        ],
        'inventory' => [
            'label' => 'Inventori & Produk',
            'desc' => 'Kelola produk, stok, harga, dan kategori. Mendukung multi-gudang, transfer stok, dan stock opname.',
            'nav' => ['products', 'warehouses', 'inventory', 'transfers', 'wms'],
            'tips' => [
                'Tambah produk: "tambah produk Kopi Susu harga 12000 satuan gelas"',
                'Cek stok: "stok produk apa yang menipis?"',
                'Import produk massal via menu Import CSV di Pengaturan',
            ],
            'examples' => ['daftar semua produk', 'produk apa yang stoknya menipis?', 'tambah produk Teh Manis harga 6000'],
        ],
        'pos' => [
            'label' => 'Kasir (POS)',
            'desc' => 'Point of Sale untuk transaksi penjualan langsung. Mendukung barcode scanner, berbagai metode bayar, dan loyalty poin.',
            'nav' => ['pos'],
            'tips' => [
                'Buka halaman Kasir untuk transaksi manual dengan UI klik produk',
                'Via AI: "jual kopi 2 gelas 15000 cash" untuk catat penjualan cepat',
                'Mendukung pembayaran: cash, transfer, QRIS, kartu',
            ],
            'examples' => ['jual kopi 2 gelas cash', 'rekap omzet POS hari ini'],
        ],
        'purchasing' => [
            'label' => 'Pembelian & Supplier',
            'desc' => 'Kelola supplier, purchase order (PO), dan penerimaan barang.',
            'nav' => ['suppliers', 'purchasing', 'goods_receipt', 'purchase_returns', 'consignment'],
            'tips' => [
                'Tambah supplier: "tambah supplier PT Sumber Jaya"',
                'Buat PO: "buat PO ke PT Sumber Jaya: kopi 100 kg harga 50000/kg"',
            ],
            'examples' => ['daftar supplier', 'buat purchase order ke PT Maju'],
        ],
        'sales' => [
            'label' => 'Penjualan & Sales Order',
            'desc' => 'Buat sales order, quotation, invoice, dan kelola piutang pelanggan.',
            'nav' => ['sales_order', 'quotations', 'invoices', 'delivery_orders', 'down_payments', 'sales_returns', 'price_lists'],
            'tips' => [
                'Buat SO: "jual 500 pcs kaos ke Toko B tempo 30 hari"',
                'Buat penawaran: "buat quotation untuk PT Maju"',
                'Catat pembayaran: "customer Budi bayar 500 ribu"',
            ],
            'examples' => ['buat sales order ke Toko ABC', 'piutang yang jatuh tempo'],
        ],
        'hrm' => [
            'label' => 'SDM & Karyawan',
            'desc' => 'Kelola data karyawan, absensi, cuti, rekrutmen, dan penilaian kinerja.',
            'nav' => ['hrm', 'recruitment', 'leave', 'performance', 'shifts', 'self_service'],
            'tips' => [
                'Tambah karyawan: "tambah karyawan Siti posisi kasir gaji 3 juta"',
                'Catat absensi: "catat hadir: Siti, Budi, Andi"',
            ],
            'examples' => ['daftar karyawan', 'catat hadir Siti, Budi, Andi', 'rekap absensi bulan ini'],
        ],
        'payroll' => [
            'label' => 'Penggajian',
            'desc' => 'Proses penggajian otomatis berdasarkan data absensi. Hitung potongan, BPJS, PPh 21.',
            'nav' => ['payroll'],
            'tips' => [
                'Proses gaji: "hitung gaji semua karyawan bulan ini"',
                'Lihat slip: "slip gaji Siti bulan ini"',
            ],
            'examples' => ['proses penggajian bulan ini', 'slip gaji Budi'],
        ],
        'finance' => [
            'label' => 'Keuangan & Akuntansi',
            'desc' => 'Catat pemasukan/pengeluaran, jurnal, neraca, laba rugi, arus kas, dan rekonsiliasi bank.',
            'nav' => ['expenses', 'receivables', 'payables', 'bank_accounts', 'bank_recon', 'budget', 'assets', 'journals', 'coa', 'trial_balance', 'balance_sheet', 'income_statement', 'cash_flow'],
            'tips' => [
                'Catat pengeluaran: "catat pengeluaran listrik 500 ribu"',
                'Laporan laba rugi: "laporan laba rugi bulan ini"',
                'Foto struk/nota bisa langsung dianalisis AI',
            ],
            'examples' => ['laporan keuangan bulan ini', 'laporan laba rugi', 'pengeluaran terbesar bulan ini'],
        ],
        'reports' => [
            'label' => 'Laporan & Export',
            'desc' => 'Generate laporan penjualan, keuangan, inventori, SDM dalam format PDF dan Excel.',
            'nav' => ['reports', 'kpi', 'forecast'],
            'tips' => [
                'Via UI: buka menu Laporan di grup Analitik',
                'Via AI: "kirim laporan bulanan ke email saya"',
            ],
            'examples' => ['download laporan penjualan PDF', 'kirim laporan bulanan ke email'],
        ],
        'crm' => [
            'label' => 'CRM & Pipeline',
            'desc' => 'Kelola prospek dan pipeline penjualan. Catat aktivitas follow-up.',
            'nav' => ['crm'],
            'tips' => [
                'Tambah prospek: "ada prospek baru PT Maju minat produk kopi estimasi 50 juta"',
                'Update stage: "deal PT Maju menang!"',
            ],
            'examples' => ['tambah lead baru PT Maju', 'pipeline CRM hari ini'],
        ],
        'assets' => [
            'label' => 'Manajemen Aset',
            'desc' => 'Daftarkan aset perusahaan, hitung depresiasi otomatis, dan jadwalkan maintenance.',
            'nav' => ['assets'],
            'tips' => [
                'Daftarkan aset: "beli mobil Toyota Avanza 300 juta umur 8 tahun"',
                'Hitung depresiasi: "hitung penyusutan aset bulan ini"',
            ],
            'examples' => ['daftar aset perusahaan', 'hitung depresiasi bulan ini'],
        ],
        'projects' => [
            'label' => 'Manajemen Proyek',
            'desc' => 'Buat dan pantau proyek, catat pengeluaran, log timesheet, monitor realisasi vs anggaran.',
            'nav' => ['projects', 'timesheets'],
            'tips' => [
                'Buat proyek: "buat proyek pembangunan rumah A budget 200 juta"',
                'Catat biaya: "pengeluaran semen 5 juta proyek rumah A"',
            ],
            'examples' => ['buat proyek baru', 'status proyek aktif'],
        ],
        'warehouse' => [
            'label' => 'Multi-Gudang',
            'desc' => 'Kelola stok di beberapa gudang, transfer antar gudang, dan stock opname.',
            'nav' => ['warehouses', 'inventory', 'transfers', 'wms'],
            'tips' => [
                'Lihat stok per gudang: "stok di gudang Jakarta berapa?"',
                'Transfer stok: "transfer 100 pcs kaos dari gudang Jakarta ke Surabaya"',
            ],
            'examples' => ['stok semua gudang', 'transfer stok antar gudang'],
        ],
        'invoice' => [
            'label' => 'Invoice & Tagihan',
            'desc' => 'Buat invoice profesional, kirim ke pelanggan via email, dan pantau status pembayaran.',
            'nav' => ['invoices'],
            'tips' => [
                'Buat invoice via menu Invoice di grup Penjualan',
                'Kirim invoice via email langsung dari sistem',
            ],
            'examples' => ['buat invoice untuk PT Maju', 'invoice yang belum dibayar'],
        ],
        'ai_chat' => [
            'label' => 'AI Chat',
            'desc' => 'Asisten AI yang bisa menjalankan semua operasi ERP via percakapan natural.',
            'nav' => [],
            'tips' => [
                'Klik ikon ✨ (bintang) di sidebar kiri untuk membuka AI Chat',
                'Kirim gambar struk/nota untuk dicatat otomatis sebagai pengeluaran',
                'Gunakan bahasa natural Indonesia — tidak perlu hafal perintah khusus',
            ],
            'examples' => ['kondisi bisnis hari ini', 'jual kopi 2 gelas cash', 'stok apa yang menipis?'],
        ],
        'approval' => [
            'label' => 'Approval Workflow',
            'desc' => 'Sistem persetujuan untuk transaksi besar.',
            'nav' => ['approvals'],
            'tips' => ['Buka menu Persetujuan di grup Operasional untuk melihat permintaan pending'],
            'examples' => ['permintaan approval yang pending'],
        ],
        'notifications' => [
            'label' => 'Notifikasi & Pengingat',
            'desc' => 'Notifikasi real-time untuk stok menipis, piutang jatuh tempo, dan aktivitas penting.',
            'nav' => ['notifications', 'reminders'],
            'tips' => [
                'Ikon 🔔 lonceng di topbar menampilkan notifikasi terbaru',
                'Set reminder: "ingatkan saya bayar hutang ke PT X besok"',
            ],
            'examples' => ['set reminder bayar hutang besok', 'kirim ringkasan ke email saya'],
        ],
        'users' => [
            'label' => 'Manajemen Pengguna',
            'desc' => 'Admin bisa menambah pengguna dengan role berbeda: Manajer, Staff, Kasir, atau Gudang.',
            'nav' => ['users'],
            'tips' => [
                'Buka menu Kelola Pengguna di grup Pengaturan (hanya Admin)',
                'Role Kasir: hanya akses POS dan AI tools penjualan',
                'Role Gudang: hanya akses Inventori dan AI tools stok',
            ],
            'examples' => ['tambah pengguna baru kasir'],
        ],
    ];

    // ─── Tool Definitions ─────────────────────────────────────────

    public static function definitions(): array
    {
        return [
            [
                'name' => 'get_app_guide',
                'description' => 'Tampilkan panduan penggunaan aplikasi Qalcuity ERP termasuk lokasi menu di sidebar. '
                    .'Gunakan tool ini ketika user bertanya tentang: '
                    .'"fitur apa saja?", "cara pakai inventory", "menu invoice di mana?", '
                    .'"di mana letak menu X?", "cara buka halaman Y?", '
                    .'"apa itu POS?", "cara tambah karyawan", "panduan laporan", '
                    .'"bisa apa saja AI ini?", "cara pakai aplikasi ini", '
                    .'"menu apa saja yang ada?", "tutorial", "help", "bantuan".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'topic' => [
                            'type' => 'string',
                            'description' => 'Topik panduan yang diminta. Pilihan: '
                                .'overview, dashboard, inventory, pos, purchasing, sales, hrm, payroll, '
                                .'finance, reports, crm, assets, projects, warehouse, invoice, '
                                .'ai_chat, approval, notifications, users. '
                                .'Kosong atau "overview" = tampilkan semua fitur.',
                        ],
                        'find_menu' => [
                            'type' => 'string',
                            'description' => 'Cari lokasi menu spesifik. Contoh: "invoice", "penggajian", "stok", "laporan". '
                                .'Akan mengembalikan lokasi sidebar, grup, ikon, dan URL langsung.',
                        ],
                        'show_examples' => [
                            'type' => 'boolean',
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
        $topic = strtolower(trim($args['topic'] ?? 'overview'));
        $findMenu = strtolower(trim($args['find_menu'] ?? ''));
        $showExamples = $args['show_examples'] ?? true;

        // Cari lokasi menu spesifik
        if ($findMenu) {
            return $this->findMenuLocation($findMenu);
        }

        // Panduan spesifik satu modul
        if ($topic !== 'overview' && isset(self::MODULES[$topic])) {
            return $this->buildModuleGuide($topic, $showExamples);
        }

        // Overview semua fitur
        return $this->buildOverview($showExamples);
    }

    // ─── Cari Lokasi Menu ─────────────────────────────────────────

    private function findMenuLocation(string $query): array
    {
        $results = [];

        // Cari di NAV berdasarkan label atau key
        foreach (self::NAV as $key => $nav) {
            $label = strtolower($nav['label']);
            $keyLower = strtolower(str_replace('_', ' ', $key));

            if (str_contains($label, $query) || str_contains($keyLower, $query) || str_contains($query, $keyLower)) {
                $group = self::SIDEBAR_GROUPS[$nav['group']] ?? null;
                $results[] = [
                    'menu' => $nav['label'],
                    'url' => $nav['path'],
                    'sidebar' => $group ? "{$group['rail_icon']} {$group['label']}" : $nav['group'],
                    'how_to' => $group
                        ? "Klik {$group['desc']}, lalu pilih \"{$nav['label']}\"".($nav['section'] ? " di bagian \"{$nav['section']}\"" : '')
                        : "Buka grup \"{$nav['group']}\" di sidebar",
                ];
            }
        }

        // Juga cari di alias umum
        $aliases = [
            'stok' => ['inventory', 'transfers'],
            'stock' => ['inventory', 'transfers'],
            'barang' => ['products', 'inventory'],
            'produk' => ['products'],
            'pelanggan' => ['customers'],
            'customer' => ['customers'],
            'pemasok' => ['suppliers'],
            'supplier' => ['suppliers'],
            'gudang' => ['warehouses', 'wms'],
            'faktur' => ['invoices'],
            'tagihan' => ['invoices', 'receivables'],
            'invoice' => ['invoices'],
            'gaji' => ['payroll'],
            'karyawan' => ['hrm'],
            'pegawai' => ['hrm'],
            'absensi' => ['hrm'],
            'cuti' => ['leave'],
            'kasir' => ['pos'],
            'penjualan' => ['sales_order', 'pos'],
            'pembelian' => ['purchasing'],
            'po' => ['purchasing'],
            'hutang' => ['payables'],
            'piutang' => ['receivables'],
            'laporan' => ['reports'],
            'report' => ['reports'],
            'aset' => ['assets'],
            'proyek' => ['projects'],
            'project' => ['projects'],
            'kontrak' => ['contracts'],
            'pengiriman' => ['shipping'],
            'crm' => ['crm'],
            'prospek' => ['crm'],
            'lead' => ['crm'],
            'anggaran' => ['budget'],
            'budget' => ['budget'],
            'jurnal' => ['journals'],
            'neraca' => ['balance_sheet', 'trial_balance'],
            'laba rugi' => ['income_statement'],
            'arus kas' => ['cash_flow'],
            'pajak' => ['taxes'],
            'notifikasi' => ['notifications'],
            'reminder' => ['reminders'],
            'pengingat' => ['reminders'],
            'import' => ['import'],
            'export' => ['reports', 'import'],
            'pengguna' => ['users'],
            'user' => ['users'],
            'bot' => ['bot'],
            'whatsapp' => ['bot'],
            'telegram' => ['bot'],
            'webhook' => ['api_settings'],
            'api' => ['api_settings'],
            'langganan' => ['subscription'],
            'loyalty' => ['loyalty'],
            'poin' => ['loyalty'],
            'komisi' => ['commission'],
            'helpdesk' => ['helpdesk'],
            'tiket' => ['helpdesk'],
            'dokumen' => ['documents'],
            'persetujuan' => ['approvals'],
            'approval' => ['approvals'],
            'rekrutmen' => ['recruitment'],
            'shift' => ['shifts'],
            'lembur' => ['hrm'],
            'produksi' => ['production'],
            'bom' => ['manufacturing'],
            'fleet' => ['fleet'],
            'kendaraan' => ['fleet'],
            'lahan' => ['farm_plots'],
            'kebun' => ['farm_plots'],
            'blok' => ['farm_plots'],
            'pertanian' => ['farm_plots'],
            'sawah' => ['farm_plots'],
            'panen' => ['farm_plots'],
            'tanam' => ['farm_plots'],
            'siklus' => ['crop_cycles'],
            'musim tanam' => ['crop_cycles'],
            'ternak' => ['livestock'],
            'peternakan' => ['livestock'],
            'kandang' => ['livestock'],
            'ayam' => ['livestock'],
            'sapi' => ['livestock'],
            'kambing' => ['livestock'],
            'konsinyasi' => ['consignment'],
            'ecommerce' => ['ecommerce'],
            'timesheet' => ['timesheets'],
            'reimbursement' => ['self_service'],
        ];

        if (empty($results) && isset($aliases[$query])) {
            foreach ($aliases[$query] as $navKey) {
                if (isset(self::NAV[$navKey])) {
                    $nav = self::NAV[$navKey];
                    $group = self::SIDEBAR_GROUPS[$nav['group']] ?? null;
                    $results[] = [
                        'menu' => $nav['label'],
                        'url' => $nav['path'],
                        'sidebar' => $group ? "{$group['rail_icon']} {$group['label']}" : $nav['group'],
                        'how_to' => $group
                            ? "Klik {$group['desc']}, lalu pilih \"{$nav['label']}\"".($nav['section'] ? " di bagian \"{$nav['section']}\"" : '')
                            : "Buka grup \"{$nav['group']}\" di sidebar",
                    ];
                }
            }
        }

        if (empty($results)) {
            return [
                'status' => 'not_found',
                'message' => "Menu \"{$query}\" tidak ditemukan. Coba kata kunci lain, atau ketik \"fitur apa saja?\" untuk melihat semua menu.",
                'actions' => [
                    ['label' => '📋 Semua Fitur', 'message' => 'tampilkan semua fitur aplikasi', 'style' => 'primary'],
                ],
            ];
        }

        $lines = ["## 📍 Lokasi Menu: \"{$query}\"\n"];
        foreach ($results as $r) {
            $lines[] = "**{$r['menu']}**";
            $lines[] = "- Sidebar: {$r['sidebar']}";
            $lines[] = "- Cara buka: {$r['how_to']}";
            $lines[] = "- URL langsung: `{$r['url']}`";
            $lines[] = '';
        }

        return [
            'status' => 'success',
            'message' => implode("\n", $lines),
            'nav' => $results,
            'actions' => [
                ['label' => '🔗 Buka '.$results[0]['menu'], 'message' => 'buka halaman '.strtolower($results[0]['menu']), 'style' => 'primary'],
                ['label' => '📋 Semua Fitur', 'message' => 'tampilkan semua fitur aplikasi', 'style' => 'default'],
            ],
        ];
    }

    // ─── Build Module Guide ───────────────────────────────────────

    private function buildModuleGuide(string $topic, bool $showExamples): array
    {
        $mod = self::MODULES[$topic];

        $lines = ["## {$mod['label']}\n", $mod['desc'], ''];

        // Navigasi sidebar
        if (! empty($mod['nav'])) {
            $lines[] = '**📍 Lokasi di aplikasi:**';
            foreach ($mod['nav'] as $navKey) {
                if (isset(self::NAV[$navKey])) {
                    $nav = self::NAV[$navKey];
                    $group = self::SIDEBAR_GROUPS[$nav['group']] ?? null;
                    $groupLabel = $group ? "{$group['rail_icon']} {$group['label']}" : '';
                    $lines[] = "- **{$nav['label']}** → Sidebar {$groupLabel}".($nav['section'] ? " › {$nav['section']}" : '')." → URL: `{$nav['path']}`";
                }
            }
            $lines[] = '';
        }

        $lines[] = '**💡 Tips penggunaan:**';
        foreach ($mod['tips'] as $tip) {
            $lines[] = "- {$tip}";
        }

        if ($showExamples && ! empty($mod['examples'])) {
            $lines[] = '';
            $lines[] = '**🗣️ Contoh perintah AI:**';
            foreach ($mod['examples'] as $ex) {
                $lines[] = "- \"{$ex}\"";
            }
        }

        $actions = $this->buildModuleActions($topic);

        return [
            'status' => 'success',
            'message' => implode("\n", $lines),
            'actions' => $actions,
        ];
    }

    private function buildOverview(bool $showExamples): array
    {
        $lines = [
            '## 🚀 Qalcuity ERP — Panduan Fitur & Navigasi',
            '',
            'Qalcuity ERP adalah sistem manajemen bisnis berbasis AI. Berikut semua modul yang tersedia:',
            '',
            '**Navigasi:** Klik ikon di sidebar kiri untuk membuka grup menu, lalu pilih halaman yang diinginkan.',
            '',
        ];

        foreach (self::MODULES as $key => $mod) {
            // Cari ikon dari nav pertama
            $icon = '📌';
            if (! empty($mod['nav']) && isset(self::NAV[$mod['nav'][0]])) {
                $navInfo = self::NAV[$mod['nav'][0]];
                $groupInfo = self::SIDEBAR_GROUPS[$navInfo['group']] ?? null;
                $icon = $groupInfo['rail_icon'] ?? '📌';
            }

            $lines[] = "### {$icon} {$mod['label']}";
            $lines[] = $mod['desc'];

            // Tampilkan lokasi sidebar
            if (! empty($mod['nav']) && isset(self::NAV[$mod['nav'][0]])) {
                $navInfo = self::NAV[$mod['nav'][0]];
                $groupInfo = self::SIDEBAR_GROUPS[$navInfo['group']] ?? null;
                if ($groupInfo) {
                    $lines[] = "> Sidebar: {$groupInfo['rail_icon']} **{$groupInfo['label']}** → {$navInfo['label']}";
                }
            }

            if ($showExamples && ! empty($mod['examples'])) {
                $lines[] = '> Contoh: "'.$mod['examples'][0].'"';
            }
            $lines[] = '';
        }

        $lines[] = '---';
        $lines[] = '💡 **Tips:** Ketik nama modul untuk panduan detail, atau tanya "menu X di mana?" untuk cari lokasi menu spesifik.';

        $actions = [
            ['label' => '📦 Panduan Inventori',  'message' => 'panduan fitur inventori',       'style' => 'default'],
            ['label' => '🖥️ Panduan POS',         'message' => 'panduan fitur kasir POS',       'style' => 'default'],
            ['label' => '📊 Panduan Laporan',     'message' => 'panduan fitur laporan',         'style' => 'default'],
            ['label' => '🤖 Kemampuan AI',        'message' => 'apa saja yang bisa dilakukan AI chat?', 'style' => 'primary'],
        ];

        return [
            'status' => 'success',
            'message' => implode("\n", $lines),
            'actions' => $actions,
        ];
    }

    private function buildModuleActions(string $topic): array
    {
        $actionMap = [
            'inventory' => [
                ['label' => '📋 Lihat Produk',      'message' => 'tampilkan semua produk',          'style' => 'default'],
                ['label' => '⚠️ Stok Menipis',       'message' => 'produk apa yang stoknya menipis?', 'style' => 'warning'],
                ['label' => '➕ Tambah Produk',      'message' => 'cara tambah produk baru',         'style' => 'primary'],
            ],
            'pos' => [
                ['label' => '💰 Catat Penjualan',   'message' => 'cara catat penjualan via AI',     'style' => 'primary'],
                ['label' => '📊 Rekap POS Hari Ini', 'message' => 'rekap omzet POS hari ini',        'style' => 'default'],
            ],
            'hrm' => [
                ['label' => '👥 Daftar Karyawan',   'message' => 'tampilkan semua karyawan',        'style' => 'default'],
                ['label' => '📅 Catat Absensi',     'message' => 'cara catat absensi karyawan',     'style' => 'primary'],
            ],
            'finance' => [
                ['label' => '📊 Laporan Keuangan',  'message' => 'laporan keuangan bulan ini',      'style' => 'default'],
                ['label' => '📉 Laba Rugi',         'message' => 'laporan laba rugi bulan ini',     'style' => 'primary'],
            ],
            'ai_chat' => [
                ['label' => '📸 Upload Struk',      'message' => 'cara upload foto struk ke AI',    'style' => 'default'],
                ['label' => '🏭 Setup Bisnis',      'message' => 'cara setup bisnis baru di sistem', 'style' => 'primary'],
                ['label' => '📋 Semua Fitur',       'message' => 'tampilkan semua fitur aplikasi',  'style' => 'default'],
            ],
            'sales' => [
                ['label' => '🧾 Buat Sales Order',  'message' => 'cara buat sales order',           'style' => 'primary'],
                ['label' => '💳 Piutang Jatuh Tempo', 'message' => 'piutang yang jatuh tempo',       'style' => 'warning'],
            ],
            'purchasing' => [
                ['label' => '📦 Buat PO',           'message' => 'cara buat purchase order',        'style' => 'primary'],
                ['label' => '📋 Daftar Supplier',   'message' => 'tampilkan semua supplier',        'style' => 'default'],
            ],
        ];

        return $actionMap[$topic] ?? [
            ['label' => '📋 Semua Fitur',   'message' => 'tampilkan semua fitur aplikasi', 'style' => 'default'],
            ['label' => '🤖 Kemampuan AI',  'message' => 'apa saja yang bisa dilakukan AI?', 'style' => 'primary'],
        ];
    }
}
