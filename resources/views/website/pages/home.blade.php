@extends('website.layouts.layouts')

@section('content')
    <section class="home-hero" id="about">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="eyebrow">YOUR PERSONAL DIGITAL LIBRARY</span>
                    <h1>Every memory.<br>Every file.<br><span>One place.</span></h1>
                    <p class="hero-description">Meet AGallery: a dedicated space for your photos, videos, and documents. Bring your digital life together in a library that feels easy to explore.</p>
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        @auth
                            <a href="{{ route('app.dashboard') }}" class="btn btn-brand btn-lg">Open my library &rarr;</a>
                        @else
                            <a href="{{ route('register') }}" class="btn btn-brand btn-lg">Create your account &rarr;</a>
                            <a href="{{ route('login') }}" class="btn btn-outline-brand btn-lg">Log in</a>
                        @endauth
                    </div>
                    <div class="hero-tags"><span><i class="bi bi-image" aria-hidden="true"></i> Photos</span><span><i class="bi bi-play-circle" aria-hidden="true"></i> Videos</span><span><i class="bi bi-file-earmark-text" aria-hidden="true"></i> Documents</span></div>
                </div>
                <div class="col-lg-6">
                    <div class="library-preview" aria-label="Illustration of the AGallery library">
                        <div class="preview-top"><strong>AGallery</strong><span>LIBRARY PREVIEW</span></div>
                        <div class="preview-body">
                            <p class="small mb-1">Your space, beautifully organised</p><h2 class="h4 mb-4">My library</h2>
                            <div class="preview-categories"><span>All files</span><span>Photos</span><span>Videos</span><span>Documents</span></div>
                            <div class="preview-grid">
                                @foreach ([['image', 'Little adventures', 'Photo collection'], ['play-circle', 'Moments in motion', 'Video collection'], ['file-earmark-text', 'Everyday essentials', 'Document collection'], ['star', 'Worth keeping close', 'Starred favourites']] as [$icon, $title, $type])
                                    <div class="preview-tile"><div class="tile-art art-{{ $loop->iteration }}"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i></div><strong>{{ $title }}</strong><span>{{ $type }}</span></div>
                                @endforeach
                            </div>
                            <p class="preview-note mb-0">A little more order. More room for what matters.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="feature-section" id="features">
        <div class="container">
            <div class="section-heading"><span class="eyebrow">MADE FOR YOUR DIGITAL LIFE</span><h2>A place for everything you keep.</h2><p>Explore the dedicated spaces inside your AGallery library.</p></div>
            <div class="row g-4">
                @foreach ([['image', 'Photos & videos', 'Keep your pictures and video collections in dedicated views, so your memories are easy to browse.'], ['file-earmark-text', 'Your documents', 'Give your everyday documents a home alongside your media, with a separate view for files.'], ['star', 'Favourites & recent files', 'Use the starred and recent sections to revisit favourites and find the files you have been working with.'], ['collection', 'A clear library structure', 'Move between your library, shared files, and trash from one familiar workspace.']] as [$icon, $title, $description])
                    <div class="col-md-6 col-xl-3"><article class="feature-card"><span class="feature-icon"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i></span><h3>{{ $title }}</h3><p>{{ $description }}</p></article></div>
                @endforeach
            </div>
        </div>
    </section>
    <section class="steps-section" id="how-it-works">
        <div class="container">
            <div class="section-heading"><span class="eyebrow">GETTING STARTED</span><h2>Your library starts here.</h2></div>
            <div class="row g-4">
                @foreach ([['Create an account', 'Sign up with your name, email address, and password.'], ['Step into your workspace', 'Log in to open your personal AGallery dashboard.'], ['Explore your library', 'Browse photos, videos, documents, and favourites from the sidebar.']] as [$title, $description])
                    <div class="col-md-4"><div class="step-number">0{{ $loop->iteration }}</div><h3 class="h5">{{ $title }}</h3><p>{{ $description }}</p></div>
                @endforeach
            </div>
            <div class="signup-banner"><div><span class="eyebrow">ALL TOGETHER, AT LAST</span><h2>Make room for what matters.</h2><p class="mb-0">Your memories and files deserve a place of their own.</p></div>
                @auth
                    <a href="{{ route('app.dashboard') }}" class="btn btn-brand btn-lg">Go to my library &rarr;</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-brand btn-lg">Sign up for AGallery &rarr;</a>
                @endauth
            </div>
        </div>
    </section>
@endsection
