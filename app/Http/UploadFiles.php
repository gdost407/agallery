<?php

namespace App\Http;

use App\Http\Requests\UploadFilesRequest;
use App\Models\Folder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class UploadFiles
{
    public function __construct(private LibraryAccess $access, private StorageManager $storage) {}

    public function handle(UploadFilesRequest $request): RedirectResponse
    {
        $folder = $request->validated('folder_id') !== null ? Folder::findOrFail($request->validated('folder_id')) : null;
        if ($folder !== null) {
            $this->access->authorizeOwner($folder, $request->user());
            $this->access->ensureUnlocked($folder, $request);
        }
        try {
            $this->storage->upload($request->user(), $request->file('files'), $folder, $request->validated('password'));
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages(['files' => 'The upload could not be saved. Please try again.']);
        }
        $route = match ($request->route()->getName()) {
            'app.photos.store' => 'app.photos',
            'app.videos.store' => 'app.videos',
            'app.documents.store' => 'app.documents',
            default => 'app.dashboard',
        };

        return $folder !== null
            ? to_route('app.folders.show', $folder->uuid)->with('status', 'Files uploaded.')
            : to_route($route)->with('status', 'Files uploaded.');
    }
}
