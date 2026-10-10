<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <x-pwa-head />
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ request()->routeIs('share.*') ? 'Shared file' : (request()->routeIs('register') ? 'Sign up' : 'Welcome back') }} | AGallery</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link href="{{ asset('assets/websites/css/auth.css') }}" rel="stylesheet">
    </head>
    <body class="font-sans antialiased auth-page">
        <header class="auth-header">
            <a href="{{ route('home') }}" aria-label="AGallery home"><img src="{{ asset('assets/AGallery-Logo.png') }}" alt="AGallery" width="150"></a>
            <a class="auth-back" href="{{ route('home') }}">&larr; Back to home</a>
        </header>
        <main class="auth-shell">
            <aside class="auth-story" aria-label="About AGallery">
                <span class="auth-eyebrow">YOUR PERSONAL DIGITAL LIBRARY</span>
                <h2>Every memory.<br>Every file.<br><span>One place.</span></h2>
                <p>A home for your favourite moments and everyday essentials. Bring your photos, videos, and documents together with AGallery.</p>
                <div class="auth-collections" aria-hidden="true">
                    <div class="collection-card"><span class="collection-symbol">&#9728;</span><strong>Little adventures</strong><small>Photos &amp; memories</small></div>
                    <div class="collection-card"><span class="collection-symbol">&#9654;</span><strong>Moments in motion</strong><small>Your video collection</small></div>
                    <div class="collection-card"><span class="collection-symbol">&#9734;</span><strong>Worth keeping close</strong><small>Everyday favourites</small></div>
                </div>
                <span class="auth-story-note">A little more order. More room for what matters.</span>
            </aside>
            <section class="auth-card" aria-label="Account access">
                {{ $slot }}
            </section>
        </main>
        <footer class="auth-footer">&copy; {{ date('Y') }} AGallery. A home for your digital life.</footer>
        <x-pwa-install />
    </body>
</html>
