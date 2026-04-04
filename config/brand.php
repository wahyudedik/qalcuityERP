<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Brand Customization Settings
    |--------------------------------------------------------------------------
    |
    | Configure your brand colors, logo, and styling preferences here.
    | These settings will be applied across all payment UI components.
    |
    */

    // Primary brand colors (Tailwind CSS color names or hex codes)
    'colors' => [
        'primary' => env('BRAND_COLOR_PRIMARY', '#3B82F6'), // Blue-500
        'secondary' => env('BRAND_COLOR_SECONDARY', '#8B5CF6'), // Purple-500
        'success' => env('BRAND_COLOR_SUCCESS', '#10B981'), // Green-500
        'warning' => env('BRAND_COLOR_WARNING', '#F59E0B'), // Amber-500
        'error' => env('BRAND_COLOR_ERROR', '#EF4444'), // Red-500
        'info' => env('BRAND_COLOR_INFO', '#06B6D4'), // Cyan-500
    ],

    // Gradient presets for headers
    'gradients' => [
        'primary' => env('BRAND_GRADIENT_PRIMARY', 'from-blue-600 to-blue-700'),
        'secondary' => env('BRAND_GRADIENT_SECONDARY', 'from-purple-600 to-purple-700'),
        'payment' => env('BRAND_GRADIENT_PAYMENT', 'from-purple-600 to-blue-600'),
    ],

    // Logo configuration
    'logo' => [
        'url' => env('BRAND_LOGO_URL', '/logo.png'),
        'width' => env('BRAND_LOGO_WIDTH', 120),
        'height' => env('BRAND_LOGO_HEIGHT', 40),
        'show_in_receipts' => env('BRAND_LOGO_IN_RECEIPTS', false),
    ],

    // Typography
    'typography' => [
        'font_family' => env('BRAND_FONT_FAMILY', 'Inter, sans-serif'),
        'heading_font' => env('BRAND_HEADING_FONT', 'Inter, sans-serif'),
    ],

    // Border radius preferences
    'border_radius' => [
        'small' => env('BRAND_RADIUS_SMALL', '0.375rem'), // rounded-md
        'medium' => env('BRAND_RADIUS_MEDIUM', '0.5rem'), // rounded-lg
        'large' => env('BRAND_RADIUS_LARGE', '0.75rem'), // rounded-xl
        'xl' => env('BRAND_RADIUS_XL', '1rem'), // rounded-2xl
    ],

    // Shadow preferences
    'shadows' => [
        'small' => env('BRAND_SHADOW_SMALL', 'shadow'),
        'medium' => env('BRAND_SHADOW_MEDIUM', 'shadow-lg'),
        'large' => env('BRAND_SHADOW_LARGE', 'shadow-xl'),
    ],

    // Payment method icons (custom URLs or emoji)
    'payment_icons' => [
        'cash' => env('PAYMENT_ICON_CASH', '💵'),
        'qris' => env('PAYMENT_ICON_QRIS', '📱'),
        'card' => env('PAYMENT_ICON_CARD', '💳'),
        'bank_transfer' => env('PAYMENT_ICON_BANK', '🏦'),
    ],

    // E-wallet branding
    'ewallets' => [
        'gopay' => [
            'name' => 'GoPay',
            'color' => '#00AED6',
            'icon' => 'GP',
        ],
        'ovo' => [
            'name' => 'OVO',
            'color' => '#4C3494',
            'icon' => 'OVO',
        ],
        'dana' => [
            'name' => 'DANA',
            'color' => '#118EEA',
            'icon' => 'DANA',
        ],
        'linkaja' => [
            'name' => 'LinkAja',
            'color' => '#E31E52',
            'icon' => 'LA',
        ],
        'shopeepay' => [
            'name' => 'ShopeePay',
            'color' => '#EE4D2D',
            'icon' => 'SP',
        ],
    ],

    // Receipt customization
    'receipt' => [
        'show_logo' => env('RECEIPT_SHOW_LOGO', false),
        'footer_message' => env('RECEIPT_FOOTER_MESSAGE', 'Thank you for your purchase!'),
        'show_qr_code' => env('RECEIPT_SHOW_QR_CODE', true),
        'paper_width' => env('RECEIPT_PAPER_WIDTH', 80), // 58 or 80 mm
    ],

    // UI Text customizations
    'text' => [
        'app_name' => env('APP_NAME', 'Qalcuity ERP'),
        'payment_title' => env('PAYMENT_UI_TITLE', 'Select Payment Method'),
        'qris_instruction' => env('QRIS_INSTRUCTION', 'Scan QR code dengan aplikasi e-wallet Anda'),
        'success_message' => env('PAYMENT_SUCCESS_MESSAGE', 'Payment Successful!'),
        'expired_message' => env('PAYMENT_EXPIRED_MESSAGE', 'Payment Expired'),
    ],

    // Feature toggles
    'features' => [
        'enable_cash' => env('PAYMENT_ENABLE_CASH', true),
        'enable_qris' => env('PAYMENT_ENABLE_QRIS', true),
        'enable_card' => env('PAYMENT_ENABLE_CARD', true),
        'enable_bank_transfer' => env('PAYMENT_ENABLE_BANK_TRANSFER', true),
        'show_quick_cash_buttons' => env('SHOW_QUICK_CASH_BUTTONS', true),
        'auto_print_receipt' => env('AUTO_PRINT_RECEIPT', true),
    ],

    // Quick cash amounts (for cash payment)
    'quick_cash_amounts' => [
        10000,
        20000,
        50000,
        100000,
        150000,
        200000,
        500000,
    ],
];
