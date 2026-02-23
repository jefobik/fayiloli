<?php

declare(strict_types=1);

/**
 * ─────────────────────────────────────────────────────────────────────────────
 * Livewire v4 — Production Configuration
 * Fayiloli EDMS · Multi-tenant · TALL Stack
 *
 * Published from vendor and trimmed to canonical v4 keys.
 * Do NOT add v3-only keys here; they will be silently ignored by Livewire v4
 * but cause confusion during debugging.
 * ─────────────────────────────────────────────────────────────────────────────
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Component Locations
    |--------------------------------------------------------------------------
    | Directories Livewire scans for view-based components (single/multi-file).
    | The make command creates new components in the first directory listed.
    */
    'component_locations' => [
        resource_path('views/livewire'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Component Namespaces
    |--------------------------------------------------------------------------
    | Namespaced aliases for component resolution. Livewire v4 resolves
    | `layouts::app` to `resources/views/layouts/app.blade.php`.
    */
    'component_namespaces' => [
        'layouts' => resource_path('views/layouts'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Page Layout
    |--------------------------------------------------------------------------
    | The layout used when a Livewire component is rendered as a full page via
    | `Route::get('/dashboard', DashboardStats::class)`.
    | Must point to a Blade component that exposes `{{ $slot }}`.
    */
    'component_layout' => 'layouts::app',

    /*
    |--------------------------------------------------------------------------
    | Lazy Loading Placeholder
    |--------------------------------------------------------------------------
    | Default placeholder view shown while a #[Lazy] component loads.
    | Set to null to use the built-in spinner; or point to a custom view.
    */
    'component_placeholder' => null,

    /*
    |--------------------------------------------------------------------------
    | Make Command Defaults
    |--------------------------------------------------------------------------
    | Controls what `php artisan make:livewire` generates by default.
    */
    'make_command' => [
        'type' => 'class',   // 'sfc', 'mfc', or 'class' — keep class-only style
        'emoji' => false,
        'with' => [
            'js' => false,
            'css' => false,
            'test' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Temporary File Uploads
    |--------------------------------------------------------------------------
    | Livewire stores file uploads in a temporary directory before they are
    | persisted permanently. Configure the disk, rules, and MIME types here.
    */
    'temporary_file_upload' => [
        'disk' => null,   // Default: 'default' (local)
        'rules' => null,   // Default: ['required', 'file', 'max:12288'] (12 MB)
        'directory' => null,   // Default: 'livewire-tmp'
        'middleware' => null,   // Default: 'throttle:60,1'
        'preview_mimes' => [
            'png',
            'gif',
            'bmp',
            'svg',
            'wav',
            'mp4',
            'mov',
            'avi',
            'wmv',
            'mp3',
            'm4a',
            'jpg',
            'jpeg',
            'mpga',
            'webp',
            'wma',
        ],
        'max_upload_time' => 5,  // Minutes before upload is invalidated
        'cleanup' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-inject Frontend Assets
    |--------------------------------------------------------------------------
    | When true, Livewire auto-inserts its JS (which includes Alpine v3) into
    | the <head> and before </body>. Keep true; we still include
    | @livewireStyles and @livewireScripts explicitly in `layouts/app.blade.php`
    | so asset ordering is predictable in relation to Bootstrap and Chart.js.
    */
    'inject_assets' => true,

    /*
    |--------------------------------------------------------------------------
    | Navigate (SPA mode)
    |--------------------------------------------------------------------------
    | Add `wire:navigate` to <a> links to fetch pages via AJAX like a SPA.
    */
    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#7c3aed',  // Brand primary — matches EDMS palette
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Theme
    |--------------------------------------------------------------------------
    | 'tailwind' or 'bootstrap'. This project uses Tailwind v4.
    */
    'pagination_theme' => 'tailwind',

    /*
    |--------------------------------------------------------------------------
    | Release Token
    |--------------------------------------------------------------------------
    | Stored client-side; on mismatch after a deploy Livewire prompts a reload.
    | Driven by LIVEWIRE_RELEASE_TOKEN env var so deploys auto-rotate it.
    */
    'release_token' => env('LIVEWIRE_RELEASE_TOKEN', 'v4-' . substr(md5(config('app.key', 'base')), 0, 8)),

    /*
    |--------------------------------------------------------------------------
    | CSP Safe
    |--------------------------------------------------------------------------
    | Set to true only if you enforce a strict Content-Security-Policy that
    | disallows inline scripts. Enables the CSP-safe Alpine build.
    */
    'csp_safe' => false,

    /*
    |--------------------------------------------------------------------------
    | Payload Guards (v4 — DoS protection)
    |--------------------------------------------------------------------------
    | Protects against oversized or deeply-nested payloads. Tune max_size
    | upward if large file-upload previews are needed (they go through a
    | separate signed URL endpoint, not the Livewire payload channel).
    */
    'payload' => [
        'max_size' => 1024 * 1024,  // 1 MB — Livewire network requests only
        'max_nesting_depth' => 10,
        'max_calls' => 50,
        'max_components' => 20,
    ],

];
