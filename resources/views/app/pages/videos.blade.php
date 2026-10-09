@extends('app.layouts.layouts')

@section('title', 'Videos')

@section('content')
    <x-library-browser page="videos" :files="$files" :folders="$folders" :current-folder="$currentFolder" :locked-files="$lockedFiles" />
@endsection
