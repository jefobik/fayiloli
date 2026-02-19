@extends('layouts.app')

@section('content')
<div class="auth-shell">

    {{-- ── Visual Panel (left) ─────────────────────────────────────────── --}}
    <div class="auth-visual">
        <div class="auth-visual-content">
            <div class="auth-visual-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="width:64px;height:64px">
                    <path fill="rgba(255,255,255,0.85)"
                          d="M512 256a15 15 0 00-7.1-12.8l-52-32 52-32.5a15 15 0 000-25.4L264 2.3c-4.8-3-11-3-15.9 0L7 153.3a15 15 0 000 25.4L58.9 211 7.1 243.3a15 15 0 000 25.4L58.8 301 7.1 333.3a15 15 0 000 25.4l241 151a15 15 0 0015.9 0l241-151a15 15 0 00-.1-25.5l-52-32 52-32.5A15 15 0 00512 256z"/>
                </svg>
            </div>
            <div class="auth-visual-title">NectarMetrics Enterprise Electronic Document Management System</div>
            <div class="auth-visual-desc">
                Secure, intelligent document management for government and modern organisations.
                Powered by NectarMetrics Solutions Limited — delivering intelligent workflow, real-time notifications, enterprise-grade security, and powerful role-based access you can trust.
            </div>

            <div class="auth-features">
                <div class="auth-feature">
                    <i class="fas fa-search"></i>
                    <span>Intelligent workflow-based and full-text search with MeiliSearch</span>
                </div>
                <div class="auth-feature">
                    <i class="fas fa-bell"></i>
                    <span>Real-time notifications &amp; audit and activity log</span>
                </div>
                <div class="auth-feature">
                    <i class="fas fa-shield-alt"></i>
                    <span>Role-based access control (Administrator, Manager, Reviewer, Approver, Viewer)</span>
                </div>
                <div class="auth-feature">
                    <i class="fas fa-layer-group"></i>
                    <span>Multi-tenant workspace isolation</span>
                </div>
                <div class="auth-feature">
                    <i class="fas fa-chart-bar"></i>
                    <span>Interactive analytics dashboard</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Form Panel (right) ──────────────────────────────────────────── --}}
    <div class="auth-form-panel">
        <div class="auth-form-inner">

            {{-- Logo --}}
            <div class="auth-form-logo">
                <div class="avatar" style="width:36px;height:36px;font-size:0.9rem">
                    <i class="fas fa-layer-group" style="font-size:0.9rem"></i>
                </div>
                <span class="brand">Fayiloli v2.9</span>
            </div>

            <h2 class="auth-form-title">Sign in to your account</h2>
            <p class="auth-form-sub">Enter your credentials to access your workspace</p>

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:0.75rem 1rem;margin-bottom:1rem">
                    <div style="display:flex;align-items:center;gap:0.5rem;color:#dc2626;font-size:0.85rem;font-weight:600">
                        <i class="fas fa-exclamation-circle"></i>
                        @foreach ($errors->all() as $error)
                            <span>{{ $error }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" x-data="{ showPw: false }">
                @csrf

                {{-- Email --}}
                <div class="auth-field">
                    <label for="email">Email address</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-envelope input-icon"></i>
                        <input
                            type="email" id="email" name="email"
                            value="{{ old('email') }}"
                            placeholder="you@company.com"
                            required autocomplete="email" autofocus
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
                        <i class="fas fa-lock input-icon"></i>
                        <input
                            :type="showPw ? 'text' : 'password'"
                            id="password" name="password"
                            placeholder="••••••••"
                            required autocomplete="current-password"
                        >
                        <button type="button" class="toggle-pw" @click="showPw = !showPw" tabindex="-1">
                            <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div style="display:flex;align-items:center;gap:0.5rem;margin-top:0.25rem">
                    <input type="checkbox" id="remember" name="remember"
                           style="width:16px;height:16px;accent-color:#7c3aed;cursor:pointer">
                    <label for="remember" style="font-size:0.82rem;color:#374151;cursor:pointer;margin:0">
                        Keep me signed in for 30 days
                    </label>
                </div>

                <button type="submit" class="auth-submit">
                    <i class="fas fa-sign-in-alt" style="margin-right:0.5rem"></i>
                    Sign In
                </button>
            </form>

            @if (Route::has('register'))
                <div class="auth-divider"><span>Don't have an account?</span></div>
                <div style="text-align:center">
                    <a href="{{ route('register') }}" class="auth-link" style="font-size:0.875rem">
                        Request access <i class="fas fa-arrow-right" style="font-size:0.75rem"></i>
                    </a>
                </div>
            @endif

            {{-- Copyright --}}
            <div style="text-align:center;margin-top:2.5rem;padding-top:1.5rem;border-top:1px solid #f1f5f9;color:#94a3b8;font-size:0.72rem">
                &copy; {{ date('Y') }} NECTARMETRICS SOLUTIONS LIMITED. All rights reserved.<br>
                <span style="margin-top:0.2rem;display:block">
                    Powered by @nectarmetrics {{ app()->version() }} &middot; EDMS v2 &middot; CBT v4
                </span>
            </div>

        </div>
    </div>
</div>

{{-- Make page-content visible for login --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var c = document.querySelector('.page-content');
        if (c) { c.style.display = 'block'; c.style.padding = '0'; }
    });
</script>
@endsection
