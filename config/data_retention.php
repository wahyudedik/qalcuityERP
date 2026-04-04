<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Data Retention Policies
    |--------------------------------------------------------------------------
    |
    | Define how long different types of data should be kept before archival
    | or deletion. These policies ensure compliance while optimizing database
    | performance by removing old, rarely-accessed data.
    |
    */

    'archival' => [

        /*
        | Activity Logs - User actions and system events
        | Default: 365 days (1 year)
        */
        'activity_logs_days' => env('DATA_RETENTION_ACTIVITY_LOGS', 365),

        /*
        | AI Usage Logs - AI interactions and prompts
        | Default: 180 days (6 months)
        */
        'ai_usage_logs_days' => env('DATA_RETENTION_AI_USAGE', 180),

        /*
        | Anomaly Alerts - System anomaly detections
        | Default: 90 days (3 months)
        */
        'anomaly_alerts_days' => env('DATA_RETENTION_ANOMALIES', 90),

        /*
        | Chat Messages - Internal chat/communication
        | Default: 180 days (6 months)
        */
        'chat_messages_days' => env('DATA_RETENTION_CHAT_MESSAGES', 180),

        /*
        | Chat Sessions - Chat session metadata
        | Default: 180 days (6 months)
        */
        'chat_sessions_days' => env('DATA_RETENTION_CHAT_SESSIONS', 180),

        /*
        | Notifications - In-app notifications
        | Default: 90 days (3 months)
        */
        'notifications_days' => env('DATA_RETENTION_NOTIFICATIONS', 90),

        /*
        | Error Logs - Application errors and exceptions
        | Default: 180 days (6 months)
        */
        'error_logs_days' => env('DATA_RETENTION_ERROR_LOGS', 180),

        /*
        | Harvest Logs - Agricultural harvest records
        | Default: 730 days (2 years) - longer for seasonal analysis
        */
        'harvest_logs_days' => env('DATA_RETENTION_HARVEST', 730),

        /*
        | Livestock Health Records - Veterinary and health tracking
        | Default: 1095 days (3 years) - regulatory requirement
        */
        'livestock_health_records_days' => env('DATA_RETENTION_LIVESTOCK_HEALTH', 1095),

        /*
        | Stock Movements - Inventory movement history
        | Default: 365 days (1 year)
        */
        'stock_movements_days' => env('DATA_RETENTION_STOCK_MOVEMENTS', 365),

    ],

    /*
    |--------------------------------------------------------------------------
    | Cleanup Policies
    |--------------------------------------------------------------------------
    |
    | Configure automatic cleanup behavior for orphaned and temporary data.
    |
    */

    'cleanup' => [

        /*
        | Enable automatic orphan cleanup
        | When true, scheduled jobs will automatically clean orphans
        */
        'auto_cleanup_enabled' => env('DATA_CLEANUP_AUTO_ENABLED', true),

        /*
        | How often to run orphan cleanup (days)
        | Default: 7 days (weekly)
        */
        'cleanup_frequency_days' => env('DATA_CLEANUP_FREQUENCY', 7),

        /*
        | Types to cleanup automatically
        | Leave empty to cleanup all configured types
        */
        'auto_cleanup_types' => [
            // 'invoice_items_without_invoice',
            // 'journal_lines_without_entry',
            // 'sales_order_items_without_order',
        ],

        /*
        | Notify admins before cleanup
        | Days in advance to send notification
        */
        'notify_before_cleanup_days' => env('DATA_CLEANUP_NOTIFY_DAYS', 2),

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration & Consolidation
    |--------------------------------------------------------------------------
    |
    | Settings for multi-tenant data migration and consolidation operations.
    |
    */

    'migration' => [

        /*
        | Enable tenant merging
        | Allow merging of multiple tenants into one
        */
        'allow_tenant_merge' => env('DATA_MIGRATION_ALLOW_MERGE', true),

        /*
        | Enable tenant splitting
        | Allow splitting one tenant into multiple
        */
        'allow_tenant_split' => env('DATA_MIGRATION_ALLOW_SPLIT', false),

        /*
        | Conflict resolution strategy
        | Options: 'prefer_target', 'prefer_source', 'manual_review'
        */
        'conflict_resolution' => env('DATA_MIGRATION_CONFLICT_STRATEGY', 'prefer_target'),

        /*
        | Automatically reassign references during merge
        | When true, child records follow parent records
        */
        'auto_reassign_references' => env('DATA_MIGRATION_AUTO_REASSIGN', true),

        /*
        | Validate data integrity before migration
        | Recommended: always true for production
        */
        'validate_before_migration' => env('DATA_MIGRATION_VALIDATE', true),

        /*
        | Create backup before migration
        | Highly recommended for safety
        */
        'backup_before_migration' => env('DATA_MIGRATION_BACKUP', true),

    ],

    /*
    |--------------------------------------------------------------------------
    | Archive Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Where and how archived data should be stored.
    |
    */

    'archive_storage' => [

        /*
        | Archive database connection
        | Leave null to use same database with _archive suffix tables
        | Set to separate connection for offloading archive data
        */
        'connection' => env('ARCHIVE_DB_CONNECTION', null),

        /*
        | Compress archived data
        | Reduces storage but adds CPU overhead
        */
        'compress' => env('ARCHIVE_COMPRESS', false),

        /*
        | Export archives to file periodically
        | Formats: 'csv', 'json', 'sql'
        */
        'export_format' => env('ARCHIVE_EXPORT_FORMAT', null),

        /*
        | Export destination path
        | Used when export_format is set
        */
        'export_path' => storage_path(env('ARCHIVE_EXPORT_PATH', 'archives')),

    ],

    /*
    |--------------------------------------------------------------------------
    | Compliance & Audit Trail
    |--------------------------------------------------------------------------
    |
    | Special handling for compliance-related data retention.
    |
    */

    'compliance' => [

        /*
        | Preserve audit trail for compliance
        | Even after archival period, keep certain records
        */
        'preserve_audit_trail' => env('DATA_COMPLIANCE_PRESERVE_AUDIT', true),

        /*
        | Compliance hold period (days)
        | Records under legal/compliance hold are preserved
        */
        'hold_period_days' => env('DATA_COMPLIANCE_HOLD_PERIOD', 2555), // 7 years

        /*
        | Types that require compliance review before deletion
        */
        'compliance_review_required' => [
            'journal_entries',
            'invoices',
            'payments',
            'tax_records',
        ],

        /*
        | Enable soft delete instead of hard delete
        | For compliance-sensitive data
        */
        'soft_delete_compliance_data' => env('DATA_COMPLIANCE_SOFT_DELETE', true),

    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduled Archival Configuration
    |--------------------------------------------------------------------------
    |
    | When and how often archival jobs should run.
    |
    */

    'schedule' => [

        /*
        | Enable scheduled archival
        | Runs automatically via Laravel scheduler
        */
        'enabled' => env('DATA_ARCHIVAL_SCHEDULED', true),

        /*
        | How often to run archival (cron expression)
        | Default: Daily at 2 AM
        */
        'frequency' => env('DATA_ARCHIVAL_FREQUENCY', '0 2 * * *'),

        /*
        | Which tenant to process (for single-tenant mode)
        | Leave null for multi-tenant processing
        */
        'tenant_id' => env('DATA_ARCHIVAL_TENANT_ID', null),

        /*
        | Batch size for archival operations
        | Larger = faster but more memory
        */
        'batch_size' => env('DATA_ARCHIVAL_BATCH_SIZE', 1000),

        /*
        | Timeout per batch (seconds)
        | Prevents runaway archival jobs
        */
        'timeout_seconds' => env('DATA_ARCHIVAL_TIMEOUT', 300),

    ],

];
