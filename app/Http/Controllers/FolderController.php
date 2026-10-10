<?php

namespace App\Http\Controllers;

use App\Http\LibraryAccess;
use App\Http\LibraryListing;
use App\Http\Requests\StoreFolderRequest;
use App\Http\StorageManager;
use App\Models\Folder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FolderController extends Controller
{
    public function destroy(Request $request, Folder $folder, StorageManager $storage): RedirectResponse
    {
        $storage->deleteFolder($folder, $request);

        return to_route('app.folders')->with('status', 'Folder and its contents permanently deleted. Storage space released.');
    }

    public function store(StoreFolderRequest $request, LibraryAccess $access): RedirectResponse
    {
        $parent = $request->validated('parent_id') !== null ? Folder::findOrFail($request->validated('parent_id')) : null;
        if ($parent !== null) {
            abort_if($parent->system_key === 'trash', 422, 'Choose a folder outside Trash.');
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

    public function show(Request $request, Folder $folder, LibraryAccess $access, LibraryListing $listing): View|RedirectResponse
    {
        $access->authorizeOwner($folder, $request->user());
        if ($folder->system_key === 'trash') {
            return to_route('app.trash');
        }
        $locked = $access->firstLocked($folder, $request);
        if ($locked !== null) {
            return view('app.pages.unlock', ['resource' => $locked, 'currentFolder' => null]);
        }

        return $listing->render($request, 'home', $folder);
    }
}
