<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Folder;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreDocumentRequest;
use App\Services\DocumentService;


class DocumentController extends Controller
{

    public function __construct(protected DocumentService $documentService)
    {
    }


    public function index()
    {
        $this->authorize('viewAny', Document::class);
        return view('documents.index');
    }

    public function sendDocumentEmail(Request $request)
    {
        $this->authorize('create', Document::class);
        $notifications = $this->documentService->setSendDocumentEmail($request);
        $view = view('documents.comments', compact('notifications'))->render();
        return response()->json(['message' => 'Email sent successfully!', 'html' => $view]);
    }

    public function getDocumentComments(Request $request)
    {
        $this->authorize('viewAny', Document::class);
        $notifications = $this->documentService->getDocumentNotifications($request->document_id);
        $view = view('documents.comments', compact('notifications'))->render();
        return response()->json(['html' => $view]);
    }
}
