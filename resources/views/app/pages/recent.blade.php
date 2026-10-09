@extends('app.layouts.layouts')

@section('title', 'Recent')

@section('content')
    <x-library-browser page="recent" :files="$files" :folders="$folders" :current-folder="$currentFolder" :locked-files="$lockedFiles" :selection-folders="$selectionFolders" />
@endsection
