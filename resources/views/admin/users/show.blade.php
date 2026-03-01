@extends('layouts.app')
@section('content')
<div class="container py-4">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary me-3" style="border-radius:8px">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
            </a>
            <div>
                <h1 class="h4 fw-bold mb-0 text-slate-800 dark:text-slate-100">Central User Profile</h1>
                <p class="text-muted small mb-0 mt-1">View platform administrator details</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm shadow-sm px-3">
                <i class="fas fa-pen me-1" aria-hidden="true"></i>Edit User
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- ── Left Column: Profile Card ────────────────────────────────────── --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 dark:bg-slate-800" style="border-radius:12px">
                <div class="card-body p-4 text-center">
                    
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold mx-auto mb-3 shadow-sm"
                         style="width:96px;height:96px;font-size:2rem;background:linear-gradient(135deg,#4f46e5,#7c3aed)">
                        {{ $user->avatar_initials }}
                    </div>
                    
                    <h5 class="fw-bold mb-1 text-slate-900 dark:text-white">{{ $user->name }}</h5>
                    <div class="text-muted small mb-3">{{ $user->user_name ? '@'.$user->user_name : 'No Username' }}</div>
                    
                    <div class="mb-4">
                        @if($user->isSuperAdmin())
                            <x-ts-badge text="Super Admin" color="fuchsia" class="px-3 py-1" />
                        @elseif($user->is_admin)
                            <x-ts-badge text="Admin" color="indigo" class="px-3 py-1" />
                        @else
                            <x-ts-badge text="User" color="gray" class="px-3 py-1" />
                        @endif
                    </div>
                    
                    <hr class="my-4" style="border-color:#f1f5f9; @apply dark:border-slate-700">
                    
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        @if($user->is_locked)
                            <x-ts-badge text="Account Locked" color="yellow" />
                        @elseif($user->is_active)
                            <x-ts-badge text="Active Account" color="green" />
                        @else
                            <x-ts-badge text="Account Inactive" color="red" />
                        @endif

                        @if($user->is_2fa_enabled)
                            <x-ts-badge text="2FA Enabled" color="teal" />
                        @endif
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Right Column: Details ────────────────────────────────────────── --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100 dark:bg-slate-800" style="border-radius:12px">
                <div class="card-body p-4 p-md-5">
                    
                    <h6 class="text-uppercase text-muted fw-bold mb-4 pb-2" style="font-size:0.75rem;letter-spacing:1px;border-bottom:1px solid #f1f5f9; @apply dark:border-slate-700">
                        Contact Information
                    </h6>
                    
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <div class="text-muted small fw-semibold mb-1">Email Address</div>
                            <div class="text-slate-900 dark:text-slate-200">
                                <a href="mailto:{{ $user->email }}" class="text-decoration-none text-indigo-600 dark:text-indigo-400">
                                    {{ $user->email }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-semibold mb-1">Phone Number</div>
                            <div class="text-slate-900 dark:text-slate-200">{{ $user->phone ?? '—' }}</div>
                        </div>
                    </div>

                    <h6 class="text-uppercase text-muted fw-bold mb-4 pb-2" style="font-size:0.75rem;letter-spacing:1px;border-bottom:1px solid #f1f5f9; @apply dark:border-slate-700">
                        System Information
                    </h6>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="text-muted small fw-semibold mb-1">Account Created</div>
                            <div class="text-slate-900 dark:text-slate-200" title="{{ $user->created_at }}">
                                {{ $user->created_at?->format('F j, Y - H:i') ?? '—' }}
                                <div class="text-muted small">{{ $user->created_at?->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-semibold mb-1">Last Updated</div>
                            <div class="text-slate-900 dark:text-slate-200" title="{{ $user->updated_at }}">
                                {{ $user->updated_at?->format('F j, Y - H:i') ?? '—' }}
                                <div class="text-muted small">{{ $user->updated_at?->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-semibold mb-1">Last Login</div>
                            <div class="text-slate-900 dark:text-slate-200" title="{{ $user->last_login_at }}">
                                {{ $user->last_login_at?->format('F j, Y - H:i') ?? 'Never' }}
                                @if($user->last_login_at)
                                <div class="text-muted small">{{ $user->last_login_at?->diffForHumans() }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-semibold mb-1">Internal ID (UUID)</div>
                            <div class="text-slate-900 dark:text-slate-200">
                                <code class="text-secondary dark:bg-slate-900 dark:text-slate-400 p-1 rounded" style="font-size:0.75rem">{{ $user->id }}</code>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
