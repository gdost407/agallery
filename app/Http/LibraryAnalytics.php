<?php

namespace App\Http;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Support\Collection;

class LibraryAnalytics
{
    public function __construct(private StorageManager $storage) {}

    /** @return Collection<int, object> */
    private function groups(User $user): Collection
    {
        return $user->files()->withTrashed()
            ->selectRaw('category, LOWER(extension) as file_extension, folder_id, SUM(size_bytes) as bytes, COUNT(*) as file_count')
            ->groupBy('category', 'folder_id')->groupByRaw('LOWER(extension)')->get();
    }

    /**
     * @param  Collection<int, Folder>  $folders
     * @param  Collection<int, object>|null  $groups
     * @return array<int, array{bytes: int, count: int, label: string}>
     */
    public function folderUsage(User $user, Collection $folders, ?Collection $groups = null): array
    {
        $parents = $folders->pluck('parent_id', 'id')->all();
        $usage = [];
        foreach ($folders as $folder) {
            $usage[$folder->id] = ['bytes' => 0, 'count' => 0, 'label' => '0 B'];
        }
        foreach ($groups ?? $this->groups($user) as $group) {
            $id = $group->folder_id;
            $visited = [];
            while ($id !== null && isset($usage[$id]) && ! isset($visited[$id])) {
                $visited[$id] = true;
                $usage[$id]['bytes'] += (int) $group->bytes;
                $usage[$id]['count'] += (int) $group->file_count;
                $id = $parents[$id] ?? null;
            }
        }
        foreach ($usage as &$item) {
            $item['label'] = $this->storage->formatBytes($item['bytes']);
        }

        return $usage;
    }

    /**
     * @param  Collection<int, Folder>  $folderTree
     * @param  Collection<int, Folder>  $visibleRoots
     * @param  list<int>  $hiddenIds
     * @return array<string, mixed>
     */
    public function dashboard(User $user, Collection $folderTree, Collection $visibleRoots, array $hiddenIds): array
    {
        $groups = $this->groups($user);
        $usage = $this->folderUsage($user, $folderTree, $groups);
        $summary = $this->storage->summary($user);
        $types = [
            'image' => ['name' => 'Images', 'color' => '#4879bd', 'icon' => 'image', 'bytes' => 0, 'count' => 0],
            'video' => ['name' => 'Videos', 'color' => '#9871c4', 'icon' => 'play-btn', 'bytes' => 0, 'count' => 0],
            'document' => ['name' => 'Documents', 'color' => '#df9b42', 'icon' => 'file-earmark-text', 'bytes' => 0, 'count' => 0],
            'other' => ['name' => 'Other', 'color' => '#4b9e91', 'icon' => 'file-earmark', 'bytes' => 0, 'count' => 0],
        ];
        $documents = [];
        foreach ($groups as $group) {
            $type = isset($types[$group->category]) ? $group->category : 'other';
            $types[$type]['bytes'] += (int) $group->bytes;
            $types[$type]['count'] += (int) $group->file_count;
            if ($type === 'document') {
                $extension = $group->file_extension ?: 'unknown';
                $documents[$extension] ??= ['extension' => strtoupper($extension), 'bytes' => 0, 'count' => 0];
                $documents[$extension]['bytes'] += (int) $group->bytes;
                $documents[$extension]['count'] += (int) $group->file_count;
            }
        }
        $denominator = max(1, $summary['total'], $summary['used'] + $user->reserved_storage_bytes);
        $stops = [];
        $position = 0;
        foreach ($types as &$type) {
            $type['label'] = $this->storage->formatBytes($type['bytes']);
            $type['percent'] = round($type['bytes'] / $denominator * 100, 2);
            $end = min(100, $position + $type['percent']);
            $stops[] = $type['color'].' '.$position.'% '.$end.'%';
            $position = $end;
        }
        unset($type);
        if ($user->reserved_storage_bytes > 0) {
            $end = min(100, $position + $user->reserved_storage_bytes / $denominator * 100);
            $stops[] = '#8993a2 '.$position.'% '.$end.'%';
            $position = $end;
        }
        $stops[] = '#e5eaf1 '.$position.'% 100%';
        $documents = collect($documents)->sortByDesc('bytes')->map(function (array $row) use ($types): array {
            $row['label'] = $this->storage->formatBytes($row['bytes']);
            $row['percent'] = $types['document']['bytes'] > 0 ? round($row['bytes'] / $types['document']['bytes'] * 100, 1) : 0;

            return $row;
        })->values();
        $folderRows = $visibleRoots->map(fn (Folder $folder): array => [
            'name' => $folder->name, 'url' => route('app.folders.show', $folder->uuid), ...$usage[$folder->id],
        ])->values();
        $unfiled = $groups->whereNull('folder_id');
        $folderRows->push(['name' => 'My library (outside folders)', 'url' => null,
            'bytes' => (int) $unfiled->sum('bytes'), 'count' => (int) $unfiled->sum('file_count'),
            'label' => $this->storage->formatBytes((int) $unfiled->sum('bytes'))]);
        $privateBytes = 0;
        $privateCount = 0;
        foreach ($folderTree as $folder) {
            if ($folder->parent_id === null && in_array($folder->id, $hiddenIds, true)) {
                $privateBytes += $usage[$folder->id]['bytes'];
                $privateCount += $usage[$folder->id]['count'];
            }
        }
        if ($privateCount > 0) {
            $folderRows->push(['name' => 'Private folders', 'url' => route('app.private'), 'bytes' => $privateBytes,
                'count' => $privateCount, 'label' => $this->storage->formatBytes($privateBytes)]);
        }
        $otherBytes = max(0, $summary['used'] - (int) $folderRows->sum('bytes'));
        if ($otherBytes > 0) {
            $folderRows->push(['name' => 'Archived folders', 'url' => null, 'bytes' => $otherBytes,
                'count' => max(0, (int) $groups->sum('file_count') - (int) $folderRows->sum('count')), 'label' => $this->storage->formatBytes($otherBytes)]);
        }

        return ['summary' => $summary, 'types' => $types, 'documents' => $documents,
            'folders' => $folderRows->sortByDesc('bytes')->values(), 'folderUsage' => $usage,
            'reserved_label' => $this->storage->formatBytes($user->reserved_storage_bytes),
            'chart' => 'conic-gradient('.implode(', ', $stops).')'];
    }
}
