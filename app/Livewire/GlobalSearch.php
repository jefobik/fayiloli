<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Document;
use App\Models\Folder;
use App\Models\Tag;

class GlobalSearch extends Component
{
    public string $query   = '';
    public bool   $isOpen  = false;
    public array  $results = [];

    public function updatedQuery(): void
    {
        $q = trim($this->query);
        if (strlen($q) < 2) {
            $this->results = [];
            $this->isOpen  = false;
            return;
        }

        try {
            // Use MeiliSearch via Laravel Scout
            $documents = Document::search($q)
                ->take(5)
                ->get()
                ->map(fn($d) => [
                    'type'      => 'document',
                    'id'        => $d->id,
                    'label'     => $d->name,
                    'sub'       => $d->folder?->name ?? 'No Folder',
                    'icon'      => $this->extIcon($d->extension),
                    'color'     => 'text-blue-600',
                    'url'       => route('getFiles', $d->folder_id ?? 0),
                    'folder_id' => $d->folder_id,
                    'ext'       => strtoupper($d->extension ?? ''),
                ]);

            $folders = Folder::search($q)
                ->take(4)
                ->get()
                ->map(fn($f) => [
                    'type'      => 'folder',
                    'id'        => $f->id,
                    'label'     => $f->name,
                    'sub'       => $f->parent?->name ?? 'Root Workspace',
                    'icon'      => 'fa-folder',
                    'color'     => 'text-amber-500',
                    'url'       => route('getFiles', $f->id),
                    'folder_id' => $f->id,
                    'ext'       => '',
                ]);

            $tags = Tag::search($q)
                ->take(3)
                ->get()
                ->map(fn($t) => [
                    'type'      => 'tag',
                    'id'        => $t->id,
                    'label'     => $t->name,
                    'sub'       => 'Tag · ' . $t->code,
                    'icon'      => 'fa-tag',
                    'color'     => 'text-purple-600',
                    'url'       => route('tags.show', $t->id),
                    'folder_id' => null,
                    'ext'       => '',
                ]);

            $this->results = $documents->concat($folders)->concat($tags)->values()->toArray();
        } catch (\Exception $e) {
            // Graceful fallback to DB LIKE search if MeiliSearch is unavailable
            $like = '%' . $q . '%';
            $this->results = Document::where('name', 'like', $like)
                ->orWhere('original_name', 'like', $like)
                ->limit(6)->get()
                ->map(fn($d) => [
                    'type' => 'document', 'id' => $d->id,
                    'label' => $d->name, 'sub' => $d->folder?->name ?? '-',
                    'icon' => $this->extIcon($d->extension), 'color' => 'text-blue-600',
                    'url' => route('getFiles', $d->folder_id ?? 0),
                    'folder_id' => $d->folder_id, 'ext' => strtoupper($d->extension ?? ''),
                ])->toArray();
        }

        $this->isOpen = count($this->results) > 0;
    }

    public function selectResult(string $url, ?int $folderId): void
    {
        $this->query   = '';
        $this->isOpen  = false;
        $this->results = [];

        if ($folderId) {
            $this->dispatch('navigate-to-folder', url: $url, folderId: $folderId);
        } else {
            $this->redirect($url);
        }
    }

    public function close(): void
    {
        $this->isOpen  = false;
    }

    private function extIcon(?string $ext): string
    {
        return match(strtolower($ext ?? '')) {
            'pdf'                   => 'fa-file-pdf',
            'doc', 'docx'           => 'fa-file-word',
            'xls', 'xlsx'           => 'fa-file-excel',
            'ppt', 'pptx'           => 'fa-file-powerpoint',
            'jpg', 'jpeg', 'png',
            'gif', 'svg', 'webp'    => 'fa-file-image',
            'mp4', 'mov', 'avi'     => 'fa-file-video',
            'mp3', 'wav'            => 'fa-file-audio',
            'zip', 'rar', '7z'      => 'fa-file-archive',
            default                 => 'fa-file-alt',
        };
    }

    public function render()
    {
        return view('livewire.global-search');
    }
}
