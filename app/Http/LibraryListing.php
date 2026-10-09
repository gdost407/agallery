<?php

namespace App\Http;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LibraryListing
{
    public function __construct(private LibraryAccess $access, private LibraryAnalytics $analytics) {}

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
        $ownerIds = $page === 'shared' ? (clone $query)->distinct()->pluck('user_id')->all() : [$user->id];
        $folderTree = Folder::withTrashed()->whereIn('user_id', $ownerIds)->get(['id', 'parent_id', 'password_hash']);
        $hiddenFolderIds = $this->protectedFolderIds($folderTree);
        $insidePrivateFolder = $folder !== null && in_array($folder->id, $hiddenFolderIds, true);
        if (! $insidePrivateFolder) {
            $query->where(fn ($files) => $files->whereNull('folder_id')->orWhereNotIn('folder_id', $hiddenFolderIds));
        }
        if ($page === 'private') {
            $query->whereRaw('1 = 0');
        }
        if ($folder !== null || $page === 'folders') {
            $query->where('folder_id', $folder?->id);
        }
        if ($request->filled('q')) {
            $query->where('original_name', 'like', '%'.mb_substr($request->string('q')->toString(), 0, 255).'%');
        }
        $files = $query->latest()->orderByDesc('id')->paginate(48)->withQueryString();
        $folders = in_array($page, ['home', 'folders'], true)
            ? $user->folders()->where('parent_id', $folder?->id)->orderBy('name')->get()
            : collect();
        if ($page === 'private') {
            $privateRoots = $folderTree->filter(fn (Folder $item): bool => $item->password_hash !== null
                && ! in_array($item->parent_id, $hiddenFolderIds, true))->pluck('id');
            $folders = $user->folders()->whereIn('id', $privateRoots)->orderBy('name')->get();
        } elseif (! $insidePrivateFolder) {
            $folders = $folders->reject(fn (Folder $item): bool => in_array($item->id, $hiddenFolderIds, true));
        }
        $lockedFiles = [];
        foreach ($files as $file) {
            $lockedFiles[$file->id] = $this->access->firstLocked($file, $request) !== null;
        }

        $lockedFolders = [];
        foreach ($folders as $item) {
            $lockedFolders[$item->id] = $this->access->firstLocked($item, $request) !== null;
        }

        $analytics = $page === 'home' && $folder === null
            ? $this->analytics->dashboard($user, $folderTree, $folders, $hiddenFolderIds) : null;
        $folderUsage = $analytics['folderUsage'] ?? (in_array($page, ['home', 'folders', 'private'], true)
            ? $this->analytics->folderUsage($user, $folderTree) : []);

        $selectionFolders = ! in_array($page, ['shared', 'trash', 'private'], true)
            ? $user->folders()->orderBy('name')->get()->filter(fn (Folder $item): bool => $this->access->firstLocked($item, $request) === null)
            : collect();

        return view('app.pages.'.($folder !== null ? 'home' : $page), [
            'files' => $files, 'folders' => $folders, 'currentFolder' => $folder,
            'lockedFiles' => $lockedFiles,
            'lockedFolders' => $lockedFolders,
            'analytics' => $analytics, 'folderUsage' => $folderUsage,
            'currentFolderUsage' => $folder !== null ? ($folderUsage[$folder->id] ?? null) : null,
            'selectionFolders' => $selectionFolders,
        ]);
    }

    /**
     * @param  Collection<int, Folder>  $folders
     * @return list<int>
     */
    private function protectedFolderIds(Collection $folders): array
    {
        $children = [];
        $pending = [];
        foreach ($folders as $item) {
            $children[$item->parent_id ?? 0][] = $item->id;
            if ($item->password_hash !== null) {
                $pending[] = $item->id;
            }
        }
        $hidden = [];
        for ($index = 0; $index < count($pending); $index++) {
            $id = $pending[$index];
            if (isset($hidden[$id])) {
                continue;
            }
            $hidden[$id] = true;
            foreach ($children[$id] ?? [] as $childId) {
                $pending[] = $childId;
            }
        }

        return array_keys($hidden);
    }
}
