<?php

/**
 * Theme Configuration
 *
 * Configures theme system settings, available themes, and color schemes.
 * Supports both central admin and multi-tenant contexts (stancl/tenancy).
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Available Themes
    |--------------------------------------------------------------------------
    |
    | Defines the themes available to users.
    |
    */
    'available' => ['light', 'dark', 'system'],

    /*
    |--------------------------------------------------------------------------
    | Default Theme
    |--------------------------------------------------------------------------
    |
    | The default theme preference when a user has not set one.
    |
    */
    'default' => 'system',

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for stancl/tenancy awareness and per-tenant theme handling.
    |
    */
    'tenancy' => [
        'enabled' => env('TENANCY_ENABLED', true),
        'per_tenant_preferences' => true,  // Each tenant can have different theme settings
        'sync_central_preference' => false, // If false, central admin theme ≠ tenant theme
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Where and how to store theme preferences.
    |
    */
    'storage' => [
        'user_model_field' => 'theme',        // Column in users table
        'local_storage_key' => 'theme_preference', // Browser localStorage key
        'session_key' => 'user_theme',             // Session key as fallback
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Labels and Icons
    |--------------------------------------------------------------------------
    |
    | Human-readable labels and Font Awesome icons for each theme.
    |
    */
    'themes' => [
        'light' => [
            'label' => 'Light',
            'icon' => 'fas fa-sun',
            'description' => 'Light theme for daytime viewing',
        ],
        'dark' => [
            'label' => 'Dark',
            'icon' => 'fas fa-moon',
            'description' => 'Dark theme for reduced eye strain',
        ],
        'system' => [
            'label' => 'System',
            'icon' => 'fas fa-desktop',
            'description' => 'Follows your device settings',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Transition Settings
    |--------------------------------------------------------------------------
    |
    | Controls how theme transitions are animated.
    |
    */
    'transitions' => [
        'enabled' => true,
        'duration' => 150,          // milliseconds
        'easing' => 'ease-in-out',
        'respect_prefers_reduced_motion' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | CSS Class Configuration
    |--------------------------------------------------------------------------
    |
    | Classes applied to elements when dark mode is active.
    |
    */
    'css' => [
        'dark_class' => 'dark',
        'dark_mode_class' => 'dark-mode',
        'transition_class' => 'theme-transition',
    ],

    /*
    |--------------------------------------------------------------------------
    | Bootstrap Data Attribute
    |--------------------------------------------------------------------------
    |
    | Bootstrap 5 uses data-bs-theme for dark mode. Configure here.
    |
    */
    'bootstrap' => [
        'attribute' => 'data-bs-theme',
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable/disable specific theme features.
    |
    */
    'features' => [
        'keyboard_shortcuts' => true,     // Enable Ctrl+Shift+T shortcut
        'system_preference_detection' => true,
        'local_storage_sync' => true,
        'persist_to_database' => true,
        'smooth_transitions' => true,
        'show_tenancy_context' => env('APP_DEBUG', false), // Show tenant context in UI (debug only)
    ],
];
