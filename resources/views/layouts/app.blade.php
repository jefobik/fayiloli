@php
    $theme = 'system';
    if (auth()->check()) {
        $theme = auth()->user()->theme ?? 'system';
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'OSTRICH') }} — Enterprise Document Management</title>

    {{-- Favicon — SVG (modern) + ICO (legacy fallback) --}}
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/img/fayiloli-icon.svg">

    {{-- Tailwind v4 + Alpine.js + Chart.js (via Vite) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Livewire styles (Auto-injected in v3+) --}}

    {{-- TallStackUI styles & scripts --}}
    @tallStackUiStyle
    @tallStackUiScript

    {{-- Livewire scripts (Auto-injected in v3+) --}}

    <script>
        (function () {
            var theme = '{{ $theme }}';
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            var isDark = theme === 'dark' || (theme === 'system' && prefersDark);

            if (isDark) {
                document.documentElement.classList.add('dark', 'dark-mode');
            }
        })();
    </script>

    {{-- ── Tenant Custom Branding Injection ── --}}
    @php
        $currentTenant = tenancy()->initialized ? tenancy()->tenant : null;
        $primaryColor = $currentTenant?->settings['brand_color'] ?? '#7c3aed'; // Default to our violet
        $primaryHover = $currentTenant?->settings['brand_hover'] ?? '#6d28d9';
    @endphp
    <style>
        :root {
            --tenant-primary:
                {{ $primaryColor }}
            ;
            --tenant-primary-hover:
                {{ $primaryHover }}
            ;
        }
    </style>
</head>

    <body class="h-full bg-[var(--app-bg)] text-[var(--text-main)] font-sans antialiased selection:bg-indigo-100 selection:text-indigo-900 {{ auth()->check() ? 'overflow-hidden' : '' }}">

        {{-- ── Main Layout Content ── --}}
        @if(isset($slot))
            {{ $slot }}
        @else
            @yield('content')
        @endif

        {{-- ── JQUERY (Required by legacy scripts) ── --}}
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

        {{-- ── Authenticated User Scripts & Assets ── --}}
        {{-- ── Authenticated User Scripts & Assets ── --}}
        @auth
            <script src="/custom-js/legacy-edms-helpers.js" defer></script>
            <script>
                // ─── Alpine.js Bridge — Shared Sidebar/Theme State ───────────────────────
                document.addEventListener('alpine:init', () => {
                    // Sidebar bridge: Mirror Alpine-managed state to legacy CSS classes
                    Alpine.effect(() => {
                        const shell = document.getElementById('appShell');
                        if (!shell) return;
                        const alpineData = Alpine.$data(shell);
                        if (!alpineData) return;
                        alpineData.sidebarOpen ? shell.classList.remove('nav-closed') : shell.classList.add('nav-closed');
                    });
                });

                /**
                 * Toggle dark mode and persist to user preferences.
                 * Bridge between Alpine/Tailwind and legacy fa-icon states.
                 */
                function edmsDarkModeToggle() {
                    var isDark = document.body.classList.toggle('dark-mode');
                    document.documentElement.classList.toggle('dark', isDark);
                    localStorage.setItem('darkMode', isDark);
                    var icon = document.getElementById('darkModeIcon');
                    if (icon) {
                        icon.classList.toggle('fa-moon', !isDark);
                        icon.classList.toggle('fa-sun', isDark);
                    }
                }
            </script>

            {{-- ── Flash Messages ── --}}
            @if(session()->hasAny(['success', 'error', 'warning', 'info']))
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        if (typeof edmsToast !== 'function') return;
                        @if(session('success')) edmsToast(@json(session('success')), 'success'); @endif
                        @if(session('error'))   edmsToast(@json(session('error')), 'error'); @endif
                        @if(session('warning')) edmsToast(@json(session('warning')), 'warning'); @endif
                        @if(session('info'))    edmsToast(@json(session('info')), 'info'); @endif
                    });
                </script>
            @endif
        @endauth

    </body>
</html>