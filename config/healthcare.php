<?php

/**
 * Healthcare Module Configuration
 *
 * Configuration settings for healthcare module security,
 * business hours, compliance, and access control.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Business Hours Configuration
    |--------------------------------------------------------------------------
    |
    | Define the standard business hours for healthcare facility access.
    | After-hours access will be logged and can trigger alerts.
    |
    */
    'business_hours' => [
        // Start hour (24-hour format, 0-23)
        'start' => env('HEALTHCARE_BUSINESS_START', 8),

        // End hour (24-hour format, 0-23)
        'end' => env('HEALTHCARE_BUSINESS_END', 18),

        // Allow weekend access (true/false)
        'allow_weekends' => env('HEALTHCARE_ALLOW_WEEKENDS', false),

        // Display format for UI
        'display_format' => 'H:i',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure security alerts and notifications for sensitive operations.
    |
    */
    'security' => [
        // Notify security team on after-hours sensitive access
        'notify_after_hours' => env('HEALTHCARE_SECURITY_NOTIFY', true),

        // Alert email for security notifications
        'alert_email' => env('HEALTHCARE_SECURITY_EMAIL', 'security@hospital.com'),

        // Alert phone for SMS notifications (optional)
        'alert_phone' => env('HEALTHCARE_SECURITY_PHONE', null),

        // Enable webhook for security alerts
        'webhook_enabled' => env('HEALTHCARE_SECURITY_WEBHOOK', false),
        'webhook_url' => env('HEALTHCARE_SECURITY_WEBHOOK_URL', null),

        // Log all access attempts (true/false)
        'log_all_access' => env('HEALTHCARE_LOG_ALL_ACCESS', true),

        // Alert on cross-department access
        'alert_cross_department' => env('HEALTHCARE_ALERT_CROSS_DEPT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compliance Settings
    |--------------------------------------------------------------------------
    |
    | HIPAA and healthcare compliance configurations.
    |
    */
    'compliance' => [
        // Enable HIPAA compliance mode
        'hipaa_enabled' => env('HEALTHCARE_HIPAA_ENABLED', true),

        // Audit log retention period (days)
        'audit_retention_days' => env('HEALTHCARE_AUDIT_RETENTION', 2555), // 7 years

        // Enable access logging
        'enable_access_logging' => env('HEALTHCARE_ACCESS_LOGGING', true),

        // Log file path for compliance audits
        'compliance_log_path' => storage_path('logs/healthcare/compliance.log'),

        // Require reason for after-hours access
        'require_after_hours_reason' => env('HEALTHCARE_AFTER_HOURS_REASON', true),

        // Maximum failed access attempts before lockout
        'max_failed_attempts' => env('HEALTHCARE_MAX_FAILED_ATTEMPTS', 5),

        // Lockout duration (minutes)
        'lockout_duration' => env('HEALTHCARE_LOCKOUT_DURATION', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Trail Settings
    |--------------------------------------------------------------------------
    |
    | Configure audit trail logging for medical record access.
    |
    */
    'audit' => [
        // Enable database audit logging
        'database_logging' => env('HEALTHCARE_AUDIT_DB', true),

        // Enable file-based audit logging
        'file_logging' => env('HEALTHCARE_AUDIT_FILE', true),

        // Log channel name
        'log_channel' => 'healthcare_audit',

        // Security log channel name
        'security_log_channel' => 'healthcare_security',

        // Include request payload in logs
        'log_request_payload' => env('HEALTHCARE_LOG_PAYLOAD', false),

        // Include response data in logs
        'log_response_data' => env('HEALTHCARE_LOG_RESPONSE', false),

        // Anonymize patient data in logs (for compliance)
        'anonymize_logs' => env('HEALTHCARE_ANONYMIZE_LOGS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Role-Based Access Control (RBAC)
    |--------------------------------------------------------------------------
    |
    | Default role permissions for healthcare module.
    |
    */
    'rbac' => [
        // Enable strict RBAC enforcement
        'strict_mode' => env('HEALTHCARE_RBAC_STRICT', true),

        // Allow role override by admin
        'allow_admin_override' => env('HEALTHCARE_ADMIN_OVERRIDE', false),

        // Default role for new healthcare staff
        'default_role' => env('HEALTHCARE_DEFAULT_ROLE', 'nurse'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Patient Portal Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for patient-facing portal features.
    |
    */
    'patient_portal' => [
        // Enable patient portal
        'enabled' => env('HEALTHCARE_PATIENT_PORTAL', true),

        // Allow patients to book appointments
        'allow_appointment_booking' => env('HEALTHCARE_PORTAL_BOOKING', true),

        // Allow patients to view lab results
        'allow_lab_results' => env('HEALTHCARE_PORTAL_LAB_RESULTS', true),

        // Delay lab result visibility (hours) - for doctor review
        'lab_result_delay_hours' => env('HEALTHCARE_LAB_RESULT_DELAY', 24),

        // Allow patients to view prescriptions
        'allow_prescriptions' => env('HEALTHCARE_PORTAL_PRESCRIPTIONS', true),

        // Allow patients to view billing
        'allow_billing' => env('HEALTHCARE_PORTAL_BILLING', true),

        // Allow patients to download records
        'allow_download_records' => env('HEALTHCARE_PORTAL_DOWNLOAD', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Emergency Access Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for emergency/break-glass access scenarios.
    |
    */
    'emergency' => [
        // Enable emergency access override
        'enabled' => env('HEALTHCARE_EMERGENCY_ACCESS', true),

        // Require reason for emergency access
        'require_reason' => env('HEALTHCARE_EMERGENCY_REASON', true),

        // Alert security team on emergency access
        'alert_security' => env('HEALTHCARE_EMERGENCY_ALERT', true),

        // Auto-expire emergency access (minutes)
        'access_expiry_minutes' => env('HEALTHCARE_EMERGENCY_EXPIRY', 60),

        // Roles that can use emergency access
        'allowed_roles' => ['doctor', 'emergency_staff', 'admin'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Retention Settings
    |--------------------------------------------------------------------------
    |
    | Configure how long healthcare data is retained.
    |
    */
    'data_retention' => [
        // Medical records retention (years)
        'medical_records_years' => env('HEALTHCARE_RECORD_RETENTION', 10),

        // Audit logs retention (years)
        'audit_logs_years' => env('HEALTHCARE_AUDIT_RETENTION', 7),

        // Prescription history retention (years)
        'prescription_years' => env('HEALTHCARE_PRESCRIPTION_RETENTION', 5),

        // Appointment history retention (years)
        'appointment_years' => env('HEALTHCARE_APPOINTMENT_RETENTION', 3),

        // Billing records retention (years)
        'billing_years' => env('HEALTHCARE_BILLING_RETENTION', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure healthcare-related notifications.
    |
    */
    'notifications' => [
        // Enable email notifications
        'email_enabled' => env('HEALTHCARE_EMAIL_NOTIFICATIONS', true),

        // Enable SMS notifications
        'sms_enabled' => env('HEALTHCARE_SMS_NOTIFICATIONS', false),

        // Enable push notifications
        'push_enabled' => env('HEALTHCARE_PUSH_NOTIFICATIONS', false),

        // Notification channels for alerts
        'alert_channels' => ['mail', 'database'],

        // Quiet hours for non-critical notifications
        'quiet_hours_start' => 22,
        'quiet_hours_end' => 7,
    ],
];
