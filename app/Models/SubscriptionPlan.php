<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'price_monthly', 'price_yearly',
        'max_users', 'max_ai_messages', 'trial_days',
        'features', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_active' => 'boolean',
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
        ];
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function isUnlimitedUsers(): bool
    {
        return $this->max_users === -1;
    }

    public function isUnlimitedAi(): bool
    {
        return $this->max_ai_messages === -1;
    }

    public static function defaultPlans(): array
    {
        return [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'price_monthly' => 99000,
                'price_yearly' => 999000,
                'max_users' => 2,
                'max_ai_messages' => 50,
                'trial_days' => 14,
                'features' => [
                    'POS Kasir',
                    'Inventori & Stok',
                    'Penjualan & Invoice',
                    'Laporan Dasar',
                    'AI Chat (50 pesan/bln)',
                    '1 Gudang',
                ],
                'sort_order' => 1,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'price_monthly' => 249000,
                'price_yearly' => 2499000,
                'max_users' => 10,
                'max_ai_messages' => 300,
                'trial_days' => 14,
                'features' => [
                    'Semua fitur Starter',
                    'Pembelian & Supplier',
                    'Piutang & Hutang (AR/AP)',
                    'Multi Gudang',
                    'Quotation → SO → Invoice',
                    'Konsinyasi (Stok Titipan)',
                    'Komisi Sales & Target',
                    'Reimbursement Karyawan',
                    'Helpdesk & Tiket Support',
                    'Subscription Billing (Recurring)',
                    'CRM & Pipeline',
                    'Laporan Keuangan (Neraca, Laba Rugi)',
                    'AI Chat (300 pesan/bln)',
                    'Export Excel & PDF',
                ],
                'sort_order' => 2,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'price_monthly' => 499000,
                'price_yearly' => 4999000,
                'max_users' => 25,
                'max_ai_messages' => 1000,
                'trial_days' => 14,
                'features' => [
                    'Semua fitur Business',
                    'HRM & Payroll',
                    'Aset & Depresiasi',
                    'Budget vs Aktual',
                    'Rekonsiliasi Bank + AI',
                    'Multi Currency',
                    'Approval Workflow',
                    'Manufaktur (BOM & MRP)',
                    'Fleet Management',
                    'WMS Gudang Lanjutan (Zone/Bin)',
                    'Manajemen Kontrak & SLA',
                    'Landed Cost (Biaya Impor)',
                    'Project Billing (T&M/Milestone)',
                    'Konstruksi (RAB, Mix Design, Termin)',
                    'Pertanian (Lahan, Siklus Tanam, Panen)',
                    'AI Forecasting Dashboard',
                    'POS Thermal Printer & Scanner',
                    'E-Commerce (Shopee/Tokopedia)',
                    'AI Anomaly Detection',
                    'AI Chat (1.000 pesan/bln)',
                    'Simulasi Bisnis (What If)',
                ],
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'price_monthly' => 999000,
                'price_yearly' => 9999000,
                'max_users' => -1,
                'max_ai_messages' => -1,
                'trial_days' => 30,
                'features' => [
                    'Semua fitur Professional',
                    'User Tak Terbatas',
                    'AI Tak Terbatas',
                    'Multi Company & Konsolidasi Keuangan',
                    'Laporan Konsolidasi (P&L, Neraca, Arus Kas)',
                    'Transaksi Intercompany & Eliminasi',
                    'Zero Input OCR',
                    'Custom Integrasi API',
                    'Webhook Outbound',
                    'Bulk Import/Export Semua Master Data',
                    'Prioritas Support',
                    'WhatsApp Bot Notifikasi',
                    'Digital Signature',
                ],
                'sort_order' => 4,
            ],
        ];
    }
}
