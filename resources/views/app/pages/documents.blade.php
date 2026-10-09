@extends('app.layouts.layouts')

@section('title', 'Documents')

@section('content')
    <x-library-browser page="documents" :files="$files" :folders="$folders" :current-folder="$currentFolder" :locked-files="$lockedFiles" :selection-folders="$selectionFolders" />
@endsection
