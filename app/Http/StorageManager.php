<?php

namespace App\Http;

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class StorageManager
{
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
    public function upload(User $user, array $uploads, ?Folder $folder): void
    {
        $paths = [];
        try {
            DB::transaction(function () use ($user, $uploads, $folder, &$paths): void {
                $account = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                $used = $this->used($account);
                $incoming = array_sum(array_map(fn (UploadedFile $upload): int => $upload->getSize(), $uploads));
                if ($incoming > max(0, $this->capacity($account) - $used - $account->reserved_storage_bytes)) {
                    throw ValidationException::withMessages(['files' => 'Not enough storage. Choose smaller files or buy more storage.']);
                }
                foreach ($uploads as $upload) {
                    $key = 'library/'.$account->id.'/'.Str::uuid();
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
                    $mime = $upload->getMimeType() ?? 'application/octet-stream';
                    $extension = mb_strtolower($upload->getClientOriginalExtension());
                    $file = $account->files()->create([
                        'folder_id' => $folder?->id,
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
        $key = 'library/'.$user->id.'/'.Str::uuid();
        try {
            return DB::transaction(function () use ($source, $user, $folder, $key): File {
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
