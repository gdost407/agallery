@extends('app.layouts.layouts')

@section('title', 'Starred')

@section('content')
    <x-library-browser page="starred" :files="$files" :folders="$folders" :current-folder="$currentFolder" :locked-files="$lockedFiles" :selection-folders="$selectionFolders" />
@endsection
