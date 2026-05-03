<?php

/**
 * Konfigurasi Multi AI Provider
 *
 * File ini mendefinisikan konfigurasi untuk semua AI provider yang didukung.
 * Nilai untuk provider Gemini di-sync dengan config/gemini.php agar backward compatible.
 *
 * Requirements: 8.1–8.5
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | Provider yang digunakan secara default untuk semua request AI.
    | Nilai ini dapat di-override melalui SystemSetting 'ai_default_provider'
    | atau TenantApiSetting 'ai_provider' per tenant.
    |
    | Supported: "gemini", "anthropic"
    |
    */
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'gemini'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Order
    |--------------------------------------------------------------------------
    |
    | Urutan provider yang dicoba ketika provider aktif tidak tersedia.
    | AiProviderRouter akan mencoba provider berikutnya dalam urutan ini
    | ketika terjadi rate limit atau server error.
    |
    */
    'fallback_order' => ['gemini', 'anthropic'],

    /*
    |--------------------------------------------------------------------------
    | Provider Mode
    |--------------------------------------------------------------------------
    |
    | Mode operasi provider:
    | - 'single'   : Hanya gunakan satu provider, tidak ada fallback lintas-provider
    | - 'failover' : Otomatis beralih ke provider berikutnya jika provider aktif gagal
    |
    */
    'mode' => env('AI_PROVIDER_MODE', 'failover'),

    /*
    |--------------------------------------------------------------------------
    | Provider Configurations
    |--------------------------------------------------------------------------
    |
    | Konfigurasi detail untuk setiap provider. Nilai env var untuk Gemini
    | menggunakan nama yang sama dengan config/gemini.php agar tetap sinkron.
    |
    */
    'providers' => [

        'gemini' => [
            // API key — sama dengan config/gemini.php
            'api_key'             => env('GEMINI_API_KEY'),

            // Model utama — sama dengan config/gemini.php
            'model'               => env('GEMINI_MODEL', 'gemini-2.5-flash'),

            // Urutan fallback model dalam provider Gemini
            'fallback_models'     => [
                'gemini-2.5-flash',       // primary
                'gemini-2.5-flash-lite',  // fallback 1 — paling cepat
                'gemini-1.5-flash',       // fallback 2 — stable, widely available
            ],

            // Timeout per API call (detik) — sama dengan config/gemini.php
            'timeout'             => env('GEMINI_TIMEOUT', 60),

            // Cooldown setelah rate limit error (detik) — sama dengan config/gemini.php
            'rate_limit_cooldown' => env('GEMINI_RATE_LIMIT_COOLDOWN', 60),

            // Cooldown setelah quota exceeded error (detik) — sama dengan config/gemini.php
            'quota_cooldown'      => env('GEMINI_QUOTA_COOLDOWN', 3600),
        ],

        'anthropic' => [
            // API key Anthropic Claude
            'api_key'             => env('ANTHROPIC_API_KEY'),

            // Model utama — default: claude-3-5-sonnet-20241022
            'model'               => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),

            // Urutan fallback model dalam provider Anthropic
            'fallback_models'     => [
                'claude-3-5-sonnet-20241022', // primary — paling capable
                'claude-3-haiku-20240307',    // fallback — paling cepat & murah
            ],

            // Timeout per API call (detik)
            'timeout'             => env('ANTHROPIC_TIMEOUT', 60),

            // Maksimum token output — default: 8192
            'max_tokens'          => env('ANTHROPIC_MAX_TOKENS', 8192),

            // Cooldown setelah rate limit error (detik) — HTTP 429/529
            'rate_limit_cooldown' => 60,

            // Cooldown setelah quota exceeded error (detik)
            'quota_cooldown'      => 3600,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Use Case Routing
    |--------------------------------------------------------------------------
    |
    | Pemetaan default setiap use case AI ke provider dan model yang sesuai.
    | Strategi hybrid: Lightweight use cases → Gemini Flash,
    | Heavyweight use cases → Claude Sonnet.
    |
    | Nilai ini digunakan sebagai fallback ketika tidak ada AiUseCaseRoute
    | yang ditemukan di database untuk use case yang diminta.
    |
    | Requirements: 9.1, 1.4, 1.5
    |
    */
    'use_case_routing' => [
        // Lightweight use cases — Gemini Flash (cepat, murah, operasional harian)
        'chatbot'             => ['provider' => 'gemini',    'model' => 'gemini-2.5-flash',          'min_plan' => null],
        'crud_ai'             => ['provider' => 'gemini',    'model' => 'gemini-2.5-flash',          'min_plan' => null],
        'auto_reply'          => ['provider' => 'gemini',    'model' => 'gemini-2.5-flash',          'min_plan' => null],
        'invoice_parsing'     => ['provider' => 'gemini',    'model' => 'gemini-2.5-flash',          'min_plan' => null],
        'document_parsing'    => ['provider' => 'gemini',    'model' => 'gemini-2.5-flash',          'min_plan' => null],
        'notification_ai'     => ['provider' => 'gemini',    'model' => 'gemini-2.5-flash',          'min_plan' => null],
        'product_description' => ['provider' => 'gemini',    'model' => 'gemini-2.5-flash',          'min_plan' => null],
        'email_draft'         => ['provider' => 'gemini',    'model' => 'gemini-2.5-flash',          'min_plan' => null],

        // Heavyweight use cases — Claude Sonnet (analitik berat, laporan, forecasting)
        'financial_report'        => ['provider' => 'anthropic', 'model' => 'claude-3-5-sonnet-20241022', 'min_plan' => 'professional'],
        'forecasting'             => ['provider' => 'anthropic', 'model' => 'claude-3-5-sonnet-20241022', 'min_plan' => 'professional'],
        'decision_support'        => ['provider' => 'anthropic', 'model' => 'claude-3-5-sonnet-20241022', 'min_plan' => 'professional'],
        'audit_analysis'          => ['provider' => 'anthropic', 'model' => 'claude-3-5-sonnet-20241022', 'min_plan' => 'professional'],
        'business_recommendation' => ['provider' => 'anthropic', 'model' => 'claude-3-5-sonnet-20241022', 'min_plan' => 'professional'],
        'bank_reconciliation_ai'  => ['provider' => 'anthropic', 'model' => 'claude-3-5-sonnet-20241022', 'min_plan' => 'professional'],
        'budget_analysis'         => ['provider' => 'anthropic', 'model' => 'claude-3-5-sonnet-20241022', 'min_plan' => 'professional'],
        'anomaly_detection'       => ['provider' => 'anthropic', 'model' => 'claude-3-5-sonnet-20241022', 'min_plan' => 'professional'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Per 1K Tokens
    |--------------------------------------------------------------------------
    |
    | Estimasi biaya dalam IDR per 1.000 token untuk setiap provider dan model.
    | Digunakan oleh UseCaseRouter untuk menghitung estimated_cost_idr pada
    | setiap request AI yang dicatat ke ai_usage_cost_logs.
    |
    | Formula: ((input_tokens + output_tokens) / 1000) * cost_per_1k_tokens
    |
    | Requirements: 9.2, 6.4, 6.5
    |
    */
    'cost_per_1k_tokens' => [
        'gemini' => [
            'gemini-2.5-flash'      => 0.15,   // IDR per 1K token
            'gemini-2.5-flash-lite' => 0.08,
            'gemini-1.5-flash'      => 0.10,
            'default'               => 0.15,
        ],
        'anthropic' => [
            'claude-3-5-sonnet-20241022' => 2.50,  // IDR per 1K token
            'claude-3-haiku-20240307'    => 0.50,
            'default'                    => 2.50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Use Case Fallback Chains
    |--------------------------------------------------------------------------
    |
    | Urutan fallback provider per kategori use case ketika provider yang
    | di-assign tidak tersedia. Lightweight use cases fallback ke Anthropic,
    | Heavyweight use cases fallback ke Gemini.
    |
    | Requirements: 9.3, 7.1, 7.2
    |
    */
    'use_case_fallback_chains' => [
        'lightweight' => ['gemini', 'anthropic'],
        'heavyweight' => ['anthropic', 'gemini'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Lightweight & Heavyweight Provider
    |--------------------------------------------------------------------------
    |
    | Shortcut konfigurasi untuk SuperAdmin yang tidak ingin mengkonfigurasi
    | routing per use case. Dapat di-override melalui environment variables.
    |
    | Requirements: 9.5
    |
    */
    'default_lightweight_provider' => env('AI_LIGHTWEIGHT_PROVIDER', 'gemini'),
    'default_heavyweight_provider' => env('AI_HEAVYWEIGHT_PROVIDER', 'anthropic'),

    /*
    |--------------------------------------------------------------------------
    | Plan Hierarchy
    |--------------------------------------------------------------------------
    |
    | Urutan hierarki subscription plan dari terendah ke tertinggi.
    | Digunakan oleh UseCaseRouter untuk tier gating — memastikan tenant
    | memiliki plan yang cukup untuk mengakses use case premium.
    |
    | Requirements: 3.3, 9.4
    |
    */
    'plan_hierarchy' => ['trial', 'starter', 'business', 'professional', 'enterprise'],

    /*
    |--------------------------------------------------------------------------
    | Cost Threshold (IDR)
    |--------------------------------------------------------------------------
    |
    | Threshold biaya AI per tenant per bulan dalam IDR.
    | Ketika total biaya tenant melebihi threshold ini, sistem akan mengirim
    | notifikasi ke SuperAdmin.
    |
    | Default: Rp 1.000.000 (1 juta rupiah)
    |
    | Requirements: 6.10
    |
    */
    'cost_threshold_idr' => env('AI_COST_THRESHOLD_IDR', 1000000),

    /*
    |--------------------------------------------------------------------------
    | Response Time Threshold (milliseconds)
    |--------------------------------------------------------------------------
    |
    | Threshold response time untuk request AI dalam milliseconds.
    | Ketika response time melebihi threshold ini, sistem akan mencatat
    | warning ke Laravel log.
    |
    | Default: 30000 ms (30 detik)
    |
    | Requirements: 10.7
    |
    */
    'response_time_threshold_ms' => env('AI_RESPONSE_TIME_THRESHOLD_MS', 30000),

];
