<?php

namespace App\Http;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FileViewer
{
    public function __construct(private LibraryAccess $access) {}

    /** @return array<string, mixed> */
    public function data(Request $request, File $file): array
    {
        $file->loadMissing('folder');
        $media = File::where('user_id', $file->user_id)->where('folder_id', $file->folder_id)
            ->where('status', 'ready')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif', 'video/mp4', 'video/webm']);
        if ($file->user_id !== $request->user()->id) {
            $media->whereHas('shareLinks', fn ($links) => $links->where('recipient_user_id', $request->user()->id)
                ->whereNull('revoked_at')->whereNull('password_hash')
                ->where(fn ($expiry) => $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now())));
        }
        $isMedia = in_array($file->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif', 'video/mp4', 'video/webm']);
        $destinations = $file->user_id === $request->user()->id
            ? $request->user()->folders()->where(fn ($folders) => $folders->whereNull('system_key')->orWhere('system_key', '!=', 'trash'))->orderBy('name')->get()->filter(fn (Folder $folder): bool => $this->access->firstLocked($folder, $request) === null)
            : collect();

        return [
            'file' => $file,
            'currentFolder' => $file->user_id === $request->user()->id ? $file->folder : null,
            'previousFile' => $isMedia ? $this->neighbor(clone $media, $file, $request, true) : null,
            'nextFile' => $isMedia ? $this->neighbor(clone $media, $file, $request, false) : null,
            'destinations' => $destinations,
        ];
    }

    private function neighbor(Builder $query, File $file, Request $request, bool $previous): ?File
    {
        $comparison = $previous ? '>' : '<';
        $direction = $previous ? 'asc' : 'desc';
        $query->where(fn (Builder $items) => $items->where('created_at', $comparison, $file->created_at)
            ->orWhere(fn (Builder $tie) => $tie->where('created_at', $file->created_at)->where('id', $comparison, $file->id)))
            ->orderBy('created_at', $direction)->orderBy('id', $direction);
        foreach ($query->cursor() as $candidate) {
            if ($this->access->firstLocked($candidate, $request) === null) {
                return $candidate;
            }
        }

        return null;
    }
}
