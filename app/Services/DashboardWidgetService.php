<?php

namespace App\Services;

class DashboardWidgetService
{
    /**
     * Registry of all available dashboard widgets.
     *
     * Each widget: title, icon (SVG path), cols (grid span 1/2/4),
     * data_group (which data method to call), roles (who can use it).
     */
    public static function registry(): array
    {
        return [
            'pos_today' => [
                'title'      => 'Omzet POS Hari Ini',
                'icon'       => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                'icon_bg'    => 'bg-emerald-500/20',
                'icon_color' => 'text-emerald-400',
                'cols'       => 1,
                'data_group' => 'pos',
                'roles'      => ['admin', 'manager', 'kasir'],
            ],
            'kpi_revenue' => [
                'title'      => 'Pendapatan Bulan Ini',
                'icon'       => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'icon_bg'    => 'bg-blue-500/20',
                'icon_color' => 'text-blue-400',
                'cols'       => 1,
                'data_group' => 'finance',
                'roles'      => ['admin', 'manager', 'staff'],
            ],
            'kpi_orders' => [
                'title'      => 'Order Bulan Ini',
                'icon'       => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
                'icon_bg'    => 'bg-green-500/20',
                'icon_color' => 'text-green-400',
                'cols'       => 1,
                'data_group' => 'sales',
                'roles'      => ['admin', 'manager', 'kasir', 'staff'],
            ],
            'ecommerce_orders' => [
                'title'      => 'Order Marketplace',
                'icon'       => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
                'icon_bg'    => 'bg-orange-500/20',
                'icon_color' => 'text-orange-400',
                'cols'       => 1,
                'data_group' => 'ecommerce',
                'roles'      => ['admin', 'manager'],
            ],
            'kpi_low_stock' => [
                'title'      => 'Stok Menipis',
                'icon'       => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                'icon_bg'    => 'bg-red-500/20',
                'icon_color' => 'text-red-400',
                'cols'       => 1,
                'data_group' => 'inventory',
                'roles'      => ['admin', 'manager', 'gudang'],
            ],
            'kpi_attendance' => [
                'title'      => 'Kehadiran Hari Ini',
                'icon'       => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                'icon_bg'    => 'bg-purple-500/20',
                'icon_color' => 'text-purple-400',
                'cols'       => 1,
                'data_group' => 'hrm',
                'roles'      => ['admin', 'manager'],
            ],
            'chart_sales' => [
                'title'      => 'Penjualan 7 Hari',
                'icon'       => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                'icon_bg'    => 'bg-blue-500/20',
                'icon_color' => 'text-blue-400',
                'cols'       => 2,
                'data_group' => 'sales',
                'roles'      => ['admin', 'manager', 'kasir', 'staff'],
            ],
            'chart_finance' => [
                'title'      => 'Keuangan 6 Bulan',
                'icon'       => 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z',
                'icon_bg'    => 'bg-emerald-500/20',
                'icon_color' => 'text-emerald-400',
                'cols'       => 2,
                'data_group' => 'finance',
                'roles'      => ['admin', 'manager'],
            ],
            'low_stock_list' => [
                'title'      => 'Daftar Stok Menipis',
                'icon'       => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                'icon_bg'    => 'bg-red-500/20',
                'icon_color' => 'text-red-400',
                'cols'       => 2,
                'data_group' => 'inventory',
                'roles'      => ['admin', 'manager', 'gudang'],
            ],
            'quick_stats' => [
                'title'      => 'Ringkasan Cepat',
                'icon'       => 'M13 10V3L4 14h7v7l9-11h-7z',
                'icon_bg'    => 'bg-amber-500/20',
                'icon_color' => 'text-amber-400',
                'cols'       => 2,
                'data_group' => 'all',
                'roles'      => ['admin', 'manager', 'staff'],
            ],
            'ai_insights' => [
                'title'      => 'Insight AI',
                'icon'       => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
                'icon_bg'    => 'bg-indigo-500/20',
                'icon_color' => 'text-indigo-400',
                'cols'       => 4,
                'data_group' => 'insights',
                'roles'      => ['admin', 'manager'],
            ],
            'anomaly_alerts' => [
                'title'      => 'Anomali Terdeteksi',
                'icon'       => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                'icon_bg'    => 'bg-red-500/20',
                'icon_color' => 'text-red-400',
                'cols'       => 4,
                'data_group' => 'anomalies',
                'roles'      => ['admin', 'manager'],
            ],
            'gamification' => [
                'title'      => 'Achievement & Level',
                'icon'       => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
                'icon_bg'    => 'bg-yellow-500/20',
                'icon_color' => 'text-yellow-400',
                'cols'       => 2,
                'data_group' => 'gamification',
                'roles'      => ['admin', 'manager', 'staff', 'kasir', 'gudang'],
            ],
        ];
    }

    /**
     * Default widget layout per role.
     */
    public static function defaultsForRole(string $role): array
    {
        $keys = match ($role) {
            'admin'   => ['ai_insights', 'anomaly_alerts', 'kpi_revenue', 'kpi_orders', 'kpi_low_stock', 'kpi_attendance', 'chart_sales', 'chart_finance', 'low_stock_list', 'quick_stats', 'gamification'],
            'manager' => ['ai_insights', 'kpi_revenue', 'kpi_orders', 'kpi_low_stock', 'kpi_attendance', 'chart_sales', 'chart_finance', 'quick_stats', 'gamification'],
            'kasir'   => ['pos_today', 'kpi_orders', 'chart_sales', 'gamification'],
            'gudang'  => ['kpi_low_stock', 'low_stock_list', 'gamification'],
            'staff'   => ['kpi_revenue', 'kpi_orders', 'chart_sales', 'quick_stats', 'gamification'],
            default   => ['kpi_revenue', 'kpi_orders', 'chart_sales', 'gamification'],
        };

        $registry = self::registry();
        $widgets  = [];

        foreach ($keys as $i => $key) {
            if (isset($registry[$key])) {
                $widgets[] = ['key' => $key, 'order' => $i, 'visible' => true];
            }
        }

        return $widgets;
    }

    /**
     * All widgets available for a given role.
     */
    public static function availableForRole(string $role): array
    {
        return collect(self::registry())
            ->filter(fn($w) => in_array($role, $w['roles']))
            ->toArray();
    }

    /**
     * Determine which data groups need to be loaded for a set of visible widgets.
     */
    public static function requiredDataGroups(array $widgets): array
    {
        $registry = self::registry();
        $groups   = [];

        foreach ($widgets as $w) {
            if (($w['visible'] ?? false) && isset($registry[$w['key']])) {
                $group = $registry[$w['key']]['data_group'];
                if ($group === 'all') {
                    $groups = array_merge($groups, ['sales', 'inventory', 'finance', 'hrm']);
                } else {
                    $groups[] = $group;
                }
            }
        }

        return array_unique($groups);
    }
}
