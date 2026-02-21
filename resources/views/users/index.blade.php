@extends('layouts.app')
@section('content')
<div class="container py-4">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h4 fw-bold text-primary mb-0">
                <i class="fas fa-users me-2" aria-hidden="true"></i>User Management
            </h1>
            <p class="text-muted small mb-0">Manage workspace users, roles and account status</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-success btn-sm">
            <i class="fas fa-user-plus me-1" aria-hidden="true"></i> Add User
        </a>
    </div>

    {{-- ── Table ───────────────────────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" aria-label="User list">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">User</th>
                            <th scope="col">Username</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Roles</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            {{-- Name + email --}}
                            <td class="ps-4" data-label="User">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar"
                                         style="width:32px;height:32px;font-size:0.72rem;background:linear-gradient(135deg,#4f46e5,#7c3aed);flex-shrink:0"
                                         aria-hidden="true">
                                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(explode(' ', $user->name ?? 'U ')[1] ?? '', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark" style="font-size:0.875rem">{{ $user->name }}</div>
                                        <div class="text-muted" style="font-size:0.75rem">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Username --}}
                            <td data-label="Username">
                                @if($user->user_name)
                                    <code class="text-secondary" style="font-size:0.8rem">{{ $user->user_name }}</code>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            {{-- Phone --}}
                            <td data-label="Phone">
                                <span style="font-size:0.85rem">{{ $user->phone ?? '—' }}</span>
                            </td>

                            {{-- Roles --}}
                            <td data-label="Roles">
                                @forelse($user->getRoleNames() as $role)
                                    <x-ts-badge :text="ucfirst($role)" color="violet" class="me-1" />
                                @empty
                                    <x-ts-badge text="No Role" color="gray" />
                                @endforelse
                            </td>

                            {{-- Status flags --}}
                            <td data-label="Status">
                                <div class="d-flex flex-wrap gap-1">
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
                                        <x-ts-badge text="2FA" color="teal" />
                                    @endif
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-4" data-label="Actions">
                                <div class="d-flex justify-content-end gap-1"
                                     role="group"
                                     aria-label="Actions for {{ $user->name }}">
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       aria-label="Edit {{ $user->name }}">
                                        <i class="fas fa-pen" aria-hidden="true"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            aria-label="Delete {{ $user->name }}"
                                            data-user-name="{{ $user->name }}"
                                            data-delete-url="{{ route('users.destroy', $user) }}"
                                            onclick="confirmDeleteUser(this)">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-users-slash fa-2x mb-2 d-block" aria-hidden="true"></i>
                                No users found.
                                <a href="{{ route('users.create') }}">Add the first user.</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(isset($users) && method_exists($users, 'hasPages') && $users->hasPages())
            <div class="card-footer bg-transparent border-0">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ── Delete Confirmation Modal ───────────────────────────────────────────── --}}
<div class="modal fade" id="deleteUserModal" tabindex="-1"
     aria-labelledby="deleteUserModalLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger fw-semibold" id="deleteUserModalLabel">
                    <i class="fas fa-triangle-exclamation me-2" aria-hidden="true"></i>Delete User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cancel deletion"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-0 small">
                    Delete <strong id="deleteUserName"></strong>?
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer border-0 pt-1">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteUserForm" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
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
