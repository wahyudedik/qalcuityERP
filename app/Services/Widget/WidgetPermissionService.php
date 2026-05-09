<?php

namespace App\Services\Widget;

use App\Models\User;

/**
 * WidgetPermissionService — Mengelola izin akses widget berdasarkan role pengguna.
 *
 * Menggunakan sistem permission yang sudah ada di Qalcuity ERP:
 * - $user->isSuperAdmin() / $user->isAdmin() untuk role tinggi
 * - $user->hasPermission(module, action) untuk pengecekan izin granular
 * - Multi-tenant isolation via BelongsToTenant trait pada model
 *
 * Aturan akses:
 * - super_admin & admin: akses penuh ke semua widget dan halaman
 * - manager: akses ke semua widget pada halaman yang diizinkan
 * - staff/kasir/gudang: akses terbatas berdasarkan modul yang diizinkan
 * - custom roles: mengikuti permission granular dari CustomRole
 */
class WidgetPermissionService
{
    /**
     * Mapping halaman widget ke modul permission yang diperlukan.
     * User harus memiliki izin 'view' pada modul terkait untuk mengakses halaman.
     */
    private const PAGE_MODULE_MAP = [
        'notifications' => null, // Semua user yang terautentikasi dapat mengakses notifikasi
        'room-availability' => 'inventory', // Menggunakan modul inventory untuk hotel/room
        'reports' => 'reports',
        'anomalies' => 'anomalies',
        'simulations' => 'simulations',
    ];

    /**
     * Widget yang memerlukan izin khusus di atas izin halaman.
     * Format: [widgetType => [page => [permission_module, permission_action]]]
     */
    private const RESTRICTED_WIDGETS = [
        'chart-results' => [
            'simulations' => ['simulations', 'view'],
        ],
        'anomaly-stats' => [
            'anomalies' => ['anomalies', 'view'],
        ],
        'report-stats' => [
            'reports' => ['reports', 'view'],
        ],
        'chart-usage' => [
            'reports' => ['reports', 'view'],
        ],
        'maintenance-schedule' => [
            'room-availability' => ['inventory', 'edit'],
        ],
        'chart-occupancy' => [
            'room-availability' => ['inventory', 'view'],
        ],
        'simulation-history' => [
            'simulations' => ['simulations', 'view'],
        ],
        'top-types' => [
            'anomalies' => ['anomalies', 'view'],
        ],
    ];

    /**
     * Daftar semua widget yang tersedia per halaman.
     */
    private const PAGE_WIDGETS = [
        'notifications' => ['summary', 'quick-actions', 'chart-trends', 'recent-items'],
        'room-availability' => ['room-summary', 'quick-actions', 'chart-occupancy', 'maintenance-schedule'],
        'reports' => ['report-stats', 'quick-actions', 'chart-usage', 'favorites'],
        'anomalies' => ['anomaly-stats', 'quick-actions', 'chart-trends', 'top-types'],
        'simulations' => ['simulation-history', 'quick-actions', 'chart-results', 'templates'],
    ];

    /**
     * Role yang diizinkan mengelola widget (tambah, hapus, atur ulang).
     */
    private const MANAGEMENT_ROLES = ['super_admin', 'admin', 'manager'];

    /**
     * Menentukan apakah pengguna dapat mengakses halaman widget tertentu.
     *
     * @param  User    $user  Pengguna yang diperiksa
     * @param  string  $page  Nama halaman
     * @return bool True jika pengguna dapat mengakses halaman
     */
    public function canAccessPage(User $user, string $page): bool
    {
        // Admin dan super_admin selalu dapat mengakses semua halaman
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        $requiredModule = self::PAGE_MODULE_MAP[$page] ?? null;

        // Halaman tanpa modul terkait (seperti notifications) terbuka untuk semua
        if ($requiredModule === null) {
            return true;
        }

        // Cek izin modul via UnifiedPermissionService
        return $user->hasPermission($requiredModule, 'view');
    }

    /**
     * Menentukan apakah pengguna dapat melihat widget tertentu pada halaman tertentu.
     *
     * Aturan akses:
     * 1. Admin/super_admin selalu dapat melihat semua widget
     * 2. User harus memiliki akses ke halaman terlebih dahulu
     * 3. Widget dengan pembatasan khusus memerlukan izin tambahan
     * 4. Widget tanpa pembatasan dapat dilihat oleh semua user yang memiliki akses halaman
     *
     * @param  User    $user        Pengguna yang diperiksa
     * @param  string  $widgetType  Tipe widget
     * @param  string  $page        Nama halaman
     * @return bool True jika pengguna dapat melihat widget
     */
    public function canViewWidget(User $user, string $widgetType, string $page): bool
    {
        // Admin dan super_admin selalu dapat melihat semua widget
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        // User harus memiliki akses ke halaman terlebih dahulu
        if (! $this->canAccessPage($user, $page)) {
            return false;
        }

        // Cek apakah widget ini memerlukan izin khusus pada halaman ini
        $restriction = self::RESTRICTED_WIDGETS[$widgetType][$page] ?? null;

        if ($restriction === null) {
            // Tidak ada pembatasan khusus — semua pengguna dengan akses halaman dapat melihat
            return true;
        }

        [$module, $action] = $restriction;

        // Cek izin granular via UnifiedPermissionService
        return $user->hasPermission($module, $action);
    }

    /**
     * Menentukan apakah pengguna dapat menambahkan widget ke halaman.
     *
     * @param  User    $user        Pengguna yang diperiksa
     * @param  string  $widgetType  Tipe widget yang akan ditambahkan
     * @param  string  $page        Nama halaman target
     * @return bool True jika pengguna dapat menambahkan widget
     */
    public function canAddWidget(User $user, string $widgetType, string $page): bool
    {
        // Harus bisa mengelola widget
        if (! $this->canManageWidgets($user)) {
            return false;
        }

        // Harus bisa melihat widget tersebut
        return $this->canViewWidget($user, $widgetType, $page);
    }

    /**
     * Menentukan apakah pengguna dapat menghapus widget dari halaman.
     *
     * @param  User    $user        Pengguna yang diperiksa
     * @param  string  $widgetType  Tipe widget yang akan dihapus
     * @param  string  $page        Nama halaman
     * @return bool True jika pengguna dapat menghapus widget
     */
    public function canRemoveWidget(User $user, string $widgetType, string $page): bool
    {
        // Harus bisa mengelola widget
        if (! $this->canManageWidgets($user)) {
            return false;
        }

        // Harus memiliki akses ke halaman
        return $this->canAccessPage($user, $page);
    }

    /**
     * Menentukan apakah pengguna dapat mengelola widget (tambah, hapus, atur ulang).
     *
     * Pengguna dengan role admin, super_admin, atau manager dapat mengelola widget.
     * Staff dan role lainnya hanya dapat melihat widget yang sudah dikonfigurasi.
     *
     * @param  User  $user  Pengguna yang diperiksa
     * @return bool True jika pengguna dapat mengelola widget
     */
    public function canManageWidgets(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        return $user->hasRole(self::MANAGEMENT_ROLES);
    }

    /**
     * Mengambil daftar tipe widget yang dapat dilihat pengguna pada halaman tertentu.
     *
     * @param  User    $user  Pengguna yang diperiksa
     * @param  string  $page  Nama halaman
     * @return array  Array tipe widget yang diizinkan
     */
    public function getPermittedWidgets(User $user, string $page): array
    {
        // Jika user tidak bisa mengakses halaman, tidak ada widget yang diizinkan
        if (! $this->canAccessPage($user, $page)) {
            return [];
        }

        $allWidgets = self::PAGE_WIDGETS[$page] ?? [];

        if (empty($allWidgets)) {
            return [];
        }

        return array_values(
            array_filter(
                $allWidgets,
                fn(string $widgetType) => $this->canViewWidget($user, $widgetType, $page)
            )
        );
    }

    /**
     * Mengambil daftar halaman yang dapat diakses pengguna.
     *
     * @param  User  $user  Pengguna yang diperiksa
     * @return array Array nama halaman yang diizinkan
     */
    public function getAccessiblePages(User $user): array
    {
        return array_values(
            array_filter(
                array_keys(self::PAGE_MODULE_MAP),
                fn(string $page) => $this->canAccessPage($user, $page)
            )
        );
    }

    /**
     * Memfilter koleksi widget berdasarkan izin pengguna.
     * Berguna untuk memfilter hasil dari WidgetManagerService.
     *
     * @param  User    $user     Pengguna yang diperiksa
     * @param  string  $page     Nama halaman
     * @param  array   $widgets  Array widget types untuk difilter
     * @return array Array widget types yang diizinkan
     */
    public function filterWidgetsByPermission(User $user, string $page, array $widgets): array
    {
        return array_values(
            array_filter(
                $widgets,
                fn(string $widgetType) => $this->canViewWidget($user, $widgetType, $page)
            )
        );
    }
}
