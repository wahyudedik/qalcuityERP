<?php

return [
    'api_key' => env('GEMINI_API_KEY'),

    // Model utama
    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),

    // Urutan fallback jika rate limit / token habis
    // Diurutkan berdasarkan prioritas: primary -> lite (TPM terbesar) -> flash -> pro
    'fallback_models' => [
        'gemini-2.5-flash',       // primary
        'gemini-2.5-flash-lite',  // fallback 1 — limit terbesar, paling cepat
        'gemini-2.5-pro',         // fallback 2 — paling capable
    ],

    // HTTP error codes yang memicu fallback
    'rate_limit_codes' => [429, 503, 500],
];
