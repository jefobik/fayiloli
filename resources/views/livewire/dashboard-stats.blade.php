<div>
    {{-- ══════════════════════════════════════════════════════════════════════
         KPI CARDS
         wire:loading.class dims the entire grid while a poll refresh runs.
         Individual skeleton cards are shown in place of real cards.
    ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="stat-grid" wire:loading.class="ds-refreshing">

        {{-- ── Skeleton (shown only while Livewire is fetching) ── --}}
        <div class="stat-card ds-skeleton" wire:loading.block aria-hidden="true">
            <div class="ds-skel-icon"></div>
            <div class="ds-skel-body"><div class="ds-skel-line ds-skel-val"></div><div class="ds-skel-line ds-skel-lbl"></div></div>
        </div>
        <div class="stat-card ds-skeleton" wire:loading.block aria-hidden="true">
            <div class="ds-skel-icon"></div>
            <div class="ds-skel-body"><div class="ds-skel-line ds-skel-val"></div><div class="ds-skel-line ds-skel-lbl"></div></div>
        </div>
        <div class="stat-card ds-skeleton" wire:loading.block aria-hidden="true">
            <div class="ds-skel-icon"></div>
            <div class="ds-skel-body"><div class="ds-skel-line ds-skel-val"></div><div class="ds-skel-line ds-skel-lbl"></div></div>
        </div>
        <div class="stat-card ds-skeleton" wire:loading.block aria-hidden="true">
            <div class="ds-skel-icon"></div>
            <div class="ds-skel-body"><div class="ds-skel-line ds-skel-val"></div><div class="ds-skel-line ds-skel-lbl"></div></div>
        </div>
        <div class="stat-card ds-skeleton" wire:loading.block aria-hidden="true">
            <div class="ds-skel-icon"></div>
            <div class="ds-skel-body"><div class="ds-skel-line ds-skel-val"></div><div class="ds-skel-line ds-skel-lbl"></div></div>
        </div>
        <div class="stat-card ds-skeleton" wire:loading.block aria-hidden="true">
            <div class="ds-skel-icon"></div>
            <div class="ds-skel-body"><div class="ds-skel-line ds-skel-val"></div><div class="ds-skel-line ds-skel-lbl"></div></div>
        </div>

        {{-- ── Real KPI cards (hidden while loading) ── --}}
        <div class="stat-card" wire:loading.remove>
            <div class="stat-icon" style="background:#ede9fe">
                <i class="fas fa-file-alt" style="color:#7c3aed" aria-hidden="true"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value" aria-label="{{ number_format($documentCount) }} total documents">{{ number_format($documentCount) }}</div>
                <div class="stat-label">Total Documents</div>
                <span class="stat-delta neutral"><i class="fas fa-database" aria-hidden="true"></i> All files</span>
            </div>
        </div>

        <div class="stat-card" wire:loading.remove>
            <div class="stat-icon" style="background:#fef3c7">
                <i class="fas fa-folder" style="color:#d97706" aria-hidden="true"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value" aria-label="{{ number_format($folderCount) }} workspaces">{{ number_format($folderCount) }}</div>
                <div class="stat-label">Workspaces</div>
                <span class="stat-delta neutral"><i class="fas fa-layer-group" aria-hidden="true"></i> Organised</span>
            </div>
        </div>

        <div class="stat-card" wire:loading.remove>
            <div class="stat-icon" style="background:#dbeafe">
                <i class="fas fa-tags" style="color:#1d4ed8" aria-hidden="true"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value" aria-label="{{ number_format($tagCount) }} tags">{{ number_format($tagCount) }}</div>
                <div class="stat-label">Tags &amp; Labels</div>
                <span class="stat-delta up"><i class="fas fa-check" aria-hidden="true"></i> Active</span>
            </div>
        </div>

        <div class="stat-card" wire:loading.remove>
            <div class="stat-icon" style="background:#dcfce7">
                <i class="fas fa-share-alt" style="color:#16a34a" aria-hidden="true"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value" aria-label="{{ number_format($sharedCount) }} shared links">{{ number_format($sharedCount) }}</div>
                <div class="stat-label">Shared Links</div>
                <span class="stat-delta {{ $sharedCount > 0 ? 'up' : 'neutral' }}">
                    <i class="fas fa-link" aria-hidden="true"></i> {{ $sharedCount > 0 ? 'Active' : 'None' }}
                </span>
            </div>
        </div>

        <div class="stat-card" wire:loading.remove>
            <div class="stat-icon" style="background:#fee2e2">
                <i class="fas fa-bell" style="color:#dc2626" aria-hidden="true"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value" aria-label="{{ number_format($unreadCount) }} unread notifications">{{ number_format($unreadCount) }}</div>
                <div class="stat-label">Unread Alerts</div>
                @if($unreadCount > 0)
                    <span class="stat-delta down"><i class="fas fa-exclamation" aria-hidden="true"></i> Needs attention</span>
                @else
                    <span class="stat-delta up"><i class="fas fa-check" aria-hidden="true"></i> All clear</span>
                @endif
            </div>
        </div>

        <div class="stat-card" wire:loading.remove>
            <div class="stat-icon" style="background:#f0fdf4">
                <i class="fas fa-users" style="color:#15803d" aria-hidden="true"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value" aria-label="{{ number_format($userCount) }} workspace members">{{ number_format($userCount) }}</div>
                <div class="stat-label">Workspace Members</div>
                <span class="stat-delta neutral"><i class="fas fa-user" aria-hidden="true"></i> Active users</span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         CHARTS ROW
         Chart.js canvases are keyed with wire:key so Livewire morphs them
         predictably. The @script block listens for the 'stats-refreshed'
         browser event (dispatched by DashboardStats::refresh()) to re-draw
         instances after every poll — without this, canvas DOM nodes that
         Livewire replaces lose their Chart.js state.
    ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="chart-grid">
        {{-- Documents by Extension --}}
        <div class="chart-card">
            <div class="chart-card-header">
                <div>
                    <h3>Documents by File Type</h3>
                    <div class="chart-sub">Distribution across extensions</div>
                </div>
                <button wire:click="refresh"
                        class="header-icon-btn ds-refresh-btn"
                        title="Refresh statistics"
                        aria-label="Refresh statistics">
                    <i class="fas fa-sync-alt"
                       style="font-size:0.8rem"
                       wire:loading.class="fa-spin"
                       wire:target="refresh"></i>
                </button>
            </div>
            <div class="chart-card-body" wire:key="ext-chart-wrap">
                @if(count($docsByExt['data'] ?? []) > 0)
                    <canvas id="extChart" wire:key="ext-chart-canvas" style="max-height:240px" aria-label="Documents by file type chart" role="img"></canvas>
                @else
                    <div class="ds-empty-chart" wire:key="ext-chart-empty">
                        <i class="fas fa-chart-pie" aria-hidden="true"></i>
                        No document data yet
                    </div>
                @endif
            </div>
        </div>

        {{-- Monthly Upload Activity --}}
        <div class="chart-card">
            <div class="chart-card-header">
                <div>
                    <h3>Upload Activity</h3>
                    <div class="chart-sub">Last 6 months</div>
                </div>
                <div class="ds-last-updated" aria-live="polite" aria-label="Last updated at {{ $lastUpdated }}">
                    <i class="fas fa-circle ds-live-dot" aria-hidden="true"></i>
                    Live &middot; {{ $lastUpdated }}
                </div>
            </div>
            <div class="chart-card-body" wire:key="bar-chart-wrap">
                <canvas id="uploadChart" wire:key="bar-chart-canvas" style="max-height:240px" aria-label="Monthly upload activity chart" role="img"></canvas>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         RECENT ACTIVITY LOG
         wire:key on each item ensures Livewire diffs correctly rather than
         re-creating every row on every poll.
    ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="activity-card">
        <div class="activity-header">
            <h3>
                <i class="fas fa-history" style="color:#7c3aed;margin-right:0.5rem" aria-hidden="true"></i>
                Recent Activity
            </h3>
            <div style="display:flex;align-items:center;gap:0.75rem">
                <span style="font-size:0.75rem;color:#94a3b8" aria-live="polite">
                    {{ count($recentActivity) }} events
                </span>
                <span class="ds-poll-badge" title="Auto-refreshes every 60 seconds">
                    <i class="fas fa-circle ds-live-dot" aria-hidden="true"></i> Live
                </span>
            </div>
        </div>

        {{-- Loading skeleton for activity log --}}
        <div wire:loading.block>
            @for($i = 0; $i < 4; $i++)
                <div class="activity-item" aria-hidden="true">
                    <div class="activity-dot" style="background:#e2e8f0"></div>
                    <div style="flex:1">
                        <div class="ds-skel-line" style="height:12px;width:60%;margin-bottom:4px;border-radius:4px;background:#f1f5f9;animation:ds-pulse 1.5s ease-in-out infinite"></div>
                        <div class="ds-skel-line" style="height:10px;width:30%;border-radius:4px;background:#f1f5f9;animation:ds-pulse 1.5s ease-in-out infinite"></div>
                    </div>
                </div>
            @endfor
        </div>

        {{-- Real activity items --}}
        <div wire:loading.remove>
            @forelse($recentActivity as $log)
                <div class="activity-item" wire:key="activity-{{ $log['id'] }}">
                    <div class="activity-dot"
                         style="background: {{ $log['event'] === 'created' ? '#10b981' : ($log['event'] === 'deleted' ? '#ef4444' : '#3b82f6') }}"
                         title="{{ ucfirst($log['event']) }}"
                         aria-label="Event: {{ $log['event'] }}">
                    </div>
                    <div style="flex:1;min-width:0">
                        <div class="activity-text">
                            <strong>{{ $log['causer'] }}</strong>
                            <span class="audit-event-badge audit-{{ $log['event'] }}" style="margin:0 0.3rem">{{ $log['event'] }}</span>
                            <em>{{ $log['subject'] }}</em>
                            @if($log['description'])
                                <span style="color:#94a3b8"> — {{ Str::limit($log['description'], 60) }}</span>
                            @endif
                        </div>
                        <div class="activity-time">
                            <i class="fas fa-clock" style="margin-right:0.2rem" aria-hidden="true"></i>
                            {{ $log['time'] }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="ds-empty-activity">
                    <i class="fas fa-history" aria-hidden="true"></i>
                    <p>No activity logged yet</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@script
<script>
    /*
     * Chart.js integration for Livewire 3 — production-grade pattern.
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

            // ── Doughnut: documents by file type ──────────────────────────
            if (extData && extData.data && extData.data.length > 0) {
                const extCtx = document.getElementById('extChart');
                if (extCtx) {
                    chartInstances.ext = new Chart(extCtx, {
                        type: 'doughnut',
                        data: {
                            labels: extData.labels,
                            datasets: [{
                                data:            extData.data,
                                backgroundColor: extData.colors,
                                borderWidth:     2,
                                borderColor:     '#fff',
                                hoverBorderColor:'#fff',
                            }]
                        },
                        options: {
                            responsive:          true,
                            maintainAspectRatio: true,
                            cutout:              '65%',
                            animation: { duration: 400 },
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels:   { padding: 14, font: { size: 11 } }
                                },
                                tooltip: {
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
                chartInstances.bar = new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels:   monthly.labels,
                        datasets: [{
                            label:           'Uploads',
                            data:            monthly.data,
                            backgroundColor: 'rgba(124, 58, 237, 0.75)',
                            borderColor:     '#7c3aed',
                            borderWidth:     0,
                            borderRadius:    6,
                            borderSkipped:   false,
                        }]
                    },
                    options: {
                        responsive:          true,
                        maintainAspectRatio: true,
                        animation: { duration: 400 },
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, font: { size: 11 } },
                                grid:  { color: '#f1f5f9' }
                            },
                            x: {
                                ticks: { font: { size: 11 } },
                                grid:  { display: false }
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
        // $wire.on() is the Livewire 3 API for listening to dispatched browser
        // events inside @script blocks. The payload matches what
        // DashboardStats::refresh() dispatches via $this->dispatch().
        $wire.on('stats-refreshed', ({ extData, monthly }) => {
            // Wait one microtask so Livewire has finished morphing the DOM
            // (canvas nodes may have been replaced) before we bind new instances.
            requestAnimationFrame(() => buildCharts(extData, monthly));
        });
    })();
</script>
@endscript

<style>
/* ── KPI grid pulse on refresh ─────────────────────────── */
.ds-refreshing { opacity: 0.6; pointer-events: none; transition: opacity 0.15s; }

/* ── Skeleton loader ────────────────────────────────────── */
@keyframes ds-pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.4; }
}
.ds-skeleton { pointer-events: none; }
.ds-skel-icon {
    width: 42px; height: 42px; border-radius: 10px;
    background: #f1f5f9; flex-shrink: 0;
    animation: ds-pulse 1.5s ease-in-out infinite;
}
.ds-skel-body { flex: 1; }
.ds-skel-line {
    height: 12px; border-radius: 6px; background: #f1f5f9;
    animation: ds-pulse 1.5s ease-in-out infinite;
    margin-bottom: 6px;
}
.ds-skel-val  { height: 22px; width: 55%; }
.ds-skel-lbl  { height: 11px; width: 75%; }

/* ── Refresh button ─────────────────────────────────────── */
.ds-refresh-btn { min-height: 36px; min-width: 36px; }

/* ── Live indicator ─────────────────────────────────────── */
.ds-live-dot {
    font-size: 0.45rem; color: #10b981;
    animation: ds-live-blink 2s ease-in-out infinite;
    vertical-align: middle; margin-right: 0.15rem;
}
@keyframes ds-live-blink {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.3; }
}
.ds-last-updated {
    font-size: 0.7rem; color: #94a3b8;
    display: flex; align-items: center; gap: 0.2rem;
}
.ds-poll-badge {
    display: inline-flex; align-items: center; gap: 0.25rem;
    font-size: 0.65rem; font-weight: 600; color: #10b981;
    background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2);
    border-radius: 999px; padding: 0.1rem 0.5rem;
    letter-spacing: 0.04em; text-transform: uppercase;
}

/* ── Empty chart placeholder ────────────────────────────── */
.ds-empty-chart {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; gap: 0.5rem;
    padding: 2.5rem 1rem; text-align: center;
    color: #94a3b8; font-size: 0.85rem;
}
.ds-empty-chart i { font-size: 2rem; opacity: 0.3; }

/* ── Empty activity placeholder ─────────────────────────── */
.ds-empty-activity {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; gap: 0.5rem;
    padding: 2.5rem 1rem; text-align: center;
    color: #94a3b8; font-size: 0.85rem;
}
.ds-empty-activity i  { font-size: 2rem; opacity: 0.3; }
.ds-empty-activity p  { margin: 0; }

/* ── Dark mode ──────────────────────────────────────────── */
body.dark-mode .ds-skel-icon,
body.dark-mode .ds-skel-line { background: #334155; }
body.dark-mode .ds-empty-chart,
body.dark-mode .ds-empty-activity { color: #64748b; }
</style>
