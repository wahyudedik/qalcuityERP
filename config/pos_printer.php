<?php

return [
    /*
    |--------------------------------------------------------------------------
    | POS Printer Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your thermal printer connection settings here.
    |
    */

    // Default printer type: 'usb', 'network', 'file', 'cups'
    'default_type' => env('POS_PRINTER_TYPE', 'usb'),

    // Default printer destination
    // USB: Printer name (e.g., "POS-58")
    // Network: IP address or IP:port (e.g., "192.168.1.100" or "192.168.1.100:9100")
    // File: File path (e.g., "/dev/usb/lp0" or "LPT1")
    // CUPS: Printer name in CUPS system
    'default_destination' => env('POS_PRINTER_DESTINATION', 'POS-58'),

    // Paper width: 58mm or 80mm
    'paper_width' => env('POS_PAPER_WIDTH', 80),

    // Auto-connect on service initialization
    'auto_connect' => env('POS_PRINTER_AUTO_CONNECT', false),

    // Receipt settings
    'receipt' => [
        // Company information
        'company_name' => env('RECEIPT_COMPANY_NAME', 'Your Company Name'),
        'address' => env('RECEIPT_ADDRESS', 'Company Address'),
        'phone' => env('RECEIPT_PHONE', '021-12345678'),
        'email' => env('RECEIPT_EMAIL', 'info@company.com'),
        'website' => env('RECEIPT_WEBSITE', 'www.company.com'),

        // Footer text
        'footer_text' => env('RECEIPT_FOOTER_TEXT', 'Thank you for your purchase!'),

        // Show/hide elements
        'show_logo' => env('RECEIPT_SHOW_LOGO', false),
        'show_tax_breakdown' => env('RECEIPT_SHOW_TAX_BREAKDOWN', true),
        'show_service_charge' => env('RECEIPT_SHOW_SERVICE_CHARGE', true),
        'show_qr_code' => env('RECEIPT_SHOW_QR_CODE', true),

        // Tax rate (percentage)
        'tax_rate' => env('RECEIPT_TAX_RATE', 10),

        // Service charge rate (percentage)
        'service_charge_rate' => env('RECEIPT_SERVICE_CHARGE_RATE', 5),
    ],

    // Kitchen printer settings (separate printer for kitchen)
    'kitchen' => [
        'enabled' => env('KITCHEN_PRINTER_ENABLED', false),
        'type' => env('KITCHEN_PRINTER_TYPE', 'network'),
        'destination' => env('KITCHEN_PRINTER_DESTINATION', '192.168.1.101'),
        'paper_width' => env('KITCHEN_PAPER_WIDTH', 80),
    ],

    // Barcode label printer settings
    'barcode' => [
        'enabled' => env('BARCODE_PRINTER_ENABLED', false),
        'type' => env('BARCODE_PRINTER_TYPE', 'usb'),
        'destination' => env('BARCODE_PRINTER_DESTINATION', 'LABEL-PRINTER'),
        'label_width' => env('BARCODE_LABEL_WIDTH', 50), // mm
        'label_height' => env('BARCODE_LABEL_HEIGHT', 30), // mm
    ],

    // Print queue settings
    'queue' => [
        'enabled' => env('PRINT_QUEUE_ENABLED', true),
        'driver' => env('PRINT_QUEUE_DRIVER', 'database'), // database, redis
        'retry_attempts' => env('PRINT_QUEUE_RETRY', 3),
        'retry_delay' => env('PRINT_QUEUE_RETRY_DELAY', 5), // seconds
    ],

    // Logging
    'log_prints' => env('LOG_PRINT_JOBS', true),
    'log_level' => env('PRINT_LOG_LEVEL', 'info'), // debug, info, warning, error
];
