@extends('app.layouts.layouts')

@section('title', 'Private folders')

@section('content')
    <x-library-browser page="private" :files="$files" :folders="$folders" :current-folder="$currentFolder" :locked-files="$lockedFiles" :locked-folders="$lockedFolders" />
@endsection
