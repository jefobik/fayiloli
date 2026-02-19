{{-- ── Sidebar Logo ─────────────────────────────────────────────────── --}}
<div class="sidebar-logo">
    <a href="{{ route('home') }}" style="display:flex;align-items:center;text-decoration:none">
        <svg style="width:26px;height:26px;flex-shrink:0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
            <path fill="#7c3aed" d="M512 256a15 15 0 00-7.1-12.8l-52-32 52-32.5a15 15 0 000-25.4L264 2.3c-4.8-3-11-3-15.9 0L7 153.3a15 15 0 000 25.4L58.9 211 7.1 243.3a15 15 0 000 25.4L58.8 301 7.1 333.3a15 15 0 000 25.4l241 151a15 15 0 0015.9 0l241-151a15 15 0 00-.1-25.5l-52-32 52-32.5A15 15 0 00512 256z"/>
        </svg>
        <span class="brand">Fayiloli</span>
        <span class="version">v2.9</span>
    </a>
</div>

{{-- ── Main Navigation ──────────────────────────────────────────────── --}}
<nav class="sidebar-nav">
    <div class="sidebar-section-label">Main</div>

    <a href="{{ route('home') }}"
       class="sidebar-link {{ Route::is('home') ? 'active' : '' }}">
        <i class="fas fa-home"></i> Dashboard
    </a>

    <a href="{{ route('documents.index') }}"
       class="sidebar-link {{ Route::is('documents.index') ? 'active' : '' }}">
        <i class="fas fa-file-alt"></i> Documents
        <span class="badge" id="sidebar-doc-count"></span>
    </a>

    <a href="{{ route('tags.index') }}"
       class="sidebar-link {{ Route::is('tags.*') ? 'active' : '' }}">
        <i class="fas fa-tags"></i> Tags
    </a>

    {{-- ── Workspaces / Folder Tree ──────────────────────────────────── --}}
    @if (Route::is('documents.index'))
        <div class="sidebar-section-label" style="margin-top:0.75rem">Workspaces</div>
        <ul class="folders">
            @if (isset($folders))
                {!! $folders !!}
            @else
                {!! generateSidebarMenu() !!}
            @endif
        </ul>
        <div id="renderFolderTagsHtml"></div>
    @endif

    <div class="sidebar-section-label" style="margin-top:0.75rem">Modules</div>

    <a href="{{ route('projects.index') }}"
       class="sidebar-link {{ Route::is('projects.*') ? 'active' : '' }}">
        <i class="fas fa-project-diagram"></i> Projects
    </a>

    <a href="{{ route('contacts.index') }}"
       class="sidebar-link {{ Route::is('contacts.*') ? 'active' : '' }}">
        <i class="fas fa-address-book"></i> Contacts
    </a>
</nav>

{{-- ── Sidebar Footer ───────────────────────────────────────────────── --}}
<div class="sidebar-footer">
    <div style="display:flex;align-items:center;gap:0.6rem">
        <div class="avatar" style="width:28px;height:28px;font-size:0.7rem;background:linear-gradient(135deg,#4f46e5,#7c3aed)">
            {{ strtoupper(substr(Auth::user()?->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(explode(' ', Auth::user()?->name ?? 'U ')[1] ?? '', 0, 1)) }}
        </div>
        <div style="flex:1;min-width:0;overflow:hidden">
            <div style="font-size:0.78rem;font-weight:600;color:#cbd5e1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
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
</style>

{{-- ── Copyright Footer ──────────────────────────────────────────── --}}
<div style="border-top:1px solid rgba(255,255,255,0.05);padding:0.5rem 1.25rem 0.7rem;flex-shrink:0;margin-top:auto">
    <div style="font-size:0.62rem;color:#334155;line-height:1.5;text-align:center">
        &copy; {{ date('Y') }} EDMS Platform<br>
        <span style="opacity:0.6">Laravel {{ app()->version() }} · v12</span>
    </div>
</div>
