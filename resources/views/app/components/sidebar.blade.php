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
                ['app.photos', 'image', 'Photos'],
                ['app.videos', 'play-btn', 'Videos'],
                ['app.documents', 'file-earmark-text', 'Documents'],
                ['app.starred', 'star', 'Starred'],
                ['app.recent', 'clock-history', 'Recent'],
                ['app.shared', 'people', 'Shared with me'],
                ['app.trash', 'trash3', 'Trash'],
            ] as [$routeName, $icon, $label])
                <a href="{{ route($routeName) }}" class="library-nav-link {{ request()->routeIs($routeName) || ($routeName === 'app.dashboard' && request()->routeIs('dashboard')) ? 'active' : '' }}" @if(request()->routeIs($routeName) || ($routeName === 'app.dashboard' && request()->routeIs('dashboard'))) aria-current="page" @endif>
                    <i class="bi bi-{{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>
        <div class="sidebar-note"><span class="note-icon"><i class="bi bi-cloud-check" aria-hidden="true"></i></span><strong>Everything, together.</strong><p>Your memories, projects and everyday files. One simple space.</p><span class="demo-label">Preview with sample files</span></div>
        <a class="sidebar-profile" href="{{ route('profile.edit') }}"><i class="bi bi-gear" aria-hidden="true"></i>Settings &amp; profile</a>
    </div>
</aside>
