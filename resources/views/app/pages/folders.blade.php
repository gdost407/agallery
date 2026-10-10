@extends('app.layouts.layouts')

@section('title', 'Folders')

@section('content')
    <x-library-browser page="folders" :files="$files" :folders="$folders" :current-folder="$currentFolder" :locked-files="$lockedFiles" :locked-folders="$lockedFolders" :folder-usage="$folderUsage" :selection-folders="$selectionFolders" :explorer-folders="$explorerFolders" :expanded-folder-ids="$expandedFolderIds" />
@endsection
