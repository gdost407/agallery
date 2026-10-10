<aside class="offcanvas-lg offcanvas-start gallery-sidebar" tabindex="-1" id="libraryNavigation" aria-labelledby="libraryNavigationLabel">
    <div class="sidebar-brand">
        <a href="{{ route('app.dashboard') }}" class="brand"><span class="brand-mark"><i class="bi bi-intersect" aria-hidden="true"></i></span><span id="libraryNavigationLabel">AGallery<span class="brand-dot">.</span></span></a>
        <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#libraryNavigation" aria-label="Close navigation"></button>
    </div>
    <div class="sidebar-content">
        <button class="btn btn-primary upload-button" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasUploadDoc" aria-controls="offcanvasUploadDoc"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Add files</button>
        <p class="nav-caption">YOUR LIBRARY</p>
        <nav class="library-nav" aria-label="Library">
            @foreach ([
                ['app.dashboard', 'grid', 'My library'],
                ['app.folders', 'folder', 'Folders'],
                ['app.photos', 'image', 'Photos'],
                ['app.videos', 'play-btn', 'Videos'],
                ['app.documents', 'file-earmark-text', 'Documents'],
                ['app.starred', 'star', 'Starred'],
                ['app.recent', 'clock-history', 'Recent'],
                ['app.shared', 'people', 'Shared with me'],
                ['app.trash', 'trash3', 'Trash'],
                ['app.private', 'shield-lock', 'Private folders'],
            ] as [$routeName, $icon, $label])
                <a href="{{ route($routeName) }}" class="library-nav-link {{ request()->routeIs($routeName) || ($routeName === 'app.dashboard' && request()->routeIs('dashboard')) ? 'active' : '' }}" @if(request()->routeIs($routeName) || ($routeName === 'app.dashboard' && request()->routeIs('dashboard'))) aria-current="page" @endif>
                    <i class="bi bi-{{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>
        <div class="sidebar-note storage-card">
            <strong>Everything, together.</strong>
            <div class="storage-summary">
                <div class="storage-chart" role="progressbar" aria-label="Storage used" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $storageUsage['percent'] }}" aria-valuetext="{{ $storageUsage['used_label'] }} of {{ $storageUsage['total_label'] }} used; {{ $storageUsage['remaining_label'] }} available">
                    <svg viewBox="0 0 80 80" aria-hidden="true">
                        <circle class="storage-chart-track" cx="40" cy="40" r="32" />
                        @if ($storageUsage['percent'] > 0)<circle class="storage-chart-used" cx="40" cy="40" r="32" pathLength="100" stroke-dasharray="{{ $storageUsage['percent'] }} 100" />@endif
                    </svg>
                    <span>{{ $storageUsage['percent'] }}%<small>used</small></span>
                </div>
                <div class="storage-total"><strong>{{ $storageUsage['used_label'] }}</strong><span>of {{ $storageUsage['total_label'] }} used</span></div>
            </div>
            <div class="storage-legend">
                <span><i class="storage-dot storage-dot-used" aria-hidden="true"></i>Used <strong>{{ $storageUsage['used_label'] }}</strong></span>
                <span><i class="storage-dot" aria-hidden="true"></i>Available <strong>{{ $storageUsage['remaining_label'] }}</strong></span>
            </div>
            <button class="btn btn-primary storage-upgrade" type="button" data-bs-toggle="modal" data-bs-target="#storageUpgradeModal"><i class="bi bi-plus-lg" aria-hidden="true"></i>Buy more storage</button>
            <span class="storage-preview">Includes files in trash</span>
        </div>
        <a class="sidebar-profile" href="{{ route('profile.edit') }}"><i class="bi bi-gear" aria-hidden="true"></i>Settings &amp; profile</a>
        <div class="d-lg-none mt-2"><x-theme-toggle /></div>
    </div>
</aside>

<div class="modal fade" id="storageUpgradeModal" tabindex="-1" aria-labelledby="storageUpgradeTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="storageUpgradeTitle">Buy more storage</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">Monthly extra storage: ₹99 for 1 GB, ₹199 for 2 GB, or ₹399 for 5 GB. Checkout is not available yet.</div>
            <div class="modal-footer"><button class="btn btn-primary" type="button" data-bs-dismiss="modal">Got it</button></div>
        </div>
    </div>
</div>
