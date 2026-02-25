@extends('layouts.app')

@php
    // Org context is only available on tenant subdomains.
    $org = tenancy()->initialized ? tenancy()->tenant : null;
@endphp

@section('content')
<div class="auth-shell">

    {{-- ── Visual Panel (left) ─────────────────────────────────────────── --}}
    <div class="auth-visual" aria-hidden="true">
        <div class="auth-visual-content">
            @if ($org)
                {{-- Tenant-branded left panel --}}
                @php
                    $gradients = [
                        'government'  => ['#dc2626','#b91c1c'],
                        'secretariat' => ['#4f46e5','#7c3aed'],
                        'agency'      => ['#0284c7','#0369a1'],
                        'department'  => ['#16a34a','#15803d'],
                        'unit'        => ['#d97706','#b45309'],
                    ];
                    $gc = $gradients[$org->tenant_type?->value ?? ''] ?? ['#475569','#334155'];
                @endphp
                <div class="auth-visual-icon" style="background:linear-gradient(135deg,{{ $gc[0] }},{{ $gc[1] }});width:96px;height:96px;display:flex;align-items:center;justify-content:center;margin-bottom:2rem">
                    <span style="font-size:2.5rem;font-weight:800;color:#fff;letter-spacing:0.02em">
                        {{ method_exists($org, 'getInitialsAttribute') ? $org->initials : strtoupper(substr($org->organization_name, 0, 1) . substr(explode(' ', $org->organization_name)[1] ?? '', 0, 1)) }}
                    </span>
                </div>
                <div class="auth-visual-title">{{ $org->organization_name }}</div>
                <div class="auth-visual-desc">
                    <strong>{{ $org->plan_label }}</strong> &middot; Secure EDMS Workspace<br>
                    Sign in with your organisation credentials to access documents, workspaces, and robust enterprise collaboration tools.
                </div>
            @else
                {{-- Central / generic left panel --}}
                <div class="auth-visual-icon" style="background: #fff; padding: 1rem; border-radius: 20px;">
                    <img src="{{ global_asset('img/fayiloli-icon.svg') }}"
                         alt=""
                         aria-hidden="true"
                         width="80" height="80">
                </div>
                <div class="auth-visual-title">Fayiloli<br>Enterprise EDMS</div>
                <div class="auth-visual-desc">
                    Secure, intelligent document management for government and modern organisations.<br><br>
                    Powered by NectarMetrics Solutions Limited — delivering intelligent workflows, real-time insights, and powerful role-based access control.
                </div>
            @endif

            <div class="auth-features">
                <div class="auth-feature">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <span>Intelligent workflow-based &amp; full-text search across all documents.</span>
                </div>
                <div class="auth-feature">
                    <i class="fas fa-shield-alt" aria-hidden="true"></i>
                    <span>Enterprise-grade role-based access control and strict workspace isolation.</span>
                </div>
                <div class="auth-feature">
                    <i class="fas fa-chart-line" aria-hidden="true"></i>
                    <span>Real-time activity tracking, detailed audit logs, and analytics.</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Form Panel (right) ──────────────────────────────────────────── --}}
    <div class="auth-form-panel">
        <div class="auth-form-inner">

            <div class="auth-form-header">
                {{-- Logo / Brand --}}
                <div class="auth-form-logo" aria-hidden="true">
                    <img src="{{ global_asset('img/fayiloli-icon.svg') }}"
                         alt=""
                         aria-hidden="true"
                         width="40" height="40"
                         style="border-radius:10px;flex-shrink:0">
                    <span class="brand">Fayiloli v{{ app()->version() }}</span>
                </div>

                @if ($org)
                    <h2 class="auth-form-title">Welcome back</h2>
                    <p class="auth-form-sub">Sign in to your {{ $org->organization_name }} workspace</p>
                @else
                    <h2 class="auth-form-title">Sign in to your account</h2>
                    <p class="auth-form-sub">Enter your credentials to access your administrative workspace</p>
                @endif
            </div>

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div role="alert" aria-live="assertive"
                     style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:1rem;margin-bottom:1.5rem">
                    <div style="display:flex;align-items:flex-start;gap:0.75rem;color:#dc2626;font-size:0.9rem;font-weight:600">
                        <i class="fas fa-exclamation-circle" aria-hidden="true" style="margin-top:0.15rem;flex-shrink:0"></i>
                        <ul style="margin:0;padding:0;list-style:none">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}"
                  x-data="{ showPw: false, loading: false }"
                  @submit="loading = true"
                  novalidate>
                @csrf

                {{-- Email --}}
                <div class="auth-field">
                    <label for="email">Email address</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-envelope input-icon" aria-hidden="true"></i>
                        <input
                            type="email" id="email" name="email"
                            value="{{ old('email') }}"
                            placeholder="you@organisation.gov.ng"
                            required autocomplete="email" autofocus
                            aria-required="true"
                            :readonly="loading"
                            @if ($errors->has('email')) aria-invalid="true" style="border-color:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,0.1);" @endif
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div class="auth-field">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem">
                        <label for="password" style="margin-bottom:0;">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="auth-link" style="font-size:0.85rem;font-weight:500">
                                Forgot password?
                            </a>
                        @endif
                    </div>
                    <div class="auth-input-wrap">
                        <i class="fas fa-lock input-icon" aria-hidden="true"></i>
                        <input
                            type="password"
                            :type="showPw ? 'text' : 'password'"
                            id="password" name="password"
                            placeholder="••••••••"
                            required autocomplete="current-password"
                            aria-required="true"
                            :readonly="loading"
                            :aria-label="showPw ? 'Password (visible)' : 'Password'"
                            @if ($errors->has('password')) aria-invalid="true" style="border-color:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,0.1);" @endif
                        >
                        <button type="button"
                                class="toggle-pw"
                                @click="showPw = !showPw"
                                :readonly="loading"
                                :aria-pressed="showPw.toString()"
                                :aria-label="showPw ? 'Hide password' : 'Show password'">
                            <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div style="display:flex;align-items:center;gap:0.75rem;margin-top:0.5rem">
                    <input type="checkbox" id="remember" name="remember"
                           style="width:18px;height:18px;accent-color:#6d28d9;cursor:pointer;border-radius:4px;border:1px solid #cbd5e1;"
                           onclick="return !document.querySelector('.auth-submit').disabled">
                    <label for="remember" style="font-size:0.9rem;color:#475569;cursor:pointer;margin:0;font-weight:500;">
                        Keep me signed in for 30 days
                    </label>
                </div>

                <button type="submit" class="auth-submit" :disabled="loading" :aria-busy="loading.toString()">
                    <span x-show="loading" x-cloak aria-hidden="true"
                          style="display:inline-block;width:18px;height:18px;border:2px solid rgba(255,255,255,0.4);border-top-color:#fff;border-radius:50%;animation:auth-spin 0.7s linear infinite;margin-right:0.5rem"></span>
                    
                    <i class="fas fa-sign-in-alt" aria-hidden="true" x-show="!loading" style="font-size:1rem;margin-right:0.25rem"></i>
                    <span x-text="loading ? 'Authenticating…' : 'Sign In'">Sign In</span>
                </button>
            </form>

            {{-- Links + Footer section --}}
            @if ($org)
                <div class="auth-divider"><span>Need another workspace?</span></div>
                <div style="text-align:center">
                    {{--
                        Always build an absolute URL to the central domain's /portal page.
                        Using config('app.url') instead of route('portal.discover') ensures
                        this link works correctly even when rendered from a tenant subdomain,
                        where the router's APP_URL base and the current Host header differ.
                    --}}
                    <a href="{{ rtrim(config('app.url'), '/') }}/portal"
                       class="auth-link"
                       style="font-size:0.95rem;display:inline-flex;align-items:center;gap:0.5rem">
                        <i class="fas fa-arrow-left" aria-hidden="true" style="font-size:0.8rem"></i>
                        Find a different organisation
                    </a>
                </div>
            @elseif (Route::has('register'))
                <div class="auth-divider"><span>Don't have an account?</span></div>
                <div style="text-align:center">
                    <a href="{{ route('register') }}" class="auth-link" style="font-size:0.95rem;display:inline-flex;align-items:center;gap:0.5rem">
                        Request access <i class="fas fa-arrow-right" aria-hidden="true" style="font-size:0.8rem"></i>
                    </a>
                </div>
            @endif

            <div style="text-align:center;margin-top:3.5rem;color:#94a3b8;font-size:0.8rem;line-height:1.5">
                &copy; {{ date('Y') }} NECTARMETRICS SOLUTIONS LIMITED.
            </div>

        </div>
    </div>
</div>

<style>
/* Submit button spinner */
@keyframes auth-spin {
    to { transform: rotate(360deg); }
}
.auth-submit:disabled { opacity: 0.75; cursor: not-allowed; }
</style>

@endsection
