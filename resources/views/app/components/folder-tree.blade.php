@foreach ($explorerFolders->get($parentId, collect()) as $treeFolder)
    <div class="folder-tree-node">
        @if ($explorerFolders->has($treeFolder->id))
            <details @if(in_array($treeFolder->id, $expandedFolderIds, true)) open @endif>
                <summary><a class="folder-tree-link {{ $currentFolder?->id === $treeFolder->id ? 'active' : '' }}" href="{{ route('app.folders.show', $treeFolder->uuid) }}" @if($currentFolder?->id === $treeFolder->id) aria-current="page" @endif><i class="bi bi-folder" aria-hidden="true"></i>{{ $treeFolder->name }}</a></summary>
                <div class="folder-tree-children">@include('app.components.folder-tree', ['parentId' => $treeFolder->id])</div>
            </details>
        @else
            <a class="folder-tree-link folder-tree-leaf {{ $currentFolder?->id === $treeFolder->id ? 'active' : '' }}" href="{{ route('app.folders.show', $treeFolder->uuid) }}" @if($currentFolder?->id === $treeFolder->id) aria-current="page" @endif><i class="bi bi-folder" aria-hidden="true"></i>{{ $treeFolder->name }}</a>
        @endif
    </div>
@endforeach
