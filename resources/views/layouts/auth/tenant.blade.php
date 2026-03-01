@php
    $theme = 'system';
    if (auth()->check()) {
        $theme = auth()->user()->theme ?? 'system';
    }

    $currentTenant = tenancy()->initialized ? tenancy()->tenant : null;
    $primaryColor = $currentTenant?->settings['brand_color'] ?? '#4f46e5';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ 
          theme: '{{ $theme }}',
          init() {
              this.applyTheme();
              window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                  if (this.theme === 'system') this.applyTheme();
              });
          },
          applyTheme() {
              let stored = localStorage.getItem('darkMode');
              let isDark = stored !== null 
                  ? stored === 'true' 
                  : (this.theme === 'dark' || (this.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches));
              
              if (isDark) {
                  document.documentElement.classList.add('dark');
                  document.body.classList.add('dark-mode');
              } else {
                  document.documentElement.classList.remove('dark');
                  document.body.classList.remove('dark-mode');
              }
          }
      }">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign In') — {{ $currentTenant?->organization_name ?? config('app.name', 'Ostrich') }}
    </title>

    {{-- Fallback FOUC prevention --}}
    <script>
        (function () {
            var stored = localStorage.getItem('darkMode');
            var theme = '{{ $theme }}';
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            var isDark = stored !== null
                ? stored === 'true'
                : (theme === 'dark' || (theme === 'system' && prefersDark));
            if (isDark) {
                document.documentElement.classList.add('dark');
                window.__authDarkMode = true;
            }
        })();
    </script>

    <style>
        :root {
            --color-primary:
                {{ $primaryColor }}
            ;
            --tw-ring-color:
                {{ $primaryColor }}
            ;
        }
    </style>

    <link rel="icon" type="image/svg+xml" href="{{ global_asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ global_asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @tallStackUiStyle
    @tallStackUiScript
</head>

<body
    class="min-h-screen bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-200 font-sans antialiased text-base transition-colors duration-200 ease-in-out flex"
    onload="if(window.__authDarkMode) document.body.classList.add('dark-mode')">

    <x-ts-toast />

    @if(session()->hasAny(['success', 'error', 'warning', 'info']))
        <script>
            document.addEventListener('alpine:init', () => {
                                    @if(session('success')) window.$interaction('toast').success('Success', @json(session('success'))).send(); @endif
                @if(session('error'))   window.$interaction('toast').error('Error', @json(session('error'))).send(); @endif
                @if(session('warning')) window.$interaction('toast').warning('Warning', @json(session('warning'))).send(); @endif
                @if(session('info'))    window.$interaction('toast').info('Info', @json(session('info'))).send(); @endif
                                });
        </script>
    @endif

    <div class="flex-1 flex w-full min-h-screen">
        <!-- Visual Panel (Hidden on Mobile) -->
        <div class="hidden md:flex w-1/2 relative items-center justify-center overflow-hidden"
            style="background-color: var(--color-primary);">

            <div class="absolute inset-0 mix-blend-overlay opacity-80 bg-cover bg-center"
                style="background-image: url('/img/login_illustration.png');" filter: grayscale(100%);">
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent via-black/30"></div>

            <div class="relative z-10 px-12 py-16 flex flex-col justify-end h-full w-full max-w-2xl mx-auto">
                <div
                    class="mb-8 p-4 bg-white/10 border border-white/20 rounded-2xl w-20 h-20 flex items-center justify-center backdrop-blur-xl shadow-2xl">
                    <span class="text-3xl font-extrabold text-white tracking-widest leading-none">
                        {{ strtoupper(substr($currentTenant?->organization_name ?? 'OR', 0, 1) . substr(explode(' ', $currentTenant?->organization_name ?? 'Organization')[1] ?? '', 0, 1)) }}
                    </span>
                </div>

                <h1 class="text-4xl font-black text-white tracking-tight mb-4">
                    {{ $currentTenant?->organization_name ?? 'Organization' }}
                </h1>
                <p class="text-lg text-white/90 mb-10 leading-relaxed font-medium">
                    <strong class="text-white">{{ $currentTenant?->plan_label ?? 'Enterprise' }}</strong> &middot;
                    Secure EDMS Workspace<br>
                    Sign in to access documents, workspaces, and enterprise collaboration tools.
                </p>

                <div class="flex items-center gap-6 mt-auto">
                    <div>
                        <div class="text-3xl font-extrabold text-white">125K+</div>
                        <div class="text-sm font-semibold text-white/70 mt-1 uppercase tracking-wider">Documents Managed
                        </div>
                    </div>
                    <div class="w-px h-12 bg-white/20"></div>
                    <div>
                        <div class="text-3xl font-extrabold text-white">48</div>
                        <div class="text-sm font-semibold text-white/70 mt-1 uppercase tracking-wider">Active Workspaces
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Panel -->
        <main class="w-full md:w-1/2 flex flex-col justify-center bg-white dark:bg-slate-950 relative overflow-y-auto">
            <div class="w-full max-w-md px-6 py-12 lg:px-12 mx-auto">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Bootstrap JS ONLY --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>

</html>