<?php

namespace App\Http;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LibraryListing
{
    public function __construct(private LibraryAccess $access) {}

    public function render(Request $request, string $page, ?Folder $folder = null): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:255']]);
        $user = $request->user();
        $query = $user->files()->where('status', 'ready');
        if ($page === 'shared') {
            $query = File::where('status', 'ready')->whereHas('shareLinks', fn ($links) => $links
                ->where('recipient_user_id', $user->id)->whereNull('revoked_at')->whereNull('password_hash')
                ->where(fn ($expiry) => $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now())));
        } elseif ($page === 'trash') {
            $query->onlyTrashed();
        } elseif ($page === 'starred') {
            $query->whereNotNull('starred_at');
        } elseif (in_array($page, ['photos', 'videos', 'documents'])) {
            $query->where('category', ['photos' => 'image', 'videos' => 'video', 'documents' => 'document'][$page]);
        }
        if ($folder !== null || $page === 'home') {
            $query->where('folder_id', $folder?->id);
        }
        if ($request->filled('q')) {
            $query->where('original_name', 'like', '%'.mb_substr($request->string('q')->toString(), 0, 255).'%');
        }
        $files = $query->latest()->orderByDesc('id')->paginate(48)->withQueryString();
        $folders = $page === 'home'
            ? $user->folders()->where('parent_id', $folder?->id)->orderBy('name')->get()
            : collect();
        $lockedFiles = [];
        foreach ($files as $file) {
            $lockedFiles[$file->id] = $this->access->firstLocked($file, $request) !== null;
        }

        return view('app.pages.'.($folder !== null ? 'home' : $page), [
            'files' => $files, 'folders' => $folders, 'currentFolder' => $folder,
            'lockedFiles' => $lockedFiles,
        ]);
    }
}
