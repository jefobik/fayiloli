{{--
╔═══════════════════════════════════════════════════════════════════════╗
║ GLOBAL SEARCH — Command Palette v3.0 ║
║ Inspired by: Raycast · Linear · Vercel dashboard search ║
║ ║
║ UX: ║
║ • Triggered by ⌘K / Ctrl+K from anywhere via window event ║
║ • Full-screen backdrop blur + dark scrim ║
║ • Centred 640px modal, 480px max-height ║
║ • 3 sections: Documents · Users (admin) · Quick Actions ║
║ • ↑ ↓ arrow key cursor, Enter to navigate, Escape to close ║
║ • Result rows: icon · label · sub · type badge ║
║ • Smooth scale+fade animation via Alpine x-transition ║
║ • Zero results state + empty query action list ║
╚═══════════════════════════════════════════════════════════════════════╝
--}}

<div x-data="{
        /*
         * Alpine local state mirrors Livewire $wire.open
         * We keep a local `cursor` to track keyboard highlight without
         * triggering a Livewire round-trip on every arrow press.
         */
        cursor: 0,

        get flatResults() {
            const sections = [
                ...($wire.results.documents ?? []),
                ...($wire.results.users     ?? []),
                ...($wire.results.actions   ?? []),
            ];
            return sections;
        },

        moveCursor(dir) {
            const len = this.flatResults.length;
            if (!len) return;
            this.cursor = (this.cursor + dir + len) % len;
            // Scroll result into view
            this.$nextTick(() => {
                const el = this.$el.querySelector('[data-cursor-idx=\'' + this.cursor + '\']');
                el?.scrollIntoView({ block: 'nearest' });
            });
        },

        confirmCursor() {
            const item = this.flatResults[this.cursor];
            if (!item) return;
            if (item.js) { eval(item.js); $wire.closePalette(); return; }
            $wire.selectResult(item.url, item.folder_id ?? null);
        },
    }" x-init="
        /* Listen for ⌘K / Ctrl+K global hotkey */
        window.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                if ($wire.open) { $wire.closePalette(); }
                else { $wire.openPalette(); $wire.executeSearch(); }
            }
        });

        /* Also listen for custom event from topbar */
        window.addEventListener('search-focus', () => {
            if (!$wire.open) { $wire.openPalette(); $wire.executeSearch(); }
            $nextTick(() => { $el.querySelector('#gw-search-input')?.focus(); });
        });
    ">

    {{-- ══════════════════════════════════════════════════════════════════
    PALETTE OVERLAY
    ════════════════════════════════════════════════════════════════════ --}}
    <template x-if="$wire.open">
        <div class="fixed inset-0 z-[var(--z-modal,2000)] flex items-start justify-center pt-[10vh] px-4" role="dialog"
            aria-modal="true" aria-label="Command palette — global search" @keydown.escape.window="$wire.closePalette()"
            @keydown.arrow-down.prevent="moveCursor(1)" @keydown.arrow-up.prevent="moveCursor(-1)"
            @keydown.enter.prevent="confirmCursor()">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-[var(--text-main)]/40 backdrop-blur-[2px]" @click="$wire.closePalette()"
                aria-hidden="true" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">
            </div>

            {{-- Panel --}}
            <div class="relative w-full max-w-[640px] bg-[var(--panel-bg)]
                        rounded-[var(--radius-lg)]
                        border border-[var(--panel-border)]
                        shadow-[var(--elevation-3)]
                        overflow-hidden
                        flex flex-col
                        max-h-[min(480px,80vh)]" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 -translate-y-2" @click.stop>

                {{-- ── Search input row ── --}}
                <div class="flex items-center gap-3 px-4 border-b border-[var(--divider)] shrink-0">

                    {{-- Leading icon --}}
                    <i class="fas fa-magnifying-glass text-[var(--text-ghost)] text-[0.9rem] shrink-0"
                        aria-hidden="true"></i>

                    {{-- Input --}}
                    <input id="gw-search-input" type="search" autocomplete="off" spellcheck="false"
                        wire:model.live.debounce.200ms="query" placeholder="Search documents, users, actions…" class="flex-1 h-14 bg-transparent border-none outline-none
                                  text-[var(--text-main)] text-[0.9375rem] font-normal
                                  placeholder:text-[var(--text-ghost)]" x-init="$nextTick(() => $el.focus())"
                        aria-label="Search" aria-autocomplete="list" aria-controls="gw-search-results"
                        :aria-activedescendant="'gw-result-' + cursor">

                    {{-- Escape hint --}}
                    <kbd class="shrink-0 hidden sm:inline-flex items-center
                                h-5 px-1.5 rounded
                                text-[0.6rem] font-semibold tracking-wider
                                text-[var(--text-ghost)]
                                border border-[var(--divider)]
                                bg-[var(--gw-surface-2)]">
                        ESC
                    </kbd>
                </div>

                {{-- ── Results body ── --}}
                <div id="gw-search-results" class="flex-1 min-h-0 overflow-y-auto py-1.5" role="listbox"
                    aria-label="Search results">

                    @php
                        $docs = $results['documents'] ?? [];
                        $users = $results['users'] ?? [];
                        $actions = $results['actions'] ?? [];
                        $total = count($docs) + count($users) + count($actions);
                        $offset = 0;   // running cursor index across sections
                    @endphp

                    {{-- ── DOCUMENTS / FOLDERS / TAGS ── --}}
                    @if (count($docs) > 0)
                        <div class="px-3 pt-2 pb-1">
                            <p class="gw-palette-section-label">Documents & Folders</p>
                        </div>
                        @foreach ($docs as $i => $item)
                            @php $idx = $offset + $i; @endphp
                            <button type="button" data-cursor-idx="{{ $idx }}" id="gw-result-{{ $idx }}" class="gw-palette-row group w-full flex items-center gap-3 px-4 py-2.5
                                                   text-left no-underline transition-colors duration-100
                                                   focus:outline-none" :class="{{ $idx }} === cursor
                                                ? 'bg-[var(--gw-surface-active)]'
                                                : 'hover:bg-[var(--gw-surface-hover)]'" @mouseenter="cursor = {{ $idx }}"
                                @click="$wire.selectResult('{{ $item['url'] }}', {{ $item['folder_id'] ?? 'null' }})"
                                role="option" :aria-selected="{{ $idx }} === cursor ? 'true' : 'false'">

                                {{-- Icon --}}
                                <div class="w-8 h-8 rounded-[var(--radius-xs)] flex items-center justify-center
                                                     shrink-0 bg-[var(--gw-surface-2)]">
                                    <i class="fa-solid {{ $item['icon'] }} text-[0.875rem]
                                                       {{ $item['type'] === 'folder' ? 'text-amber-500' : 'text-[var(--gw-blue-500)]' }}"
                                        aria-hidden="true"></i>
                                </div>

                                {{-- Text --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-[0.8125rem] font-medium text-[var(--text-main)] truncate">
                                        {{ $item['label'] }}
                                    </p>
                                    <p class="text-[0.72rem] text-[var(--text-ghost)] truncate mt-0.5">
                                        {{ $item['sub'] }}
                                    </p>
                                </div>

                                {{-- Type badge --}}
                                @if (!empty($item['badge']))
                                    <span class="shrink-0 inline-flex items-center h-4 px-1.5 rounded
                                                             text-[0.6rem] font-bold tracking-[0.06em] uppercase
                                                             {{ $item['badge_cls'] ?? 'text-slate-600 bg-slate-100' }}">
                                        {{ $item['badge'] }}
                                    </span>
                                @endif

                                {{-- Enter arrow (shown on cursor row) --}}
                                <i class="fas fa-arrow-turn-down text-[0.6rem] text-[var(--text-ghost)] shrink-0 opacity-0
                                                  group-hover:opacity-100 transition-opacity"
                                    :class="{{ $idx }} === cursor ? 'opacity-100' : ''" aria-hidden="true"></i>
                            </button>
                        @endforeach
                        @php $offset += count($docs); @endphp
                    @endif

                    {{-- ── USERS ── --}}
                    @if (count($users) > 0)
                        <div class="px-3 pt-2.5 pb-1">
                            <p class="gw-palette-section-label">People</p>
                        </div>
                        @foreach ($users as $i => $item)
                            @php $idx = $offset + $i; @endphp
                            <button type="button" data-cursor-idx="{{ $idx }}" id="gw-result-{{ $idx }}" class="gw-palette-row group w-full flex items-center gap-3 px-4 py-2.5
                                                   text-left transition-colors duration-100 focus:outline-none" :class="{{ $idx }} === cursor
                                                ? 'bg-[var(--gw-surface-active)]'
                                                : 'hover:bg-[var(--gw-surface-hover)]'" @mouseenter="cursor = {{ $idx }}"
                                @click="$wire.selectResult('{{ $item['url'] }}', null)" role="option"
                                :aria-selected="{{ $idx }} === cursor ? 'true' : 'false'">

                                {{-- Avatar chip --}}
                                <div class="w-8 h-8 rounded-full flex items-center justify-center
                                                     shrink-0 text-white text-[0.6875rem] font-bold"
                                    style="background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-primary-hover));">
                                    {{ $item['init'] }}
                                </div>

                                {{-- Text --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-[0.8125rem] font-medium text-[var(--text-main)] truncate">
                                        {{ $item['label'] }}
                                    </p>
                                    <p class="text-[0.72rem] text-[var(--text-ghost)] truncate mt-0.5">
                                        {{ $item['sub'] }}
                                    </p>
                                </div>

                                <span class="shrink-0 inline-flex items-center h-4 px-1.5 rounded
                                                     text-[0.6rem] font-bold tracking-[0.06em] uppercase
                                                     {{ $item['badge_cls'] ?? 'text-blue-700 bg-blue-50' }}">
                                    {{ $item['badge'] }}
                                </span>

                                <i class="fas fa-arrow-turn-down text-[0.6rem] text-[var(--text-ghost)] shrink-0 opacity-0
                                                  group-hover:opacity-100 transition-opacity"
                                    :class="{{ $idx }} === cursor ? 'opacity-100' : ''" aria-hidden="true"></i>
                            </button>
                        @endforeach
                        @php $offset += count($users); @endphp
                    @endif

                    {{-- ── QUICK ACTIONS ── --}}
                    @if (count($actions) > 0)
                        <div class="px-3 pt-2.5 pb-1">
                            <p class="gw-palette-section-label">Quick Actions</p>
                        </div>
                        @foreach ($actions as $i => $item)
                            @php $idx = $offset + $i; @endphp
                            <button type="button" data-cursor-idx="{{ $idx }}" id="gw-result-{{ $idx }}" class="gw-palette-row group w-full flex items-center gap-3 px-4 py-2.5
                                                   text-left transition-colors duration-100 focus:outline-none" :class="{{ $idx }} === cursor
                                                ? 'bg-[var(--gw-surface-active)]'
                                                : 'hover:bg-[var(--gw-surface-hover)]'" @mouseenter="cursor = {{ $idx }}"
                                @click="@if(!empty($item['js'])) {{ $item['js'] }}; $wire.closePalette(); @else $wire.selectResult('{{ $item['url'] }}', null) @endif"
                                role="option" :aria-selected="{{ $idx }} === cursor ? 'true' : 'false'">

                                <div class="w-8 h-8 rounded-[var(--radius-xs)] flex items-center justify-center
                                                     shrink-0 bg-[var(--gw-surface-2)]">
                                    <i class="fa-solid {{ $item['icon'] }} text-[0.875rem] text-[var(--text-muted)]"
                                        aria-hidden="true"></i>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="text-[0.8125rem] font-medium text-[var(--text-main)] truncate">
                                        {{ $item['label'] }}
                                    </p>
                                    <p class="text-[0.72rem] text-[var(--text-ghost)] truncate mt-0.5">
                                        {{ $item['sub'] }}
                                    </p>
                                </div>

                                <span class="shrink-0 inline-flex items-center h-4 px-1.5 rounded
                                                     text-[0.6rem] font-bold tracking-[0.06em] uppercase
                                                     {{ $item['badge_cls'] ?? 'text-slate-600 bg-slate-100' }}">
                                    {{ $item['badge'] }}
                                </span>

                                <i class="fas fa-arrow-turn-down text-[0.6rem] text-[var(--text-ghost)] shrink-0 opacity-0
                                                  group-hover:opacity-100 transition-opacity"
                                    :class="{{ $idx }} === cursor ? 'opacity-100' : ''" aria-hidden="true"></i>
                            </button>
                        @endforeach
                    @endif

                    {{-- ── Zero state: query typed but no results ── --}}
                    @if ($total === 0 && strlen($query) >= 2)
                        <div class="flex flex-col items-center justify-center py-10 gap-2
                                        text-[var(--text-ghost)]">
                            <i class="fas fa-magnifying-glass text-2xl opacity-30" aria-hidden="true"></i>
                            <p class="text-[0.8125rem] font-medium mt-1">No results for
                                <span class="text-[var(--text-main)]">"{{ $query }}"</span>
                            </p>
                            <p class="text-[0.75rem]">Try a different keyword or check spelling</p>
                        </div>
                    @endif
                </div>

                {{-- ── Footer: keyboard hint ── --}}
                <div class="shrink-0 flex items-center gap-4 px-4 py-2
                             border-t border-[var(--divider)]
                             bg-[var(--gw-surface-2)]">
                    <span class="flex items-center gap-1.5 text-[0.7rem] text-[var(--text-ghost)]">
                        <kbd class="gw-palette-kbd">↑↓</kbd>
                        <span>Navigate</span>
                    </span>
                    <span class="flex items-center gap-1.5 text-[0.7rem] text-[var(--text-ghost)]">
                        <kbd class="gw-palette-kbd">↵</kbd>
                        <span>Open</span>
                    </span>
                    <span class="flex items-center gap-1.5 text-[0.7rem] text-[var(--text-ghost)]">
                        <kbd class="gw-palette-kbd">ESC</kbd>
                        <span>Close</span>
                    </span>
                    <span class="ml-auto text-[0.7rem] text-[var(--text-ghost)]">
                        @php $tn = tenancy()->tenant ?? null; @endphp
                        @if ($tn)
                            <i class="fas fa-building-columns mr-1 text-[0.6rem]" aria-hidden="true"></i>
                            {{ $tn->short_name ?? $tn->organization_name ?? 'Workspace' }}
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </template>
</div>