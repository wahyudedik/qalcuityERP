<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Password Policy Configuration
    |--------------------------------------------------------------------------
    |
    | This file defines the password requirements and policies for the application.
    | These settings enforce strong passwords and prevent common security issues.
    |
    */

    'min_length' => env('PASSWORD_MIN_LENGTH', 12),

    'require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),

    'require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),

    'require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),

    'require_special_chars' => env('PASSWORD_REQUIRE_SPECIAL_CHARS', true),

    'special_chars' => '!@#$%^&*()_+-=[]{}|;:,.<>?',

    /*
    |--------------------------------------------------------------------------
    | Common Password Prevention
    |--------------------------------------------------------------------------
    */

    'prevent_common_passwords' => env('PASSWORD_PREVENT_COMMON', true),

    'common_passwords_file' => resource_path('data/common-passwords.txt'),

    /*
    |--------------------------------------------------------------------------
    | Username Prevention
    |--------------------------------------------------------------------------
    */

    'prevent_username_in_password' => env('PASSWORD_PREVENT_USERNAME', true),

    'prevent_email_in_password' => env('PASSWORD_PREVENT_EMAIL', true),

    /*
    |--------------------------------------------------------------------------
    | Password History (prevent reuse)
    |--------------------------------------------------------------------------
    */

    'prevent_reuse_count' => env('PASSWORD_HISTORY_COUNT', 5),

    /*
    |--------------------------------------------------------------------------
    | Password Expiration
    |--------------------------------------------------------------------------
    */

    'max_age_days' => env('PASSWORD_MAX_AGE_DAYS', 90),

    'expiration_warning_days' => env('PASSWORD_EXPIRATION_WARNING_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Breached Password Detection (using HIBP API)
    |--------------------------------------------------------------------------
    */

    'check_breached_passwords' => env('PASSWORD_CHECK_BREACHED', false),

    'hibp_api_timeout' => 5,

    /*
    |--------------------------------------------------------------------------
    | Complexity Score (optional advanced feature)
    |--------------------------------------------------------------------------
    */

    'min_complexity_score' => env('PASSWORD_MIN_COMPLEXITY_SCORE', 3),

    'complexity_weights' => [
        'length' => 1,
        'uppercase' => 1,
        'lowercase' => 1,
        'numbers' => 1,
        'special_chars' => 2,
        'mixed_case' => 1,
    ],
];
