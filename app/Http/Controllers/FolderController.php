<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use ZipArchive;
use App\Models\Folder;
use Illuminate\Http\Request;
use App\Http\Requests\StoreFolderRequest;
use App\Services\FolderService;

class FolderController extends Controller
{

    public function __construct(protected FolderService $folderService)
    {
    }


    public function index()
    {
        $this->authorize('viewAny', Folder::class);

        $folders = Folder::with('categories', 'subfolders')->whereNull('parent_id')->get();

        return view('folders.create', compact('folders'));
    }

    public function create()
    {
        $this->authorize('create', Folder::class);

        $folders = Folder::with('categories', 'subfolders')->whereNull('parent_id')->get();

        return view('folders.create', compact('folders'));
    }


    public function store(StoreFolderRequest $request)
    {
        $this->authorize('create', Folder::class);

        $folders = $this->folderService->setStoreFolder($request);

        return response()->json(['html' => $folders]);
    }



    public function updateFolderPositions(Request $request)
    {
        $this->authorize('update', Folder::class);

        $this->folderService->setUpdateFolderPositions($request);

        return response()->json(['message' => 'Positions updated successfully for parent rows']);
    }


    public function updateFolderChildPositions(Request $request)
    {
        $this->authorize('update', Folder::class);

        $this->folderService->setUpdateFolderChildPositions($request);

        return response()->json(['message' => 'Positions updated successfully for child rows']);
    }



    public function fetchDetails(Request $request)
    {
        $this->authorize('viewAny', Folder::class);

        $request->validate([
            'folder_ids' => 'required|array'
        ]);

        $folders = Folder::with('documents')->whereIn('id', $request->folder_ids)->get();

        return response()->json(['folders' => $folders]);
    }



    public function downloadZip(Request $request)
    {
        $this->authorize('download', Folder::class);

        $request->validate([
            'folders' => 'required|array',
        ]);

        $zipFilePath = $this->folderService->setDownloadZip($request)['zipFilePath'];
        $zipFileName = $this->folderService->setDownloadZip($request)['zipFileName'];

        if ($zipFilePath) {
            return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);
        } else {
            return response()->json(['error' => 'Error generating zip file. The folder may be empty, and you cannot create a zip file from an empty folder.'], 400);
        }
    }


    public function deleteSelecetdFolder(Request $request)
    {
        $this->authorize('delete', Folder::class);

        $folders = Folder::whereIn('id', $request->folder_ids ?? [])->get();

        foreach ($folders as $key => $folder) {
            $folder->deleteFolder();
        }

        return response()->json(['html' => $this->getParentFolders(), 'message' => 'Folder and its related records deleted successfully'], 200);
    }


    public function getParentFolders()
    {
        $folders = Folder::with(['categories'])->whereNull('parent_id')->get();
        return view('folders.table', compact('folders'))->render();
    }
}
