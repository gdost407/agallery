<nav class="navbar navbar-expand-lg site-nav" aria-label="Main navigation">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}"><img src="{{ asset('assets/AGallery-Logo.png') }}" alt="AGallery" width="150"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#homeNavigation" aria-controls="homeNavigation" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="homeNavigation">
            <ul class="navbar-nav mx-auto gap-lg-4">
                <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#how-it-works">How it works</a></li>
            </ul>
            <div class="d-flex gap-2 py-2">
                @auth
                    <a class="btn btn-brand" href="{{ route('app.dashboard') }}">Open my library &rarr;</a>
                @else
                    <a class="btn btn-outline-brand" href="{{ route('login') }}">Log in</a>
                    <a class="btn btn-brand" href="{{ route('register') }}">Sign up</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
