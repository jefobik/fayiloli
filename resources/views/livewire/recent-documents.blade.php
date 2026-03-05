{{--
╔══════════════════════════════════════════════════════════════════════╗
║ recent-documents.blade.php ║
║ Real-time activity card — two tabs: Recent Files | Activity Log ║
╚══════════════════════════════════════════════════════════════════════╝
--}}
<div wire:poll.30s class="gw-card rounded-[var(--radius-lg)] bg-[var(--panel-bg)]
                          border border-[var(--panel-border)] shadow-[var(--elevation-1)]
                          overflow-hidden" id="recent-documents-panel">

    {{-- Card header + tabs --}}
    <div class="flex items-center justify-between px-5 pt-4 pb-0 border-b border-[var(--panel-border)]">
        <div class="flex items-center gap-2">
            <h2 class="text-sm font-semibold text-[var(--text-main)]">Recent Activity</h2>
            @if($refreshing)
                <span
                    class="inline-block w-3 h-3 rounded-full border-2 border-[var(--gw-blue-600)] border-t-transparent animate-spin"
                    aria-label="Refreshing"></span>
            @else
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" aria-label="Live"
                    title="Live updates every 30 s"></span>
            @endif
        </div>

        {{-- Tab switcher --}}
        <div class="flex items-center gap-1 mb-[-1px]" role="tablist">
            <button type="button" wire:click="$set('tab', 'files')" role="tab" :aria-selected="'{{ $tab }}' === 'files'"
                class="px-3 py-2.5 text-xs font-semibold border-b-2 transition-colors
                       {{ $tab === 'files'
    ? 'border-[var(--gw-blue-600)] text-[var(--gw-blue-600)] dark:border-[var(--gw-blue-400)] dark:text-[var(--gw-blue-400)]'
    : 'border-transparent text-[var(--text-muted)] hover:text-[var(--text-main)] hover:border-[var(--panel-border)]' }}">
                <i class="fas fa-folder-open mr-1.5" aria-hidden="true"></i>
                Recent Files
            </button>
            <button type="button" wire:click="$set('tab', 'activity')" role="tab"
                :aria-selected="'{{ $tab }}' === 'activity'"
                class="px-3 py-2.5 text-xs font-semibold border-b-2 transition-colors
                       {{ $tab === 'activity'
    ? 'border-[var(--gw-blue-600)] text-[var(--gw-blue-600)] dark:border-[var(--gw-blue-400)] dark:text-[var(--gw-blue-400)]'
    : 'border-transparent text-[var(--text-muted)] hover:text-[var(--text-main)] hover:border-[var(--panel-border)]' }}">
                <i class="fas fa-clock-rotate-left mr-1.5" aria-hidden="true"></i>
                Activity Log
            </button>
        </div>
    </div>

    {{-- ── TAB: Recent Files ─────────────────────────────────────────── --}}
    @if($tab === 'files')
        @if($recentFiles->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center px-6">
                <div class="w-12 h-12 rounded-full bg-[var(--gw-surface-2)] flex items-center justify-center mb-3">
                    <i class="fas fa-folder-open text-xl text-[var(--text-ghost)]" aria-hidden="true"></i>
                </div>
                <p class="text-sm font-semibold text-[var(--text-main)]">No documents yet</p>
                <p class="text-xs text-[var(--text-muted)] mt-1">Upload your first document to get started.</p>
            </div>
        @else
            <ul role="list" class="divide-y divide-[var(--panel-border)]">
                @foreach($recentFiles as $doc)
                    @php [$iconClass, $iconColor] = $this->iconFor($doc->extension); @endphp
                    <li class="group flex items-center gap-3 px-5 py-2.5
                                            hover:bg-[var(--gw-surface-hover)] transition-colors">

                        {{-- File icon --}}
                        <div class="w-8 h-8 shrink-0 rounded-[var(--radius-sm)]
                                                flex items-center justify-center
                                                bg-[var(--gw-surface-2)]" aria-hidden="true">
                            <i class="fa-solid {{ $iconClass }} {{ $iconColor }} text-sm"></i>
                        </div>

                        {{-- Name + folder --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-[var(--text-main)] truncate
                                                   group-hover:text-[var(--gw-blue-600)] dark:group-hover:text-[var(--gw-blue-400)]
                                                   transition-colors">
                                {{ $doc->name }}
                            </p>
                            <p class="text-xs text-[var(--text-muted)] truncate mt-0.5">
                                @if($doc->folder)
                                    <i class="fas fa-folder text-[0.6rem] mr-1 text-amber-400" aria-hidden="true"></i>
                                    {{ $doc->folder->name }}
                                    <span class="mx-1 text-[var(--text-ghost)]">·</span>
                                @endif
                                {{ $doc->updated_at?->diffForHumans() }}
                            </p>
                        </div>

                        {{-- Owner initials --}}
                        @if($doc->ownerUser)
                            <div class="w-6 h-6 rounded-full flex items-center justify-center
                                                    text-white text-[0.55rem] font-bold shrink-0"
                                style="background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-primary-hover));"
                                title="{{ $doc->ownerUser->name }}" aria-label="{{ $doc->ownerUser->name }}">
                                {{ strtoupper(substr($doc->ownerUser->name, 0, 1)) }}
                            </div>
                        @endif

                        {{-- Extension pill --}}
                        <span class="hidden sm:inline-flex items-center px-1.5 py-0.5
                                                 text-[0.6rem] font-bold uppercase tracking-wide
                                                 rounded bg-[var(--gw-surface-2)] text-[var(--text-ghost)]">
                            {{ strtoupper($doc->extension ?? '—') }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif

    {{-- ── TAB: Activity Log ────────────────────────────────────────── --}}
    @if($tab === 'activity')
        @if($activityLog->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center px-6">
                <div class="w-12 h-12 rounded-full bg-[var(--gw-surface-2)] flex items-center justify-center mb-3">
                    <i class="fas fa-clock text-xl text-[var(--text-ghost)]" aria-hidden="true"></i>
                </div>
                <p class="text-sm font-semibold text-[var(--text-main)]">No activity yet</p>
                <p class="text-xs text-[var(--text-muted)] mt-1">Actions on documents, folders, and users will appear here.</p>
            </div>
        @else
            <ul role="list" class="divide-y divide-[var(--panel-border)]">
                @foreach($activityLog as $entry)
                    @php
                        $event = $entry->event ?? $entry->description ?? 'action';
                        $logName = $entry->log_name ?? 'system';
                        $causerName = $entry->causer?->name ?? 'System';
                        $causerInit = strtoupper(substr($causerName, 0, 1));
                        $subjName = null;
                        if ($entry->properties?->has('attributes')) {
                            $subjName = $entry->properties->get('attributes')['name'] ?? null;
                        }
                        $evClass = $this->eventColor($event);
                        $timeAgo = $entry->created_at?->diffForHumans() ?? '—';
                    @endphp
                    <li class="flex items-start gap-3 px-5 py-3 hover:bg-[var(--gw-surface-hover)] transition-colors">

                        {{-- Causer avatar --}}
                        <div class="w-7 h-7 mt-0.5 rounded-full shrink-0 flex items-center justify-center
                                                text-white text-[0.6rem] font-bold"
                            style="background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-primary-hover));"
                            aria-hidden="true">
                            {{ $causerInit }}
                        </div>

                        {{-- Description --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-[var(--text-main)] leading-snug">
                                <span class="font-semibold">{{ $causerName }}</span>
                                <span class="text-[var(--text-muted)]"> {{ $event }}</span>
                                @if($subjName)
                                    <span class="font-medium text-[var(--text-main)]"> "{{ Str::limit($subjName, 32) }}"</span>
                                @endif
                            </p>
                            <div class="flex items-center gap-2 mt-1">
                                {{-- Event badge --}}
                                <span
                                    class="inline-flex items-center px-1.5 py-0.5 rounded text-[0.6rem] font-bold uppercase tracking-wide {{ $evClass }}">
                                    {{ $event }}
                                </span>
                                {{-- Log name chip --}}
                                <span class="text-[0.6rem] font-semibold uppercase tracking-wide text-[var(--text-ghost)]">
                                    {{ $logName }}
                                </span>
                                <span class="text-[0.7rem] text-[var(--text-ghost)] ml-auto">{{ $timeAgo }}</span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif

    {{-- Footer --}}
    <div
        class="px-5 py-2.5 border-t border-[var(--panel-border)] bg-[var(--gw-surface-2)] flex items-center justify-between">
        <p class="text-[0.65rem] text-[var(--text-ghost)]">Auto-refreshes every 30 s</p>
        <button wire:click="refresh" class="text-[0.7rem] font-semibold text-[var(--gw-blue-600)] dark:text-[var(--gw-blue-400)]
                       hover:underline transition-colors" aria-label="Refresh now">
            <i class="fas fa-rotate-right mr-1 text-[0.6rem]" aria-hidden="true"></i>
            Refresh
        </button>
    </div>
</div>