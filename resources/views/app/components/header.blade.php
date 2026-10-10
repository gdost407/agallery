<header class="gallery-header">
    <a class="mobile-header-brand" href="{{ route('app.dashboard') }}" aria-label="AGallery home"><img src="{{ asset('assets/AGallery-Logo-Golden.png') }}" alt="AGallery" width="120" height="40"></a>
    <button class="icon-button d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#libraryNavigation" aria-controls="libraryNavigation" aria-label="Open navigation"><i class="bi bi-list" aria-hidden="true"></i></button>
    <form class="search-box" method="GET" action="{{ route('app.dashboard') }}" role="search">
        <label class="visually-hidden" for="librarySearch">Search your library</label>
        <input id="librarySearch" name="q" value="{{ is_string(request('q')) ? request('q') : '' }}" type="search" placeholder="Search your library" autocomplete="off">
        <button type="submit" class="header-search-submit" aria-label="Search library"><i class="bi bi-search" aria-hidden="true"></i></button>
        <span class="search-hint d-none d-md-inline">Search library</span>
    </form>
    <div class="dropdown">
        <button class="account-button" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Open account menu">
            <x-user-avatar :user="auth()->user()" />
            <span class="d-none d-xl-block text-start"><strong>{{ auth()->user()->name }}</strong><small>Personal account</small></span>
            <i class="bi bi-chevron-down d-none d-sm-block" aria-hidden="true"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end account-menu">
            <div class="px-3 py-2 d-flex align-items-center gap-2"><x-user-avatar :user="auth()->user()" /><div><strong class="d-block">{{ auth()->user()->name }}</strong><small class="text-secondary">{{ auth()->user()->email }}</small></div></div>
            <hr class="dropdown-divider">
            <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2" aria-hidden="true"></i>Manage profile</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Sign out</button></form>
        </div>
    </div>
</header>
