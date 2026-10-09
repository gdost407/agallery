@extends('app.layouts.layouts')

@section('title', 'My library')

@section('content')
    <x-library-browser page="home" :files="$files" :folders="$folders" :current-folder="$currentFolder" :locked-files="$lockedFiles" />
@endsection
