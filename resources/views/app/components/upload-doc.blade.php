<div class="offcanvas offcanvas-end upload-panel" tabindex="-1" id="offcanvasUploadDoc" aria-labelledby="offcanvasUploadDocLabel">
    <div class="offcanvas-header border-bottom"><h2 class="offcanvas-title fs-5" id="offcanvasUploadDocLabel">Add to your library</h2><button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button></div>
    <div class="offcanvas-body">
        <p class="text-secondary">Upload to {{ isset($currentFolder) ? $currentFolder->name : 'My library' }}.</p>
        @php
            $uploadRoute = request()->routeIs('app.photos') ? 'app.photos.store' : (request()->routeIs('app.videos') ? 'app.videos.store' : (request()->routeIs('app.documents') ? 'app.documents.store' : 'app.files.store'));
        @endphp
        <form action="{{ route($uploadRoute) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? '' }}">
            <label class="upload-dropzone" for="document"><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i><strong>Choose files</strong><span>or drop them here</span><small>Up to 20 files, 512 MB per file</small></label>
            <input class="form-control mt-3" type="file" id="document" name="files[]" multiple required aria-describedby="uploadNote" @if(request()->routeIs('app.photos')) accept="image/*" @elseif(request()->routeIs('app.videos')) accept="video/*" @elseif(request()->routeIs('app.documents')) accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.rtf,.odt,.ods,.odp" @endif>
            <div id="selectedFiles" class="selected-files mt-3" aria-live="polite"></div>
            <label for="uploadPassword" class="form-label mt-3">File password (optional)</label>
            <input type="password" id="uploadPassword" class="form-control" name="password" minlength="8" maxlength="255" autocomplete="new-password">
            <label for="uploadPasswordConfirmation" class="form-label mt-3">Confirm file password</label>
            <input type="password" id="uploadPasswordConfirmation" class="form-control" name="password_confirmation" minlength="8" maxlength="255" autocomplete="new-password">
            <p class="upload-info mt-4" id="uploadNote">Files are private. If supplied, this password protects each selected file.</p>
            <button type="submit" class="btn btn-primary w-100">Upload files</button>
        </form>
    </div>
</div>
<div class="modal fade" id="createFolderModal" tabindex="-1" aria-labelledby="createFolderTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h2 class="modal-title fs-5" id="createFolderTitle">Create folder</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <form action="{{ route('app.folders.store') }}" method="POST">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $currentFolder->id ?? '' }}">
            <div class="modal-body">
                <label for="folderName" class="form-label">Folder name</label>
                <input type="text" class="form-control mb-3" id="folderName" name="name" required maxlength="255" value="{{ old('name') }}">
                <label for="folderPassword" class="form-label">Password (optional)</label>
                <input type="password" class="form-control mb-3" id="folderPassword" name="password" minlength="8" maxlength="255" autocomplete="new-password">
                <label for="folderPasswordConfirmation" class="form-label">Confirm password</label>
                <input type="password" class="form-control" id="folderPasswordConfirmation" name="password_confirmation" minlength="8" maxlength="255" autocomplete="new-password">
                <p class="text-secondary small mt-3 mb-0">A folder password also protects the files and folders inside it.</p>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Create folder</button></div>
        </form>
    </div></div>
</div>
