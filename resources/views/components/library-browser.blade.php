@props(['page' => 'home'])

@php
    $pages = [
        'home' => ['My library', 'A home for your memories. A place for your ideas.'],
        'photos' => ['Photos', 'Every little moment, beautifully together.'],
        'videos' => ['Videos', 'Your favourite moments, in motion.'],
        'documents' => ['Documents', 'Keep your work organised and easy to find.'],
        'starred' => ['Starred', 'The things you love, always close at hand.'],
        'recent' => ['Recent', 'Pick up right where you left off.'],
        'shared' => ['Shared with me', 'A space for the things you share together.'],
        'trash' => ['Trash', 'Review sample files removed from your library.'],
    ];
    [$heading, $description] = $pages[$page];
    $samples = [
        ['name' => 'An afternoon on the green.jpg', 'type' => 'photos', 'size' => '3.2 MB', 'date' => '2026-10-04', 'image' => 'anna-rosar-ew-olGvgCCs-unsplash.jpg', 'starred' => true, 'shared' => false, 'trash' => false],
        ['name' => 'Friends & fairways.jpg', 'type' => 'photos', 'size' => '4.8 MB', 'date' => '2026-10-03', 'image' => 'girl-taking-selfie-with-friends-golf-field.jpg', 'starred' => true, 'shared' => true, 'trash' => false],
        ['name' => 'Project proposal.docx', 'type' => 'documents', 'size' => '856 KB', 'date' => '2026-10-03', 'image' => null, 'starred' => true, 'shared' => true, 'trash' => false],
        ['name' => 'Weekend highlights.mp4', 'type' => 'videos', 'size' => '156 MB', 'date' => '2026-10-02', 'image' => 'frederik-rosar-NDSZcCfnsbY-unsplash.jpg', 'starred' => false, 'shared' => true, 'trash' => false],
        ['name' => 'Annual report.pdf', 'type' => 'documents', 'size' => '2.4 MB', 'date' => '2026-10-02', 'image' => null, 'starred' => false, 'shared' => false, 'trash' => false],
        ['name' => 'A perfect Sunday.jpg', 'type' => 'photos', 'size' => '2.9 MB', 'date' => '2026-10-01', 'image' => 'anna-rosar-ZxFyVBHMK-c-unsplash.jpg', 'starred' => false, 'shared' => false, 'trash' => false],
        ['name' => 'Autumn practice.jpg', 'type' => 'photos', 'size' => '3.7 MB', 'date' => '2026-09-28', 'image' => 'frederik-rosar-NDSZcCfnsbY-unsplash.jpg', 'starred' => false, 'shared' => false, 'trash' => false],
        ['name' => 'Trip budget.xlsx', 'type' => 'documents', 'size' => '128 KB', 'date' => '2026-09-27', 'image' => null, 'starred' => false, 'shared' => true, 'trash' => false],
        ['name' => 'A day outdoors.mp4', 'type' => 'videos', 'size' => '84 MB', 'date' => '2026-09-26', 'image' => 'anna-rosar-ew-olGvgCCs-unsplash.jpg', 'starred' => true, 'shared' => false, 'trash' => false],
        ['name' => 'Presentation.pptx', 'type' => 'documents', 'size' => '4.2 MB', 'date' => '2026-09-25', 'image' => null, 'starred' => false, 'shared' => false, 'trash' => false],
        ['name' => 'Practice makes progress.jpg', 'type' => 'photos', 'size' => '2.1 MB', 'date' => '2026-09-24', 'image' => 'professional-golf-player.jpg', 'starred' => false, 'shared' => false, 'trash' => false],
        ['name' => 'Old notes.pdf', 'type' => 'documents', 'size' => '420 KB', 'date' => '2026-09-20', 'image' => null, 'starred' => false, 'shared' => false, 'trash' => true],
    ];
    $files = array_filter($samples, fn ($file) => match ($page) {
        'photos', 'videos', 'documents' => $file['type'] === $page && ! $file['trash'],
        'starred' => $file['starred'] && ! $file['trash'],
        'shared' => $file['shared'] && ! $file['trash'],
        'trash' => $file['trash'],
        default => ! $file['trash'],
    });
@endphp

<div class="page-heading">
    <div><p class="eyebrow">YOUR PERSONAL SPACE</p><h1>{{ $heading }}</h1><p class="page-description">{{ $description }}</p></div>
    <button class="btn btn-primary heading-upload" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasUploadDoc" aria-controls="offcanvasUploadDoc"><i class="bi bi-cloud-arrow-up me-2" aria-hidden="true"></i>Add files</button>
</div>

@if ($page === 'home')
    <section class="welcome-banner" aria-labelledby="welcomeTitle">
        <div class="welcome-copy"><span class="banner-tag"><span></span>A little more organised</span><h2 id="welcomeTitle">Your world.<br>All in one place.</h2><p>From your favourite photos to your next big project.<br class="d-none d-md-block"> Make room for the things that matter.</p><a href="{{ route('app.photos') }}" class="btn btn-dark">Explore your photos<i class="bi bi-arrow-up-right ms-3" aria-hidden="true"></i></a></div>
        <div class="memory-collage" aria-hidden="true"><div class="memory-photo memory-back"><img src="{{ asset('assets/websites/images/frederik-rosar-NDSZcCfnsbY-unsplash.jpg') }}" alt=""><span>A day to remember</span></div><div class="memory-photo memory-front"><img src="{{ asset('assets/websites/images/girl-taking-selfie-with-friends-golf-field.jpg') }}" alt=""><span>The good moments <i class="bi bi-heart-fill"></i></span></div><span class="collage-spark">✦</span></div>
    </section>
    <section class="collections" aria-labelledby="collectionsTitle"><div class="section-heading"><h2 id="collectionsTitle">Find your flow</h2><span>Browse by file type</span></div><div class="row g-3">
        @foreach ([['photos', 'image', 'Photos', 'rose', 'Memories worth keeping'], ['videos', 'play-btn', 'Videos', 'lavender', 'Stories in motion'], ['documents', 'file-earmark-text', 'Documents', 'blue', 'Ideas, plans & projects']] as [$type, $icon, $label, $tone, $subtitle])
            <div class="col-12 col-sm-4"><a href="{{ route('app.'.$type) }}" class="collection-card {{ $tone }}"><span class="collection-icon"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i></span><div><strong>{{ $label }}</strong><span>{{ $subtitle }}</span></div><i class="bi bi-arrow-up-right collection-arrow" aria-hidden="true"></i></a></div>
        @endforeach
    </div></section>
@endif

<section class="file-library" data-library data-page="{{ $page }}" aria-labelledby="filesTitle">
    <div class="section-heading file-section-heading"><div class="d-flex align-items-center gap-2"><h2 id="filesTitle">{{ $page === 'home' ? 'Your files' : $heading }}</h2><span class="count-badge" id="fileCount" aria-live="polite">{{ count($files) }}</span></div><span class="demo-label">Sample files</span></div>
    <div class="library-toolbar">
        <div class="filter-chips" role="group" aria-label="Filter files by type">
            @foreach (['all' => 'All files', 'photos' => 'Photos', 'videos' => 'Videos', 'documents' => 'Documents'] as $type => $label)
                @if (! in_array($page, ['photos', 'videos', 'documents']) || $type === 'all')
                    <button type="button" class="filter-chip {{ $type === 'all' ? 'active' : '' }}" data-filter="{{ $type }}" aria-pressed="{{ $type === 'all' ? 'true' : 'false' }}">{{ $type === 'all' && in_array($page, ['photos', 'videos', 'documents']) ? 'All '.$page : $label }}</button>
                @endif
            @endforeach
        </div>
        <div class="d-flex align-items-center gap-2 toolbar-options">
            <label class="visually-hidden" for="fileSort">Sort files</label><select id="fileSort" class="form-select sort-select"><option value="newest">Newest first</option><option value="oldest">Oldest first</option><option value="name">Name A–Z</option></select>
            <div class="view-toggle" role="group" aria-label="File view"><button class="active" type="button" data-view="grid" aria-label="Grid view" aria-pressed="true"><i class="bi bi-grid" aria-hidden="true"></i></button><button type="button" data-view="list" aria-label="List view" aria-pressed="false"><i class="bi bi-list-ul" aria-hidden="true"></i></button></div>
        </div>
    </div>
    <div class="file-grid" id="fileGrid">
        @foreach ($files as $file)
            @php
                $extension = strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION));
                $documentTone = match ($extension) { 'PDF' => 'coral', 'XLSX' => 'mint', 'PPTX' => 'peach', default => 'blue' };
                $fileIcon = match ($file['type']) { 'photos' => 'image', 'videos' => 'play-btn', default => 'file-earmark-text' };
            @endphp
            <article class="file-card" data-file data-name="{{ $file['name'] }}" data-type="{{ $file['type'] }}" data-date="{{ $file['date'] }}" data-extension="{{ $extension }}" data-image="{{ $file['image'] ? asset('assets/websites/images/'.$file['image']) : '' }}">
                <button type="button" class="file-preview {{ $file['type'] === 'documents' ? 'document-preview '.$documentTone : '' }}" data-preview aria-label="Preview {{ $file['name'] }}">
                    @if ($file['image'])
                        <img src="{{ asset('assets/websites/images/'.$file['image']) }}" alt="" loading="lazy">
                        @if ($file['type'] === 'videos')<span class="video-play"><i class="bi bi-play-fill" aria-hidden="true"></i></span>@endif
                    @else
                        <div class="document-sheet"><span>{{ $extension }}</span><i class="bi bi-file-earmark-text" aria-hidden="true"></i><div class="document-lines"><b></b><b></b><b></b></div></div>
                    @endif
                    <span class="file-extension">{{ $extension }}</span>
                </button>
                <div class="file-info"><div class="file-title-row"><i class="bi bi-{{ $fileIcon }} type-icon" aria-hidden="true"></i><button type="button" class="file-name" data-preview title="{{ $file['name'] }}">{{ $file['name'] }}</button>@if($file['starred'])<i class="bi bi-star-fill file-star" aria-label="Starred"></i>@endif</div><div class="file-meta"><span>{{ $file['size'] }}</span><span class="meta-dot">·</span><time datetime="{{ $file['date'] }}">{{ date('M j, Y', strtotime($file['date'])) }}</time>@if($file['shared'])<i class="bi bi-people ms-auto" title="Shared sample" aria-label="Shared"></i>@endif</div></div>
            </article>
        @endforeach
    </div>
    <div class="search-empty d-none" id="searchEmpty" role="status"><span><i class="bi bi-search" aria-hidden="true"></i></span><h3>No matching files</h3><p>Try another name or choose a different file type.</p><button type="button" class="btn btn-outline-primary" id="resetSearch">Clear filters</button></div>
</section>
