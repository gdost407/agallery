@props(['analytics'])

<section class="storage-analytics" aria-labelledby="storageOverviewTitle">
    <div class="section-heading"><h2 id="storageOverviewTitle">Storage overview</h2><span>Includes private files and Trash</span></div>
    <div class="analytics-overview">
        <div class="analytics-chart-card">
            <div class="analytics-donut" style="background: {{ $analytics['chart'] }}" role="img" aria-label="{{ $analytics['summary']['used_label'] }} used, {{ $analytics['summary']['remaining_label'] }} available out of {{ $analytics['summary']['total_label'] }}">
                <div><strong>{{ $analytics['summary']['used_label'] }}</strong><span>used of {{ $analytics['summary']['total_label'] }}</span></div>
            </div>
            <p>{{ $analytics['summary']['percent'] }}% of your storage used</p>
        </div>
        <div class="analytics-metrics">
            @foreach ($analytics['types'] as $category => $type)
                <a class="analytics-metric" href="{{ route(match ($category) { 'image' => 'app.photos', 'video' => 'app.videos', 'document' => 'app.documents', default => 'app.recent' }) }}">
                    <span class="analytics-type-icon" style="color: {{ $type['color'] }}"><i class="bi bi-{{ $type['icon'] }}" aria-hidden="true"></i></span>
                    <div><span>{{ $type['name'] }}</span><strong>{{ $type['label'] }}</strong><small>{{ $type['count'] }} files · {{ $type['percent'] }}% of storage</small></div>
                </a>
            @endforeach
            <div class="analytics-metric analytics-empty"><span class="analytics-type-icon"><i class="bi bi-hdd" aria-hidden="true"></i></span><div><span>Empty / available</span><strong>{{ $analytics['summary']['remaining_label'] }}</strong><small>Total capacity {{ $analytics['summary']['total_label'] }}</small></div></div>
            @if ($analytics['reserved_label'] !== '0 B')<div class="analytics-metric"><div><span>Reserved storage</span><strong>{{ $analytics['reserved_label'] }}</strong></div></div>@endif
        </div>
    </div>
    <div class="analytics-tables">
        <div class="analytics-panel">
            <h3>Documents by type</h3>
            @forelse ($analytics['documents'] as $document)
                <div class="analytics-document"><div><strong>{{ $document['extension'] }}</strong><span>{{ $document['count'] }} files · {{ $document['label'] }}</span></div><div class="analytics-bar" role="img" aria-label="{{ $document['extension'] }}: {{ $document['percent'] }}% of document storage"><span style="width: {{ $document['percent'] }}%"></span></div></div>
            @empty
                <p class="text-secondary mb-0">No documents uploaded yet.</p>
            @endforelse
        </div>
        <div class="analytics-panel">
            <h3>Storage by folder</h3>
            <p class="small text-secondary">Folder sizes include all subfolders and files in Trash.</p>
            <div class="table-responsive"><table class="table analytics-folder-table mb-0"><thead><tr><th scope="col">Folder</th><th scope="col">Files</th><th scope="col">Size</th></tr></thead><tbody>
                @foreach ($analytics['folders'] as $row)
                    <tr><td>@if ($row['url'])<a href="{{ $row['url'] }}">{{ $row['name'] }}</a>@else{{ $row['name'] }}@endif</td><td>{{ $row['count'] }}</td><td class="text-nowrap">{{ $row['label'] }}</td></tr>
                @endforeach
            </tbody></table></div>
        </div>
    </div>
</section>
