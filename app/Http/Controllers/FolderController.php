<?php

namespace App\Http\Controllers;

use App\Http\LibraryAccess;
use App\Http\LibraryListing;
use App\Http\Requests\StoreFolderRequest;
use App\Models\Folder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FolderController extends Controller
{
    public function store(StoreFolderRequest $request, LibraryAccess $access): RedirectResponse
    {
        $parent = $request->validated('parent_id') !== null ? Folder::findOrFail($request->validated('parent_id')) : null;
        if ($parent !== null) {
            $access->authorizeOwner($parent, $request->user());
            $access->ensureUnlocked($parent, $request);
        }
        $folder = $request->user()->folders()->create([
            'name' => $request->validated('name'), 'parent_id' => $parent?->id,
            'password_hash' => $request->validated('password'),
            'password_changed_at' => $request->validated('password') !== null ? now() : null,
        ]);

        return to_route('app.folders.show', $folder->uuid)->with('status', 'Folder created.');
    }

    public function show(Request $request, Folder $folder, LibraryAccess $access, LibraryListing $listing): View
    {
        $access->authorizeOwner($folder, $request->user());
        $locked = $access->firstLocked($folder, $request);
        if ($locked !== null) {
            return view('app.pages.unlock', ['resource' => $locked, 'currentFolder' => null]);
        }

        return $listing->render($request, 'home', $folder);
    }
}
