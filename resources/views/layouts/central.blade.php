@php
    // ── Use ThemeService for centralized theme management ──────────────────────
    $themeService = app(\App\Services\ThemeService::class);
    $theme = $themeService->getThemePreference();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    {{-- Allow pinch-zoom on all devices (WCAG 1.4.4) --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ config('app.name', 'Ostrich') }}</title>

    {{-- ── Anti-FOUC + all client-side globals (canonical ThemeService implementation).
         Replaces the two hand-rolled script blocks that were:
           • Missing window.__prefersReducedMotion (required by themeManager)
           • Missing localStorage sync (caused stale theme on hard-reload)
           • Missing data-theme / data-tenancy-context attributes
         ThemeService::generateThemeBootstrapScript() sets every global the tenant
         stack's theme-manager.js expects, making central ↔ tenant context symmetric. --}}
    {!! $themeService->generateThemeBootstrapScript($theme) !!}

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon"  href="/favicon.ico">
    <link rel="apple-touch-icon"          href="/img/fayiloli-icon.svg">

    {{-- Inter font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Livewire Styles (Auto-injected in v3+) --}}

    <style>
        /* ── Reset & Base ───────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }
        html { height: 100%; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100%;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            font-size: 1rem;
            line-height: 1.75;
        }

        /* ── Navbar ─────────────────────────────────────────────────────── */
        .central-nav {
            background: #0f172a;
            height: 60px;
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            gap: 0.5rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 0 rgba(255,255,255,0.06), 0 2px 12px rgba(0,0,0,0.35);
        }
        .central-nav-brand {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            text-decoration: none;
            flex-shrink: 0;
            padding: 0.25rem 0.4rem;
            border-radius: 8px;
            transition: background 0.15s;
        }
        .central-nav-brand:hover { background: rgba(255,255,255,0.06); }
        .central-nav-brand .brand-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: #f1f5f9;
            white-space: nowrap;
        }
        .central-nav-brand .brand-badge {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            background: rgba(124,58,237,0.22);
            color: #a78bfa;
            padding: 0.15rem 0.45rem;
            border-radius: 4px;
            border: 1px solid rgba(124,58,237,0.3);
        }

        /* divider */
        .central-nav-divider {
            width: 1px;
            height: 24px;
            background: rgba(255,255,255,0.08);
            flex-shrink: 0;
            margin: 0 0.25rem;
        }

        /* nav links */
        .central-nav-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.7rem;
            border-radius: 6px;
            font-size: 0.845rem;
            font-weight: 500;
            color: #94a3b8;
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
            white-space: nowrap;
            line-height: 1;
        }
        .central-nav-link:hover { background: rgba(255,255,255,0.08); color: #e2e8f0; }
        .central-nav-link.active {
            background: rgba(124,58,237,0.18);
            color: #c4b5fd;
            font-weight: 600;
        }
        .central-nav-link i { font-size: 0.8rem; width: 13px; text-align: center; }

        /* spacer */
        .central-nav-spacer { flex: 1; }

        /* ── Right-side icon button (notification bell, etc.) ───────────── */
        .central-icon-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px; height: 34px;
            background: none;
            border: none;
            border-radius: 8px;
            color: #64748b;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
            position: relative;
            flex-shrink: 0;
        }
        .central-icon-btn:hover { background: rgba(255,255,255,0.08); color: #cbd5e1; }
        .central-icon-btn i { font-size: 0.9rem; }
        /* notification dot */
        .central-icon-btn .notif-dot {
            position: absolute;
            top: 6px; right: 6px;
            width: 7px; height: 7px;
            background: #7c3aed;
            border-radius: 50%;
            border: 1.5px solid #0f172a;
        }

        /* ── User avatar button (dropdown trigger) ──────────────────────── */
        .central-user-btn {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.3rem 0.5rem 0.3rem 0.35rem;
            background: none;
            border: 1px solid transparent;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s;
            color: #e2e8f0;
            flex-shrink: 0;
        }
        .central-user-btn:hover {
            background: rgba(255,255,255,0.07);
            border-color: rgba(255,255,255,0.1);
        }
        .central-user-btn[aria-expanded="true"] {
            background: rgba(124,58,237,0.12);
            border-color: rgba(124,58,237,0.35);
        }
        .central-user-info { text-align: left; }
        .central-user-name {
            font-size: 0.845rem;
            font-weight: 600;
            color: #f1f5f9;
            line-height: 1.2;
            white-space: nowrap;
            max-width: 130px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .central-user-role {
            font-size: 0.7rem;
            color: #64748b;
            line-height: 1;
            margin-top: 0.1rem;
        }
        .central-super-badge {
            font-size: 0.58rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            background: rgba(220,38,38,0.18);
            color: #fca5a5;
            padding: 0.1rem 0.28rem;
            border-radius: 3px;
            border: 1px solid rgba(220,38,38,0.3);
            vertical-align: middle;
            margin-left: 0.2rem;
        }
        .central-user-chevron {
            font-size: 0.6rem;
            color: #475569;
            transition: transform 0.2s ease;
            margin-left: 0.1rem;
        }
        .central-user-chevron.rotated { transform: rotate(180deg); }

        /* ── Avatar circle ──────────────────────────────────────────────── */
        .central-nav-avatar {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            flex-shrink: 0;
            letter-spacing: 0.03em;
        }

        /* ── Dropdown panel ─────────────────────────────────────────────── */
        .central-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            min-width: 292px;
            background: #0d1526;
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px;
            box-shadow:
                0 0 0 1px rgba(124,58,237,0.1),
                0 4px 8px -2px rgba(0,0,0,0.45),
                0 24px 64px -8px rgba(0,0,0,0.72);
            z-index: 200;
            overflow: hidden;
        }

        /* header */
        .central-dropdown-header {
            padding: 1rem 1rem 0.9rem;
            background: linear-gradient(160deg, rgba(124,58,237,0.11) 0%, transparent 70%);
            border-bottom: 1px solid rgba(255,255,255,0.055);
            position: relative;
        }
        .central-dropdown-header::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, #7c3aed 0%, #4f46e5 55%, transparent 100%);
        }

        /* context badge */
        .dd-ctx-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.59rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #a78bfa;
            background: rgba(124,58,237,0.13);
            border: 1px solid rgba(124,58,237,0.24);
            padding: 0.16rem 0.48rem;
            border-radius: 20px;
            margin-bottom: 0.72rem;
        }
        .dd-ctx-badge i { font-size: 0.58rem; }

        /* avatar */
        .dd-avatar {
            width: 42px; height: 42px;
            border-radius: 11px;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 0.9rem;
            font-weight: 800;
            flex-shrink: 0;
            box-shadow: 0 0 0 2px rgba(124,58,237,0.25), 0 3px 10px rgba(0,0,0,0.4);
        }
        .dd-avatar--super {
            background: linear-gradient(135deg, #dc2626, #7c3aed);
            box-shadow: 0 0 0 2px rgba(220,38,38,0.3), 0 3px 10px rgba(0,0,0,0.4);
        }

        /* name / email / role */
        .dd-name {
            font-size: 0.88rem;
            font-weight: 700;
            color: #f8fafc;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 175px;
        }
        .dd-email {
            font-size: 0.74rem;
            color: #3d546e;
            margin-top: 0.17rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 175px;
        }
        .dd-role-pill {
            display: inline-flex;
            align-items: center;
            margin-top: 0.38rem;
            font-size: 0.61rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            background: rgba(124,58,237,0.17);
            color: #a78bfa;
            padding: 0.15rem 0.44rem;
            border-radius: 4px;
            border: 1px solid rgba(124,58,237,0.27);
        }

        /* last-login line */
        .dd-last-login {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            margin-top: 0.6rem;
            padding-top: 0.55rem;
            border-top: 1px solid rgba(255,255,255,0.045);
            font-size: 0.67rem;
            color: #2d4057;
        }
        .dd-last-login i { font-size: 0.58rem; }

        /* tenant stats row */
        .dd-stats-row {
            display: flex;
            gap: 0.38rem;
            padding: 0.6rem 0.85rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .dd-stat {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.08rem;
            padding: 0.42rem 0.2rem;
            border-radius: 8px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.055);
            text-decoration: none;
            transition: background 0.14s, border-color 0.14s;
        }
        a.dd-stat:hover {
            background: rgba(255,255,255,0.065);
            border-color: rgba(255,255,255,0.1);
        }
        .dd-stat-num {
            font-size: 1.1rem;
            font-weight: 800;
            color: #e2e8f0;
            line-height: 1;
        }
        .dd-stat-lbl {
            font-size: 0.58rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #334155;
        }
        .dd-stat--active  .dd-stat-num  { color: #86efac; }
        .dd-stat--active  { border-color: rgba(74,222,128,0.14); }
        .dd-stat--pending .dd-stat-num  { color: #fcd34d; }
        .dd-stat--pending { border-color: rgba(251,191,36,0.18); }

        /* sections, labels */
        .central-dropdown-section { padding: 0.3rem 0; }
        .central-dropdown-label {
            font-size: 0.59rem;
            font-weight: 700;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: #253347;
            padding: 0.5rem 1rem 0.2rem;
        }

        /* items — flex-start so two-line items align the icon to top */
        .central-dropdown-item {
            display: flex;
            align-items: flex-start;
            gap: 0.72rem;
            padding: 0.58rem 1rem;
            font-size: 0.845rem;
            color: #94a3b8;
            text-decoration: none;
            transition: background 0.13s;
            cursor: pointer;
        }
        .central-dropdown-item:hover { background: rgba(255,255,255,0.045); }
        .central-dropdown-item .di-icon {
            width: 30px; height: 30px;
            border-radius: 8px;
            background: rgba(255,255,255,0.045);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.77rem;
            color: #475569;
            flex-shrink: 0;
            transition: background 0.13s, color 0.13s;
            margin-top: 2px;
        }
        .central-dropdown-item:hover .di-icon { background: rgba(124,58,237,0.18); color: #c4b5fd; }
        .di-body { min-width: 0; flex: 1; }
        .di-title {
            font-size: 0.845rem;
            font-weight: 500;
            color: #94a3b8;
            line-height: 1.3;
            transition: color 0.13s;
        }
        .di-sub {
            font-size: 0.71rem;
            color: #253347;
            margin-top: 0.07rem;
            line-height: 1.3;
            font-weight: 400;
            transition: color 0.13s;
        }
        .central-dropdown-item:hover .di-title { color: #e2e8f0; }
        .central-dropdown-item:hover .di-sub   { color: #3d546e; }
        .di-check {
            flex-shrink: 0;
            align-self: center;
            margin-left: auto;
            font-size: 0.6rem;
            color: #7c3aed;
        }

        /* pending count badge */
        .dd-pending-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 19px; height: 19px;
            padding: 0 0.32rem;
            border-radius: 10px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            font-size: 0.6rem;
            font-weight: 800;
            margin-left: auto;
            flex-shrink: 0;
            align-self: center;
            box-shadow: 0 1px 5px rgba(245,158,11,0.4);
        }

        /* danger / sign-out item */
        .central-dropdown-item.danger .di-icon { color: #7f1d1d; }
        .central-dropdown-item.danger .di-title { color: #f87171; }
        .central-dropdown-item.danger .di-sub   { color: #3d1515; }
        .central-dropdown-item.danger:hover { background: rgba(239,68,68,0.07); }
        .central-dropdown-item.danger:hover .di-icon  { background: rgba(239,68,68,0.18); color: #fca5a5; }
        .central-dropdown-item.danger:hover .di-title { color: #fecaca; }
        .central-dropdown-item.danger:hover .di-sub   { color: #7f1d1d; }

        .central-dropdown-divider { height: 1px; background: rgba(255,255,255,0.05); margin: 0.12rem 0; }

        /* ── Dropdown open/close animation — zero Tailwind dependency ───────── */
        /* Alpine x-transition uses these class names instead of Tailwind utilities  */
        /* (translate-y-1, scale-98, ease-out, duration-150 are undefined here)      */
        .dd-t-enter { transition: opacity 0.15s ease-out, transform 0.15s ease-out; }
        .dd-t-leave { transition: opacity 0.10s ease-in,  transform 0.10s ease-in;  }
        .dd-t-from  { opacity: 0; transform: translateY(-6px) scale(0.97); }
        .dd-t-to    { opacity: 1; transform: translateY(0px)  scale(1);    }

        /* ── x-cloak: hide Alpine-controlled elements until initialised ─── */
        [x-cloak] { display: none !important; }

        /* ── Inline theme pills (inside user dropdown) ──────────────────── */
        .central-theme-pill {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.28rem;
            padding: 0.55rem 0.35rem;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.07);
            background: rgba(255,255,255,0.04);
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s, color 0.15s, border-color 0.15s, box-shadow 0.15s;
        }
        .central-theme-pill:hover {
            background: rgba(255,255,255,0.08);
            color: #cbd5e1;
            border-color: rgba(255,255,255,0.13);
        }
        .central-theme-pill.active {
            background: rgba(124,58,237,0.22);
            color: #c4b5fd;
            border-color: rgba(124,58,237,0.45);
            box-shadow: 0 0 0 3px rgba(124,58,237,0.12);
        }
        .central-theme-pill i { font-size: 0.88rem; }

        /* mobile hamburger */
        .central-nav-toggle {
            display: none;
            width: 36px; height: 36px;
            align-items: center; justify-content: center;
            background: none; border: none;
            border-radius: 8px;
            color: #64748b;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
            flex-shrink: 0;
        }
        .central-nav-toggle:hover { background: rgba(255,255,255,0.07); color: #e2e8f0; }

        /* collapsible nav on mobile */
        .central-nav-links {
            display: flex;
            align-items: center;
            gap: 0.15rem;
        }

        @media (max-width: 767px) {
            .central-nav-toggle { display: flex; }
            .central-nav-links { display: none; }
            .central-nav-divider { display: none; }
            .brand-badge { display: none; }
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
        .table { font-size: 0.9rem; }
        .table th { font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; }
        .table td { vertical-align: middle; color: #374151; }
        .table-hover tbody tr:hover { background: #f8fafc; }

        /* ── Form overrides ─────────────────────────────────────────────── */
        .form-label { font-size: 0.875rem; font-weight: 600; color: #374151; }
        .form-control, .form-select {
            font-size: 0.9rem; border-radius: 8px;
            border: 1.5px solid #e2e8f0; color: #1e293b;
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .form-control:focus, .form-select:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124,58,237,0.1);
        }
        .invalid-feedback { font-size: 0.8rem; }
        .form-text { font-size: 0.8rem; color: #64748b; }

        /* ── Button overrides ───────────────────────────────────────────── */
        .btn { font-weight: 600; border-radius: 8px; font-size: 0.9rem; }
        .btn-primary { background: #7c3aed; border-color: #7c3aed; }
        .btn-primary:hover { background: #6d28d9; border-color: #6d28d9; }
        .btn-outline-primary { color: #7c3aed; border-color: #7c3aed; }
        .btn-outline-primary:hover { background: #7c3aed; border-color: #7c3aed; }
        .btn-sm { font-size: 0.8rem; border-radius: 6px; }

        /* ── Alert overrides ────────────────────────────────────────────── */
        .alert { border-radius: 10px; font-size: 0.9rem; }

        /* ── Pagination ─────────────────────────────────────────────────── */
        .pagination { --bs-pagination-font-size: 0.875rem; }

        /* ── Dark mode: content area overrides for Tailwind-based elements ── */
        /* These override Tailwind bg-white / text-slate-* classes when dark mode
           is active, since Bootstrap dark mode and Tailwind dark: classes both
           need the html.dark class to activate simultaneously. */
        html.dark body {
            background-color: #020617 !important;
        }
        html.dark .bg-white {
            background-color: #0f172a !important;
        }
        html.dark .bg-slate-50 {
            background-color: #020617 !important;
        }
        html.dark .bg-slate-100 {
            background-color: #334155 !important;
        }
        html.dark .border-slate-100 {
            border-color: #334155 !important;
        }
        html.dark .border-slate-200 {
            border-color: #334155 !important;
        }
        html.dark .text-slate-900 {
            color: #cbd5e1 !important;
        }
        html.dark .text-slate-800 {
            color: #e2e8f0 !important;
        }
        html.dark .text-slate-700 {
            color: #cbd5e1 !important;
        }
        html.dark .text-slate-600 {
            color: #94a3b8 !important;
        }
        html.dark .text-slate-500 {
            color: #94a3b8 !important;
        }
        html.dark .text-slate-400 {
            color: #94a3b8 !important;
        }
        html.dark .hover\:bg-slate-50:hover {
            background-color: #253347 !important;
        }
        html.dark .card {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #e2e8f0;
        }
        html.dark .card-footer {
            background-color: #1e293b !important;
            border-top-color: #334155 !important;
        }
        /* Table rows in dark */
        html.dark .table td { color: #cbd5e1; }
        html.dark .table th { color: #94a3b8; }
        html.dark .table-hover tbody tr:hover { background: rgba(99,102,241,0.08) !important; }
        html.dark .border-b.border-slate-100 { border-color: #334155 !important; }
        /* Pending row amber tint in dark */
        html.dark .bg-amber-50\/60 {
            background-color: rgba(180, 120, 20, 0.15) !important;
        }
        /* Breadcrumb in dark */
        html.dark .breadcrumb-item.active { color: #94a3b8; }


        /* ── Footer ─────────────────────────────────────────────────────── */
        .central-footer {
            text-align: center;
            padding: 0.9rem 1.5rem;
            font-size: 0.8rem;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            background: #fff;
        }
        html.dark .central-footer {
            color: #94a3b8;
            background: #0f172a;
            border-top-color: #1e293b;
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
<nav class="central-nav">

    {{-- Brand --}}
    <a href="{{ route('tenants.index') }}" class="central-nav-brand" aria-label="Ostrich Admin — home">
        <img src="/img/fayiloli-icon.svg"
             alt=""
             aria-hidden="true"
             width="30" height="30"
             style="border-radius:7px;flex-shrink:0">
        <span class="brand-name">Ostrich</span>
        <span class="brand-badge" aria-label="Central Admin">Admin</span>
    </a>

    <div class="central-nav-divider" aria-hidden="true"></div>

    {{-- Primary nav links --}}
    <div class="central-nav-links d-none d-md-flex" role="navigation" aria-label="Main navigation">
        <a href="{{ route('tenants.index') }}"
           class="central-nav-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}"
           @if(request()->routeIs('tenants.*')) aria-current="page" @endif>
            <i class="fas fa-building-user" aria-hidden="true"></i> Tenants
        </a>
    </div>

    <div class="central-nav-spacer"></div>

    {{-- Right-side actions --}}
    @auth
    <div class="d-none d-md-flex align-items-center gap-1">

        {{-- Notification bell (ready for future wiring) --}}
        <button type="button" class="central-icon-btn" aria-label="Notifications" title="Notifications (coming soon)">
            <i class="fas fa-bell" aria-hidden="true"></i>
            {{-- Uncomment dot when there are unread notifications:
            <span class="notif-dot" aria-hidden="true"></span>
            --}}
        </button>

        {{-- User avatar dropdown --}}
        <div class="position-relative"
             x-data="{ userOpen: false }"
             @click.outside="userOpen = false"
             @close-theme-dropdown.window="userOpen = false"
             @keydown.escape.window="userOpen = false">
            <button type="button"
                    class="central-user-btn"
                    @click="userOpen = !userOpen"
                    :aria-expanded="userOpen.toString()"
                    aria-haspopup="true"
                    aria-label="Account menu for {{ Auth::user()?->name ?? 'user' }}">
                <div class="central-nav-avatar"
                     aria-hidden="true"
                     @if(Auth::user()?->isSuperAdmin()) style="background:linear-gradient(135deg,#dc2626,#7c3aed)" @endif>
                    {{ Auth::user()?->avatar_initials ?? 'SA' }}
                </div>
                <div class="central-user-info d-none d-lg-block">
                    <div class="central-user-name">
                        {{ Str::limit(Auth::user()?->name ?? '', 22) }}
                        @if(Auth::user()?->isSuperAdmin())
                            <span class="central-super-badge">SUPER</span>
                        @endif
                    </div>
                    <div class="central-user-role">{{ Auth::user()?->roleLabel() }}</div>
                </div>
                <i class="fas fa-chevron-down central-user-chevron"
                   :class="{ 'rotated': userOpen }"
                   aria-hidden="true"></i>
            </button>

            {{-- Dropdown panel --}}
            <div class="central-dropdown"
                 x-show="userOpen"
                 x-cloak
                 x-transition:enter="dd-t-enter"
                 x-transition:enter-start="dd-t-from"
                 x-transition:enter-end="dd-t-to"
                 x-transition:leave="dd-t-leave"
                 x-transition:leave-start="dd-t-to"
                 x-transition:leave-end="dd-t-from">

                @php
                    /* Lightweight aggregate — total, active, pending tenant counts */
                    try {
                        $ddStats = \App\Models\Tenant::selectRaw(
                            "count(*) as total,
                             sum(case when status = 'active'    then 1 else 0 end) as active_cnt,
                             sum(case when status = 'pending'   then 1 else 0 end) as pending_cnt,
                             sum(case when status = 'suspended' then 1 else 0 end) as suspended_cnt"
                        )->first();
                    } catch (\Throwable) {
                        $ddStats = (object)['total'=>'—','active_cnt'=>'—','pending_cnt'=>0,'suspended_cnt'=>0];
                    }
                    $ddPending = (int)$ddStats->pending_cnt;
                @endphp

                {{-- ── Header ─────────────────────────────────────────────── --}}
                <div class="central-dropdown-header">

                    {{-- Context badge --}}
                    <div class="dd-ctx-badge">
                        <i class="fas fa-shield-halved" aria-hidden="true"></i>
                        Central Admin Panel
                    </div>

                    {{-- User identity row --}}
                    <div class="d-flex align-items-center gap-3">
                        <div class="dd-avatar {{ Auth::user()?->isSuperAdmin() ? 'dd-avatar--super' : '' }}"
                             aria-hidden="true">
                            {{ Auth::user()?->avatar_initials ?? 'SA' }}
                        </div>
                        <div style="min-width:0;flex:1">
                            <div class="dd-name">
                                {{ Str::limit(Auth::user()?->name ?? '', 26) }}
                                @if(Auth::user()?->isSuperAdmin())
                                    <span class="central-super-badge" aria-label="Super Administrator">SUPER</span>
                                @endif
                            </div>
                            <div class="dd-email" title="{{ Auth::user()?->email }}">
                                {{ Auth::user()?->email }}
                            </div>
                            <span class="dd-role-pill">{{ Auth::user()?->roleLabel() }}</span>
                        </div>
                    </div>

                    {{-- Last sign-in timestamp --}}
                    @if(Auth::user()?->last_login_at)
                    <div class="dd-last-login">
                        <i class="fas fa-clock" aria-hidden="true"></i>
                        Last sign-in: {{ Auth::user()->last_login_at->diffForHumans() }}
                    </div>
                    @endif

                </div>{{-- /header --}}

                {{-- ── Tenant stats row ────────────────────────────────── --}}
                <div class="dd-stats-row" role="group" aria-label="System overview">
                    <a href="{{ route('tenants.index') }}"
                       class="dd-stat"
                       @click="userOpen = false"
                       title="All tenants">
                        <span class="dd-stat-num">{{ $ddStats->total }}</span>
                        <span class="dd-stat-lbl">Total</span>
                    </a>
                    <a href="{{ route('tenants.index') }}"
                       class="dd-stat dd-stat--active"
                       @click="userOpen = false"
                       title="Active tenants">
                        <span class="dd-stat-num">{{ $ddStats->active_cnt }}</span>
                        <span class="dd-stat-lbl">Active</span>
                    </a>
                    @if((int)$ddStats->suspended_cnt > 0)
                    <a href="{{ route('tenants.index') }}"
                       class="dd-stat"
                       @click="userOpen = false"
                       title="{{ $ddStats->suspended_cnt }} suspended"
                       style="border-color:rgba(148,163,184,0.18)">
                        <span class="dd-stat-num" style="color:#94a3b8">{{ $ddStats->suspended_cnt }}</span>
                        <span class="dd-stat-lbl">Suspended</span>
                    </a>
                    @endif
                    @if($ddPending > 0)
                    <a href="{{ route('tenants.index') }}"
                       class="dd-stat dd-stat--pending"
                       @click="userOpen = false"
                       title="{{ $ddPending }} awaiting approval">
                        <span class="dd-stat-num">{{ $ddPending }}</span>
                        <span class="dd-stat-lbl">Pending</span>
                    </a>
                    @endif
                </div>

                {{-- ── Management ───────────────────────────────────────── --}}
                <div class="central-dropdown-section">
                    <div class="central-dropdown-label">Management</div>

                    <a href="{{ route('tenants.index') }}"
                       class="central-dropdown-item"
                       @click="userOpen = false"
                       @if(request()->routeIs('tenants.*')) aria-current="page" @endif>
                        <span class="di-icon"><i class="fas fa-building-user" aria-hidden="true"></i></span>
                        <div class="di-body">
                            <div class="di-title">Tenant Management</div>
                            <div class="di-sub">Workspaces, domains &amp; billing plans</div>
                        </div>
                        @if($ddPending > 0)
                            <span class="dd-pending-badge" aria-label="{{ $ddPending }} pending approval">{{ $ddPending }}</span>
                        @elseif(request()->routeIs('tenants.*'))
                            <i class="fas fa-check di-check" aria-hidden="true"></i>
                        @endif
                    </a>
                </div>

                <div class="central-dropdown-divider"></div>

                {{-- ── Appearance ───────────────────────────────────────── --}}
                <div class="central-dropdown-section">
                    <div class="central-dropdown-label">Appearance</div>
                    <div class="d-flex gap-2 px-3 pb-3 pt-1"
                         x-data="{ cTheme: '{{ $theme }}' }"
                         @theme-updated.window="cTheme = $event.detail.theme">
                        @foreach([
                            ['light',  'fa-sun',                'Light'],
                            ['system', 'fa-circle-half-stroke', 'Auto'],
                            ['dark',   'fa-moon',               'Dark'],
                        ] as [$tv, $ti, $tl])
                        <button type="button"
                                class="central-theme-pill"
                                :class="{ 'active': cTheme === '{{ $tv }}' }"
                                @click="
                                    cTheme = '{{ $tv }}';
                                    $dispatch('theme-updated', { theme: '{{ $tv }}' });
                                    $dispatch('cycle-theme', { nextTheme: '{{ $tv }}' });
                                    userOpen = false;
                                "
                                :aria-pressed="cTheme === '{{ $tv }}' ? 'true' : 'false'"
                                title="{{ $tl }} theme">
                            <i class="fas {{ $ti }}" aria-hidden="true"></i>
                            <span>{{ $tl }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>

                <div class="central-dropdown-divider"></div>

                {{-- ── Sign out ─────────────────────────────────────────── --}}
                <div class="central-dropdown-section" style="padding-bottom:.45rem">
                    <form x-ref="centralLogoutForm"
                          id="central-logout"
                          action="{{ route('logout') }}"
                          method="POST"
                          class="d-none"
                          aria-hidden="true">
                        @csrf
                    </form>
                    <button type="button"
                            class="central-dropdown-item danger w-100 text-start"
                            style="background:none;border:none;width:100%"
                            @click="$refs.centralLogoutForm.submit()"
                            aria-label="Sign out of Central Admin">
                        <span class="di-icon">
                            <i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i>
                        </span>
                        <div class="di-body">
                            <div class="di-title">Sign out</div>
                            <div class="di-sub">End your admin session</div>
                        </div>
                    </button>
                </div>

            </div>{{-- /dropdown --}}
        </div>{{-- /position-relative --}}

    </div>{{-- /d-md-flex --}}
    @endauth

    {{-- Mobile hamburger --}}
    <button type="button"
            class="central-nav-toggle d-md-none"
            aria-label="Toggle navigation"
            aria-expanded="false"
            aria-controls="centralNavMobile"
            onclick="
                var m = document.getElementById('centralNavMobile');
                var expanded = this.getAttribute('aria-expanded') === 'true';
                m.style.display = expanded ? 'none' : 'flex';
                this.setAttribute('aria-expanded', String(!expanded));
            ">
        <i class="fas fa-bars" aria-hidden="true" style="font-size:0.95rem"></i>
    </button>

</nav>

{{-- Mobile nav panel --}}
<nav id="centralNavMobile"
     aria-label="Mobile navigation"
     style="display:none;background:#0f172a;border-bottom:1px solid rgba(255,255,255,0.07);
            padding:0.6rem 1rem 0.8rem;flex-direction:column;gap:0.1rem;
            position:sticky;top:60px;z-index:99;box-shadow:0 8px 20px rgba(0,0,0,0.3);">

    <a href="{{ route('tenants.index') }}"
       class="central-nav-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}"
       @if(request()->routeIs('tenants.*')) aria-current="page" @endif>
        <i class="fas fa-building-user" aria-hidden="true"></i> Tenants
    </a>

    @auth
    <div style="height:1px;background:rgba(255,255,255,0.07);margin:.4rem 0;" aria-hidden="true"></div>

    {{-- Mobile user identity --}}
    <div class="d-flex align-items-center gap-2 px-2 py-1" style="opacity:.85;pointer-events:none">
        <div class="central-nav-avatar"
             style="width:26px;height:26px;font-size:0.62rem;
                    @if(Auth::user()?->isSuperAdmin()) background:linear-gradient(135deg,#dc2626,#7c3aed); @endif"
             aria-hidden="true">
            {{ Auth::user()?->avatar_initials ?? 'SA' }}
        </div>
        <div>
            <div style="font-size:0.82rem;font-weight:600;color:#f1f5f9;line-height:1.2">
                {{ Auth::user()?->name }}
                @if(Auth::user()?->isSuperAdmin())
                    <span class="central-super-badge">SUPER</span>
                @endif
            </div>
            <div style="font-size:0.68rem;color:#64748b">{{ Auth::user()?->email }}</div>
        </div>
    </div>

    <div style="height:1px;background:rgba(255,255,255,0.07);margin:.4rem 0;" aria-hidden="true"></div>

    <div x-data="{}">
        <form x-ref="mobileLogoutForm" action="{{ route('logout') }}" method="POST" class="d-none" aria-hidden="true">
            @csrf
        </form>
        <button type="button"
                class="central-nav-link"
                style="color:#fca5a5;background:none;border:none;cursor:pointer;width:100%;text-align:left;"
                @click="$refs.mobileLogoutForm.submit()"
                aria-label="Sign out">
            <i class="fas fa-arrow-right-from-bracket" aria-hidden="true" style="color:#f87171"></i>
            Sign out
        </button>
    </div>
    @endauth
</nav>

{{-- ── Page Content ─────────────────────────────────────────────────────── --}}
<main class="central-content" id="main-content" tabindex="-1">
    @yield('content')
</main>

{{-- ── Footer ───────────────────────────────────────────────────────────── --}}
<footer class="central-footer" role="contentinfo">
    &copy; {{ date('Y') }} NectarMetrics Solutions Limited &middot;
    Ostrich &middot; Central Admin
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
<div class="toast-container position-fixed top-0 inset-e-0 p-3" style="z-index:1200"
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

{{-- Livewire Scripts and Alpine (Auto-injected in v3+) --}}
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

{{--
    ╔══════════════════════════════════════════════════════════════════════╗
    ║  themeManager Alpine registration — CENTRAL LAYOUT ONLY             ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  WHY THIS EXISTS                                                     ║
    ║  theme-manager.js (resources/js/) is a Vite-compiled module that is  ║
    ║  only bundled in the TENANT stack (app.blade.php → app.js → Vite).   ║
    ║  The central layout deliberately omits @vite() — it is a Bootstrap 5 ║
    ║  CDN-only admin panel with no Tailwind dependency.                   ║
    ║                                                                      ║
    ║  Without this block, the hidden GlobalThemeSwitcher Livewire         ║
    ║  component (below) fails with:                                       ║
    ║    ReferenceError: themeManager is not defined                       ║
    ║  because its blade view declares x-data="themeManager()" and Alpine  ║
    ║  tries to resolve that function before Vite ever loads.              ║
    ║                                                                      ║
    ║  CONTRACT WITH theme-manager.js                                      ║
    ║  Exposes the identical public surface (applyTheme, cycleTheme,       ║
    ║  saveToLocalStorage, getStorageKey, getConfig) so the               ║
    ║  GlobalThemeSwitcher blade works identically in both stacks.         ║
    ║  If Vite IS ever loaded on this page, theme-manager.js re-registers  ║
    ║  via its own alpine:init listener and overwrites this one —          ║
    ║  intentional, canonical version always wins.                         ║
    ╚══════════════════════════════════════════════════════════════════════╝
--}}
<script>
document.addEventListener('alpine:init', function () {
    if (typeof Alpine === 'undefined') return;

    Alpine.data('themeManager', function () {
        return {
            currentTheme:         window.__themePreference     || 'system',
            isDarkMode:           window.__isDarkMode           || false,
            prefersReducedMotion: window.__prefersReducedMotion || false,
            isTransitioning:      false,
            tenantId:             null,   /* central context — no tenant */
            isTenantContext:      false,

            init: function () {
                var self = this;
                this.applyTheme(this.currentTheme);
                /* Re-evaluate when OS colour scheme changes */
                window.matchMedia('(prefers-color-scheme: dark)')
                    .addEventListener('change', function () {
                        if (self.currentTheme === 'system') self.applyTheme('system');
                    });
            },

            applyTheme: function (theme) {
                var isDark = theme === 'dark' ||
                    (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                var html   = document.documentElement;

                /* Bootstrap dark mode */
                html.setAttribute('data-bs-theme',        isDark ? 'dark' : 'light');
                /* Generic theme attributes */
                html.setAttribute('data-theme',           theme);
                html.setAttribute('data-tenancy-context', 'central');
                /* Tailwind / app.css dark-mode class toggles */
                html.classList.toggle('dark',      isDark);
                html.classList.toggle('dark-mode', isDark);
                document.body.classList.toggle('dark-mode', isDark);

                /* Update globals so other components stay in sync */
                window.__themePreference = theme;
                window.__isDarkMode      = isDark;
                this.isDarkMode          = isDark;

                /* Persist to localStorage (central namespace key) */
                try { localStorage.setItem('theme_preference_central', theme); } catch (_) {}

                /* Broadcast for any listeners outside this component */
                window.dispatchEvent(new CustomEvent('theme-applied', {
                    detail: { theme: theme, isDark: isDark, isTenantContext: false }
                }));
            },

            cycleTheme: function () {
                var themes = ['system', 'light', 'dark'];
                var next   = themes[(themes.indexOf(this.currentTheme) + 1) % themes.length];
                this.currentTheme = next;
                this.applyTheme(next);
                window.dispatchEvent(new CustomEvent('cycle-theme', { detail: { nextTheme: next } }));
            },

            saveToLocalStorage: function (theme) {
                try { localStorage.setItem('theme_preference_central', theme || this.currentTheme); } catch (_) {}
            },

            getStorageKey: function () { return 'theme_preference_central'; },

            getConfig: function (key) {
                var defaults = {
                    keyboard_shortcuts:          true,
                    smooth_transitions:          true,
                    system_preference_detection: true,
                    local_storage_sync:          true,
                };
                var cfg = window.__themeConfig;
                return (cfg && key in cfg) ? cfg[key] : (key in defaults ? defaults[key] : true);
            }
        };
    });
});
</script>

{{-- ── Theme persistence bridge (hidden, zero-size) ─────────────────────────
     GlobalThemeSwitcher Livewire component: its @cycle-theme.window listener
     calls $wire.updateTheme() → ThemeService::setThemePreference() → writes
     users.theme in the central DB.  Visually inert; Alpine initialises it via
     the themeManager registration above so no ReferenceError occurs. --}}
@auth
<div style="position:absolute;width:0;height:0;overflow:hidden;opacity:0;pointer-events:none"
     aria-hidden="true">
    <livewire:global-theme-switcher />
</div>
@endauth
</body>
</html>
