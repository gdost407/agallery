<?php

namespace App\Http\Controllers;

use App\Http\LibraryAccess;
use App\Http\Requests\ResourcePasswordRequest;
use App\Http\Requests\UnlockResourceRequest;
use App\Models\Folder;
use Illuminate\Http\RedirectResponse;

class FolderPasswordController extends Controller
{
    public function update(ResourcePasswordRequest $request, Folder $folder, LibraryAccess $access): RedirectResponse
    {
        $access->authorizeOwner($folder, $request->user());
        $locked = $access->firstLocked($folder, $request);
        if ($locked !== null) {
            abort_unless($locked->is($folder), 423, 'Unlock the parent folder first.');
            $access->unlock($folder, $request, $request->validated('current_password') ?? '');
        }
        $password = $request->validated('password');
        $folder->update(['password_hash' => $password, 'password_changed_at' => now()]);
        if ($password !== null) {
            $access->unlock($folder, $request, $password);
        }

        return back()->with('status', $password !== null ? 'Password protection updated.' : 'Password protection removed.');
    }

    public function unlock(UnlockResourceRequest $request, Folder $folder, LibraryAccess $access): RedirectResponse
    {
        $access->authorizeOwner($folder, $request->user());
        $access->unlock($folder, $request, $request->validated('password'));

        return to_route('app.folders.show', $folder->uuid)->with('status', 'Unlocked for 30 minutes.');
    }
}
