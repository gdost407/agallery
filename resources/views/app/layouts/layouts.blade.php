<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-pwa-head />
    <title>@yield('title', 'My library') · AGallery</title>
    <link rel="icon" href="{{ asset('assets/AGallery-Logo-Golden.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/app/libs/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/websites/css/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/app/css/gallery.css') }}?v={{ filemtime(public_path('assets/app/css/gallery.css')) }}">
</head>
<body class="gallery-app">
    <a class="skip-link" href="#main-content">Skip to content</a>
    @include('app.components.sidebar')
    <div class="gallery-workspace">
        @include('app.components.header')
        <main id="main-content" class="gallery-main" tabindex="-1">
            @if (session('status') && ! request()->routeIs('profile.*'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @yield('content')
        </main>
        <footer class="workspace-footer"><span>AGallery · A little space for everything.</span><span>Private library</span></footer>
    </div>
    @include('app.components.upload-doc')
    @include('app.components.footer')
    <x-pwa-install />
</body>
</html>
