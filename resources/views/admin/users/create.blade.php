@extends('layouts.app')
@section('content')
    <div class="container py-4">

        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary me-3"
                style="border-radius:8px">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
            </a>
            <div>
                <h1 class="h4 fw-bold mb-0 text-slate-800 dark:text-slate-100">Add Central User</h1>
                <p class="text-muted small mb-0 mt-1">Create a new platform administrator</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm dark:bg-slate-800" style="border-radius:12px;max-width:800px">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="row g-4">
                        {{-- ── Personal Info ────────────────────────────────────── --}}
                        <div class="col-12 text-uppercase text-muted fw-bold mb-2"
                            style="font-size:0.75rem;letter-spacing:1px">
                            Personal Information
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-semibold text-slate-700 dark:text-slate-300">Full
                                Name</label>
                            <input type="text"
                                class="form-control @error('name') is-invalid @enderror dark:bg-slate-900 dark:border-slate-700 dark:text-white"
                                id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="user_name"
                                class="form-label fw-semibold text-slate-700 dark:text-slate-300">Username</label>
                            <input type="text"
                                class="form-control @error('user_name') is-invalid @enderror dark:bg-slate-900 dark:border-slate-700 dark:text-white"
                                id="user_name" name="user_name" value="{{ old('user_name') }}" required>
                            @error('user_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label fw-semibold text-slate-700 dark:text-slate-300">Email
                                Address</label>
                            <input type="email"
                                class="form-control @error('email') is-invalid @enderror dark:bg-slate-900 dark:border-slate-700 dark:text-white"
                                id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone"
                                class="form-label fw-semibold text-slate-700 dark:text-slate-300">Phone</label>
                            <input type="text"
                                class="form-control @error('phone') is-invalid @enderror dark:bg-slate-900 dark:border-slate-700 dark:text-white"
                                id="phone" name="phone" value="{{ old('phone') }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- ── Security ─────────────────────────────────────────── --}}
                        <div class="col-12 text-uppercase text-muted fw-bold mb-2 mt-4"
                            style="font-size:0.75rem;letter-spacing:1px;border-top:1px solid #f1f5f9;padding-top:1.5rem">
                            Security &amp; Password
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password"
                                class="form-label fw-semibold text-slate-700 dark:text-slate-300">Password</label>
                            <input type="password"
                                class="form-control @error('password') is-invalid @enderror dark:bg-slate-900 dark:border-slate-700 dark:text-white"
                                id="password" name="password" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation"
                                class="form-label fw-semibold text-slate-700 dark:text-slate-300">Confirm Password</label>
                            <input type="password"
                                class="form-control dark:bg-slate-900 dark:border-slate-700 dark:text-white"
                                id="password_confirmation" name="password_confirmation" required>
                        </div>

                        {{-- ── Central Access ───────────────────────────────────── --}}
                        <div class="col-12 text-uppercase text-muted fw-bold mb-2 mt-4"
                            style="font-size:0.75rem;letter-spacing:1px;border-top:1px solid #f1f5f9;padding-top:1.5rem">
                            Central Access Level
                        </div>

                        <div class="col-12 mb-4">
                            <div class="d-flex flex-wrap gap-4 p-3 rounded"
                                style="background:#f8fafc; @apply dark:bg-slate-900">

                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_active"
                                        name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-slate-800 dark:text-slate-200"
                                        for="is_active">Active Account</label>
                                </div>

                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_admin"
                                        name="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-slate-800 dark:text-slate-200"
                                        for="is_admin">Admin Portal Access</label>
                                </div>

                                @if(Auth::user()->isSuperAdmin())
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="is_super_admin"
                                            name="is_super_admin" value="1" {{ old('is_super_admin') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-slate-800 dark:text-danger"
                                            for="is_super_admin">Super Admin Status</label>
                                    </div>
                                @endif

                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_2fa_enabled"
                                        name="is_2fa_enabled" value="1" {{ old('is_2fa_enabled') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-slate-800 dark:text-slate-200"
                                        for="is_2fa_enabled">Require 2FA</label>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-4"
                        style="border-top:1px solid #f1f5f9; @apply dark:border-slate-700">
                        <a href="{{ route('admin.users.index') }}"
                            class="btn btn-light dark:bg-slate-700 dark:text-white dark:border-slate-600">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="fas fa-check me-1" aria-hidden="true"></i>Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection