<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    {{-- Allow pinch-zoom on all devices (WCAG 1.4.4) --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ config('app.name', 'Fayiloli') }}</title>

    {{-- Inter font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* ── Reset & Base ───────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }
        html { height: 100%; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            min-height: 100%;
            -webkit-font-smoothing: antialiased;
            font-size: 0.9375rem;
            line-height: 1.6;
        }

        /* ── Navbar ─────────────────────────────────────────────────────── */
        .central-nav {
            background: #0f172a;
            height: 60px;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        .central-nav-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
            flex-shrink: 0;
        }
        .central-nav-brand .brand-icon {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem; color: #fff;
        }
        .central-nav-brand .brand-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: #f1f5f9;
            white-space: nowrap;
        }
        .central-nav-brand .brand-badge {
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(124,58,237,0.25);
            color: #a78bfa;
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            border: 1px solid rgba(124,58,237,0.3);
        }

        /* divider */
        .central-nav-divider {
            width: 1px;
            height: 28px;
            background: rgba(255,255,255,0.1);
            flex-shrink: 0;
        }

        /* nav links */
        .central-nav-link {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 500;
            color: #94a3b8;
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
            white-space: nowrap;
        }
        .central-nav-link:hover { background: rgba(255,255,255,0.07); color: #f1f5f9; }
        .central-nav-link.active { background: rgba(124,58,237,0.2); color: #c4b5fd; }
        .central-nav-link i { font-size: 0.8rem; width: 14px; text-align: center; }

        /* spacer */
        .central-nav-spacer { flex: 1; }

        /* user menu */
        .central-nav-user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.6rem;
            border-radius: 8px;
            cursor: pointer;
            background: none;
            border: none;
            color: #cbd5e1;
            transition: background 0.15s;
        }
        .central-nav-user:hover { background: rgba(255,255,255,0.07); }
        .central-nav-user .u-name {
            font-size: 0.82rem;
            font-weight: 600;
            color: #f1f5f9;
            max-width: 140px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .central-nav-user .u-role {
            font-size: 0.68rem;
            color: #64748b;
        }
        .central-nav-avatar {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        /* dropdown */
        .central-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 220px;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.4);
            z-index: 200;
            overflow: hidden;
        }
        .central-dropdown-header {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #334155;
        }
        .central-dropdown-header .d-name { font-size: 0.85rem; font-weight: 700; color: #f1f5f9; }
        .central-dropdown-header .d-email { font-size: 0.72rem; color: #64748b; margin-top: 0.1rem; }
        .central-dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.6rem 1rem;
            font-size: 0.82rem;
            color: #cbd5e1;
            text-decoration: none;
            transition: background 0.1s;
        }
        .central-dropdown-item:hover { background: #334155; color: #f1f5f9; }
        .central-dropdown-item i { width: 14px; text-align: center; color: #64748b; }
        .central-dropdown-item.danger { color: #f87171; }
        .central-dropdown-item.danger i { color: #f87171; }
        .central-dropdown-divider { height: 1px; background: #334155; }

        /* mobile hamburger */
        .central-nav-toggle {
            display: none;
            width: 36px; height: 36px;
            align-items: center; justify-content: center;
            background: none; border: none;
            border-radius: 6px;
            color: #94a3b8;
            cursor: pointer;
            transition: background 0.15s;
            flex-shrink: 0;
        }
        .central-nav-toggle:hover { background: rgba(255,255,255,0.07); color: #f1f5f9; }

        /* collapsible nav on mobile */
        .central-nav-links {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        @media (max-width: 767px) {
            .central-nav-toggle { display: flex; }
            .central-nav-links {
                display: none;
                position: absolute;
                top: 60px; left: 0; right: 0;
                background: #0f172a;
                flex-direction: column;
                align-items: flex-start;
                padding: 0.75rem 1rem;
                gap: 0.15rem;
                border-top: 1px solid rgba(255,255,255,0.07);
                box-shadow: 0 8px 20px rgba(0,0,0,0.3);
                z-index: 99;
            }
            .central-nav-links.open { display: flex; }
            .central-nav-link { width: 100%; padding: 0.55rem 0.75rem; }
            .central-nav-divider { display: none; }
            .brand-badge { display: none; }
            .central-nav-user .u-name,
            .central-nav-user .u-role { display: none; }
        }

        /* ── Page Content ───────────────────────────────────────────────── */
        .central-content {
            padding: 1.75rem 1.5rem;
            min-height: calc(100vh - 60px - 52px);
        }
        @media (max-width: 575px) {
            .central-content { padding: 1.25rem 1rem; }
        }

        /* ── Breadcrumb overrides ────────────────────────────────────────── */
        .breadcrumb { background: none; padding: 0; margin-bottom: 1rem; font-size: 0.82rem; }
        .breadcrumb-item + .breadcrumb-item::before { color: #94a3b8; }
        .breadcrumb-item a { color: #7c3aed; text-decoration: none; }
        .breadcrumb-item a:hover { text-decoration: underline; }
        .breadcrumb-item.active { color: #64748b; }

        /* ── Card overrides ─────────────────────────────────────────────── */
        .card { border-radius: 12px; border: 1px solid #e2e8f0; }
        .card-header { background: #fff; border-bottom: 1px solid #e2e8f0; border-radius: 12px 12px 0 0 !important; }
        .card-footer { background: #fff; border-top: 1px solid #e2e8f0; border-radius: 0 0 12px 12px !important; }

        /* ── Badge overrides ─────────────────────────────────────────────── */
        .badge { font-weight: 600; letter-spacing: 0.01em; }

        /* ── Table overrides ─────────────────────────────────────────────── */
        .table { font-size: 0.875rem; }
        .table th { font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; }
        .table td { vertical-align: middle; color: #374151; }
        .table-hover tbody tr:hover { background: #f8fafc; }

        /* ── Form overrides ─────────────────────────────────────────────── */
        .form-label { font-size: 0.82rem; font-weight: 600; color: #374151; }
        .form-control, .form-select {
            font-size: 0.875rem; border-radius: 8px;
            border: 1.5px solid #e2e8f0; color: #1e293b;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124,58,237,0.1);
        }
        .invalid-feedback { font-size: 0.78rem; }
        .form-text { font-size: 0.78rem; color: #64748b; }

        /* ── Button overrides ───────────────────────────────────────────── */
        .btn { font-weight: 600; border-radius: 8px; font-size: 0.875rem; }
        .btn-primary { background: #7c3aed; border-color: #7c3aed; }
        .btn-primary:hover { background: #6d28d9; border-color: #6d28d9; }
        .btn-outline-primary { color: #7c3aed; border-color: #7c3aed; }
        .btn-outline-primary:hover { background: #7c3aed; border-color: #7c3aed; }
        .btn-sm { font-size: 0.78rem; border-radius: 6px; }

        /* ── Alert overrides ────────────────────────────────────────────── */
        .alert { border-radius: 10px; font-size: 0.875rem; }

        /* ── Pagination ─────────────────────────────────────────────────── */
        .pagination { --bs-pagination-font-size: 0.82rem; }

        /* ── Footer ─────────────────────────────────────────────────────── */
        .central-footer {
            text-align: center;
            padding: 0.9rem 1.5rem;
            font-size: 0.72rem;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            background: #fff;
        }

        /* ── Stats card accent ──────────────────────────────────────────── */
        .stat-number { font-size: 2rem; font-weight: 800; line-height: 1; }

        /* ── Responsive table stacking on xs ────────────────────────────── */
        @media (max-width: 575px) {
            .table-responsive .table thead { display: none; }
            .table-responsive .table tr {
                display: block;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                margin-bottom: 0.75rem;
                padding: 0.5rem;
            }
            .table-responsive .table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.4rem 0.5rem;
                border: none;
                border-bottom: 1px solid #f1f5f9;
                font-size: 0.82rem;
            }
            .table-responsive .table td:last-child { border-bottom: none; }
            .table-responsive .table td::before {
                content: attr(data-label);
                font-size: 0.72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #94a3b8;
                flex-shrink: 0;
                margin-right: 0.5rem;
            }
        }
    </style>
</head>
<body>

{{-- ── Navbar ───────────────────────────────────────────────────────────── --}}
<nav class="central-nav" x-data="{ open: false }" @click.outside="open = false">

    {{-- Brand --}}
    <a href="{{ route('tenants.index') }}" class="central-nav-brand" aria-label="Fayiloli Admin — go to tenants">
        <div class="brand-icon" aria-hidden="true">
            <i class="fas fa-layer-group" aria-hidden="true"></i>
        </div>
        <span class="brand-name">Fayiloli</span>
        <span class="brand-badge" aria-label="Central Admin">Admin</span>
    </a>

    <div class="central-nav-divider"></div>

    {{-- Desktop nav links (hidden on mobile) --}}
    <div class="central-nav-links d-none d-md-flex" id="centralNavLinks" role="navigation" aria-label="Main navigation">
        <a href="{{ route('tenants.index') }}"
           class="central-nav-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}"
           {{ request()->routeIs('tenants.*') ? 'aria-current=page' : '' }}>
            <i class="fas fa-building-user" aria-hidden="true"></i> Tenants
        </a>
    </div>

    <div class="central-nav-spacer"></div>

    {{-- User menu --}}
    @auth
    <div class="position-relative" x-data="{ open: false }" @click.outside="open = false">
        <button type="button"
                class="central-nav-user"
                @click="open = !open"
                :aria-expanded="open.toString()"
                aria-haspopup="true"
                aria-label="User menu for {{ Auth::user()?->name }}">
            <div class="text-end d-none d-sm-block" aria-hidden="true">
                <div class="u-name">{{ Auth::user()?->name }}</div>
                <div class="u-role">Super Admin</div>
            </div>
            <div class="central-nav-avatar" aria-hidden="true">
                {{ strtoupper(substr(Auth::user()?->name ?? 'S', 0, 1)) }}{{ strtoupper(substr(explode(' ', Auth::user()?->name ?? 'A ')[1] ?? '', 0, 1)) }}
            </div>
            <i class="fas fa-chevron-down" aria-hidden="true" style="font-size:0.65rem;color:#64748b"></i>
        </button>

        <div class="central-dropdown"
             x-show="open" x-cloak
             role="menu"
             aria-label="User account options"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="central-dropdown-header" aria-hidden="true">
                <div class="d-name">{{ Auth::user()?->name }}</div>
                <div class="d-email">{{ Auth::user()?->email }}</div>
            </div>

            <a class="central-dropdown-item" role="menuitem" href="{{ route('tenants.index') }}">
                <i class="fas fa-building-user" aria-hidden="true"></i> Tenant Management
            </a>

            <div class="central-dropdown-divider" role="separator"></div>

            <a class="central-dropdown-item danger" role="menuitem"
               href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('central-logout').submit()">
                <i class="fas fa-sign-out-alt" aria-hidden="true"></i> Sign out
            </a>
        </div>
    </div>

    <form id="central-logout" action="{{ route('logout') }}" method="POST" class="d-none" aria-hidden="true">
        @csrf
    </form>
    @endauth

    {{-- Mobile toggle --}}
    <button type="button"
            class="central-nav-toggle d-md-none"
            id="centralNavToggle"
            aria-label="Toggle navigation"
            aria-expanded="false"
            aria-controls="centralNavMobile"
            onclick="
                var links = document.getElementById('centralNavMobile');
                var expanded = this.getAttribute('aria-expanded') === 'true';
                links.style.display = expanded ? 'none' : 'flex';
                this.setAttribute('aria-expanded', !expanded);
            ">
        <i class="fas fa-bars" aria-hidden="true" style="font-size:1rem"></i>
    </button>

</nav>

{{-- Mobile nav links --}}
<nav id="centralNavMobile"
     aria-label="Mobile navigation"
     style="
    display:none;
    background:#0f172a;
    border-bottom:1px solid rgba(255,255,255,0.07);
    padding:0.5rem 1rem 0.75rem;
    flex-direction:column;
    gap:0.15rem;
    position:sticky;top:60px;z-index:99;
    box-shadow:0 8px 20px rgba(0,0,0,0.25);
">
    <a href="{{ route('tenants.index') }}"
       class="central-nav-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}"
       {{ request()->routeIs('tenants.*') ? 'aria-current=page' : '' }}>
        <i class="fas fa-building-user" aria-hidden="true"></i> Tenant Management
    </a>
</nav>

{{-- ── Page Content ─────────────────────────────────────────────────────── --}}
<main class="central-content" id="main-content" tabindex="-1">
    @yield('content')
</main>

{{-- ── Footer ───────────────────────────────────────────────────────────── --}}
<footer class="central-footer" role="contentinfo">
    &copy; {{ date('Y') }} NectarMetrics Solutions Limited &middot;
    Fayiloli EDMS &middot; Central Admin
</footer>

{{-- ── Global Flash Toasts ──────────────────────────────────────────────── --}}
@php
    $centralFlashes = [
        'success' => ['fa-circle-check',        'text-bg-success', false, session('success')],
        'danger'  => ['fa-circle-xmark',         'text-bg-danger',  false, session('error')],
        'warning' => ['fa-triangle-exclamation', 'text-bg-warning', true,  session('warning')],
        'info'    => ['fa-circle-info',          'text-bg-info',    true,  session('info')],
    ];
    $centralFlashes = array_filter($centralFlashes, fn($f) => $f[3]);
@endphp
@if(count($centralFlashes) > 0)
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1200"
     aria-live="polite" aria-atomic="true">
    @foreach($centralFlashes as $type => [$icon, $bgClass, $lightClose, $message])
    <div class="toast align-items-center {{ $bgClass }} border-0 mb-2"
         id="flashToast{{ ucfirst($type) }}"
         role="alert"
         aria-live="{{ $type === 'danger' ? 'assertive' : 'polite' }}"
         aria-atomic="true"
         data-bs-autohide="{{ ($type === 'danger' || $type === 'warning') ? 'false' : 'true' }}"
         data-bs-delay="5000">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2" style="font-size:0.875rem">
                <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                <span>{{ $message }}</span>
            </div>
            <button type="button"
                    class="btn-close {{ $lightClose ? '' : 'btn-close-white' }} me-2 m-auto"
                    data-bs-dismiss="toast" aria-label="Dismiss notification"></button>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Alpine.js --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

@if(count($centralFlashes) > 0)
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[id^="flashToast"]').forEach(function (el) {
        bootstrap.Toast.getOrCreateInstance(el).show();
    });
});
</script>
@endif

@stack('scripts')
</body>
</html>
