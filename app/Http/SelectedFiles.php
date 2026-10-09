<?php

namespace App\Http;

use App\Http\Requests\SelectedFilesRequest;
use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;
use ZipArchive;

class SelectedFiles
{
    public function __construct(private LibraryAccess $access, private StorageManager $storage) {}

    /** @return Collection<int, File> */
    public function files(SelectedFilesRequest $request, bool $lock = false): Collection
    {
        $query = $request->user()->files()->where('status', 'ready')->whereIn('uuid', $request->validated('files'))->orderBy('id');
        if ($lock) {
            $query->lockForUpdate();
        }
        $files = $query->get();
        abort_unless($files->count() === count($request->validated('files')), 404);
        foreach ($files as $file) {
            $this->access->ensureUnlocked($file, $request);
        }

        return $files;
    }

    public function transfer(SelectedFilesRequest $request): void
    {
        $paths = [];
        try {
            DB::transaction(function () use ($request, &$paths): void {
                $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                $files = $this->files($request, true);
                $folder = $request->validated('folder_id') !== null
                    ? $user->folders()->whereKey($request->validated('folder_id'))->lockForUpdate()->firstOrFail() : null;
                if ($folder !== null) {
                    $this->access->ensureUnlocked($folder, $request);
                }
                $copying = $request->validated('action') === 'copy';
                if ($copying && (int) $files->sum('size_bytes') > max(0, $this->storage->capacity($user) - $this->storage->used($user) - $user->reserved_storage_bytes)) {
                    throw ValidationException::withMessages(['folder_id' => 'Not enough storage to copy the selected files.']);
                }
                foreach ($files as $file) {
                    abort_unless($file->disk === 'local' && Storage::disk('local')->exists($file->storage_key), 404);
                }
                foreach ($files as $file) {
                    if ($copying) {
                        $paths[] = $this->storage->copy($file, $user, $folder)->storage_key;
                    } else {
                        $file->update(['folder_id' => $folder?->id]);
                    }
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($paths);
            throw $exception;
        }
    }

    /** @return list<array{name: string, url: string, id: int}> */
    public function share(SelectedFilesRequest $request): array
    {
        return DB::transaction(function () use ($request): array {
            $files = $this->files($request, true);
            $links = [];
            foreach ($files as $file) {
                $token = Str::random(64);
                $link = $request->user()->fileShareLinks()->create([
                    'file_id' => $file->id, 'token_hash' => hash('sha256', $token), 'allow_download' => true,
                ]);
                $links[] = ['name' => $file->original_name, 'url' => route('share.show', $token), 'id' => $link->id];
            }

            return $links;
        });
    }

    public function download(SelectedFilesRequest $request): BinaryFileResponse
    {
        $files = $this->files($request);
        $disk = Storage::disk('local');
        foreach ($files as $file) {
            abort_unless($file->disk === 'local' && $disk->exists($file->storage_key), 404);
        }
        $headers = ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'];
        if ($files->count() === 1) {
            $file = $files->first();

            return response()->download($disk->path($file->storage_key), $file->original_name, $headers)->setPrivate();
        }
        if (! class_exists(ZipArchive::class)) {
            throw ValidationException::withMessages(['files' => 'ZIP downloads are unavailable on this server. Download one file at a time.']);
        }
        $disk->makeDirectory('downloads');
        $path = tempnam($disk->path('downloads'), 'selection-');
        if ($path === false) {
            throw ValidationException::withMessages(['files' => 'The download could not be prepared.']);
        }
        $zip = new ZipArchive;
        $opened = false;
        try {
            if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
                throw ValidationException::withMessages(['files' => 'The download could not be prepared.']);
            }
            $opened = true;
            $names = [];
            foreach ($files as $file) {
                $base = preg_replace('/[\x00-\x1f\x7f\/\\\\]/u', '_', $file->original_name);
                $base = in_array($base, ['', '.', '..'], true) ? 'file-'.$file->id : $base;
                $name = $base;
                $suffix = 1;
                while (isset($names[mb_strtolower($name)])) {
                    $name = $suffix++.'-'.$base;
                }
                $names[mb_strtolower($name)] = true;
                if (! $zip->addFile($disk->path($file->storage_key), $name)) {
                    throw ValidationException::withMessages(['files' => 'The download could not be prepared.']);
                }
                $zip->setCompressionName($name, ZipArchive::CM_STORE);
            }
            $closed = $zip->close();
            $opened = false;
            if (! $closed) {
                throw ValidationException::withMessages(['files' => 'The download could not be prepared.']);
            }
        } catch (Throwable $exception) {
            if ($opened) {
                @$zip->close();
            }
            @unlink($path);
            throw $exception;
        }

        return response()->download($path, 'AGallery-selected-files.zip', $headers)->setPrivate()->deleteFileAfterSend(true);
    }
}
