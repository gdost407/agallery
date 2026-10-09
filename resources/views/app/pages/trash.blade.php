@extends('app.layouts.layouts')

@section('title', 'Trash')

@section('content')
    <x-library-browser page="trash" :files="$files" :folders="$folders" :current-folder="$currentFolder" :locked-files="$lockedFiles" />
@endsection
