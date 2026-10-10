<section class="media-sync-panel mb-4 p-3 border rounded-3 bg-white" data-media-sync
    data-account="{{ auth()->id() }}" data-status-url="{{ route('app.media-sync.status') }}"
    data-upload-url="{{ route('app.files.store') }}" data-token="{{ csrf_token() }}">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div><h2 class="fs-6 mb-1">Photo &amp; video sync</h2><p class="small text-secondary mb-0">Sync a folder and its subfolders to Image and Video when you open the app. Files on your device stay unchanged.</p></div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-primary" data-sync-select hidden>Choose media folder</button>
            <button type="button" class="btn btn-sm btn-outline-primary" data-sync-start hidden>Sync now</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-sync-stop hidden>Stop</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-sync-forget hidden>Disconnect folder</button>
            <a class="btn btn-sm btn-outline-primary" data-sync-refresh href="{{ url()->current() }}" hidden>Refresh library</a>
        </div>
    </div>
    <p class="small mt-2 mb-0" data-sync-status role="status" aria-live="polite">Checking folder access…</p>
    <div class="progress mt-3" data-sync-progress role="progressbar" aria-label="Media sync progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" hidden><div class="progress-bar" data-sync-bar style="width: 0%">0%</div></div>
    <p class="small text-secondary mt-2 mb-0" data-sync-wake hidden></p>
</section>
