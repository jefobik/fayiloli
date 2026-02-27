@php
    $theme = 'system';
    if (auth()->check()) {
        $theme = auth()->user()->theme ?? 'system';
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign In') — {{ config('app.name', 'Ostrich') }}</title>

    {{-- Dark mode detection before paint (FOUC prevention) --}}
    <script>
        (function () {
            var stored = localStorage.getItem('darkMode');
            var theme  = '{{ $theme }}';
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            var isDark = stored !== null
                ? stored === 'true'
                : (theme === 'dark' || (theme === 'system' && prefersDark));
            if (isDark) document.body && document.body.classList.add('dark-mode');
            // Set flag for body onload to pick up (body not yet parsed)
            window.__authDarkMode = isDark;
        })();
    </script>

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon"  href="/favicon.ico">

    {{-- Inter font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    {{-- FontAwesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Vite: Tailwind + app CSS + JS (includes auth CSS classes from app.css lines 340+) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Livewire styles (required for Alpine.js bundled with Livewire v4) --}}
    @livewireStyles
</head>
<body onload="if(window.__authDarkMode) document.body.classList.add('dark-mode')">

{{-- ── Session Flash Toasts (Alpine-based, no Bootstrap dependency) ────── --}}
@if(session('success') || session('error') || session('warning') || session('info'))
<div id="auth-toast-container"
     aria-live="polite" aria-atomic="true"
     style="position:fixed;top:1.25rem;right:1.25rem;z-index:9999;display:flex;flex-direction:column;gap:0.5rem;pointer-events:none">
    @foreach(['success' => ['fa-circle-check','#10b981'], 'error' => ['fa-circle-xmark','#ef4444'], 'warning' => ['fa-triangle-exclamation','#f59e0b'], 'info' => ['fa-circle-info','#7c3aed']] as $type => [$icon, $color])
        @if(session($type))
        <div role="alert"
             style="display:flex;align-items:flex-start;gap:0.75rem;background:#1e293b;color:#f1f5f9;border-radius:12px;padding:0.9rem 1.1rem;font-size:0.875rem;min-width:280px;max-width:360px;box-shadow:0 8px 24px rgba(0,0,0,0.2);pointer-events:auto;border-left:4px solid {{ $color }};animation:slideUp 0.3s ease">
            <i class="fa-solid {{ $icon }}" aria-hidden="true" style="color:{{ $color }};font-size:1rem;flex-shrink:0;margin-top:1px"></i>
            <div style="flex:1;min-width:0">
                <div style="font-weight:700;font-size:0.78rem;text-transform:capitalize;margin-bottom:0.15rem">{{ ucfirst($type) }}</div>
                <div style="font-size:0.82rem;opacity:0.85;line-height:1.4">{{ session($type) }}</div>
            </div>
        </div>
        @endif
    @endforeach
</div>
<script>
    setTimeout(function () {
        var c = document.getElementById('auth-toast-container');
        if (c) c.style.opacity = '0', c.style.transition = 'opacity 0.4s', setTimeout(function(){ c.remove(); }, 400);
    }, 5000);
</script>
@endif

{{-- ── Page Content ──────────────────────────────────────────────────────── --}}
<main id="auth-main" tabindex="-1">
    @yield('content')
</main>

{{-- Livewire scripts (bundles Alpine.js — required) --}}
@livewireScripts

@stack('scripts')
</body>
</html>
