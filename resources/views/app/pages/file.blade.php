@extends('app.layouts.layouts')
@section('title', $file->original_name)
@section('content')
    <div class="page-heading"><div><p class="eyebrow">{{ strtoupper($file->extension) }} FILE</p><h1 class="text-break">{{ $file->original_name }}</h1><p class="page-description">{{ \Illuminate\Support\Number::fileSize($file->size_bytes) }}</p></div><a class="btn btn-primary" href="{{ route('app.files.download', $file->uuid) }}">Download</a></div>
    <a href="{{ $file->folder_id && $file->user_id === auth()->id() ? route('app.folders.show', $file->folder->uuid) : route('app.dashboard') }}" class="d-inline-block mb-3">Back to library</a>
    <div class="card border-0 p-3 mb-4 text-center">
        @if (in_array($file->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif']))
            <img class="preview-image" src="{{ route('app.files.content', $file->uuid) }}" alt="{{ $file->original_name }}">
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
    @if ($file->user_id === auth()->id())
        <div class="d-flex gap-2 mb-4">
            <form method="POST" action="{{ route('app.files.star', $file->uuid) }}">@csrf @method('PATCH')<button class="btn btn-outline-primary" type="submit">{{ $file->starred_at ? 'Remove star' : 'Star file' }}</button></form>
            <form method="POST" action="{{ route('app.files.destroy', $file->uuid) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger" type="submit">Move to trash</button></form>
        </div>
        <x-resource-password :action="route('app.files.password', $file->uuid)" :protected="$file->password_hash !== null" />
    @endif
@endsection
