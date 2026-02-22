@extends('layouts.portal')

@php
/*
 * Prepare a JSON-safe dataset for Alpine.js.
 * Only public, non-sensitive fields are serialised: org name, type, initials,
 * gradient colour, and the pre-computed login URL.
 */
$typeLabels = [
    'government'  => 'Government',
    'secretariat' => 'Secretariat',
    'agency'      => 'Agency',
    'department'  => 'Department',
    'unit'        => 'Unit',
];
$typeColors = [
    'government'  => '#dc2626',
    'secretariat' => '#4f46e5',
    'agency'      => '#0284c7',
    'department'  => '#16a34a',
    'unit'        => '#d97706',
];

$alpineOrgs = $tenants->map(function ($t) use ($typeLabels, $typeColors) {
    $words    = array_values(array_filter(explode(' ', $t->organization_name)));
    $initials = implode('', array_map(
        fn ($w) => strtoupper($w[0]),
        array_slice($words, 0, 2)
    ));
    $typeKey = $t->tenant_type?->value ?? '';
    $color   = $typeColors[$typeKey] ?? '#475569';
    return [
        'id'       => $t->id,
        'name'     => $t->organization_name,
        'type'     => $typeLabels[$typeKey] ?? 'Organisation',
        'type_key' => $typeKey,
        'color'    => $color,
        'initials' => $initials,
        'url'      => $t->login_url,
    ];
})->values()->all();

$totalCount = count($alpineOrgs);
@endphp

@section('content')

{{--
    Root Alpine component owns all reactive state:
      search      — debounced text query
      activeType  — active filter pill key (or 'all')
      orgs        — serialised tenant dataset
    Computed:
      filtered    — intersection of search + activeType
      uniqueTypes — distinct type_key values for pill row
      countText   — reactive subtitle string
--}}
<div
    class="portal-shell"
    x-data="{
        search:     '',
        activeType: 'all',
        orgs:       @js($alpineOrgs),

        get filtered() {
            const q = this.search.trim().toLowerCase();
            return this.orgs.filter(o => {
                const typeOk   = this.activeType === 'all' || o.type_key === this.activeType;
                const searchOk = !q
                    || o.name.toLowerCase().includes(q)
                    || o.type.toLowerCase().includes(q);
                return typeOk && searchOk;
            });
        },

        get uniqueTypes() {
            return [...new Set(this.orgs.map(o => o.type_key).filter(Boolean))];
        },

        get countText() {
            const n = this.filtered.length;
            return n + (n === 1 ? ' organisation' : ' organisations');
        },

        countForType(key) {
            return this.orgs.filter(o => o.type_key === key).length;
        },

        selectType(key) {
            this.activeType = (this.activeType === key) ? 'all' : key;
        },

        typeLabel(key) {
            const m = {
                government:'Government', secretariat:'Secretariat',
                agency:'Agency', department:'Department', unit:'Unit'
            };
            return m[key] || key;
        },

        clearAll() {
            this.search     = '';
            this.activeType = 'all';
            this.$nextTick(() => this.$refs.searchInput?.focus());
        }
    }"
    @keydown.escape.window="clearAll()"
>

    {{-- ════════════ HEADER ════════════════════════════════════════════════ --}}
    <header class="p-header" role="banner">
        <div class="p-header-inner">

            <a href="/" class="p-brand" aria-label="Fayiloli EDMS — home">
                <img src="/img/fayiloli-icon.svg"
                     class="p-brand-icon"
                     alt=""
                     aria-hidden="true"
                     width="26" height="26">
                <span class="p-brand-name">Fayiloli EDMS</span>
                <span class="p-brand-ver" aria-hidden="true">v2.9</span>
            </a>

            <a href="/login" class="p-admin-link" title="Platform administrator login">
                <i class="fas fa-shield-halved" aria-hidden="true"></i>
                <span class="p-admin-label">Platform Admin</span>
            </a>

        </div>
    </header>

    {{-- ════════════ HERO (search + filters) ══════════════════════════════ --}}
    <section class="p-hero" aria-labelledby="portal-heading">

        <div class="p-hero-mesh" aria-hidden="true"></div>

        <div class="p-hero-body">

            <div class="p-hero-icon" aria-hidden="true">
                <i class="fas fa-building-columns"></i>
            </div>

            <h1 id="portal-heading" class="p-hero-title">
                Find Your Organisation
            </h1>
            <p class="p-hero-sub">
                Search for your government agency or organisation to securely access your EDMS workspace.
            </p>

            {{-- Search --}}
            <div class="p-search-wrap" role="search">
                <label for="portalSearch" class="sr-only">Search organisations</label>
                <i class="fas fa-magnifying-glass p-search-icon" aria-hidden="true"></i>
                <input
                    type="search"
                    id="portalSearch"
                    x-ref="searchInput"
                    x-model.debounce.150ms="search"
                    class="p-search-input"
                    placeholder="Type an organisation name or type…"
                    autocomplete="off"
                    spellcheck="false"
                    aria-describedby="portalCount"
                >
                <button
                    type="button"
                    class="p-search-clear"
                    x-show="search.length > 0"
                    x-cloak
                    @click="search = ''; $refs.searchInput.focus()"
                    aria-label="Clear search"
                >
                    <i class="fas fa-xmark" aria-hidden="true"></i>
                </button>
            </div>

            {{-- Type filter pills (hidden when only one type exists) --}}
            <div
                class="p-type-filters"
                role="group"
                aria-label="Filter by organisation type"
                x-show="uniqueTypes.length > 1"
                x-cloak
            >
                {{-- "All" pill --}}
                <button
                    type="button"
                    class="p-pill"
                    :class="{ 'p-pill--active': activeType === 'all' }"
                    @click="activeType = 'all'"
                    :aria-pressed="(activeType === 'all').toString()"
                >
                    All
                    <span class="p-pill-count">{{ $totalCount }}</span>
                </button>

                {{-- Per-type pills --}}
                <template x-for="type in uniqueTypes" :key="type">
                    <button
                        type="button"
                        class="p-pill"
                        :class="{ 'p-pill--type-active': activeType === type }"
                        :style="activeType === type
                            ? (() => { const c = orgs.find(o => o.type_key === type)?.color || '#7c3aed'; return 'border-color:'+c+';color:'+c+';background:'+c+'1a'; })()
                            : ''"
                        @click="selectType(type)"
                        :aria-pressed="(activeType === type).toString()"
                    >
                        <span x-text="typeLabel(type)"></span>
                        <span class="p-pill-count" x-text="countForType(type)"></span>
                    </button>
                </template>
            </div>

            {{-- Reactive count --}}
            <p class="p-count" id="portalCount" aria-live="polite" aria-atomic="true">
                <span x-text="countText">{{ $totalCount }} organisations</span> available
            </p>

        </div>
    </section>

    {{-- ════════════ MAIN — org grid ══════════════════════════════════════ --}}
    <main class="p-main">

        @if ($totalCount > 0)

            <div class="p-grid" role="list" aria-label="Available organisations">

                <template x-for="org in filtered" :key="org.id">
                    <article
                        class="p-card"
                        role="listitem"
                        x-transition:enter="p-card-enter"
                        x-transition:enter-start="p-card-enter-from"
                        x-transition:enter-end="p-card-enter-to"
                    >
                        <div class="p-card-top">
                            <div
                                class="p-card-avatar"
                                :style="'background:linear-gradient(135deg,' + org.color + 'cc,' + org.color + ')'"
                                aria-hidden="true"
                            >
                                <span x-text="org.initials"></span>
                            </div>

                            <div class="p-card-meta">
                                <div class="p-card-name" x-text="org.name"></div>
                                <span
                                    class="p-card-badge"
                                    :style="'color:' + org.color + ';background:' + org.color + '15;border-color:' + org.color + '40'"
                                    x-text="org.type"
                                ></span>
                            </div>
                        </div>

                        <a
                            class="p-card-btn"
                            :href="org.url"
                            :style="'background:' + org.color"
                            :aria-label="'Sign in to ' + org.name"
                            x-data="{ going: false }"
                            @click="going = true"
                            :class="{ 'p-card-btn--going': going }"
                        >
                            <span x-show="going" x-cloak aria-hidden="true"
                                  style="display:inline-block;width:12px;height:12px;border:2px solid rgba(255,255,255,0.4);border-top-color:#fff;border-radius:50%;animation:p-spin 0.7s linear infinite"></span>
                            <i class="fas fa-arrow-right-to-bracket" aria-hidden="true" x-show="!going"></i>
                            <span x-text="going ? 'Opening…' : 'Sign In'">Sign In</span>
                        </a>
                    </article>
                </template>

            </div>

            {{-- Empty: filters produce zero results --}}
            <div
                class="p-empty"
                x-show="filtered.length === 0"
                x-cloak
                x-transition
                role="status"
                aria-live="polite"
                aria-atomic="true"
            >
                <div class="p-empty-icon">
                    <i class="fas fa-magnifying-glass-minus" aria-hidden="true"></i>
                </div>
                <p class="p-empty-title">No organisations match your filter</p>
                <p class="p-empty-sub">Try a different search term or remove the active filter.</p>
                <button type="button" class="p-clear-btn" @click="clearAll()">
                    <i class="fas fa-rotate-left" aria-hidden="true"></i>
                    Clear all filters
                </button>
            </div>

        @else

            {{-- Empty: no active tenants provisioned --}}
            <div class="p-empty" role="status">
                <div class="p-empty-icon">
                    <i class="fas fa-building" aria-hidden="true"></i>
                </div>
                <p class="p-empty-title">No active organisations available</p>
                <p class="p-empty-sub">Contact the platform administrator to provision your workspace.</p>
            </div>

        @endif

    </main>

    {{-- ════════════ FOOTER ════════════════════════════════════════════════ --}}
    <footer class="p-footer" role="contentinfo">
        <p>
            <i class="fas fa-lock" aria-hidden="true" style="font-size:0.7rem"></i>
            Credentials are validated securely within your organisation's isolated workspace.
        </p>
        <p>&copy; {{ date('Y') }} NectarMetrics Solutions Limited &middot; EDMS Fayiloli v2.9</p>
    </footer>

</div>{{-- /x-data portal-shell --}}


{{-- ══ Styles (scoped — no dependency on app.css) ══════════════════════════ --}}
<style>
/* Alpine card-enter classes (referenced by x-transition:enter directives) */
.p-card-enter      { transition: opacity 0.2s ease-out, transform 0.2s ease-out; }
.p-card-enter-from { opacity: 0; transform: translateY(10px) scale(0.97); }
.p-card-enter-to   { opacity: 1; transform: translateY(0)    scale(1);    }

/* Sign-in button loading state */
@keyframes p-spin { to { transform: rotate(360deg); } }
.p-card-btn--going { opacity: 0.8; pointer-events: none; cursor: default; }

/* WCAG 2.5.5 — 44×44 px minimum touch targets */
.p-pill     { min-height: 36px; }   /* pills are exempt from 44px as inline controls */
.p-card-btn { min-height: 40px; }
.p-admin-link { min-height: 40px; }
.p-clear-btn  { min-height: 40px; }

/* Shell */
.portal-shell { display:flex; flex-direction:column; min-height:100vh; }

/* Header */
.p-header {
    background:#0f172a;
    border-bottom:1px solid rgba(255,255,255,0.06);
    position:sticky; top:0; z-index:50;
}
.p-header-inner {
    max-width:1200px; margin:0 auto; padding:0.9rem 1.5rem;
    display:flex; align-items:center; justify-content:space-between; gap:1rem;
}
.p-brand { display:flex; align-items:center; gap:0.6rem; }
.p-brand-icon { width:26px; height:26px; flex-shrink:0; }
.p-brand-name { font-size:1rem; font-weight:700; color:#f1f5f9; letter-spacing:-0.02em; white-space:nowrap; }
.p-brand-ver {
    font-size:0.62rem; font-weight:600; color:#475569;
    background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08);
    padding:0.12rem 0.42rem; border-radius:999px;
}
.p-admin-link {
    display:flex; align-items:center; gap:0.4rem;
    color:#64748b; font-size:0.78rem; font-weight:500;
    padding:0.45rem 0.85rem; border:1px solid #1e293b; border-radius:6px;
    white-space:nowrap; transition:color 0.15s, border-color 0.15s;
}
.p-admin-link:hover { color:#e2e8f0; border-color:#7c3aed; }

/* Hero */
.p-hero {
    position:relative; background:#0f172a; overflow:hidden;
    padding:4rem 1.5rem 3rem; text-align:center; color:#f1f5f9;
}
.p-hero-mesh {
    position:absolute; inset:0; pointer-events:none;
    background:
        radial-gradient(ellipse 60% 50% at 20% 40%, rgba(124,58,237,0.18) 0%, transparent 60%),
        radial-gradient(ellipse 50% 60% at 80% 60%, rgba(79,70,229,0.13) 0%, transparent 55%),
        radial-gradient(ellipse 80% 40% at 50% 100%, rgba(30,27,75,0.55) 0%, transparent 70%);
}
.p-hero-body { position:relative; max-width:600px; margin:0 auto; }
.p-hero-icon {
    width:64px; height:64px; background:rgba(124,58,237,0.18);
    border:1px solid rgba(124,58,237,0.35); border-radius:16px;
    display:inline-flex; align-items:center; justify-content:center;
    font-size:1.75rem; color:#a78bfa; margin-bottom:1.25rem;
}
.p-hero-title {
    font-size:clamp(1.7rem,4vw,2.4rem); font-weight:800;
    letter-spacing:-0.04em; margin:0 0 0.65rem; color:#f8fafc; line-height:1.15;
}
.p-hero-sub {
    color:#94a3b8; font-size:0.95rem; line-height:1.6; margin:0 auto 1.75rem;
}
.p-count { color:#475569; font-size:0.75rem; font-weight:500; margin-top:0.85rem; }

/* Search */
.p-search-wrap { position:relative; max-width:480px; margin:0 auto; }
.p-search-icon {
    position:absolute; left:1rem; top:50%; transform:translateY(-50%);
    color:#475569; font-size:0.82rem; pointer-events:none;
}
.p-search-input {
    width:100%;
    padding:0.9rem 2.5rem 0.9rem 2.75rem;
    border-radius:10px; border:1.5px solid #2d3748;
    background:#1e293b; color:#f1f5f9;
    font-size:0.92rem; font-family:inherit; outline:none;
    transition:border-color 0.15s, box-shadow 0.15s;
    -webkit-appearance:none;
}
.p-search-input::placeholder { color:#4a5568; }
.p-search-input:focus { border-color:#7c3aed; box-shadow:0 0 0 3px rgba(124,58,237,0.22); }
.p-search-input::-webkit-search-cancel-button { -webkit-appearance:none; }
.p-search-clear {
    position:absolute; right:0.75rem; top:50%; transform:translateY(-50%);
    background:none; border:none; color:#64748b; cursor:pointer;
    padding:0.3rem; border-radius:4px; font-size:0.82rem; font-family:inherit;
    transition:color 0.15s;
}
.p-search-clear:hover { color:#94a3b8; }

/* Type filter pills */
.p-type-filters {
    display:flex; flex-wrap:wrap; justify-content:center;
    gap:0.5rem; margin-top:1rem;
}
.p-pill {
    display:inline-flex; align-items:center; gap:0.35rem;
    padding:0.3rem 0.75rem;
    background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1);
    border-radius:999px; color:#94a3b8;
    font-size:0.75rem; font-weight:600; font-family:inherit;
    cursor:pointer; white-space:nowrap;
    transition:background 0.15s, color 0.15s, border-color 0.15s;
}
.p-pill:hover { color:#e2e8f0; background:rgba(255,255,255,0.1); }
.p-pill--active {
    color:#a78bfa !important;
    border-color:#7c3aed !important;
    background:rgba(124,58,237,0.18) !important;
}
.p-pill-count {
    background:rgba(255,255,255,0.12);
    font-size:0.65rem; font-weight:700;
    padding:0.05rem 0.38rem; border-radius:999px;
}

/* Main */
.p-main {
    flex:1; max-width:1200px; width:100%;
    margin:2.5rem auto; padding:0 1.5rem;
}
.p-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(276px, 1fr));
    gap:1rem;
}

/* Org card */
.p-card {
    background:#ffffff; border:1px solid #e2e8f0; border-radius:14px;
    padding:1.25rem; display:flex; flex-direction:column; gap:1rem;
    transition:box-shadow 0.2s, border-color 0.2s, transform 0.15s;
}
.p-card:hover {
    box-shadow:0 8px 28px rgba(0,0,0,0.1);
    border-color:#c7d2fe; transform:translateY(-2px);
}
.p-card-top { display:flex; align-items:flex-start; gap:0.9rem; }
.p-card-avatar {
    width:50px; height:50px; border-radius:11px;
    display:flex; align-items:center; justify-content:center;
    font-size:1rem; font-weight:800; color:#fff; flex-shrink:0;
}
.p-card-meta { flex:1; min-width:0; }
.p-card-name {
    font-size:0.875rem; font-weight:600; color:#1e293b;
    line-height:1.4; margin-bottom:0.35rem;
    overflow:hidden; display:-webkit-box;
    -webkit-line-clamp:2; -webkit-box-orient:vertical;
}
.p-card-badge {
    display:inline-block; font-size:0.63rem; font-weight:700;
    text-transform:uppercase; letter-spacing:0.07em;
    padding:0.13rem 0.52rem; border-radius:999px; border:1px solid;
}
.p-card-btn {
    display:flex; align-items:center; justify-content:center;
    gap:0.45rem; padding:0.625rem 1rem; border-radius:9px;
    font-size:0.82rem; font-weight:600; color:#fff;
    transition:opacity 0.15s, transform 0.12s, box-shadow 0.15s;
}
.p-card-btn:hover {
    opacity:0.88; color:#fff;
    transform:translateY(-1px); box-shadow:0 4px 16px rgba(0,0,0,0.25);
}

/* Empty states */
.p-empty { text-align:center; padding:5rem 1rem; color:#64748b; }
.p-empty-icon { font-size:2.5rem; opacity:0.3; margin-bottom:1rem; }
.p-empty-title { font-size:0.9rem; font-weight:600; color:#475569; margin-bottom:0.35rem; }
.p-empty-sub { font-size:0.8rem; color:#94a3b8; }
.p-clear-btn {
    display:inline-flex; align-items:center; gap:0.4rem;
    margin-top:1.25rem; padding:0.5rem 1rem;
    background:none; border:1px solid #e2e8f0; border-radius:8px;
    color:#7c3aed; font-size:0.8rem; font-weight:600; font-family:inherit;
    cursor:pointer; transition:background 0.15s, border-color 0.15s;
}
.p-clear-btn:hover { background:rgba(124,58,237,0.06); border-color:#7c3aed; }

/* Footer */
.p-footer {
    text-align:center; padding:1.5rem 1rem;
    color:#94a3b8; font-size:0.72rem;
    border-top:1px solid #e2e8f0; background:#f8fafc;
    margin-top:auto; display:flex; flex-direction:column; gap:0.25rem;
}

/* Screen-reader only */
.sr-only {
    position:absolute; width:1px; height:1px; padding:0; margin:-1px;
    overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border-width:0;
}

/* Responsive */
@media (max-width:600px) {
    .p-hero         { padding:2.75rem 1.25rem 2rem; }
    .p-main         { margin:1.5rem auto; padding:0 1rem; }
    .p-grid         { grid-template-columns:1fr; }
    .p-header-inner { padding:0.75rem 1rem; }
    .p-admin-label  { display:none; }
    .p-hero-title   { font-size:1.65rem; }
}
</style>

@endsection
