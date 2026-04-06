<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Face Recognition Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Python-based face recognition service
    | Install: pip install face-recognition flask opencv-python
    |
    */

    'face_recognition' => [
        'url' => env('FACE_RECOGNITION_URL', 'http://localhost:5000'),
        'api_key' => env('FACE_RECOGNITION_API_KEY', ''),
        'timeout' => 30,

        // Camera settings
        'camera_index' => env('FACE_CAMERA_INDEX', 0),
        'capture_width' => 640,
        'capture_height' => 480,

        // Recognition settings
        'confidence_threshold' => 0.6,
        'liveness_check' => true,

        // Storage
        'image_storage_path' => storage_path('app/face-recognition'),
        'max_images_per_employee' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | CCTV Integration Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for NVR/DVR integration and camera management
    |
    */

    'cctv' => [
        'nvr_url' => env('CCTV_NVR_URL', 'http://192.168.1.100:8000'),
        'api_key' => env('CCTV_API_KEY', ''),

        // Camera definitions
        'cameras' => [
            1 => [
                'name' => 'Main Entrance',
                'location' => 'Lobby',
                'channel' => 1,
                'resolution' => '1920x1080',
                'enabled' => true,
            ],
            2 => [
                'name' => 'Warehouse A',
                'location' => 'Warehouse',
                'channel' => 2,
                'resolution' => '1920x1080',
                'enabled' => true,
            ],
            3 => [
                'name' => 'Parking Lot',
                'location' => 'Outdoor',
                'channel' => 3,
                'resolution' => '1280x720',
                'enabled' => true,
            ],
            // Add more cameras as needed
        ],

        // Recording settings
        'recording_retention_days' => 30,
        'motion_detection_enabled' => true,
        'snapshot_quality' => 85, // JPEG quality

        // Alert settings
        'motion_alert_webhook' => env('CCTV_MOTION_WEBHOOK', ''),
        'alert_cooldown_seconds' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Bluetooth Scanner Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Bluetooth barcode scanners and devices
    |
    */

    'bluetooth_scanner' => [
        'auto_connect' => false,
        'scan_interval_seconds' => 30,
        'connection_timeout' => 10,

        // HID mode settings (keyboard emulation)
        'hid_mode' => [
            'enabled' => true,
            'key_delay_ms' => 50,
            'end_character' => "\r", // Enter key
        ],

        // Serial mode settings
        'serial_mode' => [
            'baud_rate' => 9600,
            'data_bits' => 8,
            'stop_bits' => 1,
            'parity' => 'none',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Smart Scale Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for digital weighing scales
    |
    */

    'smart_scale' => [
        'auto_tare' => false,
        'stability_threshold' => 0.5, // grams
        'reading_timeout' => 5, // seconds

        // Default serial settings
        'default_baud_rate' => 9600,
        'default_data_bits' => 8,
        'default_stop_bits' => 1,
        'default_parity' => 'none',

        // Unit conversion
        'default_unit' => 'g',
        'available_units' => ['g', 'kg', 'lb', 'oz'],
    ],

    /*
    |--------------------------------------------------------------------------
    | RFID/NFC Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for RFID/NFC tag readers and tags
    |
    */

    'rfid' => [
        'reader_timeout' => 5, // seconds

        // Tag types
        'supported_frequencies' => ['LF', 'HF', 'UHF'],
        'supported_protocols' => [
            'ISO14443A',
            'ISO14443B',
            'ISO15693',
            'Mifare',
            'ISO18000-6C', // EPC Gen2
        ],

        // Anti-collision
        'anti_collision_enabled' => true,
        'max_tags_per_scan' => 100,

        // Encryption
        'encrypt_tag_data' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Thermal Printer Advanced Configuration
    |--------------------------------------------------------------------------
    |
    | Extended configuration for thermal printers
    |
    */

    'thermal_printer' => [
        // Multiple printer profiles
        'printers' => [
            'receipt' => [
                'type' => env('RECEIPT_PRINTER_TYPE', 'usb'),
                'destination' => env('RECEIPT_PRINTER_DESTINATION', 'POS-58'),
                'paper_width' => 80,
                'character_encoding' => 'UTF-8',
            ],
            'kitchen' => [
                'type' => env('KITCHEN_PRINTER_TYPE', 'network'),
                'destination' => env('KITCHEN_PRINTER_DESTINATION', '192.168.1.101'),
                'paper_width' => 80,
                'character_encoding' => 'UTF-8',
            ],
            'label' => [
                'type' => env('LABEL_PRINTER_TYPE', 'usb'),
                'destination' => env('LABEL_PRINTER_DESTINATION', 'LABEL-PRINTER'),
                'paper_width' => 58,
                'character_encoding' => 'UTF-8',
            ],
        ],

        // Print queue settings
        'queue' => [
            'enabled' => true,
            'driver' => 'database',
            'retry_attempts' => 3,
            'retry_delay' => 5, // seconds
        ],

        // Receipt customization
        'receipt' => [
            'show_logo' => true,
            'logo_path' => public_path('images/logo.png'),
            'show_qr_code' => false,
            'footer_text' => 'Thank you!',
            'cut_paper' => true,
        ],
    ],
];
