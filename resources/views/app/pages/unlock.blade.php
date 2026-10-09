@extends('app.layouts.layouts')
@section('title', 'Unlock')
@section('content')
    @php
        $isFolder = $resource instanceof \App\Models\Folder;
        $name = $isFolder ? $resource->name : $resource->original_name;
    @endphp
    <div class="page-heading"><div><p class="eyebrow">PASSWORD PROTECTED</p><h1 class="text-break">{{ $name }}</h1><p class="page-description">Enter the password to open this {{ $isFolder ? 'folder' : 'file' }}.</p></div></div>
    <form action="{{ $unlockAction ?? route($isFolder ? 'app.folders.unlock' : 'app.files.unlock', $resource->uuid) }}" method="POST" class="card border-0 p-4">
        @csrf
        <label for="unlockPassword" class="form-label">Password</label>
        <input type="password" id="unlockPassword" name="password" class="form-control mb-3" required maxlength="255" autocomplete="current-password" autofocus>
        <button class="btn btn-primary align-self-start" type="submit">Unlock</button>
    </form>
@endsection
