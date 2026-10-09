@extends('app.layouts.layouts')

@section('title', 'Photos')

@section('content')
    <x-library-browser page="photos" :files="$files" :folders="$folders" :current-folder="$currentFolder" :locked-files="$lockedFiles" :selection-folders="$selectionFolders" />
@endsection
