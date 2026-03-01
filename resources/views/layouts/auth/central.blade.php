@php
    $theme = 'system';
    if (auth()->check()) {
        $theme = auth()->user()->theme ?? 'system';
    }
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
    <title>@yield('title', 'Sign In') — {{ config('app.name', 'Ostrich') }}</title>

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
                // We set a flag because body may not be painted yet
                window.__authDarkMode = true;
            }
        })();
    </script>

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

    {{-- TALLStackUI Toasts --}}
    <x-ts-toast />

    {{-- Fallback: dispatch session flashes to TALLStackUI via Livewire events if needed --}}
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
        <div
            class="hidden md:flex w-1/2 bg-slate-900 border-r border-slate-800 relative items-center justify-center overflow-hidden">
            <!-- Premium Graphic Overlay -->
            <div class="absolute inset-0 bg-cover bg-center mix-blend-screen opacity-50"
                style="background-image: url('/img/login_illustration.png');"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 to-transparent via-slate-950/80"></div>

            <div class="relative z-10 px-12 py-16 flex flex-col justify-end h-full w-full max-w-2xl mx-auto">
                <div
                    class="mb-8 p-4 bg-white/5 border border-white/10 rounded-2xl w-20 h-20 flex items-center justify-center backdrop-blur-xl shadow-2xl">
                    <img src="{{ global_asset('img/fayiloli-icon.svg') }}" alt="Ostrich" class="w-12 h-12">
                </div>

                <h1 class="text-4xl font-black text-white tracking-tight mb-4">Ostrich EDMS</h1>
                <p class="text-lg text-slate-300 mb-10 leading-relaxed font-medium">
                    Secure, intelligent document management for government and modern organisations.<br>
                    Powered by NectarMetrics Solutions.
                </p>

                <div class="flex items-center gap-6 mt-auto">
                    <div>
                        <div class="text-3xl font-extrabold text-white">125K+</div>
                        <div class="text-sm font-semibold text-slate-400 mt-1 uppercase tracking-wider">Documents
                            Managed</div>
                    </div>
                    <div class="w-px h-12 bg-slate-700"></div>
                    <div>
                        <div class="text-3xl font-extrabold text-white">99.9%</div>
                        <div class="text-sm font-semibold text-slate-400 mt-1 uppercase tracking-wider">Uptime SLA</div>
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