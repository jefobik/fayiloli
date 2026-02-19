@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">User Management</h2>
        <a href="{{ route('users.create') }}" class="btn btn-success shadow-sm"><i class="fas fa-user-plus me-1"></i> Add User</a>
    </div>
    <div class="table-responsive rounded shadow-sm">
        <table class="table table-hover align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th>Permissions</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="fw-semibold">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge bg-primary text-light">{{ $user->getRoleNames()->join(', ') }}</span></td>
                    <td><span class="badge bg-info text-dark">{{ $user->getPermissionNames()->join(', ') }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete user?')"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
