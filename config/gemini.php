<?php

return [
    'api_key' => env('GEMINI_API_KEY'),

    // Model utama
    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),

    // Urutan fallback jika rate limit / token habis
    // Diurutkan berdasarkan prioritas: primary -> lite (TPM terbesar) -> flash -> pro
    'fallback_models' => [
        'gemini-2.5-flash',       // 1K RPM | 1M TPM  — primary
        'gemini-2.5-flash-lite',  // 4K RPM | 4M TPM  — fallback 1 (limit terbesar)
        'gemini-2.0-flash',       // 2K RPM | 4M TPM  — fallback 2
        'gemini-3-flash',         // 1K RPM | 2M TPM  — fallback 3
        'gemini-2.5-pro',         // 150 RPM | 2M TPM — fallback 4 (paling lambat)
    ],

    // HTTP error codes yang memicu fallback
    'rate_limit_codes' => [429, 503, 500],
];
