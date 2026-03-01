@extends('layouts.app')
@section('content')
    @php
        $initials = $user->avatar_initials;
        $allPermissions = $user->getAllPermissions()->sortBy('name');
        $directPerms = $user->getDirectPermissions()->sortBy('name');
        $rolePerms = $user->getPermissionsViaRoles()->sortBy('name');
    @endphp

    <div class="container py-4">

        {{-- ── Breadcrumb ──────────────────────────────────────────────────────── --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
            </ol>
        </nav>

        <div class="row g-4">

            {{-- ── Left: Profile Card ───────────────────────────────────────────── --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100 dark:bg-slate-800">
                    <div class="card-body text-center pt-4">
                        <div class="avatar mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center
                                            text-white fw-bold fs-4"
                            style="width:72px;height:72px;background:linear-gradient(135deg,#4f46e5,#7c3aed)"
                            aria-hidden="true">
                            {{ $initials }}
                        </div>
                        <h5 class="fw-semibold mb-0 dark:text-slate-200">{{ $user->name }}</h5>
                        <p class="text-muted small mb-2 dark:text-slate-400">{{ '@' . $user->user_name }}</p>
                        <p class="text-muted small mb-3 dark:text-slate-400">{{ $user->email }}</p>

                        {{-- Status badges --}}
                        <div class="d-flex flex-wrap gap-1 justify-content-center mb-3">
                            @if($user->is_active)
                                <x-ts-badge text="Active" color="green" />
                            @else
                                <x-ts-badge text="Inactive" color="red" />
                            @endif
                            @if($user->is_locked)
                                <x-ts-badge text="Locked" color="yellow" />
                            @endif
                            @if($user->is_admin)
                                <x-ts-badge text="Admin" color="indigo" />
                            @endif
                            @if($user->is_2fa_enabled)
                                <x-ts-badge text="2FA On" color="teal" />
                            @endif
                        </div>

                        {{-- Roles --}}
                        <div class="d-flex flex-wrap gap-1 justify-content-center mb-3">
                            @forelse($user->getRoleNames() as $role)
                                <x-ts-badge :text="ucfirst($role)" color="violet" />
                            @empty
                                <x-ts-badge text="No Role Assigned" color="gray" />
                            @endforelse
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex gap-2 justify-content-center">
                            @can('update', $user)
                                <a href="{{ route('users.edit', $user) }}"
                                    class="btn btn-sm btn-outline-primary dark:text-indigo-400 dark:border-indigo-500">
                                    <i class="fas fa-pen me-1" aria-hidden="true"></i>Edit
                                </a>
                            @endcan
                            @can('delete', $user)
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger dark:text-red-400 dark:border-red-500"
                                    data-user-name="{{ $user->name }}" data-delete-url="{{ route('users.destroy', $user) }}"
                                    onclick="confirmDeleteUser(this)">
                                    <i class="fas fa-trash-alt me-1" aria-hidden="true"></i>Remove
                                </button>
                            @endcan
                        </div>
                    </div>

                    {{-- Account info --}}
                    <div class="card-footer bg-transparent small text-muted dark:text-slate-400 dark:border-slate-700">
                        <dl class="row mb-0 g-0" style="font-size:0.78rem">
                            @if($user->phone)
                                <dt class="col-5">Phone</dt>
                                <dd class="col-7 dark:text-slate-300">{{ $user->phone }}</dd>
                            @endif
                            @if($user->supervisor)
                                <dt class="col-5">Supervisor</dt>
                                <dd class="col-7">
                                    @can('view', $user->supervisor)
                                        <a href="{{ route('users.show', $user->supervisor) }}"
                                            class="text-decoration-none dark:text-indigo-400">{{ $user->supervisor->name }}</a>
                                    @else
                                        <span class="dark:text-slate-300">{{ $user->supervisor->name }}</span>
                                    @endcan
                                </dd>
                            @endif
                            <dt class="col-5">Last login</dt>
                            <dd class="col-7 dark:text-slate-300">
                                @if($user->last_login_at)
                                    <span title="{{ $user->last_login_at->format('d M Y H:i') }}">
                                        {{ $user->last_login_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="fst-italic text-slate-500">Never</span>
                                @endif
                            </dd>
                            @if($user->is_locked && $user->locked_at)
                                <dt class="col-5 text-danger dark:text-red-400">Locked</dt>
                                <dd class="col-7 text-danger dark:text-red-400">{{ $user->locked_at->format('d M Y H:i') }}</dd>
                            @endif
                            <dt class="col-5">Failed logins</dt>
                            <dd class="col-7">
                                <span
                                    class="{{ ($user->failed_login_attempts ?? 0) >= 3 ? 'text-warning fw-semibold dark:text-amber-500' : 'dark:text-slate-300' }}">
                                    {{ $user->failed_login_attempts ?? 0 }}
                                </span>
                            </dd>
                            <dt class="col-5">Member since</dt>
                            <dd class="col-7 dark:text-slate-300">{{ $user->created_at?->format('d M Y') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- ── Right: Permissions Detail ────────────────────────────────────── --}}
            <div class="col-lg-8">

                {{-- Roles with edit link --}}
                <div class="card border-0 shadow-sm mb-4 dark:bg-slate-800">
                    <div
                        class="card-header bg-transparent d-flex justify-content-between align-items-center py-3 dark:border-slate-700">
                        <h6 class="fw-semibold mb-0 dark:text-slate-200">
                            <i class="fas fa-user-shield me-2 text-violet-500" aria-hidden="true"></i>Assigned Roles
                        </h6>
                        @can('update', $user)
                            <a href="{{ route('users.edit', $user) }}"
                                class="btn btn-sm btn-outline-secondary dark:text-slate-300 dark:border-slate-600">
                                <i class="fas fa-pen me-1" aria-hidden="true"></i>Edit
                            </a>
                        @endcan
                    </div>
                    <div class="card-body">
                        @forelse($user->roles->sortBy('name') as $role)
                            <div
                                class="d-flex align-items-center gap-3 mb-2 pb-2 border-bottom last:border-0 dark:border-slate-700">
                                <x-ts-badge :text="ucfirst($role->name)" color="violet" />
                                <span class="text-muted small dark:text-slate-400">
                                    {{ $role->permissions_count ?? $role->permissions->count() }}
                                    permission{{ ($role->permissions->count() ?? 1) !== 1 ? 's' : '' }}
                                </span>
                                @can('manage roles')
                                    <a href="{{ route('roles.show', $role) }}"
                                        class="ms-auto btn btn-link btn-sm text-muted p-0 dark:text-slate-400" title="View role">
                                        <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                    </a>
                                @endcan
                            </div>
                        @empty
                            <p class="text-muted small mb-0 dark:text-slate-400">No roles assigned.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Effective Permissions --}}
                <div class="card border-0 shadow-sm dark:bg-slate-800">
                    <div class="card-header bg-transparent py-3 dark:border-slate-700">
                        <h6 class="fw-semibold mb-0 dark:text-slate-200">
                            <i class="fas fa-key me-2 text-amber-500" aria-hidden="true"></i>
                            Effective Permissions
                            <span class="badge bg-secondary ms-2 dark:bg-slate-700"
                                style="font-size:0.7rem">{{ $allPermissions->count() }}</span>
                        </h6>
                        <p class="text-muted small mb-0 mt-1 dark:text-slate-400">All permissions this user has (via roles +
                            direct assignments)
                        </p>
                    </div>
                    <div class="card-body">
                        @php
                            // Group by everything after the verb (e.g. "view documents" → "documents",
                            // "view document versions" → "document versions", "manage roles" → "roles")
                            $grouped = $allPermissions->groupBy(function ($p) {
                                $parts = explode(' ', $p->name, 2);
                                return $parts[1] ?? $p->name;
                            })->sortKeys();
                        @endphp

                        @forelse($grouped as $resource => $perms)
                            <div class="mb-3">
                                <div class="text-uppercase text-muted fw-semibold mb-1 dark:text-slate-400"
                                    style="font-size:0.65rem;letter-spacing:0.08em">
                                    {{ $resource }}
                                </div>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($perms as $perm)
                                        @php
                                            $isDirect = $directPerms->contains('name', $perm->name);
                                        @endphp
                                        <span
                                            class="badge rounded-pill {{ $isDirect ? 'bg-primary dark:bg-indigo-600' : 'bg-light text-dark border dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600' }}"
                                            title="{{ $isDirect ? 'Directly assigned' : 'Via role' }}" style="font-size:0.72rem">
                                            {{ $perm->name }}
                                            @if($isDirect)
                                                <i class="fas fa-star ms-1" style="font-size:0.6rem" aria-label="direct"></i>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0 dark:text-slate-400">No permissions assigned.</p>
                        @endforelse

                        @if($directPerms->count() > 0)
                            <p class="text-muted mt-2 mb-0 dark:text-slate-400" style="font-size:0.72rem">
                                <i class="fas fa-star me-1 text-primary dark:text-indigo-400" aria-hidden="true"></i>
                                Blue badges are directly assigned (override role defaults).
                            </p>
                        @endif
                    </div>
                </div>

            </div>{{-- /col-lg-8 --}}
        </div>{{-- /row --}}
    </div>{{-- /container --}}


    {{-- ── Delete Confirmation Modal ───────────────────────────────────────────── --}}
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-modal="true"
        role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-danger fw-semibold" id="deleteUserModalLabel">
                        <i class="fas fa-triangle-exclamation me-2" aria-hidden="true"></i>Remove User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cancel deletion"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="mb-0 small">
                        Remove <strong id="deleteUserName"></strong>?
                        This cannot be undone.
                    </p>
                </div>
                <div class="modal-footer border-0 pt-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteUserForm" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDeleteUser(btn) {
            document.getElementById('deleteUserName').textContent = btn.getAttribute('data-user-name');
            document.getElementById('deleteUserForm').action = btn.getAttribute('data-delete-url');
            new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
        }

        document.addEventListener('DOMContentLoaded', function () {
            var content = document.querySelector('.page-content');
            if (content) content.style.display = 'block';
            var overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'none';
        });
    </script>
@endsection