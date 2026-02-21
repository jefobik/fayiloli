@extends('layouts.central')

@section('title', 'Edit Tenant — ' . $tenant->organization_name)

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
                <i class="fa-solid fa-pen-to-square me-2 text-primary" aria-hidden="true"></i>
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

                {{-- Tenant Type --}}
                <div class="mb-3">
                    <label for="tenant_type" class="form-label fw-semibold">
                        Tenant Type <span class="text-danger">*</span>
                    </label>
                    <select id="tenant_type" name="tenant_type"
                            class="form-select @error('tenant_type') is-invalid @enderror" required>
                        @foreach ([
                            'government'  => 'Government',
                            'secretariat' => 'Secretariat',
                            'agency'      => 'Agency',
                            'department'  => 'Department',
                            'unit'        => 'Unit',
                        ] as $val => $label)
                            <option value="{{ $val }}"
                                    {{ old('tenant_type', $tenant->tenant_type ?? $tenant->plan) === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('tenant_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Operational Status --}}
                <div class="mb-3">
                    <label for="status" class="form-label fw-semibold">
                        Operational Status <span class="text-danger">*</span>
                    </label>
                    <select id="status" name="status"
                            class="form-select @error('status') is-invalid @enderror" required>
                        @foreach ([
                            'pending'   => 'Pending',
                            'active'    => 'Active',
                            'suspended' => 'Suspended',
                            'archived'  => 'Archived',
                        ] as $val => $label)
                            <option value="{{ $val }}"
                                    {{ old('status', $tenant->status) === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">The operational lifecycle state of this tenant.</div>
                </div>

                {{-- Notes --}}
                <div class="mb-3">
                    <label for="notes" class="form-label fw-semibold">Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                              class="form-control @error('notes') is-invalid @enderror"
                              placeholder="Internal notes about this tenant…">{{ old('notes', $tenant->notes) }}</textarea>
                    @error('notes')
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
                            Allow Access
                        </label>
                    </div>
                    <div class="form-text">Disabling this immediately blocks all logins for this tenant's EDMS instance.</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-solid fa-floppy-disk me-1" aria-hidden="true"></i> Save Changes
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
