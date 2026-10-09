<?php

namespace App\Http\Controllers;

use App\Http\LibraryAccess;
use App\Http\Requests\ResourcePasswordRequest;
use App\Http\Requests\UnlockResourceRequest;
use App\Models\File;
use Illuminate\Http\RedirectResponse;

class FilePasswordController extends Controller
{
    public function update(ResourcePasswordRequest $request, File $file, LibraryAccess $access): RedirectResponse
    {
        $access->authorizeOwner($file, $request->user());
        $locked = $access->firstLocked($file, $request);
        if ($locked !== null) {
            abort_unless($locked->is($file), 423, 'Unlock the parent folder first.');
            $access->unlock($file, $request, $request->validated('current_password') ?? '');
        }
        $password = $request->validated('password');
        $file->update(['password_hash' => $password, 'password_changed_at' => now()]);
        if ($password !== null) {
            $access->unlock($file, $request, $password);
        }

        return back()->with('status', $password !== null ? 'Password protection updated.' : 'Password protection removed.');
    }

    public function unlock(UnlockResourceRequest $request, File $file, LibraryAccess $access): RedirectResponse
    {
        $access->authorizeFile($file, $request->user());
        $locked = $access->firstLocked($file, $request) ?? $file;
        $access->unlock($locked, $request, $request->validated('password'));

        return to_route('app.files.show', $file->uuid)->with('status', 'Unlocked for 30 minutes.');
    }
}
