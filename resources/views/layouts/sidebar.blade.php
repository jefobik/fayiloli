@php
    use App\Enums\TenantModule;
    $tenant  = tenancy()->tenant ?? null;
    $authUser = Auth::user();

    // ── Resolve which modules are visible to THIS user ─────────────────────
    // Layer 1: module must be enabled on the tenant
    // Layer 2: user must hold the required Spatie permission for that module

    $canDocs   = $tenant?->hasModule(TenantModule::DOCUMENTS)  && $authUser?->can('view documents');
    $canTags   = $tenant?->hasModule(TenantModule::TAGS)        && $authUser?->can('view tags');
    $canUsers  = $tenant?->hasModule(TenantModule::USERS)       && $authUser?->can('view users');
    $canProj   = $tenant?->hasModule(TenantModule::PROJECTS);   // no granular permission yet
    $canConts  = $tenant?->hasModule(TenantModule::CONTACTS);   // no granular permission yet

    // "Modules" section heading only renders if at least one module is visible
    $showModSection = $canUsers || $canProj || $canConts;
@endphp

{{-- ── Sidebar Logo ─────────────────────────────────────────────────── --}}
<div class="sidebar-logo">
    <a href="{{ route('home') }}" aria-label="Fayiloli — go to dashboard"
       style="display:flex;align-items:center;text-decoration:none">
        <svg style="width:26px;height:26px;flex-shrink:0" aria-hidden="true" focusable="false"
             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
            <path fill="#7c3aed" d="M512 256a15 15 0 00-7.1-12.8l-52-32 52-32.5a15 15 0 000-25.4L264 2.3c-4.8-3-11-3-15.9 0L7 153.3a15 15 0 000 25.4L58.9 211 7.1 243.3a15 15 0 000 25.4L58.8 301 7.1 333.3a15 15 0 000 25.4l241 151a15 15 0 0015.9 0l241-151a15 15 0 00-.1-25.5l-52-32 52-32.5A15 15 0 00512 256z"/>
        </svg>
        <span class="brand">Fayiloli</span>
        <span class="version" aria-hidden="true">v2.9</span>
    </a>
</div>

{{-- ── Main Navigation ──────────────────────────────────────────────── --}}
<nav class="sidebar-nav" aria-label="Main navigation">
    <div class="sidebar-section-label" role="heading" aria-level="2">Main</div>

    <a href="{{ route('home') }}"
       class="sidebar-link {{ Route::is('home') ? 'active' : '' }}"
       {{ Route::is('home') ? 'aria-current=page' : '' }}>
        <i class="fas fa-home" aria-hidden="true"></i> Dashboard
    </a>

    @if ($canDocs)
        <a href="{{ route('documents.index') }}"
           class="sidebar-link {{ Route::is('documents.index') ? 'active' : '' }}"
           {{ Route::is('documents.index') ? 'aria-current=page' : '' }}>
            <i class="fas fa-file-alt" aria-hidden="true"></i> Documents
            <span class="badge" id="sidebar-doc-count" aria-live="polite"></span>
        </a>
    @endif

    @if ($canTags)
        <a href="{{ route('tags.index') }}"
           class="sidebar-link {{ Route::is('tags.*') ? 'active' : '' }}"
           {{ Route::is('tags.*') ? 'aria-current=page' : '' }}>
            <i class="fas fa-tags" aria-hidden="true"></i> Tags
        </a>
    @endif

    {{-- ── Workspaces / Folder Tree ──────────────────────────────────── --}}
    @if ($canDocs && Route::is('documents.index'))
        <div class="sidebar-section-label" role="heading" aria-level="2"
             style="margin-top:0.75rem;display:flex;align-items:center;">Workspaces</div>
        <ul class="folders" aria-label="Workspace folders"
            style="scroll-behavior:smooth;max-height:38vh;overflow-y:auto;">
            @if (isset($folders))
                {!! $folders !!}
            @else
                {!! generateSidebarMenu() !!}
            @endif
        </ul>
        <div id="renderFolderTagsHtml"></div>
    @endif

    {{-- ── Module-gated links ─────────────────────────────────────────── --}}
    @if ($showModSection)
        <div class="sidebar-section-label" role="heading" aria-level="2"
             style="margin-top:0.75rem">Modules</div>

        @if ($canUsers)
            <a href="{{ route('users.index') }}"
               class="sidebar-link {{ Route::is('users.*') ? 'active' : '' }}"
               {{ Route::is('users.*') ? 'aria-current=page' : '' }}>
                <i class="fas fa-users" aria-hidden="true"></i> Users
            </a>
        @endif

        @if ($canProj)
            <a href="{{ route('projects.index') }}"
               class="sidebar-link {{ Route::is('projects.*') ? 'active' : '' }}"
               {{ Route::is('projects.*') ? 'aria-current=page' : '' }}>
                <i class="fas fa-project-diagram" aria-hidden="true"></i> Projects
            </a>
        @endif

        @if ($canConts)
            <a href="{{ route('contacts.index') }}"
               class="sidebar-link {{ Route::is('contacts.*') ? 'active' : '' }}"
               {{ Route::is('contacts.*') ? 'aria-current=page' : '' }}>
                <i class="fas fa-address-book" aria-hidden="true"></i> Contacts
            </a>
        @endif
    @endif
</nav>

{{-- ── Sidebar Footer ───────────────────────────────────────────────── --}}
<div class="sidebar-footer" aria-label="Current user">
    <div style="display:flex;align-items:center;gap:0.6rem">
        <div class="avatar" aria-hidden="true"
             style="width:28px;height:28px;font-size:0.7rem;background:linear-gradient(135deg,#4f46e5,#7c3aed)">
            {{ strtoupper(substr(Auth::user()?->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(explode(' ', Auth::user()?->name ?? 'U ')[1] ?? '', 0, 1)) }}
        </div>
        <div style="flex:1;min-width:0;overflow:hidden">
            <div title="{{ Auth::user()?->name }}"
                 style="font-size:0.78rem;font-weight:600;color:#cbd5e1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                {{ Auth::user()?->name }}
            </div>
            <div style="font-size:0.65rem;color:#475569">
                @if(Auth::user()?->getRoleNames()->isNotEmpty())
                    <span class="tenant-badge" style="font-size:0.6rem;padding:0.1rem 0.35rem">
                        {{ Auth::user()->getRoleNames()->first() }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Hidden folder icon ref for legacy JS --}}
<img id="getFolderIcon" src="{{ asset('img/folder.png') }}" style="display:none" alt="">

<style>
/* Subfolder indentation */
.subfolders { margin-left: 0.75rem; border-left: 1px solid rgba(255,255,255,0.06); }
.subfolders .folders li a { padding-left: 1.75rem; }

.toggle-subfolders-btn {
    background: none; border: none; cursor: pointer;
    color: #475569; font-size: 0.7rem; padding: 0 0.3rem;
    margin-left: auto; flex-shrink: 0;
    transition: color 0.15s;
}
.toggle-subfolders-btn:hover { color: #94a3b8; }

.category { margin-left: 0.5rem; margin-bottom: 3px; font-size:0.78rem; color:#94a3b8; }
.category-tags { margin-left: 1rem; display: block; }
.tags hr { margin: 0.3em 0.5em; border-color: rgba(255,255,255,0.06); }

/* Sidebar drag-and-drop ghost */
.sortable-ghost { opacity: 0.6; background: #a78bfa !important; }

/* Sidebar sort button */
.sidebar-sort-btn { margin-left: 0.5rem; background: none; border: none; cursor: pointer; color: #7c3aed; font-size: 1rem; border-radius: 4px; }
.sidebar-sort-btn:focus-visible { outline: 2px solid #7c3aed; outline-offset: 2px; }

/* Sidebar dark mode */
body.dark-mode {
    background: #0f172a !important;
    color: #cbd5e1 !important;
}
body.dark-mode .edms-sidebar, body.dark-mode .sidebar-footer, body.dark-mode .sidebar-logo {
    background: #0f172a !important;
    color: #cbd5e1 !important;
}
body.dark-mode .sidebar-link, body.dark-mode .folders li a {
    color: #cbd5e1 !important;
}
body.dark-mode .sidebar-link.active, body.dark-mode .folders li a.active {
    background: #312e81 !important;
    color: #a78bfa !important;
}
body.dark-mode .sidebar-section-label {
    color: #a78bfa !important;
}
body.dark-mode .sidebar-sort-btn, body.dark-mode .sidebar-dark-btn {
    color: #a78bfa !important;
}
.sidebar-dark-btn { margin-left: 0.5rem; background: none; border: none; cursor: pointer; color: #7c3aed; font-size: 1.1rem; border-radius: 4px; }
.sidebar-dark-btn:focus-visible { outline: 2px solid #7c3aed; outline-offset: 2px; }
</style>

<!-- Sidebar enhancements -->
<script src="/custom-js/sidebar-enhancements.js"></script>

{{-- ── Copyright Footer ──────────────────────────────────────────── --}}
<div style="border-top:1px solid rgba(255,255,255,0.05);padding:0.5rem 1.25rem 0.7rem;flex-shrink:0;margin-top:auto">
    <div style="font-size:0.62rem;color:#334155;line-height:1.5;text-align:center">
        &copy; {{ date('Y') }} EDMS Platform<br>
        <span style="opacity:0.6">Laravel {{ app()->version() }} · v12</span>
    </div>
</div>
