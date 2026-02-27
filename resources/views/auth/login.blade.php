@extends('layouts.auth')

@php
    // Org context is only available on tenant subdomains.
    $org = tenancy()->initialized ? tenancy()->tenant : null;
@endphp

@section('content')
<div class="auth-shell">

    {{-- ── Visual Panel (left) ─────────────────────────────────────────── --}}
    <div class="auth-visual" aria-hidden="true">
        {{-- Dot-grid pattern overlay --}}
        <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,0.12) 1px,transparent 1px);background-size:28px 28px;pointer-events:none" aria-hidden="true"></div>
        {{-- Radial glow orbs --}}
        <div style="position:absolute;top:-10%;left:-10%;width:50%;height:50%;background:radial-gradient(circle,rgba(167,139,250,0.25),transparent 60%);pointer-events:none" aria-hidden="true"></div>
        <div style="position:absolute;bottom:-5%;right:-5%;width:40%;height:40%;background:radial-gradient(circle,rgba(99,102,241,0.2),transparent 60%);pointer-events:none" aria-hidden="true"></div>

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
                <div class="auth-visual-icon"
                     style="background:linear-gradient(135deg,{{ $gc[0] }},{{ $gc[1] }});width:96px;height:96px;display:flex;align-items:center;justify-content:center;margin-bottom:2rem;border-radius:24px;box-shadow:0 20px 40px rgba(0,0,0,0.3)">
                    <span style="font-size:2.5rem;font-weight:800;color:#fff;letter-spacing:0.02em">
                        {{ strtoupper(substr($org->organization_name, 0, 1) . substr(explode(' ', $org->organization_name)[1] ?? '', 0, 1)) }}
                    </span>
                </div>
                <div class="auth-visual-title">{{ $org->organization_name }}</div>
                <div class="auth-visual-desc">
                    <strong>{{ $org->plan_label }}</strong> &middot; Secure EDMS Workspace<br>
                    Sign in to access documents, workspaces, and enterprise collaboration tools.
                </div>

                {{-- Floating metrics card --}}
                <div class="auth-metrics-float">
                    <div>
                        <div class="metric-val">125K+</div>
                        <div class="metric-lbl">Documents Managed</div>
                    </div>
                    <div style="width:1px;background:rgba(255,255,255,0.12);flex-shrink:0"></div>
                    <div>
                        <div class="metric-val">48</div>
                        <div class="metric-lbl">Active Workspaces</div>
                    </div>
                </div>
            @else
                {{-- Central / generic left panel --}}
                <div class="auth-visual-icon" style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.15);padding:1.25rem;border-radius:24px;width:96px;height:96px;display:flex;align-items:center;justify-content:center;margin-bottom:2rem;box-shadow:0 20px 40px rgba(0,0,0,0.3)">
                    <img src="{{ global_asset('img/fayiloli-icon.svg') }}"
                         alt=""
                         aria-hidden="true"
                         width="56" height="56">
                </div>
                <div class="auth-visual-title">OSTRICH EDMS</div>
                <div class="auth-visual-desc">
                    Secure, intelligent document management for government and modern organisations.<br><br>
                    Powered by NectarMetrics Solutions — delivering intelligent workflows, real-time insights, and powerful role-based access control.
                </div>

                {{-- Floating metrics --}}
                <div class="auth-metrics-float">
                    <div>
                        <div class="metric-val">125K+</div>
                        <div class="metric-lbl">Documents Managed</div>
                    </div>
                    <div style="width:1px;background:rgba(255,255,255,0.12);flex-shrink:0"></div>
                    <div>
                        <div class="metric-val">99.9%</div>
                        <div class="metric-lbl">Uptime SLA</div>
                    </div>
                </div>
            @endif

            <div class="auth-features">
                <div class="auth-feature">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:rgba(167,139,250,0.3);flex-shrink:0">
                        <i class="fas fa-search" aria-hidden="true" style="font-size:0.6rem;color:#c4b5fd"></i>
                    </span>
                    <span>Intelligent workflow-based &amp; full-text search across all documents.</span>
                </div>
                <div class="auth-feature">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:rgba(167,139,250,0.3);flex-shrink:0">
                        <i class="fas fa-shield-alt" aria-hidden="true" style="font-size:0.6rem;color:#c4b5fd"></i>
                    </span>
                    <span>Enterprise-grade role-based access control and strict workspace isolation.</span>
                </div>
                <div class="auth-feature">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:rgba(167,139,250,0.3);flex-shrink:0">
                        <i class="fas fa-chart-line" aria-hidden="true" style="font-size:0.6rem;color:#c4b5fd"></i>
                    </span>
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
                    <span class="brand">Ostrich {{ app()->version() }}</span>
                </div>

                @if ($org)
                    <h2 class="auth-form-title">Welcome back</h2>
                    <p class="auth-form-sub">Sign in to your <strong>{{ $org->organization_name }}</strong> workspace</p>
                @else
                    <h2 class="auth-form-title">Sign in to your account</h2>
                    <p class="auth-form-sub">Enter your credentials to access your administrative workspace</p>
                @endif
            </div>

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div role="alert" aria-live="assertive"
                     style="background:#fef2f2;border:1px solid #fecaca;border-left:4px solid #ef4444;border-radius:8px;padding:0.85rem 1rem;margin-bottom:1.5rem">
                    <div style="display:flex;align-items:flex-start;gap:0.6rem;color:#dc2626;font-size:0.875rem;font-weight:600">
                        <i class="fas fa-exclamation-circle" aria-hidden="true" style="margin-top:0.15rem;flex-shrink:0;font-size:0.9rem"></i>
                        <ul style="margin:0;padding:0;list-style:none;font-weight:500">
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

                {{-- Loading bar --}}
                <div x-show="loading" x-cloak
                     style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#7c3aed,#a78bfa,#7c3aed);background-size:200%;animation:auth-bar 1.2s linear infinite;border-radius:0 0 2px 2px;z-index:1"></div>

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
                            id="password" name="password"
                            placeholder="••••••••"
                            required autocomplete="current-password"
                            aria-required="true"
                            :readonly="loading"
                            x-ref="passwordInput"
                            :aria-label="showPw ? 'Password (visible)' : 'Password'"
                            @if ($errors->has('password')) aria-invalid="true" style="border-color:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,0.1);" @endif
                        >
                        <button type="button"
                                class="toggle-pw"
                                @click="showPw = !showPw; $refs.passwordInput.type = showPw ? 'text' : 'password'"
                                :disabled="loading"
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

            {{-- Links + Footer --}}
            @if ($org)
                <div class="auth-divider"><span>Need another workspace?</span></div>
                <div style="text-align:center">
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
@keyframes auth-spin {
    to { transform: rotate(360deg); }
}
@keyframes auth-bar {
    0%   { background-position: 200% center; }
    100% { background-position: -200% center; }
}
.auth-submit:disabled { opacity: 0.75; cursor: not-allowed; }
.auth-form-panel { position: relative; overflow: hidden; }
</style>

@endsection
