<?php

namespace App\Services\Layout;

use App\DTOs\Layout\PageLayout;
use App\Models\User;
use App\Models\UserLayoutPreference;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LayoutEngineService
{
    /**
     * Cache TTL untuk layout preferences (5 menit)
     */
    private const CACHE_TTL = 300;

    /**
     * Default layouts untuk setiap halaman
     */
    private const DEFAULT_LAYOUTS = [
        'notifications' => [
            'columns' => [60, 40],
            'widgets' => [
                'main' => ['notification-list'],
                'sidebar' => ['notification-summary', 'notification-quick-actions', 'notification-trends'],
            ],
            'breakpoints' => [
                'mobile' => ['columns' => [100], 'sidebar_collapsed' => true],
                'tablet' => ['columns' => [70, 30], 'sidebar_collapsed' => false],
                'desktop' => ['columns' => [60, 40], 'sidebar_collapsed' => false],
            ],
        ],
        'room-availability' => [
            'columns' => [100],
            'widgets' => [
                'main' => ['room-grid', 'room-summary'],
                'additional' => ['occupancy-chart', 'room-actions'],
            ],
            'breakpoints' => [
                'mobile' => ['grid_columns' => 1],
                'tablet' => ['grid_columns' => 2],
                'desktop' => ['grid_columns' => 4],
            ],
        ],
        'export-reports' => [
            'columns' => [70, 30],
            'widgets' => [
                'main' => ['report-cards'],
                'sidebar' => ['report-analytics', 'report-quick-actions', 'favorite-reports'],
            ],
            'breakpoints' => [
                'mobile' => ['columns' => [100], 'sidebar_collapsed' => true],
                'tablet' => ['columns' => [65, 35], 'sidebar_collapsed' => false],
                'desktop' => ['columns' => [70, 30], 'sidebar_collapsed' => false],
            ],
        ],
        'anomaly-detection' => [
            'columns' => [65, 35],
            'widgets' => [
                'main' => ['anomaly-list'],
                'sidebar' => ['anomaly-summary', 'anomaly-trends', 'anomaly-actions'],
            ],
            'breakpoints' => [
                'mobile' => ['columns' => [100], 'sidebar_collapsed' => true],
                'tablet' => ['columns' => [60, 40], 'sidebar_collapsed' => false],
                'desktop' => ['columns' => [65, 35], 'sidebar_collapsed' => false],
            ],
        ],
        'business-simulation' => [
            'columns' => [60, 40],
            'widgets' => [
                'main' => ['simulation-card'],
                'sidebar' => ['simulation-history', 'simulation-templates', 'simulation-analytics'],
            ],
            'breakpoints' => [
                'mobile' => ['columns' => [100], 'sidebar_collapsed' => true],
                'tablet' => ['columns' => [65, 35], 'sidebar_collapsed' => false],
                'desktop' => ['columns' => [60, 40], 'sidebar_collapsed' => false],
            ],
        ],
    ];

    /**
     * Mengambil layout halaman untuk user tertentu
     *
     * @param  string  $page  Nama halaman (notifications, room-availability, dll)
     * @param  User  $user  User yang meminta layout
     * @return PageLayout Layout halaman yang dikonfigurasi
     */
    public function getPageLayout(string $page, User $user): PageLayout
    {
        $cacheKey = "layout.{$user->tenant_id}.{$user->id}.{$page}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($page, $user) {
            // Cari preferensi user terlebih dahulu
            $preference = UserLayoutPreference::where('user_id', $user->id)
                ->where('page', $page)
                ->first();

            if ($preference && $this->validateLayout($preference->layout_config)) {
                return new PageLayout(
                    page: $page,
                    columns: $preference->layout_config['columns'] ?? [],
                    widgets: $preference->layout_config['widgets'] ?? [],
                    breakpoints: $preference->breakpoint_config ?? []
                );
            }

            // Fallback ke default layout
            return $this->getDefaultLayout($page);
        });
    }

    /**
     * Menyimpan preferensi layout user
     *
     * @param  User  $user  User yang menyimpan preferensi
     * @param  string  $page  Nama halaman
     * @param  array  $layout  Konfigurasi layout
     *
     * @throws \InvalidArgumentException Jika layout tidak valid
     */
    public function saveUserPreferences(User $user, string $page, array $layout): void
    {
        if (! $this->validateLayout($layout)) {
            throw new \InvalidArgumentException("Layout configuration tidak valid untuk halaman {$page}");
        }

        try {
            UserLayoutPreference::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'page' => $page,
                ],
                [
                    'layout_config' => [
                        'columns' => $layout['columns'] ?? [],
                        'widgets' => $layout['widgets'] ?? [],
                    ],
                    'breakpoint_config' => $layout['breakpoints'] ?? [],
                ]
            );

            // Clear cache setelah update
            $cacheKey = "layout.{$user->tenant_id}.{$user->id}.{$page}";
            Cache::forget($cacheKey);

            Log::info('Layout preferences saved', [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'page' => $page,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save layout preferences', [
                'user_id' => $user->id,
                'page' => $page,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Mengambil layout default untuk halaman tertentu
     *
     * @param  string  $page  Nama halaman
     * @return PageLayout Layout default
     *
     * @throws \InvalidArgumentException Jika halaman tidak dikenal
     */
    public function getDefaultLayout(string $page): PageLayout
    {
        if (! isset(self::DEFAULT_LAYOUTS[$page])) {
            throw new \InvalidArgumentException("Halaman '{$page}' tidak memiliki layout default");
        }

        $config = self::DEFAULT_LAYOUTS[$page];

        return new PageLayout(
            page: $page,
            columns: $config['columns'],
            widgets: $config['widgets'],
            breakpoints: $config['breakpoints']
        );
    }

    /**
     * Memvalidasi konfigurasi layout
     *
     * @param  array  $layout  Konfigurasi layout yang akan divalidasi
     * @return bool True jika valid, false jika tidak
     */
    public function validateLayout(array $layout): bool
    {
        // Validasi struktur dasar
        if (! is_array($layout)) {
            return false;
        }

        // Validasi columns - harus array numerik
        if (isset($layout['columns'])) {
            if (! is_array($layout['columns'])) {
                return false;
            }

            // Validasi bahwa total kolom tidak melebihi 100%
            $total = array_sum($layout['columns']);
            if ($total > 100) {
                return false;
            }

            // Validasi bahwa setiap kolom adalah angka positif
            foreach ($layout['columns'] as $column) {
                if (! is_numeric($column) || $column <= 0) {
                    return false;
                }
            }
        }

        // Validasi widgets - harus array dengan key yang valid
        if (isset($layout['widgets'])) {
            if (! is_array($layout['widgets'])) {
                return false;
            }

            $validAreas = ['main', 'sidebar', 'additional', 'header', 'footer'];
            foreach ($layout['widgets'] as $area => $widgets) {
                if (! in_array($area, $validAreas)) {
                    return false;
                }

                if (! is_array($widgets)) {
                    return false;
                }

                // Validasi bahwa setiap widget adalah string
                foreach ($widgets as $widget) {
                    if (! is_string($widget)) {
                        return false;
                    }
                }
            }
        }

        // Validasi breakpoints
        if (isset($layout['breakpoints'])) {
            if (! is_array($layout['breakpoints'])) {
                return false;
            }

            $validBreakpoints = ['mobile', 'tablet', 'desktop'];
            foreach ($layout['breakpoints'] as $breakpoint => $config) {
                if (! in_array($breakpoint, $validBreakpoints)) {
                    return false;
                }

                if (! is_array($config)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Mengambil daftar halaman yang didukung
     *
     * @return Collection<string> Daftar nama halaman
     */
    public function getSupportedPages(): Collection
    {
        return collect(array_keys(self::DEFAULT_LAYOUTS));
    }

    /**
     * Reset layout user ke default
     *
     * @param  User  $user  User yang akan direset
     * @param  string  $page  Nama halaman
     */
    public function resetToDefault(User $user, string $page): void
    {
        UserLayoutPreference::where('user_id', $user->id)
            ->where('page', $page)
            ->delete();

        // Clear cache
        $cacheKey = "layout.{$user->tenant_id}.{$user->id}.{$page}";
        Cache::forget($cacheKey);

        Log::info('Layout reset to default', [
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'page' => $page,
        ]);
    }

    /**
     * Mengambil statistik penggunaan layout
     *
     * @param  string  $page  Nama halaman
     * @return array Statistik penggunaan
     */
    public function getLayoutUsageStats(string $page): array
    {
        $totalUsers = UserLayoutPreference::where('page', $page)->count();
        $customizedUsers = UserLayoutPreference::where('page', $page)
            ->whereNotNull('layout_config')
            ->count();

        return [
            'total_users' => $totalUsers,
            'customized_users' => $customizedUsers,
            'customization_rate' => $totalUsers > 0 ? ($customizedUsers / $totalUsers) * 100 : 0,
        ];
    }
}
