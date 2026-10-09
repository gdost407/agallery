@extends('app.layouts.layouts')

@section('title', 'My library')

@section('content')
    @if ($analytics)<x-library-analytics :analytics="$analytics" />@endif
    <x-library-browser page="home" :files="$files" :folders="$folders" :current-folder="$currentFolder" :locked-files="$lockedFiles" :locked-folders="$lockedFolders" :folder-usage="$folderUsage" :current-folder-usage="$currentFolderUsage" />
@endsection
