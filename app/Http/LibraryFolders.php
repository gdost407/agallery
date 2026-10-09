<?php

namespace App\Http;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LibraryFolders
{
    public const DEFAULTS = ['image' => 'Image', 'video' => 'Video', 'document' => 'Document', 'trash' => 'Trash'];

    public function ensure(User $user): void
    {
        DB::transaction(function () use ($user): void {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            foreach (self::DEFAULTS as $key => $name) {
                $user->folders()->firstOrCreate(['system_key' => $key], ['name' => $name]);
            }
        });
    }

    public function destination(User $user, string $category, ?Folder $folder = null): Folder
    {
        if ($folder !== null) {
            if ($folder->user_id !== $user->id || $folder->system_key === 'trash' || $folder->trashed()) {
                throw ValidationException::withMessages(['folder_id' => 'Choose an available folder. Trash only contains deleted files.']);
            }

            return $folder;
        }
        $this->ensure($user);

        return $user->folders()->where('system_key', in_array($category, ['image', 'video'], true) ? $category : 'document')->firstOrFail();
    }

    public static function storedName(int $userId, string $category, string $extension): string
    {
        $prefix = match ($category) {
            'image' => 'IMG', 'video' => 'VID', 'document' => 'DOC', default => 'FILE'
        };
        $extension = preg_match('/^[a-z0-9]{1,32}$/i', $extension) ? '.'.strtolower($extension) : '';

        return $prefix.$userId.now()->format('YmdHisv').'_'.Str::random(12).$extension;
    }
}
