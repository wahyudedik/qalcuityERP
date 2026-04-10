<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Account Lockout Configuration
    |--------------------------------------------------------------------------
    */

    'lockout' => [
        'enabled' => env('ACCOUNT_LOCKOUT_ENABLED', true),
        'max_attempts' => env('ACCOUNT_LOCKOUT_MAX_ATTEMPTS', 5),
        'duration_minutes' => env('ACCOUNT_LOCKOUT_DURATION', 15),
        'warning_threshold' => env('ACCOUNT_LOCKOUT_WARNING', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Management
    |--------------------------------------------------------------------------
    */

    'session' => [
        'max_concurrent_sessions' => env('MAX_CONCURRENT_SESSIONS', 3),
        'session_timeout_minutes' => env('SESSION_TIMEOUT', 120),
        'idle_timeout_minutes' => env('SESSION_IDLE_TIMEOUT', 30),
        'absolute_timeout_hours' => env('SESSION_ABSOLUTE_TIMEOUT', 24),
        'track_devices' => env('SESSION_TRACK_DEVICES', true),
        'new_device_alert' => env('SESSION_NEW_DEVICE_ALERT', true),
        'force_https' => env('SESSION_FORCE_HTTPS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Encryption
    |--------------------------------------------------------------------------
    */

    'encryption' => [
        'enabled' => env('DATA_ENCRYPTION_ENABLED', true),
        'algorithm' => env('DATA_ENCRYPTION_ALGORITHM', 'AES-256-CBC'),
        'encrypt_sensitive_fields' => env('ENCRYPT_SENSITIVE_FIELDS', true),
        'key_rotation_days' => env('ENCRYPTION_KEY_ROTATION_DAYS', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Trail
    |--------------------------------------------------------------------------
    */

    'audit' => [
        'enabled' => env('AUDIT_TRAIL_ENABLED', true),
        'retention_days' => env('AUDIT_RETENTION_DAYS', 365),
        'log_sensitive_actions' => env('AUDIT_SENSITIVE_ACTIONS', true),
        'log_reads' => env('AUDIT_LOG_READS', false),
        'log_writes' => env('AUDIT_LOG_WRITES', true),
        'log_deletes' => env('AUDIT_LOG_DELETES', true),
        'export_format' => env('AUDIT_EXPORT_FORMAT', 'csv'),
    ],

    /*
    |--------------------------------------------------------------------------
    | GDPR Compliance
    |--------------------------------------------------------------------------
    */

    'gdpr' => [
        'enabled' => env('GDPR_ENABLED', true),
        'data_export_enabled' => env('GDPR_DATA_EXPORT', true),
        'right_to_be_forgotten' => env('GDPR_RIGHT_TO_BE_FORGOTTEN', true),
        'consent_required' => env('GDPR_CONSENT_REQUIRED', true),
        'consent_version' => env('GDPR_CONSENT_VERSION', '1.0'),
        'data_deletion_delay_hours' => env('GDPR_DELETION_DELAY', 24),
        'anonymize_instead_of_delete' => env('GDPR_ANONYMIZE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | HIPAA Compliance (Healthcare)
    |--------------------------------------------------------------------------
    */

    'hipaa' => [
        'enabled' => env('HIPAA_ENABLED', false),
        'phi_access_logging' => env('HIPAA_PHI_LOGGING', true),
        'max_phi_access_per_hour' => env('HIPAA_MAX_PHI_ACCESS', 100),
        'require_break_glass_reason' => env('HIPAA_BREAK_GLASS_REASON', true),
        'audit_retention_years' => env('HIPAA_AUDIT_RETENTION', 6),
        'auto_logout_on_inactivity' => env('HIPAA_AUTO_LOGOUT', true),
        'session_timeout_minutes' => env('HIPAA_SESSION_TIMEOUT', 15),
        'ip_whitelist_required' => env('HIPAA_IP_WHITELIST', false),
        'encryption_required' => env('HIPAA_ENCRYPTION_REQUIRED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    */

    'headers' => [
        'x_frame_options' => 'DENY',
        'x_content_type_options' => 'nosniff',
        'x_xss_protection' => '1; mode=block',
        'strict_transport_security' => 'max-age=31536000; includeSubDomains',
        'content_security_policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';",
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'camera=(), microphone=(), geolocation=()',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Security
    |--------------------------------------------------------------------------
    */

    'api' => [
        'rate_limit_requests' => env('API_RATE_LIMIT', 60),
        'rate_limit_period' => env('API_RATE_LIMIT_PERIOD', 1),
        'token_expiry_hours' => env('API_TOKEN_EXPIRY', 24),
        'require_api_version' => env('API_REQUIRE_VERSION', true),
        'max_payload_size_mb' => env('API_MAX_PAYLOAD', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    */

    'uploads' => [
        'max_file_size_mb' => env('UPLOAD_MAX_SIZE', 10),
        'allowed_mime_types' => explode(',', env('UPLOAD_ALLOWED_MIMES', 'image/jpeg,image/png,application/pdf')),
        'scan_for_malware' => env('UPLOAD_SCAN_MALWARE', false),
        'quarantine_suspicious' => env('UPLOAD_QUARANTINE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Policies
    |--------------------------------------------------------------------------
    */

    'password' => [
        'min_length' => env('PASSWORD_MIN_LENGTH', 12),
        'require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
        'require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),
        'require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
        'require_special_chars' => env('PASSWORD_REQUIRE_SPECIAL_CHARS', true),
        'max_age_days' => env('PASSWORD_MAX_AGE', 90),
        'history_count' => env('PASSWORD_HISTORY', 5),
    ],
];
