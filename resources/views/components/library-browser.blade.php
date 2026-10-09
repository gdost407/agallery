@props(['page' => 'home', 'files', 'folders', 'currentFolder' => null, 'lockedFiles' => [], 'lockedFolders' => []])

@php
    $headings = ['home' => 'My library', 'photos' => 'Photos', 'videos' => 'Videos', 'documents' => 'Documents', 'starred' => 'Starred', 'recent' => 'Recent', 'shared' => 'Shared with me', 'trash' => 'Trash'];
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
        <a href="{{ $currentFolder->parent_id ? route('app.folders.show', $currentFolder->parent->uuid) : route('app.dashboard') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to parent</a>
        <span class="text-secondary">{{ $currentFolder->password_hash ? 'Password protected' : 'No folder password' }}</span>
    </div>
    <x-resource-password :action="route('app.folders.password', $currentFolder->uuid)" :protected="$currentFolder->password_hash !== null" />
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
                        <div class="text-break"><strong>{{ $folder->name }}</strong><span>{{ $folder->password_hash ? 'Password protected' : 'Folder' }}</span></div>
                        <i class="bi bi-arrow-up-right collection-arrow" aria-hidden="true"></i>
                    </a>
                </div>
            @endforeach
        </div>
    </section>
@endif

<section class="file-library" data-library data-page="{{ $page }}" aria-labelledby="filesTitle">
    <div class="section-heading file-section-heading"><div class="d-flex align-items-center gap-2"><h2 id="filesTitle">Your files</h2><span class="count-badge" id="fileCount" aria-live="polite">{{ $files->count() }}</span></div><span>{{ $files->total() }} files</span></div>
    <div class="library-toolbar">
        <div class="filter-chips" role="group" aria-label="Filter files on this page">
            @foreach (['all' => 'All files', 'photos' => 'Photos', 'videos' => 'Videos', 'documents' => 'Documents', 'other' => 'Other files'] as $type => $label)
                <button type="button" class="filter-chip {{ $type === 'all' ? 'active' : '' }}" data-filter="{{ $type }}" aria-pressed="{{ $type === 'all' ? 'true' : 'false' }}">{{ $label }}</button>
            @endforeach
        </div>
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
            @endphp
            <article class="file-card" data-file data-name="{{ $file->original_name }}" data-type="{{ $type }}" data-date="{{ $file->created_at->toIso8601String() }}">
                @if ($page !== 'trash')
                    <a href="{{ route('app.files.show', $file->uuid) }}" class="file-preview document-preview blue" aria-label="Open {{ $file->original_name }}">
                        @if (! $locked)
                            <img src="{{ route('app.files.thumbnail', $file->uuid) }}" alt="" loading="lazy" decoding="async" width="480" height="320">
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
                        @if ($page === 'trash')<span class="file-name">{{ $file->original_name }}</span>@else<a class="file-name" href="{{ route('app.files.show', $file->uuid) }}" title="{{ $file->original_name }}">{{ $file->original_name }}</a>@endif
                        @if ($file->starred_at)<i class="bi bi-star-fill file-star" aria-label="Starred"></i>@endif
                        @if ($locked)<i class="bi bi-lock" aria-label="Password protected"></i>@endif
                    </div>
                    <div class="file-meta"><span>{{ \Illuminate\Support\Number::fileSize($file->size_bytes) }}</span><span class="meta-dot">·</span><time datetime="{{ $file->created_at->toDateString() }}">{{ $file->created_at->format('M j, Y') }}</time></div>
                    @if ($page === 'trash')
                        <form action="{{ route('app.files.restore', $file->uuid) }}" method="POST" class="mt-2">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-primary" type="submit">Restore</button></form>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
    <div class="search-empty {{ $files->isEmpty() ? '' : 'd-none' }}" id="searchEmpty" role="status"><span><i class="bi bi-search" aria-hidden="true"></i></span><h3>No files found</h3><p>Upload a file or try another search.</p><button type="button" class="btn btn-outline-primary" id="resetSearch">Clear filters</button></div>
    <div class="mt-4">{{ $files->links() }}</div>
</section>
