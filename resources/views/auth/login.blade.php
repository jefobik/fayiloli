@extends(tenancy()->initialized ? 'layouts.auth.tenant' : 'layouts.auth.central')

@php
    $org = tenancy()->initialized ? tenancy()->tenant : null;
@endphp

@section('content')

    <div class="mb-8">
        {{-- Mobile Logo / Brand --}}
        <div class="lg:hidden flex items-center gap-3 mb-8">
            <img src="{{ global_asset('img/fayiloli-icon.svg') }}" alt="Ostrich" class="w-10 h-10 rounded-lg shadow-sm">
            <span class="font-bold text-xl text-slate-900 dark:text-white">Ostrich {{ app()->version() }}</span>
        </div>

        @if ($org)
            <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mb-2">Welcome back</h2>
            <p class="text-slate-600 dark:text-slate-400">Sign in to your <strong
                    class="text-slate-900 dark:text-white">{{ $org->organization_name }}</strong> workspace</p>
        @else
            <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mb-2">Sign in to your account</h2>
            <p class="text-slate-600 dark:text-slate-400">Enter your credentials to access your administrative workspace</p>
        @endif
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div
            class="mb-6 bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-900/50 p-4 rounded-xl flex items-start gap-3 shadow-sm">
            <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 mt-0.5"></i>
            <ul class="list-none p-0 m-0 text-red-800 dark:text-red-300 text-sm font-medium space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" x-data="{ showPw: false, loading: false }" @submit="loading = true"
        novalidate class="space-y-6">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Email
                address</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class="fas fa-envelope text-slate-400 dark:text-slate-500 text-sm"></i>
                </div>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@organisation.gov.ng"
                    required autocomplete="email" autofocus :readonly="loading"
                    class="block w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-sm rounded-xl focus:ring-[var(--color-primary,#7c3aed)] focus:border-[var(--color-primary,#7c3aed)] transition-all placeholder:text-slate-400 shadow-sm">
            </div>
        </div>

        {{-- Password --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <label for="password"
                    class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                        class="text-sm font-medium text-[var(--color-primary,#7c3aed)] hover:text-[var(--color-primary,#6d28d9)] dark:hover:text-[var(--color-primary,#a78bfa)] hover:underline transition-colors">Forgot
                        password?</a>
                @endif
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-slate-400 dark:text-slate-500 text-sm"></i>
                </div>
                <input :type="showPw ? 'text' : 'password'" id="password" name="password" placeholder="••••••••" required
                    autocomplete="current-password" :readonly="loading"
                    class="block w-full pl-10 pr-12 py-3 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-sm rounded-xl focus:ring-[var(--color-primary,#7c3aed)] focus:border-[var(--color-primary,#7c3aed)] transition-all placeholder:text-slate-400 shadow-sm">
                <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
                    <button type="button" @click="showPw = !showPw" :disabled="loading"
                        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-primary,#7c3aed)]">
                        <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Remember --}}
        <div class="flex items-center">
            <input type="checkbox" id="remember" name="remember"
                class="w-4 h-4 rounded text-[var(--color-primary,#7c3aed)] focus:ring-[var(--color-primary,#7c3aed)] border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:checked:bg-[var(--color-primary,#7c3aed)] cursor-pointer transition-colors">
            <label for="remember" class="ml-2.5 text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">Keep
                me signed in</label>
        </div>

        <button type="submit" :disabled="loading"
            class="w-full flex items-center justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-[var(--color-primary,#7c3aed)] hover:bg-[var(--color-primary,#6d28d9)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-primary,#7c3aed)] dark:focus:ring-offset-slate-950 transition-all disabled:opacity-75 disabled:cursor-not-allowed">
            <span x-show="loading" x-cloak
                class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin mr-2"></span>
            <i class="fas fa-sign-in-alt mt-px mr-2 text-[0.95rem]" x-show="!loading"></i>
            <span x-text="loading ? 'Authenticating…' : 'Sign In'"></span>
        </button>
    </form>

    <div class="mt-8">
        @if ($org)
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200 dark:border-slate-800"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-3 bg-white dark:bg-slate-950 text-slate-500">Need another workspace?</span>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ rtrim(config('app.url'), '/') }}/portal"
                    class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition-colors">
                    <i class="fas fa-arrow-left text-xs mr-2"></i> Find a different organisation
                </a>
            </div>
        @elseif (Route::has('register'))
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200 dark:border-slate-800"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-3 bg-white dark:bg-slate-950 text-slate-500">Don't have an account?</span>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ route('register') }}"
                    class="inline-flex items-center text-sm font-medium text-[var(--color-primary,#7c3aed)] hover:text-[var(--color-primary,#6d28d9)] dark:hover:text-[var(--color-primary,#a78bfa)] transition-colors">
                    Request access <i class="fas fa-arrow-right text-xs ml-2"></i>
                </a>
            </div>
        @endif
    </div>

    <div class="mt-12 text-center text-xs text-slate-500 font-medium tracking-wide">
        &copy; {{ date('Y') }} NECTARMETRICS SOLUTIONS LIMITED.
    </div>

@endsection