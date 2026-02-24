<div>
    {{-- Poll trigger: calls refresh() every 60 s. Placed inside (not on) the root
    div — Livewire v4 recommended pattern. Refresh() dispatches 'stats-refreshed'
    so Chart.js can re-draw after each DOM morph. --}}
    <div wire:poll.60s="refresh" style="display:none" aria-hidden="true"></div>

    {{-- ══════════════════════════════════════════════════════════════════════
    KPI CARDS
    wire:loading.class dims the entire grid while a poll refresh runs.
    Individual skeleton cards are shown in place of real cards.
    ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 sm:gap-6 transition-opacity duration-300 relative"
        wire:loading.class="opacity-50 pointer-events-none">

        {{-- ── Skeleton (shown only while Livewire is fetching) ── --}}
        <div wire:loading.flex class="absolute inset-0 z-10 w-full h-full flex flex-wrap gap-4 sm:gap-6"
            aria-hidden="true" style="display: none;">
            @for ($i = 0; $i < 6; $i++)
                <div
                    class="flex-1 min-w-[150px] flex flex-col justify-between bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm animate-pulse">
                    <div class="w-12 h-12 rounded-xl bg-slate-200 dark:bg-slate-700 mb-4"></div>
                    <div>
                        <div class="h-8 bg-slate-200 dark:bg-slate-700 rounded-lg w-1/2 mb-2"></div>
                        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-3/4 mb-4"></div>
                    </div>
                </div>
            @endfor
        </div>

        {{-- ── Real KPI cards (hidden while loading) ── --}}
        <div wire:loading.remove
            class="group bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-lg hover:border-violet-300 dark:hover:border-violet-600 transition-all duration-300 flex flex-col justify-between relative overflow-hidden">
            <div
                class="absolute -right-6 -top-6 w-24 h-24 bg-violet-50 dark:bg-violet-900/20 rounded-full blur-xl group-hover:bg-violet-100 dark:group-hover:bg-violet-900/40 transition-colors pointer-events-none">
            </div>
            <div
                class="w-12 h-12 flex items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 mb-5 ring-1 ring-violet-200 dark:ring-violet-800 relative z-10 transition-transform duration-300 group-hover:scale-105">
                <i class="fas fa-file-alt text-xl" aria-hidden="true"></i>
            </div>
            <div class="relative z-10">
                <div class="text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight"
                    aria-label="{{ number_format($documentCount) }} total documents">
                    {{ number_format($documentCount) }}
                </div>
                <div class="text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider">
                    Total Documents</div>
                <div class="mt-3.5 flex items-center text-xs font-semibold text-slate-500 dark:text-slate-500">
                    <i class="fas fa-database mr-1.5" aria-hidden="true"></i> All files
                </div>
            </div>
        </div>

        <div wire:loading.remove
            class="group bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-lg hover:border-amber-300 dark:hover:border-amber-600 transition-all duration-300 flex flex-col justify-between relative overflow-hidden">
            <div
                class="absolute -right-6 -top-6 w-24 h-24 bg-amber-50 dark:bg-amber-900/20 rounded-full blur-xl group-hover:bg-amber-100 dark:group-hover:bg-amber-900/40 transition-colors pointer-events-none">
            </div>
            <div
                class="w-12 h-12 flex items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 mb-5 ring-1 ring-amber-200 dark:ring-amber-800 relative z-10 transition-transform duration-300 group-hover:scale-105">
                <i class="fas fa-folder text-xl" aria-hidden="true"></i>
            </div>
            <div class="relative z-10">
                <div class="text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight"
                    aria-label="{{ number_format($folderCount) }} workspaces">
                    {{ number_format($folderCount) }}
                </div>
                <div class="text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider">
                    Workspaces</div>
                <div class="mt-3.5 flex items-center text-xs font-semibold text-slate-500 dark:text-slate-500">
                    <i class="fas fa-layer-group mr-1.5" aria-hidden="true"></i> Organised
                </div>
            </div>
        </div>

        <div wire:loading.remove
            class="group bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-300 flex flex-col justify-between relative overflow-hidden">
            <div
                class="absolute -right-6 -top-6 w-24 h-24 bg-blue-50 dark:bg-blue-900/20 rounded-full blur-xl group-hover:bg-blue-100 dark:group-hover:bg-blue-900/40 transition-colors pointer-events-none">
            </div>
            <div
                class="w-12 h-12 flex items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 mb-5 ring-1 ring-blue-200 dark:ring-blue-800 relative z-10 transition-transform duration-300 group-hover:scale-105">
                <i class="fas fa-tags text-xl" aria-hidden="true"></i>
            </div>
            <div class="relative z-10">
                <div class="text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight"
                    aria-label="{{ number_format($tagCount) }} tags">{{ number_format($tagCount) }}
                </div>
                <div class="text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider">Tags
                    &amp; Labels</div>
                <div class="mt-3.5 flex items-center text-xs font-semibold text-emerald-600 dark:text-emerald-500">
                    <i class="fas fa-arrow-trend-up mr-1.5" aria-hidden="true"></i> Active
                </div>
            </div>
        </div>

        <div wire:loading.remove
            class="group bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-lg hover:border-emerald-300 dark:hover:border-emerald-600 transition-all duration-300 flex flex-col justify-between relative overflow-hidden">
            <div
                class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-50 dark:bg-emerald-900/20 rounded-full blur-xl group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/40 transition-colors pointer-events-none">
            </div>
            <div
                class="w-12 h-12 flex items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 mb-5 ring-1 ring-emerald-200 dark:ring-emerald-800 relative z-10 transition-transform duration-300 group-hover:scale-105">
                <i class="fas fa-share-nodes text-xl" aria-hidden="true"></i>
            </div>
            <div class="relative z-10">
                <div class="text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight"
                    aria-label="{{ number_format($sharedCount) }} shared links">
                    {{ number_format($sharedCount) }}
                </div>
                <div class="text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider">
                    Shared Links</div>
                <div
                    class="mt-3.5 flex items-center text-xs font-semibold {{ $sharedCount > 0 ? 'text-emerald-600 dark:text-emerald-500' : 'text-slate-500 dark:text-slate-500' }}">
                    <i class="fas fa-link mr-1.5" aria-hidden="true"></i>
                    {{ $sharedCount > 0 ? 'Active Sharing' : 'None Active' }}
                </div>
            </div>
        </div>

        <div wire:loading.remove
            class="group bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-lg hover:border-rose-300 dark:hover:border-rose-600 transition-all duration-300 flex flex-col justify-between relative overflow-hidden">
            <div
                class="absolute -right-6 -top-6 w-24 h-24 bg-rose-50 dark:bg-rose-900/20 rounded-full blur-xl group-hover:bg-rose-100 dark:group-hover:bg-rose-900/40 transition-colors pointer-events-none">
            </div>
            <div
                class="w-12 h-12 flex items-center justify-center rounded-xl bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 mb-5 ring-1 ring-rose-200 dark:ring-rose-800 relative z-10 transition-transform duration-300 group-hover:scale-105">
                <i class="fas fa-bell text-xl" aria-hidden="true"></i>
            </div>
            <div class="relative z-10">
                <div class="text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight"
                    aria-label="{{ number_format($unreadCount) }} unread notifications">
                    {{ number_format($unreadCount) }}
                </div>
                <div class="text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider">
                    Unread Alerts</div>
                @if($unreadCount > 0)
                    <div class="mt-3.5 flex items-center text-xs font-semibold text-rose-600 dark:text-rose-500">
                        <i class="fas fa-exclamation-circle mr-1.5" aria-hidden="true"></i> Needs attention
                    </div>
                @else
                    <div class="mt-3.5 flex items-center text-xs font-semibold text-emerald-600 dark:text-emerald-500">
                        <i class="fas fa-check-circle mr-1.5" aria-hidden="true"></i> All clear
                    </div>
                @endif
            </div>
        </div>

        <div wire:loading.remove
            class="group bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-lg hover:border-green-300 dark:hover:border-green-600 transition-all duration-300 flex flex-col justify-between relative overflow-hidden">
            <div
                class="absolute -right-6 -top-6 w-24 h-24 bg-green-50 dark:bg-green-900/20 rounded-full blur-xl group-hover:bg-green-100 dark:group-hover:bg-green-900/40 transition-colors pointer-events-none">
            </div>
            <div
                class="w-12 h-12 flex items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 mb-5 ring-1 ring-green-200 dark:ring-green-800 relative z-10 transition-transform duration-300 group-hover:scale-105">
                <i class="fas fa-users text-xl" aria-hidden="true"></i>
            </div>
            <div class="relative z-10">
                <div class="text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight"
                    aria-label="{{ number_format($userCount) }} workspace members">
                    {{ number_format($userCount) }}
                </div>
                <div class="text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider">
                    Members</div>
                <div class="mt-3.5 flex items-center text-xs font-semibold text-slate-500 dark:text-slate-500">
                    <i class="fas fa-user mr-1.5" aria-hidden="true"></i> Active users
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
    CHARTS ROW
    Chart.js canvases are keyed with wire:key so Livewire morphs them
    predictably. The @script block listens for the 'stats-refreshed'
    browser event (dispatched by DashboardStats::refresh()) to re-draw
    instances after every poll.
    ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
        {{-- Documents by Extension --}}
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 dark:border-slate-700/50">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Documents by File Type</h3>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Distribution across
                        extensions</p>
                </div>
                <button wire:click="refresh"
                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-50 dark:bg-slate-700/50 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    title="Refresh statistics" aria-label="Refresh statistics">
                    <i class="fas fa-sync-alt text-xs"
                        wire:loading.class="animate-spin text-indigo-600 dark:text-indigo-400"
                        wire:target="refresh"></i>
                </button>
            </div>
            <div class="p-6 flex-1 flex items-center justify-center min-h-[300px]" wire:key="ext-chart-wrap"
                wire:ignore.self>
                @if(count($docsByExt['data'] ?? []) > 0)
                    <div class="relative w-full h-full max-w-[320px] mx-auto aspect-square">
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
        </div>

        {{-- Monthly Upload Activity --}}
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 dark:border-slate-700/50">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Upload Activity</h3>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Last 6 months</p>
                </div>
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 text-xs font-bold tracking-wide border border-emerald-100 dark:border-emerald-800/50 uppercase"
                    aria-live="polite" aria-label="Last updated at {{ $lastUpdated }}">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Live
                </div>
            </div>
            <div class="p-6 flex-1 flex items-center justify-center min-h-[300px]" wire:key="bar-chart-wrap"
                wire:ignore.self>
                <div class="relative w-full h-full aspect-[4/3] sm:aspect-[16/9] lg:aspect-auto">
                    <canvas id="uploadChart" wire:key="bar-chart-canvas" aria-label="Monthly upload activity chart"
                        role="img"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
    RECENT ACTIVITY LOG
    wire:key on each item ensures Livewire diffs correctly rather than
    re-creating every row on every poll. Using a structurally sound list format.
    ═══════════════════════════════════════════════════════════════════════ --}}
    <div
        class="mt-8 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden relative min-h-[300px]">
        <div
            class="flex items-center justify-between px-6 py-5 border-b border-slate-100 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-800/50 z-20 relative">
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2.5">
                <i class="fas fa-history text-indigo-500 dark:text-indigo-400" aria-hidden="true"></i>
                Recent Activity Log
            </h3>
            <div class="flex items-center gap-3">
                <span
                    class="text-xs font-bold tracking-wider text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 py-1.5 px-3 rounded-full uppercase"
                    aria-live="polite">
                    {{ count($recentActivity) }} events
                </span>
            </div>
        </div>

        <div class="p-6">
            {{-- Loading skeleton for activity log --}}
            <div wire:loading.block
                class="space-y-6 w-full h-full absolute inset-x-6 top-24 z-10 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm"
                style="display: none;">
                @for($i = 0; $i < 4; $i++)
                    <div class="flex items-start gap-4 mb-6" aria-hidden="true">
                        <div class="w-2.5 h-2.5 rounded-full mt-1.5 bg-slate-200 dark:bg-slate-700 animate-pulse"></div>
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
                            <div class="absolute top-6 left-[11px] bottom-[-28px] w-0.5 bg-slate-100 dark:bg-slate-700"></div>
                        @endif

                        <div class="w-6 h-6 rounded-full flex items-center justify-center bg-white dark:bg-slate-800 ring-4 ring-white dark:ring-slate-800 z-10 shadow-sm border border-slate-100 dark:border-slate-700"
                            title="{{ ucfirst($log['event']) }}" aria-label="Event: {{ $log['event'] }}">
                            <div class="w-2.5 h-2.5 rounded-full 
                                            @if($log['event'] === 'created') bg-emerald-500 
                                            @elseif($log['event'] === 'deleted') bg-rose-500 
                                            @else bg-blue-500 @endif">
                            </div>
                        </div>

                        <div class="flex-1 min-w-0 pb-0 mt-0.5">
                            <div class="text-sm text-slate-800 dark:text-slate-200 leading-relaxed break-words">
                                <strong class="font-extrabold text-slate-900 dark:text-white">{{ $log['causer'] }}</strong>

                                <span
                                    class="mx-1.5 inline-flex font-mono px-1.5 py-0.5 max-h-5 rounded bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 text-[0.65rem] font-bold uppercase tracking-widest leading-none items-center shadow-sm
                                                    @if($log['event'] === 'created') text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-900/10
                                                    @elseif($log['event'] === 'deleted') text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-800/50 bg-rose-50 dark:bg-rose-900/10
                                                    @else text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-800/50 bg-blue-50 dark:bg-blue-900/10 @endif">{{ $log['event'] }}</span>

                                <em
                                    class="not-italic font-semibold text-slate-800 dark:text-slate-200">{{ $log['subject'] }}</em>

                                @if($log['description'])
                                    <span class="text-slate-500 dark:text-slate-400 font-medium"> &mdash;
                                        {{ Str::limit($log['description'], 80) }}</span>
                                @endif
                            </div>
                            <div class="mt-1 flex items-center text-xs font-semibold text-slate-400 dark:text-slate-500">
                                <i class="fas fa-clock mr-1.5 opacity-70" aria-hidden="true"></i>
                                {{ $log['time'] }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-8 text-center" wire:key="empty-activity">
                        <div
                            class="w-16 h-16 mb-4 rounded-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 border-dashed flex items-center justify-center">
                            <i class="fas fa-history text-2xl text-slate-300 dark:text-slate-600" aria-hidden="true"></i>
                        </div>
                        <h4 class="text-sm font-semibold text-slate-900 dark:text-white">No activity yet</h4>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm">When users interact with documents or settings, the
                            history will appear here.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>


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