<?php

namespace App\Http\Controllers;

use App\Http\LibraryAccess;
use App\Http\Requests\UploadFilesRequest;
use App\Http\UploadFiles;
use App\Models\File;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController extends Controller
{
    public function __construct(private LibraryAccess $access) {}

    public function store(UploadFilesRequest $request, UploadFiles $uploads): RedirectResponse
    {
        return $uploads->handle($request);
    }

    public function show(Request $request, File $file): View
    {
        $this->access->authorizeFile($file, $request->user());
        $locked = $this->access->firstLocked($file, $request);
        if ($locked !== null) {
            return view('app.pages.unlock', [
                'resource' => $locked, 'currentFolder' => null,
                'unlockAction' => route('app.files.unlock', $file->uuid),
            ]);
        }

        return view('app.pages.file', ['file' => $file, 'currentFolder' => null]);
    }

    public function content(Request $request, File $file): BinaryFileResponse
    {
        $this->access->authorizeFile($file, $request->user());
        $this->access->ensureUnlocked($file, $request);
        abort_unless($file->disk === 'local' && $file->status === 'ready', 404);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($file->storage_key), 404);
        $safeInline = in_array($file->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif', 'video/mp4', 'video/webm', 'audio/mpeg', 'audio/ogg', 'application/pdf']);
        if (! $safeInline) {
            return $this->download($request, $file);
        }

        return response()->file($disk->path($file->storage_key), [
            'Content-Type' => $file->mime_type,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "sandbox; default-src 'none'",
        ])->setPrivate();
    }

    public function download(Request $request, File $file): BinaryFileResponse
    {
        $this->access->authorizeFile($file, $request->user(), true);
        $this->access->ensureUnlocked($file, $request);
        abort_unless($file->disk === 'local' && $file->status === 'ready', 404);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($file->storage_key), 404);

        return response()->download($disk->path($file->storage_key), $file->original_name, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ])->setPrivate();
    }

    public function star(Request $request, File $file): RedirectResponse
    {
        $this->access->authorizeOwner($file, $request->user());
        $this->access->ensureUnlocked($file, $request);
        $file->update(['starred_at' => $file->starred_at === null ? now() : null]);

        return back()->with('status', 'Star updated.');
    }

    public function destroy(Request $request, File $file): RedirectResponse
    {
        $this->access->authorizeOwner($file, $request->user());
        $this->access->ensureUnlocked($file, $request);
        $file->delete();

        return to_route('app.dashboard')->with('status', 'File moved to trash. It still counts towards storage.');
    }

    public function restore(Request $request, File $file): RedirectResponse
    {
        $this->access->authorizeOwner($file, $request->user());
        abort_unless($file->trashed(), 404);
        $file->restore();

        return back()->with('status', 'File restored.');
    }
}
