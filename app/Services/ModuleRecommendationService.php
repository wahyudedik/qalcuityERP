<?php

namespace App\Services;

class ModuleRecommendationService
{
    /** All available module keys */
    const ALL_MODULES = [
        'pos',
        'inventory',
        'purchasing',
        'sales',
        'invoicing',
        'hrm',
        'payroll',
        'crm',
        'accounting',
        'budget',
        'production',
        'manufacturing',
        'fleet',
        'contracts',
        'ecommerce',
        'projects',
        'assets',
        'commission',
        'helpdesk',
        'project_billing',
        'loyalty',
        'bank_reconciliation',
        'reports',
        'landed_cost',
        'consignment',
        'subscription_billing',
        'reimbursement',
        'wms',
        'agriculture',
        'livestock',
        'hotel',
        'fnb',
        'spa',
        'telecom',
        'healthcare',
        'tour_travel',
        'construction',
        'cosmetic',
        'printing',
        'mobile',
    ];

    /** Module metadata: label, icon, description */
    const MODULE_META = [
        'pos' => ['label' => 'Kasir (POS)',               'icon' => '🖥️',  'desc' => 'Point of sale untuk transaksi langsung'],
        'inventory' => ['label' => 'Inventori',                 'icon' => '📦',  'desc' => 'Manajemen stok & gudang'],
        'purchasing' => ['label' => 'Pembelian',                 'icon' => '🛒',  'desc' => 'Purchase order & supplier'],
        'sales' => ['label' => 'Penjualan',                 'icon' => '💰',  'desc' => 'Sales order & quotation'],
        'invoicing' => ['label' => 'Invoice & Piutang',         'icon' => '🧾',  'desc' => 'Invoice, piutang & hutang'],
        'hrm' => ['label' => 'SDM & Karyawan',            'icon' => '👥',  'desc' => 'Data karyawan & absensi'],
        'payroll' => ['label' => 'Penggajian',                'icon' => '💳',  'desc' => 'Slip gaji & komponen gaji'],
        'crm' => ['label' => 'CRM & Pipeline',            'icon' => '🤝',  'desc' => 'Leads, prospek & follow-up'],
        'accounting' => ['label' => 'Akuntansi (GL)',            'icon' => '📊',  'desc' => 'Jurnal, COA & laporan keuangan'],
        'budget' => ['label' => 'Anggaran',                  'icon' => '📋',  'desc' => 'Perencanaan & monitoring anggaran'],
        'production' => ['label' => 'Produksi / WO',             'icon' => '🏭',  'desc' => 'Work order & bill of materials'],
        'manufacturing' => ['label' => 'Manufaktur (BOM/MRP)',       'icon' => '⚙️',  'desc' => 'BOM multi-level, work center & MRP'],
        'fleet' => ['label' => 'Fleet Management',          'icon' => '🚛',  'desc' => 'Kendaraan, driver, BBM & maintenance'],
        'contracts' => ['label' => 'Manajemen Kontrak',         'icon' => '📝',  'desc' => 'Kontrak, SLA, recurring billing'],
        'ecommerce' => ['label' => 'E-Commerce',                'icon' => '🛍️',  'desc' => 'Sinkronisasi marketplace (Shopee, Tokopedia, Lazada, TikTok Shop)'],
        'projects' => ['label' => 'Manajemen Proyek',          'icon' => '📌',  'desc' => 'Task, milestone & timesheet'],
        'assets' => ['label' => 'Aset',                      'icon' => '🏢',  'desc' => 'Aset tetap & depresiasi'],
        'loyalty' => ['label' => 'Program Loyalitas',         'icon' => '⭐',  'desc' => 'Poin & reward pelanggan'],
        'bank_reconciliation' => ['label' => 'Rekonsiliasi Bank',         'icon' => '🏦',  'desc' => 'Cocokkan mutasi bank otomatis'],
        'reports' => ['label' => 'Laporan & KPI',             'icon' => '📈',  'desc' => 'Laporan bisnis & dashboard KPI'],
        'landed_cost' => ['label' => 'Landed Cost',               'icon' => '🚢',  'desc' => 'Alokasi biaya impor ke HPP'],
        'consignment' => ['label' => 'Konsinyasi',                'icon' => '🏪',  'desc' => 'Stok titipan, penjualan & settlement'],
        'commission' => ['label' => 'Komisi Sales',              'icon' => '💵',  'desc' => 'Target, achievement & komisi salesperson'],
        'helpdesk' => ['label' => 'Helpdesk & Tiket',          'icon' => '🎫',  'desc' => 'Support ticket, SLA & knowledge base'],
        'project_billing' => ['label' => 'Project Billing',           'icon' => '📐',  'desc' => 'Timesheet->invoice, milestone, retainer'],
        'subscription_billing' => ['label' => 'Subscription Billing',      'icon' => '🔄',  'desc' => 'Recurring invoice untuk pelanggan langganan'],
        'reimbursement' => ['label' => 'Reimbursement',             'icon' => '🧾',  'desc' => 'Pengajuan & pembayaran reimbursement karyawan'],
        'wms' => ['label' => 'WMS (Gudang Lanjutan)',      'icon' => '🏗️',  'desc' => 'Zone, rak, bin, putaway, picking & opname'],
        'agriculture' => ['label' => 'Pertanian / Lahan',         'icon' => '🌾',  'desc' => 'Manajemen lahan, siklus tanam & panen'],
        'livestock' => ['label' => 'Peternakan',                'icon' => '🐄',  'desc' => 'Tracking populasi ternak, pakan, FCR & vaksinasi'],
        'hotel' => ['label' => 'Hotel PMS',                 'icon' => '🏨',  'desc' => 'Reservasi, kamar, tamu, housekeeping & channel'],
        'fnb' => ['label' => 'F&B / Restoran',            'icon' => '🍽️',  'desc' => 'Menu, kasir restoran, room service & banquet'],
        'spa' => ['label' => 'Spa & Wellness',            'icon' => '💆',  'desc' => 'Booking treatment, terapis & paket spa'],
        'telecom' => ['label' => 'Telekomunikasi (ISP)',       'icon' => '📡',  'desc' => 'Manajemen perangkat, paket internet, hotspot & voucher'],
        'healthcare' => ['label' => 'SimRS / Healthcare',        'icon' => '🏥',  'desc' => 'Rekam medis, rawat inap, IGD, farmasi, laboratorium, radiologi & BPJS'],
        'tour_travel' => ['label' => 'Tour & Travel',             'icon' => '✈️',  'desc' => 'Paket wisata, booking, pemandu & supplier travel'],
        'construction' => ['label' => 'Konstruksi',                'icon' => '🏗️',  'desc' => 'RAB, subkontraktor, laporan harian, gantt & pengiriman material'],
        'cosmetic' => ['label' => 'Kosmetik & Beauty',         'icon' => '💄',  'desc' => 'Formula, batch produksi, QC lab, BPOM & distribusi kosmetik'],
        'printing' => ['label' => 'Percetakan',                'icon' => '🖨️',  'desc' => 'Estimasi cetak, job order, press tracking & finishing'],
        'mobile' => ['label' => 'Mobile (Mode Lapangan)',    'icon' => '📱',  'desc' => 'Picking, stock opname, transfer & input aktivitas via mobile'],
    ];

    /**
     * Recommend modules based on industry key.
     * Returns ['modules' => [...keys], 'reason' => '...']
     */
    public function recommend(string $industry): array
    {
        // Map onboarding form keys to internal recommendation keys
        $aliasMap = [
            'restaurant' => 'fnb',
            'manufacturing' => 'manufacture',
            'services' => 'service',
        ];
        $industry = $aliasMap[$industry] ?? $industry;

        return match ($industry) {
            'fnb' => [
                'modules' => ['pos', 'fnb', 'inventory', 'purchasing', 'invoicing', 'accounting', 'hrm', 'payroll', 'reports', 'loyalty'],
                'reason' => 'Bisnis F&B butuh kasir cepat, modul restoran/menu, kontrol stok bahan baku, dan pencatatan keuangan harian.',
            ],
            'retail' => [
                'modules' => ['pos', 'inventory', 'purchasing', 'sales', 'invoicing', 'accounting', 'hrm', 'payroll', 'loyalty', 'consignment', 'ecommerce', 'reports'],
                'reason' => 'Toko retail perlu manajemen stok, kasir, konsinyasi, dan integrasi marketplace.',
            ],
            'manufacture' => [
                'modules' => ['inventory', 'purchasing', 'production', 'manufacturing', 'sales', 'invoicing', 'accounting', 'hrm', 'payroll', 'assets', 'budget', 'reports'],
                'reason' => 'Manufaktur memerlukan work order, BOM multi-level, MRP, kontrol bahan baku, dan akuntansi biaya.',
            ],
            'distributor' => [
                'modules' => ['inventory', 'purchasing', 'wms', 'sales', 'invoicing', 'accounting', 'hrm', 'payroll', 'crm', 'fleet', 'landed_cost', 'bank_reconciliation', 'reports'],
                'reason' => 'Distributor fokus pada WMS gudang lanjutan, pembelian massal, fleet kendaraan, landed cost, dan piutang.',
            ],
            'construction' => [
                'modules' => ['construction', 'projects', 'purchasing', 'invoicing', 'accounting', 'hrm', 'payroll', 'assets', 'fleet', 'budget', 'reports'],
                'reason' => 'Konstruksi butuh RAB, subkontraktor, laporan harian, gantt chart, pengiriman material, dan kontrol pengeluaran per proyek.',
            ],
            'service' => [
                'modules' => ['crm', 'projects', 'project_billing', 'contracts', 'helpdesk', 'invoicing', 'accounting', 'hrm', 'payroll', 'budget', 'reports'],
                'reason' => 'Bisnis jasa fokus pada pipeline klien, project billing, kontrak, helpdesk, dan penagihan.',
            ],
            'agriculture' => [
                'modules' => ['inventory', 'purchasing', 'sales', 'invoicing', 'accounting', 'hrm', 'payroll', 'assets', 'reports', 'agriculture'],
                'reason' => 'Pertanian perlu manajemen lahan/blok, kontrol stok hasil panen, pembelian input, dan penjualan.',
            ],
            'livestock' => [
                'modules' => ['livestock', 'inventory', 'purchasing', 'sales', 'invoicing', 'accounting', 'hrm', 'payroll', 'assets', 'reports'],
                'reason' => 'Peternakan perlu tracking populasi, pakan & FCR, vaksinasi, kesehatan ternak, dan penjualan.',
            ],
            'hotel' => [
                'modules' => ['hotel', 'fnb', 'spa', 'pos', 'invoicing', 'accounting', 'hrm', 'payroll', 'crm', 'loyalty', 'reports'],
                'reason' => 'Hotel & penginapan butuh manajemen reservasi, kamar, tamu, housekeeping, F&B, spa, channel distribution, dan laporan pendapatan.',
            ],
            'telecom' => [
                'modules' => ['telecom', 'invoicing', 'subscription_billing', 'accounting', 'hrm', 'payroll', 'reports'],
                'reason' => 'ISP & provider telekomunikasi butuh manajemen perangkat, paket internet, hotspot, voucher, dan penagihan langganan.',
            ],
            'healthcare' => [
                'modules' => ['healthcare', 'invoicing', 'accounting', 'hrm', 'payroll', 'inventory', 'reports'],
                'reason' => 'Rumah sakit & klinik butuh rekam medis, rawat inap, IGD, farmasi, laboratorium, radiologi, BPJS, dan billing.',
            ],
            'tour_travel' => [
                'modules' => ['tour_travel', 'crm', 'invoicing', 'accounting', 'hrm', 'payroll', 'reports'],
                'reason' => 'Agen travel butuh manajemen paket wisata, booking, pemandu, supplier, dan penagihan.',
            ],
            'cosmetic' => [
                'modules' => ['cosmetic', 'manufacturing', 'inventory', 'purchasing', 'sales', 'invoicing', 'accounting', 'hrm', 'payroll', 'reports'],
                'reason' => 'Industri kosmetik butuh formula, batch produksi, QC lab, registrasi BPOM, dan distribusi.',
            ],
            'printing' => [
                'modules' => ['printing', 'inventory', 'purchasing', 'sales', 'invoicing', 'accounting', 'hrm', 'payroll', 'reports'],
                'reason' => 'Percetakan butuh estimasi cetak, job order, press tracking, finishing, dan penagihan.',
            ],
            default => [
                'modules' => ['pos', 'inventory', 'purchasing', 'sales', 'invoicing', 'accounting', 'hrm', 'reports'],
                'reason' => 'Modul dasar yang cocok untuk berbagai jenis bisnis.',
            ],
        };
    }
}
