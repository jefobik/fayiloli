<div>
    {{-- Poll trigger: calls refresh() every 60 s. Placed inside (not on) the root
    div — Livewire v4 recommended pattern. Refresh() dispatches 'stats-refreshed'
    so Chart.js can re-draw after each DOM morph. Using .keep-alive prevents
    unnecessary polling when navigating away or dropping connection momentarily. --}}
    <div wire:poll.60s.keep-alive="refresh" style="display:none" aria-hidden="true"></div>

    {{-- ══════════════════════════════════════════════════════════════════════
    EDMS DASHBOARD GRID
    ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 sm:gap-6 lg:gap-8 mt-2 sm:mt-4">

        {{-- ── LEFT COLUMN: MAIN METRICS & ACTIVITY ── --}}
        <div class="xl:col-span-8 space-y-6 lg:space-y-8">

            {{-- ══════════════════════════════════════════════════════════════════════
            KPI CARDS
            ═══════════════════════════════════════════════════════════════════════ --}}
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4 transition-opacity duration-300 relative"
                wire:loading.class="opacity-50 pointer-events-none">

                {{-- ── Skeleton (shown only while Livewire is fetching) ── --}}
                <div wire:loading.flex class="absolute inset-0 z-10 w-full h-full flex flex-wrap gap-4 sm:gap-6"
                    aria-hidden="true" style="display: none;">
                    @for ($i = 0; $i < 6; $i++)
                        <x-ts-card class="flex-1 min-w-[150px] flex flex-col justify-between animate-pulse">
                            <div class="w-12 h-12 rounded-xl bg-slate-200 dark:bg-slate-700 mb-4"></div>
                            <div>
                                <div class="h-8 bg-slate-200 dark:bg-slate-700 rounded-lg w-1/2 mb-2"></div>
                                <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-3/4 mb-4"></div>
                            </div>
                        </x-ts-card>
                    @endfor
                </div>

                {{-- ── Real KPI cards (hidden while loading) ── --}}
                <div wire:loading.remove>
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-900/30">
                                <i class="fas fa-file-alt text-violet-600 dark:text-violet-400 text-base" aria-hidden="true"></i>
                            </div>
                            <div class="flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                <i class="fas fa-arrow-trend-up text-[0.65rem]" aria-hidden="true"></i>
                                <span>All time</span>
                            </div>
                        </div>
                        <div class="text-2xl font-extrabold text-slate-900 dark:text-white tabular-nums">{{ number_format($documentCount) }}</div>
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 mt-1">Total Documents</div>
                    </div>
                </div>
                <div wire:loading.remove>
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                                <i class="fas fa-folder text-amber-600 dark:text-amber-400 text-base" aria-hidden="true"></i>
                            </div>
                            <div class="flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                <i class="fas fa-circle-check text-[0.6rem]" aria-hidden="true"></i>
                                <span>Active</span>
                            </div>
                        </div>
                        <div class="text-2xl font-extrabold text-slate-900 dark:text-white tabular-nums">{{ number_format($folderCount) }}</div>
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 mt-1">Workspaces</div>
                    </div>
                </div>
                <div wire:loading.remove>
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                <i class="fas fa-tag text-blue-600 dark:text-blue-400 text-base" aria-hidden="true"></i>
                            </div>
                            <div class="flex items-center gap-1 text-xs font-semibold text-blue-600 dark:text-blue-400">
                                <i class="fas fa-arrow-trend-up text-[0.6rem]" aria-hidden="true"></i>
                                <span>Labeled</span>
                            </div>
                        </div>
                        <div class="text-2xl font-extrabold text-slate-900 dark:text-white tabular-nums">{{ number_format($tagCount) }}</div>
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 mt-1">Tags &amp; Labels</div>
                    </div>
                </div>
                <div wire:loading.remove>
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/30">
                                <i class="fas fa-link text-emerald-600 dark:text-emerald-400 text-base" aria-hidden="true"></i>
                            </div>
                            <div class="flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                <span class="relative flex h-1.5 w-1.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span></span>
                                <span>Live</span>
                            </div>
                        </div>
                        <div class="text-2xl font-extrabold text-slate-900 dark:text-white tabular-nums">{{ number_format($sharedCount) }}</div>
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 mt-1">Shared Links</div>
                    </div>
                </div>
                <div wire:loading.remove>
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-rose-100 dark:bg-rose-900/30">
                                <i class="fas fa-bell text-rose-600 dark:text-rose-400 text-base" aria-hidden="true"></i>
                            </div>
                            @if($unreadCount > 0)
                                <div class="flex items-center gap-1 text-xs font-semibold text-rose-600 dark:text-rose-400">
                                    <i class="fas fa-exclamation text-[0.6rem]" aria-hidden="true"></i>
                                    <span>Pending</span>
                                </div>
                            @else
                                <div class="flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                    <i class="fas fa-circle-check text-[0.6rem]" aria-hidden="true"></i>
                                    <span>Clear</span>
                                </div>
                            @endif
                        </div>
                        <div class="text-2xl font-extrabold text-slate-900 dark:text-white tabular-nums">{{ number_format($unreadCount) }}</div>
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 mt-1">Unread Alerts</div>
                    </div>
                </div>
                <div wire:loading.remove>
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                                <i class="fas fa-users text-green-600 dark:text-green-400 text-base" aria-hidden="true"></i>
                            </div>
                            <div class="flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                <i class="fas fa-circle-check text-[0.6rem]" aria-hidden="true"></i>
                                <span>Active</span>
                            </div>
                        </div>
                        <div class="text-2xl font-extrabold text-slate-900 dark:text-white tabular-nums">{{ number_format($userCount) }}</div>
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-300 mt-1">Members</div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════════════════════
            RECENT DOCUMENTS
            ═══════════════════════════════════════════════════════════════════════ --}}
            <x-ts-card class="border-slate-200 dark:border-slate-700 shadow-sm">
                <x-slot:header>
                    <div class="flex items-center justify-between w-full">
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-clock-rotate-left text-violet-500 dark:text-violet-400" aria-hidden="true"></i>
                            Recent Documents
                        </h3>
                        <a href="{{ route('documents.index') }}"
                           class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline"
                           wire:navigate>View all →</a>
                    </div>
                </x-slot:header>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($recentDocuments as $doc)
                        <div class="flex items-center gap-3 py-2.5 group" wire:key="rdoc-{{ $doc['id'] }}">
                            <div class="w-9 h-9 shrink-0 rounded-lg bg-slate-100 dark:bg-slate-700/60
                                        flex items-center justify-center text-[0.55rem] font-extrabold
                                        text-slate-500 dark:text-slate-300 uppercase
                                        border border-slate-200 dark:border-slate-600">
                                {{ $doc['extension'] ?: 'FILE' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 truncate
                                           group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    {{ $doc['name'] }}
                                </p>
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ $doc['date'] }}
                                </p>
                            </div>
                            <i class="fas fa-chevron-right text-[0.6rem] text-slate-300 dark:text-slate-600
                                       group-hover:text-indigo-400 transition-colors shrink-0"
                               aria-hidden="true"></i>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <i class="fas fa-file-circle-plus text-3xl text-slate-300 dark:text-slate-600 mb-3"
                               aria-hidden="true"></i>
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">No documents yet</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                Upload your first document to get started.</p>
                        </div>
                    @endforelse
                </div>
            </x-ts-card>

            {{-- ══════════════════════════════════════════════════════════════════════
            MONTHLY UPLOAD ACTIVITY (MAIN COLUMN)
            ═══════════════════════════════════════════════════════════════════════ --}}
            <x-ts-card class="h-full border-slate-200 dark:border-slate-700 shadow-sm">
                <x-slot:header>
                    <div class="flex items-center justify-between w-full">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Upload Activity</h3>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Last 6 months</p>
                        </div>
                        <x-ts-badge color="emerald" light>
                            <span class="relative flex h-2 w-2 mr-1">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            Live
                        </x-ts-badge>
                    </div>
                </x-slot:header>
                <div class="flex-1 flex items-center justify-center min-h-[300px]" wire:key="bar-chart-wrap"
                    wire:ignore.self>
                    <div class="relative w-full h-full aspect-[4/3] sm:aspect-[16/9] xl:aspect-[21/9]">
                        <canvas id="uploadChart" wire:key="bar-chart-canvas" aria-label="Monthly upload activity chart"
                            role="img"></canvas>
                    </div>
                </div>
            </x-ts-card>

            {{-- ══════════════════════════════════════════════════════════════════════
            RECENT ACTIVITY LOG
            ═══════════════════════════════════════════════════════════════════════ --}}
            <x-ts-card class="relative min-h-[300px] border-slate-200 dark:border-slate-700 shadow-sm">
                <x-slot:header>
                    <div class="flex items-center justify-between w-full">
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2.5">
                            <i class="fas fa-history text-indigo-500 dark:text-indigo-400" aria-hidden="true"></i>
                            Recent Activity Log
                        </h3>
                        <x-ts-badge color="slate" light>
                            {{ count($recentActivity) }} events
                        </x-ts-badge>
                    </div>
                </x-slot:header>

                <div>
                    {{-- Loading skeleton for activity log --}}
                    <div wire:loading.block
                        class="space-y-6 w-full h-full absolute inset-x-6 top-24 z-10 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm"
                        style="display: none;">
                        @for($i = 0; $i < 4; $i++)
                            <div class="flex items-start gap-4 mb-6" aria-hidden="true">
                                <div class="w-2.5 h-2.5 rounded-full mt-1.5 bg-slate-200 dark:bg-slate-700 animate-pulse">
                                </div>
                                <div class="flex-1 space-y-2.5">
                                    <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-2/3 animate-pulse"></div>
                                    <div class="h-3 bg-slate-100 dark:bg-slate-800 rounded w-1/4 animate-pulse"></div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    {{-- Real activity items --}}
                    <div class="space-y-7 relative z-0"
                        wire:loading.class="opacity-50 blur-sm pointer-events-none transition-all">
                        @forelse($recentActivity as $log)
                            <div class="relative flex items-start gap-4 group" wire:key="activity-{{ $log['id'] }}">
                                {{-- Connection line for timeline --}}
                                @if (!$loop->last)
                                    <div class="absolute top-6 left-[11px] bottom-[-28px] w-0.5 bg-slate-100 dark:bg-slate-700">
                                    </div>
                                @endif

                                <div class="w-6 h-6 rounded-full flex items-center justify-center bg-white dark:bg-slate-800 ring-4 ring-white dark:ring-slate-800 z-10 shadow-sm border border-slate-100 dark:border-slate-700"
                                    title="{{ ucfirst($log['event']) }}" aria-label="Event: {{ $log['event'] }}">
                                    <div class="w-2.5 h-2.5 rounded-full
                                                                    @if($log['event'] === 'created') bg-emerald-500
                                                                    @elseif($log['event'] === 'deleted') bg-rose-500
                                                                    @else bg-blue-500 @endif">
                                    </div>
                                </div>

                                {{-- Causer initials avatar --}}
                                @php $causerInitial = strtoupper(substr($log['causer'] ?? 'U', 0, 1)); @endphp
                                <div class="shrink-0 w-5 h-5 rounded-full flex items-center justify-center text-white text-[0.55rem] font-bold mt-0.5 shadow-sm"
                                     style="background: linear-gradient(135deg, #7c3aed, #4f46e5);"
                                     title="{{ $log['causer'] }}"
                                     aria-hidden="true">{{ $causerInitial }}</div>

                                <div class="flex-1 min-w-0 pb-0 mt-0.5">
                                    <div class="text-sm text-slate-800 dark:text-slate-200 leading-relaxed break-words">
                                        <strong
                                            class="font-extrabold text-slate-900 dark:text-white">{{ $log['causer'] }}</strong>

                                        <span
                                            class="mx-1.5 inline-flex font-mono px-1.5 py-0.5 max-h-5 rounded bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 text-xs font-bold uppercase tracking-widest leading-none items-center shadow-sm
                                                                            @if($log['event'] === 'created') text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-900/10
                                                                            @elseif($log['event'] === 'deleted') text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-800/50 bg-rose-50 dark:bg-rose-900/10
                                                                            @else text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-800/50 bg-blue-50 dark:bg-blue-900/10 @endif">{{ $log['event'] }}</span>

                                        <em
                                            class="not-italic font-semibold text-slate-700 dark:text-slate-300">{{ $log['subject'] }}</em>

                                        @if($log['description'])
                                            <span
                                                class="text-slate-500 dark:text-slate-400 font-medium text-[0.8rem] block sm:inline mt-0.5 sm:mt-0">
                                                &mdash;
                                                {{ Str::limit($log['description'], 80) }}</span>
                                        @endif
                                    </div>
                                    <div
                                        class="mt-1 flex items-center text-xs font-medium text-slate-500 dark:text-slate-400">
                                        <i class="fas fa-clock mr-1.5 opacity-70" aria-hidden="true"></i>
                                        {{ $log['time'] }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-8 text-center"
                                wire:key="empty-activity">
                                <div
                                    class="w-16 h-16 mb-4 rounded-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 border-dashed flex items-center justify-center">
                                    <i class="fas fa-history text-2xl text-slate-300 dark:text-slate-600"
                                        aria-hidden="true"></i>
                                </div>
                                <h4 class="text-sm font-semibold text-slate-900 dark:text-white">No activity yet</h4>
                                <p class="text-xs text-slate-500 mt-1 max-w-sm">When users interact with documents or
                                    settings, the
                                    history will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </x-ts-card>

        </div> {{-- /xl:col-span-8 left column --}}

        {{-- ── RIGHT COLUMN: SIDEBAR & ACTIONS ── --}}
        <div class="xl:col-span-4 space-y-6 lg:space-y-8">

                {{-- QUICK UPLOAD ACTION WIDGET --}}
                <div class="group relative flex flex-col items-center justify-center p-6 lg:p-8 text-center bg-slate-50 dark:bg-slate-800/40 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:border-indigo-400 dark:hover:border-indigo-600 transition-all duration-300"
                    onclick="if(typeof uploadFiles === 'function') uploadFiles();" role="button" tabindex="0">
                    <div
                        class="flex items-center justify-center w-14 h-14 mb-4 rounded-full bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 group-hover:scale-110 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 text-slate-400 transition-all duration-300">
                        <i class="fas fa-cloud-upload-alt text-2xl"></i>
                    </div>
                    <h3
                        class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors">
                        Quick Upload</h3>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1 max-w-[200px]">Drag & drop
                        files here, or click to browse.</p>
                </div>

                {{-- DOCUMENTS BY EXTENSION --}}
                <x-ts-card class="border-slate-200 dark:border-slate-700 shadow-sm">
                    <x-slot:header>
                        <div class="flex items-center justify-between w-full">
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Documents by File
                                    Type</h3>
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Distribution
                                    across extensions</p>
                            </div>
                            <button wire:click="refresh"
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-50 dark:bg-slate-700/50 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                title="Refresh statistics" aria-label="Refresh statistics">
                                <i class="fas fa-sync-alt text-xs"
                                    wire:loading.class="animate-spin text-indigo-600 dark:text-indigo-400"
                                    wire:target="refresh"></i>
                            </button>
                        </div>
                    </x-slot:header>
                    <div class="flex-1 flex items-center justify-center min-h-[250px]" wire:key="ext-chart-wrap"
                        wire:ignore.self>
                        @if(count($docsByExt['data'] ?? []) > 0)
                            <div class="relative w-full h-full max-w-[280px] mx-auto aspect-square">
                                <canvas id="extChart" wire:key="ext-chart-canvas" aria-label="Documents by file type chart"
                                    role="img"></canvas>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500"
                                wire:key="ext-chart-empty">
                                <div
                                    class="w-16 h-16 mb-4 rounded-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                                    <i class="fas fa-chart-pie text-2xl opacity-50" aria-hidden="true"></i>
                                </div>
                                <p class="text-sm font-medium">No document data yet</p>
                            </div>
                        @endif
                    </div>
                </x-ts-card>

                {{-- INJECTED EXTERNAL SLOTS (From home.blade.php) --}}
                {{ $sidebar_modules ?? '' }}
                {{ $sidebar_hints ?? '' }}

        </div> {{-- /xl:col-span-4 right column --}}

    </div> {{-- /EDMS DASHBOARD GRID xl:grid-cols-12 --}}


        @script
        <script>
            /*
             * Chart.js integration for Livewire 3/4 — production-grade pattern.
             *
             * Problem: Livewire morphs the DOM on every wire:poll refresh.  Canvas
             * elements are replaced with new DOM nodes, so any Chart.js instance
             * bound to the old node becomes orphaned — the chart appears blank.
             *
             * Solution:
             *  1. Store Chart instances in a module-level map (chartInstances).
             *  2. buildCharts() destroys existing instances before creating new ones.
             *  3. $wire.on('stats-refreshed') fires after every PHP refresh() call,
             *     receiving the fresh chart data as its payload — re-draws charts.
             *  4. DOMContentLoaded guard runs the initial build once on mount.
             */
            (function () {
                const chartInstances = {};

                function buildCharts(extData, monthly) {
                    // Wait for the deferred app.js module to define window.Chart globally
                    if (typeof Chart === 'undefined') {
                        setTimeout(() => buildCharts(extData, monthly), 50);
                        return;
                    }

                    // Destroy stale instances before creating new ones so we don't
                    // leak memory or bind to the wrong (replaced) canvas node.
                    ['ext', 'bar'].forEach(key => {
                        if (chartInstances[key]) {
                            chartInstances[key].destroy();
                            delete chartInstances[key];
                        }
                    });

                    // Get computed styles for dark mode compatibility
                    const isDarkMode = document.documentElement.classList.contains('dark-mode') || document.body.classList.contains('dark-mode');
                    const gridColor = isDarkMode ? 'rgba(51, 65, 85, 0.4)' : '#f1f5f9';
                    const textColor = isDarkMode ? '#94a3b8' : '#64748b';

                    // ── Doughnut: documents by file type ──────────────────────────
                    if (extData && extData.data && extData.data.length > 0) {
                        const extCtx = document.getElementById('extChart');
                        if (extCtx) {
                            chartInstances.ext = new Chart(extCtx, {
                                type: 'doughnut',
                                data: {
                                    labels: extData.labels,
                                    datasets: [{
                                        data: extData.data,
                                        backgroundColor: extData.colors,
                                        borderWidth: 2,
                                        borderColor: isDarkMode ? '#1e293b' : '#fff',
                                        hoverBorderColor: isDarkMode ? '#1e293b' : '#fff',
                                        hoverOffset: 4
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    cutout: '70%',
                                    animation: { duration: 400 },
                                    plugins: {
                                        legend: {
                                            position: 'bottom',
                                            labels: {
                                                padding: 20,
                                                font: { size: 11, family: "'Inter', sans-serif" },
                                                color: textColor,
                                                usePointStyle: true,
                                                pointStyle: 'circle'
                                            }
                                        },
                                        tooltip: {
                                            backgroundColor: isDarkMode ? 'rgba(15, 23, 42, 0.9)' : 'rgba(255, 255, 255, 0.9)',
                                            titleColor: isDarkMode ? '#f8fafc' : '#0f172a',
                                            bodyColor: isDarkMode ? '#cbd5e1' : '#475569',
                                            borderColor: isDarkMode ? '#334155' : '#e2e8f0',
                                            borderWidth: 1,
                                            padding: 12,
                                            cornerRadius: 8,
                                            titleFont: { size: 13, weight: 'bold', family: "'Inter', sans-serif" },
                                            bodyFont: { size: 12, family: "'Inter', sans-serif" },
                                            callbacks: {
                                                label: (c) => ` ${c.label}: ${c.parsed} file${c.parsed !== 1 ? 's' : ''}`
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    }

                    // ── Bar: monthly upload activity ───────────────────────────────
                    const barCtx = document.getElementById('uploadChart');
                    if (barCtx && monthly) {
                        // Create gradient for bars
                        let barGradient = 'rgba(99, 102, 241, 0.8)';
                        try {
                            const ctx = barCtx.getContext('2d');
                            if (ctx) {
                                barGradient = ctx.createLinearGradient(0, 0, 0, 400);
                                barGradient.addColorStop(0, isDarkMode ? 'rgba(129, 140, 248, 0.9)' : 'rgba(99, 102, 241, 0.9)');
                                barGradient.addColorStop(1, isDarkMode ? 'rgba(79, 70, 229, 0.2)' : 'rgba(99, 102, 241, 0.2)');
                            }
                        } catch (e) { }

                        chartInstances.bar = new Chart(barCtx, {
                            type: 'bar',
                            data: {
                                labels: monthly.labels,
                                datasets: [{
                                    label: 'Uploads',
                                    data: monthly.data,
                                    backgroundColor: barGradient,
                                    borderColor: isDarkMode ? '#818cf8' : '#6366f1',
                                    borderWidth: 1,
                                    borderRadius: 6,
                                    borderSkipped: false,
                                    maxBarThickness: 40
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: { duration: 400 },
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: isDarkMode ? 'rgba(15, 23, 42, 0.9)' : 'rgba(255, 255, 255, 0.9)',
                                        titleColor: isDarkMode ? '#f8fafc' : '#0f172a',
                                        bodyColor: isDarkMode ? '#cbd5e1' : '#475569',
                                        borderColor: isDarkMode ? '#334155' : '#e2e8f0',
                                        borderWidth: 1,
                                        padding: 12,
                                        cornerRadius: 8,
                                        displayColors: false,
                                        callbacks: {
                                            title: (items) => items[0].label,
                                            label: (c) => `${c.parsed.y} upload${c.parsed.y !== 1 ? 's' : ''}`
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1,
                                            font: { size: 11, family: "'Inter', sans-serif" },
                                            color: textColor
                                        },
                                        grid: { color: gridColor, drawBorder: false },
                                        border: { display: false }
                                    },
                                    x: {
                                        ticks: {
                                            font: { size: 11, family: "'Inter', sans-serif" },
                                            color: textColor
                                        },
                                        grid: { display: false },
                                        border: { display: false }
                                    }
                                }
                            }
                        });
                    }
                }

                // ── Initial mount: build charts from server-rendered data ──────────
                buildCharts(
                    @json($docsByExt),
                    { labels: @json($monthlyLabels), data: @json($monthlyData) }
                );

                // ── After every PHP refresh(): re-build charts with fresh data ─────
                // $wire.on() is the Livewire 3/4 API for listening to dispatched browser
                // events inside script blocks. The payload matches what
                // DashboardStats::refresh() dispatches via $this->dispatch().
                $wire.on('stats-refreshed', (events) => {
                    // Livewire v3+ dispatches an array of events
                    const payload = Array.isArray(events) ? events[0] : events;
                    if (!payload) return;

                    const { extData, monthly } = payload;

                    // Wait one microtask so Livewire has finished morphing the DOM
                    // before we bind new instances.
                    requestAnimationFrame(() => buildCharts(extData, monthly));
                });
            })();
        </script>
        @endscript

    </div>