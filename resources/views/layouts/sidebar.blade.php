@php
    use App\Enums\TenantModule;
    $tenant = tenancy()->tenant ?? null;
    $authUser = Auth::user();

    $canDocs  = $tenant?->hasModule(TenantModule::DOCUMENTS) && $authUser?->can('view documents');
    $canTags  = $tenant?->hasModule(TenantModule::TAGS) && $authUser?->can('view tags');
    $canUsers = $tenant?->hasModule(TenantModule::USERS) && $authUser?->can('view users');
    $canProj  = $tenant?->hasModule(TenantModule::PROJECTS);
    $canConts = $tenant?->hasModule(TenantModule::HRM);
    $canStats = $tenant?->hasModule(TenantModule::STATS);

    $showModSection = $canUsers || $canProj || $canConts || $canStats;
@endphp

{{-- ── Sidebar Logo ─────────────────────────────────────────────────── --}}
<div class="sidebar-logo flex items-center h-16 shrink-0 group overflow-hidden"
     :class="sidebarCollapsed ? 'justify-center' : ''">
    <a href="{{ route('home') }}" aria-label="Ostrich — go to dashboard" wire:navigate
        class="flex items-center gap-3 text-indigo-600 dark:text-indigo-400 no-underline transition-all duration-300 min-w-0">
        <svg class="w-8 h-8 shrink-0 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300"
            aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
            <path fill="currentColor"
                d="M512 256a15 15 0 00-7.1-12.8l-52-32 52-32.5a15 15 0 000-25.4L264 2.3c-4.8-3-11-3-15.9 0L7 153.3a15 15 0 000 25.4L58.9 211 7.1 243.3a15 15 0 000 25.4L58.8 301 7.1 333.3a15 15 0 000 25.4l241 151a15 15 0 0015.9 0l241-151a15 15 0 00-.1-25.5l-52-32 52-32.5A15 15 0 00512 256z" />
        </svg>
        <span x-show="!sidebarCollapsed" x-cloak
              class="font-bold text-xl tracking-tight text-slate-900 dark:text-white whitespace-nowrap">Ostrich</span>
        <span x-show="!sidebarCollapsed" x-cloak
              class="text-[0.65rem] font-bold px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 shrink-0"
              aria-hidden="true">v2.9</span>
    </a>
</div>

{{-- ── Main Navigation ──────────────────────────────────────────────── --}}
<nav class="flex flex-1 flex-col mt-4 overflow-hidden" aria-label="Main navigation">

    {{-- Section: Main --}}
    <div x-show="!sidebarCollapsed" x-cloak
         class="text-xs font-bold leading-6 text-slate-500 dark:text-slate-400 uppercase tracking-widest px-2 mb-2"
         role="heading" aria-level="2">Main</div>

    {{-- Dashboard --}}
    <a href="{{ route('home') }}" wire:navigate
        title="Dashboard"
        class="group flex items-center gap-x-3 rounded-md py-2 text-sm font-semibold mb-1 transition-all duration-200 overflow-hidden
               {{ Route::is('home')
                  ? 'border-l-[3px] border-indigo-500 bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 pl-2.25'
                  : 'border-l-[3px] border-transparent text-slate-700 hover:text-indigo-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:text-indigo-400 dark:hover:bg-slate-700/60 pl-2.25' }}"
        :class="sidebarCollapsed ? 'justify-center px-2' : ''"
        {{ Route::is('home') ? 'aria-current=page' : '' }}>
        <i class="fas fa-home shrink-0 w-5 text-center text-base
                  {{ Route::is('home') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400' }}"
            aria-hidden="true"></i>
        <span x-show="!sidebarCollapsed" x-cloak class="nav-text">Dashboard</span>
    </a>

    @if ($canDocs)
        <a href="{{ route('documents.index') }}" wire:navigate
            title="Documents"
            class="group flex items-center gap-x-3 rounded-md py-2 text-sm font-semibold mb-1 transition-all duration-200 overflow-hidden
                   {{ Route::is('documents.index')
                      ? 'border-l-[3px] border-indigo-500 bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 pl-2.25'
                      : 'border-l-[3px] border-transparent text-slate-700 hover:text-indigo-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:text-indigo-400 dark:hover:bg-slate-700/60 pl-2.25' }}"
            :class="sidebarCollapsed ? 'justify-center px-2' : ''"
            {{ Route::is('documents.index') ? 'aria-current=page' : '' }}>
            <i class="fas fa-file-alt shrink-0 w-5 text-center text-base
                      {{ Route::is('documents.index') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400' }}"
                aria-hidden="true"></i>
            <span x-show="!sidebarCollapsed" x-cloak class="nav-text flex-1">Documents</span>
            <span x-show="!sidebarCollapsed" x-cloak class="badge ml-auto text-xs" id="sidebar-doc-count" aria-live="polite"></span>
        </a>
    @endif

    @if ($canTags)
        <a href="{{ route('tags.index') }}" wire:navigate
            title="Tags"
            class="group flex items-center gap-x-3 rounded-md py-2 text-sm font-semibold mb-1 transition-all duration-200 overflow-hidden
                   {{ Route::is('tags.*')
                      ? 'border-l-[3px] border-indigo-500 bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 pl-2.25'
                      : 'border-l-[3px] border-transparent text-slate-700 hover:text-indigo-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:text-indigo-400 dark:hover:bg-slate-700/60 pl-2.25' }}"
            :class="sidebarCollapsed ? 'justify-center px-2' : ''"
            {{ Route::is('tags.*') ? 'aria-current=page' : '' }}>
            <i class="fas fa-tags shrink-0 w-5 text-center text-base
                      {{ Route::is('tags.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400' }}"
                aria-hidden="true"></i>
            <span x-show="!sidebarCollapsed" x-cloak class="nav-text">Tags</span>
        </a>
    @endif

    {{-- ── Workspaces / Folder Tree ──────────────────────────────────── --}}
    @if ($canDocs && Route::is('documents.index'))
        <div x-show="!sidebarCollapsed" x-cloak
             class="text-xs font-bold leading-6 text-slate-500 dark:text-slate-400 uppercase tracking-widest px-2 mt-4 mb-2 flex items-center"
             role="heading" aria-level="2">Workspaces</div>
        <ul x-show="!sidebarCollapsed" x-cloak
            class="folders space-y-1" aria-label="Workspace folders"
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
        <div x-show="!sidebarCollapsed" x-cloak
             class="text-xs font-bold leading-6 text-slate-500 dark:text-slate-400 uppercase tracking-widest px-2 mt-6 mb-2"
             role="heading" aria-level="2">Modules</div>
        <div x-show="sidebarCollapsed" x-cloak class="mt-4 border-t border-slate-200 dark:border-slate-800 mb-2"></div>

        @if ($canUsers)
            <a href="{{ route('users.index') }}" wire:navigate
                title="Users"
                class="group flex items-center gap-x-3 rounded-md py-2 text-sm font-semibold mb-1 transition-all duration-200 overflow-hidden
                       {{ Route::is('users.*')
                          ? 'border-l-[3px] border-indigo-500 bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 pl-2.25'
                          : 'border-l-[3px] border-transparent text-slate-700 hover:text-indigo-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:text-indigo-400 dark:hover:bg-slate-700/60 pl-2.25' }}"
                :class="sidebarCollapsed ? 'justify-center px-2' : ''"
                {{ Route::is('users.*') ? 'aria-current=page' : '' }}>
                <i class="fas fa-users shrink-0 w-5 text-center text-base
                          {{ Route::is('users.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400' }}"
                   aria-hidden="true"></i>
                <span x-show="!sidebarCollapsed" x-cloak class="nav-text">Users</span>
            </a>
        @endif

        @if ($canProj)
            <a href="{{ route('projects.index') }}" wire:navigate
                title="Projects"
                class="group flex items-center gap-x-3 rounded-md py-2 text-sm font-semibold mb-1 transition-all duration-200 overflow-hidden
                       {{ Route::is('projects.*')
                          ? 'border-l-[3px] border-indigo-500 bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 pl-2.25'
                          : 'border-l-[3px] border-transparent text-slate-700 hover:text-indigo-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:text-indigo-400 dark:hover:bg-slate-700/60 pl-2.25' }}"
                :class="sidebarCollapsed ? 'justify-center px-2' : ''"
                {{ Route::is('projects.*') ? 'aria-current=page' : '' }}>
                <i class="fas fa-project-diagram shrink-0 w-5 text-center text-base
                          {{ Route::is('projects.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400' }}"
                   aria-hidden="true"></i>
                <span x-show="!sidebarCollapsed" x-cloak class="nav-text">Projects</span>
            </a>
        @endif

        @if ($canConts)
            <a href="{{ route('contacts.index') }}" wire:navigate
                title="Human Resources"
                class="group flex items-center gap-x-3 rounded-md py-2 text-sm font-semibold mb-1 transition-all duration-200 overflow-hidden
                       {{ Route::is('contacts.*')
                          ? 'border-l-[3px] border-indigo-500 bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 pl-2.25'
                          : 'border-l-[3px] border-transparent text-slate-700 hover:text-indigo-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:text-indigo-400 dark:hover:bg-slate-700/60 pl-2.25' }}"
                :class="sidebarCollapsed ? 'justify-center px-2' : ''"
                {{ Route::is('contacts.*') ? 'aria-current=page' : '' }}>
                <i class="fas fa-address-book shrink-0 w-5 text-center text-base
                          {{ Route::is('contacts.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400' }}"
                   aria-hidden="true"></i>
                <span x-show="!sidebarCollapsed" x-cloak class="nav-text">Human Resources</span>
            </a>
        @endif

        @if ($canStats)
            <a href="{{ route('stats.index') }}" wire:navigate
                title="Statistics"
                class="group flex items-center gap-x-3 rounded-md py-2 text-sm font-semibold mb-1 transition-all duration-200 overflow-hidden
                       {{ Route::is('stats.*')
                          ? 'border-l-[3px] border-indigo-500 bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 pl-2.25'
                          : 'border-l-[3px] border-transparent text-slate-700 hover:text-indigo-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:text-indigo-400 dark:hover:bg-slate-700/60 pl-2.25' }}"
                :class="sidebarCollapsed ? 'justify-center px-2' : ''"
                {{ Route::is('stats.*') ? 'aria-current=page' : '' }}>
                <i class="fas fa-chart-bar shrink-0 w-5 text-center text-base
                          {{ Route::is('stats.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400' }}"
                   aria-hidden="true"></i>
                <span x-show="!sidebarCollapsed" x-cloak class="nav-text">Statistics</span>
            </a>
        @endif
    @endif

    {{-- ── Collapse / Expand Button (desktop only) ─────────────────────── --}}
    <button type="button"
            class="hidden lg:flex items-center justify-center w-full py-2 mt-4 rounded-md text-slate-500 dark:text-slate-400 hover:text-indigo-500 hover:bg-slate-100 dark:hover:bg-slate-700/60 transition-colors text-xs"
            @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('sidebarCollapsed', sidebarCollapsed)"
            :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
        <i :class="sidebarCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'" class="fas text-xs"></i>
        <span x-show="!sidebarCollapsed" x-cloak class="ml-2 text-xs font-medium">Collapse</span>
    </button>

</nav>

{{-- ── Sidebar Footer (user info) ───────────────────────────────────── --}}
<div class="sidebar-footer mt-auto border-t border-slate-200 dark:border-slate-800 pt-4 pb-2 overflow-hidden"
     aria-label="Current user">
    <div class="flex items-center gap-x-3"
         :class="sidebarCollapsed ? 'justify-center px-0' : 'px-2'">
        <div class="avatar flex items-center justify-center w-8 h-8 rounded-full text-[0.7rem] font-bold text-white bg-linear-to-br from-indigo-600 to-purple-600 shrink-0 shadow-sm"
            aria-hidden="true">
            {{ strtoupper(substr(Auth::user()?->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(explode(' ', Auth::user()?->name ?? 'U ')[1] ?? '', 0, 1)) }}
        </div>
        <div x-show="!sidebarCollapsed" x-cloak class="flex-1 min-w-0 overflow-hidden">
            <div title="{{ Auth::user()?->name }}" class="text-sm font-bold text-slate-900 dark:text-white truncate">
                {{ Auth::user()?->name }}
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-300 mt-0.5 flex items-center gap-1.5 line-clamp-1">
                <span class="truncate">{{ Auth::user()?->email ?? 'Member' }}</span>
                @if(Auth::user()?->getRoleNames()->isNotEmpty())
                    <span class="px-1.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-200 font-bold tracking-wide uppercase text-[0.68rem] shrink-0 border border-slate-200 dark:border-slate-600">
                        {{ Auth::user()->getRoleNames()->first() }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Copyright Footer ─────────────────────────────────────────── --}}
<div x-show="!sidebarCollapsed" x-cloak
     class="shrink-0 border-t border-slate-200 dark:border-slate-800 px-5 pt-3 pb-4">
    <div class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed text-center font-medium">
        &copy; {{ date('Y') }} NectarMetrics Solutions EDMS Platform<br>
        <span class="opacity-75">Ostrich {{ app()->version() }} · v12</span>
    </div>
</div>

{{-- Hidden folder icon ref for legacy JS --}}
<img id="getFolderIcon" src="{{ global_asset('img/folder.png') }}" style="display:none" alt="">

<style>
    .subfolders {
        margin-left: 0.75rem;
        border-left: 1px solid rgba(148, 163, 184, 0.2);
    }
    .dark-mode .subfolders {
        border-left-color: rgba(255, 255, 255, 0.06);
    }
    .subfolders .folders li a {
        padding-left: 1.75rem;
    }
    .toggle-subfolders-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: #64748b;
        font-size: 0.75rem;
        padding: 0 0.3rem;
        margin-left: auto;
        flex-shrink: 0;
        transition: color 0.15s;
    }
    .dark-mode .toggle-subfolders-btn { color: #64748b; }
    .toggle-subfolders-btn:hover { color: #94a3b8; }
    .category { margin-left: 0.5rem; margin-bottom: 3px; font-size: 0.8rem; color: #64748b; }
    .dark-mode .category { color: #94a3b8; }
    .category-tags { margin-left: 1rem; display: block; }
    .tags hr { margin: 0.3em 0.5em; border-color: rgba(148, 163, 184, 0.2); }
    .dark-mode .tags hr { border-color: rgba(255, 255, 255, 0.06); }
    .sortable-ghost { opacity: 0.6; background: #a78bfa !important; }
    .sidebar-sort-btn {
        margin-left: 0.5rem;
        background: none;
        border: none;
        cursor: pointer;
        color: #7c3aed;
        font-size: 1rem;
        border-radius: 4px;
    }
    .sidebar-sort-btn:focus-visible { outline: 2px solid #7c3aed; outline-offset: 2px; }
    .dark-mode .sidebar-sort-btn { color: #a78bfa; }
</style>

<script src="/custom-js/sidebar-enhancements.js"></script>
