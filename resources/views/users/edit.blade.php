@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="mb-4">
        <h2 class="fw-bold text-primary">Edit User</h2>
    </div>
    <form action="{{ route('users.update', $user) }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Roles</label>
            <select name="roles[]" class="form-select" multiple>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" @if($user->hasRole($role->name)) selected @endif>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Permissions</label>
            <select name="permissions[]" class="form-select" multiple>
                @foreach($permissions as $permission)
                    <option value="{{ $permission->name }}" @if($user->hasPermissionTo($permission->name)) selected @endif>{{ $permission->name }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary px-4">Update</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
@endsection
