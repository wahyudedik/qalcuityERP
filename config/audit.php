<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Retention Policy
    |--------------------------------------------------------------------------
    |
    | Number of days to keep audit trail entries. Entries older than this
    | will be purged by the `audit:purge` command. Set to 0 to disable.
    |
    */
    'retention_days' => (int) env('AUDIT_RETENTION_DAYS', 365),

    /*
    |--------------------------------------------------------------------------
    | Rollback
    |--------------------------------------------------------------------------
    |
    | Allow administrators to rollback changes from audit trail entries.
    | Only entries with model_type, model_id, and old_values can be rolled back.
    |
    */
    'rollback_enabled' => (bool) env('AUDIT_ROLLBACK_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Excluded Fields
    |--------------------------------------------------------------------------
    |
    | Fields that should never be recorded in audit trail snapshots.
    | These are applied globally in addition to per-model $auditExclude.
    |
    */
    'global_exclude' => [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ],

];
