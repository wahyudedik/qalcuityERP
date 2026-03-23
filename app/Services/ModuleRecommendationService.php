<?php

namespace App\Services;

class ModuleRecommendationService
{
    /** All available module keys */
    const ALL_MODULES = [
        'pos', 'inventory', 'purchasing', 'sales', 'invoicing',
        'hrm', 'payroll', 'crm', 'accounting', 'budget',
        'production', 'ecommerce', 'projects', 'assets',
        'loyalty', 'bank_reconciliation', 'reports',
    ];

    /** Module metadata: label, icon, description */
    const MODULE_META = [
        'pos'                 => ['label' => 'Kasir (POS)',           'icon' => '🖥️',  'desc' => 'Point of sale untuk transaksi langsung'],
        'inventory'           => ['label' => 'Inventori',             'icon' => '📦',  'desc' => 'Manajemen stok & gudang'],
        'purchasing'          => ['label' => 'Pembelian',             'icon' => '🛒',  'desc' => 'Purchase order & supplier'],
        'sales'               => ['label' => 'Penjualan',             'icon' => '💰',  'desc' => 'Sales order & quotation'],
        'invoicing'           => ['label' => 'Invoice & Piutang',     'icon' => '🧾',  'desc' => 'Invoice, piutang & hutang'],
        'hrm'                 => ['label' => 'SDM & Karyawan',        'icon' => '👥',  'desc' => 'Data karyawan & absensi'],
        'payroll'             => ['label' => 'Penggajian',            'icon' => '💳',  'desc' => 'Slip gaji & komponen gaji'],
        'crm'                 => ['label' => 'CRM & Pipeline',        'icon' => '🤝',  'desc' => 'Leads, prospek & follow-up'],
        'accounting'          => ['label' => 'Akuntansi (GL)',        'icon' => '📊',  'desc' => 'Jurnal, COA & laporan keuangan'],
        'budget'              => ['label' => 'Anggaran',              'icon' => '📋',  'desc' => 'Perencanaan & monitoring anggaran'],
        'production'          => ['label' => 'Produksi / WO',         'icon' => '🏭',  'desc' => 'Work order & bill of materials'],
        'ecommerce'           => ['label' => 'E-Commerce',            'icon' => '🛍️',  'desc' => 'Sinkronisasi marketplace'],
        'projects'            => ['label' => 'Manajemen Proyek',      'icon' => '📌',  'desc' => 'Task, milestone & timesheet'],
        'assets'              => ['label' => 'Aset',                  'icon' => '🏢',  'desc' => 'Aset tetap & depresiasi'],
        'loyalty'             => ['label' => 'Program Loyalitas',     'icon' => '⭐',  'desc' => 'Poin & reward pelanggan'],
        'bank_reconciliation' => ['label' => 'Rekonsiliasi Bank',     'icon' => '🏦',  'desc' => 'Cocokkan mutasi bank otomatis'],
        'reports'             => ['label' => 'Laporan & KPI',         'icon' => '📈',  'desc' => 'Laporan bisnis & dashboard KPI'],
    ];

    /**
     * Recommend modules based on industry key.
     * Returns ['modules' => [...keys], 'reason' => '...']
     */
    public function recommend(string $industry): array
    {
        return match ($industry) {
            'fnb' => [
                'modules' => ['pos', 'inventory', 'purchasing', 'invoicing', 'accounting', 'hrm', 'payroll', 'reports', 'loyalty'],
                'reason'  => 'Bisnis F&B butuh kasir cepat, kontrol stok bahan baku, dan pencatatan keuangan harian.',
            ],
            'retail' => [
                'modules' => ['pos', 'inventory', 'purchasing', 'sales', 'invoicing', 'accounting', 'hrm', 'payroll', 'loyalty', 'ecommerce', 'reports'],
                'reason'  => 'Toko retail perlu manajemen stok, kasir, dan integrasi marketplace.',
            ],
            'manufacture' => [
                'modules' => ['inventory', 'purchasing', 'production', 'sales', 'invoicing', 'accounting', 'hrm', 'payroll', 'assets', 'budget', 'reports'],
                'reason'  => 'Manufaktur memerlukan work order, BOM, kontrol bahan baku, dan akuntansi biaya.',
            ],
            'distributor' => [
                'modules' => ['inventory', 'purchasing', 'sales', 'invoicing', 'accounting', 'hrm', 'payroll', 'crm', 'bank_reconciliation', 'reports'],
                'reason'  => 'Distributor fokus pada pembelian massal, distribusi ke pelanggan, dan piutang.',
            ],
            'construction' => [
                'modules' => ['projects', 'purchasing', 'invoicing', 'accounting', 'hrm', 'payroll', 'assets', 'budget', 'reports'],
                'reason'  => 'Konstruksi butuh manajemen proyek, RAB, dan kontrol pengeluaran per proyek.',
            ],
            'service' => [
                'modules' => ['crm', 'projects', 'invoicing', 'accounting', 'hrm', 'payroll', 'budget', 'reports'],
                'reason'  => 'Bisnis jasa fokus pada pipeline klien, proyek, dan penagihan.',
            ],
            'agriculture' => [
                'modules' => ['inventory', 'purchasing', 'sales', 'invoicing', 'accounting', 'hrm', 'payroll', 'assets', 'reports'],
                'reason'  => 'Pertanian perlu kontrol stok hasil panen, pembelian input, dan penjualan.',
            ],
            default => [
                'modules' => ['pos', 'inventory', 'purchasing', 'sales', 'invoicing', 'accounting', 'hrm', 'reports'],
                'reason'  => 'Modul dasar yang cocok untuk berbagai jenis bisnis.',
            ],
        };
    }
}
