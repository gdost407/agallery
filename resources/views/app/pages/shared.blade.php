@extends('app.layouts.layouts')

@section('title', 'Shared with me')

@section('content')
    <x-library-browser page="shared" :files="$files" :folders="$folders" :current-folder="$currentFolder" :locked-files="$lockedFiles" />
@endsection
