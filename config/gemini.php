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

    // AI Performance Optimization Settings
    'optimization' => [
        // Response caching
        'cache_enabled' => env('AI_RESPONSE_CACHE_ENABLED', true),
        'cache_ttl' => [
            'short' => env('AI_CACHE_SHORT_TTL', 300),      // 5 minutes - Real-time data
            'default' => env('AI_CACHE_DEFAULT_TTL', 3600), // 1 hour - General queries
            'long' => env('AI_CACHE_LONG_TTL', 86400),      // 24 hours - Periodic reports
        ],

        // Rule-based responses
        'rule_based_enabled' => env('AI_RULE_BASED_ENABLED', true),

        // Batch processing
        'batch_size' => env('AI_BATCH_SIZE', 10),
        'batch_queue' => env('AI_BATCH_QUEUE', 'ai'),
        'batch_max_chunks' => env('AI_BATCH_MAX_CHUNKS', 5),

        // Streaming
        'streaming_enabled' => env('AI_STREAMING_ENABLED', true),
        'stream_chunk_delay' => env('AI_STREAM_CHUNK_DELAY', 50),  // milliseconds
        'stream_chunk_size' => env('AI_STREAM_CHUNK_SIZE', 50),    // characters

        // Monitoring
        'logging_enabled' => env('AI_OPTIMIZATION_LOGGING', true),
        'cost_tracking_enabled' => env('AI_COST_TRACKING_ENABLED', true),
    ],
];
