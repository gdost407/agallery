<?php

namespace App\Http\Controllers;

use App\Http\LibraryAccess;
use App\Http\Requests\BulkDeleteFilesRequest;
use App\Http\Requests\TransferFileRequest;
use App\Http\StorageManager;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class FileActionController extends Controller
{
    public function __construct(private LibraryAccess $access, private StorageManager $storage) {}

    public function destroySelected(BulkDeleteFilesRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $files = $request->user()->files()->where('status', 'ready')->whereIn('uuid', $request->validated('files'))->orderBy('id')->lockForUpdate()->get();
            abort_unless($files->count() === count($request->validated('files')), 404);
            foreach ($files as $file) {
                $this->access->ensureUnlocked($file, $request);
            }
            foreach ($files as $file) {
                $file->delete();
            }
        });

        return back()->with('status', 'Selected files moved to trash. They still count towards storage.');
    }

    public function transfer(TransferFileRequest $request, File $file): RedirectResponse
    {
        $this->access->authorizeOwner($file, $request->user());
        $copying = $request->routeIs('app.files.copy');
        try {
            $result = DB::transaction(function () use ($request, $file, $copying): File {
                User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                $source = File::whereKey($file->id)->where('status', 'ready')->lockForUpdate()->firstOrFail();
                $this->access->ensureUnlocked($source, $request);
                $folder = $request->validated('folder_id') !== null
                    ? $request->user()->folders()->whereKey($request->validated('folder_id'))->lockForUpdate()->firstOrFail() : null;
                if ($folder !== null) {
                    $this->access->ensureUnlocked($folder, $request);
                }
                abort_unless($source->disk === 'local' && Storage::disk('local')->exists($source->storage_key), 404);
                if ($copying) {
                    return $this->storage->copy($source, $request->user(), $folder);
                }
                $source->update(['folder_id' => $folder?->id]);

                return $source;
            });
        } catch (ValidationException|HttpException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages(['folder_id' => 'The file could not be transferred. Please try again.']);
        }

        return to_route('app.files.show', $result->uuid)->with('status', $copying ? 'File copied.' : 'File moved.');
    }
}
