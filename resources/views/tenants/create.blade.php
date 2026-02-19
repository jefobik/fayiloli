@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:680px">

    {{-- ── Breadcrumb ─────────────────────────────────────────────────────── --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('tenants.index') }}">Tenants</a>
            </li>
            <li class="breadcrumb-item active">New Tenant</li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="fa-solid fa-building-user me-2 text-primary"></i>
                Provision New Tenant
            </h5>
            <p class="text-muted small mb-0 mt-1">
                A dedicated PostgreSQL database will be created and migrated automatically
                when you submit this form.
            </p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('tenants.store') }}" novalidate>
                @csrf

                {{-- Organisation Name --}}
                <div class="mb-3">
                    <label for="organization_name" class="form-label fw-semibold">
                        Organisation Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="organization_name" name="organization_name"
                           class="form-control @error('organization_name') is-invalid @enderror"
                           value="{{ old('organization_name') }}"
                           placeholder="Acme Corporation" required>
                    @error('organization_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Admin Email --}}
                <div class="mb-3">
                    <label for="admin_email" class="form-label fw-semibold">
                        Admin Email <span class="text-danger">*</span>
                    </label>
                    <input type="email" id="admin_email" name="admin_email"
                           class="form-control @error('admin_email') is-invalid @enderror"
                           value="{{ old('admin_email') }}"
                           placeholder="admin@acme.com" required>
                    @error('admin_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Plan --}}
                <div class="mb-3">
                    <label for="plan" class="form-label fw-semibold">
                        Subscription Plan <span class="text-danger">*</span>
                    </label>
                    <select id="plan" name="plan"
                            class="form-select @error('plan') is-invalid @enderror" required>
                        <option value="">— Select a plan —</option>
                        @foreach (['starter' => 'Starter', 'business' => 'Business', 'enterprise' => 'Enterprise'] as $val => $label)
                            <option value="{{ $val }}" {{ old('plan') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('plan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Primary Domain --}}
                <div class="mb-4">
                    <label for="domain" class="form-label fw-semibold">
                        Primary Domain <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-globe"></i></span>
                        <input type="text" id="domain" name="domain"
                               class="form-control @error('domain') is-invalid @enderror"
                               value="{{ old('domain') }}"
                               placeholder="acme.youredms.com" required>
                        @error('domain')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text">
                        Use a fully-qualified hostname (e.g. <code>acme.fayiloli.com</code>).
                        The DNS record must point to this server.
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-solid fa-rocket me-1"></i> Provision Tenant
                    </button>
                    <a href="{{ route('tenants.index') }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
