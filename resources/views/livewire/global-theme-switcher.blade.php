{{--
╔══════════════════════════════════════════════════════════════════════╗
║ GLOBAL THEME SWITCHER                                                ║
║ x-data delegates entirely to the globally registered Alpine          ║
║ themeManager (resources/js/theme-manager.js).                        ║
║ NO local Alpine.data() registration — that caused the collision.     ║
║                                                                      ║
║ Wire flow:                                                            ║
║   User clicks button → wire:click="updateTheme()" → PHP persist      ║
║   → Livewire dispatch('theme-updated') → window event                ║
║   → themeManager.setupEventListeners() picks up → applyTheme()        ║
║                                                                      ║
║ Keyboard flow (Ctrl+Shift+T):                                        ║
║   theme-manager.js cycleTheme() → dispatch cycle-theme window event  ║
║   → @cycle-theme.window on this div → $wire.updateTheme()            ║
╚══════════════════════════════════════════════════════════════════════╝
--}}
<div class="t-switcher-wrapper"
     x-data="themeManager()"
     x-init="init()"
     @theme-updated.window="currentTheme = $event.detail.theme; applyTheme($event.detail.theme)"
     @cycle-theme.window="$wire.updateTheme($event.detail.nextTheme)">

    <div class="flex flex-col gap-2 p-3 w-full max-w-xs">

        {{-- ── Header ───────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between px-1">
            <label class="text-[0.65rem] font-bold text-[var(--text-ghost)] uppercase tracking-widest select-none">
                <i class="fas fa-palette text-sm mr-1.5 opacity-75"></i> Appearance
            </label>
            <button
                type="button"
                wire:click="resetToSystem"
                class="text-[0.65rem] font-semibold
                       text-[var(--gw-blue-600)] hover:text-[var(--gw-blue-700)]
                       dark:text-[var(--gw-blue-400)] dark:hover:text-[var(--gw-blue-300)]
                       transition-colors duration-150
                       focus-visible:outline-none focus-visible:ring-2
                       focus-visible:ring-[var(--gw-blue-600)] rounded"
                title="Reset to device preference"
                aria-label="Reset theme to system (device) preference">
                Reset
            </button>
        </div>

        {{-- ── Theme option grid ────────────────────────────────────────── --}}
        <div class="grid grid-cols-3 gap-2 p-2
                    bg-[var(--gw-surface-2)]
                    rounded-[var(--radius-md)]
                    border border-[var(--panel-border)]
                    transition-colors duration-150"
             role="group" aria-label="Theme selection">

            @foreach($availableThemes as $themeKey => $themeData)
                <button
                    type="button"
                    wire:click="updateTheme('{{ $themeKey }}')"
                    wire:loading.attr="disabled"
                    wire:target="updateTheme,resetToSystem"
                    class="theme-btn flex flex-col items-center justify-center gap-1.5
                           px-2.5 py-3 rounded-[var(--radius-sm)]
                           text-[0.7rem] font-semibold
                           transition-all duration-150
                           focus-visible:outline-none focus-visible:ring-2
                           focus-visible:ring-[var(--tenant-primary)] focus-visible:ring-offset-1
                           disabled:opacity-50 disabled:cursor-not-allowed
                           {{ $themeData['isActive']
                               ? 'bg-[var(--panel-bg)] text-[var(--tenant-primary)] shadow-[var(--elevation-1)] ring-2 ring-[var(--tenant-primary)]/30'
                               : 'text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-[var(--gw-surface-hover)]' }}"
                    :class="{
                        'bg-[var(--panel-bg)] text-[var(--tenant-primary)] shadow ring-2 ring-[var(--tenant-primary)]/30': currentTheme === '{{ $themeKey }}',
                        'text-[var(--text-muted)] hover:text-[var(--text-main)]': currentTheme !== '{{ $themeKey }}'
                    }"
                    aria-pressed="{{ $themeData['isActive'] ? 'true' : 'false' }}"
                    :aria-pressed="currentTheme === '{{ $themeKey }}' ? 'true' : 'false'"
                    title="{{ $themeData['label'] }} theme">

                    <i class="fas text-base {{ $themeData['icon'] }}"
                       :class="{ 'scale-110': currentTheme === '{{ $themeKey }}' }"
                       aria-hidden="true"></i>

                    <span class="leading-tight">{{ $themeData['label'] }}</span>
                </button>
            @endforeach

        </div>

        {{-- ── Contextual description ────────────────────────────────────── --}}
        <p class="px-1 text-[0.65rem] text-[var(--text-ghost)] italic leading-relaxed"
           aria-live="polite" aria-atomic="true">
            <span x-text="
                currentTheme === 'light'  ? 'Light theme for daytime viewing' :
                currentTheme === 'dark'   ? 'Dark theme for reduced eye strain' :
                                            'Follows your device colour scheme'
            "></span>
        </p>

        {{-- ── Keyboard shortcut hint ────────────────────────────────────── --}}
        <p class="px-1 text-[0.6rem] text-[var(--text-ghost)] flex items-center gap-1">
            <i class="fas fa-keyboard opacity-60" aria-hidden="true"></i>
            <span>Cycle themes:</span>
            <kbd class="px-1.5 py-0.5
                        bg-[var(--gw-surface-2)] border border-[var(--panel-border)]
                        rounded-[var(--radius-xs)]
                        text-[var(--text-muted)] font-mono text-[0.6rem]">Ctrl+Shift+T</kbd>
        </p>

        {{-- ── Livewire loading state ────────────────────────────────────── --}}
        <div wire:loading.flex wire:target="updateTheme,resetToSystem"
             class="hidden items-center justify-center gap-2 py-1
                    text-[0.7rem] text-[var(--text-muted)]">
            <span class="theme-switcher-spinner" aria-hidden="true"></span>
            <span>Saving…</span>
        </div>

    </div>

    {{--
        NO @script block here.
        themeManager is registered globally in resources/js/theme-manager.js.
        A local Alpine.data('themeManager', ...) inside @script would overwrite the
        global registration and kill keyboard shortcuts, system-preference listener,
        and localStorage persistence — the exact regression this refactor fixes.
    --}}

    <style>
    .t-switcher-wrapper {
        position: relative;
        z-index: 10;
    }

    .theme-btn {
        position: relative;
        cursor: pointer;
        user-select: none;
    }

    .theme-btn:focus-visible {
        outline: 2px solid var(--tenant-primary);
        outline-offset: 2px;
    }

    /* Subtle icon scale pulse when active */
    .theme-btn i {
        transition: transform 150ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Livewire loading spinner */
    .theme-switcher-spinner {
        display: inline-block;
        width: 0.875rem;
        height: 0.875rem;
        border: 2px solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: ts-spin 500ms linear infinite;
    }

    @keyframes ts-spin {
        to { transform: rotate(360deg); }
    }

    kbd {
        font-family: ui-monospace, 'Cascadia Code', 'Source Code Pro', monospace;
    }
    </style>
</div>
