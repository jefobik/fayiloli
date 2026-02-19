@extends('layouts.app')

@section('content')

<div class="dashboard-wrap">

    {{-- ── Page Title + Quick Actions ────────────────────────────────── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem">
        <div>
            <h1 style="font-size:1.4rem;font-weight:800;color:#1e293b;margin:0">
                Welcome back, {{ Auth::user()?->name ?? 'User' }} 👋
            </h1>
            <p style="color:#64748b;font-size:0.875rem;margin:0.25rem 0 0">
                {{ now()->format('l, F j, Y') }} &nbsp;·&nbsp;
                <span class="tenant-badge">
                    <i class="fas fa-shield-alt" style="font-size:0.6rem"></i>
                    @if(!empty($userRoles))
                        {{ ucfirst($userRoles[0]) }}
                    @else
                        Member
                    @endif
                </span>
            </p>
        </div>

        <div style="display:flex;gap:0.5rem;flex-wrap:wrap">
            <a href="{{ route('documents.index') }}" class="toolbar-btn toolbar-btn-primary">
                <i class="fas fa-file-alt"></i> Documents
            </a>
            <button class="toolbar-btn toolbar-btn-outline" onclick="uploadFiles()">
                <i class="fas fa-upload"></i> Upload
            </button>
            <a href="{{ route('tags.index') }}" class="toolbar-btn toolbar-btn-outline">
                <i class="fas fa-tags"></i> Tags
            </a>
        </div>
    </div>

    {{-- ── Live Dashboard Stats (Livewire with charts + activity) ─────── --}}
    <livewire:dashboard-stats />

</div>

{{-- ── Make page-content visible immediately for home page ─────────────── --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var content = document.querySelector('.page-content');
        if (content) content.style.display = 'block';
        var overlay = document.getElementById('loadingOverlay');
        if (overlay) overlay.style.display = 'none';
    });
</script>

<style>
    .dashboard-wrap { padding: 1.5rem; }
    @media (max-width: 640px) { .dashboard-wrap { padding: 1rem; } }
</style>

@endsection
