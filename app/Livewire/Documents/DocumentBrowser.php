<?php

declare(strict_types=1);

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use App\Models\ShareDocument;
use App\Models\FileRequest;
use App\Jobs\SendFileRequestEmail;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentBrowser extends Component
{
    use WithFileUploads;

    #[Url]
    public $currentFolderId;

    #[Url]
    public $search = '';

    public $viewMode = 'grid';
    public $selectedDocumentId;

    // Property editing
    public $editingName;
    public $editingOwner;
    public $editingVisibility;

    public $uploads = [];
    public $isDragging = false;
    public bool $sidebarCollapsed = false;

    // Sharing & Requests
    public $showShareModal = false;
    public $showRequestModal = false;
    public $shareUrl = '';
    public $shareName = '';
    public $shareVisibility = 'public';

    public $requestName = '';
    public $requestTo = '';
    public $requestNote = '';
    public $requestDueDate = '';

    public function mount($folderId = null)
    {
        $this->currentFolderId = $folderId ?? session('activeFolderId');
        $this->sidebarCollapsed = (bool) session('sidebarCollapsed', false);

        if (Auth::check()) {
            $viewMode = Auth::user()->getPreference('docViewMode');
            if ($viewMode) {
                $this->viewMode = $viewMode;
            }
        }
    }

    #[On('sidebar-toggled')]
    public function syncSidebar($collapsed)
    {
        $this->sidebarCollapsed = (bool) $collapsed;
    }

    #[On('folderSelected')]
    public function setFolder($folderId)
    {
        $this->currentFolderId = $folderId;
        $this->selectedDocumentId = null;
        $this->reset('editingName', 'editingOwner', 'editingVisibility');
    }

    #[On('view-mode-updated')]
    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function selectDocument($documentId)
    {
        if ($this->selectedDocumentId == $documentId) {
            $this->selectedDocumentId = null;
            return;
        }

        $this->selectedDocumentId = $documentId;

        if (!Str::isUuid($documentId)) {
            return;
        }

        $doc = Document::find($documentId);
        if ($doc) {
            $this->editingName = $doc->name;
            $this->editingOwner = $doc->owner;
            $this->editingVisibility = $doc->visibility;
        }
    }

    public function updatedEditingName($value)
    {
        if ($this->selectedDocumentId && Str::isUuid($this->selectedDocumentId)) {
            Document::where('id', $this->selectedDocumentId)->update([
                'name' => $value,
                'slug' => Str::slug($value)
            ]);
        }
    }

    public function updatedEditingOwner($value)
    {
        if ($this->selectedDocumentId && Str::isUuid($this->selectedDocumentId)) {
            Document::where('id', $this->selectedDocumentId)->update(['owner' => $value]);
        }
    }

    public function toggleVisibility()
    {
        if ($this->selectedDocumentId && Str::isUuid($this->selectedDocumentId)) {
            $doc = Document::find($this->selectedDocumentId);
            $newVisibility = $doc->visibility === 'public' ? 'private' : 'public';
            $doc->update(['visibility' => $newVisibility]);
            $this->editingVisibility = $newVisibility;
        }
    }

    public function deleteDocument($id)
    {
        if (!Str::isUuid($id)) {
            return; // Invalid UUID, silently ignore
        }

        $doc = Document::find($id);
        if ($doc) {
            if (Storage::disk('document_public')->exists($doc->file_path)) {
                Storage::disk('document_public')->delete($doc->file_path);
            }
            $doc->delete();
            if ($this->selectedDocumentId == $id) {
                $this->selectedDocumentId = null;
            }
            $this->dispatch('ts-toast', type: 'success', text: 'Document deleted');
        }
    }

    public function updatedUploads()
    {
        if (!$this->currentFolderId) {
            $this->dispatch('ts-toast', type: 'error', text: 'Select a folder first');
            $this->reset('uploads');
            return;
        }

        $folder = Folder::find($this->currentFolderId);
        if (!$folder)
            return;

        foreach ($this->uploads as $file) {
            $fileName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();

            // Sanitize filename to avoid path traversal or issues
            $safeFileName = Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '.' . $extension;
            $path = 'documents/' . ($folder->name ? Str::slug($folder->name) . '/' : '') . $safeFileName;

            // Move file to permanent storage using legacy-compatible disk
            $file->storeAs(dirname($path), basename($path), ['disk' => 'document_public']);

            Document::create([
                'name' => $fileName,
                'original_name' => $fileName,
                'extension' => $extension,
                'file_path' => $path,
                'size' => $fileSize,
                'folder_id' => $this->currentFolderId,
                'visibility' => 'public',
                'owner' => Auth::id(),
                'date' => now(),
            ]);
        }

        $this->reset('uploads');
        $this->dispatch('ts-toast', type: 'success', text: 'Files uploaded successfully');
    }

    public function openShareModal()
    {
        if ($this->selectedDocumentId) {
            $doc = Document::find($this->selectedDocumentId);
            $this->shareName = $doc->name;
            $this->shareVisibility = $doc->visibility;
            $this->shareUrl = url("/document/share/{$doc->id}/" . Str::random(32));
        } elseif ($this->currentFolderId) {
            $folder = Folder::find($this->currentFolderId);
            $this->shareName = $folder->name;
            $this->shareVisibility = 'public';
            $this->shareUrl = url("/folder/share/{$this->currentFolderId}/" . Str::random(32));
        }

        $this->showShareModal = true;
    }

    public function confirmShare()
    {
        $token = (string) Str::afterLast($this->shareUrl, '/');
        $sharedId = $this->selectedDocumentId ?: $this->currentFolderId;
        $slug = $this->selectedDocumentId ? 'document' : 'folder';

        ShareDocument::create([
            'shared_id' => $sharedId,
            'name' => $this->shareName,
            'token' => $token,
            'url' => $this->shareUrl,
            'slug' => $slug,
            'visibility' => $this->shareVisibility,
            'share_type' => $this->selectedDocumentId ? Document::class : Folder::class,
            'share_id' => $sharedId,
            'user_type' => User::class,
            'user_id' => Auth::id() ?: 1,
        ]);

        $this->showShareModal = false;
        $this->dispatch('ts-toast', type: 'success', text: 'Shared successfully');
    }

    public function submitFileRequest()
    {
        $this->validate([
            'requestName' => 'required|string|max:255',
            'requestTo' => 'required|email|max:255',
            'requestNote' => 'nullable|string|max:1000',
        ]);

        if (!$this->currentFolderId) {
            $this->dispatch('ts-toast', type: 'error', text: 'Select a folder first');
            return;
        }

        DB::beginTransaction();
        try {
            $request = FileRequest::create([
                'name' => $this->requestName,
                'request_to' => $this->requestTo,
                'folder_id' => $this->currentFolderId,
                'note' => $this->requestNote,
                'due_date_in_word' => $this->requestDueDate,
            ]);

            // Create placeholder
            Document::create([
                'name' => $this->requestName,
                'original_name' => $this->requestName,
                'folder_id' => $this->currentFolderId,
                'date' => now(),
                'file_path' => 'img/empty-upload.jpg',
                'extension' => 'jpg',
                'owner' => Auth::id(),
            ]);

            SendFileRequestEmail::dispatch($request);
            DB::commit();

            $this->reset('requestName', 'requestTo', 'requestNote', 'requestDueDate', 'showRequestModal');
            $this->dispatch('ts-toast', type: 'success', text: 'File request sent');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('ts-toast', type: 'error', text: 'Failed to send request');
        }
    }

    public function getIconFor($ext)
    {
        $iconMap = [
            'pdf' => 'fa-file-pdf text-red-500',
            'doc' => 'fa-file-word text-blue-500',
            'docx' => 'fa-file-word text-blue-500',
            'xls' => 'fa-file-excel text-green-500',
            'xlsx' => 'fa-file-excel text-green-500',
            'ppt' => 'fa-file-powerpoint text-orange-500',
            'pptx' => 'fa-file-powerpoint text-orange-500',
            'zip' => 'fa-file-zipper text-violet-500',
            'rar' => 'fa-file-zipper text-violet-500',
            'mp3' => 'fa-file-audio text-cyan-500',
            'mp4' => 'fa-file-video text-indigo-500',
            'txt' => 'fa-file-lines text-slate-400',
            'jpg' => 'fa-file-image text-pink-500',
            'jpeg' => 'fa-file-image text-pink-500',
            'png' => 'fa-file-image text-pink-500',
            'svg' => 'fa-file-image text-pink-500',
        ];

        return $iconMap[strtolower((string) $ext)] ?? 'fa-file text-slate-300';
    }

    public function render()
    {
        $validFolderId = $this->currentFolderId && Str::isUuid($this->currentFolderId) ? $this->currentFolderId : null;
        $validDocId = $this->selectedDocumentId && Str::isUuid($this->selectedDocumentId) ? $this->selectedDocumentId : null;

        $documents = Document::query()
            ->when($validFolderId, fn($q) => $q->where('folder_id', $validFolderId))
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->with(['tags', 'ownerUser'])
            ->latest()
            ->get();

        $currentFolder = $validFolderId ? Folder::with('categories.tags')->find($validFolderId) : null;
        $selectedDoc = $validDocId ? Document::with(['tags', 'ownerUser'])->find($validDocId) : null;

        $owners = User::get(['id', 'name', 'email']);

        $allFolders = Folder::orderBy('name')->get(['id', 'name']);

        return view('livewire.documents.document-browser', [
            'documents' => $documents,
            'currentFolder' => $currentFolder,
            'selectedDoc' => $selectedDoc,
            'owners' => $owners,
            'allFolders' => $allFolders,
        ]);
    }
}
