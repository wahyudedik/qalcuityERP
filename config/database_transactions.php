<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Transaction Isolation Levels
    |--------------------------------------------------------------------------
    |
    | Configure isolation levels for different types of financial transactions.
    | This ensures data consistency and prevents concurrency issues like:
    | - Dirty reads
    | - Non-repeatable reads
    | - Phantom reads
    | - Lost updates
    |
    | Supported levels:
    | - 'READ UNCOMMITTED' (lowest isolation, allows dirty reads)
    | - 'READ COMMITTED' (prevents dirty reads, default in most DBs)
    | - 'REPEATABLE READ' (prevents non-repeatable reads)
    | - 'SERIALIZABLE' (highest isolation, prevents all concurrency issues)
    |
    */

    'isolation_levels' => [

        /*
        | Financial transactions that modify account balances
        | Recommended: SERIALIZABLE or REPEATABLE READ
        */
        'payment_processing' => env('DB_ISOLATION_PAYMENT', 'SERIALIZABLE'),

        /*
        | Journal entry creation and posting
        | Must be atomic - all lines succeed or all fail
        | Recommended: SERIALIZABLE
        */
        'journal_posting' => env('DB_ISOLATION_JOURNAL', 'SERIALIZABLE'),

        /*
        | Inventory stock movements
        | Prevent overselling and stock inconsistencies
        | Recommended: REPEATABLE READ or SERIALIZABLE
        */
        'inventory_movement' => env('DB_ISOLATION_INVENTORY', 'REPEATABLE READ'),

        /*
        | Invoice creation and modification
        | Ensures invoice totals remain consistent
        | Recommended: REPEATABLE READ
        */
        'invoice_operations' => env('DB_ISOLATION_INVOICE', 'REPEATABLE READ'),

        /*
        | General ledger adjustments
        | Critical for financial reporting accuracy
        | Recommended: SERIALIZABLE
        */
        'gl_adjustments' => env('DB_ISOLATION_GL', 'SERIALIZABLE'),

        /*
        | Recurring journal processing
        | Batch operations that should be isolated
        | Recommended: READ COMMITTED
        */
        'recurring_journals' => env('DB_ISOLATION_RECURRING', 'READ COMMITTED'),

        /*
        | Period closing operations
        | Must prevent any concurrent modifications
        | Recommended: SERIALIZABLE
        */
        'period_closing' => env('DB_ISOLATION_PERIOD', 'SERIALIZABLE'),

        /*
        | Default isolation for unspecified operations
        */
        'default' => env('DB_ISOLATION_DEFAULT', 'READ COMMITTED'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Timeout Settings
    |--------------------------------------------------------------------------
    |
    | Maximum time (in seconds) a transaction can run before timing out.
    | Prevents long-running transactions from holding locks indefinitely.
    |
    */

    'timeouts' => [

        /*
        | Payment processing timeout
        */
        'payment' => env('DB_TRANSACTION_TIMEOUT_PAYMENT', 30),

        /*
        | GL posting timeout
        */
        'journal' => env('DB_TRANSACTION_TIMEOUT_JOURNAL', 30),

        /*
        | Bulk operations timeout (longer for batch processing)
        */
        'bulk_operations' => env('DB_TRANSACTION_TIMEOUT_BULK', 60),

        /*
        | Default transaction timeout
        */
        'default' => env('DB_TRANSACTION_TIMEOUT_DEFAULT', 30),

    ],

    /*
    |--------------------------------------------------------------------------
    | Deadlock Detection and Retry
    |--------------------------------------------------------------------------
    |
    | Configure automatic retry behavior for deadlocked transactions.
    |
    */

    'retry' => [

        /*
        | Enable automatic retry on deadlock
        */
        'enabled' => env('DB_RETRY_ON_DEADLOCK', true),

        /*
        | Maximum number of retry attempts
        */
        'max_attempts' => env('DB_RETRY_MAX_ATTEMPTS', 3),

        /*
        | Delay between retries (milliseconds)
        */
        'delay_ms' => env('DB_RETRY_DELAY_MS', 100),

    ],

    /*
    |--------------------------------------------------------------------------
    | Lock Timeout
    |--------------------------------------------------------------------------
    |
    | How long to wait for a lock before giving up (seconds).
    | Set to null to use database default.
    |
    */

    'lock_timeout' => env('DB_LOCK_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Statement Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time for individual SQL statements (seconds).
    | Prevents runaway queries from blocking transactions.
    |
    */

    'statement_timeout' => env('DB_STATEMENT_TIMEOUT', 30),

];
