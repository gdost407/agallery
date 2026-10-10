<link rel="manifest" href="{{ url('/manifest.webmanifest') }}">
<meta name="theme-color" content="#3267e3">
<script src="{{ asset('assets/app/js/theme.js') }}?v={{ filemtime(public_path('assets/app/js/theme.js')) }}"></script>
<link rel="stylesheet" href="{{ asset('assets/app/css/theme.css') }}?v={{ filemtime(public_path('assets/app/css/theme.css')) }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="AGallery">
<link rel="apple-touch-icon" sizes="180x180" href="{{ url('/pwa-icon-180.png') }}">
<link rel="stylesheet" href="{{ url('/pwa.css') }}?v={{ filemtime(public_path('pwa.css')) }}">
<script src="{{ url('/pwa.js') }}?v={{ filemtime(public_path('pwa.js')) }}" data-service-worker="{{ url('/service-worker.js') }}" defer></script>
