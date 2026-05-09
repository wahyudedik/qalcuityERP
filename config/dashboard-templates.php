<?php

/**
 * Dashboard Templates Configuration
 *
 * TASK-017: Role-Based Dashboard Templates
 *
 * Pre-built dashboard templates for different roles and use cases.
 * Each template defines:
 * - widgets: Which widgets to show
 * - layout: Grid layout pattern (e.g., '2x2', '1x3', '3x2')
 * - cols_overrides: Custom column spans for specific widgets
 *
 * Usage:
 * - Admins can switch between templates
 * - Users can customize and save their own layouts
 * - Templates can be shared across teams
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Template
    |--------------------------------------------------------------------------
    |
    | This template will be used when no specific template is selected.
    | Usually matches the user's role default.
    |
    */
    'default' => 'role_based',

    /*
    |--------------------------------------------------------------------------
    | Role-Based Templates
    |--------------------------------------------------------------------------
    |
    | Templates automatically applied based on user role.
    | These serve as starting points that users can customize.
    |
    */
    'role_templates' => [
        'admin' => [
            'name' => 'Administrator Dashboard',
            'description' => 'Overview lengkap untuk admin: revenue, orders, inventory, attendance',
            'layout' => '4-column',
            'widgets' => [
                ['key' => 'ai_insights', 'order' => 0, 'visible' => true, 'cols_override' => 4],
                ['key' => 'anomaly_alerts', 'order' => 1, 'visible' => true, 'cols_override' => 4],
                ['key' => 'kpi_revenue', 'order' => 2, 'visible' => true, 'cols_override' => null],
                ['key' => 'kpi_orders', 'order' => 3, 'visible' => true, 'cols_override' => null],
                ['key' => 'ecommerce_orders', 'order' => 4, 'visible' => true, 'cols_override' => null],
                ['key' => 'kpi_low_stock', 'order' => 5, 'visible' => true, 'cols_override' => null],
                ['key' => 'kpi_attendance', 'order' => 6, 'visible' => true, 'cols_override' => null],
                ['key' => 'chart_sales', 'order' => 7, 'visible' => true, 'cols_override' => 2],
                ['key' => 'chart_finance', 'order' => 8, 'visible' => true, 'cols_override' => 2],
                ['key' => 'low_stock_list', 'order' => 9, 'visible' => true, 'cols_override' => 2],
                ['key' => 'quick_stats', 'order' => 10, 'visible' => true, 'cols_override' => 2],
                ['key' => 'gamification', 'order' => 11, 'visible' => true, 'cols_override' => 2],
            ],
        ],

        'manager' => [
            'name' => 'Manager Dashboard',
            'description' => 'Fokus pada KPI dan performa tim',
            'layout' => '4-column',
            'widgets' => [
                ['key' => 'ai_insights', 'order' => 0, 'visible' => true, 'cols_override' => 4],
                ['key' => 'kpi_revenue', 'order' => 1, 'visible' => true, 'cols_override' => null],
                ['key' => 'kpi_orders', 'order' => 2, 'visible' => true, 'cols_override' => null],
                ['key' => 'ecommerce_orders', 'order' => 3, 'visible' => true, 'cols_override' => null],
                ['key' => 'kpi_low_stock', 'order' => 4, 'visible' => true, 'cols_override' => null],
                ['key' => 'kpi_attendance', 'order' => 5, 'visible' => true, 'cols_override' => null],
                ['key' => 'chart_sales', 'order' => 6, 'visible' => true, 'cols_override' => 2],
                ['key' => 'chart_finance', 'order' => 7, 'visible' => true, 'cols_override' => 2],
                ['key' => 'quick_stats', 'order' => 8, 'visible' => true, 'cols_override' => 2],
                ['key' => 'gamification', 'order' => 9, 'visible' => true, 'cols_override' => 2],
            ],
        ],

        'kasir' => [
            'name' => 'Kasir Dashboard',
            'description' => 'Fokus pada transaksi POS dan penjualan harian',
            'layout' => '2-column',
            'widgets' => [
                ['key' => 'pos_today', 'order' => 0, 'visible' => true, 'cols_override' => null],
                ['key' => 'kpi_orders', 'order' => 1, 'visible' => true, 'cols_override' => null],
                ['key' => 'chart_sales', 'order' => 2, 'visible' => true, 'cols_override' => 2],
                ['key' => 'gamification', 'order' => 3, 'visible' => true, 'cols_override' => 2],
            ],
        ],

        'gudang' => [
            'name' => 'Gudang Dashboard',
            'description' => 'Fokus pada inventory dan stock management',
            'layout' => '2-column',
            'widgets' => [
                ['key' => 'kpi_low_stock', 'order' => 0, 'visible' => true, 'cols_override' => null],
                ['key' => 'low_stock_list', 'order' => 1, 'visible' => true, 'cols_override' => 2],
                ['key' => 'kpi_attendance', 'order' => 2, 'visible' => true, 'cols_override' => null],
                ['key' => 'gamification', 'order' => 3, 'visible' => true, 'cols_override' => 2],
            ],
        ],

        'staff' => [
            'name' => 'Staff Dashboard',
            'description' => 'Dashboard umum untuk staff',
            'layout' => '2-column',
            'widgets' => [
                ['key' => 'kpi_revenue', 'order' => 0, 'visible' => true, 'cols_override' => null],
                ['key' => 'kpi_orders', 'order' => 1, 'visible' => true, 'cols_override' => null],
                ['key' => 'kpi_low_stock', 'order' => 2, 'visible' => true, 'cols_override' => null],
                ['key' => 'chart_sales', 'order' => 3, 'visible' => true, 'cols_override' => 2],
                ['key' => 'quick_stats', 'order' => 4, 'visible' => true, 'cols_override' => 2],
                ['key' => 'gamification', 'order' => 5, 'visible' => true, 'cols_override' => 2],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Specialized Templates
    |--------------------------------------------------------------------------
    |
    | Pre-built templates for specific use cases that any role can use.
    | Users can switch to these templates for different perspectives.
    |
    */
    'specialized_templates' => [
        'sales_focus' => [
            'name' => 'Sales Focus',
            'description' => 'Fokus pada penjualan dan revenue',
            'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
            'roles' => ['admin', 'manager', 'kasir', 'staff'],
            'layout' => '2-column',
            'widgets' => [
                ['key' => 'kpi_revenue', 'order' => 0, 'visible' => true, 'cols_override' => null],
                ['key' => 'kpi_orders', 'order' => 1, 'visible' => true, 'cols_override' => null],
                ['key' => 'ecommerce_orders', 'order' => 2, 'visible' => true, 'cols_override' => null],
                ['key' => 'pos_today', 'order' => 3, 'visible' => true, 'cols_override' => null],
                ['key' => 'chart_sales', 'order' => 4, 'visible' => true, 'cols_override' => 4],
                ['key' => 'gamification', 'order' => 5, 'visible' => true, 'cols_override' => 2],
            ],
        ],

        'inventory_focus' => [
            'name' => 'Inventory Focus',
            'description' => 'Fokus pada inventory management',
            'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            'roles' => ['admin', 'manager', 'gudang'],
            'layout' => '2-column',
            'widgets' => [
                ['key' => 'kpi_low_stock', 'order' => 0, 'visible' => true, 'cols_override' => null],
                ['key' => 'low_stock_list', 'order' => 1, 'visible' => true, 'cols_override' => 4],
                ['key' => 'quick_stats', 'order' => 2, 'visible' => true, 'cols_override' => 2],
                ['key' => 'gamification', 'order' => 3, 'visible' => true, 'cols_override' => 2],
            ],
        ],

        'finance_focus' => [
            'name' => 'Finance Focus',
            'description' => 'Fokus pada keuangan dan accounting',
            'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'roles' => ['admin', 'manager'],
            'layout' => '2-column',
            'widgets' => [
                ['key' => 'kpi_revenue', 'order' => 0, 'visible' => true, 'cols_override' => null],
                ['key' => 'chart_finance', 'order' => 1, 'visible' => true, 'cols_override' => 4],
                ['key' => 'quick_stats', 'order' => 2, 'visible' => true, 'cols_override' => 2],
                ['key' => 'gamification', 'order' => 3, 'visible' => true, 'cols_override' => 2],
            ],
        ],

        'hrm_focus' => [
            'name' => 'HRM Focus',
            'description' => 'Fokus pada SDM dan attendance',
            'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
            'roles' => ['admin', 'manager'],
            'layout' => '2-column',
            'widgets' => [
                ['key' => 'kpi_attendance', 'order' => 0, 'visible' => true, 'cols_override' => null],
                ['key' => 'quick_stats', 'order' => 1, 'visible' => true, 'cols_override' => 2],
                ['key' => 'gamification', 'order' => 2, 'visible' => true, 'cols_override' => 2],
            ],
        ],

        'minimal' => [
            'name' => 'Minimal',
            'description' => 'Dashboard sederhana dengan widget essential',
            'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
            'roles' => ['admin', 'manager', 'kasir', 'gudang', 'staff'],
            'layout' => '2-column',
            'widgets' => [
                ['key' => 'kpi_revenue', 'order' => 0, 'visible' => true, 'cols_override' => null],
                ['key' => 'kpi_orders', 'order' => 1, 'visible' => true, 'cols_override' => null],
                ['key' => 'gamification', 'order' => 2, 'visible' => true, 'cols_override' => 2],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Layout Definitions
    |--------------------------------------------------------------------------
    |
    | Grid layout patterns for different template types.
    |
    */
    'layouts' => [
        '4-column' => [
            'grid_class' => 'grid grid-cols-2 lg:grid-cols-4',
            'max_cols' => 4,
            'description' => '4 kolom di desktop, 2 kolom di mobile',
        ],
        '2-column' => [
            'grid_class' => 'grid grid-cols-2',
            'max_cols' => 2,
            'description' => '2 kolom di semua ukuran layar',
        ],
        '1-column' => [
            'grid_class' => 'grid grid-cols-1',
            'max_cols' => 1,
            'description' => '1 kolom (full width)',
        ],
    ],
];
