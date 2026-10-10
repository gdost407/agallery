@extends('app.layouts.layouts')
@section('title', $file->original_name)
@php($immersive = in_array($file->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif', 'video/mp4', 'video/webm']))
@if($immersive) @section('immersive-viewer', 'true') @endif
@section('content')
    @unless($immersive)
    <div class="page-heading"><div><p class="eyebrow">{{ strtoupper($file->extension) }} FILE</p><h1 class="text-break">{{ $file->original_name }}</h1><p class="page-description">{{ \Illuminate\Support\Number::fileSize($file->size_bytes) }}</p></div><a class="btn btn-primary" href="{{ route('app.files.download', $file->uuid) }}">Download</a></div>
    <a href="{{ $file->folder_id && $file->user_id === auth()->id() ? route('app.folders.show', $file->folder->uuid) : route('app.dashboard') }}" class="d-inline-block mb-3">Back to library</a>
    @endunless
    <div class="{{ $immersive ? 'immersive-viewer' : 'card border-0 p-3 mb-4 text-center' }} {{ str_starts_with($file->mime_type, 'video/') ? 'immersive-video' : '' }} media-viewer" data-media-viewer @if($immersive) data-immersive @endif data-previous="{{ $previousFile ? route('app.files.show', $previousFile->uuid) : '' }}" data-next="{{ $nextFile ? route('app.files.show', $nextFile->uuid) : '' }}">
        @if($immersive)
            <div class="viewer-overlay-top">
                <a class="viewer-icon-button" href="{{ $file->folder_id && $file->user_id === auth()->id() ? route('app.folders.show', $file->folder->uuid) : route('app.dashboard') }}" aria-label="Close viewer"><i class="bi bi-arrow-left" aria-hidden="true"></i></a>
                <a class="viewer-icon-button" href="{{ route('app.files.download', $file->uuid) }}" aria-label="Download file"><i class="bi bi-download" aria-hidden="true"></i></a>
            </div>
        @endif
        <div class="viewer-stage" data-viewer-stage>
        @if (in_array($file->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif']))
            <img class="preview-image" src="{{ route('app.files.content', $file->uuid) }}" alt="{{ $file->original_name }}" draggable="false">
        @elseif (in_array($file->mime_type, ['video/mp4', 'video/webm']))
            <video class="w-100" controls preload="metadata" src="{{ route('app.files.content', $file->uuid) }}"></video>
        @elseif (in_array($file->mime_type, ['audio/mpeg', 'audio/ogg']))
            <audio class="w-100" controls src="{{ route('app.files.content', $file->uuid) }}"></audio>
        @elseif ($file->mime_type === 'application/pdf')
            <iframe title="{{ $file->original_name }}" src="{{ route('app.files.content', $file->uuid) }}" class="w-100 border-0" height="600"></iframe>
        @else
            <p class="my-4">Download this file to open it on your device.</p>
        @endif
        </div>
        @if($immersive)
            <div class="viewer-overlay-bottom">
                @if($previousFile)<a class="viewer-icon-button" href="{{ route('app.files.show', $previousFile->uuid) }}" data-viewer-navigate="previous" aria-label="Previous photo or video"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>@else<span></span>@endif
                <button class="viewer-details-button" type="button" data-bs-toggle="offcanvas" data-bs-target="#fileDetailsPanel" aria-controls="fileDetailsPanel" aria-expanded="false"><i class="bi bi-chevron-up" aria-hidden="true"></i><span>Swipe up for details</span></button>
                @if($nextFile)<a class="viewer-icon-button" href="{{ route('app.files.show', $nextFile->uuid) }}" data-viewer-navigate="next" aria-label="Next photo or video"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>@else<span></span>@endif
            </div>
        @endif
    </div>
    @unless($immersive)
    <div class="d-flex justify-content-between align-items-center gap-2 mb-4">
        @if ($previousFile)<a class="btn btn-outline-secondary" href="{{ route('app.files.show', $previousFile->uuid) }}" aria-label="Previous photo or video"><i class="bi bi-chevron-left" aria-hidden="true"></i>Previous</a>@else<span></span>@endif
        <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#fileDetailsPanel" aria-controls="fileDetailsPanel">Actions &amp; details <i class="bi bi-chevron-up" aria-hidden="true"></i></button>
        @if ($nextFile)<a class="btn btn-outline-secondary" href="{{ route('app.files.show', $nextFile->uuid) }}" aria-label="Next photo or video">Next<i class="bi bi-chevron-right" aria-hidden="true"></i></a>@else<span></span>@endif
    </div>
    @if ($file->user_id === auth()->id())
        <div class="d-flex gap-2 mb-4">
            <form method="POST" action="{{ route('app.files.star', $file->uuid) }}">@csrf @method('PATCH')<button class="btn btn-outline-primary" type="submit">{{ $file->starred_at ? 'Remove star' : 'Star file' }}</button></form>
        </div>
    @endif
    @endunless
    <div class="offcanvas offcanvas-bottom file-details-panel" @if($immersive) data-bs-backdrop="false" data-bs-scroll="true" @endif tabindex="-1" id="fileDetailsPanel" aria-labelledby="fileDetailsTitle">
        <div class="offcanvas-header"><h2 class="offcanvas-title fs-5" id="fileDetailsTitle">Actions &amp; details</h2><button class="btn-close" type="button" data-bs-dismiss="offcanvas" aria-label="Close details"></button></div>
        <div class="offcanvas-body">
            <dl class="file-details-grid">
                <dt>Name</dt><dd class="text-break">{{ $file->original_name }}</dd>
                <dt>Type</dt><dd>{{ strtoupper($file->extension) }} · {{ $file->mime_type }}</dd>
                <dt>Size</dt><dd>{{ \Illuminate\Support\Number::fileSize($file->size_bytes) }}</dd>
                <dt>Folder</dt><dd>{{ $file->folder?->name ?? 'My library' }}</dd>
                <dt>Uploaded</dt><dd>{{ $file->created_at->format('M j, Y, g:i a') }}</dd>
            </dl>
            @if ($file->user_id === auth()->id())
                @if($immersive)<form method="POST" action="{{ route('app.files.star', $file->uuid) }}" class="mb-3">@csrf @method('PATCH')<button class="btn btn-outline-primary" type="submit"><i class="bi bi-star me-1" aria-hidden="true"></i>{{ $file->starred_at ? 'Remove star' : 'Star file' }}</button></form>@endif
                <form method="POST" action="{{ route('app.files.destroy', $file->uuid) }}" class="mb-3" onsubmit="return confirm('Permanently delete this file? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-outline-danger" type="submit"><i class="bi bi-trash me-1" aria-hidden="true"></i>Delete</button><span class="small text-secondary ms-2">Permanently removes the file and frees storage</span></form>
                <form method="POST" action="{{ route('app.files.copy', $file->uuid) }}" class="file-transfer-form">
                    @csrf
                    <label for="copyDestination" class="form-label">Copy to folder</label>
                    <select id="copyDestination" name="folder_id" class="form-select"><option value="">Default folder (by file type)</option>@foreach ($destinations as $destination)<option value="{{ $destination->id }}" @selected($destination->id === $file->folder_id)>{{ $destination->name }}</option>@endforeach</select>
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-files me-1" aria-hidden="true"></i>Copy</button>
                </form>
                <form method="POST" action="{{ route('app.files.move', $file->uuid) }}" class="file-transfer-form">
                    @csrf @method('PATCH')
                    <label for="moveDestination" class="form-label">Move to folder</label>
                    <select id="moveDestination" name="folder_id" class="form-select"><option value="">Default folder (by file type)</option>@foreach ($destinations as $destination)<option value="{{ $destination->id }}" @selected($destination->id === $file->folder_id)>{{ $destination->name }}</option>@endforeach</select>
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-folder-symlink me-1" aria-hidden="true"></i>Move</button>
                </form>
                <p class="small text-secondary mt-3">Copying uses additional storage. Protected destination folders must be unlocked first.</p>
            @endif
        </div>
    </div>
@endsection
