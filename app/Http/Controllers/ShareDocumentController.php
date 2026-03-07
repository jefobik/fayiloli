<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Document;
use Illuminate\Http\Request;
use App\Models\ShareDocument;
use App\Models\User;

class ShareDocumentController extends Controller
{

    public function index()
    {
        // The shares management UI is not yet fully implemented.
        // Redirect to documents with a graceful message to avoid a 404.
        return redirect()->route('documents.index')
            ->with('info', 'Shares management dashboard is coming soon.');
    }

    function getSharedDocuments($slug, $sharedid, $token)
    {
        $shareDocument = ShareDocument::whereSlug($slug)->whereToken($token)->whereSharedId($sharedid)->first();

        abort_if(!$shareDocument, 404, 'Not Found');

        return view('shares.index', compact('shareDocument'));
    }


    function sharedDocuments(Request $request)
    {
        $validated = $request->validate([
            'shared_id' => 'required',
            'token' => 'required',
            'slug' => 'required',
            'url' => 'required',
            'name' => 'nullable',
            'valid_until' => 'nullable',
            'visibility' => 'nullable',
        ]);

        ShareDocument::create($validated +
            [
                'share_type' => $request->slug == 'folder' ? Folder::class : Document::class,
                'share_id' => $request->shared_id,
                'user_type' => User::class,
                'user_id' => auth()->id(),
            ]);

        return response()->json(['message' => 'shared successfully'], 200);
    }
}