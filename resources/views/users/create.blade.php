@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="mb-4">
        <h2 class="fw-bold text-primary">Add User</h2>
    </div>
    <form action="{{ route('users.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Roles</label>
            <select name="roles[]" class="form-select" multiple>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Permissions</label>
            <select name="permissions[]" class="form-select" multiple>
                @foreach($permissions as $permission)
                    <option value="{{ $permission->name }}">{{ $permission->name }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-success px-4">Create</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
@endsection
