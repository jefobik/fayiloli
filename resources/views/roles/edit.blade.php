@extends('layouts.app')
@section('content')
    @php
        $rolePermissions = old('permissions', $rolePermissions);
        $systemRoles = ['admin', 'manager', 'user', 'viewer'];
    @endphp

    <div class="container py-4" style="max-width:860px">

        {{-- ── Breadcrumb ──────────────────────────────────────────────────────── --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                <li class="breadcrumb-item"><a href="{{ route('roles.show', $role) }}">{{ ucfirst($role->name) }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Permissions</li>
            </ol>
        </nav>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                     style="width:40px;height:40px;font-size:0.85rem;background:linear-gradient(135deg,#4f46e5,#7c3aed)"
                     aria-hidden="true">
                    {{ strtoupper(substr($role->name, 0, 1)) }}
                </div>
                <div>
                    <h5 class="fw-semibold mb-0">{{ ucfirst($role->name) }}</h5>
                    <p class="text-muted small mb-0">
                        @if($isSystem)
                            <i class="fas fa-lock me-1" aria-hidden="true"></i>System role — permissions editable, name is fixed.
                        @else
                            Custom role — name and permissions are both editable.
                        @endif
                    </p>
                </div>
            </div>

            <form action="{{ route('roles.update', $role) }}" method="POST" novalidate>
                @csrf @method('PUT')

                <div class="card-body">

                    {{-- Role name (editable only for custom roles) --}}
                    <div class="mb-4">
                        <label for="role_name" class="form-label fw-semibold">Role Name</label>
                        @if($isSystem)
                            <input type="text"
                                   class="form-control bg-light"
                                   id="role_name"
                                   value="{{ $role->name }}"
                                   readonly
                                   aria-describedby="roleNameHelp">
                            <div id="roleNameHelp" class="form-text text-muted small">
                                System role names cannot be changed.
                            </div>
                        @else
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="role_name"
                                   name="name"
                                   value="{{ old('name', $role->name) }}"
                                   autocomplete="off"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    {{-- Permissions --}}
                    <div class="mb-2">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label fw-semibold mb-0">Permissions</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-link text-success p-0"
                                        onclick="document.querySelectorAll('.perm-check').forEach(c => { c.checked = true }); document.querySelectorAll('.group-toggle').forEach(t => { t.checked = true; t.indeterminate = false; })">
                                    Grant All
                                </button>
                                <span class="text-muted">|</span>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0"
                                        onclick="document.querySelectorAll('.perm-check').forEach(c => { c.checked = false }); document.querySelectorAll('.group-toggle').forEach(t => { t.checked = false; t.indeterminate = false; })">
                                    Revoke All
                                </button>
                            </div>
                        </div>
                        @include('roles.partials.permission_checkboxes', ['rolePermissions' => $rolePermissions])
                    </div>

                </div>

                <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                    <a href="{{ route('roles.show', $role) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1" aria-hidden="true"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save me-1" aria-hidden="true"></i>Save Permissions
                    </button>
                </div>
            </form>
        </div>
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
