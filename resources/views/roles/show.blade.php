@extends('layouts.app')
@section('content')
    @php
        $systemColors = ['admin' => '#7c3aed', 'manager' => '#0891b2', 'user' => '#059669', 'viewer' => '#6b7280'];
        $color = $systemColors[$role->name] ?? '#4f46e5';
    @endphp

    <div class="container py-4">

        {{-- ── Breadcrumb ──────────────────────────────────────────────────────── --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ ucfirst($role->name) }}</li>
            </ol>
        </nav>

        <div class="row g-4">

            {{-- ── Left: Role Card ──────────────────────────────────────────────── --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center pt-4">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold fs-3 mx-auto mb-3"
                             style="width:64px;height:64px;background:{{ $color }}"
                             aria-hidden="true">
                            {{ strtoupper(substr($role->name, 0, 1)) }}
                        </div>
                        <h5 class="fw-semibold mb-1">{{ ucfirst($role->name) }}</h5>
                        <p class="text-muted small mb-3">guard: {{ $role->guard_name }}</p>

                        @if($isSystem)
                            <span class="badge bg-light text-secondary border mb-3">
                                <i class="fas fa-lock me-1" aria-hidden="true"></i>System Role
                            </span>
                            <p class="text-muted small mb-3">System roles cannot be deleted.</p>
                        @else
                            <span class="badge bg-light text-success border border-success mb-3">Custom Role</span>
                        @endif

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2 rounded bg-light text-center">
                                    <div class="fw-bold text-primary fs-5">{{ $role->permissions->count() }}</div>
                                    <div class="text-muted" style="font-size:0.72rem">permissions</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded bg-light text-center">
                                    <div class="fw-bold text-secondary fs-5">{{ $users->count() }}</div>
                                    <div class="text-muted" style="font-size:0.72rem">users</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-center">
                            @can('update', $role)
                                <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-pen me-1" aria-hidden="true"></i>Edit Permissions
                                </a>
                            @endcan
                            @can('delete', $role)
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        data-role-name="{{ $role->name }}"
                                        data-delete-url="{{ route('roles.destroy', $role) }}"
                                        onclick="confirmDeleteRole(this)">
                                    <i class="fas fa-trash-alt me-1" aria-hidden="true"></i>Delete
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Right: Permissions + Users ───────────────────────────────────── --}}
            <div class="col-lg-8">

                {{-- Permissions by group --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent py-3">
                        <h6 class="fw-semibold mb-0">
                            <i class="fas fa-key me-2 text-amber-500" aria-hidden="true"></i>
                            Permissions
                            <span class="badge bg-secondary ms-1" style="font-size:0.7rem">{{ $role->permissions->count() }}</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        @forelse($permissions as $group => $groupPerms)
                            <div class="mb-3">
                                <div class="text-uppercase text-muted fw-semibold mb-2"
                                     style="font-size:0.65rem;letter-spacing:0.08em">
                                    {{ $group }}
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($groupPerms as $perm)
                                        @php $granted = in_array($perm->name, $rolePermissions, true); @endphp
                                        <span class="badge rounded-pill {{ $granted ? 'bg-primary' : 'bg-light text-muted' }}"
                                              style="font-size:0.72rem">
                                            @if($granted)
                                                <i class="fas fa-check me-1" style="font-size:0.6rem" aria-hidden="true"></i>
                                            @endif
                                            {{ $perm->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">No permissions defined.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Assigned Users --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3">
                        <h6 class="fw-semibold mb-0">
                            <i class="fas fa-users me-2" aria-hidden="true"></i>
                            Users with this role
                            <span class="badge bg-secondary ms-1" style="font-size:0.7rem">{{ $users->count() }}</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        @forelse($users as $user)
                            <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom last:border-0">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                     style="width:30px;height:30px;font-size:0.65rem;background:linear-gradient(135deg,#4f46e5,#7c3aed)"
                                     aria-hidden="true">
                                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold" style="font-size:0.85rem">{{ $user->name }}</div>
                                    <div class="text-muted" style="font-size:0.72rem">{{ $user->email }}</div>
                                </div>
                                @can('view users')
                                    <a href="{{ route('users.show', $user) }}"
                                       class="ms-auto btn btn-sm btn-link text-muted p-0"
                                       title="View user">
                                        <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                    </a>
                                @endcan
                            </div>
                        @empty
                            <p class="text-muted small mb-0">No users assigned to this role.</p>
                        @endforelse
                    </div>
                </div>

            </div>{{-- /col-lg-8 --}}
        </div>{{-- /row --}}
    </div>{{-- /container --}}

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-labelledby="deleteRoleModalLabel"
         aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-danger fw-semibold" id="deleteRoleModalLabel">
                        <i class="fas fa-triangle-exclamation me-2" aria-hidden="true"></i>Delete Role
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cancel"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="mb-0 small">
                        Delete role <strong id="deleteRoleName"></strong>?
                        Affected users will be moved to the default <em>user</em> role.
                    </p>
                </div>
                <div class="modal-footer border-0 pt-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteRoleForm" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDeleteRole(btn) {
            document.getElementById('deleteRoleName').textContent = btn.getAttribute('data-role-name');
            document.getElementById('deleteRoleForm').action = btn.getAttribute('data-delete-url');
            new bootstrap.Modal(document.getElementById('deleteRoleModal')).show();
        }

        document.addEventListener('DOMContentLoaded', function () {
            var content = document.querySelector('.page-content');
            if (content) content.style.display = 'block';
            var overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'none';
        });
    </script>
@endsection
