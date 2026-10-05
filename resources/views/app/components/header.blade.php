<header class="gallery-header">
    <button class="icon-button d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#libraryNavigation" aria-controls="libraryNavigation" aria-label="Open navigation"><i class="bi bi-list" aria-hidden="true"></i></button>
    <div class="search-box">
        <i class="bi bi-search" aria-hidden="true"></i>
        <label class="visually-hidden" for="librarySearch">Search files on this page</label>
        <input id="librarySearch" type="search" placeholder="{{ request()->routeIs('profile.edit') ? 'Search is available in your library' : 'Search your files, photos and more' }}" autocomplete="off" @disabled(request()->routeIs('profile.edit'))>
        <span class="search-hint d-none d-md-inline">Search library</span>
    </div>
    <div class="dropdown">
        <button class="account-button" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Open account menu">
            <span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <span class="d-none d-xl-block text-start"><strong>{{ auth()->user()->name }}</strong><small>Personal account</small></span>
            <i class="bi bi-chevron-down d-none d-sm-block" aria-hidden="true"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end account-menu">
            <div class="px-3 py-2"><strong class="d-block">{{ auth()->user()->name }}</strong><small class="text-secondary">{{ auth()->user()->email }}</small></div>
            <hr class="dropdown-divider">
            <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2" aria-hidden="true"></i>Manage profile</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Sign out</button></form>
        </div>
    </div>
</header>
