@extends('layouts.app')
@section('content')
<div class="container py-4" style="max-width:760px">

    <div class="mb-4">
        <h1 class="h4 fw-bold text-primary mb-0">
            <i class="fas fa-user-plus me-2" aria-hidden="true"></i>Add User
        </h1>
        <p class="text-muted small mb-0">Create a new user account and assign roles</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert" aria-live="assertive">
            <i class="fas fa-exclamation-circle me-2" aria-hidden="true"></i>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.store') }}" method="POST" class="card border-0 shadow-sm p-4" novalidate>
        @csrf

        {{-- ── Identity ─────────────────────────────────────────────────────── --}}
        <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:0.72rem;letter-spacing:0.06em">
            Identity
        </h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" required autofocus aria-required="true">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="user_name" class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                <input type="text" id="user_name" name="user_name"
                       class="form-control font-monospace @error('user_name') is-invalid @enderror"
                       value="{{ old('user_name') }}" required aria-required="true"
                       placeholder="unique_handle" autocomplete="username">
                @error('user_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                <input type="email" id="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required aria-required="true" autocomplete="email">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label fw-semibold">Phone</label>
                <input type="tel" id="phone" name="phone"
                       class="form-control @error('phone') is-invalid @enderror"
                       value="{{ old('phone') }}" placeholder="+234 800 000 0000">
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- ── Supervisor ───────────────────────────────────────────────────── --}}
        @if(isset($supervisors) && $supervisors->isNotEmpty())
        <div class="mb-4">
            <label for="supervisor_id" class="form-label fw-semibold">Supervisor</label>
            <select id="supervisor_id" name="supervisor_id"
                    class="form-select @error('supervisor_id') is-invalid @enderror">
                <option value="">— None —</option>
                @foreach($supervisors as $sup)
                    <option value="{{ $sup->id }}" {{ old('supervisor_id') == $sup->id ? 'selected' : '' }}>
                        {{ $sup->name }} ({{ $sup->user_name ?? $sup->email }})
                    </option>
                @endforeach
            </select>
            @error('supervisor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        @endif

        <hr class="my-4">

        {{-- ── Credentials ──────────────────────────────────────────────────── --}}
        <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:0.72rem;letter-spacing:0.06em">
            Credentials
        </h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       required aria-required="true" autocomplete="new-password">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="password_confirmation" class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="form-control" required aria-required="true" autocomplete="new-password">
            </div>
        </div>

        <hr class="my-4">

        {{-- ── Roles & Permissions ──────────────────────────────────────────── --}}
        <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:0.72rem;letter-spacing:0.06em">
            Roles &amp; Permissions
        </h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="roles" class="form-label fw-semibold">Roles</label>
                <select id="roles" name="roles[]" class="form-select" multiple size="5">
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}"
                                {{ in_array($role->name, old('roles', [])) ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Hold Ctrl / Cmd to select multiple</div>
            </div>
            <div class="col-md-6">
                <label for="permissions" class="form-label fw-semibold">Direct Permissions</label>
                <select id="permissions" name="permissions[]" class="form-select" multiple size="5">
                    @foreach($permissions as $permission)
                        <option value="{{ $permission->name }}"
                                {{ in_array($permission->name, old('permissions', [])) ? 'selected' : '' }}>
                            {{ $permission->name }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Hold Ctrl / Cmd to select multiple</div>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── Account Flags ────────────────────────────────────────────────── --}}
        <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:0.72rem;letter-spacing:0.06em">
            Account Settings
        </h6>
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch"
                           id="is_active" name="is_active" value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_active">Active</label>
                </div>
                <div class="form-text">Allow login</div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch"
                           id="is_admin" name="is_admin" value="1"
                           {{ old('is_admin') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_admin">Admin</label>
                </div>
                <div class="form-text">Full admin access</div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch"
                           id="is_locked" name="is_locked" value="1"
                           {{ old('is_locked') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_locked">Locked</label>
                </div>
                <div class="form-text">Block all access</div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch"
                           id="is_2fa_enabled" name="is_2fa_enabled" value="1"
                           {{ old('is_2fa_enabled') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_2fa_enabled">2FA</label>
                </div>
                <div class="form-text">Two-factor auth</div>
            </div>
        </div>

        {{-- ── Actions ─────────────────────────────────────────────────────── --}}
        <div class="d-flex gap-2 pt-2">
            <button type="submit" class="btn btn-success px-4">
                <i class="fas fa-check me-1" aria-hidden="true"></i> Create User
            </button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var content = document.querySelector('.page-content');
    if (content) content.style.display = 'block';
    var overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.style.display = 'none';
});
</script>
@endsection
