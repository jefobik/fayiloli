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
                <div class="auth-visual-icon" style="background:linear-gradient(135deg,{{ $gc[0] }},{{ $gc[1] }});border-radius:16px;width:72px;height:72px;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem">
                    <span style="font-size:1.6rem;font-weight:800;color:#fff;letter-spacing:0.02em">
                        {{ strtoupper(substr($org->organization_name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $org->organization_name)[1] ?? '', 0, 1)) }}
                    </span>
                </div>
                <div class="auth-visual-title">{{ $org->organization_name }}</div>
                <div class="auth-visual-desc">
                    {{ $org->plan_label }} &middot; Secure EDMS Workspace<br>
                    Sign in with your organisation credentials to access documents, workspaces, and collaboration tools.
                </div>
            @else
                {{-- Central / generic left panel --}}
                <div class="auth-visual-icon">
                    <img src="/img/fayiloli-icon.svg"
                         alt=""
                         aria-hidden="true"
                         width="72" height="72"
                         style="border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.35)">
                </div>
                <div class="auth-visual-title">NectarMetrics - FAYILOLI Electronic Document Management System</div>
                <div class="auth-visual-desc">
                    Secure, intelligent document management for government and modern organisations.
                    Powered by NectarMetrics Solutions Limited — delivering intelligent workflow, real-time notifications, enterprise-grade security, and powerful role-based access you can trust.
                </div>
            @endif

            <div class="auth-features">
                <div class="auth-feature">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <span>Intelligent workflow-based and full-text search</span>
                </div>
                <div class="auth-feature">
                    <i class="fas fa-bell" aria-hidden="true"></i>
                    <span>Real-time activities notifications &amp; audit and activity log</span>
                </div>
                <div class="auth-feature">
                    <i class="fas fa-shield-alt" aria-hidden="true"></i>
                    <span>Role-based access control (Administrator, Manager, Reviewer, Viewer)</span>
                </div>
                <div class="auth-feature">
                    <i class="fas fa-layer-group" aria-hidden="true"></i>
                    <span>Multi-tenant workspace isolation</span>
                </div>
                <div class="auth-feature">
                    <i class="fas fa-chart-bar" aria-hidden="true"></i>
                    <span>Interactive analytics dashboard</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Form Panel (right) ──────────────────────────────────────────── --}}
    <div class="auth-form-panel">
        <div class="auth-form-inner">

            {{-- Logo / Brand --}}
            <div class="auth-form-logo" aria-hidden="true">
                <img src="/img/fayiloli-icon.svg"
                     alt=""
                     aria-hidden="true"
                     width="36" height="36"
                     style="border-radius:8px;flex-shrink:0">
                <span class="brand">Fayiloli v2.9</span>
            </div>

            {{-- Org identity strip (tenant domains only) --}}
            @if ($org)
                <div class="org-identity-strip" role="region" aria-label="Organisation">
                    @php
                        $gc = $gradients[$org->tenant_type?->value ?? ''] ?? ['#475569','#334155'];
                    @endphp
                    <div class="org-identity-avatar" style="background:linear-gradient(135deg,{{ $gc[0] }},{{ $gc[1] }})">
                        {{ strtoupper(substr($org->organization_name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $org->organization_name)[1] ?? '', 0, 1)) }}
                    </div>
                    <div class="org-identity-info">
                        <div class="org-identity-name">{{ $org->organization_name }}</div>
                        <span class="org-identity-type">{{ $org->plan_label }}</span>
                    </div>
                    <a href="/portal" class="org-change-link" title="Switch organisation">
                        <i class="fas fa-exchange-alt" aria-hidden="true"></i>
                    </a>
                </div>

                <h2 class="auth-form-title">Sign in to your workspace</h2>
                <p class="auth-form-sub">Access {{ $org->organization_name }} EDMS</p>
            @else
                <h2 class="auth-form-title">Sign in to your account</h2>
                <p class="auth-form-sub">Enter your credentials to access your workspace</p>
            @endif

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div role="alert" aria-live="assertive"
                     style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:0.75rem 1rem;margin-bottom:1rem">
                    <div style="display:flex;align-items:flex-start;gap:0.5rem;color:#dc2626;font-size:0.85rem;font-weight:600">
                        <i class="fas fa-exclamation-circle" aria-hidden="true" style="margin-top:0.1rem;flex-shrink:0"></i>
                        <ul style="margin:0;padding:0;list-style:none">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{--
                Alpine state:
                  showPw   — password visibility toggle
                  loading  — true while the form POST is in-flight; prevents
                             double-submit and shows a spinner on the button
            --}}
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
                            :disabled="loading"
                            @if ($errors->has('email')) aria-invalid="true" @endif
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div class="auth-field">
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <label for="password">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="auth-link" style="font-size:0.78rem">
                                Forgot password?
                            </a>
                        @endif
                    </div>
                    <div class="auth-input-wrap">
                        <i class="fas fa-lock input-icon" aria-hidden="true"></i>
                        <input
                            :type="showPw ? 'text' : 'password'"
                            id="password" name="password"
                            placeholder="••••••••"
                            required autocomplete="current-password"
                            aria-required="true"
                            :disabled="loading"
                            :aria-label="showPw ? 'Password (visible)' : 'Password'"
                            @if ($errors->has('password')) aria-invalid="true" @endif
                        >
                        <button type="button"
                                class="toggle-pw"
                                @click="showPw = !showPw"
                                :disabled="loading"
                                :aria-pressed="showPw.toString()"
                                :aria-label="showPw ? 'Hide password' : 'Show password'">
                            <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div style="display:flex;align-items:center;gap:0.5rem;margin-top:0.25rem">
                    <input type="checkbox" id="remember" name="remember"
                           style="width:16px;height:16px;accent-color:#7c3aed;cursor:pointer"
                           :disabled="loading">
                    <label for="remember" style="font-size:0.82rem;color:#374151;cursor:pointer;margin:0">
                        Keep me signed in for 30 days
                    </label>
                </div>

                <button type="submit" class="auth-submit" :disabled="loading" :aria-busy="loading.toString()">
                    {{-- Spinner (visible while loading) --}}
                    <span x-show="loading" x-cloak aria-hidden="true"
                          style="display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,0.4);border-top-color:#fff;border-radius:50%;animation:auth-spin 0.7s linear infinite;margin-right:0.5rem;vertical-align:middle"></span>
                    {{-- Icon (visible when not loading) --}}
                    <i class="fas fa-sign-in-alt" aria-hidden="true" style="margin-right:0.5rem" x-show="!loading"></i>
                    <span x-text="loading ? 'Signing in…' : 'Sign In'">Sign In</span>
                </button>
            </form>

            {{-- Back to portal (tenant domains only) --}}
            @if ($org)
                <div class="auth-divider"><span>Wrong organisation?</span></div>
                <div style="text-align:center">
                    <a href="/portal" class="auth-link" style="font-size:0.875rem">
                        <i class="fas fa-arrow-left" aria-hidden="true" style="font-size:0.75rem"></i>
                        Find a different organisation
                    </a>
                </div>
            @elseif (Route::has('register'))
                <div class="auth-divider"><span>Don't have an account?</span></div>
                <div style="text-align:center">
                    <a href="{{ route('register') }}" class="auth-link" style="font-size:0.875rem">
                        Request access <i class="fas fa-arrow-right" aria-hidden="true" style="font-size:0.75rem"></i>
                    </a>
                </div>
            @endif

            {{-- Copyright --}}
            <div style="text-align:center;margin-top:2.5rem;padding-top:1.5rem;border-top:1px solid #f1f5f9;color:#94a3b8;font-size:0.72rem">
                &copy; {{ date('Y') }} NECTARMETRICS SOLUTIONS LIMITED. All rights reserved.<br>
                <span style="margin-top:0.2rem;display:block">
                    Powered by @@nectarmetrics {{ app()->version() }} &middot; EDMS v2.9 &middot; Heracleus-CBT v5.2
                </span>
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

/* Org identity strip on tenant login */
.org-identity-strip {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    margin-bottom: 1.25rem;
}
.org-identity-avatar {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    font-weight: 800;
    color: #fff;
    flex-shrink: 0;
    letter-spacing: 0.02em;
}
.org-identity-info { flex: 1; min-width: 0; }
.org-identity-name {
    font-size: 0.82rem;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.org-identity-type {
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #7c3aed;
}
.org-change-link {
    color: #94a3b8;
    font-size: 0.8rem;
    text-decoration: none;
    padding: 0.3rem;
    border-radius: 5px;
    transition: color 0.15s;
    flex-shrink: 0;
}
.org-change-link:hover { color: #7c3aed; }
</style>

@endsection
