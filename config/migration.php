<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Migration Optimization Settings
    |--------------------------------------------------------------------------
    |
    | These settings can help optimize migration performance during development.
    |
    */

    // Disable foreign key checks during migrations for faster execution
    'disable_foreign_keys' => env('MIGRATION_DISABLE_FOREIGN_KEYS', true),

    // Use single transaction for all migrations when possible
    'single_transaction' => env('MIGRATION_SINGLE_TRANSACTION', false),

    // Batch size for migrations (0 = all at once)
    'batch_size' => env('MIGRATION_BATCH_SIZE', 0),

];
