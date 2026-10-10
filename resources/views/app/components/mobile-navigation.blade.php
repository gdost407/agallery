<nav class="mobile-navigation" aria-label="Mobile navigation">
    @foreach ([['app.dashboard', 'grid', 'Library'], ['app.photos', 'image', 'Photos'], ['app.videos', 'play-btn', 'Videos'], ['app.folders', 'folder', 'Folders']] as [$routeName, $icon, $label])
        @php($active = request()->routeIs($routeName) || ($routeName === 'app.folders' && request()->routeIs('app.folders.*')))
        <a href="{{ route($routeName) }}" class="mobile-navigation-item {{ $active ? 'active' : '' }}" @if($active) aria-current="page" @endif><i class="bi bi-{{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span></a>
    @endforeach
    <button type="button" class="mobile-navigation-item" data-bs-toggle="offcanvas" data-bs-target="#libraryNavigation" aria-controls="libraryNavigation"><i class="bi bi-three-dots" aria-hidden="true"></i><span>More</span></button>
</nav>
