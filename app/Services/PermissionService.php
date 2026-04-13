<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    /**
     * All modules and their available actions.
     */
    public const MODULES = [
        'dashboard'      => ['view'],
        'customers'      => ['view', 'create', 'edit', 'delete'],
        'suppliers'      => ['view', 'create', 'edit', 'delete'],
        'products'       => ['view', 'create', 'edit', 'delete'],
        'warehouses'     => ['view', 'create', 'edit', 'delete'],
        'sales'          => ['view', 'create', 'edit', 'delete'],
        'invoices'       => ['view', 'create', 'edit', 'delete'],
        'quotations'     => ['view', 'create', 'edit', 'delete'],
        'delivery'       => ['view', 'create', 'edit'],
        'sales_returns'  => ['view', 'create', 'edit', 'delete'],
        'down_payments'  => ['view', 'create'],
        'price_lists'    => ['view', 'create', 'edit', 'delete'],
        'receivables'    => ['view', 'create', 'edit', 'delete'],
        'purchasing'     => ['view', 'create', 'edit', 'delete'],
        'inventory'      => ['view', 'create', 'edit', 'delete'],
        'pos'            => ['view', 'create'],
        'production'     => ['view', 'create', 'edit', 'delete'],
        'manufacturing'  => ['view', 'create', 'edit', 'delete'],
        'fleet'          => ['view', 'create', 'edit', 'delete'],
        'contracts'      => ['view', 'create', 'edit', 'delete'],
        'landed_cost'    => ['view', 'create', 'edit', 'delete'],
        'consignment'    => ['view', 'create', 'edit', 'delete'],
        'commission'     => ['view', 'create', 'edit', 'delete'],
        'helpdesk'       => ['view', 'create', 'edit', 'delete'],
        'project_billing'=> ['view', 'create', 'edit'],
        'subscription_billing' => ['view', 'create', 'edit', 'delete'],
        'reimbursement'  => ['view', 'create', 'edit', 'delete'],
        'wms'            => ['view', 'create', 'edit', 'delete'],
        'hrm'            => ['view', 'create', 'edit', 'delete'],
        'overtime'       => ['view', 'create', 'edit', 'delete'],
        'training'       => ['view', 'create', 'edit', 'delete'],
        'disciplinary'   => ['view', 'create', 'edit', 'delete'],
        'payroll'        => ['view', 'create', 'edit'],
        'crm'            => ['view', 'create', 'edit', 'delete'],
        'projects'       => ['view', 'create', 'edit', 'delete'],
        'timesheets'     => ['view', 'create', 'delete'],
        'assets'         => ['view', 'create', 'edit', 'delete'],
        'accounting'     => ['view', 'create', 'edit', 'delete'],
        'journals'       => ['view', 'create', 'edit'],
        'budget'         => ['view', 'create', 'edit', 'delete'],
        'reports'        => ['view'],
        'kpi'            => ['view', 'create', 'delete'],
        'expenses'       => ['view', 'create', 'edit', 'delete'],
        'documents'      => ['view', 'create', 'delete'],
        'reminders'      => ['view', 'create', 'delete'],
        'loyalty'        => ['view', 'create', 'edit'],
        'ecommerce'      => ['view', 'create', 'edit'],
        'shipping'       => ['view', 'create'],
        'approvals'      => ['view', 'create', 'edit'],
        'audit'          => ['view'],
        'users'          => ['view', 'create', 'edit', 'delete'],
        'taxes'          => ['view', 'create', 'edit', 'delete'],
        'bank'           => ['view', 'create', 'edit', 'delete'],
        'import'         => ['view', 'create'],
        'anomalies'      => ['view', 'create'],
        'simulations'    => ['view', 'create', 'delete'],
        'writeoffs'      => ['view', 'create', 'edit', 'delete'],
        'deferred'       => ['view', 'create', 'edit'],
        'bulk_payments'  => ['view', 'create'],
        'cost_centers'   => ['view', 'create', 'edit', 'delete'],
        'constraints'    => ['view', 'edit'],
        'custom_fields'  => ['view', 'create', 'edit', 'delete'],
        'company_groups' => ['view', 'create', 'edit', 'delete'],
        'zero_input'     => ['view', 'create'],
        'agriculture'    => ['view', 'create', 'edit', 'delete'],
        'rab'            => ['view', 'create', 'edit', 'delete'],
    ];

    /**
     * Default permissions per role.
     * true  = granted by default
     * false = denied by default
     * Omitted = denied
     */
    public const ROLE_DEFAULTS = [
        'admin' => '*', // all modules, all actions

        'manager' => [
            'dashboard'      => ['view'],
            'customers'      => ['view', 'create', 'edit', 'delete'],
            'suppliers'      => ['view', 'create', 'edit', 'delete'],
            'products'       => ['view', 'create', 'edit', 'delete'],
            'warehouses'     => ['view', 'create', 'edit', 'delete'],
            'sales'          => ['view', 'create', 'edit', 'delete'],
            'invoices'       => ['view', 'create', 'edit', 'delete'],
            'quotations'     => ['view', 'create', 'edit', 'delete'],
            'delivery'       => ['view', 'create', 'edit'],
            'sales_returns'  => ['view', 'create', 'edit'],
            'down_payments'  => ['view', 'create'],
            'price_lists'    => ['view', 'create', 'edit'],
            'receivables'    => ['view', 'create', 'edit'],
            'purchasing'     => ['view', 'create', 'edit'],
            'inventory'      => ['view', 'create', 'edit'],
            'pos'            => ['view', 'create'],
            'production'     => ['view', 'create', 'edit'],
            'manufacturing'  => ['view', 'create', 'edit'],
            'fleet'          => ['view', 'create', 'edit'],
            'contracts'      => ['view', 'create', 'edit'],
            'landed_cost'    => ['view', 'create', 'edit'],
            'consignment'    => ['view', 'create', 'edit'],
            'commission'     => ['view', 'create', 'edit'],
            'helpdesk'       => ['view', 'create', 'edit'],
            'project_billing'=> ['view', 'create', 'edit'],
            'subscription_billing' => ['view', 'create', 'edit'],
            'reimbursement'  => ['view', 'create', 'edit'],
            'wms'            => ['view', 'create', 'edit'],
            'hrm'            => ['view', 'create', 'edit'],
            'payroll'        => ['view', 'create', 'edit'],
            'overtime'       => ['view', 'create', 'edit', 'delete'],
            'training'       => ['view', 'create', 'edit', 'delete'],
            'disciplinary'   => ['view', 'create', 'edit', 'delete'],
            'crm'            => ['view', 'create', 'edit', 'delete'],
            'projects'       => ['view', 'create', 'edit', 'delete'],
            'timesheets'     => ['view', 'create', 'delete'],
            'assets'         => ['view', 'create', 'edit'],
            'accounting'     => ['view'],
            'journals'       => ['view', 'create'],
            'budget'         => ['view', 'create', 'edit'],
            'reports'        => ['view'],
            'kpi'            => ['view'],
            'expenses'       => ['view', 'create', 'edit'],
            'documents'      => ['view', 'create', 'delete'],
            'reminders'      => ['view', 'create', 'delete'],
            'loyalty'        => ['view', 'create', 'edit'],
            'ecommerce'      => ['view', 'create', 'edit'],
            'shipping'       => ['view', 'create'],
            'approvals'      => ['view', 'create', 'edit'],
            'import'         => ['view', 'create'],
            'anomalies'      => ['view'],
            'simulations'    => ['view', 'create'],
            'writeoffs'      => ['view', 'create'],
            'deferred'       => ['view', 'create'],
            'bulk_payments'  => ['view', 'create'],
            'agriculture'    => ['view', 'create', 'edit', 'delete'],
            'rab'            => ['view', 'create', 'edit', 'delete'],
        ],

        'staff' => [
            'dashboard'     => ['view'],
            'customers'     => ['view'],
            'suppliers'     => ['view'],
            'products'      => ['view'],
            'warehouses'    => ['view'],
            'sales'         => ['view'],
            'invoices'      => ['view'],
            'quotations'    => ['view'],
            'delivery'      => ['view'],
            'inventory'     => ['view'],
            'pos'           => ['view', 'create'],
            'crm'           => ['view'],
            'projects'      => ['view'],
            'timesheets'    => ['view', 'create'],
            'documents'     => ['view', 'create'],
            'reminders'     => ['view', 'create', 'delete'],
            'expenses'      => ['view', 'create'],
            'zero_input'    => ['view', 'create'],
            'helpdesk'      => ['view', 'create'],
            'agriculture'   => ['view'],
        ],

        'kasir' => [
            'dashboard'    => ['view'],
            'pos'          => ['view', 'create'],
            'inventory'    => ['view'],
            'sales'        => ['view'],
            'invoices'     => ['view'],
            'loyalty'      => ['view', 'create', 'edit'],
            'reminders'    => ['view', 'create', 'delete'],
        ],

        'gudang' => [
            'dashboard'    => ['view'],
            'inventory'    => ['view', 'create', 'edit', 'delete'],
            'products'     => ['view'],
            'warehouses'   => ['view', 'create', 'edit', 'delete'],
            'purchasing'   => ['view'],
            'production'   => ['view', 'create', 'edit'],
            'manufacturing' => ['view', 'create', 'edit'],
            'fleet'         => ['view', 'create', 'edit'],
            'consignment'   => ['view', 'create', 'edit'],
            'wms'           => ['view', 'create', 'edit'],
            'agriculture'   => ['view', 'create', 'edit'],
            'rab'           => ['view'],
            'documents'    => ['view', 'create'],
            'reminders'    => ['view', 'create', 'delete'],
        ],

        // Role hotel — housekeeping staff
        'housekeeping' => [
            'dashboard'  => ['view'],
            'reminders'  => ['view', 'create', 'delete'],
            'documents'  => ['view'],
        ],

        // Role hotel — maintenance staff
        'maintenance' => [
            'dashboard'  => ['view'],
            'reminders'  => ['view', 'create', 'delete'],
            'documents'  => ['view'],
            'assets'     => ['view'],
        ],

        // Role affiliate
        'affiliate' => [
            'dashboard'  => ['view'],
        ],
    ];

    /**
     * Check if a user has a specific permission.
     * Priority: super_admin → admin wildcard → per-user override → role default
     */
    public function check(User $user, string $module, string $action): bool
    {
        // super_admin bypasses everything
        if ($user->isSuperAdmin()) {
            return true;
        }

        // admin gets everything within their tenant
        if ($user->isAdmin()) {
            return true;
        }

        // Check per-user override (cached per user)
        $override = $this->getUserOverride($user, $module, $action);
        if ($override !== null) {
            return $override;
        }

        // Fall back to role defaults
        return $this->roleDefault($user->role, $module, $action);
    }

    /**
     * Get per-user override from DB (null = no override set).
     */
    private function getUserOverride(User $user, string $module, string $action): ?bool
    {
        // Store as plain array to avoid Eloquent Collection serialization issues
        $permissions = Cache::remember(
            "user_perms_v2:{$user->id}",
            now()->addMinutes(10),
            fn() => UserPermission::where('user_id', $user->id)
                ->get()
                ->map(fn($p) => ['module' => $p->module, 'action' => $p->action, 'granted' => (bool) $p->granted])
                ->toArray()
        );

        foreach ($permissions as $p) {
            if ($p['module'] === $module && $p['action'] === $action) {
                return $p['granted'];
            }
        }

        return null;
    }

    /**
     * Check role default.
     */
    public function roleDefault(string $role, string $module, string $action): bool
    {
        $defaults = self::ROLE_DEFAULTS[$role] ?? [];

        if ($defaults === '*') {
            return true;
        }

        return in_array($action, $defaults[$module] ?? []);
    }

    /**
     * Get all permissions for a user (merged role defaults + overrides).
     * Returns ['module' => ['action' => bool]]
     */
    public function getUserPermissions(User $user): array
    {
        $result = [];

        foreach (self::MODULES as $module => $actions) {
            foreach ($actions as $action) {
                $result[$module][$action] = $this->check($user, $module, $action);
            }
        }

        return $result;
    }

    /**
     * Save per-user permission overrides.
     * $permissions = ['sales.view' => true, 'sales.delete' => false, ...]
     */
    public function saveUserPermissions(User $user, array $permissions): void
    {
        foreach ($permissions as $key => $granted) {
            [$module, $action] = explode('.', $key, 2);

            if (! isset(self::MODULES[$module]) || ! in_array($action, self::MODULES[$module])) {
                continue;
            }

            UserPermission::updateOrCreate(
                ['user_id' => $user->id, 'module' => $module, 'action' => $action],
                ['tenant_id' => $user->tenant_id, 'granted' => (bool) $granted]
            );
        }

        // Bust cache
        Cache::forget("user_perms_v2:{$user->id}");
    }

    /**
     * Reset all per-user overrides (revert to role defaults).
     */
    public function resetUserPermissions(User $user): void
    {
        UserPermission::where('user_id', $user->id)->delete();
        Cache::forget("user_perms_v2:{$user->id}");
    }

    /**
     * Module display labels (Bahasa Indonesia).
     */
    public static function moduleLabel(string $module): string
    {
        return match($module) {
            'dashboard'      => 'Dashboard',
            'customers'      => 'Data Customer',
            'suppliers'      => 'Data Supplier',
            'sales'          => 'Sales Order',
            'invoices'       => 'Invoice',
            'quotations'     => 'Penawaran Harga',
            'delivery'       => 'Surat Jalan',
            'sales_returns'  => 'Retur Penjualan',
            'down_payments'  => 'Uang Muka (DP)',
            'price_lists'    => 'Daftar Harga',
            'receivables'    => 'Piutang & Hutang',
            'purchasing'     => 'Pembelian',
            'inventory'      => 'Inventori',
            'products'       => 'Data Produk',
            'warehouses'     => 'Data Gudang',
            'pos'            => 'Point of Sale',
            'hrm'            => 'SDM (HRM)',
            'payroll'        => 'Penggajian',
            'crm'            => 'CRM',
            'projects'       => 'Proyek',
            'production'     => 'Produksi',
            'manufacturing'  => 'Manufaktur (BOM/MRP)',
            'fleet'          => 'Fleet Management',
            'contracts'      => 'Manajemen Kontrak',
            'landed_cost'    => 'Landed Cost',
            'consignment'    => 'Konsinyasi',
            'commission'     => 'Komisi Sales',
            'helpdesk'       => 'Helpdesk & Tiket',
            'project_billing'=> 'Project Billing',
            'subscription_billing' => 'Subscription Billing',
            'reimbursement'  => 'Reimbursement',
            'wms'            => 'WMS (Gudang Lanjutan)',
            'timesheets'     => 'Timesheet',
            'assets'         => 'Aset',
            'accounting'     => 'Akuntansi',
            'journals'       => 'Jurnal GL',
            'budget'         => 'Anggaran',
            'reports'        => 'Laporan',
            'kpi'            => 'KPI Dashboard',
            'expenses'       => 'Pengeluaran',
            'documents'      => 'Dokumen',
            'reminders'      => 'Pengingat',
            'loyalty'        => 'Loyalitas',
            'ecommerce'      => 'E-Commerce',
            'shipping'       => 'Pengiriman',
            'approvals'      => 'Persetujuan',
            'audit'          => 'Audit Trail',
            'users'          => 'Manajemen User',
            'taxes'          => 'Pajak',
            'bank'           => 'Rekening & Rekonsiliasi Bank',
            'import'         => 'Import Data',
            'anomalies'      => 'Deteksi Anomali',
            'simulations'    => 'Simulasi Keuangan',
            'writeoffs'      => 'Penghapusan Piutang',
            'deferred'       => 'Amortisasi / Deferral',
            'bulk_payments'  => 'Bulk Payment',
            'cost_centers'   => 'Pusat Biaya',
            'constraints'    => 'Batasan Bisnis',
            'custom_fields'  => 'Custom Fields',
            'company_groups' => 'Grup Perusahaan',
            'zero_input'     => 'Input Cerdas (AI)',
            'agriculture'    => 'Pertanian / Lahan',
            'rab'            => 'RAB (Rencana Anggaran Biaya)',
            'overtime'       => 'Lembur',
            'training'       => 'Pelatihan & Sertifikasi',
            'disciplinary'   => 'Surat Peringatan',
            default          => ucfirst(str_replace('_', ' ', $module)),
        };
    }

    /**
     * Module category groupings for UI display.
     */
    public static function moduleCategories(): array
    {
        return [
            'Penjualan' => ['sales', 'invoices', 'quotations', 'delivery', 'sales_returns', 'down_payments', 'price_lists', 'crm', 'loyalty', 'pos', 'commission', 'helpdesk', 'subscription_billing'],
            'Inventori & Pembelian' => ['inventory', 'products', 'warehouses', 'customers', 'suppliers', 'purchasing', 'consignment', 'wms'],
            'Operasional' => ['production', 'manufacturing', 'fleet', 'contracts', 'shipping', 'approvals', 'ecommerce', 'documents', 'projects', 'timesheets', 'project_billing', 'agriculture', 'rab'],
            'SDM & Penggajian' => ['hrm', 'payroll', 'overtime', 'training', 'disciplinary', 'reimbursement'],
            'Keuangan' => ['expenses', 'receivables', 'bank', 'budget', 'assets', 'accounting', 'journals', 'deferred', 'writeoffs', 'bulk_payments', 'cost_centers', 'landed_cost'],
            'Analitik & AI' => ['reports', 'kpi', 'anomalies', 'simulations', 'zero_input'],
            'Pengaturan' => ['users', 'taxes', 'import', 'audit', 'reminders', 'custom_fields', 'constraints', 'company_groups'],
            'Lainnya' => ['dashboard'],
        ];
    }

    /**
     * Action display labels.
     */
    public static function actionLabel(string $action): string
    {
        return match($action) {
            'view'   => 'Lihat',
            'create' => 'Tambah',
            'edit'   => 'Edit',
            'delete' => 'Hapus',
            default  => ucfirst($action),
        };
    }
}
