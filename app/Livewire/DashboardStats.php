<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Document;
use App\Models\Folder;
use App\Models\Tag;
use App\Models\ShareDocument;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use App\Models\MongoActivity;

class DashboardStats extends Component
{
    public int $documentCount   = 0;
    public int $folderCount     = 0;
    public int $tagCount        = 0;
    public int $sharedCount     = 0;
    public int $unreadCount     = 0;

    public array $docsByExt     = [];
    public array $monthlyLabels = [];
    public array $monthlyData   = [];
    public array $recentActivity= [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function refresh(): void
    {
        $this->loadStats();
    }

    private function loadStats(): void
    {
        $this->documentCount = Document::count();
        $this->folderCount   = Folder::count();
        $this->tagCount      = Tag::count();
        $this->sharedCount   = ShareDocument::count();
        $this->unreadCount   = Notification::where('status', 'UNREAD')
            ->where('dismiss_status', 'UNDISMISSED')
            ->count();

        // Documents by extension (top 6).
        // withoutGlobalScope('position') prevents the Document model's global
        // ORDER BY position from being injected — PostgreSQL forbids ordering
        // by a column that isn't in the SELECT or GROUP BY of an aggregation.
        $byExt = Document::withoutGlobalScope('position')
            ->select('extension', DB::raw('count(*) as total'))
            ->whereNotNull('extension')
            ->where('extension', '!=', '')
            ->groupBy('extension')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $this->docsByExt = [
            'labels' => $byExt->pluck('extension')->map(fn($e) => strtoupper($e))->toArray(),
            'data'   => $byExt->pluck('total')->toArray(),
            'colors' => ['#7c3aed','#4f46e5','#0ea5e9','#10b981','#f59e0b','#ef4444'],
        ];

        // Monthly uploads (last 6 months).
        // Uses PostgreSQL-native to_char() for grouping — YEAR()/MONTH() are
        // MySQL-only functions. Grouping on aliases is also forbidden in PgSQL
        // so we group by the same expression used in SELECT.
        $months = collect(range(5, 0))->map(fn($m) => now()->subMonths($m));

        $uploads = Document::withoutGlobalScope('position')
            ->select(
                DB::raw("to_char(created_at, 'YYYY-MM') as month_key"),
                DB::raw('count(*) as cnt')
            )
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy(DB::raw("to_char(created_at, 'YYYY-MM')"))
            ->get()
            ->keyBy('month_key');

        $this->monthlyLabels = $months->map(fn($m) => $m->format('M Y'))->toArray();
        $this->monthlyData   = $months->map(
            fn($m) => (int) ($uploads->get($m->format('Y-m'))?->cnt ?? 0)
        )->toArray();

        // Recent activity log — reads from MongoDB via MongoActivity.
        $this->recentActivity = MongoActivity::with('causer')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn($a) => [
                'id'          => $a->id,
                'description' => $a->description,
                'event'       => $a->event ?? 'updated',
                'subject'     => $a->subject_type ? class_basename($a->subject_type) : 'System',
                'causer'      => $a->causer?->name ?? 'System',
                'time'        => $a->created_at?->diffForHumans(),
            ])->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard-stats');
    }
}
