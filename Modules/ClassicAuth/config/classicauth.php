<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This section defines the default settings for authentication within
    | the ClassicAuth module.
    |
    */

    'defaults' => [
        /*
        |--------------------------------------------------------------------------
        | Default Login Redirect
        |--------------------------------------------------------------------------
        |
        | This value defines where users are redirected after successful login
        | when no intended URL is present. You may change this value to any
        | route name or path within your application.
        |
        */
        'login_redirect' => env('AUTH_LOGIN_REDIRECT', 'dashboard'),

        /*
        |--------------------------------------------------------------------------
        | Default Logout Redirect
        |--------------------------------------------------------------------------
        |
        | This value defines where users are redirected after logging out.
        | You may change this value to any route name or path.
        |
        */
        'logout_redirect' => env('AUTH_LOGOUT_REDIRECT', '/'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Here you may configure the rate limiting settings for login attempts.
    | These values determine how many attempts are allowed before throttling.
    |
    */

    'rate_limiting' => [
        /*
        |--------------------------------------------------------------------------
        | IP-based Rate Limiting
        |--------------------------------------------------------------------------
        |
        | Maximum number of login attempts allowed per IP address within
        | the decay period.
        |
        */
        'ip' => [
            'max_attempts' => env('AUTH_IP_MAX_ATTEMPTS', 5),
            'decay_seconds' => env('AUTH_IP_DECAY_SECONDS', 60),
        ],

        /*
        |--------------------------------------------------------------------------
        | Email-based Rate Limiting
        |--------------------------------------------------------------------------
        |
        | Maximum number of login attempts allowed per email address within
        | the decay period. This helps prevent brute force attacks on specific
        | accounts.
        |
        */
        'email' => [
            'max_attempts' => env('AUTH_EMAIL_MAX_ATTEMPTS', 15),
            'decay_seconds' => env('AUTH_EMAIL_DECAY_SECONDS', 3600),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Login Tracking
    |--------------------------------------------------------------------------
    |
    | Configuration for login attempt tracking and security monitoring.
    |
    */

    'tracking' => [
        /*
        |--------------------------------------------------------------------------
        | Enable Login Tracking
        |--------------------------------------------------------------------------
        |
        | When enabled, all login attempts (successful and failed) will be
        | logged to the database for security monitoring and analytics.
        |
        */
        'enabled' => env('AUTH_TRACKING_ENABLED', true),

        /*
        |--------------------------------------------------------------------------
        | Suspicious Activity Thresholds
        |--------------------------------------------------------------------------
        |
        | These values determine when login activity is considered suspicious
        | and may trigger additional security measures.
        |
        */
        'suspicious_activity' => [
            // Failed attempts from same IP within 1 hour
            'ip_failures_threshold' => env('AUTH_IP_FAILURES_THRESHOLD', 10),
            'ip_failures_window' => env('AUTH_IP_FAILURES_WINDOW', 3600), // seconds

            // Failed attempts from different IPs for same email within 6 hours
            'email_ips_threshold' => env('AUTH_EMAIL_IPS_THRESHOLD', 5),
            'email_ips_window' => env('AUTH_EMAIL_IPS_WINDOW', 21600), // seconds
        ],

        /*
        |--------------------------------------------------------------------------
        | Retention Period
        |--------------------------------------------------------------------------
        |
        | How long to keep login attempt records in the database (in days).
        | Set to null to keep records indefinitely.
        |
        */
        'retention_days' => env('AUTH_TRACKING_RETENTION_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security-related settings for authentication.
    |
    */

    'security' => [
        /*
        |--------------------------------------------------------------------------
        | Authentication Minimum Time
        |--------------------------------------------------------------------------
        |
        | Minimum time in milliseconds that authentication should take.
        | This prevents timing attacks by ensuring consistent response times
        | regardless of whether the user exists or the password is correct.
        |
        */
        'auth_min_time_ms' => env('AUTH_MIN_TIME_MS', 300),
    ],

    'session' => [
        /*
        |--------------------------------------------------------------------------
        | Store Session Data
        |--------------------------------------------------------------------------
        |
        | When enabled, additional session data will be stored after login,
        | such as login IP, user agent, and timestamp.
        |
        */
        'store_login_data' => env('AUTH_STORE_SESSION_DATA', true),
    ],
];
