<?php

return [
    'api_key' => env('GEMINI_API_KEY'),

    // Model utama
    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),

    // Urutan fallback jika rate limit / token habis
    'fallback_models' => [
        'gemini-2.5-flash',       // primary
        'gemini-2.5-flash-lite',  // fallback 1 — paling cepat
        'gemini-1.5-flash',       // fallback 2 — stable, widely available
        'gemini-2.5-pro',         // fallback 3 — paling capable
    ],

    // HTTP error codes yang memicu fallback
    'rate_limit_codes' => [429, 503, 500],

    // Timeout per API call (detik) — referensi untuk logging
    'timeout' => env('GEMINI_TIMEOUT', 60),
];
