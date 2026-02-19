@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:680px">

    {{-- ── Breadcrumb ─────────────────────────────────────────────────────── --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('tenants.index') }}">Tenants</a></li>
            <li class="breadcrumb-item">
                <a href="{{ route('tenants.show', $tenant) }}">{{ $tenant->organization_name }}</a>
            </li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>
                Edit Tenant
            </h5>
            <div class="text-muted small font-monospace mt-1">{{ $tenant->id }}</div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('tenants.update', $tenant) }}" novalidate>
                @csrf @method('PUT')

                {{-- Organisation Name --}}
                <div class="mb-3">
                    <label for="organization_name" class="form-label fw-semibold">
                        Organisation Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="organization_name" name="organization_name"
                           class="form-control @error('organization_name') is-invalid @enderror"
                           value="{{ old('organization_name', $tenant->organization_name) }}"
                           required>
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
                           value="{{ old('admin_email', $tenant->admin_email) }}"
                           required>
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
                        @foreach (['starter' => 'Starter', 'business' => 'Business', 'enterprise' => 'Enterprise'] as $val => $label)
                            <option value="{{ $val }}"
                                    {{ old('plan', $tenant->plan) === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('plan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Active Toggle --}}
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="is_active" name="is_active" value="1"
                               {{ old('is_active', $tenant->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_active">
                            Tenant is Active
                        </label>
                    </div>
                    <div class="form-text">Suspending a tenant blocks all access to their EDMS instance.</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                    </button>
                    <a href="{{ route('tenants.show', $tenant) }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
