@php
    use App\Enums\TenantModule;
    $tenant = tenancy()->tenant ?? null;
    $authUser = Auth::user();

    $canDocs = $tenant?->hasModule(TenantModule::DOCUMENTS) && $authUser?->can('view documents');
    $canTags = $tenant?->hasModule(TenantModule::TAGS) && $authUser?->can('view tags');
    $canUsers = $tenant?->hasModule(TenantModule::USERS) && $authUser?->can('view users');
    $canRoles = $tenant?->hasModule(TenantModule::USERS) && $authUser?->can('manage roles');
    $canProj = $tenant?->hasModule(TenantModule::PROJECTS) && $authUser?->can('view projects');
    $canHrm = $tenant?->hasModule(TenantModule::HRM) && $authUser?->can('view employees');
    $canStats = $tenant?->hasModule(TenantModule::STATS) && $authUser?->can('view stats');

    $showModSection = $canUsers || $canRoles || $canProj || $canHrm || $canStats;
@endphp

{{-- ── Sidebar Logo ─────────────────────────────────────────────────── --}}
<div class="sidebar-logo flex items-center h-16 shrink-0 group overflow-hidden transition-all duration-300"
    :class="($wire.sidebarCollapsed && !sidebarHovered && !mobileMenuOpen) ? 'justify-center px-2' : 'px-6'">
    <a href="{{ route('home') }}" wire:navigate
        class="flex items-center gap-3 text-[var(--tenant-primary)] no-underline transition-all duration-300">
        <x-ts-icon name="presentation-chart-line"
            class="w-8 h-8 shrink-0 group-hover:scale-110 transition-transform duration-300" />
        <span x-show="!$wire.sidebarCollapsed || sidebarHovered || mobileMenuOpen" x-cloak
            class="font-bold text-xl tracking-tight text-[var(--text-main)] whitespace-nowrap">
            {{ $tenant?->short_name ?? 'Ostrich' }}
        </span>
    </a>
</div>

{{-- ── Main Navigation ──────────────────────────────────────────────── --}}
<nav class="flex flex-1 flex-col mt-4 overflow-hidden" aria-label="Main navigation">

    {{-- Section: Main --}}
    <div x-show="!$wire.sidebarCollapsed || sidebarHovered || mobileMenuOpen" x-cloak
        class="text-xs font-bold leading-6 text-slate-500 dark:text-slate-400 uppercase tracking-widest px-2 mb-2"
        role="heading" aria-level="2">Main</div>

    {{-- Dashboard --}}
    <a href="{{ route('home') }}" wire:navigate title="Dashboard"
        class="group flex items-center min-h-[44px] gap-x-3 rounded-md py-2 text-sm font-semibold mb-1 transition-all duration-200 overflow-hidden
               {{ Route::is('home')
    ? 'border-l-[3px] border-[var(--color-primary)] bg-[var(--color-primary)]/10 text-[var(--color-primary)] dark:bg-[var(--color-primary)]/20 dark:text-[var(--color-primary)] pl-2.25'
    : 'border-l-[3px] border-transparent text-[var(--color-text-main)] hover:text-[var(--color-primary)] hover:bg-[var(--color-surface-hover)] dark:text-[var(--color-text-main-dark)] dark:hover:text-[var(--color-primary)] dark:hover:bg-[var(--color-surface-hover-dark)] pl-2.25' }}"
        :class="($wire.sidebarCollapsed && !sidebarHovered && !mobileMenuOpen) ? 'justify-center px-2' : ''" {{ Route::is('home') ? 'aria-current=page' : '' }}>
        <i class="fas fa-home shrink-0 w-5 text-center text-base
                  {{ Route::is('home') ? 'text-[var(--color-primary)] dark:text-[var(--color-primary)]' : 'text-slate-400 group-hover:text-[var(--color-primary)] dark:group-hover:text-[var(--color-primary)]' }}"
            aria-hidden="true"></i>
        <span x-show="!$wire.sidebarCollapsed || sidebarHovered || mobileMenuOpen" x-cloak
            class="nav-text">Dashboard</span>
    </a>

    @if ($canDocs)
        <a href="{{ route('documents.index') }}" wire:navigate title="Documents"
            class="group flex items-center min-h-[44px] gap-x-3 rounded-md py-2 text-sm font-semibold mb-1 transition-all duration-200 overflow-hidden
                                           {{ Route::is('documents.index')
            ? 'border-l-[3px] border-[var(--color-primary)] bg-[var(--color-primary)]/10 text-[var(--color-primary)] dark:bg-[var(--color-primary)]/20 dark:text-[var(--color-primary)] pl-2.25'
            : 'border-l-[3px] border-transparent text-[var(--color-text-main)] hover:text-[var(--color-primary)] hover:bg-[var(--color-surface-hover)] dark:text-[var(--color-text-main-dark)] dark:hover:text-[var(--color-primary)] dark:hover:bg-[var(--color-surface-hover-dark)] pl-2.25' }}"
            :class="($wire.sidebarCollapsed && !sidebarHovered && !mobileMenuOpen) ? 'justify-center px-2' : ''" {{ Route::is('documents.index') ? 'aria-current=page' : '' }}>
            <i class="fas fa-file-alt shrink-0 w-5 text-center text-base
                                              {{ Route::is('documents.index') ? 'text-[var(--color-primary)] dark:text-[var(--color-primary)]' : 'text-slate-400 group-hover:text-[var(--color-primary)] dark:group-hover:text-[var(--color-primary)]' }}"
                aria-hidden="true"></i>
            <span x-show="!$wire.sidebarCollapsed || sidebarHovered || mobileMenuOpen" x-cloak
                class="nav-text flex-1">Documents</span>
            <span x-show="!$wire.sidebarCollapsed || sidebarHovered || mobileMenuOpen" x-cloak class="badge ml-auto text-xs"
                id="sidebar-doc-count" aria-live="polite"></span>
        </a>
    @endif

    @if ($canTags)
        <a href="{{ route('tags.index') }}" wire:navigate title="Tags"
            class="group flex items-center min-h-[44px] gap-x-3 rounded-md py-2 text-sm font-semibold mb-1 transition-all duration-200 overflow-hidden
                                           {{ Route::is('tags.*')
            ? 'border-l-[3px] border-[var(--color-primary)] bg-[var(--color-primary)]/10 text-[var(--color-primary)] dark:bg-[var(--color-primary)]/20 dark:text-[var(--color-primary)] pl-2.25'
            : 'border-l-[3px] border-transparent text-[var(--color-text-main)] hover:text-[var(--color-primary)] hover:bg-[var(--color-surface-hover)] dark:text-[var(--color-text-main-dark)] dark:hover:text-[var(--color-primary)] dark:hover:bg-[var(--color-surface-hover-dark)] pl-2.25' }}"
            :class="($wire.sidebarCollapsed && !sidebarHovered && !mobileMenuOpen) ? 'justify-center px-2' : ''" {{ Route::is('tags.*') ? 'aria-current=page' : '' }}>
            <i class="fas fa-tags shrink-0 w-5 text-center text-base
                                              {{ Route::is('tags.*') ? 'text-[var(--color-primary)] dark:text-[var(--color-primary)]' : 'text-slate-400 group-hover:text-[var(--color-primary)] dark:group-hover:text-[var(--color-primary)]' }}"
                aria-hidden="true"></i>
            <span x-show="!$wire.sidebarCollapsed || sidebarHovered || mobileMenuOpen" x-cloak class="nav-text">Tags</span>
        </a>
    @endif

    {{-- ── Workspaces / Folder Tree ──────────────────────────────────── --}}
    @if ($canDocs && Route::is('documents.index'))
        <div x-show="!$wire.sidebarCollapsed || sidebarHovered || mobileMenuOpen" x-cloak
            class="text-xs font-bold leading-6 text-slate-500 dark:text-slate-400 uppercase tracking-widest px-2 mt-4 mb-2 flex items-center"
            role="heading" aria-level="2">Workspaces</div>
        <ul x-show="!$wire.sidebarCollapsed || sidebarHovered || mobileMenuOpen" x-cloak class="folders space-y-1"
            aria-label="Workspace folders" style="scroll-behavior:smooth;max-height:38vh;overflow-y:auto;">
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
        <div x-show="!$wire.sidebarCollapsed || sidebarHovered || mobileMenuOpen" x-cloak
            class="text-xs font-bold leading-6 text-slate-500 dark:text-slate-400 uppercase tracking-widest px-2 mt-6 mb-2"
            role="heading" aria-level="2">Administration</div>
        <div x-show="$wire.sidebarCollapsed && !sidebarHovered && !mobileMenuOpen" x-cloak
            class="mt-4 border-t border-slate-200 dark:border-slate-800 mb-2"></div>

        @if(!isset($tenant) && $authUser->isAdminOrAbove())
            <a href="{{ route('admin.users.index') }}" wire:navigate title="Central Users"
                class="group flex items-center min-h-[44px] gap-x-3 rounded-md py-2 text-sm font-semibold mb-1 transition-all duration-200 overflow-hidden
                                                                       {{ Route::is('admin.users.*')
                    ? 'border-l-[3px] border-[var(--color-primary)] bg-[var(--color-primary)]/10 text-[var(--color-primary)] dark:bg-[var(--color-primary)]/20 dark:text-[var(--color-primary)] pl-2.25'
                    : 'border-l-[3px] border-transparent text-[var(--color-text-main)] hover:text-[var(--color-primary)] hover:bg-[var(--color-surface-hover)] dark:text-[var(--color-text-main-dark)] dark:hover:text-[var(--color-primary)] dark:hover:bg-[var(--color-surface-hover-dark)] pl-2.25' }}"
                :class="$wire.sidebarCollapsed && !sidebarHovered ? 'justify-center px-2' : ''" {{ Route::is('admin.users.*') ? 'aria-current=page' : '' }}>
                <i class="fas fa-users-cog shrink-0 w-5 text-center text-base
                                                                          {{ Route::is('admin.users.*') ? 'text-[var(--color-primary)] dark:text-[var(--color-primary)]' : 'text-slate-400 group-hover:text-[var(--color-primary)] dark:group-hover:text-[var(--color-primary)]' }}"
                    aria-hidden="true"></i>
                <span x-show="!$wire.sidebarCollapsed || sidebarHovered || mobileMenuOpen" x-cloak class="nav-text">Central
                    Users</span>
            </a>
        @endif

        @if ($canUsers)
            <a href="{{ route('users.index') }}" wire:navigate title="Users"
                class="group flex items-center min-h-[44px] gap-x-3 rounded-md py-2 text-sm font-semibold mb-1 transition-all duration-200 overflow-hidden
                                                                       {{ Route::is('users.*')
                    ? 'border-l-[3px] border-[var(--color-primary)] bg-[var(--color-primary)]/10 text-[var(--color-primary)] dark:bg-[var(--color-primary)]/20 dark:text-[var(--color-primary)] pl-2.25'
                    : 'border-l-[3px] border-transparent text-[var(--color-text-main)] hover:text-[var(--color-primary)] hover:bg-[var(--color-surface-hover)] dark:text-[var(--color-text-main-dark)] dark:hover:text-[var(--color-primary)] dark:hover:bg-[var(--color-surface-hover-dark)] pl-2.25' }}"
                :class="$wire.sidebarCollapsed && !sidebarHovered ? 'justify-center px-2' : ''" {{ Route::is('users.*') ? 'aria-current=page' : '' }}>
                <i class="fas fa-users shrink-0 w-5 text-center text-base
                                                                          {{ Route::is('users.*') ? 'text-[var(--color-primary)] dark:text-[var(--color-primary)]' : 'text-slate-400 group-hover:text-[var(--color-primary)] dark:group-hover:text-[var(--color-primary)]' }}"
                    aria-hidden="true"></i>
                <span x-show="!$wire.sidebarCollapsed || sidebarHovered || mobileMenuOpen" x-cloak class="nav-text">Users</span>
            </a>
        @endif

        @if ($canRoles)
            <a href="{{ route('roles.index') }}" wire:navigate title="Roles &amp; Permissions"
                class="group flex items-center min-h-[44px] gap-x-3 rounded-md py-2 text-sm font-semibold mb-1 transition-all duration-200 overflow-hidden
                                                                       {{ Route::is('roles.*')
                    ? 'border-l-[3px] border-[var(--color-primary)] bg-[var(--color-primary)]/10 text-[var(--color-primary)] dark:bg-[var(--color-primary)]/20 dark:text-[var(--color-primary)] pl-2.25'
                    : 'border-l-[3px] border-transparent text-[var(--color-text-main)] hover:text-[var(--color-primary)] hover:bg-[var(--color-surface-hover)] dark:text-[var(--color-text-main-dark)] dark:hover:text-[var(--color-primary)] dark:hover:bg-[var(--color-surface-hover-dark)] pl-2.25' }}"
                :class="$wire.sidebarCollapsed && !sidebarHovered ? 'justify-center px-2' : ''" {{ Route::is('roles.*') ? 'aria-current=page' : '' }}>
                <i class="fas fa-shield-halved shrink-0 w-5 text-center text-base
                                                                          {{ Route::is('roles.*') ? 'text-[var(--color-primary)] dark:text-[var(--color-primary)]' : 'text-slate-400 group-hover:text-[var(--color-primary)] dark:group-hover:text-[var(--color-primary)]' }}"
                    aria-hidden="true"></i>
                <span x-show="!$wire.sidebarCollapsed || sidebarHovered || mobileMenuOpen" x-cloak class="nav-text">Roles &amp;
                    Permissions</span>
            </a>
        @endif

        @if ($canProj)
            <a href="{{ route('projects.index') }}" wire:navigate title="Projects"
                class="group flex items-center min-h-[44px] gap-x-3 rounded-md py-2 text-sm font-semibold mb-1 transition-all duration-200 overflow-hidden
                                                                       {{ Route::is('projects.*')
                    ? 'border-l-[3px] border-[var(--color-primary)] bg-[var(--color-primary)]/10 text-[var(--color-primary)] dark:bg-[var(--color-primary)]/20 dark:text-[var(--color-primary)] pl-2.25'
                    : 'border-l-[3px] border-transparent text-[var(--color-text-main)] hover:text-[var(--color-primary)] hover:bg-[var(--color-surface-hover)] dark:text-[var(--color-text-main-dark)] dark:hover:text-[var(--color-primary)] dark:hover:bg-[var(--color-surface-hover-dark)] pl-2.25' }}"
                :class="$wire.sidebarCollapsed && !sidebarHovered ? 'justify-center px-2' : ''" {{ Route::is('projects.*') ? 'aria-current=page' : '' }}>
                <i class="fas fa-project-diagram shrink-0 w-5 text-center text-base
                                                                          {{ Route::is('projects.*') ? 'text-[var(--color-primary)] dark:text-[var(--color-primary)]' : 'text-slate-400 group-hover:text-[var(--color-primary)] dark:group-hover:text-[var(--color-primary)]' }}"
                    aria-hidden="true"></i>
                <span x-show="!$wire.sidebarCollapsed || sidebarHovered || mobileMenuOpen" x-cloak
                    class="nav-text">Projects</span>
            </a>
        @endif

        @if ($canHrm)
            <a href="{{ route('hrm.index') }}" wire:navigate title="Human Resources"
                class="group flex items-center min-h-[44px] gap-x-3 rounded-md py-2 text-sm font-semibold mb-1 transition-all duration-200 overflow-hidden
                                                                       {{ Route::is('hrm.*')
                    ? 'border-l-[3px] border-[var(--color-primary)] bg-[var(--color-primary)]/10 text-[var(--color-primary)] dark:bg-[var(--color-primary)]/20 dark:text-[var(--color-primary)] pl-2.25'
                    : 'border-l-[3px] border-transparent text-[var(--color-text-main)] hover:text-[var(--color-primary)] hover:bg-[var(--color-surface-hover)] dark:text-[var(--color-text-main-dark)] dark:hover:text-[var(--color-primary)] dark:hover:bg-[var(--color-surface-hover-dark)] pl-2.25' }}"
                :class="$wire.sidebarCollapsed && !sidebarHovered ? 'justify-center px-2' : ''" {{ Route::is('hrm.*') ? 'aria-current=page' : '' }}>
                <i class="fas fa-address-book shrink-0 w-5 text-center text-base
                                                                          {{ Route::is('hrm.*') ? 'text-[var(--color-primary)] dark:text-[var(--color-primary)]' : 'text-slate-400 group-hover:text-[var(--color-primary)] dark:group-hover:text-[var(--color-primary)]' }}"
                    aria-hidden="true"></i>
                <span x-show="!$wire.sidebarCollapsed || sidebarHovered || mobileMenuOpen" x-cloak class="nav-text">Human
                    Resources</span>
            </a>
        @endif

        @if ($canStats)
            <a href="{{ route('stats.index') }}" wire:navigate title="Statistics"
                class="group flex items-center min-h-[44px] gap-x-3 rounded-md py-2 text-sm font-semibold mb-1 transition-all duration-200 overflow-hidden
                                                                       {{ Route::is('stats.*')
                    ? 'border-l-[3px] border-[var(--color-primary)] bg-[var(--color-primary)]/10 text-[var(--color-primary)] dark:bg-[var(--color-primary)]/20 dark:text-[var(--color-primary)] pl-2.25'
                    : 'border-l-[3px] border-transparent text-[var(--color-text-main)] hover:text-[var(--color-primary)] hover:bg-[var(--color-surface-hover)] dark:text-[var(--color-text-main-dark)] dark:hover:text-[var(--color-primary)] dark:hover:bg-[var(--color-surface-hover-dark)] pl-2.25' }}"
                :class="$wire.sidebarCollapsed && !sidebarHovered ? 'justify-center px-2' : ''" {{ Route::is('stats.*') ? 'aria-current=page' : '' }}>
                <i class="fas fa-chart-bar shrink-0 w-5 text-center text-base
                                                                          {{ Route::is('stats.*') ? 'text-[var(--color-primary)] dark:text-[var(--color-primary)]' : 'text-slate-400 group-hover:text-[var(--color-primary)] dark:group-hover:text-[var(--color-primary)]' }}"
                    aria-hidden="true"></i>
                <span x-show="!$wire.sidebarCollapsed || sidebarHovered || mobileMenuOpen" x-cloak
                    class="nav-text">Statistics</span>
            </a>
        @endif
    @endif

    {{-- ── Collapse / Expand Button (desktop only) ─────────────────────── --}}
    <button type="button"
        class="hidden lg:flex items-center min-h-[44px] justify-center w-full py-2 mt-4 rounded-md text-[var(--text-muted)] hover:text-[var(--tenant-primary)] hover:bg-[var(--slate-100)] dark:hover:bg-white/5 transition-colors text-xs"
        @click="$wire.toggleSidebar()" :aria-label="$wire.sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
        <x-ts-icon :name="$sidebarCollapsed ? 'chevron-right' : 'chevron-left'" class="h-4 w-4" />
        <span x-show="!$wire.sidebarCollapsed || sidebarHovered || mobileMenuOpen" x-cloak
            class="ml-2 text-xs font-medium">Collapse</span>
    </button>

</nav>

{{-- ── Sidebar Footer (user info) ───────────────────────────────────── --}}
<div class="sidebar-footer mt-auto border-t border-[var(--panel-border)] pt-4 pb-2 overflow-hidden">
    <div class="flex items-center gap-x-3"
        :class="($wire.sidebarCollapsed && !sidebarHovered && !mobileMenuOpen) ? 'justify-center px-0' : 'px-2'">
        <div
            class="w-8 h-8 rounded-full bg-[var(--tenant-primary)] text-white flex items-center justify-center text-xs font-bold shrink-0 shadow-sm">
            {{ strtoupper(substr(Auth::user()?->name ?? 'U', 0, 1)) }}
        </div>
        <div x-show="!$wire.sidebarCollapsed || sidebarHovered || mobileMenuOpen" x-cloak
            class="flex-1 min-w-0 overflow-hidden">
            <div title="{{ Auth::user()?->name }}" class="text-sm font-bold text-[var(--text-main)] truncate">
                {{ Auth::user()?->name }}
            </div>
            <div class="text-xs text-[var(--text-muted)] truncate">
                {{ Auth::user()?->email }}
            </div>
        </div>
    </div>
</div>