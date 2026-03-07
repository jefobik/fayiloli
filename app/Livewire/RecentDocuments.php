<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Document;
use App\Models\MongoActivity;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

/**
 * RecentDocuments — Real-time activity feed for the home dashboard.
 *
 * Shows two tabs:
 *   1. "Recent Files"   — The 12 most-recently created/updated Documents
 *   2. "Activity Log"  — The 20 most-recent MongoActivity events (all log names)
 *
 * The component polls every 30 s and can be refreshed manually.
 * It only shows data for the current tenant's database (tenancy is already
 * initialised by the time this component mounts).
 */
class RecentDocuments extends Component
{
    /** Active tab: 'files' | 'activity' */
    public string $tab = 'files';

    /** Whether a manual refresh is in progress (used for spinner). */
    public bool $refreshing = false;

    // ── Icon map (extension → FA class + colour) ─────────────────────────────

    private const ICON_MAP = [
        'pdf' => ['fa-file-pdf', 'text-red-500'],
        'doc' => ['fa-file-word', 'text-blue-500'],
        'docx' => ['fa-file-word', 'text-blue-500'],
        'xls' => ['fa-file-excel', 'text-emerald-500'],
        'xlsx' => ['fa-file-excel', 'text-emerald-500'],
        'ppt' => ['fa-file-powerpoint', 'text-orange-500'],
        'pptx' => ['fa-file-powerpoint', 'text-orange-500'],
        'zip' => ['fa-file-zipper', 'text-violet-500'],
        'rar' => ['fa-file-zipper', 'text-violet-500'],
        'mp3' => ['fa-file-audio', 'text-cyan-500'],
        'mp4' => ['fa-file-video', 'text-indigo-500'],
        'txt' => ['fa-file-lines', 'text-slate-400'],
        'jpg' => ['fa-file-image', 'text-pink-500'],
        'jpeg' => ['fa-file-image', 'text-pink-500'],
        'png' => ['fa-file-image', 'text-pink-500'],
        'svg' => ['fa-file-image', 'text-pink-500'],
        'csv' => ['fa-file-csv', 'text-emerald-400'],
    ];

    // ── Event listeners ───────────────────────────────────────────────────────

    /** Fired by document browser after an upload or delete */
    #[On('documents-updated')]
    public function refresh(): void
    {
        $this->refreshing = true;
        // Livewire re-renders; refreshing resets to false via render()
    }

    // ── Computed properties ───────────────────────────────────────────────────

    /** Latest 12 documents ordered by updated_at (PostgreSQL / tenant DB). */
    #[Computed]
    public function recentFiles(): Collection
    {
        $docs = Document::withoutGlobalScopes()  // skip the position sort scope
            ->with('folder')
            ->latest('updated_at')
            ->limit(12)
            ->get();

        // Pre-resolve ownerUser in a single UUID-validated batch query.
        // Avoids passing legacy non-UUID owner values (e.g. 'admin') into a
        // PostgreSQL UUID column comparison → SQLSTATE[22P02].
        // Uses getRawOriginal() to bypass the getOwnerAttribute accessor so
        // we can collect the raw FK values for the batch lookup.
        $validOwnerIds = $docs
            ->map(fn($d) => $d->getRawOriginal('owner'))
            ->filter(fn($id) => $id && Str::isUuid((string) $id))
            ->unique()
            ->values();

        $owners = $validOwnerIds->isNotEmpty()
            ? User::whereIn('id', $validOwnerIds)->get()->keyBy('id')
            : collect();

        foreach ($docs as $doc) {
            $rawId = $doc->getRawOriginal('owner');
            $doc->setRelation(
                'ownerUser',
                ($rawId && Str::isUuid((string) $rawId)) ? ($owners->get($rawId) ?? null) : null
            );
        }

        return $docs;
    }

    /** Latest 20 MongoActivity records (all logs, newest first). */
    #[Computed]
    public function activityLog(): Collection
    {
        try {
            $activities = MongoActivity::orderByDesc('created_at')->limit(20)->get();

            // Guard: some legacy MongoDB records carry causer_id = 'admin' (a
            // string username from before UUID migration).  Passing that to
            // User::find() executes WHERE id = 'admin' on a UUID column →
            // SQLSTATE[22P02].  Pre-resolve causers in a single batch query so
            // the blade view never triggers a lazy load.
            $validIds = $activities
                ->pluck('causer_id')
                ->filter(fn($id) => $id && Str::isUuid((string) $id))
                ->unique()
                ->values();

            $users = $validIds->isNotEmpty()
                ? User::whereIn('id', $validIds)->get()->keyBy('id')
                : collect();

            foreach ($activities as $activity) {
                $id = $activity->causer_id ?? null;
                $activity->setRelation(
                    'causer',
                    ($id && Str::isUuid((string) $id)) ? ($users->get($id) ?? null) : null
                );
            }

            return $activities;
        } catch (\Throwable) {
            return collect();
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function iconFor(?string $ext): array
    {
        return self::ICON_MAP[strtolower((string) $ext)] ?? ['fa-file', 'text-slate-400'];
    }

    public function eventColor(string $event): string
    {
        return match ($event) {
            'created' => 'text-emerald-600 bg-emerald-50 dark:text-emerald-400 dark:bg-emerald-950/30',
            'updated' => 'text-blue-600 bg-blue-50 dark:text-blue-400 dark:bg-blue-950/30',
            'deleted' => 'text-red-600 bg-red-50 dark:text-red-400 dark:bg-red-950/30',
            default => 'text-slate-500 bg-slate-100 dark:text-slate-400 dark:bg-slate-800',
        };
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render(): \Illuminate\View\View
    {
        $this->refreshing = false;

        return view('livewire.recent-documents', [
            'recentFiles' => $this->recentFiles,
            'activityLog' => $this->activityLog,
        ]);
    }
}
