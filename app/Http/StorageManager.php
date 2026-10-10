<?php

namespace App\Http;

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class StorageManager
{
    /** @param Collection<int, File> $files */
    public function deleteFiles(User $user, Collection $files, Request $request): void
    {
        $access = app(LibraryAccess::class);
        foreach ($files as $file) {
            $access->authorizeOwner($file, $user);
            $access->ensureUnlocked($file, $request);
        }
        foreach ($files as $file) {
            DB::transaction(function () use ($user, $file): void {
                $account = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                $stored = $account->files()->withTrashed()->whereKey($file->id)->lockForUpdate()->first();
                if ($stored === null) {
                    return;
                }
                try {
                    $disk = Storage::disk($stored->disk);
                    if (! $disk->delete($stored->storage_key) || ! Storage::disk('local')->deleteDirectory('thumbnails/'.$stored->uuid)) {
                        throw new RuntimeException('Stored file cleanup failed.');
                    }
                } catch (Throwable $exception) {
                    report($exception);
                    throw ValidationException::withMessages(['files' => 'The file could not be permanently deleted. Please try again.']);
                }
                $stored->storageUsageEvents()->delete();
                $stored->shareLinks()->delete();
                $stored->forceDelete();
                $account->used_storage_bytes = $this->used($account);
                $account->save();
            });
        }
    }

    public function deleteFolder(Folder $folder, Request $request): void
    {
        $access = app(LibraryAccess::class);
        $access->authorizeOwner($folder, $request->user());
        $access->ensureUnlocked($folder, $request);
        abort_if($folder->system_key !== null, 422, 'Default folders cannot be deleted.');
        $all = $request->user()->folders()->withTrashed()->orderBy('id')->get();
        $request->attributes->set('library_folders', $all->keyBy('id')->all());
        $pending = [$folder->id];
        $ids = [];
        for ($index = 0; $index < count($pending); $index++) {
            $id = $pending[$index];
            if (in_array($id, $ids, true)) {
                continue;
            }
            $ids[] = $id;
            foreach ($all->where('parent_id', $id) as $child) {
                $access->ensureUnlocked($child, $request);
                $pending[] = $child->id;
            }
        }
        $files = $request->user()->files()->withTrashed()->whereIn('folder_id', $ids)->get();
        $this->deleteFiles($request->user(), $files, $request);
        DB::transaction(function () use ($request, $ids): void {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            foreach (array_reverse($ids) as $id) {
                $item = $request->user()->folders()->withTrashed()->whereKey($id)->firstOrFail();
                $item->shareLinks()->delete();
                $item->forceDelete();
            }
        });
    }

    public function capacity(User $user): int
    {
        $additional = $user->storageSubscriptions()->where('status', 'active')
            ->whereNull('ended_at')
            ->where('current_period_starts_at', '<=', now())
            ->where('current_period_ends_at', '>', now())
            ->sum('additional_storage_bytes');

        return $user->free_storage_bytes + (int) $additional;
    }

    public function used(User $user): int
    {
        return (int) $user->files()->withTrashed()->sum('size_bytes');
    }

    /** @return array{used: int, total: int, remaining: int, percent: float, used_label: string, total_label: string, remaining_label: string} */
    public function summary(User $user): array
    {
        $used = $this->used($user);
        $total = $this->capacity($user);
        $remaining = max(0, $total - $used - $user->reserved_storage_bytes);

        return [
            'used' => $used, 'total' => $total, 'remaining' => $remaining,
            'percent' => $total > 0 ? min(100, round($used / $total * 100, 1)) : 100.0,
            'used_label' => $this->formatBytes($used),
            'total_label' => $this->formatBytes($total),
            'remaining_label' => $this->formatBytes($remaining),
        ];
    }

    public function formatBytes(int $bytes): string
    {
        foreach (['GB' => 1000000000, 'MB' => 1000000, 'KB' => 1000] as $unit => $divisor) {
            if ($bytes >= $divisor) {
                return rtrim(rtrim(number_format($bytes / $divisor, 2, '.', ''), '0'), '.').' '.$unit;
            }
        }

        return $bytes.' B';
    }

    /** @param list<UploadedFile> $uploads */
    public function upload(User $user, array $uploads, ?Folder $folder, bool $sync = false): void
    {
        $paths = [];
        try {
            DB::transaction(function () use ($user, $uploads, $folder, $sync, &$paths): void {
                $account = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                if ($sync) {
                    $checksums = [];
                    $uploads = array_values(array_filter($uploads, function (UploadedFile $upload) use ($account, &$checksums): bool {
                        $checksum = hash_file('sha256', $upload->getRealPath());
                        if (isset($checksums[$checksum]) || $account->files()->withTrashed()->where('status', 'ready')->where('checksum_sha256', $checksum)->exists()) {
                            return false;
                        }
                        $checksums[$checksum] = true;

                        return true;
                    }));
                }
                $used = $this->used($account);
                $incoming = array_sum(array_map(fn (UploadedFile $upload): int => $upload->getSize(), $uploads));
                if ($incoming > max(0, $this->capacity($account) - $used - $account->reserved_storage_bytes)) {
                    throw ValidationException::withMessages(['files' => 'Not enough storage. Choose smaller files or buy more storage.']);
                }
                foreach ($uploads as $upload) {
                    $mime = $upload->getMimeType() ?? 'application/octet-stream';
                    $extension = mb_strtolower($upload->getClientOriginalExtension());
                    $category = $this->category($mime, $extension);
                    $destination = app(LibraryFolders::class)->destination($account, $category, $folder);
                    $storedName = LibraryFolders::storedName($account->id, $category, $extension);
                    $key = 'library/'.$account->id.'/'.$destination->uuid.'/'.$storedName;
                    $paths[] = $key;
                    $stream = fopen($upload->getRealPath(), 'rb');
                    try {
                        $stored = Storage::disk('local')->put($key, $stream);
                    } finally {
                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                    }
                    if (! $stored) {
                        throw new RuntimeException('The upload could not be saved.');
                    }
                    $file = $account->files()->create([
                        'folder_id' => $destination->id,
                        'stored_name' => $storedName,
                        'original_name' => basename(str_replace('\\', '/', $upload->getClientOriginalName())),
                        'extension' => $extension,
                        'mime_type' => $mime,
                        'category' => $this->category($mime, $extension),
                        'disk' => 'local', 'storage_key' => $key,
                        'size_bytes' => $upload->getSize(),
                        'checksum_sha256' => hash_file('sha256', $upload->getRealPath()),
                        'status' => 'ready',
                    ]);
                    $account->storageUsageEvents()->create([
                        'file_id' => $file->id, 'operation' => 'upload',
                        'bytes_delta' => $file->size_bytes, 'idempotency_key' => (string) Str::uuid(),
                    ]);
                }
                $account->used_storage_bytes = $used + $incoming;
                $account->save();
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($paths);
            throw $exception;
        }
    }

    public function copy(File $source, User $user, ?Folder $folder): File
    {
        $folder = app(LibraryFolders::class)->destination($user, $source->category, $folder);
        $storedName = LibraryFolders::storedName($user->id, $source->category, $source->extension);
        $key = 'library/'.$user->id.'/'.$folder->uuid.'/'.$storedName;
        try {
            return DB::transaction(function () use ($source, $user, $folder, $key, $storedName): File {
                $account = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                $used = $this->used($account);
                if ($source->size_bytes > max(0, $this->capacity($account) - $used - $account->reserved_storage_bytes)) {
                    throw ValidationException::withMessages(['folder_id' => 'Not enough storage to copy this file.']);
                }
                $disk = Storage::disk('local');
                if (! $disk->copy($source->storage_key, $key)) {
                    throw new RuntimeException('The file could not be copied.');
                }
                $copy = $source->replicate(['uuid', 'starred_at', 'deleted_at']);
                $copy->folder_id = $folder?->id;
                $copy->storage_key = $key;
                $copy->stored_name = $storedName;
                $copy->save();
                $account->storageUsageEvents()->create([
                    'file_id' => $copy->id, 'operation' => 'upload',
                    'bytes_delta' => $copy->size_bytes, 'idempotency_key' => (string) Str::uuid(),
                ]);
                $account->used_storage_bytes = $used + $copy->size_bytes;
                $account->save();

                return $copy;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($key);
            throw $exception;
        }
    }

    public function category(string $mime, string $extension): string
    {
        if (str_starts_with($mime, 'image/') && $mime !== 'image/svg+xml') {
            return 'image';
        }
        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }
        if (str_starts_with($mime, 'audio/')) {
            return 'audio';
        }
        if (str_starts_with($mime, 'text/') || in_array($extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp', 'rtf', 'csv', 'txt'])) {
            return 'document';
        }

        return in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz']) ? 'archive' : 'other';
    }
}
