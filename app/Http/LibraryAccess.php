<?php

namespace App\Http;

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LibraryAccess
{
    public function authorizeOwner(File|Folder $resource, User $user): void
    {
        abort_unless($resource->user_id === $user->id, 404);
    }

    public function authorizeFile(File $file, User $user, bool $download = false): void
    {
        if ($file->user_id === $user->id) {
            return;
        }
        $links = $file->shareLinks()->where('recipient_user_id', $user->id)
            ->whereNull('revoked_at')->whereNull('password_hash')
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()));
        if ($download) {
            $links->where('allow_download', true);
        }
        abort_unless($links->exists(), 404);
    }

    /** @return list<Folder> */
    public function ancestors(File|Folder $resource, Request $request): array
    {
        $folderId = $resource instanceof File ? $resource->folder_id : $resource->parent_id;
        $folders = [];
        $visited = [];
        $cachedFolders = $request->attributes->get('library_folders', []);
        while ($folderId !== null) {
            abort_if(isset($visited[$folderId]), 404);
            $visited[$folderId] = true;
            $folder = $cachedFolders[$folderId] ??= Folder::findOrFail($folderId);
            abort_unless($folder->user_id === $resource->user_id, 404);
            $folders[] = $folder;
            $folderId = $folder->parent_id;
        }

        $request->attributes->set('library_folders', $cachedFolders);

        return array_reverse($folders);
    }

    public function firstLocked(File|Folder $resource, Request $request): File|Folder|null
    {
        foreach ([...$this->ancestors($resource, $request), $resource] as $item) {
            if ($item->password_hash !== null && ! $this->isUnlocked($item, $request)) {
                return $item;
            }
        }

        return null;
    }

    public function ensureUnlocked(File|Folder $resource, Request $request): void
    {
        abort_if($this->firstLocked($resource, $request) !== null, 423, 'Unlock the protected file or folder first.');
    }

    public function unlock(File|Folder $resource, Request $request, string $password): void
    {
        if ($resource->password_hash !== null && ! Hash::check($password, $resource->password_hash)) {
            throw ValidationException::withMessages(['password' => 'The password is incorrect.']);
        }
        $request->session()->put($this->sessionKey($resource, $request), [
            'fingerprint' => hash('sha256', $resource->password_hash ?? ''),
            'expires_at' => now()->addMinutes(30)->timestamp,
        ]);
    }

    private function isUnlocked(File|Folder $resource, Request $request): bool
    {
        $grant = $request->session()->get($this->sessionKey($resource, $request));

        return is_array($grant)
            && ($grant['expires_at'] ?? 0) > now()->timestamp
            && hash_equals(hash('sha256', $resource->password_hash ?? ''), $grant['fingerprint'] ?? '');
    }

    private function sessionKey(File|Folder $resource, Request $request): string
    {
        return 'library_unlocks.'.$request->user()->id.'.'.$resource->getTable().'.'.$resource->id;
    }
}
