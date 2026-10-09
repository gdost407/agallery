<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-pwa-head />
    <meta name="description" content="AGallery brings your photos, videos, and documents together in one personal digital library.">
    <title>AGallery | Your personal digital library</title>
    <link href="{{ asset('assets/websites/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/websites/css/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/websites/css/home.css') }}" rel="stylesheet">
</head>
<body>
    @include('website.components.header')
    <main id="main-content">@yield('content')</main>
    @include('website.components.footer')
    <script src="{{ asset('assets/websites/js/bootstrap.bundle.min.js') }}"></script>
    <x-pwa-install />
</body>
</html>
