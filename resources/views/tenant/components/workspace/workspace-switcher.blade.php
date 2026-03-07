{{--
╔══════════════════════════════════════════════════════════════════════╗
║ GW-STYLE WORKSPACE SWITCHER — Enterprise SaaS Edition ║
║ Trigger: 3×3 waffle icon with active workspace indicator dot ║
║ Panel: Floating card — current workspace, central portal link, ║
║ scrollable tenant list with keyboard arrow-key navigation ║
╚══════════════════════════════════════════════════════════════════════╝
--}}
<div x-data="{
        wsOpen: false,
        focusedIndex: -1,
        items: [],
        init() {
            this.$nextTick(() => {
                this.items = Array.from(
                    this.$el.querySelectorAll('[data-ws-item]')
                );
            });
        },
        focusItem(i) {
            const el = this.items[i];
            if (el) { el.focus(); this.focusedIndex = i; }
        },
        onKeyNav(e) {
            if (!this.wsOpen) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                const next = Math.min(this.focusedIndex + 1, this.items.length - 1);
                this.focusItem(next);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const prev = Math.max(this.focusedIndex - 1, 0);
                this.focusItem(prev);
            }
        }
     }" class="relative" @keydown.escape="wsOpen = false" @keydown.window="onKeyNav($event)">

    @if($currentTenant)

        {{-- ── Waffle trigger button ──────────────────────────────────── --}}
        <div class="relative">
            <button type="button"
                @click="wsOpen = !wsOpen; $nextTick(() => { items = Array.from($el.parentElement.parentElement.querySelectorAll('[data-ws-item]')); focusedIndex = -1; })"
                :aria-expanded="wsOpen.toString()" aria-haspopup="true"
                aria-label="Switch workspace — {{ $currentTenant->organization_name }}" class="gw-header-action-btn flex items-center justify-center w-10 h-10 rounded-full
                                   text-[var(--text-muted)]
                                   hover:bg-[var(--gw-surface-hover)]
                                   transition-colors focus:outline-none
                                   focus-visible:ring-2 focus-visible:ring-[var(--gw-blue-600)]">
                {{-- 3×3 GW Waffle SVG --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 192 192" aria-hidden="true"
                    focusable="false" fill="currentColor">
                    <rect x="0" y="0" width="56" height="56" rx="10" />
                    <rect x="68" y="0" width="56" height="56" rx="10" />
                    <rect x="136" y="0" width="56" height="56" rx="10" />
                    <rect x="0" y="68" width="56" height="56" rx="10" />
                    <rect x="68" y="68" width="56" height="56" rx="10" />
                    <rect x="136" y="68" width="56" height="56" rx="10" />
                    <rect x="0" y="136" width="56" height="56" rx="10" />
                    <rect x="68" y="136" width="56" height="56" rx="10" />
                    <rect x="136" y="136" width="56" height="56" rx="10" />
                </svg>
            </button>

            {{-- Active workspace indicator dot --}}
            <span class="absolute bottom-1 right-1 block w-2 h-2 rounded-full
                                 bg-[var(--gw-blue-600)] dark:bg-[var(--gw-blue-400)]
                                 ring-2 ring-[var(--panel-bg)]" aria-hidden="true"
                title="You are in: {{ $currentTenant->organization_name }}">
            </span>
        </div>

        {{-- ── Workspace panel ─────────────────────────────────────────── --}}
        <div x-show="wsOpen" @click.outside="wsOpen = false" x-cloak role="dialog" aria-label="Workspace switcher"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95 translate-y-1"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95 translate-y-1" class="absolute top-[calc(100%+12px)] right-0 w-80
                            bg-[var(--panel-bg)] border border-[var(--panel-border)]
                            rounded-[var(--radius-lg)] shadow-[var(--elevation-3)]
                            overflow-hidden z-[var(--z-popover)]">

            {{-- Panel header — current workspace --}}
            <div class="px-4 py-3 border-b border-[var(--panel-border)] flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[0.65rem] font-bold tracking-widest uppercase text-[var(--text-ghost)]">
                        Current Workspace
                    </p>
                    <p class="text-sm font-semibold text-[var(--text-main)] mt-0.5 truncate">
                        {{ $currentTenant->organization_name }}
                    </p>
                </div>
                @php
                    $chipGc = $wsTypeColors[$currentTenant->tenant_type?->value ?? ''] ?? ['#7c3aed', '#6d28d9'];
                    $words = array_values(array_filter(explode(' ', $currentTenant->organization_name)));
                    $chipInitials = strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1));
                @endphp
                <div class="w-10 h-10 rounded-[var(--radius-sm)] shrink-0 flex items-center
                                    justify-center text-white text-sm font-extrabold shadow-sm"
                    style="background: linear-gradient(135deg, {{ $chipGc[0] }}, {{ $chipGc[1] }});" aria-hidden="true">
                    {{ $chipInitials }}
                </div>
            </div>

            {{-- Central portal link --}}
            <a href="{{ route('portal.discover') }}" data-ws-item class="flex items-center gap-3 px-4 py-3 no-underline
                              hover:bg-[var(--gw-surface-hover)] transition-colors
                              border-b border-[var(--panel-border)]
                              focus-visible:outline-none focus-visible:bg-[var(--gw-surface-hover)]" role="menuitem">
                <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0
                                    bg-[var(--tenant-primary-muted)] text-[var(--tenant-primary)]">
                    <i class="fas fa-th-large text-sm" aria-hidden="true"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-[var(--tenant-primary)] leading-tight">
                        Central Portal
                    </p>
                    <p class="text-xs text-[var(--text-muted)] mt-0.5">Management dashboard</p>
                </div>
                <i class="fas fa-arrow-right text-[0.65rem] text-[var(--text-ghost)] shrink-0" aria-hidden="true"></i>
            </a>

            {{-- Tenant list --}}
            @if($availableTenants->isNotEmpty())
                <div class="px-4 py-2 bg-[var(--gw-surface-2)]">
                    <p class="text-[0.65rem] font-bold tracking-widest uppercase text-[var(--text-ghost)]">
                        Switch Workspace
                    </p>
                </div>

                <div class="max-h-60 overflow-y-auto divide-y divide-[var(--panel-border)]" role="listbox"
                    aria-label="Available workspaces">
                    @foreach($availableTenants as $t)
                        @php
                            $wsWords = array_values(array_filter(explode(' ', $t->organization_name)));
                            $wsInit = strtoupper(substr($wsWords[0] ?? '', 0, 1) . substr($wsWords[1] ?? '', 0, 1));
                            $wsColors = $wsTypeColors[$t->tenant_type?->value ?? ''] ?? ['#7c3aed', '#6d28d9'];
                            $switchUrl = route('switch.workspace', $t->id);
                            $isCurrent = $t->id === $currentTenant->id;
                        @endphp
                        <a href="{{ $switchUrl }}" data-ws-item class="group flex items-center gap-3 px-4 py-2.5 no-underline
                                                          transition-colors
                                                          focus-visible:outline-none
                                                          {{ $isCurrent
                            ? 'bg-[var(--gw-surface-active)]'
                            : 'hover:bg-[var(--gw-surface-hover)] focus-visible:bg-[var(--gw-surface-hover)]' }}"
                            role="option" aria-selected="{{ $isCurrent ? 'true' : 'false' }}"
                            aria-label="Switch to {{ $t->organization_name }}" @if($isCurrent) aria-current="true" @endif>

                            <div class="w-8 h-8 rounded-[var(--radius-sm)] shrink-0 flex items-center justify-center
                                                                text-white text-[0.6rem] font-extrabold shadow-sm
                                                                transition-transform group-hover:scale-105"
                                style="background: linear-gradient(135deg, {{ $wsColors[0] }}, {{ $wsColors[1] }});"
                                aria-hidden="true">
                                {{ $wsInit }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <p
                                    class="text-sm font-medium truncate transition-colors
                                                                  {{ $isCurrent
                            ? 'text-[var(--gw-blue-600)] dark:text-[var(--gw-blue-400)] font-semibold'
                            : 'text-[var(--text-main)] group-hover:text-[var(--gw-blue-600)] dark:group-hover:text-[var(--gw-blue-400)]' }}">
                                    {{ $t->organization_name }}
                                </p>
                                <p class="text-xs text-[var(--text-ghost)] truncate mt-0.5">
                                    {{ $t->domains->first()?->domain ?? 'No domain' }}
                                </p>
                            </div>

                            @if($isCurrent)
                                <i class="fas fa-check text-xs text-[var(--gw-blue-600)] dark:text-[var(--gw-blue-400)] shrink-0"
                                    aria-label="Current workspace" aria-hidden="true"></i>
                            @else
                                <i class="fas fa-arrow-right text-[0.65rem] text-[var(--text-ghost)]
                                                                          opacity-0 group-hover:opacity-100 transition-opacity shrink-0"
                                    aria-hidden="true"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>