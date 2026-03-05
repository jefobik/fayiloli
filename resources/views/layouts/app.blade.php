@php
    // ── ThemeService — single source of truth for theme preference ──────────────
    $themeService  = app(\App\Services\ThemeService::class);
    $theme         = $themeService->getThemePreference();

    // ── Tenant branding ──────────────────────────────────────────────────────────
    $currentTenant = tenancy()->initialized ? tenancy()->tenant : null;
    $tenantName    = $currentTenant?->organization_name ?? config('app.name', 'Fayiloli');

    // Brand colours (per-tenant override; falls back to design-system defaults)
    $primaryColor  = $currentTenant?->settings['brand_color']  ?? 'var(--primary-600)';
    $primaryHover  = $currentTenant?->settings['brand_hover']  ?? 'var(--primary-700)';

    // Per-tenant logo / favicon (optional — gracefully degrades to platform defaults)
    $tenantLogoUrl    = $currentTenant?->settings['logo_url']    ?? null;
    $tenantFaviconUrl = $currentTenant?->settings['favicon_url'] ?? null;

    // View density: user preference → tenant default → 'comfortable'
    // Drives CSS [data-density] selectors (--density-* tokens in theme.css)
    $density = null;
    if (auth()->check()) {
        $density = auth()->user()->getPreference('view_density');
    }
    if (!$density) {
        $density = $currentTenant?->settings['default_density'] ?? 'comfortable';
    }
    $density = in_array($density, ['comfortable', 'cozy', 'compact']) ? $density : 'comfortable';

    // color-scheme meta: tells browser native chrome (scrollbar, inputs) which mode
    // to render BEFORE the JS theme script runs — eliminates any chrome-level FOUC.
    $colorScheme = match ($theme) {
        'dark'  => 'dark',
        'light' => 'light',
        default => 'light dark',
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full"
      data-theme="{{ $theme }}"
      data-density="{{ $density }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- colour-scheme: native chrome (scrollbar, date inputs) honours this
         BEFORE any JS runs, eliminating browser-chrome FOUC. --}}
    <meta name="color-scheme" content="{{ $colorScheme }}">

    {{-- Authenticated tenant workspaces must never be indexed by search engines --}}
    @auth
        <meta name="robots" content="noindex, nofollow">
    @endauth

    <title>{{ $tenantName }} — {{ config('app.name', 'Fayiloli') }}</title>
    <meta name="description" content="Enterprise document management workspace for {{ $tenantName }}.">

    {{-- ── Favicons — prefer per-tenant asset, fall back to platform icon ─────── --}}
    @if($tenantFaviconUrl)
        <link rel="icon" href="{{ $tenantFaviconUrl }}">
    @else
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="icon" type="image/x-icon"  href="/favicon.ico">
    @endif
    <link rel="apple-touch-icon" href="{{ $tenantFaviconUrl ?? '/img/fayiloli-icon.svg' }}">

    {{-- ── Theme bootstrap script (FIRST script tag — MUST precede all CSS) ─────
         Seeds window.__themeConfig, window.__tenantContext, window.__themePreference,
         applies dark/light classes and data-theme / data-bs-theme attributes
         SYNCHRONOUSLY before the browser paints a single pixel. --}}
    {!! $themeService->generateThemeBootstrapScript($theme) !!}

    {{-- ── Per-tenant CSS variable overrides (inline :root) ──────────────────────
         Placed BEFORE @vite so downstream CSS calc()s and color-mix()s that reference
         --tenant-primary already see the correct value from first parse. --}}
    <style>
        :root {
            --tenant-primary:       {{ $primaryColor }};
            --tenant-primary-hover: {{ $primaryHover }};
            @if($tenantLogoUrl)
            --tenant-logo-url:      url('{{ $tenantLogoUrl }}');
            @endif
        }
    </style>

    {{-- Livewire styles (inject_assets is false in config/livewire.php) --}}
    @livewireStyles

    {{-- Vite: Tailwind CSS v4 + Alpine.js shim + Chart.js + jQuery --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- TALLStackUI component styles and Alpine plugin setup --}}
    @tallStackUiStyle
    @tallStackUiScript

    {{-- Per-page head injection (meta, preload hints, page-level fonts, etc.) --}}
    @stack('head')
</head>

<body class="h-full bg-[var(--app-bg)] text-[var(--text-main)] font-sans antialiased
             selection:bg-[var(--gw-blue-50)] selection:text-[var(--gw-blue-700)]
             {{ auth()->check() ? 'overflow-hidden' : '' }}"
      data-user-theme="{{ $theme }}">

    {{-- Skip navigation link — WCAG 2.4.1. Must be the first focusable element. --}}
    @auth
        <a href="#main-content" class="gw-skip-link" tabindex="1">
            Skip to main content
        </a>
    @endauth

    {{-- ── Main layout slot / content section ───────────────────────────────── --}}
    @if(isset($slot))
        {{ $slot }}
    @else
        @yield('content')
    @endif

    {{-- ── Authenticated page scripts ────────────────────────────────────────── --}}
    @auth
        {{-- Legacy EDMS helpers (jQuery-dependent utilities). jQuery is now bundled
             via Vite (app.js → window.$ = window.jQuery = jQuery) so this deferred
             script will find $ already available on the window object. --}}
        <script src="/custom-js/legacy-edms-helpers.js" defer></script>

        <script>
            /**
             * Sidebar CSS class bridge.
             * Keeps the legacy `.nav-closed` class in sync with AppShell's
             * sidebarCollapsed prop so any legacy CSS selectors continue to work.
             */
            document.addEventListener('alpine:init', () => {
                Alpine.effect(() => {
                    const shell = document.getElementById('appShell');
                    if (!shell) return;
                    const collapsed = shell.hasAttribute('data-sidebar-collapsed');
                    shell.classList.toggle('nav-closed', collapsed);
                });
            });

            /**
             * Global ⌘K / Ctrl+K — focuses the omnibox search.
             * GlobalSearch Livewire component listens for the 'search-focus' window event.
             */
            document.addEventListener('keydown', function (e) {
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    window.dispatchEvent(new CustomEvent('search-focus'));
                }
            });

            /**
             * ⌘Shift+Q / Ctrl+Shift+Q — keyboard shortcut to sign out.
             * Submits the CSRF-protected logout form already present in header.blade.php.
             */
            document.addEventListener('keydown', function (e) {
                if ((e.metaKey || e.ctrlKey) && e.shiftKey && e.key === 'Q') {
                    e.preventDefault();
                    const form = document.getElementById('logout-form');
                    if (form) form.submit();
                }
            });
        </script>

        {{-- ── Session flash → TALLStackUI toast bridge ─────────────────────── --}}
        @if(session()->hasAny(['success', 'error', 'warning', 'info']))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (typeof window.edmsToast !== 'function') return;
                    @if(session('success')) window.edmsToast(@json(session('success')), 'success'); @endif
                    @if(session('error'))   window.edmsToast(@json(session('error')),   'error');   @endif
                    @if(session('warning')) window.edmsToast(@json(session('warning')), 'warning'); @endif
                    @if(session('info'))    window.edmsToast(@json(session('info')),    'info');    @endif
                });
            </script>
        @endif
    @endauth

    @livewireScripts
    @stack('scripts')

</body>

</html>
