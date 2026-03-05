<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use App\Models\Document;
use App\Models\Folder;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GlobalSearch extends Component
{
    public string $query = '';
    public bool $open = false;    // palette visibility
    public int $cursor = 0;        // keyboard cursor index across flat result list

    #[Locked] public array $results = []; // keyed by section

    // ── Open / close ──────────────────────────────────────────────────────────

    public function openPalette(): void
    {
        $this->open = true;
        $this->cursor = 0;
    }

    public function closePalette(): void
    {
        $this->open = false;
        $this->query = '';
        $this->results = [];
        $this->cursor = 0;
    }

    // ── Search lifecycle ──────────────────────────────────────────────────────

    public function updatedQuery(): void
    {
        $this->cursor = 0;
        $this->executeSearch();
    }

    public function executeSearch(): void
    {
        $q = trim($this->query);

        // Always show quick actions when palette is open
        $actions = $this->buildActions($q);

        if (strlen($q) < 2) {
            $this->results = ['actions' => $actions, 'documents' => [], 'users' => []];
            return;
        }

        try {
            $documents = $this->searchDocuments($q);
            $users = $this->searchUsers($q);
        } catch (\Exception) {
            $documents = $this->fallbackDocuments($q);
            $users = [];
        }

        $this->results = [
            'documents' => $documents,
            'users' => $users,
            'actions' => $actions,
        ];
    }

    // ── Navigate to a result ─────────────────────────────────────────────────

    public function selectResult(string $url, ?int $folderId = null): void
    {
        $this->closePalette();

        if ($folderId) {
            $this->dispatch('navigate-to-folder', url: $url, folderId: $folderId);
        } else {
            $this->redirect($url);
        }
    }

    // ── Section builders ─────────────────────────────────────────────────────

    private function searchDocuments(string $q): array
    {
        $docs = Document::search($q)->take(6)->get()
            ->map(fn($d) => [
                'type' => 'document',
                'id' => $d->id,
                'label' => $d->name,
                'sub' => $d->folder?->name ?? 'No folder',
                'icon' => $this->extIcon($d->extension),
                'badge' => strtoupper($d->extension ?? ''),
                'badge_cls' => $this->extBadgeClass($d->extension),
                'url' => route('getFiles', $d->folder_id ?? 0),
                'folder_id' => $d->folder_id,
            ]);

        $folders = Folder::search($q)->take(3)->get()
            ->map(fn($f) => [
                'type' => 'folder',
                'id' => $f->id,
                'label' => $f->name,
                'sub' => $f->parent?->name ?? 'Root workspace',
                'icon' => 'fa-folder',
                'badge' => 'Folder',
                'badge_cls' => 'text-amber-600 bg-amber-50',
                'url' => route('getFiles', $f->id),
                'folder_id' => $f->id,
            ]);

        $tags = Tag::search($q)->take(3)->get()
            ->map(fn($t) => [
                'type' => 'tag',
                'id' => $t->id,
                'label' => $t->name,
                'sub' => 'Tag · ' . $t->code,
                'icon' => 'fa-tag',
                'badge' => 'Tag',
                'badge_cls' => 'text-purple-600 bg-purple-50',
                'url' => route('tags.show', $t->id),
                'folder_id' => null,
            ]);

        return $docs->concat($folders)->concat($tags)->values()->toArray();
    }

    private function fallbackDocuments(string $q): array
    {
        $like = '%' . $q . '%';

        return Document::where('name', 'like', $like)
            ->orWhere('original_name', 'like', $like)
            ->limit(6)
            ->get()
            ->map(fn($d) => [
                'type' => 'document',
                'id' => $d->id,
                'label' => $d->name,
                'sub' => $d->folder?->name ?? '-',
                'icon' => $this->extIcon($d->extension),
                'badge' => strtoupper($d->extension ?? ''),
                'badge_cls' => $this->extBadgeClass($d->extension),
                'url' => route('getFiles', $d->folder_id ?? 0),
                'folder_id' => $d->folder_id,
            ])
            ->toArray();
    }

    private function searchUsers(string $q): array
    {
        $user = Auth::user();

        // Only admins/managers see users
        if (!$user?->hasAnyRole(['admin', 'manager'])) {
            return [];
        }

        $like = '%' . $q . '%';

        return User::where('name', 'like', $like)
            ->orWhere('email', 'like', $like)
            ->limit(4)
            ->get()
            ->map(fn($u) => [
                'type' => 'user',
                'id' => $u->id,
                'label' => $u->name,
                'sub' => $u->email,
                'init' => strtoupper(substr($u->name, 0, 1)),
                'icon' => 'fa-circle-user',
                'badge' => ucfirst($u->getRoleNames()->first() ?? 'User'),
                'badge_cls' => 'text-blue-700 bg-blue-50',
                'url' => route('users.show', $u->id),
                'folder_id' => null,
            ])
            ->toArray();
    }

    private function buildActions(string $q): array
    {
        $user = Auth::user();
        $all = [
            [
                'type' => 'action',
                'label' => 'Go to Dashboard',
                'sub' => 'Home · workspace overview',
                'icon' => 'fa-house-chimney',
                'badge' => 'Navigate',
                'badge_cls' => 'text-slate-600 bg-slate-100',
                'url' => route('home'),
                'folder_id' => null,
            ],
            [
                'type' => 'action',
                'label' => 'Browse Documents',
                'sub' => 'Open document library',
                'icon' => 'fa-file-lines',
                'badge' => 'Navigate',
                'badge_cls' => 'text-slate-600 bg-slate-100',
                'url' => route('documents.index'),
                'folder_id' => null,
            ],
            [
                'type' => 'action',
                'label' => 'Upload File',
                'sub' => 'Add documents to your workspace',
                'icon' => 'fa-arrow-up-from-bracket',
                'badge' => 'Action',
                'badge_cls' => 'text-blue-600 bg-blue-50',
                'url' => '#',
                'folder_id' => null,
                'js' => 'uploadFiles()',
            ],
        ];

        if ($user?->hasRole(['admin', 'manager'])) {
            $all[] = [
                'type' => 'action',
                'label' => 'Manage Users',
                'sub' => 'User management · admin',
                'icon' => 'fa-users',
                'badge' => 'Admin',
                'badge_cls' => 'text-amber-700 bg-amber-50',
                'url' => route('users.index'),
                'folder_id' => null,
            ];
            $all[] = [
                'type' => 'action',
                'label' => 'Roles & Permissions',
                'sub' => 'Manage access control',
                'icon' => 'fa-shield-halved',
                'badge' => 'Admin',
                'badge_cls' => 'text-amber-700 bg-amber-50',
                'url' => route('roles.index'),
                'folder_id' => null,
            ];
        }

        if ($q === '') {
            return $all;
        }

        // Fuzzy filter: keep actions where label/sub contain query chars
        $lower = strtolower($q);
        return array_values(array_filter(
            $all,
            fn($a) => str_contains(strtolower($a['label']), $lower)
            || str_contains(strtolower($a['sub']), $lower)
        ));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function extIcon(?string $ext): string
    {
        return match (strtolower($ext ?? '')) {
            'pdf' => 'fa-file-pdf',
            'doc', 'docx' => 'fa-file-word',
            'xls', 'xlsx' => 'fa-file-excel',
            'ppt', 'pptx' => 'fa-file-powerpoint',
            'jpg', 'jpeg', 'png',
            'gif', 'svg', 'webp' => 'fa-file-image',
            'mp4', 'mov', 'avi' => 'fa-file-video',
            'mp3', 'wav' => 'fa-file-audio',
            'zip', 'rar', '7z' => 'fa-file-zipper',
            default => 'fa-file-lines',
        };
    }

    private function extBadgeClass(?string $ext): string
    {
        return match (strtolower($ext ?? '')) {
            'pdf' => 'text-red-600 bg-red-50',
            'doc', 'docx' => 'text-blue-700 bg-blue-50',
            'xls', 'xlsx' => 'text-green-700 bg-green-50',
            'ppt', 'pptx' => 'text-orange-700 bg-orange-50',
            'jpg', 'jpeg',
            'png', 'gif',
            'svg', 'webp' => 'text-pink-700 bg-pink-50',
            default => 'text-slate-600 bg-slate-100',
        };
    }

    public function render()
    {
        return view('tenant.components.search.global-search');
    }
}
