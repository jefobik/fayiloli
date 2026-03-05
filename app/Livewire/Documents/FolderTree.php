<?php

declare(strict_types=1);

namespace App\Livewire\Documents;

use App\Models\Folder;
use Livewire\Component;
use Livewire\Attributes\On;

class FolderTree extends Component
{
    public $activeFolderId;
    public $expandedFolders = [];

    public $showCreateModal = false;
    public $newFolderName;
    public $newFolderParentId;

    public function mount($activeFolderId = null)
    {
        $this->activeFolderId = $activeFolderId ?? session('activeFolderId');

        if ($this->activeFolderId && \Illuminate\Support\Str::isUuid($this->activeFolderId)) {
            $this->expandToFolder($this->activeFolderId);
        }
    }

    #[On('folderSelected')]
    public function setActiveFolder($folderId)
    {
        $this->activeFolderId = $folderId;
        if ($folderId && \Illuminate\Support\Str::isUuid($folderId)) {
            $this->expandToFolder($folderId);
        }
    }

    public function toggleFolder($folderId)
    {
        if (in_array($folderId, $this->expandedFolders)) {
            $this->expandedFolders = array_diff($this->expandedFolders, [$folderId]);
        } else {
            $this->expandedFolders[] = $folderId;
        }
    }

    public function selectFolder($folderId)
    {
        $this->activeFolderId = $folderId;
        session(['activeFolderId' => $folderId]);
        $this->dispatch('folderSelected', folderId: $folderId);
    }

    protected function expandToFolder($folderId)
    {
        if (!$folderId || !\Illuminate\Support\Str::isUuid($folderId))
            return;

        $folder = Folder::find($folderId);
        while ($folder && $folder->parent_id) {
            if (!in_array($folder->parent_id, $this->expandedFolders)) {
                $this->expandedFolders[] = $folder->parent_id;
            }
            $folder = Folder::find($folder->parent_id);
        }
    }

    public function createFolder()
    {
        $this->validate([
            'newFolderName' => 'required|string|max:255',
            'newFolderParentId' => 'nullable|uuid|exists:folders,id',
        ]);

        $folder = Folder::create([
            'name' => $this->newFolderName,
            'parent_id' => $this->newFolderParentId ?: null,
            'visibility' => 'public',
        ]);

        $this->reset('newFolderName', 'newFolderParentId', 'showCreateModal');
        $this->expandedFolders[] = $folder->parent_id;
        $this->selectFolder($folder->id);

        $this->dispatch('ts-toast', type: 'success', text: 'Workspace created successfully');
    }

    public function render()
    {
        $folders = Folder::whereNull('parent_id')
            ->with([
                'subfolders' => function ($query) {
                    $query->orderBy('name');
                }
            ])
            ->orderBy('name')
            ->get();

        $allFolders = Folder::orderBy('name')->get(['id', 'name']);

        return view('livewire.documents.folder-tree', [
            'folders' => $folders,
            'allFolders' => $allFolders,
        ]);
    }
}
