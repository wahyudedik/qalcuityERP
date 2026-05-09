<?php

namespace App\Services\Widget;

use App\Models\User;
use App\Models\UserWidgetPreference;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WidgetManagerService
{
    /**
     * @var WidgetPermissionService
     */
    private WidgetPermissionService $permissionService;

    public function __construct(WidgetPermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Cache TTL untuk widget data (5 menit)
     */
    private const CACHE_TTL = 300;

    /**
     * Widget yang tersedia per halaman
     */
    private const PAGE_WIDGETS = [
        'notifications' => ['summary', 'quick-actions', 'chart-trends', 'recent-items'],
        'room-availability' => ['room-summary', 'quick-actions', 'chart-occupancy', 'maintenance-schedule'],
        'reports' => ['report-stats', 'quick-actions', 'chart-usage', 'favorites'],
        'anomalies' => ['anomaly-stats', 'quick-actions', 'chart-trends', 'top-types'],
        'simulations' => ['simulation-history', 'quick-actions', 'chart-results', 'templates'],
    ];

    /**
     * Konfigurasi default widget per tipe
     */
    private const DEFAULT_WIDGET_CONFIGS = [
        'summary' => ['show_unread' => true, 'show_priority' => true, 'show_total' => true],
        'quick-actions' => ['max_actions' => 4, 'show_icons' => true],
        'chart-trends' => ['period_days' => 7, 'chart_type' => 'line'],
        'recent-items' => ['limit' => 10, 'show_timestamp' => true],
        'room-summary' => ['show_by_category' => true, 'show_maintenance' => true],
        'chart-occupancy' => ['period_days' => 7, 'chart_type' => 'bar'],
        'maintenance-schedule' => ['upcoming_days' => 7, 'show_overdue' => true],
        'report-stats' => ['show_frequency' => true, 'show_last_run' => true],
        'chart-usage' => ['period_days' => 30, 'chart_type' => 'bar'],
        'favorites' => ['limit' => 5, 'show_last_accessed' => true],
        'anomaly-stats' => ['show_by_severity' => true, 'show_resolved' => true],
        'chart-results' => ['show_comparison' => true, 'chart_type' => 'line'],
        'top-types' => ['limit' => 5, 'show_percentage' => true],
        'simulation-history' => ['limit' => 5, 'show_status' => true],
        'templates' => ['show_favorites' => true, 'limit' => 10],
    ];

    /**
     * Mengambil daftar widget yang tersedia untuk halaman tertentu,
     * difilter berdasarkan tipe halaman dan izin pengguna.
     *
     * @param  string  $page  Nama halaman (notifications, room-availability, dll)
     * @param  User|null  $user  User untuk pengecekan izin (opsional, jika null tidak difilter)
     * @return Collection<int, array> Koleksi widget yang tersedia
     */
    public function getAvailableWidgets(string $page, ?User $user = null): Collection
    {
        if (! isset(self::PAGE_WIDGETS[$page])) {
            Log::warning('WidgetManagerService: halaman tidak dikenal', ['page' => $page]);

            return collect();
        }

        $widgets = collect(self::PAGE_WIDGETS[$page])->map(function (string $widgetType) use ($page) {
            return [
                'type' => $widgetType,
                'page' => $page,
                'default_config' => self::DEFAULT_WIDGET_CONFIGS[$widgetType] ?? [],
                'label' => $this->getWidgetLabel($widgetType),
            ];
        });

        // Filter berdasarkan izin pengguna jika user diberikan
        if ($user !== null) {
            $permittedTypes = $this->permissionService->getPermittedWidgets($user, $page);

            $widgets = $widgets->filter(
                fn(array $widget) => in_array($widget['type'], $permittedTypes, true)
            )->values();
        }

        return $widgets;
    }

    /**
     * Mengambil widget yang aktif milik user untuk halaman tertentu.
     * Jika user belum memiliki preferensi, fallback ke widget default.
     * Hasil difilter berdasarkan izin pengguna.
     *
     * @param  User  $user  User yang diminta widget-nya
     * @param  string  $page  Nama halaman
     * @return Collection<int, UserWidgetPreference> Koleksi widget preferences
     */
    public function getUserWidgets(User $user, string $page): Collection
    {
        // Cek akses halaman terlebih dahulu
        if (! $this->permissionService->canAccessPage($user, $page)) {
            return collect();
        }

        $widgets = UserWidgetPreference::where('user_id', $user->id)
            ->forPage($page)
            ->active()
            ->ordered()
            ->get();

        if ($widgets->isEmpty()) {
            $widgets = $this->createDefaultWidgets($user, $page);
        }

        // Filter widget berdasarkan izin pengguna
        $permittedTypes = $this->permissionService->getPermittedWidgets($user, $page);

        return $widgets->filter(
            fn(UserWidgetPreference $widget) => in_array($widget->widget_type, $permittedTypes, true)
        )->values();
    }

    /**
     * Menambahkan widget baru ke halaman user dengan validasi dan pengecekan izin.
     *
     * @param  User  $user  User yang menambahkan widget
     * @param  string  $page  Nama halaman target
     * @param  string  $widgetType  Tipe widget yang akan ditambahkan
     * @param  array  $config  Konfigurasi tambahan widget
     * @return UserWidgetPreference Widget preference yang baru dibuat
     *
     * @throws \InvalidArgumentException Jika widget tidak valid atau tidak kompatibel dengan halaman
     * @throws \App\Exceptions\InsufficientPlanException Jika user tidak memiliki izin
     */
    public function addWidget(User $user, string $page, string $widgetType, array $config = []): UserWidgetPreference
    {
        // Validasi halaman didukung
        if (! isset(self::PAGE_WIDGETS[$page])) {
            throw new \InvalidArgumentException("Halaman '{$page}' tidak didukung oleh Widget Manager.");
        }

        // Validasi widget kompatibel dengan halaman
        if (! in_array($widgetType, self::PAGE_WIDGETS[$page], true)) {
            throw new \InvalidArgumentException(
                "Widget '{$widgetType}' tidak tersedia untuk halaman '{$page}'. "
                    . 'Widget yang tersedia: ' . implode(', ', self::PAGE_WIDGETS[$page])
            );
        }

        // Cek izin pengguna untuk menambahkan widget
        if (! $this->permissionService->canAddWidget($user, $widgetType, $page)) {
            throw new \InvalidArgumentException(
                "Anda tidak memiliki izin untuk menambahkan widget '{$widgetType}' pada halaman '{$page}'."
            );
        }

        // Cek apakah widget sudah ada (hindari duplikat aktif)
        $existing = UserWidgetPreference::where('user_id', $user->id)
            ->where('page', $page)
            ->where('widget_type', $widgetType)
            ->first();

        if ($existing) {
            // Aktifkan kembali jika sebelumnya dinonaktifkan
            if (! $existing->is_active) {
                $existing->update(['is_active' => true]);
            }

            return $existing;
        }

        // Tentukan posisi berikutnya
        $nextPosition = (int) (UserWidgetPreference::where('user_id', $user->id)
            ->where('page', $page)
            ->max('position') ?? -1) + 1;

        // Gabungkan config default dengan config yang diberikan
        $mergedConfig = array_merge(
            self::DEFAULT_WIDGET_CONFIGS[$widgetType] ?? [],
            $config
        );

        $widget = UserWidgetPreference::create([
            'user_id' => $user->id,
            'page' => $page,
            'widget_type' => $widgetType,
            'widget_config' => $mergedConfig,
            'position' => $nextPosition,
            'is_active' => true,
        ]);

        Log::info('Widget ditambahkan', [
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'page' => $page,
            'widget_type' => $widgetType,
        ]);

        return $widget;
    }

    /**
     * Menghapus widget dari halaman user dan membersihkan cache terkait.
     *
     * @param  User  $user  User pemilik widget
     * @param  int  $widgetId  ID widget yang akan dihapus
     * @return bool True jika berhasil dihapus, false jika widget tidak ditemukan atau tidak diizinkan
     */
    public function removeWidget(User $user, int $widgetId): bool
    {
        $widget = UserWidgetPreference::where('id', $widgetId)
            ->where('user_id', $user->id)
            ->first();

        if (! $widget) {
            Log::warning('WidgetManagerService: widget tidak ditemukan untuk dihapus', [
                'user_id' => $user->id,
                'widget_id' => $widgetId,
            ]);

            return false;
        }

        // Cek izin pengguna untuk menghapus widget
        if (! $this->permissionService->canRemoveWidget($user, $widget->widget_type, $widget->page)) {
            Log::warning('WidgetManagerService: user tidak memiliki izin menghapus widget', [
                'user_id' => $user->id,
                'widget_id' => $widgetId,
                'widget_type' => $widget->widget_type,
                'page' => $widget->page,
            ]);

            return false;
        }

        // Hapus cache data widget sebelum menghapus record
        $cacheKey = $this->buildWidgetCacheKey($user->tenant_id, $widgetId, $widget->widget_type);
        Cache::forget($cacheKey);

        $widget->delete();

        Log::info('Widget dihapus', [
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'widget_id' => $widgetId,
            'widget_type' => $widget->widget_type,
            'page' => $widget->page,
        ]);

        return true;
    }

    /**
     * Memperbarui konfigurasi widget dengan validasi.
     *
     * @param  int  $widgetId  ID widget yang akan diperbarui
     * @param  array  $config  Konfigurasi baru (akan di-merge dengan config yang ada)
     * @return bool True jika berhasil diperbarui
     *
     * @throws \InvalidArgumentException Jika konfigurasi tidak valid
     * @throws \RuntimeException Jika widget tidak ditemukan
     */
    public function updateWidgetConfig(int $widgetId, array $config): bool
    {
        $widget = UserWidgetPreference::with('user')->find($widgetId);

        if (! $widget) {
            throw new \RuntimeException("Widget dengan ID {$widgetId} tidak ditemukan.");
        }

        // Validasi config tidak boleh kosong
        if (empty($config)) {
            throw new \InvalidArgumentException('Konfigurasi widget tidak boleh kosong.');
        }

        // Validasi semua value harus scalar, array, atau null
        foreach ($config as $key => $value) {
            if (! is_scalar($value) && ! is_array($value) && ! is_null($value)) {
                throw new \InvalidArgumentException(
                    "Nilai konfigurasi untuk key '{$key}' tidak valid."
                );
            }
        }

        // Gabungkan dengan config yang sudah ada
        $mergedConfig = array_merge($widget->widget_config ?? [], $config);

        $updated = $widget->update(['widget_config' => $mergedConfig]);

        if ($updated) {
            // Invalidasi cache data widget agar data segar diambil ulang
            $tenantId = $widget->user?->tenant_id ?? 0;
            $cacheKey = $this->buildWidgetCacheKey($tenantId, $widgetId, $widget->widget_type);
            Cache::forget($cacheKey);

            Log::info('Konfigurasi widget diperbarui', [
                'widget_id' => $widgetId,
                'widget_type' => $widget->widget_type,
            ]);
        }

        return $updated;
    }

    /**
     * Mengambil data widget dengan Redis caching (TTL 5 menit).
     *
     * @param  UserWidgetPreference  $widget  Widget preference yang datanya diminta
     * @return array Data widget yang sudah di-cache atau segar dari provider
     */
    public function getWidgetData(UserWidgetPreference $widget): array
    {
        $tenantId = $widget->user?->tenant_id ?? 0;
        $cacheKey = $this->buildWidgetCacheKey($tenantId, $widget->id, $widget->widget_type);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($widget) {
            try {
                return $this->fetchWidgetData($widget);
            } catch (\Throwable $e) {
                Log::error('Gagal mengambil data widget', [
                    'widget_id' => $widget->id,
                    'widget_type' => $widget->widget_type,
                    'page' => $widget->page,
                    'error' => $e->getMessage(),
                ]);

                return [
                    'error' => true,
                    'message' => 'Data widget tidak dapat dimuat.',
                    'widget_type' => $widget->widget_type,
                ];
            }
        });
    }

    /**
     * Mengambil daftar halaman yang didukung oleh Widget Manager.
     *
     * @return Collection<int, string> Daftar nama halaman
     */
    public function getSupportedPages(): Collection
    {
        return collect(array_keys(self::PAGE_WIDGETS));
    }

    /**
     * Mengambil instance WidgetPermissionService.
     *
     * @return WidgetPermissionService
     */
    public function getPermissionService(): WidgetPermissionService
    {
        return $this->permissionService;
    }

    /**
     * Membuat widget default untuk user pada halaman tertentu.
     * Dipanggil saat user belum memiliki preferensi widget.
     *
     * @param  User  $user  User yang akan dibuatkan widget default
     * @param  string  $page  Nama halaman
     * @return Collection<int, UserWidgetPreference> Koleksi widget default
     */
    private function createDefaultWidgets(User $user, string $page): Collection
    {
        if (! isset(self::PAGE_WIDGETS[$page])) {
            return collect();
        }

        $widgets = collect();

        foreach (self::PAGE_WIDGETS[$page] as $position => $widgetType) {
            try {
                $widget = UserWidgetPreference::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'page' => $page,
                        'widget_type' => $widgetType,
                    ],
                    [
                        'widget_config' => self::DEFAULT_WIDGET_CONFIGS[$widgetType] ?? [],
                        'position' => $position,
                        'is_active' => true,
                    ]
                );

                $widgets->push($widget);
            } catch (\Throwable $e) {
                Log::warning('Gagal membuat widget default', [
                    'user_id' => $user->id,
                    'page' => $page,
                    'widget_type' => $widgetType,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $widgets->sortBy('position')->values();
    }

    /**
     * Mengambil data aktual dari provider berdasarkan tipe widget.
     * Specialized widget services (NotificationWidgetService, dll.) akan
     * mengisi data ini pada implementasi lanjutan.
     *
     * @param  UserWidgetPreference  $widget  Widget yang datanya diminta
     * @return array Data widget
     */
    private function fetchWidgetData(UserWidgetPreference $widget): array
    {
        $config = $widget->widget_config ?? [];

        return match ($widget->widget_type) {
            'summary', 'room-summary', 'report-stats', 'anomaly-stats' => [
                'widget_type' => $widget->widget_type,
                'page' => $widget->page,
                'config' => $config,
                'data' => [],
                'fetched_at' => now()->toIso8601String(),
            ],
            'quick-actions' => [
                'widget_type' => 'quick-actions',
                'page' => $widget->page,
                'config' => $config,
                'actions' => [],
                'fetched_at' => now()->toIso8601String(),
            ],
            'chart-trends', 'chart-occupancy', 'chart-usage', 'chart-results' => [
                'widget_type' => $widget->widget_type,
                'page' => $widget->page,
                'config' => $config,
                'labels' => [],
                'datasets' => [],
                'fetched_at' => now()->toIso8601String(),
            ],
            'recent-items', 'favorites', 'simulation-history', 'templates', 'top-types', 'maintenance-schedule' => [
                'widget_type' => $widget->widget_type,
                'page' => $widget->page,
                'config' => $config,
                'items' => [],
                'fetched_at' => now()->toIso8601String(),
            ],
            default => [
                'widget_type' => $widget->widget_type,
                'page' => $widget->page,
                'config' => $config,
                'fetched_at' => now()->toIso8601String(),
            ],
        };
    }

    /**
     * Membangun cache key untuk data widget.
     *
     * @param  int  $tenantId  ID tenant
     * @param  int  $widgetId  ID widget
     * @param  string  $widgetType  Tipe widget
     * @return string Cache key
     */
    private function buildWidgetCacheKey(int $tenantId, int $widgetId, string $widgetType): string
    {
        return "widget_data.{$tenantId}.{$widgetId}.{$widgetType}";
    }

    /**
     * Mengambil label yang dapat dibaca manusia untuk tipe widget.
     *
     * @param  string  $widgetType  Tipe widget
     * @return string Label widget dalam Bahasa Indonesia
     */
    private function getWidgetLabel(string $widgetType): string
    {
        return match ($widgetType) {
            'summary' => 'Ringkasan Notifikasi',
            'quick-actions' => 'Aksi Cepat',
            'chart-trends' => 'Grafik Tren',
            'recent-items' => 'Item Terbaru',
            'room-summary' => 'Ringkasan Kamar',
            'chart-occupancy' => 'Grafik Okupansi',
            'maintenance-schedule' => 'Jadwal Maintenance',
            'report-stats' => 'Statistik Laporan',
            'chart-usage' => 'Grafik Penggunaan',
            'favorites' => 'Laporan Favorit',
            'anomaly-stats' => 'Statistik Anomali',
            'top-types' => 'Jenis Anomali Teratas',
            'simulation-history' => 'Riwayat Simulasi',
            'chart-results' => 'Grafik Hasil Simulasi',
            'templates' => 'Template Simulasi',
            default => ucwords(str_replace('-', ' ', $widgetType)),
        };
    }
}
