<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Category;
use App\Models\Folder;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Tag::class);

        $tags    = Tag::orderBy('name')->paginate(25);
        $folders = Folder::with(['categories.tags', 'subfolders'])->get();

        return view('tags.index', compact('tags', 'folders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Tag::class);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTagRequest $request)
    {
        $this->authorize('create', Tag::class);

        if (!empty($request->parent_id)) {
            $folder = Folder::firstOrCreate(['name' => $request->folder_name, 'parent_id' => $request->parent_id]);
        } else {
            $folder = Folder::firstOrCreate(['name' => $request->folder_name]);
        }

        $category = Category::create(['name' => $request->category_name]);

        $folder->categories()->attach($category);

        if ($request->tags) {
            foreach ($request->tags as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $category->tags()->attach($tag);
            }
        }

        $folder->load('categories');
        $categories = $folder->categories;

        $view = view('folders.tags', compact('categories'))->render();

        return response()->json(['html' => $view]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        $this->authorize('view', $tag);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag)
    {
        $this->authorize('update', $tag);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $this->authorize('update', $tag);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        $this->authorize('delete', $tag);

        $tag->delete();

        return response()->json(['message' => 'Tag deleted successfully']);
    }

    // Controller logic for searching tags
    public function searchTags(Request $request)
    {
        $this->authorize('viewAny', Tag::class);

        $query = $request->input('query');
        $tags = Tag::where('name', 'like', "%$query%")->get();
        return response()->json(['tags' => $tags]);
    }

    // Controller logic for adding tags
    public function addTag(Request $request)
    {
        $this->authorize('create', Tag::class);

        $tagId = $request->input('tag_id');
        return response()->json(['message' => 'Tag added successfully']);
    }
}
