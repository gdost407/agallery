@props(['page' => 'home', 'files', 'folders', 'currentFolder' => null, 'lockedFiles' => [], 'lockedFolders' => [], 'folderUsage' => [], 'currentFolderUsage' => null, 'selectionFolders' => [], 'explorerFolders' => collect(), 'expandedFolderIds' => []])

@php
    $headings = ['home' => 'My library', 'folders' => 'Folders', 'private' => 'Private folders', 'photos' => 'Photos', 'videos' => 'Videos', 'documents' => 'Documents', 'starred' => 'Starred', 'recent' => 'Recent', 'shared' => 'Shared with me', 'trash' => 'Trash'];
    $filesHeading = $page === 'home' && ! $currentFolder ? 'Recent uploads' : 'Your files';
    $heading = $currentFolder?->name ?? $headings[$page];
@endphp

<div class="page-heading">
    <div><p class="eyebrow">YOUR PERSONAL SPACE</p><h1>{{ $heading }}</h1><p class="page-description">{{ $page === 'trash' ? 'Trashed files still count towards your storage.' : 'Your uploaded files, organised in one place.' }}</p></div>
    <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#createFolderModal"><i class="bi bi-folder-plus me-2" aria-hidden="true"></i>New folder</button>
        <button class="btn btn-primary heading-upload" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasUploadDoc" aria-controls="offcanvasUploadDoc"><i class="bi bi-cloud-arrow-up me-2" aria-hidden="true"></i>Add files</button>
    </div>
</div>

@if ($currentFolder)
    <div class="d-flex gap-3 align-items-center mb-3">
        <a href="{{ $currentFolder->parent_id ? route('app.folders.show', $currentFolder->parent->uuid) : route($currentFolder->password_hash ? 'app.private' : 'app.folders') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to parent</a>
        <span class="text-secondary">{{ $currentFolder->password_hash ? 'Password protected' : 'No folder password' }}</span>
    </div>
    @if ($currentFolderUsage)<p class="folder-usage-summary"><i class="bi bi-folder" aria-hidden="true"></i><strong>{{ $currentFolderUsage['label'] }}</strong> · {{ $currentFolderUsage['count'] }} files including subfolders and Trash</p>@endif
    @if (!$currentFolder->system_key)
    <details class="item-actions mb-3"><summary><i class="bi bi-three-dots" aria-hidden="true"></i>Folder actions</summary><div class="item-actions-content">
    <form method="POST" action="{{ route('app.folders.destroy', $currentFolder->uuid) }}" class="mb-3" onsubmit="return confirm('Permanently delete this folder, all subfolders and their files? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-outline-danger" type="submit"><i class="bi bi-trash me-2" aria-hidden="true"></i>Delete folder permanently</button></form>
    <x-resource-password :action="route('app.folders.password', $currentFolder->uuid)" :protected="$currentFolder->password_hash !== null" />
    </div></details>
    @endif
@endif

@if ($page === 'folders' || $currentFolder)
<div class="folder-explorer">
    <nav class="folder-explorer-sidebar" aria-label="Folder explorer">
        <h2>Explorer</h2>
        <a class="folder-tree-link {{ !$currentFolder ? 'active' : '' }}" href="{{ route('app.folders') }}"><i class="bi bi-house" aria-hidden="true"></i>All folders</a>
        @include('app.components.folder-tree', ['parentId' => 0])
        <a class="folder-tree-link" href="{{ route('app.private') }}"><i class="bi bi-lock" aria-hidden="true"></i>Private folders</a>
    </nav>
    <div class="folder-explorer-content">
@endif

@if ($folders->isNotEmpty())
    <section class="collections" aria-label="Folders">
        <div class="section-heading"><h2>Folders</h2></div>
        <div class="row g-3">
            @foreach ($folders as $folder)
                @php
                    $folderLocked = $lockedFolders[$folder->id] ?? false;
                @endphp
                <div class="col-12 col-sm-6 col-xl-4">
                    <a href="{{ route('app.folders.show', $folder->uuid) }}" class="collection-card blue">
                        <span class="collection-icon {{ $folderLocked ? 'protected-folder-icon' : '' }}"><i class="bi bi-folder" aria-hidden="true"></i>@if ($folderLocked)<span class="folder-lock-badge"><i class="bi bi-lock-fill" aria-hidden="true"></i><i class="bi bi-link-45deg" aria-hidden="true"></i></span>@endif</span>
                        <div class="text-break"><strong>{{ $folder->name }}</strong><span>{{ $folder->password_hash ? 'Password protected' : 'Folder' }}</span>@if(isset($folderUsage[$folder->id]))<span>{{ $folderUsage[$folder->id]['label'] }} · {{ $folderUsage[$folder->id]['count'] }} files including subfolders</span>@endif</div>
                        <i class="bi bi-arrow-up-right collection-arrow" aria-hidden="true"></i>
                    </a>
                    @if (!$folder->system_key && !$folderLocked)
                        <details class="item-actions mt-2"><summary aria-label="Actions for folder {{ $folder->name }}"><i class="bi bi-three-dots" aria-hidden="true"></i>Actions</summary><div class="item-actions-content">
                        <form method="POST" action="{{ route('app.folders.destroy', $folder->uuid) }}" class="mt-2" onsubmit="return confirm('Permanently delete this folder, all subfolders and their files? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Delete folder {{ $folder->name }}">Delete folder</button></form>
                        </div></details>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
@endif

@if ($page === 'private')
    <p class="text-secondary">Protected folders and their contents are hidden from your normal gallery. Open a folder and enter its password to access the files inside.</p>
    @if ($folders->isEmpty())<p>No private folders yet. Create a folder with a password to add one here.</p>@endif
@else
<section class="file-library" data-library data-page="{{ $page }}" aria-labelledby="filesTitle">
    @if ($page !== 'trash' && $page !== 'shared')
        <div class="selection-toolbar mb-3" data-selection-toolbar hidden>
            <span data-selection-count role="status" aria-live="polite">0 selected</span>
            <details class="item-actions" data-selection-menu><summary><i class="bi bi-three-dots" aria-hidden="true"></i>Selected file actions</summary><div class="item-actions-content">
            <form id="selectedFilesDelete" method="POST" action="{{ route('app.files.selected') }}" class="selection-actions" onsubmit="return event.submitter?.value !== 'delete' || confirm('Permanently delete the selected files? This cannot be undone.')">
                @csrf
                <button class="btn btn-danger btn-sm" type="submit" name="action" value="delete" data-delete-selected data-selection-action disabled><i class="bi bi-trash me-1" aria-hidden="true"></i>Delete selected</button>
                <button class="btn btn-outline-primary btn-sm" type="button" data-selection-action data-selection-transfer="copy" data-bs-toggle="modal" data-bs-target="#selectedTransferModal" disabled>Copy</button>
                <button class="btn btn-outline-primary btn-sm" type="button" data-selection-action data-selection-transfer="move" data-bs-toggle="modal" data-bs-target="#selectedTransferModal" disabled>Move</button>
                <button class="btn btn-outline-primary btn-sm" type="submit" name="action" value="share" data-selection-action disabled>Share</button>
                <button class="btn btn-outline-primary btn-sm" type="submit" name="action" value="download" data-selection-action disabled>Download</button>
            </form>
            </div></details>
            <button class="btn btn-outline-secondary btn-sm" type="button" data-cancel-selection>Cancel</button>
        </div>
        <div class="modal fade" id="selectedTransferModal" tabindex="-1" aria-labelledby="selectedTransferTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h2 class="modal-title fs-5" id="selectedTransferTitle">Transfer selected files</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body"><label class="form-label" for="selectedDestination">Destination folder</label><select class="form-select" id="selectedDestination" name="folder_id" form="selectedFilesDelete"><option value="">Default folder (by file type)</option>@foreach ($selectionFolders as $destination)<option value="{{ $destination->id }}">{{ $destination->name }}</option>@endforeach</select><p class="small text-secondary mt-3 mb-0">Protected folders must be unlocked first. Copying uses additional storage.</p></div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary" name="action" value="copy" form="selectedFilesDelete" data-confirm-transfer>Copy selected files</button></div>
        </div></div></div>
        <button class="btn btn-sm btn-outline-secondary mb-3" type="button" data-start-selection>Select files</button>
    @endif
    <div class="section-heading file-section-heading"><div class="d-flex align-items-center gap-2"><h2 id="filesTitle">{{ $filesHeading }}</h2><span class="count-badge" id="fileCount" aria-live="polite">{{ $files->count() }}</span></div><span>{{ $files->total() }} files</span></div>
    <div class="library-toolbar">
        <div class="d-flex align-items-center gap-2 toolbar-options">
            <label class="visually-hidden" for="fileSort">Sort files on this page</label><select id="fileSort" class="form-select sort-select"><option value="newest">Newest first</option><option value="oldest">Oldest first</option><option value="name">Name A–Z</option></select>
            <div class="view-toggle" role="group" aria-label="File view"><button class="active" type="button" data-view="grid" aria-label="Grid view" aria-pressed="true"><i class="bi bi-grid" aria-hidden="true"></i></button><button type="button" data-view="list" aria-label="List view" aria-pressed="false"><i class="bi bi-list-ul" aria-hidden="true"></i></button></div>
        </div>
    </div>
    <div class="file-grid" id="fileGrid">
        @foreach ($files as $file)
            @php
                $type = match ($file->category) { 'image' => 'photos', 'video' => 'videos', 'document' => 'documents', default => 'other' };
                $locked = $lockedFiles[$file->id] ?? false;
                $mediaCard = in_array($file->category, ['image', 'video'], true);
            @endphp
            <article class="file-card {{ $mediaCard ? 'media-thumbnail-card' : '' }}" data-file data-name="{{ $file->original_name }}" data-type="{{ $type }}" data-date="{{ $file->created_at->toIso8601String() }}">
                @if ($page !== 'trash' && $page !== 'shared' && $file->user_id === auth()->id() && ! $locked)
                    <label class="file-select"><input type="checkbox" name="files[]" value="{{ $file->uuid }}" form="selectedFilesDelete" data-file-select aria-label="Select {{ $file->original_name }}"></label>
                @endif
                @if ($page !== 'trash')
                    <a href="{{ route('app.files.show', $file->uuid) }}" class="file-preview document-preview blue" aria-label="Open {{ $file->original_name }}">
                        @if (! $locked)
                            @if(in_array($file->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif']))
                                <img src="{{ route('app.files.content', $file->uuid) }}" alt="" loading="lazy" decoding="async" width="480" height="320">
                            @elseif(in_array($file->mime_type, ['video/mp4', 'video/webm']))
                                <video class="file-video-preview" src="{{ route('app.files.content', $file->uuid) }}#t=0.1" muted playsinline preload="metadata" aria-hidden="true" tabindex="-1"></video>
                                <span class="video-preview-play"><i class="bi bi-play-fill" aria-hidden="true"></i></span>
                            @else
                            <img src="{{ route('app.files.thumbnail', $file->uuid) }}" alt="" loading="lazy" decoding="async" width="480" height="320">
                            @endif
                        @else
                            <div class="document-sheet locked-preview-placeholder" aria-hidden="true"><span>{{ strtoupper($file->extension) }}</span><i class="bi bi-{{ $type === 'videos' ? 'play-btn' : ($type === 'photos' ? 'image' : 'file-earmark-text') }}"></i></div>
                            <span class="protected-preview-overlay"><span class="protected-preview-icons"><i class="bi bi-lock-fill" aria-hidden="true"></i><i class="bi bi-link-45deg" aria-hidden="true"></i></span><span>Password protected</span><small>Enter password to open</small></span>
                        @endif
                        <span class="file-extension">{{ strtoupper($file->extension) }}</span>
                    </a>
                @else
                    <div class="file-preview document-preview blue"><div class="document-sheet"><span>{{ strtoupper($file->extension) }}</span><i class="bi bi-trash" aria-hidden="true"></i></div></div>
                @endif
                <div class="file-info">
                    <div class="file-title-row">
                        @if ($page === 'trash')<span class="file-name {{ $mediaCard ? 'file-name-badge' : '' }}" title="{{ $file->original_name }}">{{ $file->original_name }}</span>@else<a class="file-name {{ $mediaCard ? 'file-name-badge' : '' }}" href="{{ route('app.files.show', $file->uuid) }}" title="{{ $file->original_name }}">{{ $file->original_name }}</a>@endif
                        @if ($file->starred_at)<i class="bi bi-star-fill file-star" aria-label="Starred"></i>@endif
                        @if ($locked)<i class="bi bi-lock" aria-label="Password protected"></i>@endif
                    </div>
                    <div class="file-meta"><span>{{ \Illuminate\Support\Number::fileSize($file->size_bytes) }}</span><span class="meta-dot">·</span><time datetime="{{ $file->created_at->toDateString() }}">{{ $file->created_at->format('M j, Y') }}</time></div>
                    @if (!$mediaCard || $page === 'trash')
                    <details class="item-actions mt-2"><summary aria-label="Actions for {{ $file->original_name }}"><i class="bi bi-three-dots" aria-hidden="true"></i>Actions</summary><div class="item-actions-content">
                    @if ($page !== 'trash')
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('app.files.show', $file->uuid) }}">Open &amp; details</a>
                        @if (!$locked)<a class="btn btn-sm btn-outline-primary" href="{{ route('app.files.download', $file->uuid) }}">Download</a>@endif
                    @endif
                    @if ($page === 'trash')
                        <form action="{{ route('app.files.restore', $file->uuid) }}" method="POST" class="mt-2">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-primary" type="submit">Restore</button></form>
                    @endif
                    @if ($file->user_id === auth()->id() && !$locked)
                        <form method="POST" action="{{ route('app.files.destroy', $file->uuid) }}" class="mt-2" onsubmit="return confirm('Permanently delete this file? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Delete {{ $file->original_name }}">Delete permanently</button></form>
                    @endif
                    </div></details>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
    <div class="search-empty {{ $files->isEmpty() ? '' : 'd-none' }}" id="searchEmpty" role="status"><span><i class="bi bi-search" aria-hidden="true"></i></span><h3>No files found</h3><p>Upload a file or try another search.</p><button type="button" class="btn btn-outline-primary" id="resetSearch">Clear search</button></div>
    <div class="mt-4">@if($page === 'home' && !$currentFolder && !request()->filled('q'))<a class="btn btn-outline-primary" href="{{ route('app.recent') }}">View all uploads</a>@else{{ $files->links() }}@endif</div>
</section>
@endif
@if ($page === 'folders' || $currentFolder)
    </div>
</div>
@endif
