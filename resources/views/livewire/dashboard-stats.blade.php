<div wire:poll.60s="refresh">
    {{-- ── KPI Cards ── --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ede9fe">
                <i class="fas fa-file-alt" style="color:#7c3aed"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ number_format($documentCount) }}</div>
                <div class="stat-label">Total Documents</div>
                <span class="stat-delta neutral"><i class="fas fa-database"></i> All files</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7">
                <i class="fas fa-folder" style="color:#d97706"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ number_format($folderCount) }}</div>
                <div class="stat-label">Workspaces / Folders</div>
                <span class="stat-delta neutral"><i class="fas fa-layer-group"></i> Organised</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#dbeafe">
                <i class="fas fa-tags" style="color:#1d4ed8"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ number_format($tagCount) }}</div>
                <div class="stat-label">Tags & Labels</div>
                <span class="stat-delta up"><i class="fas fa-check"></i> Active</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7">
                <i class="fas fa-share-alt" style="color:#16a34a"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ number_format($sharedCount) }}</div>
                <div class="stat-label">Shared Links</div>
                <span class="stat-delta @if($sharedCount > 0) up @else neutral @endif">
                    <i class="fas fa-link"></i> Active
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2">
                <i class="fas fa-bell" style="color:#dc2626"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ number_format($unreadCount) }}</div>
                <div class="stat-label">Unread Notifications</div>
                @if($unreadCount > 0)
                    <span class="stat-delta down"><i class="fas fa-exclamation"></i> Needs attention</span>
                @else
                    <span class="stat-delta up"><i class="fas fa-check"></i> All clear</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Charts Row ── --}}
    <div class="chart-grid">
        {{-- Documents by Extension --}}
        <div class="chart-card">
            <div class="chart-card-header">
                <div>
                    <h3>Documents by File Type</h3>
                    <div class="chart-sub">Distribution across extensions</div>
                </div>
                <button wire:click="refresh" class="header-icon-btn" title="Refresh">
                    <i class="fas fa-sync-alt" style="font-size:0.8rem" wire:loading.class="fa-spin"></i>
                </button>
            </div>
            <div class="chart-card-body">
                @if(count($docsByExt['data'] ?? []) > 0)
                    <canvas id="extChart" style="max-height:240px"></canvas>
                @else
                    <div style="text-align:center;padding:2rem;color:#94a3b8;font-size:0.85rem">
                        <i class="fas fa-chart-pie" style="font-size:2rem;display:block;margin-bottom:0.5rem;opacity:0.3"></i>
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
            </div>
            <div class="chart-card-body">
                <canvas id="uploadChart" style="max-height:240px"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Recent Activity Log ── --}}
    <div class="activity-card">
        <div class="activity-header">
            <h3><i class="fas fa-history" style="color:#7c3aed;margin-right:0.5rem"></i>Recent Activity</h3>
            <span style="font-size:0.75rem;color:#94a3b8">Audit log — last {{ count($recentActivity) }} events</span>
        </div>
        @forelse($recentActivity as $log)
            <div class="activity-item">
                <div class="activity-dot"
                    style="background: {{ $log['event'] === 'created' ? '#10b981' : ($log['event'] === 'deleted' ? '#ef4444' : '#3b82f6') }}"></div>
                <div style="flex:1;min-width:0">
                    <div class="activity-text">
                        <strong>{{ $log['causer'] }}</strong>
                        <span class="audit-event-badge audit-{{ $log['event'] }}" style="margin:0 0.3rem">{{ $log['event'] }}</span>
                        <em>{{ $log['subject'] }}</em>
                        @if($log['description'])
                            — {{ Str::limit($log['description'], 60) }}
                        @endif
                    </div>
                    <div class="activity-time"><i class="fas fa-clock" style="margin-right:0.2rem"></i>{{ $log['time'] }}</div>
                </div>
            </div>
        @empty
            <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:0.85rem">
                <i class="fas fa-history" style="font-size:2rem;display:block;margin-bottom:0.5rem;opacity:0.3"></i>
                No activity logged yet
            </div>
        @endforelse
    </div>
</div>

@script
<script>
    (function() {
        const extData  = @json($docsByExt);
        const monthly  = { labels: @json($monthlyLabels), data: @json($monthlyData) };

        // Donut chart
        if (extData.data && extData.data.length > 0) {
            const extCtx = document.getElementById('extChart');
            if (extCtx) {
                new Chart(extCtx, {
                    type: 'doughnut',
                    data: {
                        labels: extData.labels,
                        datasets: [{
                            data: extData.data,
                            backgroundColor: extData.colors,
                            borderWidth: 2,
                            borderColor: '#fff',
                            hoverBorderColor: '#fff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        cutout: '65%',
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 14, font: { size: 11 } } },
                            tooltip: { callbacks: { label: (c) => ` ${c.label}: ${c.parsed} files` } }
                        }
                    }
                });
            }
        }

        // Bar chart
        const barCtx = document.getElementById('uploadChart');
        if (barCtx) {
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: monthly.labels,
                    datasets: [{
                        label: 'Uploads',
                        data: monthly.data,
                        backgroundColor: 'rgba(124, 58, 237, 0.75)',
                        borderColor: '#7c3aed',
                        borderWidth: 0,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, font: { size: 11 } },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            ticks: { font: { size: 11 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    })();
</script>
@endscript
