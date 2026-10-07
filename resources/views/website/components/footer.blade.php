<footer class="site-footer">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div><strong>AGallery</strong><p class="mb-0 small">A home for your memories and everyday files.</p></div>
        <div class="d-flex flex-wrap gap-4">
            <a href="#features">Explore features</a>
            @auth
                <a href="{{ route('app.dashboard') }}">My library</a>
            @else
                <a href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}">Sign up</a>
            @endauth
        </div>
        <span class="small">&copy; {{ date('Y') }} AGallery</span>
    </div>
</footer>
